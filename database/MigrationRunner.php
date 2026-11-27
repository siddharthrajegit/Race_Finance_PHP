<?php
/**
 * Dual-Driver (SQLite & MySQL) Database Migration Runner
 * Zero-dependency, idempotent, cPanel-safe migration manager.
 */

require_once __DIR__ . '/../config/db.php';

class MigrationRunner {
    public static function ensureTrackingTable(PDO $pdo, string $driver): void {
        if ($driver === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations_applied (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration_name TEXT NOT NULL UNIQUE,
                    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `migrations_applied` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `migration_name` VARCHAR(255) NOT NULL UNIQUE,
                    `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public static function getAppliedMigrations(PDO $pdo, string $driver): array {
        self::ensureTrackingTable($pdo, $driver);
        $rows = $pdo->query("SELECT migration_name, applied_at FROM migrations_applied ORDER BY id ASC")->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['migration_name']] = $r['applied_at'];
        }
        return $map;
    }

    public static function getAvailableMigrations(): array {
        $dir = __DIR__ . '/migrations';
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.sql') ?: [];
        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * Extract SQL section for active driver (-- [SQLITE] ... -- [MYSQL] ...)
     * If no driver markers exist, return the entire file content.
     */
    public static function extractDriverSql(string $rawSql, string $driver): string {
        $hasMarkers = preg_match('/^--\s*\[(SQLITE|MYSQL)\]/mi', $rawSql) === 1;
        if (!$hasMarkers) {
            return $rawSql;
        }

        $targetTag = strtoupper($driver);
        $lines = preg_split('/\R/', $rawSql);
        $capture = false;
        $captured = [];

        foreach ($lines as $line) {
            if (preg_match('/^--\s*\[(SQLITE|MYSQL|COMMON)\]/i', trim($line), $m)) {
                $section = strtoupper($m[1]);
                $capture = ($section === $targetTag || $section === 'COMMON');
                continue;
            }
            if ($capture) {
                $captured[] = $line;
            }
        }

        return implode("\n", $captured);
    }

    /**
     * Split SQL script into executable statements, ignoring comment-only blocks.
     */
    public static function splitStatements(string $sql): array {
        $lines = preg_split('/\R/', $sql);
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }
            $cleanLines[] = $line;
        }
        $cleanSql = implode("\n", $cleanLines);
        $parts = explode(';', $cleanSql);
        $statements = [];
        foreach ($parts as $part) {
            $stmt = trim($part);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
        }
        return $statements;
    }

    /**
     * Execute pending migrations. Returns summary array.
     */
    public static function runPending(?PDO $pdo = null, ?string $driver = null): array {
        $pdo = $pdo ?? DB::getConnection();
        $driver = $driver ?? DB::getDriver();

        self::ensureTrackingTable($pdo, $driver);
        $applied = self::getAppliedMigrations($pdo, $driver);
        $files = self::getAvailableMigrations();

        $results = [
            'driver' => $driver,
            'executed' => [],
            'skipped' => [],
            'errors' => []
        ];

        foreach ($files as $filePath) {
            $name = basename($filePath);
            if (isset($applied[$name])) {
                $results['skipped'][] = $name;
                continue;
            }

            $rawSql = file_get_contents($filePath);
            $driverSql = self::extractDriverSql((string)$rawSql, $driver);
            $statements = self::splitStatements($driverSql);

            try {
                if ($driver === 'sqlite') {
                    $pdo->beginTransaction();
                }

                foreach ($statements as $stmt) {
                    try {
                        $pdo->exec($stmt);
                    } catch (PDOException $e) {
                        $msg = strtolower($e->getMessage());
                        // Idempotency guard for ALTER TABLE ADD COLUMN if column already exists
                        if (str_contains($stmt, 'ADD COLUMN') && (str_contains($msg, 'duplicate column') || str_contains($msg, 'already exists'))) {
                            continue;
                        }
                        throw $e;
                    }
                }

                $rec = $pdo->prepare("INSERT INTO migrations_applied (migration_name) VALUES (?)");
                $rec->execute([$name]);

                if ($driver === 'sqlite' && $pdo->inTransaction()) {
                    $pdo->commit();
                }

                $results['executed'][] = $name;
            } catch (Throwable $e) {
                if ($driver === 'sqlite' && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $results['errors'][] = [
                    'migration' => $name,
                    'error' => $e->getMessage()
                ];
                break; // Stop on first failure to keep order consistent
            }
        }

        return $results;
    }

    /**
     * Rollback a specific migration by prefix/name using database/migrations/rollback/<prefix>_rollback.sql
     */
    public static function rollbackMigration(string $migrationName, ?PDO $pdo = null, ?string $driver = null): array {
        $pdo = $pdo ?? DB::getConnection();
        $driver = $driver ?? DB::getDriver();

        self::ensureTrackingTable($pdo, $driver);
        $prefix = explode('_', $migrationName, 2)[0];
        $rollbackFile = __DIR__ . '/migrations/rollback/' . $prefix . '_rollback.sql';
        if (!file_exists($rollbackFile)) {
            throw new Exception("Rollback SQL not found for {$migrationName} ({$rollbackFile}).");
        }

        $rawSql = (string)file_get_contents($rollbackFile);
        $driverSql = self::extractDriverSql($rawSql, $driver);
        $statements = self::splitStatements($driverSql);

        if ($driver === 'sqlite') {
            $pdo->beginTransaction();
        }
        try {
            foreach ($statements as $stmt) {
                $pdo->exec($stmt);
            }
            $del = $pdo->prepare("DELETE FROM migrations_applied WHERE migration_name = ?");
            $del->execute([$migrationName]);

            if ($driver === 'sqlite' && $pdo->inTransaction()) {
                $pdo->commit();
            }
            return ['rolled_back' => $migrationName, 'statements' => count($statements)];
        } catch (Throwable $e) {
            if ($driver === 'sqlite' && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
