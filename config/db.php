<?php
/**
 * Database Connection Manager (PDO)
 * Supports MySQL / MariaDB (default for cPanel) and SQLite (automatic fallback)
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $connection = env('DB_CONNECTION');
        $dbHost = env('DB_HOST', 'localhost');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE') ?: env('DB_NAME');
        $dbUser = env('DB_USERNAME') ?: env('DB_USER');
        $dbPass = env('DB_PASSWORD') ?: env('DB_PASS');

        // Check if MySQL credentials are provided
        $tryMysql = ($connection === 'mysql') || ($connection !== 'sqlite' && !empty($dbName));

        if ($tryMysql && !empty($dbName)) {
            try {
                $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                self::$instance = new PDO($dsn, $dbUser, $dbPass, $options);
                self::$driver = 'mysql';
                return self::$instance;
            } catch (PDOException $e) {
                // If MySQL was explicitly requested and failed, log error
                if ($connection === 'mysql') {
                    throw new Exception("Database Connection Error (MySQL): " . $e->getMessage());
                }
                // Otherwise fall back to SQLite
                error_log("MySQL connection failed, falling back to SQLite: " . $e->getMessage());
            }
        }

        // SQLite connection (default fallback if MySQL not configured)
        $dataDir = ROOT_DIR . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        $sqlitePath = $dataDir . '/biller.db';
        $dsn = "sqlite:" . $sqlitePath;
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        self::$instance = new PDO($dsn, null, null, $options);
        self::$instance->exec("PRAGMA foreign_keys = ON;");
        self::$instance->exec("PRAGMA journal_mode = WAL;");
        self::$driver = 'sqlite';

        return self::$instance;
    }

    public static function getDriver(): string {
        if (self::$instance === null) {
            self::getConnection();
        }
        return self::$driver;
    }

    public static function isMysql(): bool {
        return self::getDriver() === 'mysql';
    }

    public static function isSqlite(): bool {
        return self::getDriver() === 'sqlite';
    }
}
