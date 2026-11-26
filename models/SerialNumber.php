<?php
/**
 * Serial Number / IMEI Tracking Model
 * Guarantees firm-level unique serial numbers and full lifecycle state ('in_stock', 'sold', 'returned', 'damaged').
 */

require_once __DIR__ . '/Model.php';

class SerialNumber extends Model {
    public static function getByFirmId(int $firmId, ?int $itemId = null, ?string $status = null): array {
        $sql = "
            SELECT s.*, i.name AS item_name, i.unit AS item_unit, w.name AS warehouse_name
            FROM item_serials s
            JOIN items i ON i.id = s.item_id
            LEFT JOIN warehouses w ON w.id = s.warehouse_id
            WHERE s.firm_id = ?
        ";
        $params = [$firmId];
        if ($itemId) {
            $sql .= " AND s.item_id = ?";
            $params[] = $itemId;
        }
        if ($status && in_array($status, ['in_stock', 'sold', 'returned', 'damaged'], true)) {
            $sql .= " AND s.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY s.id DESC LIMIT 500";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM item_serials WHERE id = ? AND firm_id = ? LIMIT 1");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function parseSerialInput(string $rawText): array {
        $parts = preg_split('/[\r\n,;]+/', $rawText) ?: [];
        $clean = [];
        foreach ($parts as $p) {
            $s = trim($p);
            if ($s !== '' && !in_array($s, $clean, true)) {
                $clean[] = $s;
            }
        }
        return $clean;
    }

    public static function registerSerials(
        int $firmId,
        int $itemId,
        ?int $warehouseId,
        array $serialNumbers,
        ?int $purchaseInvoiceId = null,
        ?string $notes = null
    ): array {
        if (empty($serialNumbers)) {
            return [];
        }

        $db = self::db();
        $chk = $db->prepare("SELECT id, status FROM item_serials WHERE firm_id = ? AND serial_number = ? LIMIT 1");
        $ins = $db->prepare("
            INSERT INTO item_serials (firm_id, item_id, warehouse_id, serial_number, status, purchase_invoice_id, notes)
            VALUES (?, ?, ?, ?, 'in_stock', ?, ?)
        ");

        $created = [];
        foreach ($serialNumbers as $raw) {
            $sn = trim((string)$raw);
            if ($sn === '') continue;

            $chk->execute([$firmId, $sn]);
            if ($chk->fetch()) {
                throw new Exception("Duplicate Serial/IMEI '{$sn}' already registered for this business.");
            }
            $ins->execute([$firmId, $itemId, $warehouseId, $sn, $purchaseInvoiceId, $notes]);
            $created[] = self::getById((int)$db->lastInsertId(), $firmId);
        }

        return $created;
    }

    public static function sellSerials(int $firmId, int $itemId, array $serialNumbers, int $saleInvoiceId): void {
        if (empty($serialNumbers)) return;

        $db = self::db();
        $chk = $db->prepare("SELECT id, item_id, status FROM item_serials WHERE firm_id = ? AND serial_number = ? LIMIT 1");
        $upd = $db->prepare("
            UPDATE item_serials
            SET status = 'sold', sale_invoice_id = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND firm_id = ?
        ");

        foreach ($serialNumbers as $raw) {
            $sn = trim((string)$raw);
            if ($sn === '') continue;

            $chk->execute([$firmId, $sn]);
            $row = $chk->fetch();
            if (!$row || (int)$row['item_id'] !== $itemId) {
                throw new Exception("Serial/IMEI '{$sn}' does not exist for this product.");
            }
            if ($row['status'] !== 'in_stock') {
                throw new Exception("Serial/IMEI '{$sn}' is not available (current status: {$row['status']}).");
            }
            $upd->execute([$saleInvoiceId, (int)$row['id'], $firmId]);
        }
    }

    public static function releaseSoldSerialsForInvoice(int $firmId, int $saleInvoiceId): void {
        self::db()->prepare("
            UPDATE item_serials
            SET status = 'in_stock', sale_invoice_id = NULL, updated_at = CURRENT_TIMESTAMP
            WHERE firm_id = ? AND sale_invoice_id = ? AND status = 'sold'
        ")->execute([$firmId, $saleInvoiceId]);
    }

    public static function updateStatus(int $id, int $firmId, string $status, ?string $notes = null): ?array {
        if (!in_array($status, ['in_stock', 'sold', 'returned', 'damaged'], true)) {
            throw new Exception("Invalid serial status.");
        }
        $existing = self::getById($id, $firmId);
        if (!$existing) return null;

        self::db()->prepare("
            UPDATE item_serials
            SET status = ?, notes = COALESCE(?, notes), updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND firm_id = ?
        ")->execute([$status, $notes, $id, $firmId]);

        return self::getById($id, $firmId);
    }
}
