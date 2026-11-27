<?php
/**
 * Base Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Security.php';

class Model {
    public const GST_STATES = [
        ['code' => '01', 'name' => 'Jammu & Kashmir'],
        ['code' => '02', 'name' => 'Himachal Pradesh'],
        ['code' => '03', 'name' => 'Punjab'],
        ['code' => '04', 'name' => 'Chandigarh'],
        ['code' => '05', 'name' => 'Uttarakhand'],
        ['code' => '06', 'name' => 'Haryana'],
        ['code' => '07', 'name' => 'Delhi'],
        ['code' => '08', 'name' => 'Rajasthan'],
        ['code' => '09', 'name' => 'Uttar Pradesh'],
        ['code' => '10', 'name' => 'Bihar'],
        ['code' => '11', 'name' => 'Sikkim'],
        ['code' => '12', 'name' => 'Arunachal Pradesh'],
        ['code' => '13', 'name' => 'Nagaland'],
        ['code' => '14', 'name' => 'Manipur'],
        ['code' => '15', 'name' => 'Mizoram'],
        ['code' => '16', 'name' => 'Tripura'],
        ['code' => '17', 'name' => 'Meghalaya'],
        ['code' => '18', 'name' => 'Assam'],
        ['code' => '19', 'name' => 'West Bengal'],
        ['code' => '20', 'name' => 'Jharkhand'],
        ['code' => '21', 'name' => 'Odisha'],
        ['code' => '22', 'name' => 'Chhattisgarh'],
        ['code' => '23', 'name' => 'Madhya Pradesh'],
        ['code' => '24', 'name' => 'Gujarat'],
        ['code' => '26', 'name' => 'Dadra and Nagar Haveli and Daman and Diu'],
        ['code' => '27', 'name' => 'Maharashtra'],
        ['code' => '29', 'name' => 'Karnataka'],
        ['code' => '30', 'name' => 'Goa'],
        ['code' => '31', 'name' => 'Lakshadweep'],
        ['code' => '32', 'name' => 'Kerala'],
        ['code' => '33', 'name' => 'Tamil Nadu'],
        ['code' => '34', 'name' => 'Puducherry'],
        ['code' => '35', 'name' => 'Andaman & Nicobar Islands'],
        ['code' => '36', 'name' => 'Telangana'],
        ['code' => '37', 'name' => 'Andhra Pradesh'],
        ['code' => '38', 'name' => 'Ladakh'],
        ['code' => '97', 'name' => 'Other Territory']
    ];

    protected static function db(): PDO {
        return DB::getConnection();
    }

    private const ALLOWED_SEQUENCE_TARGETS = [
        'stock_transfers' => 'transfer_number',
        'expenses' => 'expense_number',
        'journal_entries' => 'entry_number',
        'production_orders' => 'order_number',
    ];

    public static function formatDocumentNumber(int|string|null $num): string {
        $parsed = (int)$num;
        if ($parsed < 0) return '001';
        return str_pad((string)$parsed, 3, '0', STR_PAD_LEFT);
    }

    public static function getOrInitFirmSequence(int $firmId, string $docType, string $table, string $column): int {
        if ((self::ALLOWED_SEQUENCE_TARGETS[$table] ?? null) !== $column) {
            throw new Exception("Invalid sequence target: {$table}.{$column}");
        }
        $db = self::db();
        $stmt = $db->prepare("SELECT last_number FROM document_sequences WHERE firm_id = ? AND doc_type = ?");
        $stmt->execute([$firmId, $docType]);
        $row = $stmt->fetch();
        if ($row !== false) {
            return (int)$row['last_number'];
        }

        $maxStmt = $db->prepare("SELECT {$column} FROM {$table} WHERE firm_id = ?");
        $maxStmt->execute([$firmId]);
        $max = 0;
        foreach ($maxStmt->fetchAll(PDO::FETCH_COLUMN) as $val) {
            $strVal = trim((string)$val);
            if ($strVal !== '' && ctype_digit($strVal)) {
                $num = (int)$strVal;
                if ($num > $max) $max = $num;
            }
        }

        try {
            $ins = $db->prepare("INSERT INTO document_sequences (firm_id, doc_type, last_number) VALUES (?, ?, ?)");
            $ins->execute([$firmId, $docType, $max]);
        } catch (Throwable $e) {
            // Handle concurrent init gracefully
        }
        return $max;
    }

    public static function allocateFirmSequence(int $firmId, string $docType, ?string $requestedNumber, string $table, string $column): string {
        if ((self::ALLOWED_SEQUENCE_TARGETS[$table] ?? null) !== $column) {
            throw new Exception("Invalid sequence target: {$table}.{$column}");
        }
        $db = self::db();
        $last = self::getOrInitFirmSequence($firmId, $docType, $table, $column);
        $chkStmt = $db->prepare("SELECT id FROM {$table} WHERE firm_id = ? AND {$column} = ? LIMIT 1");

        $trimmed = $requestedNumber !== null ? trim($requestedNumber) : '';
        if ($trimmed !== '') {
            $cand = ctype_digit($trimmed) ? self::formatDocumentNumber((int)$trimmed) : $trimmed;
            $chkStmt->execute([$firmId, $cand]);
            if ($chkStmt->fetch()) {
                throw new Exception("Document number '{$cand}' already exists.");
            }
            if (ctype_digit($trimmed) && (int)$trimmed > $last) {
                $upd = $db->prepare("UPDATE document_sequences SET last_number = ?, updated_at = CURRENT_TIMESTAMP WHERE firm_id = ? AND doc_type = ?");
                $upd->execute([(int)$trimmed, $firmId, $docType]);
            }
            return $cand;
        }

        $next = $last + 1;
        while (true) {
            $cand = self::formatDocumentNumber($next);
            $chkStmt->execute([$firmId, $cand]);
            if (!$chkStmt->fetch()) {
                $upd = $db->prepare("UPDATE document_sequences SET last_number = ?, updated_at = CURRENT_TIMESTAMP WHERE firm_id = ? AND doc_type = ?");
                $upd->execute([$next, $firmId, $docType]);
                return $cand;
            }
            $next++;
        }
    }

    public static function transaction(callable $callback) {
        $db = self::db();
        $isTopLevel = !$db->inTransaction();
        if ($isTopLevel) {
            $db->beginTransaction();
        }
        try {
            $result = $callback($db);
            if ($isTopLevel) {
                $db->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($isTopLevel && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
