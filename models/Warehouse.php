<?php
/**
 * Multi-Warehouse / Godown Model & Auditable Stock Valuation Engine (FIFO & Weighted Average)
 * Maintains firm-scoped warehouse locations, per-location stock balances, and inventory cost layers.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Setting.php';

class Warehouse extends Model {
    /**
     * Ensure every firm has at least one default warehouse ("Main Warehouse")
     * and synchronize any items missing warehouse_stock rows.
     */
    public static function ensureDefaultWarehouse(int $firmId): array {
        $stmt = self::db()->prepare("SELECT * FROM warehouses WHERE firm_id = ? ORDER BY is_default DESC, id ASC LIMIT 1");
        $stmt->execute([$firmId]);
        $wh = $stmt->fetch();

        if (!$wh) {
            $ins = self::db()->prepare("
                INSERT INTO warehouses (firm_id, name, code, address, is_default, is_active)
                VALUES (?, 'Main Warehouse', 'MAIN', NULL, 1, 1)
            ");
            $ins->execute([$firmId]);
            $id = (int)self::db()->lastInsertId();
            $wh = self::getById($id, $firmId);
        }

        // Seed any existing items that do not have warehouse_stock entries yet
        $whId = (int)$wh['id'];
        $missingStmt = self::db()->prepare("
            SELECT i.id, i.current_stock, i.purchase_price
            FROM items i
            WHERE i.firm_id = ?
              AND NOT EXISTS (SELECT 1 FROM warehouse_stock ws WHERE ws.item_id = i.id AND ws.firm_id = ?)
        ");
        $missingStmt->execute([$firmId, $firmId]);
        $missingItems = $missingStmt->fetchAll();

        if (!empty($missingItems)) {
            $wsIns = self::db()->prepare("
                INSERT INTO warehouse_stock (firm_id, warehouse_id, item_id, quantity)
                VALUES (?, ?, ?, ?)
            ");
            $mvCheck = self::db()->prepare("SELECT COUNT(*) FROM stock_movements WHERE firm_id = ? AND item_id = ?");
            $mvIns = self::db()->prepare("
                INSERT INTO stock_movements (firm_id, warehouse_id, item_id, movement_type, quantity_delta, unit_cost, total_cost, remaining_qty, source_type, notes)
                VALUES (?, ?, ?, 'opening', ?, ?, ?, ?, 'opening_seed', 'Initial warehouse stock migration')
            ");

            foreach ($missingItems as $it) {
                $qty = (float)$it['current_stock'];
                $cost = max(0.0, (float)($it['purchase_price'] ?? 0));
                $wsIns->execute([$firmId, $whId, (int)$it['id'], $qty]);

                $mvCheck->execute([$firmId, (int)$it['id']]);
                if ((int)$mvCheck->fetchColumn() === 0 && $qty > 0) {
                    $mvIns->execute([$firmId, $whId, (int)$it['id'], $qty, $cost, $qty * $cost, $qty]);
                }
            }
        }

        return $wh;
    }

    public static function getByFirmId(int $firmId, bool $activeOnly = false): array {
        self::ensureDefaultWarehouse($firmId);
        $sql = "
            SELECT w.*,
                   COALESCE((SELECT COUNT(*) FROM warehouse_stock ws WHERE ws.warehouse_id = w.id AND ws.quantity != 0), 0) AS active_items_count,
                   COALESCE((SELECT SUM(ws.quantity) FROM warehouse_stock ws WHERE ws.warehouse_id = w.id), 0) AS total_units
            FROM warehouses w
            WHERE w.firm_id = ?
        ";
        if ($activeOnly) {
            $sql .= " AND w.is_active = 1";
        }
        $sql .= " ORDER BY w.is_default DESC, w.name ASC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM warehouses WHERE id = ? AND firm_id = ?");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function getDefault(int $firmId): array {
        return self::ensureDefaultWarehouse($firmId);
    }

    public static function create(array $data): array {
        return self::transaction(function ($db) use ($data) {
            $firmId = (int)$data['firm_id'];
            self::ensureDefaultWarehouse($firmId);

            $name = trim((string)($data['name'] ?? ''));
            if ($name === '') {
                throw new Exception("Warehouse / godown name is required.");
            }
            $code = !empty($data['code']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($data['code'])), 0, 20)) : null;
            $address = !empty($data['address']) ? trim((string)$data['address']) : null;
            $isDefault = !empty($data['is_default']) ? 1 : 0;

            if ($isDefault) {
                $db->prepare("UPDATE warehouses SET is_default = 0 WHERE firm_id = ?")->execute([$firmId]);
            }

            $stmt = $db->prepare("
                INSERT INTO warehouses (firm_id, name, code, address, is_default, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$firmId, $name, $code, $address, $isDefault]);
            $id = (int)$db->lastInsertId();
            return self::getById($id, $firmId);
        });
    }

    public static function update(int $id, int $firmId, array $data): ?array {
        return self::transaction(function ($db) use ($id, $firmId, $data) {
            $existing = self::getById($id, $firmId);
            if (!$existing) return null;

            $name = trim((string)($data['name'] ?? $existing['name']));
            if ($name === '') {
                throw new Exception("Warehouse name cannot be empty.");
            }
            $code = !empty($data['code']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($data['code'])), 0, 20)) : null;
            $address = isset($data['address']) ? trim((string)$data['address']) : $existing['address'];
            $isDefault = !empty($data['is_default']) ? 1 : (int)$existing['is_default'];
            $isActive = array_key_exists('is_active', $data) ? (!empty($data['is_active']) ? 1 : 0) : (int)$existing['is_active'];

            if ($isDefault) {
                $db->prepare("UPDATE warehouses SET is_default = 0 WHERE firm_id = ?")->execute([$firmId]);
                $isActive = 1; // Default warehouse must remain active
            }

            $db->prepare("
                UPDATE warehouses SET name = ?, code = ?, address = ?, is_default = ?, is_active = ?
                WHERE id = ? AND firm_id = ?
            ")->execute([$name, $code, $address, $isDefault, $isActive, $id, $firmId]);

            return self::getById($id, $firmId);
        });
    }

    public static function delete(int $id, int $firmId): bool {
        $wh = self::getById($id, $firmId);
        if (!$wh) return false;
        if (!empty($wh['is_default'])) {
            throw new Exception("Cannot delete the default warehouse. Set another warehouse as default first.");
        }

        $chk = self::db()->prepare("SELECT COALESCE(SUM(ABS(quantity)), 0) FROM warehouse_stock WHERE warehouse_id = ? AND firm_id = ?");
        $chk->execute([$id, $firmId]);
        if ((float)$chk->fetchColumn() > 0.001) {
            throw new Exception("Cannot delete a warehouse that still holds stock. Transfer stock out first.");
        }

        return self::db()->prepare("DELETE FROM warehouses WHERE id = ? AND firm_id = ?")->execute([$id, $firmId]);
    }

    public static function getItemWarehouseBreakdown(int $itemId, int $firmId): array {
        self::ensureDefaultWarehouse($firmId);
        $stmt = self::db()->prepare("
            SELECT w.id AS warehouse_id, w.name AS warehouse_name, w.code AS warehouse_code, w.is_default,
                   COALESCE(ws.quantity, 0) AS quantity
            FROM warehouses w
            LEFT JOIN warehouse_stock ws ON ws.warehouse_id = w.id AND ws.item_id = ? AND ws.firm_id = ?
            WHERE w.firm_id = ?
            ORDER BY w.is_default DESC, w.name ASC
        ");
        $stmt->execute([$itemId, $firmId, $firmId]);
        return $stmt->fetchAll();
    }

    public static function getStockByWarehouse(int $firmId, int $warehouseId): array {
        self::ensureDefaultWarehouse($firmId);
        $stmt = self::db()->prepare("
            SELECT i.id, i.name, i.item_code, i.unit, i.category, i.sale_price, i.purchase_price,
                   i.current_stock AS total_stock,
                   COALESCE(ws.quantity, 0) AS warehouse_qty
            FROM items i
            LEFT JOIN warehouse_stock ws ON ws.item_id = i.id AND ws.warehouse_id = ? AND ws.firm_id = ?
            WHERE i.firm_id = ?
            ORDER BY i.name ASC
        ");
        $stmt->execute([$warehouseId, $firmId, $firmId]);
        return $stmt->fetchAll();
    }

    public static function getWarehouseItemQty(int $firmId, int $warehouseId, int $itemId): float {
        $stmt = self::db()->prepare("SELECT quantity FROM warehouse_stock WHERE firm_id = ? AND warehouse_id = ? AND item_id = ?");
        $stmt->execute([$firmId, $warehouseId, $itemId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (float)$val : 0.0;
    }

    /**
     * Adjust warehouse stock, reconcile items.current_stock, and record FIFO/Weighted-Avg cost movement.
     */
    public static function adjustWarehouseStock(
        int $firmId,
        ?int $warehouseId,
        int $itemId,
        float $delta,
        string $movementType,
        ?float $unitCost = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $notes = null,
        ?int $createdBy = null,
        ?int $batchId = null
    ): void {
        if ($itemId <= 0 || abs($delta) < 0.00001) {
            return;
        }

        $wh = $warehouseId ? self::getById($warehouseId, $firmId) : null;
        if (!$wh) {
            $wh = self::ensureDefaultWarehouse($firmId);
        }
        $targetWhId = (int)$wh['id'];

        $db = self::db();

        // Ensure warehouse_stock row exists & update quantity
        if (DB::isMysql()) {
            $db->prepare("
                INSERT INTO warehouse_stock (firm_id, warehouse_id, item_id, quantity)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ")->execute([$firmId, $targetWhId, $itemId, $delta]);
        } else {
            $db->prepare("
                INSERT INTO warehouse_stock (firm_id, warehouse_id, item_id, quantity)
                VALUES (?, ?, ?, ?)
                ON CONFLICT(warehouse_id, item_id) DO UPDATE SET
                    quantity = warehouse_stock.quantity + excluded.quantity,
                    updated_at = CURRENT_TIMESTAMP
            ")->execute([$firmId, $targetWhId, $itemId, $delta]);
        }

        // Reconcile items.current_stock = sum across all warehouses
        $sumStmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM warehouse_stock WHERE firm_id = ? AND item_id = ?");
        $sumStmt->execute([$firmId, $itemId]);
        $totalQty = (float)$sumStmt->fetchColumn();
        $db->prepare("UPDATE items SET current_stock = ? WHERE id = ? AND firm_id = ?")
           ->execute([$totalQty, $itemId, $firmId]);

        // Resolve unit cost for movement logging and FIFO layer tracking
        if ($unitCost === null) {
            $costStmt = $db->prepare("SELECT purchase_price FROM items WHERE id = ? AND firm_id = ?");
            $costStmt->execute([$itemId, $firmId]);
            $unitCost = max(0.0, (float)($costStmt->fetchColumn() ?: 0.0));
        }

        $effectiveUnitCost = max(0.0, $unitCost);
        $remainingLayerQty = 0.0;
        $computedOutCost = $effectiveUnitCost * abs($delta);

        if ($delta > 0) {
            // Positive stock inflow creates an auditable FIFO cost layer
            $remainingLayerQty = $delta;
        } else {
            // Negative stock outflow consumes positive FIFO layers chronologically
            $need = abs($delta);
            $layerStmt = $db->prepare("
                SELECT id, remaining_qty, unit_cost
                FROM stock_movements
                WHERE firm_id = ? AND item_id = ? AND remaining_qty > 0
                ORDER BY id ASC
            ");
            $layerStmt->execute([$firmId, $itemId]);
            $layers = $layerStmt->fetchAll();

            if (!empty($layers)) {
                $computedOutCost = 0.0;
                $updLayer = $db->prepare("UPDATE stock_movements SET remaining_qty = ? WHERE id = ?");
                foreach ($layers as $layer) {
                    if ($need <= 0.00001) break;
                    $avail = (float)$layer['remaining_qty'];
                    $take = min($need, $avail);
                    $updLayer->execute([$avail - $take, (int)$layer['id']]);
                    $computedOutCost += $take * (float)$layer['unit_cost'];
                    $need -= $take;
                }
                if ($need > 0.00001) {
                    // Fallback to item purchase price for any uncovered negative stock
                    $computedOutCost += $need * $effectiveUnitCost;
                }
                $effectiveUnitCost = abs($delta) > 0 ? ($computedOutCost / abs($delta)) : $effectiveUnitCost;
            }
        }

        $mvStmt = $db->prepare("
            INSERT INTO stock_movements (
                firm_id, warehouse_id, item_id, movement_type, quantity_delta,
                unit_cost, total_cost, remaining_qty, source_type, source_id,
                batch_id, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $mvStmt->execute([
            $firmId,
            $targetWhId,
            $itemId,
            $movementType,
            $delta,
            round($effectiveUnitCost, 4),
            round($computedOutCost, 2),
            round($remainingLayerQty, 4),
            $sourceType,
            $sourceId,
            $batchId,
            $notes,
            $createdBy
        ]);
    }

    /**
     * Compute stock valuation across all firm items using 'fifo' or 'weighted_avg'.
     */
    public static function getStockValuation(int $firmId, ?string $method = null): array {
        self::ensureDefaultWarehouse($firmId);
        if ($method === null) {
            $settings = Setting::get($firmId);
            $method = $settings['general']['stock_valuation_method'] ?? 'fifo';
        }
        $method = in_array($method, ['fifo', 'weighted_avg'], true) ? $method : 'fifo';

        $itemsStmt = self::db()->prepare("SELECT id, name, item_code, unit, category, purchase_price, sale_price, current_stock FROM items WHERE firm_id = ? ORDER BY name ASC");
        $itemsStmt->execute([$firmId]);
        $items = $itemsStmt->fetchAll();

        $layerStmt = self::db()->prepare("
            SELECT remaining_qty, unit_cost, quantity_delta
            FROM stock_movements
            WHERE firm_id = ? AND item_id = ? AND quantity_delta > 0
            ORDER BY id ASC
        ");

        $rows = [];
        $totalQty = 0.0;
        $totalValue = 0.0;
        $totalSalePotential = 0.0;

        foreach ($items as $it) {
            $qty = max(0.0, (float)$it['current_stock']);
            $fallbackCost = max(0.0, (float)$it['purchase_price']);
            $salePrice = max(0.0, (float)$it['sale_price']);

            $layerStmt->execute([$firmId, (int)$it['id']]);
            $layers = $layerStmt->fetchAll();

            $val = 0.0;
            $avgCost = $fallbackCost;

            if ($qty > 0) {
                if ($method === 'fifo') {
                    $covered = 0.0;
                    foreach ($layers as $l) {
                        $rem = (float)$l['remaining_qty'];
                        if ($rem <= 0) continue;
                        $take = min($qty - $covered, $rem);
                        if ($take <= 0) break;
                        $val += $take * (float)$l['unit_cost'];
                        $covered += $take;
                    }
                    if ($covered < $qty) {
                        $val += ($qty - $covered) * $fallbackCost;
                    }
                    $avgCost = $val / $qty;
                } else {
                    // Weighted Average from all positive stock inflows
                    $sumQty = 0.0;
                    $sumCost = 0.0;
                    foreach ($layers as $l) {
                        $q = (float)$l['quantity_delta'];
                        $sumQty += $q;
                        $sumCost += $q * (float)$l['unit_cost'];
                    }
                    $avgCost = ($sumQty > 0) ? ($sumCost / $sumQty) : $fallbackCost;
                    $val = $qty * $avgCost;
                }
            }

            $salePotential = $qty * $salePrice;
            $totalQty += $qty;
            $totalValue += $val;
            $totalSalePotential += $salePotential;

            $rows[] = [
                'item_id' => (int)$it['id'],
                'name' => $it['name'],
                'item_code' => $it['item_code'],
                'category' => $it['category'],
                'unit' => $it['unit'],
                'on_hand_qty' => round($qty, 2),
                'unit_cost' => round($avgCost, 2),
                'stock_value' => round($val, 2),
                'sale_potential' => round($salePotential, 2),
                'estimated_margin' => round($salePotential - $val, 2)
            ];
        }

        return [
            'method' => $method,
            'items' => $rows,
            'total_qty' => round($totalQty, 2),
            'total_value' => round($totalValue, 2),
            'total_sale_potential' => round($totalSalePotential, 2)
        ];
    }
}
