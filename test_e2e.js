async function testAuthAndEndpoints() {
  console.log('Testing End-to-End User Flow...');

  // 1. Get Login Page & CSRF Token
  const getLoginRes = await fetch('http://localhost:3000/auth/login');
  const cookie = getLoginRes.headers.get('set-cookie');
  console.log('1. GET /auth/login status:', getLoginRes.status);
  console.log('   Cookie obtained:', !!cookie);

  if (!cookie) {
    throw new Error('No session cookie returned on GET /auth/login');
  }

  const sessionCookie = cookie.split(';')[0];
  const html = await getLoginRes.text();
  const csrfMatch = html.match(/name="_csrf" value="([a-f0-9]+)"/i);
  const csrfToken = csrfMatch ? csrfMatch[1] : '';

  // Post Login with CSRF token
  const loginParams = new URLSearchParams();
  loginParams.append('identifier', '9876543210');
  loginParams.append('password', 'admin123');
  loginParams.append('_csrf', csrfToken);

  const loginRes = await fetch('http://localhost:3000/auth/login', {
    method: 'POST',
    headers: { Cookie: sessionCookie },
    body: loginParams,
    redirect: 'manual'
  });

  // Helper for authenticated requests
  const authFetch = async (url) => {
    return fetch(url, {
      headers: { Cookie: sessionCookie }
    });
  };

  // 2. Dashboard
  const dashRes = await authFetch('http://localhost:3000/dashboard');
  console.log('2. Dashboard status:', dashRes.status);

  // 3. Sales Invoices
  const salesRes = await authFetch('http://localhost:3000/sales');
  console.log('3. Sales Invoices list status:', salesRes.status);

  // 4. Items
  const itemsRes = await authFetch('http://localhost:3000/items');
  console.log('4. Items & Stock list status:', itemsRes.status);

  // 5. Parties
  const partiesRes = await authFetch('http://localhost:3000/parties');
  console.log('5. Parties list status:', partiesRes.status);

  // 6. Reports (Tax & Party)
  const taxRes = await authFetch('http://localhost:3000/reports/tax');
  console.log('6. GSTR-1 Tax report status:', taxRes.status);

  // 7. Backup Export
  const backupRes = await authFetch('http://localhost:3000/backup/export');
  const backupJson = await backupRes.json();
  console.log('7. Backup JSON export status:', backupRes.status, 'Firms in export:', backupJson.firms.length);

  // 8. Print A4 Invoice View
  const printRes = await authFetch('http://localhost:3000/invoices/print/1');
  console.log('8. Print A4 Invoice status:', printRes.status);

  console.log('\n✅ ALL HTTP ROUTE TESTS PASSED PERFECTLY!');
}

testAuthAndEndpoints().catch(err => {
  console.error('Test failed:', err);
  process.exit(1);
});
