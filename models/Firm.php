<?php
/**
 * Firm / Business Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';

class Firm extends Model {
    public static function getByUserId(int $userId): array {
        $stmt = self::db()->prepare("SELECT * FROM firms WHERE user_id = ? ORDER BY is_default DESC, name ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, ?int $userId = null): ?array {
        if ($userId !== null) {
            $stmt = self::db()->prepare("SELECT * FROM firms WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        } else {
            $stmt = self::db()->prepare("SELECT * FROM firms WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch() ?: null;
    }

    public static function getDefault(int $userId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM firms WHERE user_id = ? ORDER BY is_default DESC, id ASC LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): ?array {
        return self::transaction(function($db) use ($data) {
            $userId = (int)$data['user_id'];
            
            $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM firms WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $existingCount = (int)$countStmt->fetch()['cnt'];

            if ($existingCount >= 2) {
                throw new Exception("Maximum firm limit reached. A user can only register up to 2 business firms.");
            }

            $isDefault = !empty($data['is_default']) ? 1 : 0;
            if ($isDefault) {
                $db->prepare("UPDATE firms SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            }
            $defaultFlag = ($isDefault || $existingCount === 0) ? 1 : 0;

            $stmt = $db->prepare("
                INSERT INTO firms (
                    user_id, name, gstin, pan, phone, email, address, city, state, state_code,
                    pincode, bank_name, bank_account_no, bank_ifsc, bank_branch, upi_id, terms,
                    logo_path, signature_path, is_default
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                trim($data['name']),
                !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null,
                !empty($data['pan']) ? strtoupper(trim($data['pan'])) : null,
                !empty($data['phone']) ? trim($data['phone']) : null,
                !empty($data['email']) ? trim($data['email']) : null,
                !empty($data['address']) ? trim($data['address']) : null,
                !empty($data['city']) ? trim($data['city']) : null,
                !empty($data['state']) ? trim($data['state']) : null,
                !empty($data['state_code']) ? trim($data['state_code']) : null,
                !empty($data['pincode']) ? trim($data['pincode']) : null,
                !empty($data['bank_name']) ? trim($data['bank_name']) : null,
                !empty($data['bank_account_no']) ? trim($data['bank_account_no']) : null,
                !empty($data['bank_ifsc']) ? strtoupper(trim($data['bank_ifsc'])) : null,
                !empty($data['bank_branch']) ? trim($data['bank_branch']) : null,
                !empty($data['upi_id']) ? trim($data['upi_id']) : null,
                !empty($data['terms']) ? trim($data['terms']) : null,
                $data['logo_path'] ?? null,
                $data['signature_path'] ?? null,
                $defaultFlag
            ]);

            $id = (int)$db->lastInsertId();
            return self::getById($id, $userId);
        });
    }

    public static function update(int $id, int $userId, array $data): ?array {
        return self::transaction(function($db) use ($id, $userId, $data) {
            $isDefault = isset($data['is_default']) ? ($data['is_default'] ? 1 : 0) : null;
            if ($isDefault === 1) {
                $db->prepare("UPDATE firms SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            }

            $query = "
                UPDATE firms SET
                    name = ?, gstin = ?, pan = ?, phone = ?, email = ?, address = ?, city = ?,
                    state = ?, state_code = ?, pincode = ?, bank_name = ?, bank_account_no = ?,
                    bank_ifsc = ?, bank_branch = ?, upi_id = ?, terms = ?, is_default = COALESCE(?, is_default)
            ";
            $params = [
                trim($data['name']),
                !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null,
                !empty($data['pan']) ? strtoupper(trim($data['pan'])) : null,
                !empty($data['phone']) ? trim($data['phone']) : null,
                !empty($data['email']) ? trim($data['email']) : null,
                !empty($data['address']) ? trim($data['address']) : null,
                !empty($data['city']) ? trim($data['city']) : null,
                !empty($data['state']) ? trim($data['state']) : null,
                !empty($data['state_code']) ? trim($data['state_code']) : null,
                !empty($data['pincode']) ? trim($data['pincode']) : null,
                !empty($data['bank_name']) ? trim($data['bank_name']) : null,
                !empty($data['bank_account_no']) ? trim($data['bank_account_no']) : null,
                !empty($data['bank_ifsc']) ? strtoupper(trim($data['bank_ifsc'])) : null,
                !empty($data['bank_branch']) ? trim($data['bank_branch']) : null,
                !empty($data['upi_id']) ? trim($data['upi_id']) : null,
                !empty($data['terms']) ? trim($data['terms']) : null,
                $isDefault
            ];

            if (array_key_exists('logo_path', $data)) {
                $query .= ", logo_path = ?";
                $params[] = $data['logo_path'];
            }
            if (array_key_exists('signature_path', $data)) {
                $query .= ", signature_path = ?";
                $params[] = $data['signature_path'];
            }

            $query .= " WHERE id = ? AND user_id = ?";
            $params[] = $id;
            $params[] = $userId;

            $db->prepare($query)->execute($params);
            return self::getById($id, $userId);
        });
    }

    public static function setDefault(int $id, int $userId): void {
        self::transaction(function($db) use ($id, $userId) {
            $db->prepare("UPDATE firms SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            $db->prepare("UPDATE firms SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
        });
    }

    public static function delete(int $id, int $userId): bool {
        $stmt = self::db()->prepare("DELETE FROM firms WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}
