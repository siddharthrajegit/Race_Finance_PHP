<?php
/**
 * Phase 1 Automated Tests: Multi-Warehouse, Stock Valuation (FIFO/W-Avg), Expenses, Staff RBAC & Audit Logs
 */

require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../models/Warehouse.php';
require_once __DIR__ . '/../models/StockTransfer.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/StaffAccount.php';
require_once __DIR__ . '/../models/AuditLog.php';

echo "--- Phase 1 Tests: Warehouses, Transfers, Valuation, Expenses, Staff RBAC & Audit Logs ---\n";

$p1Owner = User::create([
    'name' => 'Phase1 Owner',
    'email' => 'p1owner_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);

$p1Firm = Firm::create([
    'user_id' => $p1Owner['id'],
    'name' => 'P1 Trading Co',
    'state' => 'Maharashtra',
    'state_code' => '27',
    'is_default' => 1
]);
$p1FirmOther = Firm::create([
    'user_id' => $p1Owner['id'],
    'name' => 'P1 Secondary Co',
    'state' => 'Gujarat',
    'state_code' => '24',
    'is_default' => 0
]);
$p1FirmId = (int)$p1Firm['id'];

// 1. Default Warehouse + Opening Stock Synchronization
$defaultWh = Warehouse::ensureDefaultWarehouse($p1FirmId);
$t->assertTrue(!empty($defaultWh['id']) && (int)$defaultWh['is_default'] === 1, 'P1: Default warehouse auto-created');

$godown2 = Warehouse::create([
    'firm_id' => $p1FirmId,
    'name' => 'City Godown',
    'code' => 'GD2',
    'is_default' => 0
]);
$t->assertEquals('GD2', $godown2['code'], 'P1: Second warehouse created cleanly');

$p1Supplier = Party::create([
    'firm_id' => $p1FirmId,
    'type' => 'supplier',
    'name' => 'P1 Supplier'
]);
$p1Customer = Party::create([
    'firm_id' => $p1FirmId,
    'type' => 'customer',
    'name' => 'P1 Customer'
]);

// Create item with 0 opening stock, then purchase 2 batches at different prices for FIFO vs W-Avg test
$valItem = Item::create([
    'firm_id' => $p1FirmId,
    'name' => 'Valuation Bearing',
    'unit' => 'PCS',
    'purchase_price' => 100.00,
    'sale_price' => 180.00,
    'tax_rate' => 18.00,
    'opening_stock' => 0
]);
$valItemId = (int)$valItem['id'];

// Purchase 1: 10 units @ 100 into Main Warehouse
Invoice::create([
    'firm_id' => $p1FirmId,
    'type' => 'purchase',
    'invoice_date' => '2026-11-01',
    'party_id' => $p1Supplier['id'],
    'party_name' => 'P1 Supplier',
    'warehouse_id' => $defaultWh['id'],
    'subtotal' => 1000.00,
    'taxable_amount' => 1000.00,
    'grand_total' => 1000.00,
    'paid_amount' => 0,
    'balance_due' => 1000.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $valItemId,
    'item_name' => 'Valuation Bearing',
    'quantity' => 10,
    'rate' => 100.00,
    'total_amount' => 1000.00
]]);

// Purchase 2: 10 units @ 150 into Main Warehouse
Invoice::create([
    'firm_id' => $p1FirmId,
    'type' => 'purchase',
    'invoice_date' => '2026-11-02',
    'party_id' => $p1Supplier['id'],
    'party_name' => 'P1 Supplier',
    'warehouse_id' => $defaultWh['id'],
    'subtotal' => 1500.00,
    'taxable_amount' => 1500.00,
    'grand_total' => 1500.00,
    'paid_amount' => 0,
    'balance_due' => 1500.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $valItemId,
    'item_name' => 'Valuation Bearing',
    'quantity' => 10,
    'rate' => 150.00,
    'total_amount' => 1500.00
]]);

