<?php
/**
 * Warehouse, Godown, Stock Transfer & Valuation Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Warehouse.php';
require_once __DIR__ . '/../models/StockTransfer.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/Setting.php';

class WarehouseController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_WAREHOUSE') || !ENABLE_WAREHOUSE) {
            Flash::set('info_msg', 'Multi-warehouse feature is currently disabled in configuration.');
            header('Location: /items');
            exit;
        }
    }

    public function listWarehouses(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_warehouses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $warehouses = Warehouse::getByFirmId($firmId);
        $selectedWhId = !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : (int)($warehouses[0]['id'] ?? 0);
        $selectedWarehouse = $selectedWhId > 0 ? Warehouse::getById($selectedWhId, $firmId) : null;
        $stockRows = $selectedWarehouse ? Warehouse::getStockByWarehouse($firmId, (int)$selectedWarehouse['id']) : [];

        view('warehouses/list', [
            'title' => 'Warehouses & Godowns',
            'warehouses' => $warehouses,
            'selectedWarehouse' => $selectedWarehouse,
            'stockRows' => $stockRows,
            'activeMenu' => 'warehouses'
        ]);
    }

    public function getCreate(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_warehouses');
        Auth::requireActiveFirm();

        view('warehouses/form', [
            'title' => 'Add Warehouse / Godown',
            'warehouse' => null,
            'activeMenu' => 'warehouses'
        ]);
    }

    public function postCreate(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_warehouses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $wh = Warehouse::create([
                'firm_id' => $firmId,
                'name' => $_POST['name'] ?? '',
                'code' => $_POST['code'] ?? '',
                'address' => $_POST['address'] ?? '',
                'is_default' => !empty($_POST['is_default'])
            ]);
            AuditLog::log($firmId, 'CREATE', 'warehouse', $wh['id'], "Created warehouse {$wh['name']}");
            Flash::set('success_msg', "Warehouse \"{$wh['name']}\" added successfully!");
            header('Location: /warehouses');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to create warehouse.'));
            header('Location: /warehouses/create');
            exit;
        }
    }

    public function getEdit(array $params): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_warehouses');
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $warehouse = Warehouse::getById($id, (int)$firm['id']);
        if (!$warehouse) {
            Flash::set('error_msg', 'Warehouse not found.');
            header('Location: /warehouses');
            exit;
        }

        view('warehouses/form', [
            'title' => 'Edit Warehouse / Godown',
            'warehouse' => $warehouse,
            'activeMenu' => 'warehouses'
        ]);
    }

    public function postEdit(array $params): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_warehouses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $id = (int)($params['id'] ?? 0);

        try {
            $wh = Warehouse::update($id, $firmId, [
                'name' => $_POST['name'] ?? '',
                'code' => $_POST['code'] ?? '',
                'address' => $_POST['address'] ?? '',
                'is_default' => !empty($_POST['is_default']),
                'is_active' => !empty($_POST['is_active'])
            ]);
            if (!$wh) {
                Flash::set('error_msg', 'Warehouse not found.');
                header('Location: /warehouses');
                exit;
            }
            AuditLog::log($firmId, 'UPDATE', 'warehouse', $id, "Updated warehouse {$wh['name']}");
            Flash::set('success_msg', "Warehouse \"{$wh['name']}\" updated.");
            header('Location: /warehouses');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update warehouse.'));
            header("Location: /warehouses/edit/{$id}");
            exit;
        }
    }

    public function postDelete(array $params): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_warehouses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $id = (int)($params['id'] ?? 0);

        try {
            Warehouse::delete($id, $firmId);
            AuditLog::log($firmId, 'DELETE', 'warehouse', $id, "Deleted warehouse #{$id}");
            Flash::set('success_msg', 'Warehouse deleted successfully.');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Could not delete warehouse.'));
        }
        header('Location: /warehouses');
        exit;
    }

    public function listTransfers(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_warehouses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $transfers = StockTransfer::getByFirmId($firmId, 100);

        view('warehouses/transfers', [
            'title' => 'Inter-Warehouse Stock Transfers',
            'transfers' => $transfers,
            'activeMenu' => 'warehouses'
        ]);
    }

    public function getCreateTransfer(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('transfer_stock');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $warehouses = Warehouse::getByFirmId($firmId, true);
        if (count($warehouses) < 2) {
            Flash::set('info_msg', 'Create at least 2 active warehouses before recording a stock transfer.');
            header('Location: /warehouses/create');
            exit;
        }

        $items = Item::getByFirmId($firmId);
        $nextNum = StockTransfer::getNextTransferNumber($firmId);

        view('warehouses/transfer_form', [
            'title' => 'New Stock Transfer',
            'warehouses' => $warehouses,
            'items' => $items,
            'nextTransferNumber' => $nextNum,
            'activeMenu' => 'warehouses'
        ]);
    }

    public function postCreateTransfer(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('transfer_stock');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $itemIds = $_POST['item_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $itemsData = [];
            if (is_array($itemIds)) {
                foreach ($itemIds as $idx => $rawItemId) {
                    $itemId = (int)$rawItemId;
                    $qty = Security::parseCleanFloat($quantities[$idx] ?? 0);
                    if ($itemId > 0 && $qty > 0) {
                        $itemsData[] = ['item_id' => $itemId, 'quantity' => $qty];
                    }
                }
            }

            $transfer = StockTransfer::create([
                'firm_id' => $firmId,
                'transfer_number' => $_POST['transfer_number'] ?? null,
                'transfer_date' => $_POST['transfer_date'] ?? date('Y-m-d'),
                'source_warehouse_id' => (int)($_POST['source_warehouse_id'] ?? 0),
                'dest_warehouse_id' => (int)($_POST['dest_warehouse_id'] ?? 0),
                'notes' => $_POST['notes'] ?? null,
                'created_by' => $user ? (int)$user['id'] : null
            ], $itemsData);

            AuditLog::log($firmId, 'TRANSFER', 'stock_transfer', $transfer['id'], "Stock transfer #{$transfer['transfer_number']}");
            Flash::set('success_msg', "Stock Transfer #{$transfer['transfer_number']} completed!");
            header('Location: /warehouses/transfers');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Stock transfer failed.'));
            header('Location: /warehouses/transfers/create');
            exit;
        }
    }

    public function getValuation(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('adjust_stock');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $settings = Setting::get($firmId);
        $defaultMethod = $settings['general']['stock_valuation_method'] ?? 'fifo';
        $method = isset($_GET['method']) && in_array($_GET['method'], ['fifo', 'weighted_avg'], true)
            ? $_GET['method']
            : $defaultMethod;

        $valuation = Warehouse::getStockValuation($firmId, $method);

        view('warehouses/valuation', [
            'title' => 'Stock Valuation Report',
            'valuation' => $valuation,
            'activeMenu' => 'warehouses'
        ]);
    }

    public function apiItemBreakdown(array $params): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_items', true);
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $breakdown = Warehouse::getItemWarehouseBreakdown($id, (int)$firm['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'breakdown' => $breakdown]);
        exit;
    }
}
