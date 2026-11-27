<?php
/**
 * Phase 4 Automated Tests: Batch/Expiry FEFO, Unique Serial/IMEI Lifecycle, Manufacturing BOM & Atomic Production Orders
 */

require_once __DIR__ . '/../models/Batch.php';
require_once __DIR__ . '/../models/SerialNumber.php';
require_once __DIR__ . '/../models/Bom.php';

echo "--- Phase 4 Tests: Batches/FEFO, Serial/IMEI & Manufacturing BOM ---\n";

$p4Owner = User::create([
    'name' => 'Phase4 Owner',
    'email' => 'p4owner_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);
$p4Firm = Firm::create([
    'user_id' => $p4Owner['id'],
    'name' => 'P4 Pharma & Assembly Co',
    'is_default' => 1
]);
$p4FirmId = (int)$p4Firm['id'];
$p4Cust = Party::create(['firm_id' => $p4FirmId, 'type' => 'customer', 'name' => 'P4 Clinic']);
$p4Supp = Party::create(['firm_id' => $p4FirmId, 'type' => 'supplier', 'name' => 'P4 Distributor']);

// 1. Batch & Expiry (FEFO) Test
$medItem = Item::create([
    'firm_id' => $p4FirmId,
    'name' => 'Amoxicillin 500mg',
    'unit' => 'BOX',
    'sale_price' => 120.00,
    'purchase_price' => 80.00,
    'opening_stock' => 0,
    'tracking_type' => 'batch'
]);
$t->assertEquals('batch', $medItem['tracking_type'], 'P4: Item tracking_type saved as batch');

// Purchase Batch LATE (expiry 2028-01-01, qty 5) FIRST, then Batch SOON (expiry 2026-12-15, qty 5)
Invoice::create([
    'firm_id' => $p4FirmId,
    'type' => 'purchase',
    'invoice_date' => '2026-11-01',
    'party_id' => $p4Supp['id'],
    'party_name' => 'P4 Distributor',
    'subtotal' => 400.00,
    'taxable_amount' => 400.00,
    'grand_total' => 400.00,
    'paid_amount' => 0,
    'balance_due' => 400.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $medItem['id'],
    'item_name' => 'Amoxicillin 500mg',
    'quantity' => 5,
    'rate' => 80.00,
    'total_amount' => 400.00,
    'batch_number' => 'B-LATE',
    'expiry_date' => '2028-01-01'
]]);

Invoice::create([
    'firm_id' => $p4FirmId,
    'type' => 'purchase',
    'invoice_date' => '2026-11-02',
    'party_id' => $p4Supp['id'],
    'party_name' => 'P4 Distributor',
    'subtotal' => 400.00,
    'taxable_amount' => 400.00,
    'grand_total' => 400.00,
    'paid_amount' => 0,
    'balance_due' => 400.00,
    'payment_status' => 'unpaid',
    'payment_mode' => 'credit'
], [[
    'item_id' => $medItem['id'],
    'item_name' => 'Amoxicillin 500mg',
    'quantity' => 5,
    'rate' => 80.00,
    'total_amount' => 400.00,
    'batch_number' => 'B-SOON',
    'expiry_date' => date('Y-m-d', strtotime('+10 days'))
]]);

$expAlerts = Batch::getExpiringAlerts($p4FirmId, 30);
$t->assertEquals(1, count($expAlerts), 'P4 Batch: Expiring within 30 days alert catches B-SOON only');
$t->assertEquals('B-SOON', $expAlerts[0]['batch_number'], 'P4 Batch: Alert identifies soonest expiring batch');

// Sell 6 boxes -> FEFO MUST consume all 5 of B-SOON first, then 1 of B-LATE (leaving 4 in B-LATE)!
Invoice::create([
    'firm_id' => $p4FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-05',
    'party_id' => $p4Cust['id'],
    'party_name' => 'P4 Clinic',
    'subtotal' => 720.00,
    'taxable_amount' => 720.00,
    'grand_total' => 720.00,
    'paid_amount' => 720.00,
    'balance_due' => 0,
    'payment_status' => 'paid',
    'payment_mode' => 'cash'
], [[
    'item_id' => $medItem['id'],
    'item_name' => 'Amoxicillin 500mg',
    'quantity' => 6,
    'rate' => 120.00,
    'total_amount' => 720.00
]]);

$batchesAfter = Batch::getByFirmId($p4FirmId, (int)$medItem['id']);
$byNo = [];
foreach ($batchesAfter as $b) $byNo[$b['batch_number']] = (float)$b['remaining_qty'];
$t->assertFloatEquals(0.0, $byNo['B-SOON'], 'P4 FEFO: Earliest expiring batch B-SOON depleted first (5 -> 0)');
$t->assertFloatEquals(4.0, $byNo['B-LATE'], 'P4 FEFO: Later batch B-LATE consumed remaining 1 unit (5 -> 4)');

// 2. Serial Number / IMEI Tracking Test
$phoneItem = Item::create([
    'firm_id' => $p4FirmId,
    'name' => 'Smartphone X',
    'unit' => 'PCS',
    'sale_price' => 15000,
    'purchase_price' => 12000,
    'opening_stock' => 2,
    'tracking_type' => 'serial'
]);
SerialNumber::registerSerials($p4FirmId, (int)$phoneItem['id'], null, ['IMEI-AAA-111', 'IMEI-BBB-222']);

// Reject duplicate serial per firm
$dupSerialBlocked = false;
try {
    SerialNumber::registerSerials($p4FirmId, (int)$phoneItem['id'], null, ['IMEI-AAA-111']);
} catch (Exception $e) {
    $dupSerialBlocked = true;
}
$t->assertTrue($dupSerialBlocked, 'P4 Serial: Duplicate Serial/IMEI in same firm strictly blocked');

// Sell IMEI-AAA-111 on sale invoice
$phoneSale = Invoice::create([
    'firm_id' => $p4FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-06',
    'party_id' => $p4Cust['id'],
    'party_name' => 'P4 Clinic',
    'subtotal' => 15000.00,
    'taxable_amount' => 15000.00,
    'grand_total' => 15000.00,
    'paid_amount' => 15000.00,
    'balance_due' => 0,
    'payment_status' => 'paid',
    'payment_mode' => 'upi'
], [[
    'item_id' => $phoneItem['id'],
    'item_name' => 'Smartphone X',
    'quantity' => 1,
    'rate' => 15000.00,
    'total_amount' => 15000.00,
    'serial_numbers' => ['IMEI-AAA-111']
]]);

$soldList = SerialNumber::getByFirmId($p4FirmId, (int)$phoneItem['id'], 'sold');
$t->assertEquals(1, count($soldList), 'P4 Serial: IMEI-AAA-111 marked sold after sale invoice');

// Reject selling already-sold serial again
$resellBlocked = false;
try {
    SerialNumber::sellSerials($p4FirmId, (int)$phoneItem['id'], ['IMEI-AAA-111'], 999);
} catch (Exception $e) {
    $resellBlocked = true;
}
$t->assertTrue($resellBlocked, 'P4 Serial: Selling already-sold Serial/IMEI throws exception');

// Delete invoice releases serial back to in_stock
Invoice::delete((int)$phoneSale['id'], $p4FirmId);
$inStockSerials = SerialNumber::getByFirmId($p4FirmId, (int)$phoneItem['id'], 'in_stock');
$t->assertEquals(2, count($inStockSerials), 'P4 Serial: Deleting sale invoice restores serial back to in_stock');

// 3. Basic Manufacturing / BOM & Atomic Production Order
$rawWood = Item::create(['firm_id' => $p4FirmId, 'name' => 'Oak Plank', 'unit' => 'PCS', 'purchase_price' => 200, 'opening_stock' => 10]);
$rawScrew = Item::create(['firm_id' => $p4FirmId, 'name' => 'Steel Screws', 'unit' => 'PCS', 'purchase_price' => 5, 'opening_stock' => 40]);
$chairItem = Item::create(['firm_id' => $p4FirmId, 'name' => 'Oak Chair', 'unit' => 'PCS', 'sale_price' => 1200, 'opening_stock' => 0]);

$bom = Bom::saveBom($p4FirmId, [
    'finished_item_id' => $chairItem['id'],
    'bom_name' => 'Oak Chair BOM',
    'output_qty' => 1
], [
    ['component_item_id' => $rawWood['id'], 'quantity_per_unit' => 2],
    ['component_item_id' => $rawScrew['id'], 'quantity_per_unit' => 8]
]);
$t->assertEquals(2, count($bom['components']), 'P4 BOM: Recipe saved with 2 raw components');

// Produce 4 chairs -> consumes 8 Oak Planks (10->2) & 32 Steel Screws (40->8); cost = 4*(2*200 + 8*5) = 1760
$prodOrder = Bom::executeProductionOrder([
    'firm_id' => $p4FirmId,
    'bom_id' => $bom['id'],
    'production_date' => '2026-11-08',
    'produced_qty' => 4
]);
$t->assertEquals('001', $prodOrder['order_number'], 'P4 BOM: First production order assigned #001');
$t->assertFloatEquals(1760.0, (float)$prodOrder['total_component_cost'], 'P4 BOM: Production cost computed from raw component rates');
$t->assertFloatEquals(2.0, (float)Item::getById((int)$rawWood['id'], $p4FirmId)['current_stock'], 'P4 BOM: Oak Planks consumed (10 -> 2)');
$t->assertFloatEquals(8.0, (float)Item::getById((int)$rawScrew['id'], $p4FirmId)['current_stock'], 'P4 BOM: Steel Screws consumed (40 -> 8)');
$t->assertFloatEquals(4.0, (float)Item::getById((int)$chairItem['id'], $p4FirmId)['current_stock'], 'P4 BOM: Finished Oak Chairs credited (0 -> 4)');

// Over-production exceeding raw stock (try 5 more chairs = 10 planks, only 2 left) must fail cleanly without mutating screws/chairs
$bomShortFailed = false;
try {
    Bom::executeProductionOrder([
        'firm_id' => $p4FirmId,
        'bom_id' => $bom['id'],
        'produced_qty' => 5
    ]);
} catch (Exception $e) {
    $bomShortFailed = true;
}
$t->assertTrue($bomShortFailed, 'P4 BOM: Insufficient raw material blocks production order');
$t->assertFloatEquals(4.0, (float)Item::getById((int)$chairItem['id'], $p4FirmId)['current_stock'], 'P4 BOM: Rolled-back production leaves finished stock untouched');