// Sell 12 units from Main Warehouse -> FIFO consumes all 10 @ 100 + 2 @ 150, leaving 8 @ 150 = 1200.00!
// Weighted Avg unit cost = (10*100 + 10*150)/20 = 125 -> 8 * 125 = 1000.00!
Invoice::create([
    'firm_id' => $p1FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-03',
    'party_id' => $p1Customer['id'],
    'party_name' => 'P1 Customer',
    'warehouse_id' => $defaultWh['id'],
    'subtotal' => 2160.00,
    'taxable_amount' => 2160.00,
    'grand_total' => 2160.00,
    'paid_amount' => 0,
    'balance_due' => 2160.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $valItemId,
    'item_name' => 'Valuation Bearing',
    'quantity' => 12,
    'rate' => 180.00,
    'total_amount' => 2160.00
]]);

$fifoVal = Warehouse::getStockValuation($p1FirmId, 'fifo');
$wavgVal = Warehouse::getStockValuation($p1FirmId, 'weighted_avg');
$t->assertFloatEquals(1200.0, (float)$fifoVal['total_value'], 'P1: FIFO valuation leaves 8 units @ 150 = 1200');
$t->assertFloatEquals(1000.0, (float)$wavgVal['total_value'], 'P1: Weighted Avg valuation = 8 * 125 = 1000');

// 2. Atomic Inter-Warehouse Stock Transfer + Insufficient Stock Rejection
$tMainBefore = Warehouse::getWarehouseItemQty($p1FirmId, (int)$defaultWh['id'], $valItemId);
$t->assertFloatEquals(8.0, $tMainBefore, 'P1: Main warehouse has 8 units before transfer');

$transferOk = StockTransfer::create([
    'firm_id' => $p1FirmId,
    'transfer_date' => '2026-11-04',
    'source_warehouse_id' => $defaultWh['id'],
    'dest_warehouse_id' => $godown2['id'],
    'notes' => 'Move 3 units to City Godown'
], [[
    'item_id' => $valItemId,
    'quantity' => 3
]]);
$t->assertEquals('001', $transferOk['transfer_number'], 'P1: First transfer allocated sequence 001');
$t->assertFloatEquals(5.0, Warehouse::getWarehouseItemQty($p1FirmId, (int)$defaultWh['id'], $valItemId), 'P1: Source warehouse decreased 8 -> 5');
$t->assertFloatEquals(3.0, Warehouse::getWarehouseItemQty($p1FirmId, (int)$godown2['id'], $valItemId), 'P1: Destination warehouse increased 0 -> 3');
$t->assertFloatEquals(8.0, (float)Item::getById($valItemId, $p1FirmId)['current_stock'], 'P1: Global item stock unchanged at 8 after internal transfer');

// Reject transfer exceeding warehouse balance and verify rollback
$failedTransfer = false;
try {
    StockTransfer::create([
        'firm_id' => $p1FirmId,
        'transfer_date' => '2026-11-04',
        'source_warehouse_id' => $defaultWh['id'],
        'dest_warehouse_id' => $godown2['id']
    ], [[
        'item_id' => $valItemId,
        'quantity' => 99
    ]]);
} catch (Exception $e) {
    $failedTransfer = true;
}
$t->assertTrue($failedTransfer, 'P1: Insufficient warehouse stock transfer throws exception');
$t->assertFloatEquals(5.0, Warehouse::getWarehouseItemQty($p1FirmId, (int)$defaultWh['id'], $valItemId), 'P1: Failed transfer left source stock untouched at 5');

// 3. Expenses CRUD & Summaries
$exp1 = Expense::create([
    'firm_id' => $p1FirmId,
    'expense_date' => '2026-11-05',
    'category' => 'Office Rent',
    'payee' => 'Metro Properties',
    'amount' => 12500.00,
    'payment_mode' => 'bank_transfer',
    'created_by' => $p1Owner['id']
]);
$exp2 = Expense::create([
    'firm_id' => $p1FirmId,
    'expense_date' => '2026-11-06',
    'category' => 'Utilities & Electricity',
    'payee' => 'MSEB',
    'amount' => 2500.00,
    'payment_mode' => 'upi',
    'created_by' => $p1Owner['id']
]);
$t->assertEquals('001', $exp1['expense_number'], 'P1: Expense auto-sequence #001');
$t->assertEquals('002', $exp2['expense_number'], 'P1: Expense auto-sequence #002');
$expSum = Expense::getSummary($p1FirmId);
$t->assertFloatEquals(15000.0, (float)$expSum['total_amount'], 'P1: Expense summary totals 15000');

