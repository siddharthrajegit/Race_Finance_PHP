<?php
/**
 * Barcode Label Studio & POS Scanner Lookup Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/SvgBarcode.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Setting.php';

class BarcodeController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_BARCODE') || !ENABLE_BARCODE) {
            Flash::set('info_msg', 'Barcode module is currently disabled in configuration.');
            header('Location: /items');
            exit;
        }
    }

    public function studio(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_items');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $items = Item::getByFirmId($firmId);
        $selectedItemId = !empty($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
        $labelQty = min(60, max(1, (int)($_GET['qty'] ?? 12)));
        $selectedItem = $selectedItemId > 0 ? Item::getById($selectedItemId, $firmId) : ($items[0] ?? null);

        $previewLabels = [];
        if ($selectedItem) {
            $code = !empty($selectedItem['item_code'])
                ? (string)$selectedItem['item_code']
                : 'ITM-' . str_pad((string)$selectedItem['id'], 5, '0', STR_PAD_LEFT);
            $svg = SvgBarcode::render($code, 42, 1.4, true);
            for ($i = 0; $i < $labelQty; $i++) {
                $previewLabels[] = [
                    'name' => $selectedItem['name'],
                    'code' => $code,
                    'price' => (float)($selectedItem['sale_price'] ?? 0),
                    'unit' => $selectedItem['unit'] ?? 'PCS',
                    'svg' => $svg
                ];
            }
        }

        view('barcodes/studio', [
            'title' => 'Barcode Label Printing Studio',
            'items' => $items,
            'selectedItem' => $selectedItem,
            'labelQty' => $labelQty,
            'previewLabels' => $previewLabels,
            'firm' => $firm,
            'settings' => Setting::get($firmId),
            'activeMenu' => 'items'
        ]);
    }

    public function apiLookup(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_items', true);
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $code = trim((string)($_GET['code'] ?? ''));

        header('Content-Type: application/json; charset=utf-8');
        if ($code === '') {
            echo json_encode(['success' => false, 'error' => 'Barcode is empty.']);
            exit;
        }

        $items = Item::getByFirmId($firmId);
        $match = null;
        foreach ($items as $it) {
            $autoCode = 'ITM-' . str_pad((string)$it['id'], 5, '0', STR_PAD_LEFT);
            if (strcasecmp((string)($it['item_code'] ?? ''), $code) === 0 || strcasecmp($autoCode, $code) === 0) {
                $match = $it;
                break;
            }
        }

        echo json_encode([
            'success' => ($match !== null),
            'item' => $match
        ]);
        exit;
    }
}
