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
                self::ensureSchema(self::$instance, self::$driver);
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
        self::ensureSchema(self::$instance, self::$driver);

        return self::$instance;
    }

    private static function ensureSchema(PDO $pdo, string $driver): void {
        try {
            if ($driver === 'sqlite') {
                $cols = $pdo->query("PRAGMA table_info(firms)")->fetchAll();
                $colNames = array_column($cols, 'name');
                if (!empty($colNames) && !in_array('qr_code_path', $colNames, true)) {
                    $pdo->exec("ALTER TABLE firms ADD COLUMN qr_code_path VARCHAR(255) NULL;");
                }

                // Check and add items columns: category, sub_unit, sub_unit_qty
                $itemCols = $pdo->query("PRAGMA table_info(items)")->fetchAll();
                $itemColNames = array_column($itemCols, 'name');
                if (!empty($itemColNames)) {
                    if (!in_array('category', $itemColNames, true)) {
                        $pdo->exec("ALTER TABLE items ADD COLUMN category TEXT NULL;");
                    }
                    if (!in_array('sub_unit', $itemColNames, true)) {
                        $pdo->exec("ALTER TABLE items ADD COLUMN sub_unit TEXT NULL;");
                    }
                    if (!in_array('sub_unit_qty', $itemColNames, true)) {
                        $pdo->exec("ALTER TABLE items ADD COLUMN sub_unit_qty REAL NULL;");
                    }
                }

                // Ensure custom_units table exists for user-scoped measurement units
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS custom_units (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        unit_code TEXT NOT NULL,
                        unit_label TEXT NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE(user_id, unit_code),
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    );
                ");

                // Ensure estimates and estimate_items tables exist
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS estimates (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        firm_id INTEGER NOT NULL,
                        estimate_number TEXT NOT NULL,
                        estimate_date DATE NOT NULL,
                        customer_name TEXT NULL,
                        subtotal REAL DEFAULT 0.00,
                        discount_amount REAL DEFAULT 0.00,
                        tax_amount REAL DEFAULT 0.00,
                        grand_total REAL DEFAULT 0.00,
                        notes TEXT NULL,
                        status TEXT DEFAULT 'draft',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE(firm_id, estimate_number),
                        FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
                    );
                ");

                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS estimate_items (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        estimate_id INTEGER NOT NULL,
                        item_id INTEGER NULL,
                        item_name TEXT NOT NULL,
                        hsn_code TEXT NULL,
                        unit TEXT DEFAULT 'PCS',
                        sub_unit TEXT NULL,
                        sub_unit_qty REAL NULL,
                        quantity REAL NOT NULL DEFAULT 1.00,
                        rate REAL NOT NULL DEFAULT 0.00,
                        tax_rate REAL DEFAULT 0.00,
                        discount_percent REAL DEFAULT 0.00,
                        discount_amount REAL DEFAULT 0.00,
                        taxable_amount REAL DEFAULT 0.00,
                        tax_amount REAL DEFAULT 0.00,
                        total_amount REAL NOT NULL DEFAULT 0.00,
                        FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE CASCADE,
                        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
                    );
                ");
            } else {
                $check = $pdo->query("SHOW COLUMNS FROM firms LIKE 'qr_code_path'")->fetch();
                if (!$check) {
                    $pdo->exec("ALTER TABLE firms ADD COLUMN qr_code_path VARCHAR(255) NULL;");
                }

                // Check items columns
                $checkCat = $pdo->query("SHOW COLUMNS FROM items LIKE 'category'")->fetch();
                if (!$checkCat) {
                    $pdo->exec("ALTER TABLE items ADD COLUMN category VARCHAR(100) NULL DEFAULT NULL;");
                }
                $checkSubUnit = $pdo->query("SHOW COLUMNS FROM items LIKE 'sub_unit'")->fetch();
                if (!$checkSubUnit) {
                    $pdo->exec("ALTER TABLE items ADD COLUMN sub_unit VARCHAR(50) NULL DEFAULT NULL;");
                }
                $checkSubUnitQty = $pdo->query("SHOW COLUMNS FROM items LIKE 'sub_unit_qty'")->fetch();
                if (!$checkSubUnitQty) {
                    $pdo->exec("ALTER TABLE items ADD COLUMN sub_unit_qty DECIMAL(10,2) NULL DEFAULT NULL;");
                }

                // Ensure custom_units table exists for user-scoped measurement units
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `custom_units` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `user_id` INT NOT NULL,
                        `unit_code` VARCHAR(20) NOT NULL,
                        `unit_label` VARCHAR(50) NOT NULL,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY `uq_user_unit` (`user_id`, `unit_code`),
                        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                // Ensure estimates and estimate_items tables exist
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `estimates` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `firm_id` INT NOT NULL,
                        `estimate_number` VARCHAR(100) NOT NULL,
                        `estimate_date` DATE NOT NULL,
                        `customer_name` VARCHAR(255) NULL,
                        `subtotal` DECIMAL(15,2) DEFAULT 0.00,
                        `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
                        `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
                        `grand_total` DECIMAL(15,2) DEFAULT 0.00,
                        `notes` TEXT NULL,
                        `status` VARCHAR(50) DEFAULT 'draft',
                        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY `uq_estimate_firm_number` (`firm_id`, `estimate_number`),
                        FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `estimate_items` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `estimate_id` INT NOT NULL,
                        `item_id` INT NULL,
                        `item_name` VARCHAR(255) NOT NULL,
                        `hsn_code` VARCHAR(50) NULL,
                        `unit` VARCHAR(50) DEFAULT 'PCS',
                        `sub_unit` VARCHAR(50) NULL,
                        `sub_unit_qty` DECIMAL(10,2) NULL,
                        `quantity` DECIMAL(15,2) NOT NULL DEFAULT 1.00,
                        `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                        `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
                        `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
                        `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
                        `taxable_amount` DECIMAL(15,2) DEFAULT 0.00,
                        `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
                        `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                        FOREIGN KEY (`estimate_id`) REFERENCES `estimates`(`id`) ON DELETE CASCADE,
                        FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }
        } catch (Throwable $e) {
            // Table might not exist yet during fresh setup, safe to ignore
            error_log("Schema update notice: " . $e->getMessage());
        }
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
