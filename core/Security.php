<?php
/**
 * Security Utilities
 * Includes: Progressive Math CAPTCHA, Open Redirect Protection, Path Traversal Defense, Input Sanitization
 * RACE FINANCE - Small Business Billing & Inventory System
 */

require_once __DIR__ . '/../config/config.php';

class Security {
    public const MAX_FAILED_ATTEMPTS = 3;
    public const WINDOW_SECONDS = 900; // 15 minutes

    public static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public static function generateCaptcha(): array {
        $operators = ['+', '-', '×'];
        $op = $operators[array_rand($operators)];
        
        if ($op === '+') {
            $num1 = rand(3, 17);
            $num2 = rand(2, 16);
            $answer = $num1 + $num2;
        } elseif ($op === '-') {
            $num1 = rand(10, 24);
            $num2 = rand(1, 9);
            $answer = $num1 - $num2;
        } else {
            $num1 = rand(2, 9);
            $num2 = rand(2, 8);
            $answer = $num1 * $num2;
        }

        $question = "What is $num1 $op $num2 = ?";
        $_SESSION['captchaQuestion'] = $question;
        $_SESSION['captchaAnswer'] = (string)$answer;

        return ['question' => $question, 'answer' => (string)$answer];
    }

    public static function isCaptchaRequired(?string $identifier = null): bool {
        $failedCount = $_SESSION['loginFailedAttempts'] ?? 0;
        if ($failedCount >= self::MAX_FAILED_ATTEMPTS) {
            return true;
        }

        // Track by IP in session storage or cache
        $ip = self::getClientIp();
        if (isset($_SESSION['ip_tracker'][$ip])) {
            $record = $_SESSION['ip_tracker'][$ip];
            if ($record['count'] >= self::MAX_FAILED_ATTEMPTS && (time() - $record['lastAttempt']) <= self::WINDOW_SECONDS) {
                return true;
            }
        }

        return false;
    }

    public static function validateCaptcha(?string $submittedAnswer): bool {
        if (empty($_SESSION['captchaAnswer'])) {
            return false;
        }

        $expected = trim((string)$_SESSION['captchaAnswer']);
        $given = trim((string)$submittedAnswer);

        $isValid = ($given !== '' && $given === $expected);

        unset($_SESSION['captchaAnswer'], $_SESSION['captchaQuestion']);
        return $isValid;
    }

    public static function recordFailedAttempt(?string $identifier = null): void {
        $_SESSION['loginFailedAttempts'] = ($_SESSION['loginFailedAttempts'] ?? 0) + 1;

        $ip = self::getClientIp();
        if (!isset($_SESSION['ip_tracker'])) {
            $_SESSION['ip_tracker'] = [];
        }
        $prevCount = $_SESSION['ip_tracker'][$ip]['count'] ?? 0;
        $_SESSION['ip_tracker'][$ip] = [
            'count' => $prevCount + 1,
            'lastAttempt' => time()
        ];

        if (self::isCaptchaRequired($identifier)) {
            self::generateCaptcha();
        }
    }

    public static function clearFailedAttempts(?string $identifier = null): void {
        unset($_SESSION['loginFailedAttempts'], $_SESSION['captchaAnswer'], $_SESSION['captchaQuestion']);
        $ip = self::getClientIp();
        unset($_SESSION['ip_tracker'][$ip]);
    }

    public static function getSafeRedirectUrl(?string $targetUrl, string $defaultRedirect = '/dashboard', ?string $userRole = null): string {
        if (empty($targetUrl) || !is_string($targetUrl)) {
            return $defaultRedirect;
        }

        $trimmed = trim($targetUrl);

        if (
            !str_starts_with($trimmed, '/') ||
            str_starts_with($trimmed, '//') ||
            str_starts_with($trimmed, '/\\') ||
            str_contains($trimmed, '\\') ||
            preg_match('/^[\/][\/\\\\]/', $trimmed) ||
            preg_match('/[\x00-\x1F\x7F-\x9F]/', $trimmed)
        ) {
            return $defaultRedirect;
        }

        $baseOrigin = rtrim(APP_URL, '/');
        $parsed = parse_url($trimmed);
        
        $path = $parsed['path'] ?? '/';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $defaultRedirect;
        }

        $safePath = $path . (!empty($parsed['query']) ? '?' . $parsed['query'] : '') . (!empty($parsed['fragment']) ? '#' . $parsed['fragment'] : '');

        if ($userRole && $userRole !== 'admin' && str_starts_with($safePath, '/admin')) {
            return $defaultRedirect;
        }

        return $safePath;
    }

    public static function getSafeRefererUrl(string $defaultRedirect = '/dashboard'): string {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if (!$referer || !is_string($referer)) {
            return $defaultRedirect;
        }

        $parsed = parse_url($referer);
        if (empty($parsed['host'])) {
            return $defaultRedirect;
        }

        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        if (strtolower($parsed['host']) !== strtolower(explode(':', $currentHost)[0])) {
            return $defaultRedirect;
        }

        $path = ($parsed['path'] ?? '/') . (!empty($parsed['query']) ? '?' . $parsed['query'] : '') . (!empty($parsed['fragment']) ? '#' . $parsed['fragment'] : '');
        return self::getSafeRedirectUrl($path, $defaultRedirect);
    }

    public static function safeDeleteUpload(?string $relativeFilePath): void {
        if (empty($relativeFilePath) || !is_string($relativeFilePath)) {
            return;
        }

        $cleanRelative = ltrim(trim($relativeFilePath), "/\\");
        $fullPath = realpath(ROOT_DIR . '/public/' . $cleanRelative);
        $uploadsDir = realpath(UPLOADS_DIR);

        if ($fullPath && $uploadsDir && str_starts_with($fullPath, $uploadsDir) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    public static function parseCleanFloat($value, float $fallback = 0.0): float {
        if ($value === null || $value === '' || $value === false) {
            return $fallback;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        $sanitized = preg_replace('/[,₹$\s]/u', '', (string)$value);
        if (is_numeric($sanitized)) {
            return (float)$sanitized;
        }
        return $fallback;
    }

    public static function getSafeErrorMessage($err, string $fallback = 'An unexpected error occurred. Please try again.'): string {
        if (!$err) return $fallback;
        $msg = is_string($err) ? $err : ($err instanceof Throwable ? $err->getMessage() : '');
        if (!$msg) return $fallback;

        if (stripos($msg, 'duplicate') !== false || stripos($msg, 'unique constraint') !== false || stripos($msg, 'already exists') !== false) {
            return 'A record with these details already exists.';
        }

        if (preg_match('/(sql|sqlite|syntax|pdo|error|fatal|exception|stack trace|constraint)/i', $msg)) {
            return $fallback;
        }
        return $msg;
    }

    public static function formatBytes(int $bytes, int $decimals = 2): string {
        if ($bytes <= 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int)floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
    }
}
