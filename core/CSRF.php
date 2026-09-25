<?php
/**
 * CSRF Protection Middleware & Helper
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Flash.php';

class CSRF {
    public static function getToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(): bool {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $sessionToken = self::getToken();
        $submittedToken = $_POST['_csrf'] 
            ?? $_GET['_csrf'] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? $_SERVER['HTTP_CSRF_TOKEN'] 
            ?? null;

        // Also check raw JSON input if Content-Type is application/json
        if (!$submittedToken) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input) && isset($input['_csrf'])) {
                $submittedToken = $input['_csrf'];
            }
        }

        if (is_string($submittedToken) && is_string($sessionToken) && hash_equals($sessionToken, $submittedToken)) {
            return true;
        }

        // Invalid or missing token
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                  (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        if ($isAjax) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Invalid or missing CSRF security token. Please refresh the page and try again.'
            ]);
            exit;
        }

        Flash::set('error_msg', 'Your session or security token was invalid/expired. Please try again.');
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer");
        exit;
    }
}

// Global helper for templates
function csrf_token(): string {
    return CSRF::getToken();
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(CSRF::getToken(), ENT_QUOTES, 'UTF-8') . '">';
}
