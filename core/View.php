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
require_once __DIR__ . '/../models/Setting.php';

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
        $firmSettings = ($activeFirm && !empty($activeFirm['id'])) ? Setting::get((int)$activeFirm['id']) : Setting::DEFAULT_SETTINGS;

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
            'firmSettings' => $firmSettings,
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

/**
 * Format a date string into firm's configured date format (defaults to Day/Month/Year: DD/MM/YYYY)
 */
function formatDate(?string $dateStr, ?string $customFormat = null): string {
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return '';
    }

    $targetFormat = $customFormat;
    if (!$targetFormat) {
        $firm = Auth::getActiveFirm();
        if ($firm && !empty($firm['id'])) {
            $settings = Setting::get((int)$firm['id']);
            $targetFormat = $settings['general']['date_format'] ?? 'DD/MM/YYYY';
        } else {
            $targetFormat = 'DD/MM/YYYY';
        }
    }

    $phpFormat = match(strtoupper(trim($targetFormat))) {
        'DD-MM-YYYY' => 'd-m-Y',
        'YYYY-MM-DD' => 'Y-m-d',
        'DD/MM/YYYY' => 'd/m/Y',
        default => 'd/m/Y'
    };

    $trimmed = trim($dateStr);

    // If incoming string is in YYYY-MM-DD (e.g. from MySQL)
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $trimmed, $m)) {
        $dt = DateTime::createFromFormat('Y-m-d', "{$m[1]}-{$m[2]}-{$m[3]}");
        if ($dt) {
            return $dt->format($phpFormat);
        }
    }

    // If incoming string is already in DD/MM/YYYY
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $trimmed, $m)) {
        $dt = DateTime::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', (int)$m[1], (int)$m[2], (int)$m[3]));
        if ($dt) {
            return $dt->format($phpFormat);
        }
    }

    // If incoming string is in DD-MM-YYYY
    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $trimmed, $m)) {
        $dt = DateTime::createFromFormat('d-m-Y', sprintf('%02d-%02d-%04d', (int)$m[1], (int)$m[2], (int)$m[3]));
        if ($dt) {
            return $dt->format($phpFormat);
        }
    }

    $ts = strtotime($trimmed);
    if ($ts !== false) {
        return date($phpFormat, $ts);
    }

    return $trimmed;
}

/**
 * Normalize any incoming date string (DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD) into standard MySQL YYYY-MM-DD format
 */
function normalizeDate(?string $dateStr): ?string {
    if (empty($dateStr)) {
        return null;
    }
    $trimmed = trim($dateStr);
    if ($trimmed === '' || $trimmed === '0000-00-00' || $trimmed === '0000-00-00 00:00:00') {
        return null;
    }

    // Already YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
        return $trimmed;
    }

    // DD/MM/YYYY -> YYYY-MM-DD
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $trimmed, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }

    // DD-MM-YYYY -> YYYY-MM-DD
    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $trimmed, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }

    $ts = strtotime($trimmed);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }

    return null;
}
