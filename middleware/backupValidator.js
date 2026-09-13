const path = require('path');

/**
 * Strict Backup Validation & Sanitization Schema (Vulnerability H5)
 *
 * Protects database restore against:
 * - Denial-of-service / memory bombs (excessive array counts)
 * - Stored XSS injection (HTML/script tags stripped from all string fields)
 * - Path traversal (file paths sanitized to stay strictly in /uploads/ with no ../)
 * - Number corruption (NaN, Infinity, and invalid negative quantities/rates clamped)
 * - Schema tampering (unknown enums sanitized to secure defaults)
 */

const LIMITS = {
  MAX_FIRMS: 50,
  MAX_PARTIES: 10000,
  MAX_ITEMS: 10000,
  MAX_INVOICES: 20000,
  MAX_INVOICE_ITEMS: 100000,
  MAX_PAYMENTS: 20000
};

function sanitizeString(val, maxLen = 255, allowEmpty = true) {
  if (val === null || val === undefined) return allowEmpty ? null : '';
  let str = String(val).trim();
  // Strip all HTML/XML tags including nested/malformed attempts
  while (/<[^>]*>/.test(str)) {
    str = str.replace(/<[^>]*>/g, '');
  }
  // Strip any remaining angle brackets to neutralize broken tag injection
  str = str.replace(/[<>]/g, '');
  // Strip null bytes and ASCII control characters
  str = str.replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F-\u009F]/g, '');
  if (str.length > maxLen) {
    str = str.substring(0, maxLen);
  }
  if (!str && allowEmpty) {
    return null;
  }
  return str;
}

function sanitizeNumber(val, defaultVal = 0, min = 0, max = 1000000000) {
  if (val === null || val === undefined) return defaultVal;
  const num = parseFloat(val);
  if (isNaN(num) || !isFinite(num)) return defaultVal;
  if (num < min) return min;
  if (num > max) return max;
  return num;
}

function sanitizeInteger(val, defaultVal = 0, min = 0, max = 1000000000) {
  const num = sanitizeNumber(val, defaultVal, min, max);
  return Math.round(num);
}

function sanitizeFilePath(val) {
  if (!val || typeof val !== 'string') return null;
  const cleaned = val.replace(/\\/g, '/');
  const base = path.basename(cleaned);
  // Must be a clean filename with allowed image extensions
  if (!base || base.includes('..') || !/^[\w\-\.]+\.(jpg|jpeg|png|webp)$/i.test(base)) {
    return null;
  }
  return `/uploads/${base}`;
}

function sanitizeEnum(val, allowedValues, defaultValue) {
  if (typeof val === 'string') {
    const lower = val.trim().toLowerCase();
    if (allowedValues.includes(lower)) return lower;
  }
  return defaultValue;
}

function sanitizeDate(val) {
  if (!val || typeof val !== 'string') {
    return new Date().toISOString().split('T')[0];
  }
  const trimmed = val.trim();
  if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
    return trimmed;
  }
  try {
    const d = new Date(trimmed);
    if (!isNaN(d.getTime())) {
      return d.toISOString().split('T')[0];
    }
  } catch (e) {}
  return new Date().toISOString().split('T')[0];
}

/**
 * Validates the backup JSON structure and returns a clean, sanitized backup object.
 * Throws an Error with an informative user message if the backup is corrupt or malicious.
 */
