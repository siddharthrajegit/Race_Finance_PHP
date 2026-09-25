<?php
/**
 * Lightweight Express-Style HTTP Router
 * Supports GET, POST, named parameters (:id), closures, and Controller@method syntax
 * RACE FINANCE - Small Business Billing & Inventory System
 */

require_once __DIR__ . '/CSRF.php';
require_once __DIR__ . '/View.php';

class Router {
    private static array $routes = [];

    public static function get(string $path, $handler): void {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, $handler): void {
        self::addRoute('POST', $path, $handler);
    }

    private static function addRoute(string $method, string $path, $handler): void {
        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path === '//') $path = '/';
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public static function dispatch(): void {
        // Enforce CSRF on state-mutating requests
        CSRF::verify();

        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '//') $requestUri = '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = self::convertRouteToRegex($route['path'], $paramNames);
            if (preg_match($pattern, $requestUri, $matches)) {
                $params = [];
                foreach ($paramNames as $index => $name) {
                    $params[$name] = $matches[$index + 1] ?? null;
                }

                self::executeHandler($route['handler'], $params);
                return;
            }
        }

        // 404 Handler
        http_response_code(404);
        try {
            view('404', ['title' => '404 - Page Not Found', 'activeMenu' => '']);
        } catch (Throwable $e) {
            echo "<h1>404 - Page Not Found</h1>";
        }
    }

    private static function convertRouteToRegex(string $routePath, ?array &$paramNames = []): string {
        $paramNames = [];
        $parts = explode('/', trim($routePath, '/'));
        $regexParts = [];

        foreach ($parts as $part) {
            if ($part === '') continue;
            if (str_starts_with($part, ':')) {
                $paramNames[] = substr($part, 1);
                $regexParts[] = '([^/]+)';
            } else {
                $regexParts[] = preg_quote($part, '#');
            }
        }

        return '#^/' . implode('/', $regexParts) . '$#';
    }

    private static function executeHandler($handler, array $params = []): void {
        if (is_callable($handler)) {
            call_user_func($handler, $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $action) = explode('@', $handler, 2);
            $controllerFile = ROOT_DIR . '/controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
            }
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    $controller->$action($params);
                    return;
                }
            }
        }

        if (is_array($handler) && count($handler) === 2) {
            list($controller, $action) = $handler;
            if (is_string($controller)) {
                $controllerFile = ROOT_DIR . '/controllers/' . $controller . '.php';
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                }
                $controller = new $controller();
            }
            if (is_object($controller) && method_exists($controller, $action)) {
                $controller->$action($params);
                return;
            }
        }

        throw new Exception("Router could not resolve handler: " . print_r($handler, true));
    }
}
