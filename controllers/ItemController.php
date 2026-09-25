<?php
/**
 * Item Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Item.php';

class ItemController {
    public function listItems(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $items = Item::getByFirmId((int)$firm['id']);
        $lowStock = Item::getLowStock((int)$firm['id']);

        view('items/list', [
            'title' => 'Items & Inventory',
            'items' => $items,
            'lowStockCount' => count($lowStock),
            'activeMenu' => 'items'
        ]);
    }

    public function getCreate(): void {
        Auth::requireUserOnly();
        Auth::requireActiveFirm();

        view('items/form', [
            'title' => 'Add New Item',
            'item' => null,
            'activeMenu' => 'items'
        ]);
    }

    public function postCreate(): void {
        Auth::requireUserOnly();
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

            Item::create([
                'firm_id' => $firmId,
                'name' => $name,
                'item_code' => $_POST['item_code'] ?? null,
                'hsn_code' => $_POST['hsn_code'] ?? null,
                'unit' => $_POST['unit'] ?? 'PCS',
                'sale_price' => $salePrice,
                'purchase_price' => $purchasePrice,
                'tax_rate' => $taxRate,
                'tax_inclusive' => !empty($_POST['tax_inclusive']) ? 1 : 0,
                'opening_stock' => Security::parseCleanFloat($_POST['opening_stock'] ?? 0),
                'low_stock_threshold' => Security::parseCleanFloat($_POST['low_stock_threshold'] ?? 5),
                'description' => $_POST['description'] ?? null
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
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $itemId = (int)($params['id'] ?? 0);
        $item = Item::getById($itemId, (int)$firm['id']);

        if (!$item) {
            Flash::set('error_msg', 'Item not found.');
            header('Location: /items');
            exit;
        }

        view('items/form', [
            'title' => "Edit {$item['name']}",
            'item' => $item,
            'activeMenu' => 'items'
        ]);
    }

    public function postEdit(array $params): void {
        Auth::requireUserOnly();
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

            Item::update($itemId, $firmId, [
                'name' => $name,
                'item_code' => $_POST['item_code'] ?? null,
                'hsn_code' => $_POST['hsn_code'] ?? null,
                'unit' => $_POST['unit'] ?? 'PCS',
                'sale_price' => $salePrice,
                'purchase_price' => $purchasePrice,
                'tax_rate' => $taxRate,
                'tax_inclusive' => !empty($_POST['tax_inclusive']) ? 1 : 0,
                'low_stock_threshold' => Security::parseCleanFloat($_POST['low_stock_threshold'] ?? 5),
                'description' => $_POST['description'] ?? null
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
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $itemId = (int)($params['id'] ?? 0);

        try {
            $qty = Security::parseCleanFloat($_POST['adjustment'] ?? 0);
            if ($qty <= 0) {
                Flash::set('error_msg', 'Stock adjustment quantity must be greater than 0.');
                header('Location: /items');
                exit;
            }

            $action = $_POST['action'] ?? 'add';
            $finalDelta = ($action === 'reduce') ? -abs($qty) : abs($qty);

            Item::adjustStock($itemId, (int)$firm['id'], $finalDelta);
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
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $itemId = (int)($params['id'] ?? 0);

        try {
            Item::delete($itemId, (int)$firm['id']);
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
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $items = Item::getByFirmId((int)$firm['id']);

        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
    }

    public function postQuickCreate(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        header('Content-Type: application/json');

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Item name is required.']);
                exit;
            }

            $salePrice = max(0.0, Security::parseCleanFloat($_POST['sale_price'] ?? 0));
            $purchasePrice = max(0.0, Security::parseCleanFloat($_POST['purchase_price'] ?? 0));
            $taxRate = min(100.0, max(0.0, Security::parseCleanFloat($_POST['tax_rate'] ?? 0)));

            $item = Item::create([
                'firm_id' => (int)$firm['id'],
                'name' => $name,
                'item_code' => $_POST['item_code'] ?? null,
                'hsn_code' => $_POST['hsn_code'] ?? null,
                'unit' => $_POST['unit'] ?? 'PCS',
                'sale_price' => $salePrice,
                'purchase_price' => $purchasePrice,
                'tax_rate' => $taxRate,
                'tax_inclusive' => !empty($_POST['tax_inclusive']) ? 1 : 0,
                'opening_stock' => Security::parseCleanFloat($_POST['opening_stock'] ?? 0),
                'low_stock_threshold' => Security::parseCleanFloat($_POST['low_stock_threshold'] ?? 5),
                'description' => $_POST['description'] ?? null
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