Expense::update((int)$exp2['id'], $p1FirmId, ['amount' => 3000.00, 'category' => 'Utilities & Electricity']);
$t->assertFloatEquals(15500.0, (float)Expense::getSummary($p1FirmId)['total_amount'], 'P1: Updated expense recalculates summary to 15500');
$t->assertTrue(Expense::delete((int)$exp2['id'], $p1FirmId), 'P1: Expense deleted successfully');
$t->assertFloatEquals(12500.0, (float)Expense::getSummary($p1FirmId)['total_amount'], 'P1: Summary equals 12500 after deletion');

// 4. Staff, RBAC & Multi-Firm Access Isolation
$cashier = StaffAccount::createStaffUser($p1Owner['id'], [
    'name' => 'Ravi Cashier',
    'phone' => '9988776655',
    'password' => 'Cashier123'
], [$p1FirmId], 'cashier');

$caAccountant = StaffAccount::createStaffUser($p1Owner['id'], [
    'name' => 'Priya CA',
    'email' => 'ca_' . uniqid() . '@firm.in',
    'password' => 'Accountant1'
], [$p1FirmId, (int)$p1FirmOther['id']], 'accountant');

RBAC::clearCache();
$cashierUser = User::findById((int)$cashier['user_id']);
$caUser = User::findById((int)$caAccountant['user_id']);

$t->assertTrue(RBAC::hasPermission('create_sales', $cashierUser, $p1Firm), 'P1 RBAC: Cashier can create sales in assigned firm');
$t->assertTrue(!RBAC::hasPermission('delete_sales', $cashierUser, $p1Firm), 'P1 RBAC: Cashier cannot delete sales');
$t->assertTrue(!RBAC::hasPermission('manage_expenses', $cashierUser, $p1Firm), 'P1 RBAC: Cashier cannot manage expenses');
$t->assertTrue(!RBAC::hasPermission('view_sales', $cashierUser, $p1FirmOther), 'P1 RBAC: Cashier denied on unassigned firm');

$t->assertTrue(RBAC::hasPermission('view_accounting', $caUser, $p1FirmOther), 'P1 RBAC: CA/Accountant can view accounting on second assigned firm');
$t->assertTrue(!RBAC::hasPermission('create_sales', $caUser, $p1Firm), 'P1 RBAC: CA/Accountant cannot create sales bills');

// Verify accessible firms helper respects active staff assignments
$cashierFirms = Firm::getAccessibleByUserId((int)$cashierUser['id']);
$t->assertEquals(1, count($cashierFirms), 'P1 RBAC: Cashier sees only 1 assigned firm');
$caFirms = Firm::getAccessibleByUserId((int)$caUser['id']);
$t->assertEquals(2, count($caFirms), 'P1 RBAC: CA sees both assigned firms');

// Pause cashier account and confirm access drops immediately
StaffAccount::updateStaffUser($p1Owner['id'], (int)$cashierUser['id'], [
    'name' => 'Ravi Cashier',
    'is_active' => 0
], [$p1FirmId], 'cashier');
RBAC::clearCache();
$t->assertTrue(!RBAC::hasPermission('create_sales', $cashierUser, $p1Firm), 'P1 RBAC: Paused cashier loses permissions immediately');
$t->assertEquals(0, count(Firm::getAccessibleByUserId((int)$cashierUser['id'])), 'P1 RBAC: Paused cashier sees 0 accessible firms');

// 5. Audit Logs & Secret Scrubbing
AuditLog::log($p1FirmId, 'UPDATE', 'staff_account', $cashierUser['id'], 'Updated staff password=SuperSecret99 token=abc123', $p1Owner);
$logs = AuditLog::getByFirmId($p1FirmId, ['action' => 'UPDATE']);
$t->assertTrue(!empty($logs), 'P1 AuditLog: Audit entries recorded for firm');
$foundRedacted = false;
foreach ($logs as $lg) {
    if (str_contains((string)$lg['details'], 'password=[REDACTED]') && !str_contains((string)$lg['details'], 'SuperSecret99')) {
        $foundRedacted = true;
        break;
    }
}
$t->assertTrue($foundRedacted, 'P1 AuditLog: Sensitive password/token strings scrubbed');
