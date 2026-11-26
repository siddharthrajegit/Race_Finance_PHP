<?php
/**
 * Phase 6 Automated Tests: GSTR CSV / Formula Injection Protection, Tally XML, Backup v2.0 Round-Trip & All 13 Migrations
 */

require_once __DIR__ . '/../controllers/ExportController.php';

echo "--- Phase 6 Tests: GSTR Exports, Tally XML, Backup v2.0 & Migration Integrity ---\n";

// 1. Verify all 13 migrations ran cleanly
$appliedMigs = MigrationRunner::getAppliedMigrations($testPdo, 'sqlite');
$availMigs = array_map('basename', MigrationRunner::getAvailableMigrations());
$t->assertEquals(13, count($availMigs), 'P6: 13 numbered migration files present (001..013)');
$t->assertEquals(13, count($appliedMigs), 'P6: All 13 migrations cleanly recorded in migrations_applied');

// 2. CSV Formula Injection Protection
$t->assertEquals("'=cmd|' /C calc", ExportController::sanitizeCsvCell("=cmd|' /C calc"), 'P6 CSV: Neutralizes leading = formula injection');
$t->assertEquals("'+SUM(A1)", ExportController::sanitizeCsvCell("+SUM(A1)"), 'P6 CSV: Neutralizes leading + formula injection');
$t->assertEquals("'-abc", ExportController::sanitizeCsvCell("-abc"), 'P6 CSV: Neutralizes formula text');
$t->assertEquals("-450.25", ExportController::sanitizeCsvCell("-450.25"), 'P6 CSV: Preserves valid signed numeric values cleanly');

// 3. GSTR-1 / 2B / 3B Summary Math & Tally XML Output
$p6Owner = User::create([
    'name' => 'Phase6 Owner',
    'email' => 'p6owner_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);
$p6Firm = Firm::create([
    'user_id' => $p6Owner['id'],
    'name' => 'P6 Compliance Exports Ltd',
    'gstin' => '27AABCP6666D1Z5',
    'is_default' => 1
]);
$p6FirmId = (int)$p6Firm['id'];
$p6Cust = Party::create(['firm_id' => $p6FirmId, 'type' => 'customer', 'name' => 'B2B Buyer Ltd', 'gstin' => '27AABCU1234F1Z2']);
$p6Supp = Party::create(['firm_id' => $p6FirmId, 'type' => 'supplier', 'name' => 'ITC Vendor LLP', 'gstin' => '27AABCV5678G1Z9']);
$p6Item = Item::create(['firm_id' => $p6FirmId, 'name' => 'Export Item', 'sale_price' => 200, 'purchase_price' => 100, 'opening_stock' => 25, 'tracking_type' => 'batch']);

Invoice::create([
    'firm_id' => $p6FirmId,
    'type' => 'purchase',
    'invoice_date' => '2026-11-10',
    'party_id' => $p6Supp['id'],
    'party_name' => 'ITC Vendor LLP',
    'party_gstin' => '27AABCV5678G1Z9',
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
], [['item_id' => $p6Item['id'], 'item_name' => 'Export Item', 'quantity' => 10, 'rate' => 100, 'total_amount' => 1180]]);

Invoice::create([
    'firm_id' => $p6FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-12',
    'party_id' => $p6Cust['id'],
    'party_name' => 'B2B Buyer Ltd',
    'party_gstin' => '27AABCU1234F1Z2',
    'is_gst_bill' => 1,
    'subtotal' => 2000.00,
    'taxable_amount' => 2000.00,
    'cgst_amount' => 180.00,
    'sgst_amount' => 180.00,
    'tax_amount' => 360.00,
    'grand_total' => 2360.00,
    'paid_amount' => 2360.00,
    'balance_due' => 0,
    'payment_status' => 'paid',
    'payment_mode' => 'upi'
], [['item_id' => $p6Item['id'], 'item_name' => 'Export Item', 'quantity' => 10, 'rate' => 200, 'total_amount' => 2360]]);

$gstSummary = ExportController::computeGstrSummaries($p6FirmId, '2026-11-01', '2026-11-30');
$t->assertFloatEquals(360.0, (float)($gstSummary['outward']['cgst'] + $gstSummary['outward']['sgst']), 'P6 GSTR: Outward sales GST equals 360');
$t->assertFloatEquals(180.0, (float)($gstSummary['inward']['cgst'] + $gstSummary['inward']['sgst']), 'P6 GSTR: Purchase ITC equals 180');
$t->assertFloatEquals(180.0, (float)$gstSummary['net_payable']['total_tax'], 'P6 GSTR-3B: Net payable GST = Outward (360) - ITC (180) = 180');

// Tally XML generation check
$tallyXml = ExportController::buildTallyXml($p6Firm, '2026-11-01', '2026-11-30');
$domCheck = @simplexml_load_string($tallyXml);
$t->assertTrue($domCheck !== false && str_contains($tallyXml, '<ENVELOPE>') && str_contains($tallyXml, 'B2B Buyer Ltd') && str_contains($tallyXml, 'VOUCHER'), 'P6 Tally XML: Generates valid <ENVELOPE> XML with party ledgers and vouchers');

// 4. Backup v2.0 Full Export & Restore Round-Trip onto fresh user
$v2Backup = Backup::exportFullBackup((int)$p6Owner['id']);
$t->assertEquals('2.0', $v2Backup['version'], 'P6 Backup: Export carries version 2.0');
$t->assertTrue(!empty($v2Backup['warehouses']) && !empty($v2Backup['journal_entries']) && !empty($v2Backup['chart_of_accounts']), 'P6 Backup v2.0: Exports warehouses, COA, and journals');

$p6RestoreUser = User::create([
    'name' => 'Phase6 Restored User',
    'email' => 'p6restored_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);
$t->assertTrue(Backup::restoreFullBackup((int)$p6RestoreUser['id'], $v2Backup), 'P6 Backup v2.0: Full restore completes cleanly without error');
$restoredFirms = Firm::getByUserId((int)$p6RestoreUser['id']);
$t->assertEquals(1, count($restoredFirms), 'P6 Backup v2.0: Restored firm belongs to target user');
$rfId = (int)$restoredFirms[0]['id'];
$t->assertEquals(1, count(Invoice::getByFirmId($rfId, 'sale')), 'P6 Backup v2.0: Restored sales invoice cleanly');
$t->assertEquals(1, count(Invoice::getByFirmId($rfId, 'purchase')), 'P6 Backup v2.0: Restored purchase bill cleanly');
