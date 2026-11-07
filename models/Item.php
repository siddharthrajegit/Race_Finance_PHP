<?php
/**
 * Item / Inventory Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class Item extends Model {
    public static function getByFirmId(int $firmId): array {
        $stmt = self::db()->prepare("SELECT * FROM items WHERE firm_id = ? ORDER BY name ASC");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function getCategories(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT DISTINCT category 
            FROM items 
            WHERE firm_id = ? AND category IS NOT NULL AND TRIM(category) != '' 
            ORDER BY category ASC
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public static function getByCategory(int $firmId, ?string $category = null): array {
        if ($category === null || $category === 'all') {
            return self::getByFirmId($firmId);
        }
        if ($category === '' || $category === '__none__') {
            $stmt = self::db()->prepare("SELECT * FROM items WHERE firm_id = ? AND (category IS NULL OR TRIM(category) = '') ORDER BY name ASC");
            $stmt->execute([$firmId]);
            return $stmt->fetchAll();
        }
        $stmt = self::db()->prepare("SELECT * FROM items WHERE firm_id = ? AND category = ? ORDER BY name ASC");
        $stmt->execute([$firmId, $category]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM items WHERE id = ? AND firm_id = ?");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function getByName(string $name, int $firmId): ?array {
        if (trim($name) === '') return null;
        $stmt = self::db()->prepare("SELECT * FROM items WHERE LOWER(name) = LOWER(?) AND firm_id = ?");
        $stmt->execute([trim($name), $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function getLowStock(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT * FROM items 
            WHERE firm_id = ? AND current_stock <= low_stock_threshold 
            ORDER BY current_stock ASC
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): ?array {
        $firmId = (int)$data['firm_id'];
        $initialStock = Security::parseCleanFloat($data['opening_stock'] ?? 0);
        $category = !empty($data['category']) ? trim($data['category']) : null;
        $subUnit = !empty($data['sub_unit']) ? strtoupper(trim($data['sub_unit'])) : null;
        $subUnitQty = isset($data['sub_unit_qty']) && $data['sub_unit_qty'] !== '' && (float)$data['sub_unit_qty'] > 0 
            ? Security::parseCleanFloat($data['sub_unit_qty']) 
            : null;

        $stmt = self::db()->prepare("
            INSERT INTO items (
                firm_id, name, category, item_code, hsn_code, unit, sub_unit, sub_unit_qty,
                sale_price, purchase_price, tax_rate, tax_inclusive,
                opening_stock, current_stock, low_stock_threshold, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $firmId,
            trim($data['name']),
            $category,
            !empty($data['item_code']) ? trim($data['item_code']) : null,
            !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            !empty($data['unit']) ? strtoupper(trim($data['unit'])) : 'PCS',
            $subUnit,
            $subUnitQty,
            max(0.0, Security::parseCleanFloat($data['sale_price'] ?? 0)),
            max(0.0, Security::parseCleanFloat($data['purchase_price'] ?? 0)),
            min(100.0, max(0.0, Security::parseCleanFloat($data['tax_rate'] ?? 0))),
            !empty($data['tax_inclusive']) ? 1 : 0,
            $initialStock,
            $initialStock,
            max(0.0, Security::parseCleanFloat($data['low_stock_threshold'] ?? 5)),
            !empty($data['description']) ? trim($data['description']) : null
        ]);

        $id = (int)self::db()->lastInsertId();
        return self::getById($id, $firmId);
    }

    public static function update(int $id, int $firmId, array $data): ?array {
        $category = !empty($data['category']) ? trim($data['category']) : null;
        $subUnit = !empty($data['sub_unit']) ? strtoupper(trim($data['sub_unit'])) : null;
        $subUnitQty = isset($data['sub_unit_qty']) && $data['sub_unit_qty'] !== '' && (float)$data['sub_unit_qty'] > 0 
            ? Security::parseCleanFloat($data['sub_unit_qty']) 
            : null;

        $stmt = self::db()->prepare("
            UPDATE items SET
                name = ?, category = ?, item_code = ?, hsn_code = ?, unit = ?,
                sub_unit = ?, sub_unit_qty = ?, sale_price = ?,
                purchase_price = ?, tax_rate = ?, tax_inclusive = ?,
                low_stock_threshold = ?, description = ?
            WHERE id = ? AND firm_id = ?
        ");

        $stmt->execute([
            trim($data['name']),
            $category,
            !empty($data['item_code']) ? trim($data['item_code']) : null,
            !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            !empty($data['unit']) ? strtoupper(trim($data['unit'])) : 'PCS',
            $subUnit,
            $subUnitQty,
            max(0.0, Security::parseCleanFloat($data['sale_price'] ?? 0)),
            max(0.0, Security::parseCleanFloat($data['purchase_price'] ?? 0)),
            min(100.0, max(0.0, Security::parseCleanFloat($data['tax_rate'] ?? 0))),
            !empty($data['tax_inclusive']) ? 1 : 0,
            max(0.0, Security::parseCleanFloat($data['low_stock_threshold'] ?? 5)),
            !empty($data['description']) ? trim($data['description']) : null,
            $id,
            $firmId
        ]);

        return self::getById($id, $firmId);
    }

    public static function adjustStock(int $id, int $firmId, float $adjustment): ?array {
        if ($adjustment == 0) {
            return self::getById($id, $firmId);
        }
        $stmt = self::db()->prepare("
            UPDATE items 
            SET current_stock = current_stock + ? 
            WHERE id = ? AND firm_id = ?
        ");
        $stmt->execute([$adjustment, $id, $firmId]);
        return self::getById($id, $firmId);
    }

    public static function adjustInvoiceItemStock(?int $itemId, int $firmId, string $invoiceType, float $quantity, int $direction = 1): void {
        if (!$itemId || $quantity <= 0) return;

        $typeMultiplier = ($invoiceType === 'sale') ? -1 : (($invoiceType === 'purchase') ? 1 : 0);
        if ($typeMultiplier === 0) return;

        $delta = $typeMultiplier * $direction * $quantity;
        $stmt = self::db()->prepare("UPDATE items SET current_stock = current_stock + ? WHERE id = ? AND firm_id = ?");
        $stmt->execute([$delta, $itemId, $firmId]);
    }

    public static function delete(int $id, int $firmId): bool {
        $stmt = self::db()->prepare("DELETE FROM items WHERE id = ? AND firm_id = ?");
        return $stmt->execute([$id, $firmId]);
    }
}
