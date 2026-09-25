<?php
/**
 * Application Configuration & Environment Loader
 * RACE FINANCE - Small Business Billing & Inventory System
 */

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

// Load .env file if present
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Helper to get environment variable with fallback
function env($key, $default = null) {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return $val;
}

// Detect Base URL
$appUrl = env('APP_URL');
if (!$appUrl) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appUrl = $protocol . '://' . $host;
}
$appUrl = rtrim($appUrl, '/');

define('APP_URL', $appUrl);
define('APP_NAME', 'RACE FINANCE');
define('SUPPORT_PHONE', env('SUPPORT_PHONE', '+91 9672847747'));
define('SUPPORT_PHONE_RAW', preg_replace('/[^0-9]/', '', SUPPORT_PHONE));
define('ROOT_DIR', dirname(__DIR__));
define('UPLOADS_DIR', ROOT_DIR . '/public/uploads');

// Error reporting based on environment
$isProduction = env('NODE_ENV') === 'production' || env('APP_ENV') === 'production';
if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Session configuration - 180 Days persistent session (matching Node app)
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 180 * 24 * 60 * 60; // 180 days
    ini_set('session.gc_maxlifetime', (string)$lifetime);
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
