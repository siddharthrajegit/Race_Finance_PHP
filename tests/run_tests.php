<?php
/**
 * RACE FINANCE - Automated Test Suite (Zero External Dependencies)
 * Runs against an isolated temporary SQLite database copy so production data is untouched.
 * Usage: php tests/run_tests.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

// Isolate environment to temporary test SQLite database before loading DB/config
$testRoot = sys_get_temp_dir() . '/race_finance_test_' . uniqid();
mkdir($testRoot . '/data', 0755, true);
copy(__DIR__ . '/../data/biller.db', $testRoot . '/data/biller.db');

// Override DB connection via env before config loads
putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');
putenv('DB_CONNECTION=sqlite');
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Point DB singleton to isolated temp database using Reflection
$testPdo = new PDO('sqlite:' . $testRoot . '/data/biller.db', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);
$testPdo->exec('PRAGMA foreign_keys = ON;');

$dbRef = new ReflectionClass('DB');
$instProp = $dbRef->getProperty('instance');
$instProp->setAccessible(true);
$instProp->setValue(null, $testPdo);
$drvProp = $dbRef->getProperty('driver');
$drvProp->setAccessible(true);
$drvProp->setValue(null, 'sqlite');

require_once __DIR__ . '/../database/MigrationRunner.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Firm.php';
require_once __DIR__ . '/../models/Party.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Estimate.php';
require_once __DIR__ . '/../models/Backup.php';
require_once __DIR__ . '/../models/Report.php';

class TestRunner {
    public int $passed = 0;
    public int $failed = 0;
    public array $failures = [];

    public function assertTrue(bool $cond, string $label): void {
        if ($cond) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->failures[] = $label;
            echo "  [FAIL] {$label}\n";
        }
    }

    public function assertEquals($expected, $actual, string $label): void {
        if ($expected == $actual) {
            $this->passed++;
        } else {
            $this->failed++;
            $msg = "{$label} (expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ")";
            $this->failures[] = $msg;
            echo "  [FAIL] {$msg}\n";
        }
    }

    public function assertFloatEquals(float $expected, float $actual, string $label, float $eps = 0.01): void {
        if (abs($expected - $actual) <= $eps) {
            $this->passed++;
        } else {
            $this->failed++;
            $msg = "{$label} (expected {$expected}, got {$actual})";
            $this->failures[] = $msg;
            echo "  [FAIL] {$msg}\n";
        }
    }
}

$t = new TestRunner();

echo "=== Running RACE FINANCE Test Suite (Isolated SQLite DB) ===\n";

// 0. Migration runner test
$migRes = MigrationRunner::runPending($testPdo, 'sqlite');
$t->assertEquals(0, count($migRes['errors']), 'Pending migrations run cleanly without errors');

// 1. Existing regression tests: User + Firm + Party + Item + Invoice + Payment + FIFO
$testUser = User::create([
    'name' => 'QA Owner',
    'phone' => '9999900001',
    'password' => password_hash('Test1234', PASSWORD_BCRYPT),
    'role' => 'user',
    'status' => 'active'
]);
$t->assertTrue(!empty($testUser['id']), 'Create test business owner user');

$firm1 = Firm::create([
    'user_id' => $testUser['id'],
    'name' => 'QA Firm Alpha',
    'state' => 'Maharashtra',
    'state_code' => '27',
    'upi_id' => 'alpha@upi'
]);
$t->assertTrue(!empty($firm1['id']), 'Create Firm 1');

$firm2 = Firm::create([
    'user_id' => $testUser['id'],
    'name' => 'QA Firm Beta',
    'state' => 'Karnataka',
    'state_code' => '29'
]);
$t->assertTrue(!empty($firm2['id']), 'Create Firm 2');

$limitThrown = false;
try {
    Firm::create(['user_id' => $testUser['id'], 'name' => 'Firm 3 Should Fail']);
} catch (Throwable $e) {
    $limitThrown = true;
}
$t->assertTrue($limitThrown, 'Enforce 2-firm maximum limit');

// Parties & Firm Isolation
$cust1 = Party::create(['firm_id' => $firm1['id'], 'type' => 'customer', 'name' => 'Cust Alpha', 'phone' => '9876543210']);
$supp1 = Party::create(['firm_id' => $firm1['id'], 'type' => 'supplier', 'name' => 'Supp Alpha']);
$t->assertTrue(Party::getById((int)$cust1['id'], (int)$firm2['id']) === null, 'Firm isolation: Party from Firm 1 not visible in Firm 2');

// Item creation & stock movement
$item1 = Item::create([
    'firm_id' => $firm1['id'],
    'name' => 'Widget A',
    'item_code' => 'WID-001',
    'hsn_code' => '8481',
    'unit' => 'PCS',
    'sale_price' => 100.00,
    'purchase_price' => 60.00,
    'tax_rate' => 18.00,
    'opening_stock' => 20,
    'low_stock_threshold' => 5
]);
$t->assertFloatEquals(20.0, (float)$item1['current_stock'], 'Opening stock initialized to 20');

// Create Sale Invoice (2 qty @ 100 + 18% GST = 236, paid 100)
$sale1 = Invoice::create([
    'firm_id' => $firm1['id'],
    'type' => 'sale',
    'invoice_date' => '2026-11-01',
    'party_id' => $cust1['id'],
    'party_name' => 'Cust Alpha',
    'is_gst_bill' => 1,
    'is_interstate' => 0,
    'subtotal' => 200.00,
    'taxable_amount' => 200.00,
    'cgst_amount' => 18.00,
    'sgst_amount' => 18.00,
    'igst_amount' => 0.00,
    'tax_amount' => 36.00,
    'round_off' => 0.00,
    'grand_total' => 236.00,
    'paid_amount' => 100.00,
    'balance_due' => 136.00,
    'payment_status' => 'partial',
    'payment_mode' => 'cash'
], [[
    'item_id' => $item1['id'],
    'item_name' => 'Widget A',
    'quantity' => 2,
    'rate' => 100.00,
    'tax_rate' => 18.00,
    'cgst_rate' => 9.00,
    'cgst_amount' => 18.00,
    'sgst_rate' => 9.00,
    'sgst_amount' => 18.00,
    'total_amount' => 236.00
]]);

$afterSaleItem = Item::getById((int)$item1['id'], (int)$firm1['id']);
$t->assertFloatEquals(18.0, (float)$afterSaleItem['current_stock'], 'Sale deducted 2 units (20 -> 18)');

// Second Sale Invoice + FIFO Payment test
$sale2 = Invoice::create([
    'firm_id' => $firm1['id'],
    'type' => 'sale',
    'invoice_date' => '2026-11-05',
    'party_id' => $cust1['id'],
    'party_name' => 'Cust Alpha',
    'is_gst_bill' => 0,
    'subtotal' => 100.00,
    'taxable_amount' => 100.00,
    'grand_total' => 100.00,
    'paid_amount' => 0.00,
    'balance_due' => 100.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $item1['id'],
    'item_name' => 'Widget A',
    'quantity' => 1,
    'rate' => 100.00,
    'total_amount' => 100.00
]]);

Payment::create([
    'firm_id' => $firm1['id'],
    'type' => 'payment_in',
    'payment_date' => '2026-11-10',
    'party_id' => $cust1['id'],
    'amount' => 150.00,
    'payment_mode' => 'upi'
]);

$inv1Reloaded = Invoice::getById((int)$sale1['id'], (int)$firm1['id']);
$inv2Reloaded = Invoice::getById((int)$sale2['id'], (int)$firm1['id']);
$t->assertEquals('paid', $inv1Reloaded['payment_status'], 'FIFO cleared oldest invoice #1');
$t->assertFloatEquals(14.0, (float)$inv2Reloaded['paid_amount'], 'FIFO applied remaining 14 to invoice #2');
$t->assertFloatEquals(86.0, (float)$inv2Reloaded['balance_due'], 'Invoice #2 balance due is 86');

// Backup export & restore test
$backupJson = Backup::exportFullBackup((int)$testUser['id']);
$t->assertTrue(count($backupJson['firms']) === 2 && count($backupJson['invoices']) === 2, 'Backup exports firms & invoices');

// Load additional modular tests if present
foreach (glob(__DIR__ . '/test_*.php') ?: [] as $testFile) {
    require_once $testFile;
}

// Cleanup temp directory
@unlink($testRoot . '/data/biller.db');
@rmdir($testRoot . '/data');
@rmdir($testRoot);

echo "=== Summary: {$t->passed} passed, {$t->failed} failed ===\n";
exit($t->failed > 0 ? 1 : 0);
