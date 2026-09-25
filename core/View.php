<?php
/**
 * Template View Renderer
 * Provides layout support, global variables, and HTML escaping
 * RACE FINANCE - Small Business Billing & Inventory System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/CSRF.php';
require_once __DIR__ . '/Flash.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Firm.php';

class View {
    public static function render(string $viewName, array $data = [], bool $layout = true): void {
        // Strip .php or .ejs extension if provided
        $viewPath = preg_replace('/\.(php|ejs)$/', '', $viewName);
        $fullPath = ROOT_DIR . '/views/' . $viewPath . '.php';

        if (!file_exists($fullPath)) {
            // Check if .ejs file exists (for development warning)
            $ejsPath = ROOT_DIR . '/views/' . $viewPath . '.ejs';
            if (file_exists($ejsPath)) {
                throw new Exception("View template '$viewName' exists as .ejs but needs to be converted to .php");
            }
            throw new Exception("View template not found: $fullPath");
        }

        // Global variables for all views
        $currentUser = Auth::user();
        $isAdmin = Auth::isAdmin();
        $activeFirm = Auth::getActiveFirm();
        $userFirms = ($currentUser && !$isAdmin) ? Firm::getByUserId($currentUser['id']) : [];

        $platformSettings = [];
        try {
            $platformSettings = Admin::getAllPlatformSettings();
        } catch (Throwable $e) {
            $platformSettings = [];
        }

        $globals = [
            'appUrl' => APP_URL,
            'appDomain' => parse_url(APP_URL, PHP_URL_HOST) ?: 'racefinance.site',
            'supportPhone' => SUPPORT_PHONE,
            'supportPhoneRaw' => SUPPORT_PHONE_RAW,
            'user' => $currentUser,
            'isAdmin' => $isAdmin,
            'activeFirm' => $activeFirm,
            'userFirms' => $userFirms,
            'csrfToken' => CSRF::getToken(),
            'success_msg' => Flash::get('success_msg'),
            'error_msg' => Flash::get('error_msg'),
            'info_msg' => Flash::get('info_msg'),
            'currentUrl' => $_SERVER['REQUEST_URI'] ?? '/',
            'activeMenu' => $data['activeMenu'] ?? '',
            'platformSettings' => $platformSettings
        ];

        // Merge globals with view data ($data overrides globals if key matches)
        $variables = array_merge($globals, $data);
        extract($variables, EXTR_SKIP);

        if ($layout) {
            require ROOT_DIR . '/views/partials/header.php';
            require $fullPath;
            require ROOT_DIR . '/views/partials/footer.php';
        } else {
            require $fullPath;
        }
    }
}

// Global convenience helper
function view(string $viewName, array $data = [], bool $layout = true): void {
    View::render($viewName, $data, $layout);
}

// Global XSS escaping helper
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
