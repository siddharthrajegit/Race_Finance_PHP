<?php
/**
 * Double-Entry Accounting Engine
 * Implements Firm Chart of Accounts, Immutable Journals & Reversals, Retroactive Backfill,
 * Trial Balance, Profit & Loss, Balance Sheet, and Direct Cash Flow Statement.
 */

require_once __DIR__ . '/Model.php';

class Accounting extends Model {
    public const DEFAULT_ACCOUNTS = [
        ['code' => '1000', 'name' => 'Cash in Hand',                    'account_type' => 'asset',     'opening_side' => 'debit'],
        ['code' => '1100', 'name' => 'Bank Account',                    'account_type' => 'asset',     'opening_side' => 'debit'],
        ['code' => '1200', 'name' => 'Accounts Receivable (Debtors)',   'account_type' => 'asset',     'opening_side' => 'debit'],
        ['code' => '1300', 'name' => 'Inventory Asset',                 'account_type' => 'asset',     'opening_side' => 'debit'],
        ['code' => '1400', 'name' => 'GST Input Tax Credit',            'account_type' => 'asset',     'opening_side' => 'debit'],
        ['code' => '2000', 'name' => 'Accounts Payable (Creditors)',    'account_type' => 'liability', 'opening_side' => 'credit'],
        ['code' => '2100', 'name' => 'CGST Payable',                    'account_type' => 'liability', 'opening_side' => 'credit'],
        ['code' => '2110', 'name' => 'SGST Payable',                    'account_type' => 'liability', 'opening_side' => 'credit'],
        ['code' => '2120', 'name' => 'IGST Payable',                    'account_type' => 'liability', 'opening_side' => 'credit'],
        ['code' => '2130', 'name' => 'Other Current Liabilities',       'account_type' => 'liability', 'opening_side' => 'credit'],
        ['code' => '3000', 'name' => "Owner's Equity / Capital",        'account_type' => 'equity',    'opening_side' => 'credit'],
        ['code' => '3100', 'name' => 'Retained Earnings',               'account_type' => 'equity',    'opening_side' => 'credit'],
        ['code' => '4000', 'name' => 'Sales Revenue',                   'account_type' => 'income',    'opening_side' => 'credit'],
        ['code' => '4100', 'name' => 'Other Income & Rounding',         'account_type' => 'income',    'opening_side' => 'credit'],
        ['code' => '5000', 'name' => 'Purchases / Cost of Goods',       'account_type' => 'expense',   'opening_side' => 'debit'],
        ['code' => '5100', 'name' => 'Discounts & Rounding Allowed',    'account_type' => 'expense',   'opening_side' => 'debit'],
        ['code' => '5200', 'name' => 'Rent Expense',                    'account_type' => 'expense',   'opening_side' => 'debit'],
        ['code' => '5300', 'name' => 'Salaries & Wages',                'account_type' => 'expense',   'opening_side' => 'debit'],
        ['code' => '5400', 'name' => 'Utilities & Electricity',         'account_type' => 'expense',   'opening_side' => 'debit'],
        ['code' => '5500', 'name' => 'Office Supplies',                 'account_type' => 'expense',   'opening_side' => 'debit'],
        ['code' => '5900', 'name' => 'General Operating Expenses',      'account_type' => 'expense',   'opening_side' => 'debit']
    ];

