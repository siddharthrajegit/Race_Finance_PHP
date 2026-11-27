<?php
/**
 * Batch & Expiry / FEFO (First-Expired, First-Out) Model
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Warehouse.php';

class Batch extends Model {
    /**
     * List batches for firm or item using cross-DB FEFO ordering (earliest expiry first, NULL expiry last).
     */
    public static function getByFirmId(int $firmId, ?int $itemId = null, bool $inStockOnly = false): array {
        $sql = "
            SELECT b.*, i.name AS item_name, i.unit AS item_unit, i.item_code,
                   w.name AS warehouse_name
            FROM item_batches b
            JOIN items i ON i.id = b.item_id
            LEFT JOIN warehouses w ON w.id = b.warehouse_id
            WHERE b.firm_id = ?
        ";
        $params = [$firmId];
        if ($itemId) {
            $sql .= " AND b.item_id = ?";
            $params[] = $itemId;
        }
        if ($inStockOnly) {
            $sql .= " AND b.remaining_qty > 0";
        }
        $sql .= " ORDER BY CASE WHEN b.expiry_date IS NULL THEN 1 ELSE 0 END, b.expiry_date ASC, b.id ASC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM item_batches WHERE id = ? AND firm_id = ? LIMIT 1");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function getExpiringAlerts(int $firmId, int $withinDays = 30): array {
        $cutoff = date('Y-m-d', strtotime("+{$withinDays} days"));
        $stmt = self::db()->prepare("
            SELECT b.*, i.name AS item_name, i.unit AS item_unit
            FROM item_batches b
            JOIN items i ON i.id = b.item_id
            WHERE b.firm_id = ?
              AND b.remaining_qty > 0
              AND b.expiry_date IS NOT NULL
              AND b.expiry_date <= ?
            ORDER BY b.expiry_date ASC, b.id ASC
        ");
        $stmt->execute([$firmId, $cutoff]);
        return $stmt->fetchAll();
    }

    /**
     * Create a new batch or add incoming stock to an existing batch on purchase / receipt.
     */
    public static function upsertBatch(
        int $firmId,
        int $itemId,
        ?int $warehouseId,
        string $batchNumber,
        float $qty,
        ?string $mfgDate = null,
        ?string $expiryDate = null,
        float $purchaseRate = 0.0
    ): array {
        $batchNo = trim($batchNumber);
        if ($batchNo === '') {
            throw new Exception("Batch number cannot be empty.");
        }
        if ($qty <= 0) {
            throw new Exception("Batch quantity must be greater than 0.");
        }

        $db = self::db();
        $chk = $db->prepare("SELECT * FROM item_batches WHERE firm_id = ? AND item_id = ? AND batch_number = ? LIMIT 1");
        $chk->execute([$firmId, $itemId, $batchNo]);
        $existing = $chk->fetch();

        if ($existing) {
            $newInit = round((float)$existing['initial_qty'] + $qty, 4);
            $newRem = round((float)$existing['remaining_qty'] + $qty, 4);
            $db->prepare("
                UPDATE item_batches
                SET initial_qty = ?, remaining_qty = ?,
                    mfg_date = COALESCE(?, mfg_date),
                    expiry_date = COALESCE(?, expiry_date),
                    purchase_rate = CASE WHEN ? > 0 THEN ? ELSE purchase_rate END
                WHERE id = ? AND firm_id = ?
            ")->execute([
                $newInit, $newRem,
                $mfgDate ?: null, $expiryDate ?: null,
                $purchaseRate, $purchaseRate,
                (int)$existing['id'], $firmId
            ]);
            return self::getById((int)$existing['id'], $firmId);
        }

        $db->prepare("
            INSERT INTO item_batches (firm_id, item_id, warehouse_id, batch_number, mfg_date, expiry_date, purchase_rate, initial_qty, remaining_qty)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $firmId,
            $itemId,
            $warehouseId,
            $batchNo,
            $mfgDate ?: null,
            $expiryDate ?: null,
            max(0.0, $purchaseRate),
            round($qty, 4),
            round($qty, 4)
        ]);

        return self::getById((int)$db->lastInsertId(), $firmId);
    }

    /**
     * Deduct sold/consumed quantity following FEFO (earliest expiry first, NULL last).
     */
    public static function deductFefo(int $firmId, int $itemId, float $qty, ?int $preferredBatchId = null): array {
        if ($qty <= 0) return [];
        $need = $qty;
        $allocations = [];
        $db = self::db();

        if ($preferredBatchId) {
            $pref = self::getById($preferredBatchId, $firmId);
            if ($pref && (int)$pref['item_id'] === $itemId && (float)$pref['remaining_qty'] > 0) {
                $take = min($need, (float)$pref['remaining_qty']);
                $db->prepare("UPDATE item_batches SET remaining_qty = ? WHERE id = ?")
                   ->execute([round((float)$pref['remaining_qty'] - $take, 4), (int)$pref['id']]);
                $allocations[] = ['batch_id' => (int)$pref['id'], 'batch_number' => $pref['batch_number'], 'qty' => $take];
                $need -= $take;
            }
        }

        if ($need > 0.0001) {
            $batches = self::getByFirmId($firmId, $itemId, true);
            $upd = $db->prepare("UPDATE item_batches SET remaining_qty = ? WHERE id = ?");
            foreach ($batches as $b) {
                if ($need <= 0.0001) break;
                $avail = (float)$b['remaining_qty'];
                $take = min($need, $avail);
                $upd->execute([round($avail - $take, 4), (int)$b['id']]);
                $allocations[] = ['batch_id' => (int)$b['id'], 'batch_number' => $b['batch_number'], 'qty' => $take];
                $need -= $take;
            }
        }

        return $allocations;
    }

    public static function adjustBatchQty(int $batchId, int $firmId, float $delta): ?array {
        $b = self::getById($batchId, $firmId);
        if (!$b) return null;
        $newRem = round(max(0.0, (float)$b['remaining_qty'] + $delta), 4);
        self::db()->prepare("UPDATE item_batches SET remaining_qty = ? WHERE id = ? AND firm_id = ?")
            ->execute([$newRem, $batchId, $firmId]);
        return self::getById($batchId, $firmId);
    }
}
