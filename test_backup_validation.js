const assert = require('assert');
const { validateAndSanitizeBackup, LIMITS } = require('./middleware/backupValidator');
const { Backup, User } = require('./models');
const db = require('./config/db');

console.log('=== Starting H5: Backup Validation & Sanitization Security Test ===\n');

// 1. Rejection of invalid root formats
console.log('Test 1: Reject invalid root formats');
assert.throws(() => validateAndSanitizeBackup(null), /Root payload must be a valid JSON object/);
assert.throws(() => validateAndSanitizeBackup([]), /Root payload must be a valid JSON object/);
assert.throws(() => validateAndSanitizeBackup('not-an-object'), /Root payload must be a valid JSON object/);
assert.throws(() => validateAndSanitizeBackup({}), /Backup must contain at least one business firm/);
assert.throws(() => validateAndSanitizeBackup({ firms: [] }), /Backup must contain at least one business firm/);
console.log('  Passed: Non-objects and empty firms properly rejected.');

// 2. Rejection of array floods (DoS / Memory Bomb)
console.log('\nTest 2: Rejection of array floods (DoS protection)');
const tooManyFirms = new Array(LIMITS.MAX_FIRMS + 1).fill({ name: 'Firm' });
assert.throws(() => validateAndSanitizeBackup({ firms: tooManyFirms }), /Exceeded limit of 50 firms/);

const tooManyParties = new Array(LIMITS.MAX_PARTIES + 1).fill({ name: 'Party' });
assert.throws(() => validateAndSanitizeBackup({ firms: [{ name: 'F1' }], parties: tooManyParties }), /Exceeded limit of 10000 parties/);
console.log('  Passed: Array flood DoS attempts blocked.');

// 3. Stored XSS Neutralization
console.log('\nTest 3: Stored XSS Neutralization');
const xssPayload = {
  firms: [{
    id: 1,
    name: '<script>alert("firm_xss")</script>Secure Firm',
    address: '<img src=x onerror="alert(\'address_xss\')">123 Street',
    terms: '<<script>script>evil()</script>No terms'
  }],
  parties: [{
    id: 1,
    firm_id: 1,
    name: '<svg onload=alert(1)>Customer Corp',
    billing_address: '<a href="javascript:steal()">Click here</a>Downtown'
  }],
  items: [{
    id: 1,
    firm_id: 1,
    name: '<iframe src="evil.com"></iframe>Widget Pro',
    description: '<script>document.cookie="stolen";</script>Top notch widget'
  }],
  invoices: [{
    id: 1,
    firm_id: 1,
    party_id: 1,
    invoice_number: '<script>xss()</script>INV-001',
    notes: 'Payment due <script>alert(1)</script> promptly'
  }]
};

const cleanXss = validateAndSanitizeBackup(xssPayload);
assert.strictEqual(cleanXss.firms[0].name.includes('<script>'), false);
assert.strictEqual(cleanXss.firms[0].name.includes('<'), false);
assert.strictEqual(cleanXss.firms[0].name.includes('>'), false);
assert.strictEqual(cleanXss.firms[0].address.includes('img'), false);
assert.strictEqual(cleanXss.firms[0].address.includes('<'), false);
assert.strictEqual(cleanXss.firms[0].terms.includes('<'), false);

assert.strictEqual(cleanXss.parties[0].name.includes('svg'), false);
assert.strictEqual(cleanXss.parties[0].name.includes('<'), false);
assert.strictEqual(cleanXss.parties[0].billing_address.includes('<a'), false);

assert.strictEqual(cleanXss.items[0].name.includes('iframe'), false);
assert.strictEqual(cleanXss.items[0].description.includes('<script>'), false);
assert.strictEqual(cleanXss.invoices[0].invoice_number.includes('<script>'), false);
console.log('  Passed: All HTML/XML and script tags stripped across all entities.');

// 4. File Path Traversal & Executable Rejection
console.log('\nTest 4: File Path Traversal and Executable Upload Rejection');
const traversalPayload = {
  firms: [{
    name: 'Path Test Firm',
    logo_path: '../../../../windows/system32/cmd.exe',
    signature_path: '../../uploads/malicious.php'
  }]
};
const cleanPaths = validateAndSanitizeBackup(traversalPayload);
assert.strictEqual(cleanPaths.firms[0].logo_path, null, 'Executable file path should be nullified');
assert.strictEqual(cleanPaths.firms[0].signature_path, null, 'PHP file path should be nullified');

const validImagePath = {
  firms: [{
    name: 'Image Test Firm',
    logo_path: 'C:\\Users\\admin\\uploads\\my_logo.png',
    signature_path: '/uploads/my_sig.jpg'
  }]
};
const cleanImg = validateAndSanitizeBackup(validImagePath);
assert.strictEqual(cleanImg.firms[0].logo_path, '/uploads/my_logo.png');
assert.strictEqual(cleanImg.firms[0].signature_path, '/uploads/my_sig.jpg');
console.log('  Passed: Path traversal and non-image paths blocked; clean image paths preserved.');

