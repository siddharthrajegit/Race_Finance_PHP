<?php
/**
 * Authentication & Authorization Manager
 * RACE FINANCE - Small Business Billing & Inventory System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Flash.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Firm.php';

class Auth {
    private static ?array $cachedUser = null;
    private static ?array $cachedFirm = null;

    public static function user(): ?array {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $user = User::findById($userId);
        if ($user) {
            self::$cachedUser = $user;
        }
        return self::$cachedUser;
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function isAuthenticated(): bool {
        return self::user() !== null;
    }

    public static function isAdmin(): bool {
        $u = self::user();
        return $u && ($u['role'] === 'admin');
    }

    public static function login(array $user): void {
        // Regenerate session ID upon login to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        self::$cachedUser = $user;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        self::$cachedUser = null;
        self::$cachedFirm = null;
    }

    public static function requireLogin(): void {
        $user = self::user();
        if (!$user) {
            Flash::set('error_msg', 'Please sign in to access this page.');
            $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
            $_SESSION['returnTo'] = Security::getSafeRedirectUrl($currentUri, '/dashboard');
            header('Location: /auth/login');
            exit;
        }

        if ($user['status'] === 'suspended') {
            self::logout();
            Flash::set('error_msg', 'Your account has been suspended by the platform administrator.');
            header('Location: /auth/login');
            exit;
        }

        if ($user['role'] !== 'admin' && !empty($user['subscription_expires_at'])) {
            if (strtotime($user['subscription_expires_at']) < time()) {
                self::logout();
                Flash::set('error_msg', 'Your subscription has expired. All your billing data and records are safely preserved. Please contact the administrator on WhatsApp to renew.');
                header('Location: /auth/login');
                exit;
            }
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            Flash::set('error_msg', 'Access denied. Administrator privileges required.');
            header('Location: /dashboard');
            exit;
        }
    }

    public static function requireUserOnly(): void {
        self::requireLogin();
        if (self::isAdmin()) {
            Flash::set('error_msg', 'Admins manage the platform and do not have access to individual business billing, party, item, or inventory features.');
            header('Location: /admin');
            exit;
        }
    }

    public static function getActiveFirm(): ?array {
        if (self::$cachedFirm !== null) {
            return self::$cachedFirm;
        }

        $user = self::user();
        if (!$user || $user['role'] === 'admin') {
            return null;
        }

        $firms = Firm::getByUserId($user['id']);
        if (empty($firms)) {
            return null;
        }

        $activeFirmId = $_SESSION['activeFirmId'] ?? null;
        $activeFirm = null;

        if ($activeFirmId) {
            foreach ($firms as $f) {
                if ((int)$f['id'] === (int)$activeFirmId) {
                    $activeFirm = $f;
                    break;
                }
            }
        }

        if (!$activeFirm) {
            // Default to is_default = 1 or first firm
            foreach ($firms as $f) {
                if (!empty($f['is_default'])) {
                    $activeFirm = $f;
                    break;
                }
            }
            if (!$activeFirm) {
                $activeFirm = $firms[0];
            }
            $_SESSION['activeFirmId'] = $activeFirm['id'];
        }

        self::$cachedFirm = $activeFirm;
        return self::$cachedFirm;
    }

    public static function requireActiveFirm(): ?array {
        $firm = self::getActiveFirm();
        if (!$firm) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (!str_starts_with($uri, '/firms/create') && !str_starts_with($uri, '/auth') && !str_starts_with($uri, '/firms') && !str_starts_with($uri, '/admin')) {
                Flash::set('info_msg', 'Welcome! Please create your first business firm to get started.');
                header('Location: /firms/create');
                exit;
            }
        }
        return $firm;
    }
}
