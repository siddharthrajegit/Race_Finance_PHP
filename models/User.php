<?php
/**
 * User Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class User extends Model {
    public const SAFE_FIELDS = 'id, name, email, phone, google_id, avatar, role, status, subscription_expires_at, created_at';

    public static function findById(int $id): ?array {
        $stmt = self::db()->prepare("SELECT " . self::SAFE_FIELDS . " FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $stmt = self::db()->prepare("SELECT " . self::SAFE_FIELDS . " FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([trim($email)]);
        return $stmt->fetch() ?: null;
    }

    public static function findByPhone(string $phone): ?array {
        $stmt = self::db()->prepare("SELECT " . self::SAFE_FIELDS . " FROM users WHERE phone = ?");
        $stmt->execute([trim($phone)]);
        return $stmt->fetch() ?: null;
    }

    public static function findByGoogleId(string $googleId): ?array {
        $stmt = self::db()->prepare("SELECT " . self::SAFE_FIELDS . " FROM users WHERE google_id = ?");
        $stmt->execute([$googleId]);
        return $stmt->fetch() ?: null;
    }

    public static function getPasswordHashById(int $id): ?string {
        $stmt = self::db()->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row['password'] : null;
    }

    public static function findByEmailOrPhone(string $identifier): ?array {
        $clean = trim($identifier);
        $stmt = self::db()->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) OR phone = ?");
        $stmt->execute([$clean, $clean]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): ?array {
        $name = trim($data['name']);
        $email = !empty($data['email']) ? trim($data['email']) : null;
        $phone = !empty($data['phone']) ? trim($data['phone']) : null;
        $password = $data['password_hash'] ?? $data['password'] ?? null;
        $googleId = $data['google_id'] ?? null;
        $avatar = $data['avatar'] ?? null;
        $role = $data['role'] ?? 'user';
        $status = $data['status'] ?? 'active';
        $expiry = $data['subscription_expires_at'] ?? date('Y-m-d H:i:s', strtotime('+365 days'));

        $stmt = self::db()->prepare("
            INSERT INTO users (name, email, phone, password, google_id, avatar, role, status, subscription_expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $phone, $password, $googleId, $avatar, $role, $status, $expiry]);
        $id = (int)self::db()->lastInsertId();
        return self::findById($id);
    }

    public static function extendSubscription(int $id, int $daysToAdd = 365): ?array {
        $user = self::findById($id);
        if (!$user) return null;

        $baseTime = time();
        if (!empty($user['subscription_expires_at'])) {
            $existing = strtotime($user['subscription_expires_at']);
            if ($existing > $baseTime) {
                $baseTime = $existing;
            }
        }
        $newExpiry = date('Y-m-d H:i:s', $baseTime + ($daysToAdd * 86400));

        $stmt = self::db()->prepare("UPDATE users SET subscription_expires_at = ?, status = 'active' WHERE id = ?");
        $stmt->execute([$newExpiry, $id]);
        return self::findById($id);
    }

    public static function updateGoogleId(int $id, string $googleId, ?string $avatar = null): ?array {
        $stmt = self::db()->prepare("UPDATE users SET google_id = ?, avatar = COALESCE(avatar, ?) WHERE id = ?");
        $stmt->execute([$googleId, $avatar, $id]);
        return self::findById($id);
    }

    public static function updateRole(int $id, string $role): ?array {
        $stmt = self::db()->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        return self::findById($id);
    }

    public static function updateStatus(int $id, string $status): ?array {
        $stmt = self::db()->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        return self::findById($id);
    }

    public static function updatePassword(int $id, string $hashedPassword): ?array {
        $stmt = self::db()->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $id]);
        return self::findById($id);
    }

    public static function delete(int $id): bool {
        return self::transaction(function($db) use ($id) {
            // Delete uploaded logo & signature files from disk
            $stmt = $db->prepare("SELECT logo_path, signature_path FROM firms WHERE user_id = ?");
            $stmt->execute([$id]);
            $firms = $stmt->fetchAll();
            foreach ($firms as $f) {
                Security::safeDeleteUpload($f['logo_path']);
                Security::safeDeleteUpload($f['signature_path']);
            }

            // Cascade deletes
            $db->prepare("DELETE FROM invoice_items WHERE invoice_id IN (
                SELECT id FROM invoices WHERE firm_id IN (SELECT id FROM firms WHERE user_id = ?)
            )")->execute([$id]);

            $db->prepare("DELETE FROM invoices WHERE firm_id IN (SELECT id FROM firms WHERE user_id = ?)")->execute([$id]);
            $db->prepare("DELETE FROM payments WHERE firm_id IN (SELECT id FROM firms WHERE user_id = ?)")->execute([$id]);
            $db->prepare("DELETE FROM items WHERE firm_id IN (SELECT id FROM firms WHERE user_id = ?)")->execute([$id]);
            $db->prepare("DELETE FROM parties WHERE firm_id IN (SELECT id FROM firms WHERE user_id = ?)")->execute([$id]);
            $db->prepare("DELETE FROM firms WHERE user_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

            return true;
        });
    }
}