// 5. Numeric and Type Clamping
console.log('\nTest 5: Numeric and Type Clamping');
const corruptNumbers = {
  firms: [{ name: 'Number Firm' }],
  items: [{
    id: 1,
    firm_id: 1,
    name: 'Test Item',
    sale_price: -500, // Invalid negative
    purchase_price: 'NaN', // Invalid NaN
    tax_rate: 150 // Invalid > 100%
  }],
  invoice_items: [{
    invoice_id: 1,
    item_id: 1,
    item_name: 'Line 1',
    quantity: -10, // Invalid negative
    rate: 'Infinity' // Invalid infinity
  }]
};
const cleanNumbers = validateAndSanitizeBackup(corruptNumbers);
assert.strictEqual(cleanNumbers.items[0].sale_price, 0, 'Negative sale price must clamp to 0');
assert.strictEqual(cleanNumbers.items[0].purchase_price, 0, 'NaN purchase price must default to 0');
assert.strictEqual(cleanNumbers.items[0].tax_rate, 100, 'Tax rate > 100 must clamp to 100');
assert.strictEqual(cleanNumbers.invoice_items[0].quantity, 0.001, 'Negative quantity must clamp to min 0.001');
assert.strictEqual(cleanNumbers.invoice_items[0].rate, 0, 'Infinity rate must default to 0');
console.log('  Passed: All invalid numbers clamped or defaulted safely.');

// 6. Database Restore Integration & Undefined Parameter Protection
console.log('\nTest 6: Database Restore Integration & Null-Safe Foreign Key Binding');
// Find or create a test user
let user = db.prepare('SELECT * FROM users LIMIT 1').get();
if (!user) {
  const insert = db.prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)').run(
    'Test Backup User', 'backup_test@example.com', 'hash', 'user'
  );
  user = { id: insert.lastInsertRowid };
}

const maliciousDbPayload = {
  firms: [{
    id: 991,
    name: '<b>Secure Test Firm</b>',
    gstin: '<script>bad()</script>27AAAAA0000A1Z5'
  }],
  parties: [{
    id: 881,
    firm_id: 991,
    name: '<script>alert(1)</script>Test Party',
    opening_balance: 500
  }],
  items: [{
    id: 771,
    firm_id: 991,
    name: '<img src=x onerror=alert(1)>Test Product',
    sale_price: -250
  }],
  invoices: [{
    id: 661,
    firm_id: 991,
    party_id: 999999, // Unmatched party ID - must not crash with undefined bind parameter
    invoice_number: 'INV-TEST-001',
    invoice_date: '2026-09-13',
    grand_total: 1000
  }],
  invoice_items: [{
    invoice_id: 661,
    item_id: 888888, // Unmatched item ID - must not crash with undefined bind parameter
    item_name: '<script>xss()</script>Item Line',
    quantity: 2,
    rate: 500,
    total_amount: 1000
  }],
  payments: [{
    firm_id: 991,
    party_id: 881,
    invoice_id: 777777, // Unmatched invoice ID - must not crash with undefined bind parameter
    payment_number: 'PAY-TEST-001',
    payment_date: '2026-09-13',
    amount: 1000
  }]
};

// Execute restore
const restoreResult = Backup.restoreFullBackup(user.id, maliciousDbPayload);
assert.strictEqual(restoreResult, true, 'Restore should succeed');

// Verify database records are sanitized
const restoredFirm = db.prepare('SELECT * FROM firms WHERE user_id = ? AND name LIKE ?').get(user.id, '%Secure Test Firm%');
assert.ok(restoredFirm, 'Firm should be created');
assert.strictEqual(restoredFirm.name, 'Secure Test Firm', 'HTML tags in firm name must be stripped in DB');
assert.strictEqual(restoredFirm.gstin.includes('<script>'), false, 'GSTIN must not contain script tags');

const restoredParty = db.prepare('SELECT * FROM parties WHERE firm_id = ?').get(restoredFirm.id);
assert.ok(restoredParty, 'Party should be created');
assert.strictEqual(restoredParty.name, 'alert(1)Test Party', 'Script tags in party name must be stripped in DB');

const restoredItem = db.prepare('SELECT * FROM items WHERE firm_id = ?').get(restoredFirm.id);
assert.ok(restoredItem, 'Item should be created');
assert.strictEqual(restoredItem.name, 'Test Product', 'Image onerror tags in item name must be stripped in DB');
assert.strictEqual(restoredItem.sale_price, 0, 'Negative price must be stored as 0 in DB');

const restoredInvoice = db.prepare('SELECT * FROM invoices WHERE firm_id = ?').get(restoredFirm.id);
assert.ok(restoredInvoice, 'Invoice should be created');
assert.strictEqual(restoredInvoice.party_id, null, 'Unmatched party ID must be safely stored as NULL, not crashing on undefined');

const restoredInvoiceItem = db.prepare('SELECT * FROM invoice_items WHERE invoice_id = ?').get(restoredInvoice.id);
assert.ok(restoredInvoiceItem, 'Invoice item should be created');
assert.strictEqual(restoredInvoiceItem.item_id, null, 'Unmatched item ID must be safely stored as NULL');
assert.strictEqual(restoredInvoiceItem.item_name, 'xss()Item Line', 'Script tags in invoice item name must be stripped in DB');

console.log('  Passed: Database restore successfully executed and all DB records are verified clean and sanitized.');

console.log('\n=== ALL H5 BACKUP VALIDATION TESTS PASSED SUCCESSFULLY! ===');
process.exit(0);
