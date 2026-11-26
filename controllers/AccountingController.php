<?php
/**
 * Double-Entry Accounting & Bank Reconciliation Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Accounting.php';
require_once __DIR__ . '/../models/BankReconciliation.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AccountingController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_ACCOUNTING') || !ENABLE_ACCOUNTING) {
            Flash::set('info_msg', 'Accounting module is currently disabled in configuration.');
            header('Location: /dashboard');
            exit;
        }
    }

    public function index(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $accounts = Accounting::getAccounts($firmId);

        view('accounting/coa', [
            'title' => 'Chart of Accounts & Accounting',
            'accounts' => $accounts,
            'activeMenu' => 'accounting'
        ]);
    }

    public function postCreateAccount(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $acc = Accounting::createAccount($firmId, [
                'code' => $_POST['code'] ?? '',
                'name' => $_POST['name'] ?? '',
                'account_type' => $_POST['account_type'] ?? 'expense',
                'opening_balance' => Security::parseCleanFloat($_POST['opening_balance'] ?? 0),
                'opening_side' => $_POST['opening_side'] ?? ''
            ]);
            AuditLog::log($firmId, 'CREATE', 'chart_of_account', $acc['id'], "Added COA {$acc['code']} - {$acc['name']}");
            Flash::set('success_msg', "Account \"{$acc['code']} - {$acc['name']}\" added!");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to add account.'));
        }
        header('Location: /accounting');
        exit;
    }

    public function postBackfill(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $res = Accounting::backfillMissingJournals($firmId);
            AuditLog::log($firmId, 'BACKFILL', 'accounting', null, json_encode($res));
            Flash::set('success_msg', "Synced historical journals: {$res['invoices']} invoices, {$res['payments']} payments, {$res['expenses']} expenses posted.");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to backfill historical journals.'));
        }
        header('Location: /accounting/journals');
        exit;
    }

    public function listJournals(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $filters = [
            'source_type' => trim($_GET['source_type'] ?? ''),
            'from_date' => trim($_GET['from_date'] ?? ''),
            'to_date' => trim($_GET['to_date'] ?? '')
        ];
        $entries = Accounting::getJournalEntries($firmId, $filters, 100);

        view('accounting/journals', [
            'title' => 'Journal Entries',
            'entries' => $entries,
            'filters' => $filters,
            'activeMenu' => 'accounting'
        ]);
    }

    public function getCreateJournal(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $accounts = Accounting::getAccounts($firmId, true);
        $nextEntryNumber = Accounting::getOrInitFirmSequence($firmId, 'journal', 'journal_entries', 'entry_number') + 1;

        view('accounting/journal_form', [
            'title' => 'Post Manual Journal Entry',
            'accounts' => $accounts,
            'nextEntryNumber' => Accounting::formatDocumentNumber($nextEntryNumber),
            'activeMenu' => 'accounting'
        ]);
    }

    public function postCreateJournal(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $accIds = $_POST['account_id'] ?? [];
            $descs = $_POST['line_description'] ?? [];
            $debits = $_POST['debit'] ?? [];
            $credits = $_POST['credit'] ?? [];

            $lines = [];
            if (is_array($accIds)) {
                foreach ($accIds as $i => $rawAccId) {
                    $aid = (int)$rawAccId;
                    if ($aid <= 0) continue;
                    $lines[] = [
                        'account_id' => $aid,
                        'description' => trim((string)($descs[$i] ?? '')),
                        'debit' => Security::parseCleanFloat($debits[$i] ?? 0),
                        'credit' => Security::parseCleanFloat($credits[$i] ?? 0)
                    ];
                }
            }

            $je = Accounting::createJournalEntry([
                'firm_id' => $firmId,
                'entry_number' => $_POST['entry_number'] ?? null,
                'entry_date' => $_POST['entry_date'] ?? date('Y-m-d'),
                'source_type' => 'manual',
                'reference_no' => $_POST['reference_no'] ?? null,
                'narration' => $_POST['narration'] ?? 'Manual journal entry',
                'created_by' => $user ? (int)$user['id'] : null
            ], $lines);

            AuditLog::log($firmId, 'CREATE', 'journal_entry', $je['id'], "Posted manual JE #{$je['entry_number']} total {$je['total_debit']}");
            Flash::set('success_msg', "Journal Entry #{$je['entry_number']} posted!");
            header('Location: /accounting/journals');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to post journal entry.'));
            header('Location: /accounting/journals/create');
            exit;
        }
    }

    public function postReverseJournal(array $params): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();
        $id = (int)($params['id'] ?? 0);

        try {
            $rev = Accounting::reverseJournalEntry($firmId, $id, 'Manual reversal', $user ? (int)$user['id'] : null);
            if ($rev) {
                AuditLog::log($firmId, 'REVERSE', 'journal_entry', $id, "Reversed JE #{$id} with #{$rev['entry_number']}");
                Flash::set('success_msg', "Reversal entry #{$rev['entry_number']} created.");
            } else {
                Flash::set('error_msg', 'Journal entry not found or already reversed.');
            }
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to reverse journal entry.'));
        }
        header('Location: /accounting/journals');
        exit;
    }

    public function getTrialBalance(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $asOf = !empty($_GET['as_of']) ? trim($_GET['as_of']) : date('Y-m-d');

        $tb = Accounting::getTrialBalance((int)$firm['id'], $asOf);

        view('accounting/trial_balance', [
            'title' => 'Trial Balance',
            'tb' => $tb,
            'asOf' => $asOf,
            'activeMenu' => 'accounting'
        ]);
    }

    public function getProfitAndLoss(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $fromDate = !empty($_GET['from_date']) ? trim($_GET['from_date']) : '';
        $toDate = !empty($_GET['to_date']) ? trim($_GET['to_date']) : '';

        $pnl = Accounting::getProfitAndLoss((int)$firm['id'], $fromDate ?: null, $toDate ?: null);

        view('accounting/profit_loss', [
            'title' => 'Profit & Loss Statement',
            'pnl' => $pnl,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'activeMenu' => 'accounting'
        ]);
    }

    public function getBalanceSheet(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $asOf = !empty($_GET['as_of']) ? trim($_GET['as_of']) : date('Y-m-d');

        $bs = Accounting::getBalanceSheet((int)$firm['id'], $asOf);

        view('accounting/balance_sheet', [
            'title' => 'Balance Sheet',
            'bs' => $bs,
            'asOf' => $asOf,
            'activeMenu' => 'accounting'
        ]);
    }

    public function getCashFlow(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $fromDate = !empty($_GET['from_date']) ? trim($_GET['from_date']) : '';
        $toDate = !empty($_GET['to_date']) ? trim($_GET['to_date']) : '';

        $cf = Accounting::getCashFlowStatement((int)$firm['id'], $fromDate ?: null, $toDate ?: null);

        view('accounting/cash_flow', [
            'title' => 'Cash Flow Statement (Direct Method)',
            'cf' => $cf,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'activeMenu' => 'accounting'
        ]);
    }

    public function listBankReconciliation(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $filters = ['match_status' => trim($_GET['match_status'] ?? '')];
        $lines = BankReconciliation::getStatementLines($firmId, $filters);
        $summary = BankReconciliation::getSummary($firmId);

        view('accounting/reconciliation', [
            'title' => 'Bank Reconciliation (CSV Import)',
            'lines' => $lines,
            'summary' => $summary,
            'filters' => $filters,
            'activeMenu' => 'accounting'
        ]);
    }

    public function postImportCsv(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            if (!isset($_FILES['statement_csv']) || $_FILES['statement_csv']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Please select a valid CSV bank statement file.');
            }
            $ext = strtolower(pathinfo((string)$_FILES['statement_csv']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['csv', 'txt'], true)) {
                throw new Exception('Only CSV files (.csv / .txt) are supported.');
            }
            if ($_FILES['statement_csv']['size'] > 2 * 1024 * 1024) {
                throw new Exception('CSV file exceeds 2MB size limit.');
            }

            $res = BankReconciliation::importCsv($firmId, $_FILES['statement_csv']['tmp_name'], $user ? (int)$user['id'] : null);
            AuditLog::log($firmId, 'IMPORT', 'bank_statement', null, "Imported {$res['imported_count']} CSV lines");
            Flash::set('success_msg', "Imported {$res['imported_count']} statement lines successfully.");
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to import bank statement CSV.'));
        }
        header('Location: /accounting/reconciliation');
        exit;
    }

    public function postAddManualLine(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $line = BankReconciliation::addStatementLine($firmId, [
                'txn_date' => $_POST['txn_date'] ?? date('Y-m-d'),
                'description' => $_POST['description'] ?? '',
                'reference_no' => $_POST['reference_no'] ?? null,
                'debit_amount' => Security::parseCleanFloat($_POST['debit_amount'] ?? 0),
                'credit_amount' => Security::parseCleanFloat($_POST['credit_amount'] ?? 0),
                'created_by' => $user ? (int)$user['id'] : null
            ]);
            AuditLog::log($firmId, 'CREATE', 'bank_statement_line', $line['id'], "Added bank statement row");
            Flash::set('success_msg', 'Statement line added.');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to add statement line.'));
        }
        header('Location: /accounting/reconciliation');
        exit;
    }

    public function postMatchLine(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $lineId = (int)($_POST['line_id'] ?? 0);
            $target = trim((string)($_POST['match_target'] ?? ''));
            if (strpos($target, ':') === false) {
                throw new Exception('Select a matching payment or expense entry.');
            }
            list($sType, $sId) = explode(':', $target, 2);
            BankReconciliation::matchLine($firmId, $lineId, $sType, (int)$sId);
            AuditLog::log($firmId, 'RECONCILE', 'bank_statement_line', $lineId, "Matched with {$sType}:{$sId}");
            Flash::set('success_msg', 'Bank transaction matched with books.');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to match transaction.'));
        }
        header('Location: /accounting/reconciliation');
        exit;
    }

    public function postSetLineStatus(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_accounting');
        $firm = Auth::requireActiveFirm();
        $lineId = (int)($_POST['line_id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? 'unmatched'));
        BankReconciliation::setLineStatus((int)$firm['id'], $lineId, $status);
        Flash::set('success_msg', 'Statement row status updated.');
        header('Location: /accounting/reconciliation');
        exit;
    }

    public function apiCandidates(array $params): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_accounting', true);
        $firm = Auth::requireActiveFirm();
        $candidates = BankReconciliation::getCandidateMatches((int)$firm['id'], (int)($params['id'] ?? 0));
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'candidates' => $candidates]);
        exit;
    }
}
