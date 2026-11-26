<?php
/**
 * Basic Manufacturing / Bill of Materials (BOM) & Production Order Engine
 * Consumes raw component stock atomically and credits produced finished inventory with calculated unit cost.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/Warehouse.php';

class Bom extends Model {
    public static function getBomsByFirmId(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT b.*, i.name AS finished_item_name, i.unit AS finished_item_unit,
                   i.current_stock AS finished_current_stock,
                   (SELECT COUNT(*) FROM bom_components bc WHERE bc.bom_id = b.id) AS component_count
            FROM boms b
            JOIN items i ON i.id = b.finished_item_id
            WHERE b.firm_id = ?
            ORDER BY b.bom_name ASC
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function getBomById(int $bomId, int $firmId): ?array {
        $stmt = self::db()->prepare("
            SELECT b.*, i.name AS finished_item_name, i.unit AS finished_item_unit
            FROM boms b
            JOIN items i ON i.id = b.finished_item_id
            WHERE b.id = ? AND b.firm_id = ?
            LIMIT 1
        ");
        $stmt->execute([$bomId, $firmId]);
        $bom = $stmt->fetch();
        if (!$bom) return null;

        $cStmt = self::db()->prepare("
            SELECT bc.*, i.name AS component_name, i.unit AS component_unit,
                   i.purchase_price, i.current_stock
            FROM bom_components bc
            JOIN items i ON i.id = bc.component_item_id
            WHERE bc.bom_id = ?
            ORDER BY bc.id ASC
        ");
        $cStmt->execute([$bomId]);
        $bom['components'] = $cStmt->fetchAll();
        return $bom;
    }

    public static function saveBom(int $firmId, array $data, array $components): array {
        return self::transaction(function ($db) use ($firmId, $data, $components) {
            $finishedItemId = (int)($data['finished_item_id'] ?? 0);
            $finishedItem = Item::getById($finishedItemId, $firmId);
            if (!$finishedItem) {
                throw new Exception("Finished product does not belong to this firm.");
            }
            $bomName = trim((string)($data['bom_name'] ?? ($finishedItem['name'] . ' Recipe')));
            $outputQty = max(0.01, (float)($data['output_qty'] ?? 1.0));

            if (empty($components)) {
                throw new Exception("At least one raw material component is required.");
            }

            $validComponents = [];
            foreach ($components as $c) {
                $compId = (int)($c['component_item_id'] ?? 0);
                $qty = round((float)($c['quantity_per_unit'] ?? 0), 4);
                if ($compId <= 0 || $qty <= 0) continue;
                if ($compId === $finishedItemId) {
                    throw new Exception("A finished product cannot include itself as a BOM component.");
                }
                $compItem = Item::getById($compId, $firmId);
                if (!$compItem) {
                    throw new Exception("Component item #{$compId} does not belong to this firm.");
                }
                $validComponents[] = [
                    'component_item_id' => $compId,
                    'quantity_per_unit' => $qty,
                    'unit' => $compItem['unit'] ?? 'PCS'
                ];
            }

            if (empty($validComponents)) {
                throw new Exception("Add at least one valid component with quantity > 0.");
            }

            $chk = $db->prepare("SELECT id FROM boms WHERE firm_id = ? AND finished_item_id = ? LIMIT 1");
            $chk->execute([$firmId, $finishedItemId]);
            $existingId = $chk->fetchColumn();

            if ($existingId) {
                $bomId = (int)$existingId;
                $db->prepare("UPDATE boms SET bom_name = ?, output_qty = ?, notes = ?, is_active = 1 WHERE id = ? AND firm_id = ?")
                   ->execute([$bomName, $outputQty, !empty($data['notes']) ? trim((string)$data['notes']) : null, $bomId, $firmId]);
                $db->prepare("DELETE FROM bom_components WHERE bom_id = ?")->execute([$bomId]);
            } else {
                $db->prepare("INSERT INTO boms (firm_id, finished_item_id, bom_name, output_qty, notes, is_active) VALUES (?, ?, ?, ?, ?, 1)")
                   ->execute([$firmId, $finishedItemId, $bomName, $outputQty, !empty($data['notes']) ? trim((string)$data['notes']) : null]);
                $bomId = (int)$db->lastInsertId();
            }

            $insComp = $db->prepare("INSERT INTO bom_components (bom_id, component_item_id, quantity_per_unit, unit) VALUES (?, ?, ?, ?)");
            foreach ($validComponents as $vc) {
                $insComp->execute([$bomId, $vc['component_item_id'], $vc['quantity_per_unit'], $vc['unit']]);
            }

            return self::getBomById($bomId, $firmId);
        });
    }

    public static function deleteBom(int $bomId, int $firmId): bool {
        $chk = self::db()->prepare("SELECT COUNT(*) FROM production_orders WHERE firm_id = ? AND bom_id = ?");
        $chk->execute([$firmId, $bomId]);
        if ((int)$chk->fetchColumn() > 0) {
            throw new Exception("Cannot delete BOM with recorded production orders.");
        }
        return self::db()->prepare("DELETE FROM boms WHERE id = ? AND firm_id = ?")->execute([$bomId, $firmId]);
    }

    public static function getProductionOrders(int $firmId, int $limit = 100): array {
        $limit = max(1, min(500, $limit));
        $stmt = self::db()->prepare("
            SELECT po.*, b.bom_name, i.name AS finished_item_name, i.unit AS finished_item_unit,
                   w.name AS warehouse_name
            FROM production_orders po
            JOIN boms b ON b.id = po.bom_id
            JOIN items i ON i.id = po.finished_item_id
            LEFT JOIN warehouses w ON w.id = po.warehouse_id
            WHERE po.firm_id = ?
            ORDER BY po.production_date DESC, po.id DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    /**
     * Execute manufacturing production order inside a strict DB transaction.
     */
    public static function executeProductionOrder(array $data): array {
        return self::transaction(function ($db) use ($data) {
            $firmId = (int)$data['firm_id'];
            $bomId = (int)($data['bom_id'] ?? 0);
            $bom = self::getBomById($bomId, $firmId);
            if (!$bom || empty($bom['components'])) {
                throw new Exception("Valid BOM template with components is required.");
            }

            $producedQty = round((float)($data['produced_qty'] ?? 0), 4);
            if ($producedQty <= 0) {
                throw new Exception("Produced quantity must be greater than 0.");
            }

            $warehouseId = !empty($data['warehouse_id']) ? (int)$data['warehouse_id'] : null;
            if (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE) {
                $wh = $warehouseId ? Warehouse::getById($warehouseId, $firmId) : Warehouse::ensureDefaultWarehouse($firmId);
                if (!$wh) {
                    $wh = Warehouse::ensureDefaultWarehouse($firmId);
                }
                $warehouseId = (int)$wh['id'];
            }

            $scale = $producedQty / max(0.0001, (float)$bom['output_qty']);
            $orderNumber = self::allocateFirmSequence($firmId, 'production', $data['order_number'] ?? null, 'production_orders', 'order_number');

            // 1. Validate raw material availability before mutating any stock
            $consumptions = [];
            $totalCompCost = 0.0;

            foreach ($bom['components'] as $comp) {
                $compItemId = (int)$comp['component_item_id'];
                $reqQty = round((float)$comp['quantity_per_unit'] * $scale, 4);

                if (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE && $warehouseId) {
                    $avail = Warehouse::getWarehouseItemQty($firmId, $warehouseId, $compItemId);
                } else {
                    $it = Item::getById($compItemId, $firmId);
                    $avail = $it ? (float)$it['current_stock'] : 0.0;
                }

                if ($avail + 0.0001 < $reqQty) {
                    throw new Exception("Insufficient raw material stock for \"{$comp['component_name']}\" (Available: {$avail} {$comp['component_unit']}, Required: {$reqQty}).");
                }

                $compUnitCost = max(0.0, (float)($comp['purchase_price'] ?? 0));
                $totalCompCost += ($reqQty * $compUnitCost);
                $consumptions[] = [
                    'item_id' => $compItemId,
                    'qty' => $reqQty,
                    'unit_cost' => $compUnitCost
                ];
            }

            $totalCompCost = round($totalCompCost, 2);
            $finishedUnitCost = round($totalCompCost / $producedQty, 4);
            $createdBy = !empty($data['created_by']) ? (int)$data['created_by'] : null;

            $db->prepare("
                INSERT INTO production_orders (
                    firm_id, order_number, bom_id, finished_item_id, warehouse_id,
                    production_date, produced_qty, total_component_cost, unit_cost, status, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?)
            ")->execute([
                $firmId,
                $orderNumber,
                $bomId,
                (int)$bom['finished_item_id'],
                $warehouseId,
                !empty($data['production_date']) ? $data['production_date'] : date('Y-m-d'),
                $producedQty,
                $totalCompCost,
                round($finishedUnitCost, 2),
                !empty($data['notes']) ? trim((string)$data['notes']) : null,
                $createdBy
            ]);
            $orderId = (int)$db->lastInsertId();

            // 2. Consume raw components & credit finished good
            foreach ($consumptions as $con) {
                if (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE) {
                    Warehouse::adjustWarehouseStock(
                        $firmId,
                        $warehouseId,
                        $con['item_id'],
                        -$con['qty'],
                        'production_out',
                        $con['unit_cost'],
                        'production_order',
                        $orderId,
                        "Consumed for Production Order #{$orderNumber}",
                        $createdBy
                    );
                } else {
                    Item::adjustStock($con['item_id'], $firmId, -$con['qty']);
                }
            }

            if (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE) {
                Warehouse::adjustWarehouseStock(
                    $firmId,
                    $warehouseId,
                    (int)$bom['finished_item_id'],
                    $producedQty,
                    'production_in',
                    $finishedUnitCost,
                    'production_order',
                    $orderId,
                    "Produced via BOM Order #{$orderNumber}",
                    $createdBy
                );
            } else {
                Item::adjustStock((int)$bom['finished_item_id'], $firmId, $producedQty);
            }

            $stmt = $db->prepare("SELECT * FROM production_orders WHERE id = ? AND firm_id = ? LIMIT 1");
            $stmt->execute([$orderId, $firmId]);
            return $stmt->fetch();
        });
    }
}
