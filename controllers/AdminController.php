<?php
/**
 * Admin Controller
 * Platform Command Center, Subscriber Management, Diagnostics & Governance
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Firm.php';

class AdminController {
    private static function validatePasswordComplexity(string $pwd): ?string {
        if (strlen($pwd) < 8) {
            return 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[a-zA-Z]/', $pwd) || !preg_match('/[0-9]/', $pwd)) {
            return 'Password must contain at least one letter and one number.';
        }
        return null;
    }

    public function getDashboard(): void {
        Auth::requireAdmin();

        try {
            $metrics = Admin::getDashboardMetrics();
            $recentActivities = Admin::getRecentActivities(12);
            $recentLogs = Admin::getRecentLogs(6);

            $uploadsDir = ROOT_DIR . '/public/uploads';
            $uploadsBytes = 0;
            $uploadsCount = 0;
            if (is_dir($uploadsDir)) {
                $files = scandir($uploadsDir);
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..') continue;
                    $fp = $uploadsDir . '/' . $f;
                    if (is_file($fp) && !is_link($fp)) {
                        $uploadsBytes += filesize($fp);
                        $uploadsCount++;
                    }
                }
            }

            $dbSize = 0;
            if (DB::isMysql()) {
                try {
                    $dbName = env('DB_DATABASE') ?: env('DB_NAME');
                    $q = DB::getConnection()->prepare("SELECT SUM(data_length + index_length) AS s FROM information_schema.TABLES WHERE table_schema = ?");
                    $q->execute([$dbName]);
                    $row = $q->fetch();
                    $dbSize = (int)($row['s'] ?? 0);
                } catch (Throwable $e) {}
            } else {
                $sqlitePath = ROOT_DIR . '/data/biller.db';
                if (file_exists($sqlitePath)) {
                    $dbSize = filesize($sqlitePath);
                }
            }

            view('admin/dashboard', [
                'title' => 'Platform Command Center',
                'activeMenu' => 'admin-dashboard',
                'metrics' => $metrics,
                'recentActivities' => $recentActivities,
                'recentLogs' => $recentLogs,
                'storage' => [
                    'uploads' => [
                        'totalBytes' => $uploadsBytes,
                        'fileCount' => $uploadsCount,
                        'sizeFormatted' => Security::formatBytes($uploadsBytes)
                    ],
                    'dbSize' => Security::formatBytes($dbSize),
                    'totalStorage' => Security::formatBytes($uploadsBytes + $dbSize)
                ]
            ]);
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to load admin dashboard.'));
            header('Location: /dashboard');
            exit;
        }
    }

    public function getUsers(): void {
        Auth::requireAdmin();

        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        $users = Admin::getAllUsers($search, $role, $status);

        $newlyCreatedUser = $_SESSION['newlyCreatedUser'] ?? null;
        unset($_SESSION['newlyCreatedUser']);

        view('admin/users', [
            'title' => 'Subscriber & User Management',
            'activeMenu' => 'admin-users',
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'newlyCreatedUser' => $newlyCreatedUser
        ]);
    }

    public function getUserDetails(array $params): void {
        Auth::requireAdmin();
        $targetId = (int)($params['id'] ?? 0);
        $data = Admin::getUserDeepInfo($targetId);

        if (!$data || !$data['user']) {
            Flash::set('error_msg', 'User not found.');
            header('Location: /admin/users');
            exit;
        }

        view('admin/user-details', [
            'title' => "Subscriber: {$data['user']['name']} (" . ($data['user']['phone'] ?? $data['user']['email']) . ")",
            'activeMenu' => 'admin-users',
            'targetUser' => $data['user'],
            'firms' => $data['firms'],
            'parties' => $data['parties'],
            'items' => $data['items'],
            'invoices' => $data['invoices'],
            'financials' => $data['financials']
        ]);
    }

    public function postCreateUser(): void {
        Auth::requireAdmin();
        $admin = Auth::user();

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Subscriber business/owner name is required.');
                header('Location: /admin/users');
                exit;
            }

            $rawPhone = trim($_POST['phone'] ?? '');
            $cleanedPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strlen($cleanedPhone) < 10) {
                Flash::set('error_msg', 'A valid 10-digit mobile phone number is required as the User ID.');
                header('Location: /admin/users');
                exit;
            }

            if (User::findByPhone($cleanedPhone)) {
                Flash::set('error_msg', "A subscriber account with phone number {$cleanedPhone} already exists.");
                header('Location: /admin/users');
                exit;
            }

            $password = $_POST['password'] ?? '';
            $passError = self::validatePasswordComplexity($password);
            if ($passError) {
                Flash::set('error_msg', $passError);
                header('Location: /admin/users');
                exit;
            }

            $email = trim($_POST['email'] ?? '');
            if ($email !== '') {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Flash::set('error_msg', 'Invalid Email: Please provide a valid email address.');
                    header('Location: /admin/users');
                    exit;
                }
                if (User::findByEmail($email)) {
                    Flash::set('error_msg', 'A subscriber with this email address already exists.');
                    header('Location: /admin/users');
                    exit;
                }
            }

            $assignedRole = (($_POST['role'] ?? '') === 'admin') ? 'admin' : 'user';
            $subscriptionExpiresAt = null;

            if ($assignedRole !== 'admin') {
                $duration = $_POST['subscription_duration'] ?? '365';
                if ($duration === 'custom' && !empty($_POST['custom_expiry_date'])) {
                    $subscriptionExpiresAt = date('Y-m-d H:i:s', strtotime($_POST['custom_expiry_date']));
                } else {
                    $days = (int)$duration ?: 365;
                    $subscriptionExpiresAt = date('Y-m-d H:i:s', time() + ($days * 86400));
                }
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

            $newUser = User::create([
                'name' => $name,
                'email' => $email ?: null,
                'phone' => $cleanedPhone,
                'password' => $hashedPassword,
                'role' => $assignedRole,
                'status' => 'active',
                'subscription_expires_at' => $subscriptionExpiresAt
            ]);

            $formattedExpiry = $subscriptionExpiresAt ? date('d M Y', strtotime($subscriptionExpiresAt)) : 'Permanent (Admin)';

            $_SESSION['newlyCreatedUser'] = [
                'id' => $newUser['id'],
                'name' => $newUser['name'],
                'phone' => $newUser['phone'],
                'rawPassword' => $password,
                'role' => $newUser['role'],
                'expiresAt' => $formattedExpiry
            ];

            Admin::logAction(
                (int)$admin['id'],
                $admin['name'],
                'CREATE_SUBSCRIBER',
                'User',
                (string)$newUser['id'],
                "Created subscriber account \"{$newUser['name']}\" (Phone ID: {$newUser['phone']}) with role {$newUser['role']}, validity until {$formattedExpiry}",
                Security::getClientIp()
            );

            Flash::set('success_msg', "Subscriber account \"{$newUser['name']}\" (ID: {$newUser['phone']}) registered successfully.");
            header('Location: /admin/users');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to create subscriber.'));
            header('Location: /admin/users');
            exit;
        }
    }

    public function postRenewSubscription(array $params): void {
        Auth::requireAdmin();
        $admin = Auth::user();
        $targetId = (int)($params['id'] ?? 0);
        $user = User::findById($targetId);

        if (!$user) {
            Flash::set('error_msg', 'User not found.');
            header('Location: /admin/users');
            exit;
        }

        try {
            $days = (int)($_POST['duration_days'] ?? 365);
            $updated = User::extendSubscription($targetId, $days);
            $formattedExpiry = date('d M Y', strtotime($updated['subscription_expires_at']));

            Admin::logAction(
                (int)$admin['id'],
                $admin['name'],
                'RENEW_SUBSCRIPTION',
                'User',
                (string)$targetId,
                "Renewed subscription for \"{$user['name']}\" ({$user['phone']}) for {$days} days. New expiry: {$formattedExpiry}",
                Security::getClientIp()
            );

            Flash::set('success_msg', "Subscription for \"{$user['name']}\" extended by {$days} days! Valid until {$formattedExpiry}.");
            header('Location: ' . Security::getSafeRefererUrl('/admin/users'));
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to renew subscription.'));
            header('Location: /admin/users');
            exit;
        }
    }

    public function postToggleUserRole(array $params): void {
        Auth::requireAdmin();
        $admin = Auth::user();
        $targetId = (int)($params['id'] ?? 0);

        if ($targetId === (int)$admin['id']) {
            Flash::set('error_msg', 'You cannot change your own administrator role.');
            header('Location: /admin/users');
            exit;
        }

        $user = User::findById($targetId);
        if (!$user) {
            Flash::set('error_msg', 'User not found.');
            header('Location: /admin/users');
            exit;
        }

        $newRole = ($user['role'] === 'admin') ? 'user' : 'admin';
        User::updateRole($targetId, $newRole);

        Admin::logAction(
            (int)$admin['id'],
            $admin['name'],
            'CHANGE_ROLE',
            'User',
            (string)$targetId,
            "Changed role of \"{$user['name']}\" from {$user['role']} to {$newRole}",
            Security::getClientIp()
        );

        Flash::set('success_msg', "Role for \"{$user['name']}\" changed to " . strtoupper($newRole) . ".");
        header('Location: /admin/users');
        exit;
    }

    public function postToggleUserStatus(array $params): void {
        Auth::requireAdmin();
        $admin = Auth::user();
        $targetId = (int)($params['id'] ?? 0);

        if ($targetId === (int)$admin['id']) {
            Flash::set('error_msg', 'You cannot suspend your own account.');
            header('Location: /admin/users');
            exit;
        }

        $user = User::findById($targetId);
        if (!$user) {
            Flash::set('error_msg', 'User not found.');
            header('Location: /admin/users');
            exit;
        }

        $newStatus = ($user['status'] === 'active') ? 'suspended' : 'active';
        User::updateStatus($targetId, $newStatus);

        Admin::logAction(
            (int)$admin['id'],
            $admin['name'],
            ($newStatus === 'suspended') ? 'SUSPEND_SUBSCRIBER' : 'ACTIVATE_SUBSCRIBER',
            'User',
            (string)$targetId,
            "Changed status of \"{$user['name']}\" to {$newStatus}",
            Security::getClientIp()
        );

        Flash::set('success_msg', "Account \"{$user['name']}\" is now " . strtoupper($newStatus) . ".");
        header('Location: /admin/users');
        exit;
    }

    public function postResetUserPassword(array $params): void {
        Auth::requireAdmin();
        $admin = Auth::user();
        $targetId = (int)($params['id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';

        $passError = self::validatePasswordComplexity($newPass);
        if ($passError) {
            Flash::set('error_msg', $passError);
            header("Location: /admin/users/{$targetId}");
            exit;
        }

        $user = User::findById($targetId);
        if (!$user) {
            Flash::set('error_msg', 'User not found.');
            header('Location: /admin/users');
            exit;
        }

        $hashed = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 10]);
        User::updatePassword($targetId, $hashed);

        Admin::logAction(
            (int)$admin['id'],
            $admin['name'],
            'RESET_PASSWORD',
            'User',
            (string)$targetId,
            "Admin reset password for subscriber \"{$user['name']}\"",
            Security::getClientIp()
        );

        Flash::set('success_msg', "Password for \"{$user['name']}\" has been updated successfully.");
        header('Location: /admin/users');
        exit;
    }

    public function postDeleteUser(array $params): void {
        Auth::requireAdmin();
        $admin = Auth::user();
        $targetId = (int)($params['id'] ?? 0);

        if ($targetId === (int)$admin['id']) {
            Flash::set('error_msg', 'You cannot delete your own admin account.');
            header('Location: /admin/users');
            exit;
        }

        $user = User::findById($targetId);
        if (!$user) {
            Flash::set('error_msg', 'User not found.');
            header('Location: /admin/users');
            exit;
        }

        User::delete($targetId);

        Admin::logAction(
            (int)$admin['id'],
            $admin['name'],
            'DELETE_USER',
            'User',
            (string)$targetId,
            "Deleted subscriber account \"{$user['name']}\" and all associated business records",
            Security::getClientIp()
        );

        Flash::set('success_msg', "Subscriber \"{$user['name']}\" and all records deleted.");
        header('Location: /admin/users');
        exit;
    }

    public function getFirms(): void {
        Auth::requireAdmin();
        $search = $_GET['search'] ?? '';
        $firms = Admin::getAllFirms($search);

        view('admin/firms', [
            'title' => 'Registered Business Firms Directory',
            'activeMenu' => 'admin-firms',
            'firms' => $firms,
            'search' => $search
        ]);
    }

    public function getInvoices(): void {
        Auth::requireAdmin();
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $paymentStatus = $_GET['payment_status'] ?? '';

        $invoices = Admin::getAllInvoices([
            'search' => $search,
            'type' => $type,
            'payment_status' => $paymentStatus
        ]);

        view('admin/invoices', [
            'title' => 'Platform Invoices & Ledger Explorer',
            'activeMenu' => 'admin-invoices',
            'invoices' => $invoices,
            'search' => $search,
            'type' => $type,
            'payment_status' => $paymentStatus
        ]);
    }

    public function getSystemHealth(): void {
        Auth::requireAdmin();

        $uploadsDir = ROOT_DIR . '/public/uploads';
        $uploadsBytes = 0;
        $uploadsCount = 0;
        if (is_dir($uploadsDir)) {
            $files = scandir($uploadsDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $fp = $uploadsDir . '/' . $f;
                if (is_file($fp) && !is_link($fp)) {
                    $uploadsBytes += filesize($fp);
                    $uploadsCount++;
                }
            }
        }

        $dbSize = 0;
        if (DB::isMysql()) {
            try {
                $dbName = env('DB_DATABASE') ?: env('DB_NAME');
                $q = DB::getConnection()->prepare("SELECT SUM(data_length + index_length) AS s FROM information_schema.TABLES WHERE table_schema = ?");
                $q->execute([$dbName]);
                $row = $q->fetch();
                $dbSize = (int)($row['s'] ?? 0);
            } catch (Throwable $e) {}
        } else {
            $sqlitePath = ROOT_DIR . '/data/biller.db';
            if (file_exists($sqlitePath)) {
                $dbSize = filesize($sqlitePath);
            }
        }

        $allUsers = Admin::getAllUsers();
        $regularUsers = array_filter($allUsers, fn($u) => $u['role'] !== 'admin');
        $totalSubscriberUsedBytes = 0;
        foreach ($regularUsers as $u) {
            if (!empty($u['storage']['totalUsedBytes'])) {
                $totalSubscriberUsedBytes += $u['storage']['totalUsedBytes'];
            }
        }

        $totalPoolQuotaMB = count($regularUsers) * 200;
        $totalPoolUsedMB = round($totalSubscriberUsedBytes / (1024 * 1024), 2);
        $poolUsagePercentage = $totalPoolQuotaMB > 0 ? round(($totalPoolUsedMB / $totalPoolQuotaMB) * 100, 1) : 0;

        $topStorageUsers = $regularUsers;
        usort($topStorageUsers, fn($a, $b) => ($b['storage']['totalUsedBytes'] ?? 0) - ($a['storage']['totalUsedBytes'] ?? 0));

        view('admin/system', [
            'title' => 'System Health & Maintenance',
            'activeMenu' => 'admin-system',
            'sysInfo' => [
                'uptime' => 0,
                'cpuCount' => 2,
                'freeMem' => 'N/A',
                'totalMem' => 'N/A'
            ],
            'storage' => [
                'uploads' => [
                    'totalBytes' => $uploadsBytes,
                    'fileCount' => $uploadsCount,
                    'sizeFormatted' => Security::formatBytes($uploadsBytes)
                ],
                'dbSize' => Security::formatBytes($dbSize),
                'totalStorage' => Security::formatBytes($uploadsBytes + $dbSize),
                'poolQuotaMB' => $totalPoolQuotaMB,
                'poolUsedMB' => $totalPoolUsedMB,
                'poolUsagePercentage' => $poolUsagePercentage,
                'userQuotaMB' => 200,
                'subscribersCount' => count($regularUsers),
                'topStorageUsers' => $topStorageUsers
            ],
            'auditLogs' => Admin::getRecentLogs(35)
        ]);
    }

    public function postVacuumDb(): void {
        Auth::requireAdmin();
        $admin = Auth::user();

        try {
            if (DB::isSqlite()) {
                DB::getConnection()->exec("VACUUM; PRAGMA optimize;");
            } else {
                DB::getConnection()->exec("ANALYZE TABLE users, firms, parties, items, invoices, invoice_items, payments;");
            }

            Admin::logAction(
                (int)$admin['id'],
                $admin['name'],
                'OPTIMIZE_DATABASE',
                'System',
                DB::getDriver(),
                'Executed database index optimization',
                Security::getClientIp()
            );

            Flash::set('success_msg', 'Database index performance optimized successfully!');
            header('Location: /admin/system');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to optimize database.'));
            header('Location: /admin/system');
            exit;
        }
    }

    public function postCleanOrphans(): void {
        Auth::requireAdmin();
        $admin = Auth::user();

        try {
            $uploadsDir = ROOT_DIR . '/public/uploads';
            $cleanedCount = 0;
            $freedBytes = 0;

            if (is_dir($uploadsDir)) {
                $rows = DB::getConnection()->query("SELECT logo_path, signature_path FROM firms")->fetchAll();
                $activeFiles = [];
                foreach ($rows as $r) {
                    if (!empty($r['logo_path'])) $activeFiles[basename($r['logo_path'])] = true;
                    if (!empty($r['signature_path'])) $activeFiles[basename($r['signature_path'])] = true;
                }

                $files = scandir($uploadsDir);
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..') continue;
                    if (!isset($activeFiles[$f])) {
                        $fp = $uploadsDir . '/' . $f;
                        if (is_file($fp) && !is_link($fp)) {
                            $freedBytes += filesize($fp);
                            @unlink($fp);
                            $cleanedCount++;
                        }
                    }
                }
            }

            Admin::logAction(
                (int)$admin['id'],
                $admin['name'],
                'CLEAN_ORPHAN_FILES',
                'Storage',
                'Uploads',
                "Purged {$cleanedCount} orphaned files, freed " . Security::formatBytes($freedBytes),
                Security::getClientIp()
            );

            Flash::set('success_msg', "Cleaned {$cleanedCount} orphaned files and reclaimed " . Security::formatBytes($freedBytes) . " of storage!");
            header('Location: /admin/system');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to clean orphaned files.'));
            header('Location: /admin/system');
            exit;
        }
    }

    public function getDownloadDb(): void {
        Flash::set('info_msg', 'Database download requires password authentication.');
        header('Location: /admin/system');
        exit;
    }

    public function postDownloadDb(): void {
        Auth::requireAdmin();
        $admin = Auth::user();
        $adminPass = $_POST['admin_password'] ?? '';

        $hash = User::getPasswordHashById((int)$admin['id']);
        if (!$hash || !password_verify($adminPass, $hash)) {
            Flash::set('error_msg', 'Authorization failed: Incorrect administrator password.');
            header('Location: /admin/system');
            exit;
        }

        $sqlitePath = ROOT_DIR . '/data/biller.db';
        if (file_exists($sqlitePath)) {
            $timestamp = date('Y-m-d_H-i-s');
            header('Content-Type: application/x-sqlite3');
            header("Content-Disposition: attachment; filename=\"RACE_FINANCE_DB_SNAPSHOT_{$timestamp}.db\"");
            readfile($sqlitePath);
            exit;
        }

        Flash::set('error_msg', 'Direct snapshot is available for SQLite installations. For MySQL, please use cPanel Backup or phpMyAdmin Export.');
        header('Location: /admin/system');
        exit;
    }

    public function getSettings(): void {
        Auth::requireAdmin();
        $settings = Admin::getAllPlatformSettings();

        view('admin/settings', [
            'title' => 'SaaS Platform Governance Settings',
            'activeMenu' => 'admin-settings',
            'settings' => $settings
        ]);
    }

    public function postSettings(): void {
        Auth::requireAdmin();
        $admin = Auth::user();

        try {
            $maxFirms = (int)($_POST['max_firms_limit'] ?? 2);
            $maxUpload = (int)($_POST['max_upload_size_mb'] ?? 2);
            $announcement = trim($_POST['platform_announcement'] ?? '');
            $annType = $_POST['platform_announcement_type'] ?? 'info';
            $enableAnn = !empty($_POST['enable_announcement']) ? '1' : '0';
            $maintenance = !empty($_POST['maintenance_mode']) ? '1' : '0';

            Admin::setPlatformSetting('max_firms_limit', $maxFirms);
            Admin::setPlatformSetting('max_upload_size_mb', $maxUpload);
            Admin::setPlatformSetting('platform_announcement', $announcement);
            Admin::setPlatformSetting('platform_announcement_type', $annType);
            Admin::setPlatformSetting('enable_announcement', $enableAnn);
            Admin::setPlatformSetting('maintenance_mode', $maintenance);

            Admin::logAction(
                (int)$admin['id'],
                $admin['name'],
                'UPDATE_PLATFORM_SETTINGS',
                'Settings',
                'Global',
                "Updated platform governance settings: Max firms = {$maxFirms}, Announcement active = {$enableAnn}",
                Security::getClientIp()
            );

            Flash::set('success_msg', 'Platform governance settings updated successfully.');
            header('Location: /admin/settings');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save settings.'));
            header('Location: /admin/settings');
            exit;
        }
    }
}
