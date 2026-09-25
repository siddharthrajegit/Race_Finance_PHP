<?php
/**
 * Platform Administration & Governance Model
 * Handles metrics, subscriber management, storage audits, and security logging
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class Admin extends Model {
    public static function getDashboardMetrics(): array {
        $userStats = self::db()->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
            FROM users
        ")->fetch();

        $firmStats = self::db()->query("SELECT COUNT(*) as total FROM firms")->fetch();

        $invoiceStats = self::db()->query("
            SELECT 
                COUNT(*) as total_count,
                SUM(CASE WHEN type = 'sale' THEN 1 ELSE 0 END) as sales_count,
                SUM(CASE WHEN type = 'purchase' THEN 1 ELSE 0 END) as purchase_count,
                SUM(CASE WHEN type = 'sale' THEN grand_total ELSE 0 END) as total_sales_amount,
                SUM(CASE WHEN type = 'purchase' THEN grand_total ELSE 0 END) as total_purchase_amount,
                SUM(paid_amount) as total_paid_amount,
                SUM(balance_due) as total_balance_due
            FROM invoices
        ")->fetch();

        $paymentStats = self::db()->query("
            SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN type = 'payment_in' THEN amount ELSE 0 END) as total_received,
                SUM(CASE WHEN type = 'payment_out' THEN amount ELSE 0 END) as total_paid_out
            FROM payments
        ")->fetch();

        $itemStats = self::db()->query("SELECT COUNT(*) as total_items FROM items")->fetch();
        $partyStats = self::db()->query("SELECT COUNT(*) as total_parties FROM parties")->fetch();

        $regularUsers = self::db()->query("SELECT id FROM users WHERE role != 'admin'")->fetchAll();
        $totalPoolUsedBytes = 0;
        foreach ($regularUsers as $u) {
            $st = self::getUserStorageStats((int)$u['id']);
            if ($st) {
                $totalPoolUsedBytes += $st['totalUsedBytes'];
            }
        }
        $totalPoolQuotaMB = count($regularUsers) * 200;
        $totalPoolUsedMB = round($totalPoolUsedBytes / (1024 * 1024), 2);
        $poolUsagePercentage = $totalPoolQuotaMB > 0 ? round(($totalPoolUsedMB / $totalPoolQuotaMB) * 100, 1) : 0;

        return [
            'users' => $userStats ?: ['total' => 0, 'active' => 0, 'suspended' => 0, 'admins' => 0],
            'firms' => $firmStats ?: ['total' => 0],
            'invoices' => $invoiceStats ?: ['total_count' => 0, 'sales_count' => 0, 'purchase_count' => 0, 'total_sales_amount' => 0, 'total_purchase_amount' => 0, 'total_paid_amount' => 0, 'total_balance_due' => 0],
            'payments' => $paymentStats ?: ['total_payments' => 0, 'total_received' => 0, 'total_paid_out' => 0],
            'items' => $itemStats ?: ['total_items' => 0],
            'parties' => $partyStats ?: ['total_parties' => 0],
            'storage' => [
                'totalPoolQuotaMB' => $totalPoolQuotaMB,
                'totalPoolUsedMB' => $totalPoolUsedMB,
                'poolUsagePercentage' => $poolUsagePercentage,
                'totalPoolUsedBytes' => $totalPoolUsedBytes,
                'quotaPerUserMB' => 200
            ]
        ];
    }

    public static function getUserStorageStats(int $userId): ?array {
        try {
            $userStmt = self::db()->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch();
            if (!$user) return null;

            $firmStmt = self::db()->prepare("SELECT id, name, logo_path, signature_path FROM firms WHERE user_id = ?");
            $firmStmt->execute([$userId]);
            $userFirms = $firmStmt->fetchAll();
            $firmCount = count($userFirms);

            $totalUserQuotaBytes = 200 * 1024 * 1024; // 200 MB
            $perFirmQuotaBytes = ($firmCount <= 1) ? $totalUserQuotaBytes : (100 * 1024 * 1024);

            $totalUserUsedBytes = 0;
            $firmsStorage = [];
            $publicDir = ROOT_DIR . '/public';

            foreach ($userFirms as $firm) {
                $fId = (int)$firm['id'];
                $itemSt = self::db()->prepare("SELECT COUNT(*) as count FROM items WHERE firm_id = ?");
                $itemSt->execute([$fId]);
                $itemCount = (int)$itemSt->fetch()['count'];

                $invSt = self::db()->prepare("SELECT COUNT(*) as count FROM invoices WHERE firm_id = ?");
                $invSt->execute([$fId]);
                $invCount = (int)$invSt->fetch()['count'];

                $partySt = self::db()->prepare("SELECT COUNT(*) as count FROM parties WHERE firm_id = ?");
                $partySt->execute([$fId]);
                $partyCount = (int)$partySt->fetch()['count'];

                $paySt = self::db()->prepare("SELECT COUNT(*) as count FROM payments WHERE firm_id = ?");
                $paySt->execute([$fId]);
                $payCount = (int)$paySt->fetch()['count'];

                // Estimated database bytes
                $rawDbBytes = ($itemCount * 128) + ($invCount * 256) + ($partyCount * 128) + ($payCount * 96) + 1024;
                $dbAllocatedBytes = (int)round($rawDbBytes * 1.5);

                $mediaBytes = 0;
                if (!empty($firm['logo_path'])) {
                    $lp = $publicDir . '/' . ltrim($firm['logo_path'], '/');
                    if (file_exists($lp)) $mediaBytes += filesize($lp);
                }
                if (!empty($firm['signature_path'])) {
                    $sp = $publicDir . '/' . ltrim($firm['signature_path'], '/');
                    if (file_exists($sp)) $mediaBytes += filesize($sp);
                }

                $firmTotalUsedBytes = $dbAllocatedBytes + $mediaBytes;
                $totalUserUsedBytes += $firmTotalUsedBytes;

                $firmQuotaMB = (int)round($perFirmQuotaBytes / (1024 * 1024));
                $firmUsedMB = round($firmTotalUsedBytes / (1024 * 1024), 2);
                $firmPercentage = min(100.0, max(0.1, round(($firmTotalUsedBytes / $perFirmQuotaBytes) * 100, 1)));

                $firmsStorage[] = [
                    'firmId' => $fId,
                    'firmName' => $firm['name'],
                    'dbAllocatedBytes' => $dbAllocatedBytes,
                    'dbAllocatedMB' => round($dbAllocatedBytes / (1024 * 1024), 2),
                    'mediaBytes' => $mediaBytes,
                    'mediaMB' => round($mediaBytes / (1024 * 1024), 2),
                    'backupsBytes' => 0,
                    'backupsMB' => 0,
                    'totalUsedBytes' => $firmTotalUsedBytes,
                    'totalUsedMB' => $firmUsedMB,
                    'quotaBytes' => $perFirmQuotaBytes,
                    'quotaMB' => $firmQuotaMB,
                    'usedPercentage' => $firmPercentage,
                    'counts' => [
                        'items' => $itemCount,
                        'invoices' => $invCount,
                        'parties' => $partyCount,
                        'payments' => $payCount
                    ]
                ];
            }

            $totalUserUsedMB = round($totalUserUsedBytes / (1024 * 1024), 2);
            $totalUserPercentage = min(100.0, max(0.1, round(($totalUserUsedBytes / $totalUserQuotaBytes) * 100, 1)));

            return [
                'userId' => $userId,
                'userName' => $user['name'],
                'totalQuotaBytes' => $totalUserQuotaBytes,
                'totalQuotaMB' => 200,
                'totalUsedBytes' => $totalUserUsedBytes,
                'totalUsedMB' => $totalUserUsedMB,
                'totalUsedPercentage' => $totalUserPercentage,
                'isOverQuota' => $totalUserUsedBytes > $totalUserQuotaBytes,
                'firmCount' => $firmCount,
                'quotaPerFirmMB' => (int)round($perFirmQuotaBytes / (1024 * 1024)),
                'firmsStorage' => $firmsStorage
            ];
        } catch (Throwable $err) {
            return [
                'userId' => $userId,
                'totalQuotaMB' => 200,
                'totalUsedMB' => 0,
                'totalUsedPercentage' => 0,
                'isOverQuota' => false,
                'firmCount' => 0,
                'quotaPerFirmMB' => 200,
                'firmsStorage' => []
            ];
        }
    }

    public static function getAllUsers(string $search = '', string $role = '', string $status = ''): array {
        $query = "
            SELECT 
                u.id, u.name, u.email, u.phone, u.role, u.status, u.avatar, u.subscription_expires_at, u.created_at,
                COUNT(DISTINCT f.id) as firms_count,
                COUNT(DISTINCT i.id) as invoices_count,
                COALESCE(SUM(CASE WHEN i.type = 'sale' THEN i.grand_total ELSE 0 END), 0) as total_turnover
            FROM users u
            LEFT JOIN firms f ON f.user_id = u.id
            LEFT JOIN invoices i ON i.firm_id = f.id
            WHERE 1=1
        ";
        $params = [];

        if (trim($search) !== '') {
            $query .= " AND (LOWER(u.name) LIKE ? OR LOWER(COALESCE(u.email, '')) LIKE ? OR u.phone LIKE ?)";
            $term = '%' . strtolower(trim($search)) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        if (trim($role) !== '') {
            $query .= " AND u.role = ?";
            $params[] = trim($role);
        }
        if (trim($status) !== '') {
            $query .= " AND u.status = ?";
            $params[] = trim($status);
        }

        $query .= " GROUP BY u.id, u.name, u.email, u.phone, u.role, u.status, u.avatar, u.subscription_expires_at, u.created_at ORDER BY u.created_at DESC";
        $stmt = self::db()->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        foreach ($users as &$u) {
            $u['storage'] = self::getUserStorageStats((int)$u['id']);

            if ($u['role'] !== 'admin' && !empty($u['subscription_expires_at'])) {
                $diffSec = strtotime($u['subscription_expires_at']) - time();
                $daysLeft = (int)ceil($diffSec / 86400);
                $u['subscription_days_left'] = $daysLeft;
                $u['is_expired'] = ($daysLeft <= 0);
                $u['is_expiring_soon'] = ($daysLeft > 0 && $daysLeft <= 15);
            } else {
                $u['subscription_days_left'] = null;
                $u['is_expired'] = false;
                $u['is_expiring_soon'] = false;
            }
        }
        unset($u);

        return $users;
    }

    public static function getUserDeepInfo(int $userId): ?array {
        $stmt = self::db()->prepare("SELECT id, name, email, phone, role, status, avatar, subscription_expires_at, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) return null;

        if ($user['role'] !== 'admin' && !empty($user['subscription_expires_at'])) {
            $diffSec = strtotime($user['subscription_expires_at']) - time();
            $daysLeft = (int)ceil($diffSec / 86400);
            $user['subscription_days_left'] = $daysLeft;
            $user['is_expired'] = ($daysLeft <= 0);
            $user['is_expiring_soon'] = ($daysLeft > 0 && $daysLeft <= 15);
        }

        $firmStmt = self::db()->prepare("
            SELECT f.*,
                COUNT(DISTINCT p.id) as parties_count,
                COUNT(DISTINCT it.id) as items_count,
                COUNT(DISTINCT inv.id) as invoices_count,
                COALESCE(SUM(CASE WHEN inv.type = 'sale' THEN inv.grand_total ELSE 0 END), 0) as total_turnover
            FROM firms f
            LEFT JOIN parties p ON p.firm_id = f.id
            LEFT JOIN items it ON it.firm_id = f.id
            LEFT JOIN invoices inv ON inv.firm_id = f.id
            WHERE f.user_id = ?
            GROUP BY f.id
            ORDER BY f.created_at DESC
        ");
        $firmStmt->execute([$userId]);
        $firms = $firmStmt->fetchAll();

        $partyStmt = self::db()->prepare("
            SELECT p.*, f.name as firm_name
            FROM parties p
            JOIN firms f ON f.id = p.firm_id
            WHERE f.user_id = ?
            ORDER BY p.name ASC
        ");
        $partyStmt->execute([$userId]);
        $parties = $partyStmt->fetchAll();

        $itemStmt = self::db()->prepare("
            SELECT it.*, f.name as firm_name
            FROM items it
            JOIN firms f ON f.id = it.firm_id
            WHERE f.user_id = ?
            ORDER BY it.name ASC
        ");
        $itemStmt->execute([$userId]);
        $items = $itemStmt->fetchAll();

        $invStmt = self::db()->prepare("
            SELECT inv.*, f.name as firm_name
            FROM invoices inv
            JOIN firms f ON f.id = inv.firm_id
            WHERE f.user_id = ?
            ORDER BY inv.created_at DESC
            LIMIT 200
        ");
        $invStmt->execute([$userId]);
        $invoices = $invStmt->fetchAll();

        $finStmt = self::db()->prepare("
            SELECT 
                COUNT(DISTINCT inv.id) as total_invoices,
                COALESCE(SUM(CASE WHEN inv.type = 'sale' THEN inv.grand_total ELSE 0 END), 0) as total_sales,
                COALESCE(SUM(CASE WHEN inv.type = 'purchase' THEN inv.grand_total ELSE 0 END), 0) as total_purchases,
                COALESCE(SUM(inv.paid_amount), 0) as total_paid,
                COALESCE(SUM(inv.balance_due), 0) as total_due
            FROM invoices inv
            JOIN firms f ON f.id = inv.firm_id
            WHERE f.user_id = ?
        ");
        $finStmt->execute([$userId]);
        $financials = $finStmt->fetch();

        $storage = self::getUserStorageStats($userId);

        return [
            'user' => $user,
            'firms' => $firms,
            'parties' => $parties,
            'items' => $items,
            'invoices' => $invoices,
            'financials' => $financials,
            'storage' => $storage
        ];
    }

    public static function getAllFirms(string $search = ''): array {
        $query = "
            SELECT 
                f.*,
                u.name as owner_name,
                u.email as owner_email,
                u.phone as owner_phone,
                COUNT(DISTINCT p.id) as parties_count,
                COUNT(DISTINCT it.id) as items_count,
                COUNT(DISTINCT inv.id) as invoices_count,
                COALESCE(SUM(CASE WHEN inv.type = 'sale' THEN inv.grand_total ELSE 0 END), 0) as total_turnover
            FROM firms f
            JOIN users u ON u.id = f.user_id
            LEFT JOIN parties p ON p.firm_id = f.id
            LEFT JOIN items it ON it.firm_id = f.id
            LEFT JOIN invoices inv ON inv.firm_id = f.id
            WHERE 1=1
        ";
        $params = [];

        if (trim($search) !== '') {
            $query .= " AND (LOWER(f.name) LIKE ? OR LOWER(COALESCE(f.gstin, '')) LIKE ? OR LOWER(u.name) LIKE ? OR LOWER(COALESCE(u.email, '')) LIKE ? OR u.phone LIKE ?)";
            $term = '%' . strtolower(trim($search)) . '%';
            $params = [$term, $term, $term, $term, $term];
        }

        $query .= " GROUP BY f.id ORDER BY f.created_at DESC";
        $stmt = self::db()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getAllInvoices(array $filters = []): array {
        $query = "
            SELECT 
                inv.*,
                f.name as firm_name,
                u.name as owner_name,
                u.phone as owner_phone,
                u.email as owner_email
            FROM invoices inv
            JOIN firms f ON f.id = inv.firm_id
            JOIN users u ON u.id = f.user_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (inv.invoice_number LIKE ? OR LOWER(inv.party_name) LIKE ? OR LOWER(f.name) LIKE ? OR u.phone LIKE ? OR LOWER(u.name) LIKE ?)";
            $term = '%' . strtolower(trim($filters['search'])) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        if (!empty($filters['type'])) {
            $query .= " AND inv.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['payment_status'])) {
            $query .= " AND inv.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        $query .= " ORDER BY inv.created_at DESC LIMIT 200";
        $stmt = self::db()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getRecentActivities(int $limit = 15): array {
        $users = self::db()->query("
            SELECT 'user_registered' as type, name as title, COALESCE(phone, email, 'New Account') as subtitle, created_at, id as entity_id
            FROM users
            ORDER BY created_at DESC LIMIT {$limit}
        ")->fetchAll();

        $firms = self::db()->query("
            SELECT 'firm_created' as type, f.name as title, u.name as subtitle, f.created_at, f.id as entity_id
            FROM firms f
            JOIN users u ON u.id = f.user_id
            ORDER BY f.created_at DESC LIMIT {$limit}
        ")->fetchAll();

        $invoiceRows = self::db()->query("
            SELECT 'invoice_created' as type, inv.invoice_number, inv.grand_total, inv.party_name, f.name as firm_name, inv.created_at, inv.id as entity_id
            FROM invoices inv
            JOIN firms f ON f.id = inv.firm_id
            ORDER BY inv.created_at DESC LIMIT {$limit}
        ")->fetchAll();

        $invoices = [];
        foreach ($invoiceRows as $r) {
            $invoices[] = [
                'type' => 'invoice_created',
                'title' => ($r['invoice_number'] ?? '') . ' - ₹ ' . ($r['grand_total'] ?? 0),
                'subtitle' => ($r['party_name'] ?? '') . ' (' . ($r['firm_name'] ?? '') . ')',
                'created_at' => $r['created_at'],
                'entity_id' => $r['entity_id']
            ];
        }

        $all = array_merge($users, $firms, $invoices);
        usort($all, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        return array_slice($all, 0, $limit);
    }

    public static function logAction(?int $adminId, ?string $adminName, string $action, ?string $targetType = null, ?string $targetId = null, ?string $details = null, ?string $ip = null): void {
        $stmt = self::db()->prepare("
            INSERT INTO admin_audit_logs (admin_id, admin_name, action, target_type, target_id, details, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $adminId,
            $adminName ?: 'Admin',
            $action,
            $targetType,
            (string)$targetId,
            $details,
            $ip
        ]);
    }

    public static function getRecentLogs(int $limit = 25): array {
        $stmt = self::db()->query("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT {$limit}");
        return $stmt->fetchAll();
    }

    public static function getPlatformSetting(string $key, $defaultValue = null) {
        $stmt = self::db()->prepare("SELECT value FROM platform_settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $defaultValue;
    }

    public static function setPlatformSetting(string $key, $value): void {
        if (DB::isMysql()) {
            $stmt = self::db()->prepare("
                INSERT INTO platform_settings (`key`, `value`, updated_at)
                VALUES (?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP
            ");
        } else {
            $stmt = self::db()->prepare("
                INSERT INTO platform_settings (`key`, `value`, updated_at)
                VALUES (?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(`key`) DO UPDATE SET `value` = excluded.`value`, updated_at = CURRENT_TIMESTAMP
            ");
        }
        $stmt->execute([$key, (string)$value]);
    }

    public static function getAllPlatformSettings(): array {
        $rows = self::db()->query("SELECT `key`, `value` FROM platform_settings")->fetchAll();
        $settings = [
            'max_firms_limit' => '2',
            'max_upload_size_mb' => '2',
            'platform_announcement' => '',
            'platform_announcement_type' => 'info',
            'enable_announcement' => '0',
            'maintenance_mode' => '0'
        ];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }
}
