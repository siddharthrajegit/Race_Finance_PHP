<?php
/**
 * Item Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Warehouse.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ItemController {
    public function listItems(): void {
        RBAC::requirePermission('view_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $selectedCategory = trim($_GET['category'] ?? '');
        
        if ($selectedCategory !== '') {
            $items = Item::getByCategory($firmId, $selectedCategory);
        } else {
            $items = Item::getByFirmId($firmId);
        }

        $lowStock = Item::getLowStock($firmId);
        $categories = Item::getCategories($firmId);
        $warehouses = (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE) ? Warehouse::getByFirmId($firmId, true) : [];

        view('items/list', [
            'title' => 'Items & Inventory',
            'items' => $items,
            'lowStockCount' => count($lowStock),
            'categories' => $categories,
            'warehouses' => $warehouses,
            'selectedCategory' => $selectedCategory,
            'activeMenu' => 'items'
        ]);
    }

    public function getCreate(): void {
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $categories = Item::getCategories((int)$firm['id']);

        view('items/form', [
            'title' => 'Add New Item',
            'item' => null,
            'categories' => $categories,
            'activeMenu' => 'items'
        ]);
    }

    public function postCreate(): void {
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Item name is required.');
                header('Location: /items/create');
                exit;
            }

            $salePrice = Security::parseCleanFloat($_POST['sale_price'] ?? 0);
            $purchasePrice = Security::parseCleanFloat($_POST['purchase_price'] ?? 0);
            $taxRate = Security::parseCleanFloat($_POST['tax_rate'] ?? 0);

            if ($salePrice <= 0 && !empty($_POST['sale_price_with_tax'])) {
                $priceWithTax = Security::parseCleanFloat($_POST['sale_price_with_tax']);
                if ($priceWithTax > 0) {
                    $salePrice = round($priceWithTax / (1 + ($taxRate / 100)), 2);
                }
            }

            if ($salePrice < 0 || $purchasePrice < 0) {
                Flash::set('error_msg', 'Sale price and purchase price cannot be negative.');
                header('Location: /items/create');
                exit;
            }

            if ($taxRate < 0 || $taxRate > 100) {
                Flash::set('error_msg', 'Tax rate must be between 0% and 100%.');
                header('Location: /items/create');
                exit;
            }

            $cleanHsn = !empty($_POST['hsn_code']) ? substr(preg_replace('/[^a-zA-Z0-9\-\.\/ ]/', '', strip_tags(trim($_POST['hsn_code']))), 0, 20) : null;
            $cleanUnit = !empty($_POST['unit']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($_POST['unit'])), 0, 15)) : 'PCS';
            $cleanCategory = !empty($_POST['category']) ? trim($_POST['category']) : null;
            $cleanSubUnit = !empty($_POST['sub_unit']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($_POST['sub_unit'])), 0, 15)) : null;
            $cleanSubUnitQty = isset($_POST['sub_unit_qty']) && $_POST['sub_unit_qty'] !== '' && (float)$_POST['sub_unit_qty'] > 0
                ? Security::parseCleanFloat($_POST['sub_unit_qty'])
                : null;

            Item::create([
                'firm_id' => $firmId,
                'name' => $name,
                'category' => $cleanCategory,
                'item_code' => $_POST['item_code'] ?? null,
                'hsn_code' => $cleanHsn,
                'unit' => $cleanUnit,
                'sub_unit' => $cleanSubUnit,
                'sub_unit_qty' => $cleanSubUnitQty,
                'sale_price' => $salePrice,
                'purchase_price' => $purchasePrice,
                'tax_rate' => $taxRate,
                'tax_inclusive' => !empty($_POST['tax_inclusive']) ? 1 : 0,
                'opening_stock' => Security::parseCleanFloat($_POST['opening_stock'] ?? 0),
                'low_stock_threshold' => Security::parseCleanFloat($_POST['low_stock_threshold'] ?? 5),
                'description' => $_POST['description'] ?? null,
                'tracking_type' => $_POST['tracking_type'] ?? 'none'
            ]);

            Flash::set('success_msg', "Item \"{$name}\" added successfully!");
            header('Location: /items');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to add item.'));
            header('Location: /items/create');
            exit;
        }
    }

    public function getEdit(array $params): void {
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $itemId = (int)($params['id'] ?? 0);
        $item = Item::getById($itemId, (int)$firm['id']);

        if (!$item) {
            Flash::set('error_msg', 'Item not found.');
            header('Location: /items');
            exit;
        }

        $categories = Item::getCategories((int)$firm['id']);

        view('items/form', [
            'title' => "Edit {$item['name']}",
            'item' => $item,
            'categories' => $categories,
            'activeMenu' => 'items'
        ]);
    }

    public function postEdit(array $params): void {
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $itemId = (int)($params['id'] ?? 0);

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Item name is required.');
                header("Location: /items/edit/{$itemId}");
                exit;
            }

            $salePrice = Security::parseCleanFloat($_POST['sale_price'] ?? 0);
            $purchasePrice = Security::parseCleanFloat($_POST['purchase_price'] ?? 0);
            $taxRate = Security::parseCleanFloat($_POST['tax_rate'] ?? 0);

            if ($salePrice <= 0 && !empty($_POST['sale_price_with_tax'])) {
                $priceWithTax = Security::parseCleanFloat($_POST['sale_price_with_tax']);
                if ($priceWithTax > 0) {
                    $salePrice = round($priceWithTax / (1 + ($taxRate / 100)), 2);
                }
            }

            if ($salePrice < 0 || $purchasePrice < 0) {
                Flash::set('error_msg', 'Sale price and purchase price cannot be negative.');
                header("Location: /items/edit/{$itemId}");
                exit;
            }

            if ($taxRate < 0 || $taxRate > 100) {
                Flash::set('error_msg', 'Tax rate must be between 0% and 100%.');
                header("Location: /items/edit/{$itemId}");
                exit;
            }

            $cleanHsn = !empty($_POST['hsn_code']) ? substr(preg_replace('/[^a-zA-Z0-9\-\.\/ ]/', '', strip_tags(trim($_POST['hsn_code']))), 0, 20) : null;
            $cleanUnit = !empty($_POST['unit']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($_POST['unit'])), 0, 15)) : 'PCS';
            $cleanCategory = !empty($_POST['category']) ? trim($_POST['category']) : null;
            $cleanSubUnit = !empty($_POST['sub_unit']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($_POST['sub_unit'])), 0, 15)) : null;
            $cleanSubUnitQty = isset($_POST['sub_unit_qty']) && $_POST['sub_unit_qty'] !== '' && (float)$_POST['sub_unit_qty'] > 0
                ? Security::parseCleanFloat($_POST['sub_unit_qty'])
                : null;

            Item::update($itemId, $firmId, [
                'name' => $name,
                'category' => $cleanCategory,
                'item_code' => $_POST['item_code'] ?? null,
                'hsn_code' => $cleanHsn,
                'unit' => $cleanUnit,
                'sub_unit' => $cleanSubUnit,
                'sub_unit_qty' => $cleanSubUnitQty,
                'sale_price' => $salePrice,
                'purchase_price' => $purchasePrice,
                'tax_rate' => $taxRate,
                'tax_inclusive' => !empty($_POST['tax_inclusive']) ? 1 : 0,
                'low_stock_threshold' => Security::parseCleanFloat($_POST['low_stock_threshold'] ?? 5),
                'description' => $_POST['description'] ?? null,
                'tracking_type' => $_POST['tracking_type'] ?? 'none'
            ]);

            Flash::set('success_msg', 'Item details updated.');
            header('Location: /items');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update item.'));
            header("Location: /items/edit/{$itemId}");
            exit;
        }
    }

    public function postAdjustStock(array $params): void {
        RBAC::requirePermission('adjust_stock');
        $firm = Auth::requireActiveFirm();
        $itemId = (int)($params['id'] ?? 0);
        $user = Auth::user();

        try {
            $qty = Security::parseCleanFloat($_POST['adjustment'] ?? 0);
            if ($qty <= 0) {
                Flash::set('error_msg', 'Stock adjustment quantity must be greater than 0.');
                header('Location: /items');
                exit;
            }

            $action = $_POST['action'] ?? 'add';
            $finalDelta = ($action === 'reduce') ? -abs($qty) : abs($qty);
            $warehouseId = !empty($_POST['warehouse_id']) ? (int)$_POST['warehouse_id'] : null;

            Item::adjustStock($itemId, (int)$firm['id'], $finalDelta, $warehouseId, trim($_POST['notes'] ?? '') ?: null, $user ? (int)$user['id'] : null);
            if (defined('ENABLE_AUDIT_LOGS') && ENABLE_AUDIT_LOGS) {
                AuditLog::log((int)$firm['id'], 'ADJUST_STOCK', 'item', $itemId, "Adjusted stock by {$finalDelta}");
            }
            Flash::set('success_msg', 'Stock level updated.');
            header('Location: /items');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to adjust stock.'));
            header('Location: /items');
            exit;
        }
    }

    public function postDelete(array $params): void {
        RBAC::requirePermission('manage_items');
        $firm = Auth::requireActiveFirm();
        $itemId = (int)($params['id'] ?? 0);

        try {
            Item::delete($itemId, (int)$firm['id']);
            if (defined('ENABLE_AUDIT_LOGS') && ENABLE_AUDIT_LOGS) {
                AuditLog::log((int)$firm['id'], 'DELETE', 'item', $itemId, "Deleted item #{$itemId}");
            }
            Flash::set('success_msg', 'Item deleted.');
            header('Location: /items');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to delete item.'));
            header('Location: /items');
            exit;
        }
    }

    public function apiGetItems(): void {
        RBAC::requirePermission('view_items', true);
        $firm = Auth::requireActiveFirm();
        $items = Item::getByFirmId((int)$firm['id']);

        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
    }

    public function postQuickCreate(): void {
        RBAC::requirePermission('manage_items', true);
        $firm = Auth::requireActiveFirm();
        header('Content-Type: application/json');

        try {
            $input = $_POST;
            if (empty($input['name'])) {
                $rawInput = file_get_contents('php://input');
                if (!empty($rawInput)) {
                    $jsonInput = json_decode($rawInput, true);
                    if (is_array($jsonInput)) {
                        $input = array_merge($input, $jsonInput);
                    }
                }
            }

            $name = trim($input['name'] ?? '');
            if ($name === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Item name is required.']);
                exit;
            }

            $taxRate = min(100.0, max(0.0, Security::parseCleanFloat($input['tax_rate'] ?? 0)));
            $salePrice = max(0.0, Security::parseCleanFloat($input['sale_price'] ?? 0));
            if ($salePrice <= 0.0 && !empty($input['sale_price_with_tax'])) {
                $priceWithTax = Security::parseCleanFloat($input['sale_price_with_tax']);
                if ($priceWithTax > 0.0) {
                    $salePrice = round($priceWithTax / (1 + ($taxRate / 100)), 2);
                }
            }
            $purchasePrice = max(0.0, Security::parseCleanFloat($input['purchase_price'] ?? 0));
            if ($purchasePrice === 0.0 && $salePrice > 0.0) {
                $purchasePrice = $salePrice;
            }
            if ($salePrice === 0.0 && $purchasePrice > 0.0) {
                $salePrice = $purchasePrice;
            }

            $cleanHsn = !empty($input['hsn_code']) ? substr(preg_replace('/[^a-zA-Z0-9\-\.\/ ]/', '', strip_tags(trim($input['hsn_code']))), 0, 20) : null;
            $cleanUnit = !empty($input['unit']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($input['unit'])), 0, 15)) : 'PCS';
            $cleanCategory = !empty($input['category']) ? trim($input['category']) : null;
            $cleanSubUnit = !empty($input['sub_unit']) ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($input['sub_unit'])), 0, 15)) : null;
            $cleanSubUnitQty = isset($input['sub_unit_qty']) && $input['sub_unit_qty'] !== '' && (float)$input['sub_unit_qty'] > 0
                ? Security::parseCleanFloat($input['sub_unit_qty'])
                : null;

            $item = Item::create([
                'firm_id' => (int)$firm['id'],
                'name' => $name,
                'category' => $cleanCategory,
                'item_code' => $input['item_code'] ?? null,
                'hsn_code' => $cleanHsn,
                'unit' => $cleanUnit,
                'sub_unit' => $cleanSubUnit,
                'sub_unit_qty' => $cleanSubUnitQty,
                'sale_price' => $salePrice,
                'purchase_price' => $purchasePrice,
                'tax_rate' => $taxRate,
                'tax_inclusive' => !empty($input['tax_inclusive']) ? 1 : 0,
                'opening_stock' => Security::parseCleanFloat($input['opening_stock'] ?? 0),
                'low_stock_threshold' => Security::parseCleanFloat($input['low_stock_threshold'] ?? 5),
                'description' => $input['description'] ?? null
            ]);

            echo json_encode([
                'success' => true,
                'item' => $item,
                'message' => "Item \"{$item['name']}\" created successfully!"
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => Security::getSafeErrorMessage($e, 'Failed to create item.')]);
            exit;
        }
    }
}
