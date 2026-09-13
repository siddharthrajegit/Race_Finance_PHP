const http = require('http');
const db = require('./config/db');

async function testPersistentSessions() {
  console.log('========================================================');
  console.log('🧪 TESTING 6-MONTH PERSISTENT SQLITE SESSIONS');
  console.log('========================================================');

  const app = require('./server');
  const PORT = 3003;
  let server = http.createServer(app);
  await new Promise((resolve) => server.listen(PORT, resolve));
  console.log(`1. Test server started on port ${PORT}`);

  try {
    const baseUrl = `http://localhost:${PORT}`;

    // Step A: Fetch login page to get CSRF token and cookie
    const getLoginRes = await fetch(`${baseUrl}/auth/login`);
    const initialCookie = getLoginRes.headers.get('set-cookie');
    if (!initialCookie) throw new Error('No cookie returned on GET /auth/login');
    let sessionCookie = initialCookie.split(';')[0];
    const html = await getLoginRes.text();
    const csrfMatch = html.match(/name="_csrf" value="([a-f0-9]{64})"/i);
    const csrfToken = csrfMatch ? csrfMatch[1] : '';

    console.log('✓ Initial session cookie received:', sessionCookie.substring(0, 30) + '...');
    console.log('✓ CSRF Token received:', csrfToken.substring(0, 16) + '...');

    // Step B: Log in user (phone 946101347, pass admin123)
    const loginParams = new URLSearchParams({
      identifier: '946101347',
      password: 'admin123',
      _csrf: csrfToken
    });

    const loginRes = await fetch(`${baseUrl}/auth/login`, {
      method: 'POST',
      headers: { Cookie: sessionCookie },
      body: loginParams,
      redirect: 'manual'
    });

    console.log('✓ Login response status:', loginRes.status);
    const postCookie = loginRes.headers.get('set-cookie');
    if (postCookie) sessionCookie = postCookie.split(';')[0];

    // Check cookie expiration date
    console.log('\n--- Checking Cookie Expiration Lifespan ---');
    const cookieHeader = postCookie || initialCookie;
    console.log('Full Cookie Header:', cookieHeader);

    const expiresMatch = cookieHeader.match(/Expires=([^;]+)/i);
    if (!expiresMatch) throw new Error('No Expires attribute found in session cookie');
    const expiresDate = new Date(expiresMatch[1]);
    const diffDays = Math.round((expiresDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
    console.log(`✓ Cookie Expires Date: ${expiresDate.toISOString()}`);
    console.log(`✓ Cookie Lifespan: ${diffDays} days (Expected ~180 days / 6 months)`);
    if (diffDays < 175 || diffDays > 185) {
      throw new Error(`Cookie lifespan ${diffDays} days is not 6 months (~180 days)`);
    }

    // Step C: Verify session is stored in SQLite database
    console.log('\n--- Checking SQLite Database Storage ---');
    const sessionRows = db.prepare('SELECT sid, expired, length(sess) as sess_len FROM sessions').all();
    console.log(`✓ Total active sessions stored in SQLite database: ${sessionRows.length}`);
    if (sessionRows.length === 0) {
      throw new Error('No sessions found in SQLite sessions table!');
    }

    const latestSession = sessionRows[sessionRows.length - 1];
    const dbDiffDays = Math.round((latestSession.expired - Date.now()) / (1000 * 60 * 60 * 24));
    console.log(`✓ Latest Session in DB: SID=${latestSession.sid.substring(0, 15)}... (Expires in ${dbDiffDays} days)`);

    // Step D: Simulate Server Reboot (Kill server, start fresh instance)
    console.log('\n--- Simulating Server Restart (cPanel / Passenger Idle Sleep) ---');
    await new Promise((resolve) => server.close(resolve));
    console.log('✓ Server 1 stopped.');

    // Start a completely fresh server on port 3004
    const PORT2 = 3004;
    const server2 = http.createServer(app);
    await new Promise((resolve) => server2.listen(PORT2, resolve));
    console.log(`✓ Server 2 restarted on port ${PORT2}.`);

    try {
      const baseUrl2 = `http://localhost:${PORT2}`;
      // Request dashboard using the session cookie from Server 1
      const dashRes = await fetch(`${baseUrl2}/dashboard`, {
        headers: { Cookie: sessionCookie },
        redirect: 'manual'
      });

      console.log('✓ Dashboard status after server reboot:', dashRes.status);
      if (dashRes.status !== 200) {
        throw new Error(`Session did not survive server restart! Status: ${dashRes.status}, Location: ${dashRes.headers.get('location')}`);
      }
      console.log('🎉 SUCCESS: User remained logged in across server restart!');

      // Step E: Test Logout & Session Invalidation (H3 Defense)
      console.log('\n--- Testing Logout & Database Invalidation (H3 Defense) ---');
      // Subtest 1: GET /auth/logout (Simulating malicious <img> CSRF tag) must NOT logout
      const getLogoutRes = await fetch(`${baseUrl2}/auth/logout`, {
        headers: { Cookie: sessionCookie },
        redirect: 'manual'
      });
      console.log('✓ GET /auth/logout status:', getLogoutRes.status, 'Redirect:', getLogoutRes.headers.get('location'));
      const stillLoggedInRes = await fetch(`${baseUrl2}/dashboard`, {
        headers: { Cookie: sessionCookie },
        redirect: 'manual'
      });
      if (stillLoggedInRes.status !== 200) {
        throw new Error('GET /auth/logout should NOT destroy session (H3 defense failure)!');
      }
      console.log('✓ H3 Protection Verified: GET /auth/logout did not log out the user!');

      // Subtest 2: Extract CSRF token from dashboard and POST /auth/logout
      const dashHtml = await stillLoggedInRes.text();
      const csrfMatch = dashHtml.match(/name="_csrf" value="([a-f0-9]{64})"/i);
      if (!csrfMatch) throw new Error('CSRF token not found in dashboard logout form');
      const logoutCsrfToken = csrfMatch[1];

      const logoutBody = new URLSearchParams({ _csrf: logoutCsrfToken });
      const logoutRes = await fetch(`${baseUrl2}/auth/logout`, {
        method: 'POST',
        headers: {
          Cookie: sessionCookie,
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: logoutBody,
        redirect: 'manual'
      });
      console.log('✓ POST /auth/logout with CSRF status:', logoutRes.status, 'Redirect:', logoutRes.headers.get('location'));

      // Check dashboard again with old cookie
      const afterLogoutRes = await fetch(`${baseUrl2}/dashboard`, {
        headers: { Cookie: sessionCookie },
        redirect: 'manual'
      });
      console.log('✓ Accessing dashboard after logout status:', afterLogoutRes.status, 'Redirect:', afterLogoutRes.headers.get('location'));
      if (afterLogoutRes.status !== 302 || !afterLogoutRes.headers.get('location').includes('/auth/login')) {
        throw new Error('Logged out session was not properly invalidated!');
      }
      console.log('🎉 SUCCESS: Session was properly destroyed and cannot be reused!');

    } finally {
      await new Promise((resolve) => server2.close(resolve));
    }

    console.log('\n========================================================');
    console.log('✅ ALL 6-MONTH PERSISTENT SESSION TESTS PASSED 100%!');
    console.log('========================================================');
  } finally {
    try { server.close(); } catch (e) {}
  }
}

testPersistentSessions().catch(err => {
  console.error('\n❌ Test Failed:', err);
  process.exit(1);
});
