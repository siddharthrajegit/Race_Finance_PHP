<?php
/**
 * Phase 2 Automated Tests: Double-Entry Accounting, Trial Balance, P&L, Balance Sheet, Cash Flow & Bank CSV Reconciliation
 */

require_once __DIR__ . '/../models/Accounting.php';
require_once __DIR__ . '/../models/BankReconciliation.php';
require_once __DIR__ . '/../models/Expense.php';

echo "--- Phase 2 Tests: Double-Entry Accounting, Financial Statements & Bank Reconciliation ---\n";

$p2Owner = User::create([
    'name' => 'Phase2 Owner',
    'email' => 'p2owner_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);

$p2Firm = Firm::create([
    'user_id' => $p2Owner['id'],
    'name' => 'P2 Accounting Corp',
    'state' => 'Maharashtra',
    'state_code' => '27',
    'is_default' => 1
]);
$p2FirmId = (int)$p2Firm['id'];

Accounting::ensureDefaultAccounts($p2FirmId);
$accounts = Accounting::getAccounts($p2FirmId);
$t->assertTrue(count($accounts) >= 18, 'P2: Default Chart of Accounts seeded cleanly');

// Custom account creation test
$customBank = Accounting::createAccount($p2FirmId, [
    'code' => '1150',
    'name' => 'ICICI Current Account',
    'account_type' => 'asset',
    'opening_balance' => 10000.00,
    'opening_side' => 'debit'
]);
$t->assertEquals('1150', $customBank['code'], 'P2: Custom account created');

// Reject unbalanced manual journal entry
$unbalancedRejected = false;
try {
    Accounting::createJournalEntry([
        'firm_id' => $p2FirmId,
        'entry_date' => '2026-11-10',
        'narration' => 'Invalid test'
    ], [
        ['account_code' => '1000', 'debit' => 500, 'credit' => 0],
        ['account_code' => '3000', 'debit' => 0, 'credit' => 400]
    ]);
} catch (Exception $e) {
    $unbalancedRejected = true;
}
$t->assertTrue($unbalancedRejected, 'P2: Unbalanced manual journal entry strictly rejected');

// Post balanced Owner Capital Equity entry: Dr Bank 50,000 / Cr Owner Equity 50,000
Accounting::createJournalEntry([
    'firm_id' => $p2FirmId,
    'entry_date' => '2026-11-01',
    'narration' => 'Owner capital infusion'
], [
    ['account_code' => '1100', 'debit' => 50000, 'credit' => 0],
    ['account_code' => '3000', 'debit' => 0, 'credit' => 50000]
]);

// Create Supplier Purchase Bill: Taxable 1000 + 18% GST = 1180 (unpaid)
$p2Supplier = Party::create(['firm_id' => $p2FirmId, 'type' => 'supplier', 'name' => 'P2 Vendor']);
$p2Customer = Party::create(['firm_id' => $p2FirmId, 'type' => 'customer', 'name' => 'P2 Client']);
$p2Item = Item::create([
    'firm_id' => $p2FirmId, 'name' => 'P2 Product', 'sale_price' => 200, 'purchase_price' => 100, 'opening_stock' => 20
]);

Invoice::create([
    'firm_id' => $p2FirmId,
    'type' => 'purchase',
    'invoice_date' => '2026-11-05',
    'party_id' => $p2Supplier['id'],
    'party_name' => 'P2 Vendor',
    'is_gst_bill' => 1,
    'subtotal' => 1000.00,
    'taxable_amount' => 1000.00,
    'cgst_amount' => 90.00,
    'sgst_amount' => 90.00,
    'tax_amount' => 180.00,
    'grand_total' => 1180.00,
    'paid_amount' => 0,
    'balance_due' => 1180.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $p2Item['id'],
    'item_name' => 'P2 Product',
    'quantity' => 10,
    'rate' => 100.00,
    'tax_rate' => 18.00,
    'cgst_rate' => 9.0,
    'cgst_amount' => 90.0,
    'sgst_rate' => 9.0,
    'sgst_amount' => 90.0,
    'total_amount' => 1180.00
]]);

// Create Sales Invoice: Taxable 2000 + 360 GST = 2360, Paid immediately 2360 by UPI
$saleInv = Invoice::create([
    'firm_id' => $p2FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-06',
    'party_id' => $p2Customer['id'],
    'party_name' => 'P2 Client',
    'is_gst_bill' => 1,
    'subtotal' => 2000.00,
    'taxable_amount' => 2000.00,
    'cgst_amount' => 180.00,
    'sgst_amount' => 180.00,
    'tax_amount' => 360.00,
    'grand_total' => 2360.00,
    'paid_amount' => 2360.00,
    'balance_due' => 0.00,
    'payment_status' => 'paid',
    'payment_mode' => 'upi'
], [[
    'item_id' => $p2Item['id'],
    'item_name' => 'P2 Product',
    'quantity' => 10,
    'rate' => 200.00,
    'tax_rate' => 18.00,
    'cgst_rate' => 9.0,
    'cgst_amount' => 180.0,
    'sgst_rate' => 9.0,
    'sgst_amount' => 180.0,
    'total_amount' => 2360.00
]]);

// Record Expense: Rent 500 by cash
$expP2 = Expense::create([
    'firm_id' => $p2FirmId,
    'expense_date' => '2026-11-07',
    'category' => 'Rent',
    'payee' => 'Building Owner',
    'amount' => 500.00,
    'payment_mode' => 'cash'
]);

// Verify Trial Balance
$tb = Accounting::getTrialBalance($p2FirmId);
$t->assertTrue($tb['is_balanced'], 'P2: Trial Balance is strictly balanced after invoices, payments & expenses');
$t->assertFloatEquals($tb['total_debit'], $tb['total_credit'], 'P2: Trial Balance total debit == total credit');

// Verify P&L: Income 2000, Expenses (1000 Purchases + 500 Rent = 1500), Net Profit = 500
$pnl = Accounting::getProfitAndLoss($p2FirmId);
$t->assertFloatEquals(2000.0, (float)$pnl['total_income'], 'P2: P&L Revenue equals 2000');
$t->assertFloatEquals(1500.0, (float)$pnl['total_expense'], 'P2: P&L Expenses equal 1500');
$t->assertFloatEquals(500.0, (float)$pnl['net_profit'], 'P2: P&L Net Profit equals 500');

// Verify Balance Sheet equation: Assets == Liabilities + Equity
$bs = Accounting::getBalanceSheet($p2FirmId);
$t->assertTrue($bs['is_balanced'], 'P2: Balance Sheet Assets == Liabilities + Equity');
$t->assertFloatEquals($bs['total_assets'], $bs['total_liabilities_and_equity'], 'P2: Balance Sheet exact equation balance');

// Verify Cash Flow Statement (Direct Method):
// Capital Bank +50,000 + Sales Payment Bank +2,360 - Rent Cash -500 = 51,860 closing
$cf = Accounting::getCashFlowStatement($p2FirmId);
$t->assertFloatEquals(2360.0, (float)$cf['operating_inflows'], 'P2: Cash Flow operating inflows = 2360');
$t->assertFloatEquals(500.0, (float)$cf['operating_outflows'], 'P2: Cash Flow operating outflows = 500');
$t->assertFloatEquals(51860.0, (float)$cf['closing_cash'], 'P2: Cash Flow closing cash & bank = 51860');

// Verify Immutable Reversal when Sales Invoice is deleted
$journalsBeforeDel = count(Accounting::getJournalEntries($p2FirmId));
Invoice::delete((int)$saleInv['id'], $p2FirmId);
$journalsAfterDel = count(Accounting::getJournalEntries($p2FirmId));
// Both invoice journal + inline payment journal get offsetting reversals -> +2 entries
$t->assertEquals($journalsBeforeDel + 2, $journalsAfterDel, 'P2: Deleting paid invoice posts immutable offsetting reversal journals');

$tbAfterDel = Accounting::getTrialBalance($p2FirmId);
$t->assertTrue($tbAfterDel['is_balanced'], 'P2: Trial Balance remains strictly balanced after reversal');

// Historical Backfill idempotency check
$backfill = Accounting::backfillMissingJournals($p2FirmId);
$t->assertEquals(0, $backfill['invoices'] + $backfill['payments'] + $backfill['expenses'], 'P2: Backfill is idempotent when journals already exist');

// Bank Statement CSV Import & Matching test
$csvPath = $testRoot . '/bank_sample.csv';
file_put_contents($csvPath, "Txn Date,Narration,Reference,Debit,Credit,Balance\n2026-11-06,UPI/CR/2360/P2CLIENT,UTR9911,0,2360.00,52360.00\n2026-11-07,CASH/RENT/EXP,REF100,500.00,0,51860.00\n");
$imp = BankReconciliation::importCsv($p2FirmId, $csvPath, $p2Owner['id']);
$t->assertEquals(2, $imp['imported_count'], 'P2: Imported 2 bank statement lines from CSV');

$bankLines = BankReconciliation::getStatementLines($p2FirmId);
$rentLine = null;
foreach ($bankLines as $bl) {
    if (abs((float)$bl['debit_amount'] - 500.0) < 0.01) {
        $rentLine = $bl;
    }
}
$t->assertTrue($rentLine !== null, 'P2: Found 500 debit line from imported bank CSV');
$cands = BankReconciliation::getCandidateMatches($p2FirmId, (int)$rentLine['id']);
$matchedExp = false;
foreach ($cands as $c) {
    if ($c['source_type'] === 'expense' && (int)$c['source_id'] === (int)$expP2['id']) {
        $matchedExp = true;
    }
}
$t->assertTrue($matchedExp, 'P2: Bank reconciliation suggests matching 500 expense candidate');
$t->assertTrue(BankReconciliation::matchLine($p2FirmId, (int)$rentLine['id'], 'expense', (int)$expP2['id']), 'P2: Bank line matched with expense');
$t->assertEquals(1, BankReconciliation::getSummary($p2FirmId)['matched_count'], 'P2: Summary shows 1 matched line');
@unlink($csvPath);
