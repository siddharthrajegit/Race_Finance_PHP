<?php
/**
 * Flash Messaging Service
 * Stores one-time messages in session across redirects
 */

class Flash {
    public static function set(string $key, string $message): void {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        if (!isset($_SESSION['flash'][$key])) {
            $_SESSION['flash'][$key] = [];
        }
        $_SESSION['flash'][$key][] = $message;
    }

    public static function get(string $key): array {
        if (isset($_SESSION['flash'][$key])) {
            $messages = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $messages;
        }
        return [];
    }

    public static function has(string $key): bool {
        return !empty($_SESSION['flash'][$key]);
    }
}

// Global convenience functions
function set_flash(string $key, string $message): void {
    Flash::set($key, $message);
}

function get_flash(string $key): array {
    return Flash::get($key);
}
