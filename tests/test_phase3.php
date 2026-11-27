<?php
/**
 * Phase 3 Automated Tests: Barcode SVG, Dynamic UPI QR URI, Loyalty Points, Public Online Catalogue Isolation
 */

require_once __DIR__ . '/../core/SvgBarcode.php';
require_once __DIR__ . '/../core/SvgQrCode.php';
require_once __DIR__ . '/../models/Loyalty.php';
require_once __DIR__ . '/../models/Catalogue.php';

echo "--- Phase 3 Tests: Barcode SVG, UPI QR URI, Loyalty Points & Public Catalogue ---\n";

// 1. Pure-PHP Code128B Barcode SVG generation (no GD required)
$svg = SvgBarcode::render('SKU-1042');
$t->assertTrue(str_contains($svg, '<svg') && str_contains($svg, '<rect') && str_contains($svg, 'SKU-1042'), 'P3: Pure-PHP Code128B renders valid vector SVG with SKU label');

// 2. NPCI UPI intent URI construction
$uri = SvgQrCode::buildUpiUri('merchant@upi', 'Race Mart', 1250.50, 'Bill #009');
$t->assertTrue(
    str_starts_with($uri, 'upi://pay?')
    && str_contains($uri, 'pa=merchant%40upi')
    && str_contains($uri, 'am=1250.50')
    && str_contains($uri, 'cu=INR'),
    'P3: Standard NPCI UPI deep link URI constructed accurately'
);

// Setup isolated Phase 3 firm
$p3Owner = User::create([
    'name' => 'Phase3 Owner',
    'email' => 'p3owner_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);
$p3Firm = Firm::create([
    'user_id' => $p3Owner['id'],
    'name' => 'P3 Retail Store',
    'phone' => '9876543210',
    'upi_id' => 'p3retail@okaxis',
    'is_default' => 1
]);
$p3FirmId = (int)$p3Firm['id'];

$p3Cust = Party::create(['firm_id' => $p3FirmId, 'type' => 'customer', 'name' => 'VIP Buyer']);
$p3CustId = (int)$p3Cust['id'];
$p3ItemIn = Item::create(['firm_id' => $p3FirmId, 'name' => 'Visible Snack', 'item_code' => 'SNK01', 'sale_price' => 50, 'opening_stock' => 15]);
$p3ItemOut = Item::create(['firm_id' => $p3FirmId, 'name' => 'OutOfStock Drink', 'item_code' => 'DRK00', 'sale_price' => 40, 'opening_stock' => 0]);

// 3. Loyalty Program: Earn on invoice & reverse on delete
Loyalty::updateProgram($p3FirmId, [
    'is_enabled' => 1,
    'program_name' => 'Gold Rewards',
    'points_per_currency' => 2,
    'currency_unit' => 100,
    'point_value' => 1.00,
    'min_redeem_points' => 5,
    'max_redeem_pct' => 50
]);

$loyalInv = Invoice::create([
    'firm_id' => $p3FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-12',
    'party_id' => $p3CustId,
    'party_name' => 'VIP Buyer',
    'subtotal' => 500.00,
    'taxable_amount' => 500.00,
    'grand_total' => 500.00,
    'paid_amount' => 500.00,
    'balance_due' => 0,
    'payment_status' => 'paid',
    'payment_mode' => 'cash'
], [[
    'item_id' => $p3ItemIn['id'],
    'item_name' => 'Visible Snack',
    'quantity' => 10,
    'rate' => 50.00,
    'total_amount' => 500.00
]]);

// Expected points: (500 / 100) * 2 = 10.00 points
$balAfterEarn = Loyalty::getPartyBalance($p3CustId, $p3FirmId);
$t->assertFloatEquals(10.0, $balAfterEarn, 'P3 Loyalty: Customer earned 10 points on ₹500 bill');

// Delete invoice -> points must reverse cleanly back to 0
Invoice::delete((int)$loyalInv['id'], $p3FirmId);
$t->assertFloatEquals(0.0, Loyalty::getPartyBalance($p3CustId, $p3FirmId), 'P3 Loyalty: Deleting invoice reverses earned loyalty points back to 0');

// Manual bonus + redemption guard
Loyalty::adjustPoints($p3FirmId, $p3CustId, 20, 'adjust', null, 'Welcome bonus');
$t->assertFloatEquals(20.0, Loyalty::getPartyBalance($p3CustId, $p3FirmId), 'P3 Loyalty: Manual adjustment added 20 points');

$overRedeemFailed = false;
try {
    Invoice::create([
        'firm_id' => $p3FirmId,
        'type' => 'sale',
        'invoice_date' => '2026-11-13',
        'party_id' => $p3CustId,
        'party_name' => 'VIP Buyer',
        'subtotal' => 100.00,
        'taxable_amount' => 100.00,
        'grand_total' => 100.00,
        'paid_amount' => 100.00,
        'balance_due' => 0,
        'payment_status' => 'paid',
        'payment_mode' => 'cash',
        'redeem_points' => 99
    ], [[
        'item_id' => $p3ItemIn['id'],
        'item_name' => 'Visible Snack',
        'quantity' => 2,
        'rate' => 50.00,
        'total_amount' => 100.00
    ]]);
} catch (Exception $e) {
    $overRedeemFailed = true;
}
$t->assertTrue($overRedeemFailed, 'P3 Loyalty: Over-redemption exceeding customer balance throws validation exception');

// 4. Public Online Catalogue: unpublished vs published, hide_out_of_stock filter & slug isolation
$cat = Catalogue::ensureSettings($p3FirmId);
$t->assertTrue(Catalogue::getPublicCatalogueBySlug($cat['slug']) === null, 'P3 Catalogue: Unpublished store returns null publicly');

$updatedCat = Catalogue::updateSettings($p3FirmId, [
    'is_published' => 1,
    'slug' => 'p3-mart-' . $p3FirmId,
    'headline' => 'P3 Retail Storefront',
    'whatsapp_number' => '9876543210',
    'show_prices' => 1,
    'show_stock_badge' => 1,
    'hide_out_of_stock' => 1
]);
$pubData = Catalogue::getPublicCatalogueBySlug($updatedCat['slug']);
$t->assertTrue($pubData !== null, 'P3 Catalogue: Published store loads via public slug');
$t->assertEquals(1, count($pubData['items']), 'P3 Catalogue: hide_out_of_stock hides 0-stock items and leaves 1 in-stock item');
$t->assertEquals('Visible Snack', $pubData['items'][0]['name'], 'P3 Catalogue: Scoped strictly to firm items');
