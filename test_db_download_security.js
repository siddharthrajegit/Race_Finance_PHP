const assert = require('assert');
const http = require('http');
const app = require('./server');
const { db, User, Admin } = require('./models');

console.log('========================================================');
console.log('🔒 VERIFYING H4: DATABASE DOWNLOAD PASSWORD CONFIRMATION');
console.log('========================================================\n');

async function runTests() {
  const PORT = 3008;
  const server = http.createServer(app);
  await new Promise(resolve => server.listen(PORT, resolve));

  try {
    const baseUrl = `http://localhost:${PORT}`;

    // 1. Log in as admin to obtain authenticated session & CSRF token
    const loginPageRes = await fetch(`${baseUrl}/auth/login`);
    const setCookie = loginPageRes.headers.get('set-cookie');
    assert.ok(setCookie, 'Session cookie must be provided');
    const sessionCookie = setCookie.split(';')[0];
    const loginHtml = await loginPageRes.text();
    const csrfMatch = loginHtml.match(/name="_csrf" value="([a-f0-9]{64})"/i);
    assert.ok(csrfMatch, 'CSRF token must be present');
    const csrfToken = csrfMatch[1];

    // Submit admin credentials (admin: 9414223562 / admin123)
    const adminIdentifier = '9414223562';

    const loginBody = new URLSearchParams({
      identifier: adminIdentifier,
      password: 'admin123',
      _csrf: csrfToken
    });

    const loginRes = await fetch(`${baseUrl}/auth/login`, {
      method: 'POST',
      headers: {
        Cookie: sessionCookie,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: loginBody,
      redirect: 'manual'
    });

    const authCookieHeader = loginRes.headers.get('set-cookie');
    const authCookie = authCookieHeader ? authCookieHeader.split(';')[0] : sessionCookie;
    console.log('✓ Admin authenticated successfully');

    // 2. Fetch system page to get latest CSRF token
    const sysRes = await fetch(`${baseUrl}/admin/system`, {
      headers: { Cookie: authCookie }
    });
    assert.strictEqual(sysRes.status, 200);
    const sysHtml = await sysRes.text();
    const sysCsrfMatch = sysHtml.match(/name="_csrf" value="([a-f0-9]{64})"/i);
    assert.ok(sysCsrfMatch, 'CSRF token found on system page');
    const sysCsrfToken = sysCsrfMatch[1];
    assert.ok(sysHtml.includes('downloadDbModal'), 'Password confirmation modal must be in system page HTML');
    console.log('✓ Confirmation modal detected in system administration page');

    // --- Test 1: GET /admin/system/download-db MUST NOT download raw DB ---
    console.log('\n--- Test 1: GET /admin/system/download-db must be blocked ---');
    const getRes = await fetch(`${baseUrl}/admin/system/download-db`, {
      headers: { Cookie: authCookie },
      redirect: 'manual'
    });
    assert.strictEqual(getRes.status, 302);
    assert.ok(getRes.headers.get('location').includes('/admin/system'));
    console.log('✓ GET request safely redirected to /admin/system without downloading DB');

    // --- Test 2: POST without CSRF token MUST be blocked ---
    console.log('\n--- Test 2: POST /admin/system/download-db without CSRF must fail ---');
    const noCsrfRes = await fetch(`${baseUrl}/admin/system/download-db`, {
      method: 'POST',
      headers: {
        Cookie: authCookie,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({ admin_password: 'admin123' }),
      redirect: 'manual'
    });
    assert.strictEqual(noCsrfRes.status, 302);
    console.log('✓ POST without CSRF token blocked by CSRF middleware');

    // --- Test 3: POST with WRONG admin password MUST be rejected ---
    console.log('\n--- Test 3: POST with INCORRECT password must be rejected ---');
    const wrongPassRes = await fetch(`${baseUrl}/admin/system/download-db`, {
      method: 'POST',
      headers: {
        Cookie: authCookie,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        _csrf: sysCsrfToken,
        admin_password: 'completely_wrong_password_123'
      }),
      redirect: 'manual'
    });
    assert.strictEqual(wrongPassRes.status, 302);
    assert.ok(wrongPassRes.headers.get('location').includes('/admin/system'));

    // Check audit log recorded the failed attempt
    const failedLog = db.prepare("SELECT * FROM admin_audit_logs WHERE action = 'FAILED_DB_DOWNLOAD_INVALID_PASSWORD' ORDER BY id DESC LIMIT 1").get();
    assert.ok(failedLog, 'Failed download attempt must be logged in admin_audit_logs');
    console.log('✓ Incorrect password rejected, audit log recorded:', failedLog.action);

    // --- Test 4: POST with CORRECT admin password MUST succeed & stream DB ---
    console.log('\n--- Test 4: POST with CORRECT password must download DB snapshot ---');
    const successRes = await fetch(`${baseUrl}/admin/system/download-db`, {
      method: 'POST',
      headers: {
        Cookie: authCookie,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        _csrf: sysCsrfToken,
        admin_password: 'admin123'
      }),
      redirect: 'manual'
    });
    assert.strictEqual(successRes.status, 200);
    const contentDisposition = successRes.headers.get('content-disposition');
    assert.ok(contentDisposition && contentDisposition.includes('RACE_FINANCE_DB_SNAPSHOT'), 'Must return database file attachment');
    const dbBuffer = await successRes.arrayBuffer();
    assert.ok(dbBuffer.byteLength > 1000, 'Database snapshot must not be empty');

    // Check audit log recorded the success
    const successLog = db.prepare("SELECT * FROM admin_audit_logs WHERE action = 'DOWNLOAD_DATABASE_BACKUP' ORDER BY id DESC LIMIT 1").get();
    assert.ok(successLog, 'Successful download must be logged in admin_audit_logs');
    console.log(`✓ Correct password accepted! DB snapshot received (${dbBuffer.byteLength} bytes)`);
    console.log('✓ Audit log recorded:', successLog.action, 'by', successLog.admin_name);

    console.log('\n========================================================');
    console.log('🎉 ALL H4 DATABASE DOWNLOAD SECURITY TESTS PASSED (100%)!');
    console.log('========================================================');
  } finally {
    server.close();
  }
}

runTests().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
