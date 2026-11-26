<?php
/**
 * Bank Statement CSV Import & Manual Reconciliation Model
 * Matches imported bank transactions against recorded RACE FINANCE payments and expenses.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class BankReconciliation extends Model {
    public static function getStatementLines(int $firmId, array $filters = [], int $limit = 200): array {
        $sql = "SELECT * FROM bank_statement_lines WHERE firm_id = ?";
        $params = [$firmId];

        if (!empty($filters['match_status'])) {
            $sql .= " AND match_status = ?";
            $params[] = $filters['match_status'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND txn_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND txn_date <= ?";
            $params[] = $filters['to_date'];
        }

        $limit = max(1, min(500, $limit));
        $sql .= " ORDER BY txn_date DESC, id DESC LIMIT {$limit}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getSummary(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT
                COUNT(*) AS total_lines,
                SUM(CASE WHEN match_status = 'matched' THEN 1 ELSE 0 END) AS matched_count,
                SUM(CASE WHEN match_status = 'unmatched' THEN 1 ELSE 0 END) AS unmatched_count,
                SUM(CASE WHEN match_status = 'ignored' THEN 1 ELSE 0 END) AS ignored_count,
                COALESCE(SUM(credit_amount), 0) AS total_credits,
                COALESCE(SUM(debit_amount), 0) AS total_debits
            FROM bank_statement_lines
            WHERE firm_id = ?
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetch() ?: [
            'total_lines' => 0, 'matched_count' => 0, 'unmatched_count' => 0,
            'ignored_count' => 0, 'total_credits' => 0.0, 'total_debits' => 0.0
        ];
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM bank_statement_lines WHERE id = ? AND firm_id = ? LIMIT 1");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    private static function parseTxnDate(?string $raw): string {
        $val = trim((string)$raw);
        if ($val === '') {
            return date('Y-m-d');
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd M Y', 'd-M-Y', 'Y/m/d'] as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $val);
            if ($dt && $dt->format($fmt) === $val) {
                return $dt->format('Y-m-d');
            }
        }
        $ts = strtotime($val);
        return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
    }

    public static function addStatementLine(int $firmId, array $data): array {
        $txnDate = self::parseTxnDate($data['txn_date'] ?? null);
        $desc = trim((string)($data['description'] ?? ''));
        if ($desc === '') {
            throw new Exception("Transaction description is required.");
        }
        $debit = round(max(0.0, (float)($data['debit_amount'] ?? 0)), 2);
        $credit = round(max(0.0, (float)($data['credit_amount'] ?? 0)), 2);
        if ($debit == 0.0 && $credit == 0.0) {
            throw new Exception("Either debit (withdrawal) or credit (deposit) amount must be non-zero.");
        }

        $stmt = self::db()->prepare("
            INSERT INTO bank_statement_lines (
                firm_id, bank_account_code, txn_date, description, reference_no,
                debit_amount, credit_amount, balance_after, match_status, imported_batch, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'unmatched', ?, ?)
        ");
        $stmt->execute([
            $firmId,
            $data['bank_account_code'] ?? '1100',
            $txnDate,
            $desc,
            !empty($data['reference_no']) ? trim((string)$data['reference_no']) : null,
            $debit,
            $credit,
            isset($data['balance_after']) && $data['balance_after'] !== '' ? (float)$data['balance_after'] : null,
            $data['imported_batch'] ?? null,
            !empty($data['created_by']) ? (int)$data['created_by'] : null
        ]);

        return self::getById((int)self::db()->lastInsertId(), $firmId);
    }

    /**
     * Parse and import a standard bank statement CSV file.
     * Supports header auto-detection for Date, Description, Reference, Debit/Withdrawal, Credit/Deposit, Balance.
     */
    public static function importCsv(int $firmId, string $filePath, ?int $createdBy = null): array {
        if (!is_readable($filePath)) {
            throw new Exception("Uploaded CSV file cannot be read.");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Unable to open CSV file.");
        }

        $header = fgetcsv($handle);
        if (!$header || count($header) < 3) {
            fclose($handle);
            throw new Exception("CSV file must contain a header row with Date, Description, and Debit/Credit columns.");
        }

        // Normalize header names to indices
        $idxDate = null;
        $idxDesc = null;
        $idxRef = null;
        $idxDebit = null;
        $idxCredit = null;
        $idxAmount = null;
        $idxBal = null;

        foreach ($header as $i => $col) {
            $clean = strtolower(trim((string)preg_replace('/^\xEF\xBB\xBF/', '', $col)));
            if ($idxDate === null && preg_match('/date|txn_dt/', $clean)) $idxDate = $i;
            elseif ($idxDesc === null && preg_match('/desc|narr|particular|remark|detail/', $clean)) $idxDesc = $i;
            elseif ($idxRef === null && preg_match('/ref|utr|cheque|chq/', $clean)) $idxRef = $i;
            elseif ($idxDebit === null && preg_match('/debit|withdraw|dr\b/', $clean)) $idxDebit = $i;
            elseif ($idxCredit === null && preg_match('/credit|deposit|cr\b/', $clean)) $idxCredit = $i;
            elseif ($idxAmount === null && preg_match('/amount|amt/', $clean)) $idxAmount = $i;
            elseif ($idxBal === null && preg_match('/balance|bal\b/', $clean)) $idxBal = $i;
        }

        if ($idxDate === null || $idxDesc === null || ($idxDebit === null && $idxCredit === null && $idxAmount === null)) {
            fclose($handle);
            throw new Exception("Could not recognize required columns (Date, Description, Debit/Credit or Amount).");
        }

        $batch = 'csv_' . date('YmdHis');
        $importedCount = 0;

        self::transaction(function () use ($handle, $firmId, $batch, $createdBy, $idxDate, $idxDesc, $idxRef, $idxDebit, $idxCredit, $idxAmount, $idxBal, &$importedCount) {
            while (($row = fgetcsv($handle)) !== false) {
                $rawDate = trim((string)($row[$idxDate] ?? ''));
                $desc = trim((string)($row[$idxDesc] ?? ''));
                if ($rawDate === '' || $desc === '') {
                    continue;
                }

                $debit = ($idxDebit !== null) ? max(0.0, Security::parseCleanFloat($row[$idxDebit] ?? 0)) : 0.0;
                $credit = ($idxCredit !== null) ? max(0.0, Security::parseCleanFloat($row[$idxCredit] ?? 0)) : 0.0;

                if ($idxDebit === null && $idxCredit === null && $idxAmount !== null) {
                    $signed = Security::parseCleanFloat($row[$idxAmount] ?? 0);
                    if ($signed >= 0) $credit = $signed;
                    else $debit = abs($signed);
                }

                if ($debit == 0.0 && $credit == 0.0) {
                    continue;
                }

                self::addStatementLine($firmId, [
                    'txn_date' => $rawDate,
                    'description' => $desc,
                    'reference_no' => $idxRef !== null ? ($row[$idxRef] ?? null) : null,
                    'debit_amount' => $debit,
                    'credit_amount' => $credit,
                    'balance_after' => ($idxBal !== null && trim((string)($row[$idxBal] ?? '')) !== '') ? Security::parseCleanFloat($row[$idxBal]) : null,
                    'imported_batch' => $batch,
                    'created_by' => $createdBy
                ]);
                $importedCount++;
            }
        });

        fclose($handle);
        if ($importedCount === 0) {
            throw new Exception("No valid non-zero transactions found in the uploaded CSV.");
        }

        return ['batch' => $batch, 'imported_count' => $importedCount];
    }

    /**
     * Suggest matching book transactions (payments & expenses) by amount/direction proximity.
     */
    public static function getCandidateMatches(int $firmId, int $lineId): array {
        $line = self::getById($lineId, $firmId);
        if (!$line) return [];

        $candidates = [];
        $credit = (float)$line['credit_amount'];
        $debit = (float)$line['debit_amount'];

        if ($credit > 0) {
            // Bank deposit -> payment_in
            $pStmt = self::db()->prepare("
                SELECT p.id, p.payment_number AS doc_no, p.payment_date AS txn_date, p.amount, p.payment_mode, p.reference_no,
                       COALESCE(pt.name, 'Customer') AS party_name
                FROM payments p
                LEFT JOIN parties pt ON pt.id = p.party_id
                WHERE p.firm_id = ? AND p.type = 'payment_in'
                  AND ABS(p.amount - ?) <= 5.0
                ORDER BY ABS(p.amount - ?) ASC, p.payment_date DESC
                LIMIT 25
            ");
            $pStmt->execute([$firmId, $credit, $credit]);
            foreach ($pStmt->fetchAll() as $p) {
                $candidates[] = [
                    'source_type' => 'payment',
                    'source_id' => (int)$p['id'],
                    'label' => "Receipt #{$p['doc_no']} — {$p['party_name']} ({$p['payment_mode']})",
                    'txn_date' => $p['txn_date'],
                    'amount' => (float)$p['amount'],
                    'reference_no' => $p['reference_no']
                ];
            }
        }

        if ($debit > 0) {
            // Bank withdrawal -> payment_out OR expense
            $pStmt = self::db()->prepare("
                SELECT p.id, p.payment_number AS doc_no, p.payment_date AS txn_date, p.amount, p.payment_mode, p.reference_no,
                       COALESCE(pt.name, 'Supplier') AS party_name
                FROM payments p
                LEFT JOIN parties pt ON pt.id = p.party_id
                WHERE p.firm_id = ? AND p.type = 'payment_out'
                  AND ABS(p.amount - ?) <= 5.0
                ORDER BY ABS(p.amount - ?) ASC, p.payment_date DESC
                LIMIT 15
            ");
            $pStmt->execute([$firmId, $debit, $debit]);
            foreach ($pStmt->fetchAll() as $p) {
                $candidates[] = [
                    'source_type' => 'payment',
                    'source_id' => (int)$p['id'],
                    'label' => "Payment Out #{$p['doc_no']} — {$p['party_name']} ({$p['payment_mode']})",
                    'txn_date' => $p['txn_date'],
                    'amount' => (float)$p['amount'],
                    'reference_no' => $p['reference_no']
                ];
            }

            try {
                $eStmt = self::db()->prepare("
                    SELECT id, expense_number, expense_date, category, payee, amount, reference_no
                    FROM expenses
                    WHERE firm_id = ? AND ABS(amount - ?) <= 5.0
                    ORDER BY ABS(amount - ?) ASC, expense_date DESC
                    LIMIT 15
                ");
                $eStmt->execute([$firmId, $debit, $debit]);
                foreach ($eStmt->fetchAll() as $e) {
                    $candidates[] = [
                        'source_type' => 'expense',
                        'source_id' => (int)$e['id'],
                        'label' => "Expense #{$e['expense_number']} — {$e['category']}" . (!empty($e['payee']) ? " ({$e['payee']})" : ''),
                        'txn_date' => $e['expense_date'],
                        'amount' => (float)$e['amount'],
                        'reference_no' => $e['reference_no']
                    ];
                }
            } catch (Throwable $ex) {
                // Ignore if expenses unused
            }
        }

        return $candidates;
    }

    public static function matchLine(int $firmId, int $lineId, string $sourceType, int $sourceId): bool {
        if (!in_array($sourceType, ['payment', 'expense', 'journal'], true) || $sourceId <= 0) {
            throw new Exception("Invalid book transaction selected.");
        }
        $line = self::getById($lineId, $firmId);
        if (!$line) return false;

        $stmt = self::db()->prepare("
            UPDATE bank_statement_lines
            SET match_status = 'matched', matched_source_type = ?, matched_source_id = ?, matched_at = CURRENT_TIMESTAMP
            WHERE id = ? AND firm_id = ?
        ");
        return $stmt->execute([$sourceType, $sourceId, $lineId, $firmId]);
    }

    public static function setLineStatus(int $firmId, int $lineId, string $status): bool {
        if (!in_array($status, ['unmatched', 'ignored'], true)) {
            return false;
        }
        $stmt = self::db()->prepare("
            UPDATE bank_statement_lines
            SET match_status = ?, matched_source_type = NULL, matched_source_id = NULL, matched_at = NULL
            WHERE id = ? AND firm_id = ?
        ");
        return $stmt->execute([$status, $lineId, $firmId]);
    }
}
