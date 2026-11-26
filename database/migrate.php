<?php
/**
 * CLI Migration Runner
 * Usage: php database/migrate.php [--rollback=001_warehouses.sql] [--status]
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("CLI only. Use /admin/system in the web UI.\n");
}

require_once __DIR__ . '/MigrationRunner.php';

$opts = getopt('', ['rollback:', 'status']);
$pdo = DB::getConnection();
$driver = DB::getDriver();

if (isset($opts['status'])) {
    $applied = MigrationRunner::getAppliedMigrations($pdo, $driver);
    $available = MigrationRunner::getAvailableMigrations();
    echo "Database driver: {$driver}\n";
    echo "Migrations:\n";
    foreach ($available as $file) {
        $name = basename($file);
        $status = isset($applied[$name]) ? "APPLIED ({$applied[$name]})" : "PENDING";
        echo "  - {$name}: {$status}\n";
    }
    exit(0);
}

if (!empty($opts['rollback'])) {
    $target = basename((string)$opts['rollback']);
    echo "Rolling back {$target} on {$driver}...\n";
    $res = MigrationRunner::rollbackMigration($target, $pdo, $driver);
    echo "Rolled back {$res['rolled_back']} ({$res['statements']} statements).\n";
    exit(0);
}

echo "Running pending migrations on {$driver}...\n";
$result = MigrationRunner::runPending($pdo, $driver);
echo "Executed: " . count($result['executed']) . "\n";
foreach ($result['executed'] as $m) {
    echo "  [OK] {$m}\n";
}
echo "Skipped (already applied): " . count($result['skipped']) . "\n";
if (!empty($result['errors'])) {
    foreach ($result['errors'] as $err) {
        echo "  [ERR] {$err['migration']}: {$err['error']}\n";
    }
    exit(1);
}
echo "Done.\n";