function validateAndSanitizeBackup(backupData) {
  if (!backupData || typeof backupData !== 'object' || Array.isArray(backupData)) {
    throw new Error('Invalid backup file format: Root payload must be a valid JSON object.');
  }

  // 1. Validate Firms
  if (!Array.isArray(backupData.firms) || backupData.firms.length === 0) {
    throw new Error('Invalid backup file format: Backup must contain at least one business firm.');
  }
  if (backupData.firms.length > LIMITS.MAX_FIRMS) {
    throw new Error(`Invalid backup file: Exceeded limit of ${LIMITS.MAX_FIRMS} firms.`);
  }

  // 2. Validate Array Boundaries
  if (backupData.parties && (!Array.isArray(backupData.parties) || backupData.parties.length > LIMITS.MAX_PARTIES)) {
    throw new Error(`Invalid backup file: Exceeded limit of ${LIMITS.MAX_PARTIES} parties.`);
  }
  if (backupData.items && (!Array.isArray(backupData.items) || backupData.items.length > LIMITS.MAX_ITEMS)) {
    throw new Error(`Invalid backup file: Exceeded limit of ${LIMITS.MAX_ITEMS} items.`);
  }
  if (backupData.invoices && (!Array.isArray(backupData.invoices) || backupData.invoices.length > LIMITS.MAX_INVOICES)) {
    throw new Error(`Invalid backup file: Exceeded limit of ${LIMITS.MAX_INVOICES} invoices.`);
  }
  if (backupData.invoice_items && (!Array.isArray(backupData.invoice_items) || backupData.invoice_items.length > LIMITS.MAX_INVOICE_ITEMS)) {
    throw new Error(`Invalid backup file: Exceeded limit of ${LIMITS.MAX_INVOICE_ITEMS} line items.`);
  }
  if (backupData.payments && (!Array.isArray(backupData.payments) || backupData.payments.length > LIMITS.MAX_PAYMENTS)) {
    throw new Error(`Invalid backup file: Exceeded limit of ${LIMITS.MAX_PAYMENTS} payments.`);
  }

  const cleanFirms = backupData.firms.map((f, idx) => ({
    id: f.id !== undefined ? sanitizeInteger(f.id, idx + 1) : idx + 1,
    name: sanitizeString(f.name, 100, false) || `Restored Firm ${idx + 1}`,
    gstin: sanitizeString(f.gstin, 20),
    pan: sanitizeString(f.pan, 20),
    phone: sanitizeString(f.phone, 20),
    email: sanitizeString(f.email, 100),
    address: sanitizeString(f.address, 500),
    city: sanitizeString(f.city, 100),
    state: sanitizeString(f.state, 100),
    state_code: sanitizeString(f.state_code, 10),
    pincode: sanitizeString(f.pincode, 10),
    bank_name: sanitizeString(f.bank_name, 100),
    bank_account_no: sanitizeString(f.bank_account_no, 50),
    bank_ifsc: sanitizeString(f.bank_ifsc, 20),
    bank_branch: sanitizeString(f.bank_branch, 100),
    upi_id: sanitizeString(f.upi_id, 100),
    terms: sanitizeString(f.terms, 2000),
    logo_path: sanitizeFilePath(f.logo_path),
    signature_path: sanitizeFilePath(f.signature_path),
    is_default: f.is_default ? 1 : 0
  }));

  const cleanParties = (backupData.parties || []).map((p, idx) => ({
    id: p.id !== undefined ? sanitizeInteger(p.id, idx + 1) : idx + 1,
    firm_id: sanitizeInteger(p.firm_id, 0),
    type: sanitizeEnum(p.type, ['customer', 'supplier'], 'customer'),
    name: sanitizeString(p.name, 100, false) || `Restored Party ${idx + 1}`,
    phone: sanitizeString(p.phone, 20),
    email: sanitizeString(p.email, 100),
    gstin: sanitizeString(p.gstin, 20),
    pan: sanitizeString(p.pan, 20),
    billing_address: sanitizeString(p.billing_address, 500),
    shipping_address: sanitizeString(p.shipping_address, 500),
    city: sanitizeString(p.city, 100),
    state: sanitizeString(p.state, 100),
    state_code: sanitizeString(p.state_code, 10),
    pincode: sanitizeString(p.pincode, 10),
    opening_balance: sanitizeNumber(p.opening_balance, 0, -1000000000, 1000000000)
  }));

  const cleanItems = (backupData.items || []).map((it, idx) => ({
    id: it.id !== undefined ? sanitizeInteger(it.id, idx + 1) : idx + 1,
    firm_id: sanitizeInteger(it.firm_id, 0),
    name: sanitizeString(it.name, 100, false) || `Restored Item ${idx + 1}`,
    item_code: sanitizeString(it.item_code, 50),
    hsn_code: sanitizeString(it.hsn_code, 20),
    unit: sanitizeString(it.unit, 20) || 'PCS',
    sale_price: sanitizeNumber(it.sale_price, 0, 0, 1000000000),
    purchase_price: sanitizeNumber(it.purchase_price, 0, 0, 1000000000),
    tax_rate: sanitizeNumber(it.tax_rate, 0, 0, 100),
    tax_inclusive: it.tax_inclusive ? 1 : 0,
    opening_stock: sanitizeNumber(it.opening_stock, 0, -1000000, 1000000),
    current_stock: sanitizeNumber(it.current_stock, 0, -1000000, 1000000),
    low_stock_threshold: sanitizeNumber(it.low_stock_threshold, 0, 0, 1000000),
    description: sanitizeString(it.description, 1000)
  }));

  const cleanInvoices = (backupData.invoices || []).map((inv, idx) => ({
    id: inv.id !== undefined ? sanitizeInteger(inv.id, idx + 1) : idx + 1,
    firm_id: sanitizeInteger(inv.firm_id, 0),
    type: sanitizeEnum(inv.type, ['sale', 'purchase'], 'sale'),
    invoice_number: sanitizeString(inv.invoice_number, 50, false) || `INV-RESTORE-${idx + 1}`,
    invoice_date: sanitizeDate(inv.invoice_date),
    due_date: inv.due_date ? sanitizeDate(inv.due_date) : null,
    party_id: inv.party_id !== undefined && inv.party_id !== null ? sanitizeInteger(inv.party_id, 0) : null,
    party_name: sanitizeString(inv.party_name, 100, false) || 'Walk-in Customer',
    party_phone: sanitizeString(inv.party_phone, 20),
    party_gstin: sanitizeString(inv.party_gstin, 20),
    party_address: sanitizeString(inv.party_address, 500),
    party_state: sanitizeString(inv.party_state, 100),
    party_state_code: sanitizeString(inv.party_state_code, 10),
    is_gst_bill: inv.is_gst_bill ? 1 : 0,
    is_interstate: inv.is_interstate ? 1 : 0,
    subtotal: sanitizeNumber(inv.subtotal, 0),
    discount_type: sanitizeEnum(inv.discount_type, ['fixed', 'percentage', 'amount'], 'fixed'),
    discount_value: sanitizeNumber(inv.discount_value, 0, 0, 1000000000),
    discount_amount: sanitizeNumber(inv.discount_amount, 0, 0, 1000000000),
    taxable_amount: sanitizeNumber(inv.taxable_amount, 0),
    cgst_amount: sanitizeNumber(inv.cgst_amount, 0),
    sgst_amount: sanitizeNumber(inv.sgst_amount, 0),
    igst_amount: sanitizeNumber(inv.igst_amount, 0),
    tax_amount: sanitizeNumber(inv.tax_amount, 0),
    round_off: sanitizeNumber(inv.round_off, 0, -100, 100),
    grand_total: sanitizeNumber(inv.grand_total, 0),
    paid_amount: sanitizeNumber(inv.paid_amount, 0),
    balance_due: sanitizeNumber(inv.balance_due, 0),
    payment_status: sanitizeEnum(inv.payment_status, ['paid', 'partial', 'unpaid'], 'unpaid'),
    payment_mode: sanitizeString(inv.payment_mode, 50) || 'Cash',
    notes: sanitizeString(inv.notes, 1000),
    terms: sanitizeString(inv.terms, 2000)
  }));

  const cleanInvoiceItems = (backupData.invoice_items || []).map((ii, idx) => ({
    invoice_id: sanitizeInteger(ii.invoice_id, 0),
    item_id: ii.item_id !== undefined && ii.item_id !== null ? sanitizeInteger(ii.item_id, 0) : null,
    item_name: sanitizeString(ii.item_name, 100, false) || `Restored Line Item ${idx + 1}`,
    hsn_code: sanitizeString(ii.hsn_code, 20),
    unit: sanitizeString(ii.unit, 20) || 'PCS',
    quantity: sanitizeNumber(ii.quantity, 1, 0.001, 1000000),
    rate: sanitizeNumber(ii.rate, 0, 0, 1000000000),
    discount_percent: sanitizeNumber(ii.discount_percent, 0, 0, 100),
    discount_amount: sanitizeNumber(ii.discount_amount, 0),
    taxable_amount: sanitizeNumber(ii.taxable_amount, 0),
    tax_rate: sanitizeNumber(ii.tax_rate, 0, 0, 100),
    cgst_rate: sanitizeNumber(ii.cgst_rate, 0, 0, 100),
    cgst_amount: sanitizeNumber(ii.cgst_amount, 0),
    sgst_rate: sanitizeNumber(ii.sgst_rate, 0, 0, 100),
    sgst_amount: sanitizeNumber(ii.sgst_amount, 0),
    igst_rate: sanitizeNumber(ii.igst_rate, 0, 0, 100),
    igst_amount: sanitizeNumber(ii.igst_amount, 0),
    total_amount: sanitizeNumber(ii.total_amount, 0)
  }));

  const cleanPayments = (backupData.payments || []).map((p, idx) => ({
    firm_id: sanitizeInteger(p.firm_id, 0),
    type: sanitizeEnum(p.type, ['payment_in', 'payment_out'], 'payment_in'),
    payment_number: sanitizeString(p.payment_number, 50, false) || `PAY-RESTORE-${idx + 1}`,
    payment_date: sanitizeDate(p.payment_date),
    party_id: sanitizeInteger(p.party_id, 0),
    invoice_id: p.invoice_id !== undefined && p.invoice_id !== null ? sanitizeInteger(p.invoice_id, 0) : null,
    amount: sanitizeNumber(p.amount, 0, 0, 1000000000),
    payment_mode: sanitizeString(p.payment_mode, 50) || 'Cash',
    reference_no: sanitizeString(p.reference_no, 100),
    notes: sanitizeString(p.notes, 1000)
  }));

  return {
    firms: cleanFirms,
    parties: cleanParties,
    items: cleanItems,
    invoices: cleanInvoices,
    invoice_items: cleanInvoiceItems,
    payments: cleanPayments
  };
}

module.exports = {
  LIMITS,
  validateAndSanitizeBackup,
  sanitizeString,
  sanitizeNumber,
  sanitizeFilePath,
  sanitizeEnum,
  sanitizeDate
};
