<?php
/**
 * Invoice Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Party.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Model.php';

class InvoiceController {
    private static function formArrayValue($value, int $index, $fallback = 0) {
        if (is_array($value)) {
            return (isset($value[$index]) && $value[$index] !== '') ? $value[$index] : $fallback;
        }
        return ($value !== null && $value !== '') ? $value : $fallback;
    }

    private static function calculateTax(float $taxable, float $taxRate, bool $isGst, bool $isInterstate): array {
        $tax = ['cgst_rate' => 0.0, 'cgst_amount' => 0.0, 'sgst_rate' => 0.0, 'sgst_amount' => 0.0, 'igst_rate' => 0.0, 'igst_amount' => 0.0, 'total' => 0.0];
        if (!$isGst || $taxRate <= 0) return $tax;

        if ($isInterstate) {
            $tax['igst_rate'] = $taxRate;
            $tax['igst_amount'] = $taxable * ($taxRate / 100);
            $tax['total'] = $tax['igst_amount'];
        } else {
            $tax['cgst_rate'] = $taxRate / 2;
            $tax['sgst_rate'] = $taxRate / 2;
            $tax['cgst_amount'] = $taxable * ($tax['cgst_rate'] / 100);
            $tax['sgst_amount'] = $taxable * ($tax['sgst_rate'] / 100);
            $tax['total'] = $tax['cgst_amount'] + $tax['sgst_amount'];
        }
        return $tax;
    }

    private static function resolveParty(array $body, int $firmId, array $cleaned): int {
        $type = $body['type'] ?? 'sale';
        $partyId = !empty($body['party_id']) && is_numeric($body['party_id']) ? (int)$body['party_id'] : null;

        if ($partyId) {
            return $partyId;
        }

        $partyName = trim($body['party_name'] ?? '');
        $existing = Party::getByName($partyName, $firmId);
        if ($existing) {
            return (int)$existing['id'];
        }

        $newParty = Party::create([
            'firm_id' => $firmId,
            'type' => ($type === 'purchase') ? 'supplier' : 'customer',
            'name' => $partyName,
            'phone' => $cleaned['cleanPhone'],
            'email' => null,
            'gstin' => $cleaned['cleanGstin'],
            'billing_address' => $body['party_address'] ?? null,
            'state' => $cleaned['validatedState'] ?? null,
            'state_code' => $cleaned['validatedStateCode'] ?? null,
            'opening_balance' => 0
        ]);
        return (int)$newParty['id'];
    }

    private static function resolveItemId(array $body, int $index, int $firmId, string $type, bool $isGst, string $itemName): int {
        $rawItemId = self::formArrayValue($body['item_id'] ?? null, $index, null);
        if ($rawItemId && is_numeric($rawItemId) && (int)$rawItemId > 0) {
            return (int)$rawItemId;
        }

        $existing = Item::getByName($itemName, $firmId);
        if ($existing) {
            return (int)$existing['id'];
        }

        $rowRate = max(0.0, Security::parseCleanFloat(self::formArrayValue($body['rate'] ?? null, $index, 0)));
        $rowTaxRate = $isGst ? min(100.0, max(0.0, Security::parseCleanFloat(self::formArrayValue($body['item_tax_rate'] ?? null, $index, 0)))) : 0;

        $newItem = Item::create([
            'firm_id' => $firmId,
            'name' => $itemName,
            'item_code' => null,
            'hsn_code' => self::formArrayValue($body['hsn_code'] ?? null, $index, '') ?: null,
            'unit' => self::formArrayValue($body['unit'] ?? null, $index, 'PCS') ?: 'PCS',
            'sale_price' => ($type === 'sale') ? $rowRate : 0,
            'purchase_price' => ($type === 'purchase') ? $rowRate : 0,
            'tax_rate' => $rowTaxRate,
            'opening_stock' => 0,
            'low_stock_threshold' => 0
        ]);
        return (int)$newItem['id'];
    }

    private static function buildInvoiceSubmission(array $body, int $firmId, array $activeFirm, ?array $existingInvoice = null): array {
        $type = $body['type'] ?? ($existingInvoice['type'] ?? 'sale');

        $invoiceNumber = trim((string)($body['invoice_number'] ?? ''));
        if ($invoiceNumber !== '' && (!ctype_digit($invoiceNumber) || (int)$invoiceNumber <= 0)) {
            throw new Exception("Invalid Bill Number: Bill / Invoice number must contain only positive numbers (e.g. 001, 101).");
        }

        $partyName = trim($body['party_name'] ?? '');
        if ($partyName === '') {
            throw new Exception("Customer / Party name is required.");
        }

        $cleanPhone = null;
        $rawPhone = trim($body['party_phone'] ?? '');
        if ($rawPhone !== '') {
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strlen($cleanPhone) !== 10) {
                throw new Exception("Invalid Phone: Mobile number must contain exactly 10 digits.");
            }
        }

        $cleanGstin = null;
        $rawGstin = trim($body['party_gstin'] ?? '');
        if ($rawGstin !== '') {
            $cleanGstin = strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rawGstin));
            if (strlen($cleanGstin) !== 15) {
                throw new Exception("Invalid GSTIN: GSTIN must be exactly 15 characters long.");
            }
        }

        $validatedState = !empty($body['party_state']) ? trim($body['party_state']) : null;
        $validatedStateCode = !empty($body['party_state_code']) ? trim($body['party_state_code']) : null;
        if ($cleanGstin && strlen($cleanGstin) === 15) {
            $gstPrefix = substr($cleanGstin, 0, 2);
            foreach (Model::GST_STATES as $s) {
                if ($s['code'] === $gstPrefix) {
                    $validatedState = $s['name'];
                    $validatedStateCode = $s['code'];
                    break;
                }
            }
        }

        $partyId = self::resolveParty($body, $firmId, [
            'cleanPhone' => $cleanPhone,
            'cleanGstin' => $cleanGstin,
            'validatedState' => $validatedState,
            'validatedStateCode' => $validatedStateCode
        ]);

        $isGst = !empty($body['is_gst_bill']);
        $isInterstate = !empty($body['is_interstate']);

        $itemNames = is_array($body['item_name'] ?? null) ? $body['item_name'] : (!empty($body['item_name']) ? [$body['item_name']] : []);
        $items = [];
        $subtotal = 0.0;
        $cgstTotal = 0.0;
        $sgstTotal = 0.0;
        $igstTotal = 0.0;
        $taxTotal = 0.0;

        for ($i = 0; $i < count($itemNames); $i++) {
            $itemName = trim((string)$itemNames[$i]);
            if ($itemName === '') continue;

            $qty = Security::parseCleanFloat(self::formArrayValue($body['quantity'] ?? null, $i, 1));
            if ($qty <= 0) {
                throw new Exception("Invalid Quantity: Quantity for item \"{$itemName}\" must be greater than 0.");
            }

            $rate = Security::parseCleanFloat(self::formArrayValue($body['rate'] ?? null, $i, 0));
            if ($rate < 0) {
                throw new Exception("Invalid Price: Price for item \"{$itemName}\" cannot be negative.");
            }

            $discPct = min(100.0, max(0.0, Security::parseCleanFloat(self::formArrayValue($body['item_discount_percent'] ?? null, $i, 0))));
            $gross = $qty * $rate;
            $discAmt = $gross * ($discPct / 100);
            $taxable = max(0.0, $gross - $discAmt);

            $taxRate = 0.0;
            if ($isGst) {
                $taxRate = min(100.0, max(0.0, Security::parseCleanFloat(self::formArrayValue($body['item_tax_rate'] ?? null, $i, 0))));
            }

            $tax = self::calculateTax($taxable, $taxRate, $isGst, $isInterstate);
            $totalAmount = $taxable + $tax['total'];

            $subtotal += $taxable;
            $cgstTotal += $tax['cgst_amount'];
            $sgstTotal += $tax['sgst_amount'];
            $igstTotal += $tax['igst_amount'];
            $taxTotal += $tax['total'];

            $items[] = [
                'item_id' => self::resolveItemId($body, $i, $firmId, $type, $isGst, $itemName),
                'item_name' => $itemName,
                'hsn_code' => self::formArrayValue($body['hsn_code'] ?? null, $i, '') ?: null,
                'unit' => self::formArrayValue($body['unit'] ?? null, $i, 'PCS') ?: 'PCS',
                'quantity' => $qty,
                'rate' => $rate,
                'discount_percent' => $discPct,
                'discount_amount' => $discAmt,
                'taxable_amount' => $taxable,
                'tax_rate' => $taxRate,
                'cgst_rate' => $tax['cgst_rate'],
                'cgst_amount' => $tax['cgst_amount'],
                'sgst_rate' => $tax['sgst_rate'],
                'sgst_amount' => $tax['sgst_amount'],
                'igst_rate' => $tax['igst_rate'],
                'igst_amount' => $tax['igst_amount'],
                'total_amount' => $totalAmount
            ];
        }

        if (empty($items)) {
            throw new Exception("Please add at least one item row to the bill.");
        }

        $discountType = $body['discount_type'] ?? 'percentage';
        $discountValue = max(0.0, Security::parseCleanFloat($body['discount_value'] ?? 0));
        $isPct = ($discountType === 'percentage');

        if ($isPct && $discountValue > 100) {
            throw new Exception("Invalid Discount: Overall discount percentage cannot exceed 100%.");
        }
        if (!$isPct && $discountValue > $subtotal) {
            throw new Exception("Invalid Discount: Overall discount amount cannot exceed subtotal.");
        }

        $discountAmount = $isPct ? min($subtotal, $subtotal * ($discountValue / 100)) : min($subtotal, $discountValue);
        $netTaxable = max(0.0, $subtotal - $discountAmount);

        // Final Amount GST calculation mode if selected
        if ($isGst && ($body['gst_calc_mode'] ?? '') === 'final_amount') {
            $finalTaxRate = min(100.0, max(0.0, Security::parseCleanFloat($body['final_tax_rate'] ?? 0)));
            if ($isInterstate) {
                $igstTotal = $netTaxable * ($finalTaxRate / 100);
                $cgstTotal = 0.0;
                $sgstTotal = 0.0;
                $taxTotal = $igstTotal;
            } else {
                $cgstTotal = $netTaxable * (($finalTaxRate / 2) / 100);
                $sgstTotal = $netTaxable * (($finalTaxRate / 2) / 100);
                $igstTotal = 0.0;
                $taxTotal = $cgstTotal + $sgstTotal;
            }
        }

        $unroundedGrand = $netTaxable + $taxTotal;
        $grandTotal = round($unroundedGrand);
        $roundOff = $grandTotal - $unroundedGrand;

        $paidAmount = max(0.0, Security::parseCleanFloat($body['paid_amount'] ?? 0));
        $balanceDue = max(0.0, $grandTotal - $paidAmount);
        $paymentStatus = ($paidAmount >= $grandTotal && $grandTotal > 0) ? 'paid' : (($paidAmount > 0) ? 'partial' : 'unpaid');

        $invoiceData = [
            'firm_id' => $firmId,
            'type' => $type,
            'invoice_number' => !empty($body['invoice_number']) ? trim($body['invoice_number']) : ($existingInvoice['invoice_number'] ?? null),
            'invoice_date' => !empty($body['invoice_date']) ? $body['invoice_date'] : date('Y-m-d'),
            'due_date' => !empty($body['due_date']) ? $body['due_date'] : null,
            'party_id' => $partyId,
            'party_name' => $partyName,
            'party_phone' => $cleanPhone,
            'party_gstin' => $cleanGstin,
            'party_address' => $body['party_address'] ?? null,
            'party_state' => $validatedState,
            'party_state_code' => $validatedStateCode,
            'is_gst_bill' => $isGst ? 1 : 0,
            'is_interstate' => $isInterstate ? 1 : 0,
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $netTaxable,
            'cgst_amount' => $cgstTotal,
            'sgst_amount' => $sgstTotal,
            'igst_amount' => $igstTotal,
            'tax_amount' => $taxTotal,
            'round_off' => $roundOff,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'payment_status' => $paymentStatus,
            'payment_mode' => $body['payment_mode'] ?? 'cash',
            'notes' => $body['notes'] ?? null,
            'terms' => $body['terms'] ?? ($activeFirm['terms'] ?? null)
        ];

        return ['invoiceData' => $invoiceData, 'items' => $items];
    }

    private static function renderForm(string $type, string $title, ?array $invoice = null): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $invType = $invoice ? ($invoice['type'] ?? 'sale') : $type;

        view('invoices/form', [
            'title' => $title,
            'isEdit' => ($invoice !== null),
            'invoice' => $invoice,
            'invoiceType' => $invType,
            'nextInvoiceNumber' => $invoice ? $invoice['invoice_number'] : Invoice::getNextInvoiceNumber($firmId, $invType),
            'today' => $invoice ? $invoice['invoice_date'] : date('Y-m-d'),
            'parties' => Party::getByFirmId($firmId, ($invType === 'sale') ? 'customer' : 'supplier'),
            'items' => Item::getByFirmId($firmId),
            'settings' => Setting::get($firmId),
            'gstStates' => Model::GST_STATES,
            'activeMenu' => ($invType === 'sale') ? 'sales' : 'purchases'
        ]);
    }

    public function listSales(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();

        view('invoices/sales_list', [
            'title' => 'Sales Records',
            'invoices' => Invoice::getByFirmId((int)$firm['id'], 'sale'),
            'activeMenu' => 'sales'
        ]);
    }

    public function listPurchases(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();

        view('invoices/purchase_list', [
            'title' => 'Purchase Records',
            'invoices' => Invoice::getByFirmId((int)$firm['id'], 'purchase'),
            'activeMenu' => 'purchases'
        ]);
    }

    public function getCreateSale(): void {
        self::renderForm('sale', 'Create Sales Record');
    }

    public function getCreatePurchase(): void {
        self::renderForm('purchase', 'Create Purchase Record');
    }

    public function postCreate(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $type = $_POST['type'] ?? 'sale';
        $redirectUrl = ($type === 'purchase') ? '/purchases/create' : '/sales/create';

        try {
            $data = self::buildInvoiceSubmission($_POST, (int)$firm['id'], $firm);
            $created = Invoice::create($data['invoiceData'], $data['items']);

            $label = ($type === 'purchase') ? 'Purchase Bill' : 'Sales Invoice';
            Flash::set('success_msg', "{$label} \"{$created['invoice_number']}\" created successfully!");
            header("Location: /invoices/view/{$created['id']}");
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to generate bill.'));
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    public function getEdit(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $invoice = Invoice::getById($id, (int)$firm['id']);

        if (!$invoice) {
            Flash::set('error_msg', 'Invoice not found.');
            header('Location: /sales');
            exit;
        }

        $label = ($invoice['type'] === 'purchase') ? 'Purchase Bill' : 'Sales Invoice';
        self::renderForm($invoice['type'], "Edit {$label}: {$invoice['invoice_number']}", $invoice);
    }

    public function postEdit(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $existing = Invoice::getById($id, (int)$firm['id']);

        if (!$existing) {
            Flash::set('error_msg', 'Invoice not found.');
            header('Location: /sales');
            exit;
        }

        try {
            $data = self::buildInvoiceSubmission($_POST, (int)$firm['id'], $firm, $existing);
            $updated = Invoice::update($id, (int)$firm['id'], $data['invoiceData'], $data['items']);

            $label = ($updated['type'] === 'purchase') ? 'Purchase Bill' : 'Sales Invoice';
            Flash::set('success_msg', "{$label} \"{$updated['invoice_number']}\" updated successfully!");
            header("Location: /invoices/view/{$updated['id']}");
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update bill.'));
            header("Location: /invoices/edit/{$id}");
            exit;
        }
    }

    public function getView(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $invoice = Invoice::getById($id, (int)$firm['id']);

        if (!$invoice) {
            Flash::set('error_msg', 'Invoice not found.');
            header('Location: /sales');
            exit;
        }

        view('invoices/view', [
            'title' => ($invoice['type'] === 'purchase' ? 'Purchase Record' : 'Sales Record') . " - {$invoice['invoice_number']}",
            'invoice' => $invoice,
            'firm' => $firm,
            'activeMenu' => ($invoice['type'] === 'purchase') ? 'purchases' : 'sales'
        ]);
    }

    public function getDownload(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $invoice = Invoice::getById($id, (int)$firm['id']);

        if (!$invoice) {
            Flash::set('error_msg', 'Record not found.');
            header('Location: /sales');
            exit;
        }

        view('invoices/download', [
            'title' => "Download Record - {$invoice['invoice_number']}",
            'invoice' => $invoice,
            'firm' => $firm,
            'settings' => Setting::get((int)$firm['id'])
        ], false);
    }

    public function getPrintA4(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $invoice = Invoice::getById($id, (int)$firm['id']);

        if (!$invoice) {
            Flash::set('error_msg', 'Record not found.');
            header('Location: /sales');
            exit;
        }

        view('invoices/print_a4', [
            'title' => "Print Record - {$invoice['invoice_number']}",
            'invoice' => $invoice,
            'firm' => $firm
        ], false);
    }

    public function postDelete(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);
        $invoice = Invoice::getById($id, (int)$firm['id']);

        if (!$invoice) {
            Flash::set('error_msg', 'Record not found.');
            header('Location: /sales');
            exit;
        }

        Invoice::delete($id, (int)$firm['id']);
        Flash::set('success_msg', "Record \"{$invoice['invoice_number']}\" deleted and item inventory restored.");
        header('Location: ' . (($invoice['type'] === 'purchase') ? '/purchases' : '/sales'));
        exit;
    }
}
