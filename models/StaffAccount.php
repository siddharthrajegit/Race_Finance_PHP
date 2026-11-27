<?php
/**
 * Staff & Dedicated CA/Accountant Account Model
 * Manages per-firm named staff logins, role presets, custom permissions, and active status.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Firm.php';

class StaffAccount extends Model {
    public static function getForUserAndFirm(int $userId, int $firmId): ?array {
        try {
            $stmt = self::db()->prepare("
                SELECT sa.*, f.user_id AS firm_owner_id
                FROM staff_accounts sa
                JOIN firms f ON f.id = sa.firm_id
                WHERE sa.user_id = ? AND sa.firm_id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId, $firmId]);
            return $stmt->fetch() ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Returns all firms a staff user is actively assigned to (provided owner is active & not expired).
     */
    public static function getActiveFirmsForStaff(int $userId): array {
        try {
            $stmt = self::db()->prepare("
                SELECT f.*, sa.role AS access_role, sa.permissions_json, sa.owner_user_id
                FROM staff_accounts sa
                JOIN firms f ON f.id = sa.firm_id
                JOIN users owner ON owner.id = sa.owner_user_id
                WHERE sa.user_id = ?
                  AND sa.is_active = 1
                  AND owner.status = 'active'
                  AND (owner.subscription_expires_at IS NULL OR owner.subscription_expires_at >= ?)
                ORDER BY f.is_default DESC, f.name ASC
            ");
            $stmt->execute([$userId, date('Y-m-d H:i:s')]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Group staff assignments by staff user_id for a given owner.
     */
    public static function getByOwner(int $ownerUserId): array {
        $stmt = self::db()->prepare("
            SELECT sa.*, u.name AS staff_name, u.phone AS staff_phone, u.email AS staff_email,
                   u.status AS user_status, f.name AS firm_name
            FROM staff_accounts sa
            JOIN users u ON u.id = sa.user_id
            JOIN firms f ON f.id = sa.firm_id
            WHERE sa.owner_user_id = ?
            ORDER BY u.name ASC, f.name ASC
        ");
        $stmt->execute([$ownerUserId]);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $r) {
            $uid = (int)$r['user_id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'user_id' => $uid,
                    'name' => $r['staff_name'],
                    'phone' => $r['staff_phone'],
                    'email' => $r['staff_email'],
                    'user_status' => $r['user_status'],
                    'role' => $r['role'],
                    'is_active' => (int)$r['is_active'] === 1,
                    'permissions_json' => $r['permissions_json'],
                    'notes' => $r['notes'],
                    'created_at' => $r['created_at'],
                    'firms' => []
                ];
            }
            $grouped[$uid]['firms'][] = [
                'firm_id' => (int)$r['firm_id'],
                'firm_name' => $r['firm_name'],
                'role' => $r['role'],
                'is_active' => (int)$r['is_active'] === 1
            ];
        }

        return array_values($grouped);
    }

    public static function getStaffDetailForOwner(int $ownerUserId, int $staffUserId): ?array {
        $all = self::getByOwner($ownerUserId);
        foreach ($all as $s) {
            if ((int)$s['user_id'] === $staffUserId) {
                return $s;
            }
        }
        return null;
    }

    /**
     * Create a new named staff/CA user account and assign to selected owner firms.
     */
    public static function createStaffUser(int $ownerUserId, array $userData, array $firmIds, string $role, ?array $customPermissions = null, ?string $notes = null): array {
        return self::transaction(function ($db) use ($ownerUserId, $userData, $firmIds, $role, $customPermissions, $notes) {
            $validFirmIds = [];
            foreach ($firmIds as $fid) {
                $f = Firm::getById((int)$fid, $ownerUserId);
                if ($f) {
                    $validFirmIds[] = (int)$f['id'];
                }
            }
            if (empty($validFirmIds)) {
                throw new Exception("Please assign at least one of your business firms to this staff account.");
            }

            $validRoles = ['manager', 'cashier', 'storekeeper', 'accountant'];
            if (!in_array($role, $validRoles, true)) {
                $role = 'cashier';
            }

            $name = trim((string)($userData['name'] ?? ''));
            $phone = !empty($userData['phone']) ? preg_replace('/[^0-9]/', '', (string)$userData['phone']) : null;
            $email = !empty($userData['email']) ? strtolower(trim((string)$userData['email'])) : null;
            $password = (string)($userData['password'] ?? '');

            if ($name === '') {
                throw new Exception("Staff member name is required.");
            }
            if (!$phone && !$email) {
                throw new Exception("Either a 10-digit phone number or email address is required for login.");
            }
            if ($phone && strlen($phone) !== 10) {
                throw new Exception("Phone number must be exactly 10 digits.");
            }
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Please provide a valid email address.");
            }

            // Ensure identifier doesn't collide with unrelated accounts
            $existingByPhone = $phone ? User::findByPhone($phone) : null;
            $existingByEmail = $email ? User::findByEmail($email) : null;
            if ($existingByPhone || $existingByEmail) {
                throw new Exception("A user account with this phone or email already exists in the system.");
            }

            if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                throw new Exception("Password must be at least 8 characters and include at least one letter and one number.");
            }

            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $staffUser = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed,
                'role' => 'staff',
                'status' => 'active',
                'subscription_expires_at' => null
            ]);

            $permJson = $customPermissions !== null ? json_encode(array_values($customPermissions)) : null;

            $stmt = $db->prepare("
                INSERT INTO staff_accounts (owner_user_id, user_id, firm_id, role, permissions_json, is_active, notes)
                VALUES (?, ?, ?, ?, ?, 1, ?)
            ");
            foreach ($validFirmIds as $fid) {
                $stmt->execute([$ownerUserId, (int)$staffUser['id'], $fid, $role, $permJson, $notes]);
            }

            return self::getStaffDetailForOwner($ownerUserId, (int)$staffUser['id']) ?? $staffUser;
        });
    }

    public static function updateStaffUser(int $ownerUserId, int $staffUserId, array $data, array $firmIds, string $role, ?array $customPermissions = null): ?array {
        return self::transaction(function ($db) use ($ownerUserId, $staffUserId, $data, $firmIds, $role, $customPermissions) {
            $existing = self::getStaffDetailForOwner($ownerUserId, $staffUserId);
            if (!$existing) {
                return null;
            }

            $validFirmIds = [];
            foreach ($firmIds as $fid) {
                $f = Firm::getById((int)$fid, $ownerUserId);
                if ($f) {
                    $validFirmIds[] = (int)$f['id'];
                }
            }
            if (empty($validFirmIds)) {
                throw new Exception("Staff account must be assigned to at least one firm.");
            }

            $validRoles = ['manager', 'cashier', 'storekeeper', 'accountant'];
            if (!in_array($role, $validRoles, true)) {
                $role = 'cashier';
            }

            $name = trim((string)($data['name'] ?? $existing['name']));
            $isActive = !empty($data['is_active']) ? 1 : 0;
            $notes = isset($data['notes']) ? trim((string)$data['notes']) : $existing['notes'];

            if ($name !== '') {
                $db->prepare("UPDATE users SET name = ?, status = ? WHERE id = ? AND role = 'staff'")
                   ->execute([$name, $isActive ? 'active' : 'suspended', $staffUserId]);
            }

            if (!empty($data['new_password'])) {
                $pwd = (string)$data['new_password'];
                if (strlen($pwd) < 8 || !preg_match('/[a-zA-Z]/', $pwd) || !preg_match('/[0-9]/', $pwd)) {
                    throw new Exception("New password must be at least 8 characters with letters and numbers.");
                }
                $db->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'staff'")
                   ->execute([password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 10]), $staffUserId]);
            }

            $permJson = $customPermissions !== null ? json_encode(array_values($customPermissions)) : null;

            // Replace firm assignments owned by this owner
            $db->prepare("DELETE FROM staff_accounts WHERE owner_user_id = ? AND user_id = ?")
               ->execute([$ownerUserId, $staffUserId]);

            $ins = $db->prepare("
                INSERT INTO staff_accounts (owner_user_id, user_id, firm_id, role, permissions_json, is_active, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($validFirmIds as $fid) {
                $ins->execute([$ownerUserId, $staffUserId, $fid, $role, $permJson, $isActive, $notes]);
            }

            return self::getStaffDetailForOwner($ownerUserId, $staffUserId);
        });
    }

    public static function deleteStaffUser(int $ownerUserId, int $staffUserId): bool {
        return self::transaction(function ($db) use ($ownerUserId, $staffUserId) {
            $existing = self::getStaffDetailForOwner($ownerUserId, $staffUserId);
            if (!$existing) {
                return false;
            }
            $db->prepare("DELETE FROM staff_accounts WHERE owner_user_id = ? AND user_id = ?")
               ->execute([$ownerUserId, $staffUserId]);

            // Only delete the user row if role='staff' and no other firm assignments remain
            $chk = $db->prepare("SELECT COUNT(*) FROM staff_accounts WHERE user_id = ?");
            $chk->execute([$staffUserId]);
            if ((int)$chk->fetchColumn() === 0) {
                $db->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'")->execute([$staffUserId]);
            }
            return true;
        });
    }
}
