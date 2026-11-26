<?php
/**
 * Business Expense Management Model
 * Handles expense tracking, categories, attachments, summaries, and accounting integration hooks.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class Expense extends Model {
    public const DEFAULT_CATEGORIES = [
        ['name' => 'Rent',                  'account_code' => '5200'],
        ['name' => 'Salaries & Wages',      'account_code' => '5300'],
        ['name' => 'Utilities & Electricity','account_code' => '5400'],
        ['name' => 'Office Supplies',       'account_code' => '5500'],
        ['name' => 'Travel & Conveyance',   'account_code' => '5900'],
        ['name' => 'Repairs & Maintenance', 'account_code' => '5900'],
        ['name' => 'Packaging & Freight',   'account_code' => '5900'],
        ['name' => 'Other Expense',         'account_code' => '5900']
    ];

    public static function ensureDefaultCategories(int $firmId): void {
        $chk = self::db()->prepare("SELECT COUNT(*) FROM expense_categories WHERE firm_id = ?");
        $chk->execute([$firmId]);
        if ((int)$chk->fetchColumn() > 0) {
            return;
        }
        $ins = self::db()->prepare("
            INSERT INTO expense_categories (firm_id, name, account_code, is_default)
            VALUES (?, ?, ?, 1)
        ");
        foreach (self::DEFAULT_CATEGORIES as $cat) {
            try {
                $ins->execute([$firmId, $cat['name'], $cat['account_code']]);
            } catch (Throwable $e) {
                // Ignore duplicate races
            }
        }
    }

    public static function getCategories(int $firmId): array {
        self::ensureDefaultCategories($firmId);
        $stmt = self::db()->prepare("SELECT * FROM expense_categories WHERE firm_id = ? ORDER BY is_default DESC, name ASC");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function createCategory(int $firmId, string $name, ?string $accountCode = '5900'): void {
        $clean = trim($name);
        if ($clean === '') {
            throw new Exception("Category name is required.");
        }
        if (DB::isMysql()) {
            $stmt = self::db()->prepare("
                INSERT INTO expense_categories (firm_id, name, account_code, is_default)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE name = name
            ");
        } else {
            $stmt = self::db()->prepare("
                INSERT INTO expense_categories (firm_id, name, account_code, is_default)
                VALUES (?, ?, ?, 0)
                ON CONFLICT(firm_id, name) DO NOTHING
            ");
        }
        $stmt->execute([$firmId, $clean, $accountCode ?: '5900']);
    }

    public static function getNextExpenseNumber(int $firmId): string {
        $last = self::getOrInitFirmSequence($firmId, 'expense', 'expenses', 'expense_number');
        $next = $last + 1;
        $stmt = self::db()->prepare("SELECT id FROM expenses WHERE firm_id = ? AND expense_number = ?");
        while (true) {
            $cand = self::formatDocumentNumber($next);
            $stmt->execute([$firmId, $cand]);
            if (!$stmt->fetch()) {
                return $cand;
            }
            $next++;
        }
    }

    public static function getByFirmId(int $firmId, array $filters = [], int $limit = 100, int $offset = 0): array {
        $sql = "
            SELECT e.*, u.name AS created_by_name
            FROM expenses e
            LEFT JOIN users u ON u.id = e.created_by
            WHERE e.firm_id = ?
        ";
        $params = [$firmId];

        if (!empty($filters['category'])) {
            $sql .= " AND e.category = ?";
            $params[] = trim((string)$filters['category']);
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $filters['to_date'];
        }

        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);
        $sql .= " ORDER BY e.expense_date DESC, e.id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getSummary(int $firmId, array $filters = []): array {
        $sql = "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM expenses WHERE firm_id = ?";
        $params = [$firmId];
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = trim((string)$filters['category']);
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND expense_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND expense_date <= ?";
            $params[] = $filters['to_date'];
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: ['total_count' => 0, 'total_amount' => 0.0];
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM expenses WHERE id = ? AND firm_id = ?");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): array {
        return self::transaction(function ($db) use ($data) {
            $firmId = (int)$data['firm_id'];
            self::ensureDefaultCategories($firmId);

            $category = trim((string)($data['category'] ?? 'Other Expense'));
            if ($category === '') $category = 'Other Expense';
            self::createCategory($firmId, $category);

            $amount = round(max(0.0, (float)($data['amount'] ?? 0)), 2);
            if ($amount <= 0) {
                throw new Exception("Expense amount must be greater than 0.");
            }

            $expenseNumber = self::allocateFirmSequence(
                $firmId,
                'expense',
                $data['expense_number'] ?? null,
                'expenses',
                'expense_number'
            );

            $stmt = $db->prepare("
                INSERT INTO expenses (
                    firm_id, expense_number, expense_date, category, payee,
                    amount, payment_mode, reference_no, attachment_path, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $firmId,
                $expenseNumber,
                !empty($data['expense_date']) ? $data['expense_date'] : date('Y-m-d'),
                $category,
                !empty($data['payee']) ? trim((string)$data['payee']) : null,
                $amount,
                $data['payment_mode'] ?? 'cash',
                !empty($data['reference_no']) ? trim((string)$data['reference_no']) : null,
                $data['attachment_path'] ?? null,
                !empty($data['notes']) ? trim((string)$data['notes']) : null,
                !empty($data['created_by']) ? (int)$data['created_by'] : null
            ]);

            $id = (int)$db->lastInsertId();
            $expense = self::getById($id, $firmId);

            if (class_exists('Accounting') && defined('ENABLE_ACCOUNTING') && ENABLE_ACCOUNTING) {
                Accounting::postExpenseJournal($expense);
            }

            return $expense;
        });
    }

    public static function update(int $id, int $firmId, array $data): ?array {
        return self::transaction(function ($db) use ($id, $firmId, $data) {
            $existing = self::getById($id, $firmId);
            if (!$existing) return null;

            $category = trim((string)($data['category'] ?? $existing['category']));
            if ($category === '') $category = 'Other Expense';
            self::createCategory($firmId, $category);

            $amount = round(max(0.0, (float)($data['amount'] ?? $existing['amount'])), 2);
            if ($amount <= 0) {
                throw new Exception("Expense amount must be greater than 0.");
            }

            $expenseNumber = $existing['expense_number'];
            if (!empty($data['expense_number']) && trim((string)$data['expense_number']) !== $existing['expense_number']) {
                $candidate = trim((string)$data['expense_number']);
                if (ctype_digit($candidate)) {
                    $candidate = self::formatDocumentNumber((int)$candidate);
                }
                $chk = $db->prepare("SELECT id FROM expenses WHERE firm_id = ? AND expense_number = ? AND id != ?");
                $chk->execute([$firmId, $candidate, $id]);
                if ($chk->fetch()) {
                    throw new Exception("Expense number \"{$candidate}\" already exists.");
                }
                $expenseNumber = $candidate;
            }

            $attach = array_key_exists('attachment_path', $data) ? $data['attachment_path'] : $existing['attachment_path'];

            $db->prepare("
                UPDATE expenses SET
                    expense_number = ?, expense_date = ?, category = ?, payee = ?,
                    amount = ?, payment_mode = ?, reference_no = ?, attachment_path = ?,
                    notes = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND firm_id = ?
            ")->execute([
                $expenseNumber,
                !empty($data['expense_date']) ? $data['expense_date'] : $existing['expense_date'],
                $category,
                !empty($data['payee']) ? trim((string)$data['payee']) : null,
                $amount,
                $data['payment_mode'] ?? $existing['payment_mode'],
                !empty($data['reference_no']) ? trim((string)$data['reference_no']) : null,
                $attach,
                !empty($data['notes']) ? trim((string)$data['notes']) : null,
                !empty($data['updated_by']) ? (int)$data['updated_by'] : null,
                $id,
                $firmId
            ]);

            $updated = self::getById($id, $firmId);

            if (class_exists('Accounting') && defined('ENABLE_ACCOUNTING') && ENABLE_ACCOUNTING) {
                Accounting::reverseSourceJournal($firmId, 'expense', $id, "Edited Expense #{$expenseNumber}");
                Accounting::postExpenseJournal($updated);
            }

            return $updated;
        });
    }

    public static function delete(int $id, int $firmId): bool {
        return self::transaction(function ($db) use ($id, $firmId) {
            $existing = self::getById($id, $firmId);
            if (!$existing) return false;

            if (class_exists('Accounting') && defined('ENABLE_ACCOUNTING') && ENABLE_ACCOUNTING) {
                Accounting::reverseSourceJournal($firmId, 'expense', $id, "Deleted Expense #{$existing['expense_number']}");
            }

            if (!empty($existing['attachment_path'])) {
                Security::safeDeleteUpload($existing['attachment_path']);
            }

            return $db->prepare("DELETE FROM expenses WHERE id = ? AND firm_id = ?")->execute([$id, $firmId]);
        });
    }
}
