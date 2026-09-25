<?php
/**
 * Payment Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Party.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Setting.php';

class PaymentController {
    public function listPayments(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $type = $_GET['type'] ?? null;
        $payments = Payment::getByFirmId((int)$firm['id'], $type);

        $title = ($type === 'payment_in') ? 'Payment Receipts (In)' : (($type === 'payment_out') ? 'Payment Vouchers (Out)' : 'All Payments');

        view('payments/list', [
            'title' => $title,
            'payments' => $payments,
            'typeFilter' => $type,
            'activeMenu' => 'payments'
        ]);
    }

    public function getCreate(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $type = $_GET['type'] ?? 'payment_in';
        $partyType = ($type === 'payment_in') ? 'customer' : 'supplier';
        $parties = Party::getByFirmId($firmId, $partyType);
        $nextPaymentNumber = Payment::getNextPaymentNumber($firmId, $type);

        view('payments/form', [
            'title' => ($type === 'payment_in') ? 'Record Payment Receipt (In)' : 'Record Payment Voucher (Out)',
            'type' => $type,
            'nextPaymentNumber' => $nextPaymentNumber,
            'today' => date('Y-m-d'),
            'parties' => $parties,
            'selectedPartyId' => !empty($_GET['party_id']) ? (int)$_GET['party_id'] : null,
            'selectedInvoiceId' => !empty($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : null,
            'activeMenu' => 'payments'
        ]);
    }

    public function postCreate(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $type = $_POST['type'] ?? 'payment_in';

        try {
            $partyId = !empty($_POST['party_id']) ? (int)$_POST['party_id'] : null;
            if (!$partyId) {
                Flash::set('error_msg', 'Please select a customer/supplier party.');
                header("Location: /payments/create?type={$type}");
                exit;
            }

            $amount = Security::parseCleanFloat($_POST['amount'] ?? 0);
            if ($amount <= 0) {
                Flash::set('error_msg', 'Please enter a valid payment amount greater than 0.');
                header("Location: /payments/create?type={$type}");
                exit;
            }

            $created = Payment::create([
                'firm_id' => $firmId,
                'type' => $type,
                'payment_number' => !empty($_POST['payment_number']) ? trim($_POST['payment_number']) : null,
                'payment_date' => !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d'),
                'party_id' => $partyId,
                'invoice_id' => !empty($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : null,
                'amount' => $amount,
                'payment_mode' => $_POST['payment_mode'] ?? 'cash',
                'reference_no' => !empty($_POST['reference_no']) ? trim($_POST['reference_no']) : null,
                'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null
            ]);

            Flash::set('success_msg', "Payment record \"{$created['payment_number']}\" created successfully!");
            header('Location: /payments');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to record payment.'));
            header("Location: /payments/create?type={$type}");
            exit;
        }
    }

    public function getView(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $id = (int)($params['id'] ?? 0);
        $payment = Payment::getById($id, $firmId);

        if (!$payment) {
            Flash::set('error_msg', 'Payment slip not found.');
            header('Location: /payments');
            exit;
        }

        $party = Party::getById((int)$payment['party_id'], $firmId);
        $settings = Setting::get($firmId);

        $linkedInvoices = [];
        if (!empty($payment['invoice_id'])) {
            $inv = Invoice::getById((int)$payment['invoice_id'], $firmId);
            if ($inv) $linkedInvoices[] = $inv;
        }

        view('payments/view', [
            'title' => ($payment['type'] === 'payment_in' ? 'Payment Receipt' : 'Payment Voucher') . " - {$payment['payment_number']}",
            'payment' => $payment,
            'party' => $party,
            'firm' => $firm,
            'linkedInvoices' => $linkedInvoices,
            'settings' => $settings,
            'activeMenu' => 'payments'
        ], false);
    }

    public function postDelete(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $id = (int)($params['id'] ?? 0);

        try {
            Payment::delete($id, (int)$firm['id']);
            Flash::set('success_msg', 'Payment record removed.');
            header('Location: /payments');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to delete payment.'));
            header('Location: /payments');
            exit;
        }
    }
}
