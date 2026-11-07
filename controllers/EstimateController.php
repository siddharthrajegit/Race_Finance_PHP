<?php
/**
 * Estimate & Rate List Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Estimate.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Firm.php';

class EstimateController {
    public function listEstimates(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $estimates = Estimate::getByFirmId($firmId);

        view('estimates/list', [
            'title' => 'Estimates & Quotations',
            'estimates' => $estimates,
            'activeMenu' => 'estimates'
        ]);
    }

    public function getBuilder(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $categories = Item::getCategories($firmId);
        $allItems = Item::getByFirmId($firmId);
        $nextEstimateNumber = Estimate::getNextEstimateNumber($firmId);

        // Preload items if cloning from an existing estimate
        $preloadedEstimate = null;
        if (!empty($_GET['from_estimate'])) {
            $preloadedEstimate = Estimate::getById((int)$_GET['from_estimate'], $firmId);
        }

        view('estimates/builder', [
            'title' => 'Rate List & Estimate Builder',
            'firm' => $firm,
            'categories' => $categories,
            'allItems' => $allItems,
            'nextEstimateNumber' => $nextEstimateNumber,
            'preloadedEstimate' => $preloadedEstimate,
            'activeMenu' => 'estimates'
        ]);
    }

    public function postSave(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                  (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        try {
            $input = $_POST;
            $items = [];
            if (!empty($input['items_json'])) {
                $decoded = json_decode($input['items_json'], true);
                if (is_array($decoded)) {
                    $items = $decoded;
                }
            } elseif (!empty($input['items']) && is_array($input['items'])) {
                $items = $input['items'];
            }

            if (empty($items)) {
                throw new Exception("Please select at least one item to save the estimate.");
            }

            $dateRaw = $input['estimate_date'] ?? date('Y-m-d');
            $cleanDate = normalizeDate($dateRaw) ?: date('Y-m-d');
            $customerName = !empty($input['customer_name']) ? trim($input['customer_name']) : null;
            $estimateNumber = !empty($input['estimate_number']) ? trim($input['estimate_number']) : null;
            $notes = !empty($input['notes']) ? trim($input['notes']) : null;

            $estimate = Estimate::create([
                'firm_id' => $firmId,
                'estimate_number' => $estimateNumber,
                'estimate_date' => $cleanDate,
                'customer_name' => $customerName,
                'notes' => $notes,
                'status' => 'draft'
            ], $items);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'estimate_id' => $estimate['id'],
                    'estimate_number' => $estimate['estimate_number'],
                    'redirect' => '/estimates/view/' . $estimate['id']
                ]);
                exit;
            }

            Flash::set('success_msg', "Estimate #{$estimate['estimate_number']} saved successfully!");
            header('Location: /estimates/view/' . $estimate['id']);
            exit;
        } catch (Throwable $e) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => Security::getSafeErrorMessage($e, 'Failed to save estimate.')
                ]);
                exit;
            }

            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save estimate.'));
            header('Location: /estimates/builder');
            exit;
        }
    }

    public function getView(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $id = (int)($params['id'] ?? 0);

        $estimate = Estimate::getById($id, $firmId);
        if (!$estimate) {
            Flash::set('error_msg', 'Estimate not found.');
            header('Location: /estimates');
            exit;
        }

        view('estimates/view', [
            'title' => "Estimate #{$estimate['estimate_number']}",
            'estimate' => $estimate,
            'firm' => $firm,
            'activeMenu' => 'estimates'
        ]);
    }

    public function getPrint(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $id = (int)($params['id'] ?? 0);

        $estimate = Estimate::getById($id, $firmId);
        if (!$estimate) {
            Flash::set('error_msg', 'Estimate not found.');
            header('Location: /estimates');
            exit;
        }

        $mode = $_GET['mode'] ?? 'estimate'; // 'estimate' or 'ratelist'

        // Render print layout without main sidebar layout
        view('estimates/print', [
            'title' => ($mode === 'ratelist') ? "Rate List" : "Estimate #{$estimate['estimate_number']}",
            'estimate' => $estimate,
            'firm' => $firm,
            'mode' => $mode
        ], false);
    }

    public function postPrintDirect(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();

        $mode = $_POST['mode'] ?? 'ratelist';
        $customerName = !empty($_POST['customer_name']) ? trim($_POST['customer_name']) : null;
        $estimateNumber = !empty($_POST['estimate_number']) ? trim($_POST['estimate_number']) : '';
        $estimateDate = !empty($_POST['estimate_date']) ? normalizeDate($_POST['estimate_date']) : date('Y-m-d');
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        $items = [];
        if (!empty($_POST['items_json'])) {
            $decoded = json_decode($_POST['items_json'], true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        // Calculate totals on the fly for direct printing
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        foreach ($items as &$it) {
            $qty = max(0.001, Security::parseCleanFloat($it['quantity'] ?? 1));
            $rate = max(0.0, Security::parseCleanFloat($it['rate'] ?? 0));
            $taxRate = min(100.0, max(0.0, Security::parseCleanFloat($it['tax_rate'] ?? 0)));
            $discPercent = min(100.0, max(0.0, Security::parseCleanFloat($it['discount_percent'] ?? 0)));

            $lineBase = $qty * $rate;
            $lineDisc = round($lineBase * ($discPercent / 100), 2);
            $lineTaxable = max(0.0, $lineBase - $lineDisc);
            $lineTax = round($lineTaxable * ($taxRate / 100), 2);
            $lineTotal = round($lineTaxable + $lineTax, 2);

            $it['quantity'] = $qty;
            $it['rate'] = $rate;
            $it['tax_rate'] = $taxRate;
            $it['discount_percent'] = $discPercent;
            $it['taxable_amount'] = $lineTaxable;
            $it['tax_amount'] = $lineTax;
            $it['total_amount'] = $lineTotal;

            $subtotal += $lineTaxable;
            $discountTotal += $lineDisc;
            $taxTotal += $lineTax;
            $grandTotal += $lineTotal;
        }
        unset($it);

        $estimate = [
            'id' => null,
            'estimate_number' => $estimateNumber,
            'estimate_date' => $estimateDate,
            'customer_name' => $customerName,
            'subtotal' => $subtotal,
            'discount_amount' => $discountTotal,
            'tax_amount' => $taxTotal,
            'grand_total' => $grandTotal,
            'notes' => $notes,
            'items' => $items
        ];

        view('estimates/print', [
            'title' => ($mode === 'ratelist') ? "Rate List" : "Estimate - " . ($estimateNumber ?: 'Live'),
            'estimate' => $estimate,
            'firm' => $firm,
            'mode' => $mode
        ], false);
    }

    public function postDelete(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $id = (int)($params['id'] ?? 0);

        try {
            Estimate::delete($id, $firmId);
            Flash::set('success_msg', 'Estimate deleted successfully.');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to delete estimate.'));
        }

        header('Location: /estimates');
        exit;
    }

    public function apiItemsByCategory(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $category = $_GET['category'] ?? null;
        $items = Item::getByCategory($firmId, $category);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
        exit;
    }
}
