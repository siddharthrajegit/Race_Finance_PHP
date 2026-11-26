<?php
/**
 * Phase 5 Automated Tests: Offline Sync Idempotency (client_uuid), Conflict Guards & PWA Assets
 */

echo "--- Phase 5 Tests: Offline Sync Idempotency & PWA Manifest/SW Verification ---\n";

// 1. Verify PWA & offline script assets exist cleanly
$rootPublic = __DIR__ . '/../public';
$t->assertTrue(file_exists($rootPublic . '/manifest.json'), 'P5 PWA: manifest.json exists');
$manifest = json_decode((string)file_get_contents($rootPublic . '/manifest.json'), true);
$t->assertTrue(is_array($manifest) && !empty($manifest['icons']) && count($manifest['icons']) >= 2, 'P5 PWA: manifest.json valid with 192 & 512 icons');
$t->assertTrue(file_exists($rootPublic . '/img/icon-192.png') && file_exists($rootPublic . '/img/icon-512.png'), 'P5 PWA: 192px & 512px PNG icons present');
$t->assertTrue(file_exists($rootPublic . '/sw.js'), 'P5 PWA: sw.js service worker exists');
$t->assertTrue(file_exists($rootPublic . '/js/offline.js'), 'P5 PWA: offline.js IndexedDB queue script exists');

// 2. Offline Invoice Sync Idempotency (client_uuid) & Stock Consistency
$p5Owner = User::create([
    'name' => 'Phase5 Owner',
    'email' => 'p5owner_' . uniqid() . '@example.com',
    'password' => password_hash('TestPass123', PASSWORD_BCRYPT, ['cost' => 4]),
    'role' => 'user',
    'status' => 'active'
]);
$p5Firm = Firm::create([
    'user_id' => $p5Owner['id'],
    'name' => 'P5 Offline Store',
    'is_default' => 1
]);
$p5FirmId = (int)$p5Firm['id'];
$p5Cust = Party::create(['firm_id' => $p5FirmId, 'type' => 'customer', 'name' => 'P5 Walk-in']);
$p5Item = Item::create([
    'firm_id' => $p5FirmId,
    'name' => 'Offline Sync Product',
    'sale_price' => 250,
    'opening_stock' => 20
]);

$uuid1 = 'offline-uuid-' . uniqid();
$syncPayload = [
    'firm_id' => $p5FirmId,
    'type' => 'sale',
    'invoice_date' => '2026-11-15',
    'party_id' => $p5Cust['id'],
    'party_name' => 'P5 Walk-in',
    'subtotal' => 500.00,
    'taxable_amount' => 500.00,
    'grand_total' => 500.00,
    'paid_amount' => 500.00,
    'balance_due' => 0.00,
    'payment_status' => 'paid',
    'payment_mode' => 'cash',
    'client_uuid' => $uuid1
];
$syncLines = [[
    'item_id' => $p5Item['id'],
    'item_name' => 'Offline Sync Product',
    'quantity' => 2,
    'rate' => 250.00,
    'total_amount' => 500.00
]];

$firstCreated = Invoice::create($syncPayload, $syncLines);
$t->assertEquals($uuid1, $firstCreated['client_uuid'] ?? '', 'P5 Offline Sync: Invoice stores client_uuid');
$t->assertFloatEquals(18.0, (float)Item::getById((int)$p5Item['id'], $p5FirmId)['current_stock'], 'P5 Offline Sync: Stock deducted once (20 -> 18)');

// Replay exact same offline client_uuid -> must return identical invoice WITHOUT duplicating bill or deducting stock again!
$replayed = Invoice::create($syncPayload, $syncLines);
$t->assertEquals((int)$firstCreated['id'], (int)$replayed['id'], 'P5 Offline Sync: Replayed client_uuid returns existing invoice idempotently');
$t->assertFloatEquals(18.0, (float)Item::getById((int)$p5Item['id'], $p5FirmId)['current_stock'], 'P5 Offline Sync: Idempotent replay does NOT double-deduct stock');

// Verify lookup helper
$foundByUuid = Invoice::findByClientUuid($p5FirmId, $uuid1);
$t->assertTrue($foundByUuid !== null && (int)$foundByUuid['id'] === (int)$firstCreated['id'], 'P5 Offline Sync: findByClientUuid locates bill accurately');
