const assert = require('assert');
const { User, Firm, Party, Item, Invoice } = require('./models');
const db = require('./config/db');
const http = require('http');

console.log('=== Starting H6: Negative Quantity / Rate in Invoices Security Test ===\n');

// 1. Direct Model Layer Defense Verification
console.log('Test 1: Model Defense - Item pricing cannot be negative');
let firm = db.prepare('SELECT * FROM firms LIMIT 1').get();
if (!firm) {
  let user = db.prepare('SELECT * FROM users LIMIT 1').get();
  if (!user) {
    const uRes = db.prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)').run(
      'H6 Tester', 'h6tester@example.com', 'hash', 'user'
    );
    user = { id: uRes.lastInsertRowid };
  }
  const fRes = db.prepare('INSERT INTO firms (user_id, name) VALUES (?, ?)').run(user.id, 'H6 Test Firm');
  firm = { id: fRes.lastInsertRowid };
}

const negItem = Item.create({
  firm_id: firm.id,
  name: 'H6 Neg Price Test Item',
  sale_price: -150,
  purchase_price: -80,
  tax_rate: -5
});
assert.strictEqual(negItem.sale_price, 0, 'Item sale_price must clamp to 0');
assert.strictEqual(negItem.purchase_price, 0, 'Item purchase_price must clamp to 0');
assert.strictEqual(negItem.tax_rate, 0, 'Item tax_rate must clamp to 0');
console.log('  Passed: Item pricing defensively clamped to non-negative.');

// 2. Direct Model Layer - Stock Adjustment with Negative Quantity
console.log('\nTest 2: Model Defense - Stock adjustment ignores zero or negative quantities');
const stockItem = Item.create({
  firm_id: firm.id,
  name: 'H6 Stock Protection Item',
  sale_price: 100,
  opening_stock: 50
});
const initialStock = stockItem.current_stock;

// Attempt to create an invoice directly through Invoice.create with negative quantity
const invData = {
  firm_id: firm.id,
  type: 'sale',
  invoice_number: 'H6-TEST-' + Date.now(),
  invoice_date: '2026-09-13',
  party_name: 'H6 Test Customer',
  subtotal: 500,
  grand_total: 500,
  paid_amount: 200
};
const lineItems = [{
  item_id: stockItem.id,
  item_name: stockItem.name,
  quantity: -10, // Attempt to corrupt stock via negative quantity
  rate: 50,
  discount_percent: 0,
  taxable_amount: 500,
  total_amount: 500
}];

assert.throws(() => {
  Invoice.create(invData, lineItems);
}, /must be greater than 0/);

const reloadedStock = db.prepare('SELECT current_stock FROM items WHERE id = ?').get(stockItem.id);
assert.strictEqual(reloadedStock.current_stock, initialStock, 'Negative quantity must NOT alter item stock');
console.log('  Passed: Invoice.create strictly rejects negative quantity and preserves inventory stock.');


// 3. Controller Layer Validation via Mock Request Execution
console.log('\nTest 3: Controller Layer - Submission validation rejects negative values');
const invoiceController = require('./controllers/invoiceController');

function runControllerTest(body, expectedErrorPattern) {
  let redirected = false;
  let flashMsg = '';
  let redirectTarget = '';

  const req = {
    body,
    activeFirm: firm,
    flash: (type, msg) => {
      if (type === 'error_msg') flashMsg = msg;
    }
  };
  const res = {
    redirect: (url) => {
      redirected = true;
      redirectTarget = url;
    }
  };

  invoiceController.postCreate(req, res);
  assert.ok(redirected, 'Controller should redirect on validation error');
  assert.ok(
    expectedErrorPattern.test(flashMsg),
    `Flash message "${flashMsg}" should match ${expectedErrorPattern}`
  );
}

// 3a. Negative Quantity
runControllerTest({
  type: 'sale',
  invoice_number: '1001',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['-5'],
  rate: ['100']
}, /Invalid Quantity.*greater than 0/i);
console.log('  Passed: Negative quantity rejected by controller.');

// 3b. Zero Quantity
runControllerTest({
  type: 'sale',
  invoice_number: '1002',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['0'],
  rate: ['100']
}, /Invalid Quantity.*greater than 0/i);
console.log('  Passed: Zero quantity rejected by controller.');

// 3c. Negative Rate
runControllerTest({
  type: 'sale',
  invoice_number: '1003',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['2'],
  rate: ['-250']
}, /Invalid Price \/ Rate.*cannot be negative/i);
console.log('  Passed: Negative rate rejected by controller.');

// 3d. Negative Item Discount Percent
runControllerTest({
  type: 'sale',
  invoice_number: '1004',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['2'],
  rate: ['100'],
  item_discount_percent: ['-15']
}, /Invalid Discount.*between 0% and 100%/i);
console.log('  Passed: Negative item discount percent rejected by controller.');

// 3e. Item Discount Percent > 100
runControllerTest({
  type: 'sale',
  invoice_number: '1005',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['2'],
  rate: ['100'],
  item_discount_percent: ['120']
}, /Invalid Discount.*between 0% and 100%/i);
console.log('  Passed: Item discount percent > 100 rejected by controller.');

// 3f. Negative Overall Discount
runControllerTest({
  type: 'sale',
  invoice_number: '1006',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['2'],
  rate: ['100'],
  discount_value: '-50'
}, /Invalid Discount.*cannot be negative/i);
console.log('  Passed: Negative overall discount rejected by controller.');

// 3g. Negative Paid Amount
runControllerTest({
  type: 'sale',
  invoice_number: '1007',
  party_name: 'Test Party',
  item_name: ['Widget'],
  quantity: ['2'],
  rate: ['100'],
  paid_amount: '-100'
}, /Invalid Paid Amount.*cannot be negative/i);
console.log('  Passed: Negative paid amount rejected by controller.');

// 4. Legitimate Valid Submission Must Succeed
console.log('\nTest 4: Legitimate valid invoice submission succeeds seamlessly');
let legitRedirected = false;
let legitFlash = '';
let legitRedirectUrl = '';
const legitReq = {
  body: {
    type: 'sale',
    invoice_number: '1008',
    party_name: 'Valid Customer',
    item_name: ['Valid Product'],
    quantity: ['3'],
    rate: ['200'],
    item_discount_percent: ['5'],
    discount_value: '10',
    paid_amount: '100'
  },
  activeFirm: firm,
  flash: (type, msg) => {
    if (type === 'success_msg') legitFlash = msg;
  }
};
const legitRes = {
  redirect: (url) => {
    legitRedirected = true;
    legitRedirectUrl = url;
  }
};
invoiceController.postCreate(legitReq, legitRes);
assert.ok(legitRedirected, 'Valid invoice should redirect');
assert.ok(legitRedirectUrl.startsWith('/invoices/view/'), `Redirect should go to invoice view (got ${legitRedirectUrl})`);
assert.ok(legitFlash.includes('created successfully'), 'Success flash message expected');
console.log('  Passed: Valid invoice successfully created.');

console.log('\n=== ALL H6 NEGATIVE INVOICE SECURITY TESTS PASSED! ===');
process.exit(0);
