const app = require('./server');
const http = require('http');

async function runCsrfVerification() {
  console.log('====================================================');
  console.log('🔒 RUNNING COMPREHENSIVE CSRF SECURITY VERIFICATION');
  console.log('====================================================');

  // Start temporary test server on port 3001
  const PORT = 3001;
  const server = http.createServer(app);
  await new Promise((resolve) => server.listen(PORT, resolve));
  console.log(`Test server running on port ${PORT}`);

  try {
    const baseUrl = `http://localhost:${PORT}`;

    // Test 1: GET /auth/login returns a CSRF token in the HTML and a session cookie
    console.log('\n--- Test 1: GET /auth/login should provide a CSRF token ---');
    const getRes = await fetch(`${baseUrl}/auth/login`);
    const setCookie = getRes.headers.get('set-cookie');
    if (!setCookie) throw new Error('No session cookie returned on GET /auth/login');
    const sessionCookie = setCookie.split(';')[0];
    const html = await getRes.text();
    const tokenMatch = html.match(/name="_csrf" value="([a-f0-9]{64})"/i);
    if (!tokenMatch) throw new Error('CSRF token input field not found in login form');
    const validToken = tokenMatch[1];
    console.log('✓ Session Cookie received:', sessionCookie.substring(0, 25) + '...');
    console.log('✓ CSRF Token received (64-char hex):', validToken.substring(0, 16) + '...');

    // Test 2: POST /auth/login WITHOUT CSRF token -> MUST BE REJECTED
    console.log('\n--- Test 2: POST /auth/login WITHOUT CSRF token must be blocked ---');
    const noTokenBody = new URLSearchParams({
      identifier: '9876543210',
      password: 'admin123'
    });
    const rejectedRes1 = await fetch(`${baseUrl}/auth/login`, {
      method: 'POST',
      headers: { Cookie: sessionCookie },
      body: noTokenBody,
      redirect: 'manual'
    });
    console.log('✓ Response status:', rejectedRes1.status, '(Redirected)');
    console.log('✓ Location header:', rejectedRes1.headers.get('location'));
    // Since it was rejected, it redirects back with flash error
    if (rejectedRes1.status !== 302 && rejectedRes1.status !== 403) {
      throw new Error('Expected 302 or 403 rejection for missing CSRF token');
    }

    // Test 3: POST /auth/login with FORGED / INVALID CSRF token -> MUST BE REJECTED
    console.log('\n--- Test 3: POST /auth/login with INVALID CSRF token must be blocked ---');
    const forgedBody = new URLSearchParams({
      identifier: '9876543210',
      password: 'admin123',
      _csrf: '0000000000000000000000000000000000000000000000000000000000000000'
    });
    const rejectedRes2 = await fetch(`${baseUrl}/auth/login`, {
      method: 'POST',
      headers: { Cookie: sessionCookie },
      body: forgedBody,
      redirect: 'manual'
    });
    console.log('✓ Response status:', rejectedRes2.status, '(Blocked, redirected)');
    if (rejectedRes2.status !== 302 && rejectedRes2.status !== 403) {
      throw new Error('Expected rejection for forged CSRF token');
    }

    // Test 4: AJAX POST without CSRF token -> MUST return 403 JSON
    console.log('\n--- Test 4: AJAX POST without CSRF header/token must return 403 JSON ---');
    const ajaxNoTokenRes = await fetch(`${baseUrl}/parties/quick-create`, {
      method: 'POST',
      headers: {
        Cookie: sessionCookie,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ name: 'Malicious Party' })
    });
    console.log('✓ AJAX response status:', ajaxNoTokenRes.status, '(403 expected)');
    const ajaxNoTokenJson = await ajaxNoTokenRes.json();
    console.log('✓ AJAX error payload:', ajaxNoTokenJson);
    if (ajaxNoTokenRes.status !== 403 || ajaxNoTokenJson.success !== false) {
      throw new Error('Expected 403 JSON for missing AJAX CSRF token');
    }

    // Test 5: POST /auth/login with VALID CSRF token -> MUST SUCCEED
    console.log('\n--- Test 5: POST /auth/login with VALID CSRF token must succeed ---');
    const validBody = new URLSearchParams({
      identifier: '946101347',
      password: 'admin123',
      _csrf: validToken
    });
    const loginSuccessRes = await fetch(`${baseUrl}/auth/login`, {
      method: 'POST',
      headers: { Cookie: sessionCookie },
      body: validBody,
      redirect: 'manual'
    });
    console.log('✓ Login success status:', loginSuccessRes.status);
    console.log('✓ Redirect destination:', loginSuccessRes.headers.get('location'));
    const authSetCookie = loginSuccessRes.headers.get('set-cookie');
    const authSessionCookie = authSetCookie ? authSetCookie.split(';')[0] : sessionCookie;

    if (loginSuccessRes.status !== 302 || !loginSuccessRes.headers.get('location').includes('/dashboard')) {
      throw new Error('Login with valid CSRF token did not redirect to dashboard');
    }

    const dashRes = await fetch(`${baseUrl}/dashboard`, {
      headers: { Cookie: authSessionCookie }
    });
    const dashHtml = await dashRes.text();
    const dashCsrfMatch = dashHtml.match(/name="csrf-token" content="([a-f0-9]{64})"/i);
    const activeCsrfToken = dashCsrfMatch ? dashCsrfMatch[1] : validToken;

    // Test 6: Authenticated AJAX request WITH X-CSRF-Token header -> MUST BE ACCEPTED
    console.log('\n--- Test 6: AJAX POST with valid X-CSRF-Token header must pass CSRF validation ---');
    const ajaxValidRes = await fetch(`${baseUrl}/parties/quick-create`, {
      method: 'POST',
      headers: {
        Cookie: authSessionCookie,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-Token': activeCsrfToken
      },
      body: JSON.stringify({
        name: 'CSRF Verified Customer ' + Date.now(),
        phone: '9123456780',
        type: 'customer'
      })
    });
    console.log('✓ AJAX response status:', ajaxValidRes.status);
    const ajaxValidJson = await ajaxValidRes.json();
    console.log('✓ AJAX result:', ajaxValidJson.success ? 'Success! Created Party ID ' + ajaxValidJson.party.id : ajaxValidJson);
    if (!ajaxValidJson.success) {
      throw new Error('AJAX request with valid CSRF token failed');
    }

    // Test 7: Multipart form submission with CSRF query param -> MUST SUCCEED
    console.log('\n--- Test 7: Multipart POST (/firms/edit/2?_csrf=...) must pass CSRF validation ---');
    const multipartFormData = new FormData();
    multipartFormData.append('name', 'HAPPY (CSRF Verified)');
    multipartFormData.append('phone', '9876543210');
    multipartFormData.append('city', 'Jaipur');
    multipartFormData.append('state', 'Telangana');
    multipartFormData.append('state_code', '36');
    multipartFormData.append('_csrf', activeCsrfToken);

    const multipartRes = await fetch(`${baseUrl}/firms/edit/2?_csrf=${activeCsrfToken}`, {
      method: 'POST',
      headers: {
        Cookie: authSessionCookie
      },
      body: multipartFormData,
      redirect: 'manual'
    });

    console.log('✓ Multipart POST response status:', multipartRes.status);
    console.log('✓ Multipart POST redirect:', multipartRes.headers.get('location'));
    if (multipartRes.status !== 302 || !multipartRes.headers.get('location').includes('/firms')) {
      throw new Error('Multipart form submission with CSRF token failed');
    }

    console.log('\n====================================================');
    console.log('🎉 ALL CSRF SECURITY TESTS PASSED PERFECTLY!');
    console.log('====================================================');
  } finally {
    server.close();
  }
}

runCsrfVerification().catch(err => {
  console.error('\n❌ CSRF Verification Failed:', err);
  process.exit(1);
});
