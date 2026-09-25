<?php
/**
 * SQLite to MySQL Data Migration Tool
 * Migrates all records from local data/biller.db into configured MySQL database.
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$sqlitePath = ROOT_DIR . '/data/biller.db';
if (!file_exists($sqlitePath)) {
    die("Error: SQLite database not found at: $sqlitePath\n");
}

$dbHost = env('DB_HOST', 'localhost');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE') ?: env('DB_NAME');
$dbUser = env('DB_USERNAME') ?: env('DB_USER');
$dbPass = env('DB_PASSWORD') ?: env('DB_PASS');

if (empty($dbName)) {
    die("Error: Please set DB_NAME, DB_USER, DB_PASS in your .env file first.\n");
}

try {
    echo "Connecting to SQLite...\n";
    $sqlite = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connecting to MySQL ($dbName on $dbHost)...\n";
    $mysql = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $mysql->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $tables = [
        'users',
        'firms',
        'parties',
        'items',
        'invoices',
        'invoice_items',
        'payments',
        'google_tokens',
        'firm_settings',
        'platform_settings',
        'admin_audit_logs',
        'document_sequences'
    ];

    foreach ($tables as $table) {
        echo "Migrating table: $table ... ";
        try {
            $rows = $sqlite->query("SELECT * FROM {$table}")->fetchAll();
            if (empty($rows)) {
                echo "(0 rows, skipped)\n";
                continue;
            }

            $firstRow = $rows[0];
            $columns = array_keys($firstRow);
            $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));

            $insertStmt = $mysql->prepare("INSERT IGNORE INTO `{$table}` ({$colList}) VALUES ({$placeholders})");

            $count = 0;
            foreach ($rows as $row) {
                $values = array_values($row);
                $insertStmt->execute($values);
                $count++;
            }
            $eol = php_sapi_name() === 'cli' ? "\n" : "<br>";
            echo "($count rows migrated successfully)$eol";
        } catch (Throwable $te) {
            $eol = php_sapi_name() === 'cli' ? "\n" : "<br>";
            echo "Warning: " . $te->getMessage() . "$eol";
        }
    }

    $mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");
    $eol = php_sapi_name() === 'cli' ? "\n" : "<br>";
    echo "{$eol}🎉 Migration completed successfully!$eol";

} catch (Throwable $e) {
    die("Migration Failed: " . $e->getMessage() . "\n");
}
