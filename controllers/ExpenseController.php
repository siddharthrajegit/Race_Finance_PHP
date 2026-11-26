<?php
/**
 * Business Expense Management Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ExpenseController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_EXPENSES') || !ENABLE_EXPENSES) {
            Flash::set('info_msg', 'Expense module is currently disabled in configuration.');
            header('Location: /dashboard');
            exit;
        }
    }

    public function listExpenses(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_expenses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $filters = [
            'category' => trim($_GET['category'] ?? ''),
            'from_date' => trim($_GET['from_date'] ?? ''),
            'to_date' => trim($_GET['to_date'] ?? '')
        ];

        $expenses = Expense::getByFirmId($firmId, $filters, 150);
        $summary = Expense::getSummary($firmId, $filters);
        $categories = Expense::getCategories($firmId);

        view('expenses/list', [
            'title' => 'Expenses & Outflows',
            'expenses' => $expenses,
            'summary' => $summary,
            'categories' => $categories,
            'filters' => $filters,
            'activeMenu' => 'expenses'
        ]);
    }

    public function getCreate(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_expenses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        view('expenses/form', [
            'title' => 'Record Business Expense',
            'expense' => null,
            'categories' => Expense::getCategories($firmId),
            'nextExpenseNumber' => Expense::getNextExpenseNumber($firmId),
            'activeMenu' => 'expenses'
        ]);
    }

    public function postCreate(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_expenses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $attachmentPath = Upload::handle('attachment', 'expenses', true);
            $category = trim((string)($_POST['new_category'] ?? ''));
            if ($category === '') {
                $category = trim((string)($_POST['category'] ?? 'Other Expense'));
            }

            $expense = Expense::create([
                'firm_id' => $firmId,
                'expense_number' => $_POST['expense_number'] ?? null,
                'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
                'category' => $category,
                'payee' => $_POST['payee'] ?? null,
                'amount' => Security::parseCleanFloat($_POST['amount'] ?? 0),
                'payment_mode' => $_POST['payment_mode'] ?? 'cash',
                'reference_no' => $_POST['reference_no'] ?? null,
                'attachment_path' => $attachmentPath,
                'notes' => $_POST['notes'] ?? null,
                'created_by' => $user ? (int)$user['id'] : null
            ]);

            AuditLog::log($firmId, 'CREATE', 'expense', $expense['id'], "Recorded expense #{$expense['expense_number']} ({$expense['category']}) amount {$expense['amount']}");
            Flash::set('success_msg', "Expense #{$expense['expense_number']} saved!");
            header('Location: /expenses');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save expense.'));
            header('Location: /expenses/create');
            exit;
        }
    }

    public function getEdit($id): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_expenses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $expense = Expense::getById((int)$id, $firmId);
        if (!$expense) {
            Flash::set('error_msg', 'Expense record not found.');
            header('Location: /expenses');
            exit;
        }

        view('expenses/form', [
            'title' => 'Edit Expense #' . $expense['expense_number'],
            'expense' => $expense,
            'categories' => Expense::getCategories($firmId),
            'nextExpenseNumber' => $expense['expense_number'],
            'activeMenu' => 'expenses'
        ]);
    }

    public function postEdit($id): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_expenses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $existing = Expense::getById((int)$id, $firmId);
            if (!$existing) {
                Flash::set('error_msg', 'Expense record not found.');
                header('Location: /expenses');
                exit;
            }

            $attach = $existing['attachment_path'];
            $newAttach = Upload::handle('attachment', 'expenses', true);
            if ($newAttach) {
                if (!empty($attach)) {
                    Security::safeDeleteUpload($attach);
                }
                $attach = $newAttach;
            } elseif (!empty($_POST['remove_attachment']) && !empty($attach)) {
                Security::safeDeleteUpload($attach);
                $attach = null;
            }

            $category = trim((string)($_POST['new_category'] ?? ''));
            if ($category === '') {
                $category = trim((string)($_POST['category'] ?? $existing['category']));
            }

            $updated = Expense::update((int)$id, $firmId, [
                'expense_number' => $_POST['expense_number'] ?? $existing['expense_number'],
                'expense_date' => $_POST['expense_date'] ?? $existing['expense_date'],
                'category' => $category,
                'payee' => $_POST['payee'] ?? null,
                'amount' => Security::parseCleanFloat($_POST['amount'] ?? 0),
                'payment_mode' => $_POST['payment_mode'] ?? 'cash',
                'reference_no' => $_POST['reference_no'] ?? null,
                'attachment_path' => $attach,
                'notes' => $_POST['notes'] ?? null,
                'updated_by' => $user ? (int)$user['id'] : null
            ]);

            AuditLog::log($firmId, 'UPDATE', 'expense', $id, "Updated expense #{$updated['expense_number']} amount {$updated['amount']}");
            Flash::set('success_msg', "Expense #{$updated['expense_number']} updated!");
            header('Location: /expenses');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update expense.'));
            header("Location: /expenses/edit/{$id}");
            exit;
        }
    }

    public function postDelete($id): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_expenses');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $existing = Expense::getById((int)$id, $firmId);
            if (Expense::delete((int)$id, $firmId)) {
                AuditLog::log($firmId, 'DELETE', 'expense', $id, "Deleted expense #" . ($existing['expense_number'] ?? $id));
                Flash::set('success_msg', 'Expense deleted successfully.');
            } else {
                Flash::set('error_msg', 'Expense record not found.');
            }
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to delete expense.'));
        }
        header('Location: /expenses');
        exit;
    }
}