    public static function ensureDefaultAccounts(int $firmId): void {
        $chk = self::db()->prepare("SELECT code FROM chart_of_accounts WHERE firm_id = ?");
        $chk->execute([$firmId]);
        $existing = array_flip($chk->fetchAll(PDO::FETCH_COLUMN));

        $ins = self::db()->prepare("
            INSERT INTO chart_of_accounts (firm_id, code, name, account_type, opening_balance, opening_side, is_system, is_active)
            VALUES (?, ?, ?, ?, 0.00, ?, 1, 1)
        ");

        foreach (self::DEFAULT_ACCOUNTS as $acc) {
            if (!isset($existing[$acc['code']])) {
                try {
                    $ins->execute([$firmId, $acc['code'], $acc['name'], $acc['account_type'], $acc['opening_side']]);
                } catch (Throwable $e) {
                    // Ignore duplicate insertion race
                }
            }
        }
    }

    public static function getAccounts(int $firmId, bool $activeOnly = false): array {
        self::ensureDefaultAccounts($firmId);
        $sql = "SELECT * FROM chart_of_accounts WHERE firm_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY code ASC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function getAccountByCode(int $firmId, string $code): ?array {
        self::ensureDefaultAccounts($firmId);
        $stmt = self::db()->prepare("SELECT * FROM chart_of_accounts WHERE firm_id = ? AND code = ? LIMIT 1");
        $stmt->execute([$firmId, trim($code)]);
        return $stmt->fetch() ?: null;
    }

    public static function getAccountById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM chart_of_accounts WHERE id = ? AND firm_id = ? LIMIT 1");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function createAccount(int $firmId, array $data): array {
        self::ensureDefaultAccounts($firmId);
        $code = preg_replace('/[^a-zA-Z0-9_\-]/', '', trim((string)($data['code'] ?? '')));
        $name = trim((string)($data['name'] ?? ''));
        $type = in_array($data['account_type'] ?? '', ['asset', 'liability', 'equity', 'income', 'expense'], true)
            ? $data['account_type']
            : 'expense';
        $side = in_array($data['opening_side'] ?? '', ['debit', 'credit'], true)
            ? $data['opening_side']
            : (in_array($type, ['asset', 'expense'], true) ? 'debit' : 'credit');
        $opening = round(max(0.0, (float)($data['opening_balance'] ?? 0)), 2);

        if ($code === '' || $name === '') {
            throw new Exception("Account code and name are required.");
        }

        $chk = self::db()->prepare("SELECT id FROM chart_of_accounts WHERE firm_id = ? AND code = ?");
        $chk->execute([$firmId, $code]);
        if ($chk->fetch()) {
            throw new Exception("Account code '{$code}' already exists.");
        }

        self::db()->prepare("
            INSERT INTO chart_of_accounts (firm_id, code, name, account_type, parent_code, opening_balance, opening_side, is_system, is_active)
            VALUES (?, ?, ?, ?, ?, 0.00, ?, 0, 1)
        ")->execute([
            $firmId,
            $code,
            $name,
            $type,
            !empty($data['parent_code']) ? trim((string)$data['parent_code']) : null,
            $side
        ]);

        $createdAcc = self::getAccountByCode($firmId, $code);

        // Post balanced opening entry against Retained Earnings (3100) so Trial Balance & Balance Sheet stay balanced
        if ($opening > 0 && $code !== '3100') {
            $lines = ($side === 'debit')
                ? [
                    ['account_id' => (int)$createdAcc['id'], 'description' => "Opening balance for {$name}", 'debit' => $opening, 'credit' => 0],
                    ['account_code' => '3100', 'description' => "Opening balance equity offset ({$code})", 'debit' => 0, 'credit' => $opening]
                ]
                : [
                    ['account_code' => '3100', 'description' => "Opening balance equity offset ({$code})", 'debit' => $opening, 'credit' => 0],
                    ['account_id' => (int)$createdAcc['id'], 'description' => "Opening balance for {$name}", 'debit' => 0, 'credit' => $opening]
                ];
            self::createJournalEntry([
                'firm_id' => $firmId,
                'entry_date' => date('Y-m-d'),
                'source_type' => 'opening',
                'source_id' => (int)$createdAcc['id'],
                'narration' => "Opening balance for {$code} - {$name}"
            ], $lines);
        }

