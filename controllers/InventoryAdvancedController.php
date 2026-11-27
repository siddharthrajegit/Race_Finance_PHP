<?php
/**
 * Advanced Inventory Controller: Batch / Expiry (FEFO), Serial / IMEI, and Manufacturing BOM
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Batch.php';
require_once __DIR__ . '/../models/SerialNumber.php';
require_once __DIR__ . '/../models/Bom.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Warehouse.php';
require_once __DIR__ . '/../models/AuditLog.php';

class InventoryAdvancedController {
    // -------------------------------------------------------------------------
    // Batches & Expiry / FEFO
    // -------------------------------------------------------------------------
    public function listBatches(): void {
        if (!defined('ENABLE_BATCH_EXPIRY') || !ENABLE_BATCH_EXPIRY) {
            Flash::set('info_msg', 'Batch & Expiry tracking is disabled.');
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('view_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $itemId = !empty($_GET['item_id']) ? (int)$_GET['item_id'] : null;
        $batches = Batch::getByFirmId($firmId, $itemId);
        $expiring = Batch::getExpiringAlerts($firmId, 30);
        $items = Item::getByFirmId($firmId);
        $warehouses = Warehouse::getByFirmId($firmId, true);

        view('batches/list', [
            'title' => 'Batches & Expiry (FEFO)',
            'batches' => $batches,
            'expiring' => $expiring,
            'items' => $items,
            'warehouses' => $warehouses,
            'selectedItemId' => $itemId,
            'activeMenu' => 'batches'
        ]);
    }

    public function postCreateBatch(): void {
        if (!defined('ENABLE_BATCH_EXPIRY') || !ENABLE_BATCH_EXPIRY) {
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $itemId = (int)($_POST['item_id'] ?? 0);
            $item = Item::getById($itemId, $firmId);
            if (!$item) throw new Exception('Selected product not found.');

            $whId = !empty($_POST['warehouse_id']) ? (int)$_POST['warehouse_id'] : null;
            $qty = Security::parseCleanFloat($_POST['qty'] ?? 0);
            $batch = Batch::upsertBatch(
                $firmId,
                $itemId,
                $whId,
                (string)($_POST['batch_number'] ?? ''),
                $qty,
                !empty($_POST['mfg_date']) ? $_POST['mfg_date'] : null,
                !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
                Security::parseCleanFloat($_POST['purchase_rate'] ?? $item['purchase_price'])
            );

            if (!empty($_POST['sync_stock'])) {
                Item::adjustStock($itemId, $firmId, $qty, $whId, "Batch #{$batch['batch_number']} added");
            }

            AuditLog::log($firmId, 'CREATE', 'item_batch', $batch['id'], "Batch #{$batch['batch_number']} for {$item['name']} qty={$qty}");
            Flash::set('success_msg', "Batch \"{$batch['batch_number']}\" saved!");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save batch.'));
        }
        header('Location: /batches');
        exit;
    }

    // -------------------------------------------------------------------------
    // Serial Numbers & IMEI Tracking
    // -------------------------------------------------------------------------
    public function listSerials(): void {
        if (!defined('ENABLE_SERIAL_TRACKING') || !ENABLE_SERIAL_TRACKING) {
            Flash::set('info_msg', 'Serial / IMEI tracking is disabled.');
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('view_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $itemId = !empty($_GET['item_id']) ? (int)$_GET['item_id'] : null;
        $status = trim((string)($_GET['status'] ?? ''));

        $serials = SerialNumber::getByFirmId($firmId, $itemId, $status ?: null);
        $items = Item::getByFirmId($firmId);
        $warehouses = Warehouse::getByFirmId($firmId, true);

        view('serials/list', [
            'title' => 'Serial Numbers & IMEI Register',
            'serials' => $serials,
            'items' => $items,
            'warehouses' => $warehouses,
            'selectedItemId' => $itemId,
            'selectedStatus' => $status,
            'activeMenu' => 'serials'
        ]);
    }

    public function postRegisterSerials(): void {
        if (!defined('ENABLE_SERIAL_TRACKING') || !ENABLE_SERIAL_TRACKING) {
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $itemId = (int)($_POST['item_id'] ?? 0);
            $item = Item::getById($itemId, $firmId);
            if (!$item) throw new Exception('Selected product not found.');

            $serialList = SerialNumber::parseSerialInput((string)($_POST['serial_numbers'] ?? ''));
            if (empty($serialList)) throw new Exception('Enter at least one serial number or IMEI.');

            $whId = !empty($_POST['warehouse_id']) ? (int)$_POST['warehouse_id'] : null;
            $created = SerialNumber::registerSerials($firmId, $itemId, $whId, $serialList, null, trim((string)($_POST['notes'] ?? '')) ?: null);

            AuditLog::log($firmId, 'CREATE', 'item_serials', $itemId, "Registered " . count($created) . " serial(s) for {$item['name']}");
            Flash::set('success_msg', count($created) . " Serial/IMEI number(s) registered!");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to register serial numbers.'));
        }
        header('Location: /serials');
        exit;
    }

    public function postUpdateSerialStatus(): void {
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        try {
            $id = (int)($_POST['serial_id'] ?? 0);
            $status = trim((string)($_POST['status'] ?? 'in_stock'));
            SerialNumber::updateStatus($id, (int)$firm['id'], $status);
            Flash::set('success_msg', 'Serial status updated.');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update serial status.'));
        }
        header('Location: /serials');
        exit;
    }

    // -------------------------------------------------------------------------
    // Basic Manufacturing / BOM & Production Orders
    // -------------------------------------------------------------------------
    public function listBoms(): void {
        if (!defined('ENABLE_BOM') || !ENABLE_BOM) {
            Flash::set('info_msg', 'Manufacturing / BOM module is disabled.');
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('manage_bom');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $boms = Bom::getBomsByFirmId($firmId);
        $orders = Bom::getProductionOrders($firmId, 50);
        $items = Item::getByFirmId($firmId);
        $warehouses = Warehouse::getByFirmId($firmId, true);

        view('bom/list', [
            'title' => 'Manufacturing / Bill of Materials (BOM)',
            'boms' => $boms,
            'orders' => $orders,
            'items' => $items,
            'warehouses' => $warehouses,
            'activeMenu' => 'bom'
        ]);
    }

    public function postSaveBom(): void {
        if (!defined('ENABLE_BOM') || !ENABLE_BOM) {
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('manage_bom');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $compIds = $_POST['component_item_id'] ?? [];
            $qtys = $_POST['quantity_per_unit'] ?? [];
            $components = [];
            if (is_array($compIds)) {
                foreach ($compIds as $idx => $cid) {
                    $cId = (int)$cid;
                    $q = Security::parseCleanFloat($qtys[$idx] ?? 0);
                    if ($cId > 0 && $q > 0) {
                        $components[] = ['component_item_id' => $cId, 'quantity_per_unit' => $q];
                    }
                }
            }

            $bom = Bom::saveBom($firmId, [
                'finished_item_id' => (int)($_POST['finished_item_id'] ?? 0),
                'bom_name' => $_POST['bom_name'] ?? '',
                'output_qty' => Security::parseCleanFloat($_POST['output_qty'] ?? 1),
                'notes' => $_POST['notes'] ?? null
            ], $components);

            AuditLog::log($firmId, 'SAVE_BOM', 'bom', $bom['id'], "Saved BOM {$bom['bom_name']}");
            Flash::set('success_msg', "BOM \"{$bom['bom_name']}\" saved!");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save BOM.'));
        }
        header('Location: /bom');
        exit;
    }

    public function postDeleteBom(array $params): void {
        RBAC::requirePermission('manage_bom');
        $firm = Auth::requireActiveFirm();
        try {
            Bom::deleteBom((int)($params['id'] ?? 0), (int)$firm['id']);
            Flash::set('success_msg', 'BOM deleted.');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to delete BOM.'));
        }
        header('Location: /bom');
        exit;
    }

    public function postExecuteProduction(): void {
        if (!defined('ENABLE_BOM') || !ENABLE_BOM) {
            header('Location: /items');
            exit;
        }
        RBAC::requirePermission('manage_bom');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $order = Bom::executeProductionOrder([
                'firm_id' => $firmId,
                'bom_id' => (int)($_POST['bom_id'] ?? 0),
                'warehouse_id' => !empty($_POST['warehouse_id']) ? (int)$_POST['warehouse_id'] : null,
                'production_date' => $_POST['production_date'] ?? date('Y-m-d'),
                'produced_qty' => Security::parseCleanFloat($_POST['produced_qty'] ?? 0),
                'notes' => $_POST['notes'] ?? null,
                'created_by' => $user ? (int)$user['id'] : null
            ]);
            AuditLog::log($firmId, 'PRODUCTION', 'production_order', $order['id'], "Produced qty={$order['produced_qty']} Order #{$order['order_number']}");
            Flash::set('success_msg', "Production Order #{$order['order_number']} completed! Stock updated.");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Production order failed.'));
        }
        header('Location: /bom');
        exit;
    }
}
