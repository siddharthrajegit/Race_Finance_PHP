<?php
/**
 * Warehouse Stock Transfer Model
 * Executes atomic inter-warehouse stock movements inside database transactions.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Warehouse.php';
require_once __DIR__ . '/Item.php';

class StockTransfer extends Model {
    public static function getNextTransferNumber(int $firmId): string {
        $last = self::getOrInitFirmSequence($firmId, 'transfer', 'stock_transfers', 'transfer_number');
        $next = $last + 1;
        $stmt = self::db()->prepare("SELECT id FROM stock_transfers WHERE firm_id = ? AND transfer_number = ?");
        while (true) {
            $cand = self::formatDocumentNumber($next);
            $stmt->execute([$firmId, $cand]);
            if (!$stmt->fetch()) {
                return $cand;
            }
            $next++;
        }
    }

    public static function getByFirmId(int $firmId, int $limit = 100, int $offset = 0): array {
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);
        $stmt = self::db()->prepare("
            SELECT st.*,
                   sw.name AS source_warehouse_name,
                   dw.name AS dest_warehouse_name,
                   u.name AS created_by_name,
                   (SELECT COUNT(*) FROM stock_transfer_items sti WHERE sti.transfer_id = st.id) AS items_count,
                   (SELECT COALESCE(SUM(sti.quantity), 0) FROM stock_transfer_items sti WHERE sti.transfer_id = st.id) AS total_qty
            FROM stock_transfers st
            JOIN warehouses sw ON sw.id = st.source_warehouse_id
            JOIN warehouses dw ON dw.id = st.dest_warehouse_id
            LEFT JOIN users u ON u.id = st.created_by
            WHERE st.firm_id = ?
            ORDER BY st.transfer_date DESC, st.id DESC
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("
            SELECT st.*, sw.name AS source_warehouse_name, dw.name AS dest_warehouse_name
            FROM stock_transfers st
            JOIN warehouses sw ON sw.id = st.source_warehouse_id
            JOIN warehouses dw ON dw.id = st.dest_warehouse_id
            WHERE st.id = ? AND st.firm_id = ?
        ");
        $stmt->execute([$id, $firmId]);
        $transfer = $stmt->fetch();
        if (!$transfer) return null;

        $itStmt = self::db()->prepare("SELECT * FROM stock_transfer_items WHERE transfer_id = ? ORDER BY id ASC");
        $itStmt->execute([$id]);
        $transfer['items'] = $itStmt->fetchAll();
        return $transfer;
    }

    public static function create(array $data, array $itemsData): array {
        return self::transaction(function ($db) use ($data, $itemsData) {
            $firmId = (int)$data['firm_id'];
            $sourceWhId = (int)($data['source_warehouse_id'] ?? 0);
            $destWhId = (int)($data['dest_warehouse_id'] ?? 0);

            if ($sourceWhId <= 0 || $destWhId <= 0) {
                throw new Exception("Both source and destination warehouses are required.");
            }
            if ($sourceWhId === $destWhId) {
                throw new Exception("Source and destination warehouses must be different.");
            }

            $srcWh = Warehouse::getById($sourceWhId, $firmId);
            $dstWh = Warehouse::getById($destWhId, $firmId);
            if (!$srcWh || !$dstWh) {
                throw new Exception("Invalid warehouse selected for this firm.");
            }
            if (empty($itemsData)) {
                throw new Exception("Please add at least one item to transfer.");
            }

            $transferNumber = self::allocateFirmSequence(
                $firmId,
                'transfer',
                $data['transfer_number'] ?? null,
                'stock_transfers',
                'transfer_number'
            );

            $transferDate = !empty($data['transfer_date']) ? $data['transfer_date'] : date('Y-m-d');
            $createdBy = !empty($data['created_by']) ? (int)$data['created_by'] : null;

            $hdr = $db->prepare("
                INSERT INTO stock_transfers (firm_id, transfer_number, transfer_date, source_warehouse_id, dest_warehouse_id, status, notes, created_by)
                VALUES (?, ?, ?, ?, ?, 'completed', ?, ?)
            ");
            $hdr->execute([
                $firmId,
                $transferNumber,
                $transferDate,
                $sourceWhId,
                $destWhId,
                !empty($data['notes']) ? trim((string)$data['notes']) : null,
                $createdBy
            ]);
            $transferId = (int)$db->lastInsertId();

            $lineStmt = $db->prepare("
                INSERT INTO stock_transfer_items (transfer_id, item_id, item_name, unit, quantity)
                VALUES (?, ?, ?, ?, ?)
            ");

            // Aggregate quantities per item first to validate available stock accurately
            $requestedByItem = [];
            foreach ($itemsData as $row) {
                $itemId = (int)($row['item_id'] ?? 0);
                $qty = (float)($row['quantity'] ?? 0);
                if ($itemId <= 0 || $qty <= 0) {
                    throw new Exception("Every transfer line must have a valid item and quantity greater than 0.");
                }
                $requestedByItem[$itemId] = ($requestedByItem[$itemId] ?? 0.0) + $qty;
            }

            foreach ($requestedByItem as $itemId => $totalQty) {
                $item = Item::getById($itemId, $firmId);
                if (!$item) {
                    throw new Exception("Item #{$itemId} does not belong to this firm.");
                }
                $avail = Warehouse::getWarehouseItemQty($firmId, $sourceWhId, $itemId);
                if ($avail + 0.0001 < $totalQty) {
                    throw new Exception("Insufficient stock in {$srcWh['name']} for \"{$item['name']}\" (Available: {$avail} {$item['unit']}, Requested: {$totalQty}).");
                }
            }

            foreach ($itemsData as $row) {
                $itemId = (int)$row['item_id'];
                $qty = (float)$row['quantity'];
                $item = Item::getById($itemId, $firmId);

                $lineStmt->execute([
                    $transferId,
                    $itemId,
                    $item['name'],
                    $item['unit'] ?? 'PCS',
                    $qty
                ]);

                $unitCost = max(0.0, (float)($item['purchase_price'] ?? 0));

                // Debit source warehouse
                Warehouse::adjustWarehouseStock(
                    $firmId,
                    $sourceWhId,
                    $itemId,
                    -$qty,
                    'transfer_out',
                    $unitCost,
                    'stock_transfer',
                    $transferId,
                    "Transfer #{$transferNumber} to {$dstWh['name']}",
                    $createdBy
                );

                // Credit destination warehouse
                Warehouse::adjustWarehouseStock(
                    $firmId,
                    $destWhId,
                    $itemId,
                    $qty,
                    'transfer_in',
                    $unitCost,
                    'stock_transfer',
                    $transferId,
                    "Transfer #{$transferNumber} from {$srcWh['name']}",
                    $createdBy
                );
            }

            return self::getById($transferId, $firmId);
        });
    }
}