        return $createdAcc;
    }

    public static function createJournalEntry(array $entryData, array $lines): array {
        return self::transaction(function ($db) use ($entryData, $lines) {
            $firmId = (int)$entryData['firm_id'];
            self::ensureDefaultAccounts($firmId);

            if (count($lines) < 2) {
                throw new Exception("A double-entry journal must contain at least 2 lines.");
            }

            $normalizedLines = [];
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($lines as $line) {
                $debit = round(max(0.0, (float)($line['debit'] ?? 0)), 2);
                $credit = round(max(0.0, (float)($line['credit'] ?? 0)), 2);
                if ($debit == 0.0 && $credit == 0.0) {
                    continue;
                }
                if ($debit > 0 && $credit > 0) {
                    throw new Exception("A journal line cannot carry both non-zero debit and credit.");
                }

                $acc = null;
                if (!empty($line['account_id'])) {
                    $acc = self::getAccountById((int)$line['account_id'], $firmId);
                } elseif (!empty($line['account_code'])) {
                    $acc = self::getAccountByCode($firmId, (string)$line['account_code']);
                }
                if (!$acc) {
                    throw new Exception("Invalid Chart of Accounts entry for journal line.");
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
                $normalizedLines[] = [
                    'account_id' => (int)$acc['id'],
                    'description' => !empty($line['description']) ? trim((string)$line['description']) : null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'party_id' => !empty($line['party_id']) ? (int)$line['party_id'] : null
                ];
            }

            if (count($normalizedLines) < 2) {
                throw new Exception("Journal entry requires at least two non-zero lines.");
            }

            $totalDebit = round($totalDebit, 2);
            $totalCredit = round($totalCredit, 2);

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("Unbalanced journal entry rejected: Debits ({$totalDebit}) != Credits ({$totalCredit}).");
            }
            if ($totalDebit <= 0) {
                throw new Exception("Journal entry total must be greater than zero.");
            }

            $entryNumber = self::allocateFirmSequence(
                $firmId,
                'journal',
                $entryData['entry_number'] ?? null,
                'journal_entries',
                'entry_number'
            );

            $db->prepare("
                INSERT INTO journal_entries (
                    firm_id, entry_number, entry_date, source_type, source_id,
                    reference_no, narration, total_debit, total_credit, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $firmId,
                $entryNumber,
                !empty($entryData['entry_date']) ? $entryData['entry_date'] : date('Y-m-d'),
                $entryData['source_type'] ?? 'manual',
                !empty($entryData['source_id']) ? (int)$entryData['source_id'] : null,
                !empty($entryData['reference_no']) ? trim((string)$entryData['reference_no']) : null,
                !empty($entryData['narration']) ? trim((string)$entryData['narration']) : null,
                $totalDebit,
                $totalCredit,
                !empty($entryData['created_by']) ? (int)$entryData['created_by'] : null
            ]);

            $entryId = (int)$db->lastInsertId();

            $lineStmt = $db->prepare("
                INSERT INTO journal_lines (journal_entry_id, firm_id, account_id, description, debit, credit, party_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($normalizedLines as $nl) {
                $lineStmt->execute([
                    $entryId,
                    $firmId,
                    $nl['account_id'],
                    $nl['description'],
                    $nl['debit'],
                    $nl['credit'],
                    $nl['party_id']
                ]);
            }

            return self::getJournalById($entryId, $firmId);
        });
    }

    public static function getJournalById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM journal_entries WHERE id = ? AND firm_id = ? LIMIT 1");
        $stmt->execute([$id, $firmId]);
        $entry = $stmt->fetch();
        if (!$entry) return null;

        $lStmt = self::db()->prepare("
            SELECT jl.*, coa.code AS account_code, coa.name AS account_name, coa.account_type
            FROM journal_lines jl
            JOIN chart_of_accounts coa ON coa.id = jl.account_id
            WHERE jl.journal_entry_id = ?
            ORDER BY jl.debit DESC, jl.id ASC
        ");
        $lStmt->execute([$id]);
        $entry['lines'] = $lStmt->fetchAll();
        return $entry;
    }

    public static function getJournalEntries(int $firmId, array $filters = [], int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM journal_entries WHERE firm_id = ?";
        $params = [$firmId];

        if (!empty($filters['source_type'])) {
            $sql .= " AND source_type = ?";
            $params[] = $filters['source_type'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND entry_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND entry_date <= ?";
            $params[] = $filters['to_date'];
        }

        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);
        $sql .= " ORDER BY entry_date DESC, id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();

        $lStmt = self::db()->prepare("
            SELECT jl.*, coa.code AS account_code, coa.name AS account_name
            FROM journal_lines jl
            JOIN chart_of_accounts coa ON coa.id = jl.account_id
            WHERE jl.journal_entry_id = ?
            ORDER BY jl.debit DESC, jl.id ASC
        ");
        foreach ($entries as &$e) {
            $lStmt->execute([(int)$e['id']]);
            $e['lines'] = $lStmt->fetchAll();
        }
        unset($e);

        return $entries;
    }

    /**
     * Post an immutable offsetting reversal entry instead of mutating historical journal lines.
     */
    public static function reverseJournalEntry(int $firmId, int $journalId, string $reason = 'Reversed', ?int $actorId = null): ?array {
        return self::transaction(function ($db) use ($firmId, $journalId, $reason, $actorId) {
            $orig = self::getJournalById($journalId, $firmId);
            if (!$orig || !empty($orig['is_reversed'])) {
                return null;
            }

            $revLines = [];
            foreach ($orig['lines'] as $l) {
                $revLines[] = [
                    'account_id' => (int)$l['account_id'],
                    'description' => 'Reversal: ' . ($l['description'] ?: $orig['narration']),
                    'debit' => (float)$l['credit'],
                    'credit' => (float)$l['debit'],
                    'party_id' => $l['party_id'] ? (int)$l['party_id'] : null
                ];
            }

            $revEntry = self::createJournalEntry([
                'firm_id' => $firmId,
                'entry_date' => date('Y-m-d'),
                'source_type' => 'reversal',
                'source_id' => $journalId,
                'reference_no' => $orig['entry_number'],
                'narration' => $reason . " (Reverses JE #{$orig['entry_number']})",
                'created_by' => $actorId
            ], $revLines);

            $db->prepare("UPDATE journal_entries SET is_reversed = 1, reversed_by_entry_id = ? WHERE id = ? AND firm_id = ?")
               ->execute([(int)$revEntry['id'], $journalId, $firmId]);

            return $revEntry;
        });
    }

    /**
     * Reverse any active (non-reversed) journals tied to a specific source entity.
     */
    public static function reverseSourceJournal(int $firmId, string $sourceType, int $sourceId, string $reason = 'Updated or deleted'): void {
        if ($sourceType === 'invoice') {
            $pStmt = self::db()->prepare("SELECT id FROM payments WHERE invoice_id = ? AND firm_id = ?");
            $pStmt->execute([$sourceId, $firmId]);
            foreach ($pStmt->fetchAll(PDO::FETCH_COLUMN) as $pid) {
                self::reverseSourceJournal($firmId, 'payment', (int)$pid, $reason);
            }
        }

        $stmt = self::db()->prepare("
            SELECT id FROM journal_entries
            WHERE firm_id = ? AND source_type = ? AND source_id = ? AND is_reversed = 0
        ");
        $stmt->execute([$firmId, $sourceType, $sourceId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $jeId) {
            self::reverseJournalEntry($firmId, (int)$jeId, $reason);
        }
    }

    /**
     * Post balanced double-entry journal for a Sales or Purchase Invoice (+ linked inline payment if present).
     */
    public static function postInvoiceJournal(array $inv): ?array {
        $firmId = (int)$inv['firm_id'];
        $invId = (int)$inv['id'];
        $grand = round((float)($inv['grand_total'] ?? 0), 2);
        if ($grand <= 0) {
            return null;
        }

        self::ensureDefaultAccounts($firmId);

        // Idempotency guard: skip if active journal already exists for this invoice
        $exists = self::db()->prepare("
            SELECT id FROM journal_entries
            WHERE firm_id = ? AND source_type = 'invoice' AND source_id = ? AND is_reversed = 0
            LIMIT 1
        ");
        $exists->execute([$firmId, $invId]);
        $existingJeId = $exists->fetchColumn();
        if ($existingJeId) {
            // Also ensure linked inline payments are posted
            self::postLinkedInvoicePayments($firmId, $invId);
            return self::getJournalById((int)$existingJeId, $firmId);
        }

        $type = $inv['type'] ?? 'sale';
        $partyId = !empty($inv['party_id']) ? (int)$inv['party_id'] : null;
        $taxable = round((float)($inv['taxable_amount'] ?? 0), 2);
        $cgst = round((float)($inv['cgst_amount'] ?? 0), 2);
        $sgst = round((float)($inv['sgst_amount'] ?? 0), 2);
        $igst = round((float)($inv['igst_amount'] ?? 0), 2);
        $taxTotal = $cgst + $sgst + $igst;

        // Reconcile any small rounding delta so debits === credits strictly
        $roundDelta = round($grand - ($taxable + $taxTotal), 2);
        $lines = [];

        if ($type === 'sale') {
            $lines[] = [
                'account_code' => '1200',
                'description' => "Sales Invoice #{$inv['invoice_number']} - {$inv['party_name']}",
                'debit' => $grand,
                'credit' => 0,
                'party_id' => $partyId
            ];
            if ($taxable > 0) {
                $lines[] = ['account_code' => '4000', 'description' => 'Sales Revenue', 'debit' => 0, 'credit' => $taxable];
            }
            if ($cgst > 0) $lines[] = ['account_code' => '2100', 'description' => 'CGST Output', 'debit' => 0, 'credit' => $cgst];
            if ($sgst > 0) $lines[] = ['account_code' => '2110', 'description' => 'SGST Output', 'debit' => 0, 'credit' => $sgst];
            if ($igst > 0) $lines[] = ['account_code' => '2120', 'description' => 'IGST Output', 'debit' => 0, 'credit' => $igst];
            if ($roundDelta > 0) {
                $lines[] = ['account_code' => '4100', 'description' => 'Rounding Adjustment', 'debit' => 0, 'credit' => $roundDelta];
            } elseif ($roundDelta < 0) {
                $lines[] = ['account_code' => '5100', 'description' => 'Rounding / Discount Adjustment', 'debit' => abs($roundDelta), 'credit' => 0];
            }
        } else {
            if ($taxable > 0) {
                $lines[] = ['account_code' => '5000', 'description' => "Purchase Bill #{$inv['invoice_number']}", 'debit' => $taxable, 'credit' => 0];
            }
            if ($taxTotal > 0) {
                $lines[] = ['account_code' => '1400', 'description' => 'GST Input Tax Credit', 'debit' => $taxTotal, 'credit' => 0];
            }
            if ($roundDelta > 0) {
                $lines[] = ['account_code' => '5100', 'description' => 'Rounding Adjustment', 'debit' => $roundDelta, 'credit' => 0];
            } elseif ($roundDelta < 0) {
                $lines[] = ['account_code' => '4100', 'description' => 'Rounding Credit', 'debit' => 0, 'credit' => abs($roundDelta)];
            }
            $lines[] = [
                'account_code' => '2000',
                'description' => "Purchase Payable #{$inv['invoice_number']} - {$inv['party_name']}",
                'debit' => 0,
                'credit' => $grand,
                'party_id' => $partyId
            ];
        }

        $je = self::createJournalEntry([
            'firm_id' => $firmId,
            'entry_date' => $inv['invoice_date'] ?? date('Y-m-d'),
            'source_type' => 'invoice',
            'source_id' => $invId,
            'reference_no' => $inv['invoice_number'],
            'narration' => ($type === 'sale' ? 'Sales Invoice' : 'Purchase Bill') . " #{$inv['invoice_number']} ({$inv['party_name']})"
        ], $lines);

        self::postLinkedInvoicePayments($firmId, $invId);
        return $je;
    }

    private static function postLinkedInvoicePayments(int $firmId, int $invoiceId): void {
        $pStmt = self::db()->prepare("SELECT * FROM payments WHERE invoice_id = ? AND firm_id = ?");
        $pStmt->execute([$invoiceId, $firmId]);
        foreach ($pStmt->fetchAll() as $pay) {
            self::postPaymentJournal($pay);
        }
    }

    public static function postPaymentJournal(array $payment): ?array {
        $firmId = (int)$payment['firm_id'];
        $payId = (int)$payment['id'];
        $amount = round((float)($payment['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return null;
        }

        $exists = self::db()->prepare("
            SELECT id FROM journal_entries
            WHERE firm_id = ? AND source_type = 'payment' AND source_id = ? AND is_reversed = 0
            LIMIT 1
        ");
        $exists->execute([$firmId, $payId]);
        if ($existingId = $exists->fetchColumn()) {
            return self::getJournalById((int)$existingId, $firmId);
        }

        $cashOrBank = (strtolower((string)($payment['payment_mode'] ?? 'cash')) === 'cash') ? '1000' : '1100';
        $partyId = !empty($payment['party_id']) ? (int)$payment['party_id'] : null;
        $isIn = (($payment['type'] ?? 'payment_in') === 'payment_in');

        $lines = $isIn
            ? [
                ['account_code' => $cashOrBank, 'description' => "Receipt #{$payment['payment_number']} ({$payment['payment_mode']})", 'debit' => $amount, 'credit' => 0],
                ['account_code' => '1200', 'description' => "Customer Receipt #{$payment['payment_number']}", 'debit' => 0, 'credit' => $amount, 'party_id' => $partyId]
            ]
            : [
                ['account_code' => '2000', 'description' => "Supplier Payment #{$payment['payment_number']}", 'debit' => $amount, 'credit' => 0, 'party_id' => $partyId],
                ['account_code' => $cashOrBank, 'description' => "Payment #{$payment['payment_number']} ({$payment['payment_mode']})", 'debit' => 0, 'credit' => $amount]
            ];

        return self::createJournalEntry([
            'firm_id' => $firmId,
            'entry_date' => $payment['payment_date'] ?? date('Y-m-d'),
            'source_type' => 'payment',
            'source_id' => $payId,
            'reference_no' => $payment['payment_number'],
            'narration' => ($isIn ? 'Payment Receipt' : 'Payment Out') . " #{$payment['payment_number']}"
        ], $lines);
    }

    public static function postExpenseJournal(array $expense): ?array {
        $firmId = (int)$expense['firm_id'];
        $expId = (int)$expense['id'];
        $amount = round((float)($expense['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return null;
        }

        $exists = self::db()->prepare("
            SELECT id FROM journal_entries
            WHERE firm_id = ? AND source_type = 'expense' AND source_id = ? AND is_reversed = 0
            LIMIT 1
        ");
        $exists->execute([$firmId, $expId]);
        if ($existingId = $exists->fetchColumn()) {
            return self::getJournalById((int)$existingId, $firmId);
        }

        self::ensureDefaultAccounts($firmId);

        $catStmt = self::db()->prepare("SELECT account_code FROM expense_categories WHERE firm_id = ? AND name = ? LIMIT 1");
        $catStmt->execute([$firmId, $expense['category'] ?? '']);
        $accCode = $catStmt->fetchColumn() ?: '5900';
        if (!self::getAccountByCode($firmId, (string)$accCode)) {
            $accCode = '5900';
        }

        $cashOrBank = (strtolower((string)($expense['payment_mode'] ?? 'cash')) === 'cash') ? '1000' : '1100';

        return self::createJournalEntry([
            'firm_id' => $firmId,
            'entry_date' => $expense['expense_date'] ?? date('Y-m-d'),
            'source_type' => 'expense',
            'source_id' => $expId,
            'reference_no' => $expense['expense_number'],
            'narration' => "Expense #{$expense['expense_number']}: {$expense['category']}" . (!empty($expense['payee']) ? " ({$expense['payee']})" : ''),
            'created_by' => !empty($expense['created_by']) ? (int)$expense['created_by'] : null
        ], [
            ['account_code' => $accCode, 'description' => $expense['category'], 'debit' => $amount, 'credit' => 0],
            ['account_code' => $cashOrBank, 'description' => "Paid via {$expense['payment_mode']}", 'debit' => 0, 'credit' => $amount]
        ]);
    }

    /**
     * Retroactively post journals for any pre-existing historical invoices, payments, and expenses
     * that were created before double-entry accounting was enabled.
     */
    public static function backfillMissingJournals(int $firmId): array {
        self::ensureDefaultAccounts($firmId);
        $posted = ['invoices' => 0, 'payments' => 0, 'expenses' => 0];

        $invStmt = self::db()->prepare("
            SELECT i.* FROM invoices i
            WHERE i.firm_id = ?
              AND NOT EXISTS (
                  SELECT 1 FROM journal_entries je
                  WHERE je.firm_id = i.firm_id AND je.source_type = 'invoice' AND je.source_id = i.id AND je.is_reversed = 0
              )
            ORDER BY i.invoice_date ASC, i.id ASC
        ");
        $invStmt->execute([$firmId]);
        foreach ($invStmt->fetchAll() as $inv) {
            if (self::postInvoiceJournal($inv)) {
                $posted['invoices']++;
            }
        }

        $payStmt = self::db()->prepare("
            SELECT p.* FROM payments p
            WHERE p.firm_id = ?
              AND NOT EXISTS (
                  SELECT 1 FROM journal_entries je
                  WHERE je.firm_id = p.firm_id AND je.source_type = 'payment' AND je.source_id = p.id AND je.is_reversed = 0
              )
            ORDER BY p.payment_date ASC, p.id ASC
        ");
        $payStmt->execute([$firmId]);
        foreach ($payStmt->fetchAll() as $pay) {
            if (self::postPaymentJournal($pay)) {
                $posted['payments']++;
            }
        }

        try {
            $expStmt = self::db()->prepare("
                SELECT e.* FROM expenses e
                WHERE e.firm_id = ?
                  AND NOT EXISTS (
                      SELECT 1 FROM journal_entries je
                      WHERE je.firm_id = e.firm_id AND je.source_type = 'expense' AND je.source_id = e.id AND je.is_reversed = 0
                  )
                ORDER BY e.expense_date ASC, e.id ASC
            ");
            $expStmt->execute([$firmId]);
            foreach ($expStmt->fetchAll() as $exp) {
                if (self::postExpenseJournal($exp)) {
                    $posted['expenses']++;
                }
            }
        } catch (Throwable $e) {
            // Expenses table might be empty or optional
        }

        return $posted;
    }

    public static function getTrialBalance(int $firmId, ?string $asOfDate = null): array {
        self::ensureDefaultAccounts($firmId);

        $accounts = self::getAccounts($firmId);
        $dateClause = $asOfDate ? " AND je.entry_date <= ?" : "";
        $params = [$firmId];
        if ($asOfDate) $params[] = $asOfDate;

        $stmt = self::db()->prepare("
            SELECT jl.account_id,
                   COALESCE(SUM(jl.debit), 0) AS total_debit,
                   COALESCE(SUM(jl.credit), 0) AS total_credit
            FROM journal_lines jl
            JOIN journal_entries je ON je.id = jl.journal_entry_id
            WHERE jl.firm_id = ? {$dateClause}
            GROUP BY jl.account_id
        ");
        $stmt->execute($params);
        $sums = [];
        foreach ($stmt->fetchAll() as $r) {
            $sums[(int)$r['account_id']] = $r;
        }

        $rows = [];
        $grandDebit = 0.0;
        $grandCredit = 0.0;

        foreach ($accounts as $acc) {
            $aid = (int)$acc['id'];
            $open = (float)$acc['opening_balance'];
            $openDebit = ($acc['opening_side'] === 'debit') ? $open : 0.0;
            $openCredit = ($acc['opening_side'] === 'credit') ? $open : 0.0;

            $dr = $openDebit + (float)($sums[$aid]['total_debit'] ?? 0.0);
            $cr = $openCredit + (float)($sums[$aid]['total_credit'] ?? 0.0);
            $net = round($dr - $cr, 2);

            $netDebit = $net > 0 ? $net : 0.0;
            $netCredit = $net < 0 ? abs($net) : 0.0;

            if ($dr == 0.0 && $cr == 0.0) {
                continue;
            }

            $grandDebit += $netDebit;
            $grandCredit += $netCredit;

            $rows[] = [
                'account_id' => $aid,
                'code' => $acc['code'],
                'name' => $acc['name'],
                'account_type' => $acc['account_type'],
                'gross_debit' => round($dr, 2),
                'gross_credit' => round($cr, 2),
                'net_debit' => round($netDebit, 2),
                'net_credit' => round($netCredit, 2)
            ];
        }

        return [
            'rows' => $rows,
            'total_debit' => round($grandDebit, 2),
            'total_credit' => round($grandCredit, 2),
            'is_balanced' => abs($grandDebit - $grandCredit) < 0.02
        ];
    }

    public static function getProfitAndLoss(int $firmId, ?string $fromDate = null, ?string $toDate = null): array {
        self::ensureDefaultAccounts($firmId);

        $where = "jl.firm_id = ? AND coa.account_type IN ('income', 'expense')";
        $params = [$firmId];
        if ($fromDate) {
            $where .= " AND je.entry_date >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $where .= " AND je.entry_date <= ?";
            $params[] = $toDate;
        }

        $stmt = self::db()->prepare("
            SELECT coa.id, coa.code, coa.name, coa.account_type,
                   COALESCE(SUM(jl.debit), 0) AS total_debit,
                   COALESCE(SUM(jl.credit), 0) AS total_credit
            FROM journal_lines jl
            JOIN journal_entries je ON je.id = jl.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jl.account_id
            WHERE {$where}
            GROUP BY coa.id, coa.code, coa.name, coa.account_type
            ORDER BY coa.code ASC
        ");
        $stmt->execute($params);

        $incomeRows = [];
        $expenseRows = [];
        $totalIncome = 0.0;
        $totalExpense = 0.0;

        foreach ($stmt->fetchAll() as $r) {
            if ($r['account_type'] === 'income') {
                $amt = round((float)$r['total_credit'] - (float)$r['total_debit'], 2);
                if (abs($amt) < 0.005) continue;
                $totalIncome += $amt;
                $incomeRows[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amt];
            } else {
                $amt = round((float)$r['total_debit'] - (float)$r['total_credit'], 2);
                if (abs($amt) < 0.005) continue;
                $totalExpense += $amt;
                $expenseRows[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $amt];
            }
        }

        return [
            'income' => $incomeRows,
            'expenses' => $expenseRows,
            'total_income' => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'net_profit' => round($totalIncome - $totalExpense, 2)
        ];
    }

    public static function getBalanceSheet(int $firmId, ?string $asOfDate = null): array {
        self::ensureDefaultAccounts($firmId);

        $accounts = self::getAccounts($firmId);
        $dateClause = $asOfDate ? " AND je.entry_date <= ?" : "";
        $params = [$firmId];
        if ($asOfDate) $params[] = $asOfDate;

        $stmt = self::db()->prepare("
            SELECT jl.account_id,
                   COALESCE(SUM(jl.debit), 0) AS total_debit,
                   COALESCE(SUM(jl.credit), 0) AS total_credit
            FROM journal_lines jl
            JOIN journal_entries je ON je.id = jl.journal_entry_id
            WHERE jl.firm_id = ? {$dateClause}
            GROUP BY jl.account_id
        ");
        $stmt->execute($params);
        $sums = [];
        foreach ($stmt->fetchAll() as $r) {
            $sums[(int)$r['account_id']] = $r;
        }

        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $totalEquity = 0.0;

        foreach ($accounts as $acc) {
            if (!in_array($acc['account_type'], ['asset', 'liability', 'equity'], true)) {
                continue;
            }
            $aid = (int)$acc['id'];
            $open = (float)$acc['opening_balance'];
            $dr = (($acc['opening_side'] === 'debit') ? $open : 0.0) + (float)($sums[$aid]['total_debit'] ?? 0.0);
            $cr = (($acc['opening_side'] === 'credit') ? $open : 0.0) + (float)($sums[$aid]['total_credit'] ?? 0.0);

            if ($acc['account_type'] === 'asset') {
                $bal = round($dr - $cr, 2);
                if (abs($bal) < 0.005) continue;
                $totalAssets += $bal;
                $assets[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            } elseif ($acc['account_type'] === 'liability') {
                $bal = round($cr - $dr, 2);
                if (abs($bal) < 0.005) continue;
                $totalLiabilities += $bal;
                $liabilities[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            } else {
                $bal = round($cr - $dr, 2);
                if (abs($bal) < 0.005) continue;
                $totalEquity += $bal;
                $equity[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            }
        }

        // Current period retained profit/loss up to asOfDate
        $pnl = self::getProfitAndLoss($firmId, null, $asOfDate);
        $netProfit = (float)$pnl['net_profit'];
        if (abs($netProfit) >= 0.005) {
            $equity[] = ['code' => '3999', 'name' => 'Current Period Net Profit / (Loss)', 'balance' => $netProfit];
            $totalEquity += $netProfit;
        }

        $totalLiabEq = round($totalLiabilities + $totalEquity, 2);

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'total_equity' => round($totalEquity, 2),
            'total_liabilities_and_equity' => $totalLiabEq,
            'is_balanced' => abs($totalAssets - $totalLiabEq) < 0.02
        ];
    }

    /**
     * Direct-method Cash Flow Statement derived from actual posted Cash (1000) & Bank (1100) journal movements.
     */
    public static function getCashFlowStatement(int $firmId, ?string $fromDate = null, ?string $toDate = null): array {
        self::ensureDefaultAccounts($firmId);

        // Opening cash + bank before fromDate
        $openWhere = "jl.firm_id = ? AND coa.code IN ('1000', '1100')";
        $openParams = [$firmId];
        if ($fromDate) {
            $openWhere .= " AND je.entry_date < ?";
            $openParams[] = $fromDate;
        } else {
            $openWhere .= " AND 1 = 0"; // Without fromDate, opening comes from COA opening_balance only
        }

        $accStmt = self::db()->prepare("SELECT opening_balance, opening_side FROM chart_of_accounts WHERE firm_id = ? AND code IN ('1000', '1100')");
        $accStmt->execute([$firmId]);
        $openingCash = 0.0;
        foreach ($accStmt->fetchAll() as $ca) {
            $openingCash += ($ca['opening_side'] === 'debit') ? (float)$ca['opening_balance'] : -(float)$ca['opening_balance'];
        }

        $preStmt = self::db()->prepare("
            SELECT COALESCE(SUM(jl.debit - jl.credit), 0)
            FROM journal_lines jl
            JOIN journal_entries je ON je.id = jl.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jl.account_id
            WHERE {$openWhere}
        ");
        $preStmt->execute($openParams);
        $openingCash += (float)$preStmt->fetchColumn();

        // Period cash/bank lines
        $pWhere = "jl.firm_id = ? AND coa.code IN ('1000', '1100')";
        $pParams = [$firmId];
        if ($fromDate) {
            $pWhere .= " AND je.entry_date >= ?";
            $pParams[] = $fromDate;
        }
        if ($toDate) {
            $pWhere .= " AND je.entry_date <= ?";
            $pParams[] = $toDate;
        }

        $stmt = self::db()->prepare("
            SELECT jl.debit, jl.credit, je.source_type, je.narration, je.entry_date, coa.code AS cash_code
            FROM journal_lines jl
            JOIN journal_entries je ON je.id = jl.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jl.account_id
            WHERE {$pWhere}
            ORDER BY je.entry_date ASC, je.id ASC
        ");
        $stmt->execute($pParams);

        $operatingIn = 0.0;
        $operatingOut = 0.0;
        $otherCashNet = 0.0;

        foreach ($stmt->fetchAll() as $row) {
            $net = (float)$row['debit'] - (float)$row['credit'];
            if (in_array($row['source_type'], ['payment', 'expense', 'invoice'], true)) {
                if ($net >= 0) $operatingIn += $net;
                else $operatingOut += abs($net);
            } else {
                $otherCashNet += $net;
            }
        }

        $netOperating = round($operatingIn - $operatingOut, 2);
        $netChange = round($netOperating + $otherCashNet, 2);
        $closingCash = round($openingCash + $netChange, 2);

        return [
            'method' => 'direct',
            'opening_cash' => round($openingCash, 2),
            'operating_inflows' => round($operatingIn, 2),
            'operating_outflows' => round($operatingOut, 2),
            'net_operating_cash' => $netOperating,
            'other_manual_cash_net' => round($otherCashNet, 2),
            'net_change_in_cash' => $netChange,
            'closing_cash' => $closingCash
        ];
    }
}
