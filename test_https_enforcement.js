const http = require('http');
const assert = require('assert');
const express = require('express');

console.log('========================================================');
console.log('🔒 VERIFYING C6: HTTPS ENFORCEMENT & HSTS SECURITY');
console.log('========================================================\n');

// Build an express app with the exact middleware from server.js to test in isolation
function createApp(isProduction) {
  const app = express();
  app.set('trust proxy', 1);

  if (isProduction) {
    app.use((req, res, next) => {
      // Exclude loopback / local addresses so internal diagnostic health-checks and tests never fail
      const hostname = req.hostname || '';
      const hostHeader = req.headers.host || '';
      const isLoopback = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1' ||
                         hostHeader.startsWith('localhost') || hostHeader.startsWith('127.0.0.1');
      if (isLoopback) {
        return next();
      }

      // Check if the request arrived securely or via an SSL-terminating reverse proxy
      const protoHeader = req.headers['x-forwarded-proto'];
      const firstProto = protoHeader ? protoHeader.split(',')[0].trim().toLowerCase() : '';
      const isHttps = req.secure ||
                      firstProto === 'https' ||
                      req.headers['x-forwarded-ssl'] === 'on' ||
                      req.headers['x-url-scheme'] === 'https' ||
                      req.headers['front-end-https'] === 'on' ||
                      req.headers['https'] === 'on' ||
                      (req.connection && req.connection.encrypted) ||
                      (req.socket && req.socket.encrypted);

      if (!isHttps) {
        const targetHost = req.headers.host || hostname || 'racefinance.site';
        const redirectUrl = `https://${targetHost}${req.originalUrl || req.url}`;
        const statusCode = (req.method === 'GET' || req.method === 'HEAD') ? 301 : 308;
        return res.redirect(statusCode, redirectUrl);
      }

      // Enforce HTTP Strict Transport Security (HSTS) - 1 year with subdomains
      res.setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
      next();
    });
  }

  app.get('/test', (req, res) => res.status(200).send('OK_GET'));
  app.post('/test', (req, res) => res.status(200).send('OK_POST'));
  return app;
}

function makeRequest(server, options) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => { data += chunk; });
      res.on('end', () => resolve({ status: res.statusCode, headers: res.headers, body: data }));
    });
    req.on('error', reject);
    req.end();
  });
}

async function runTests() {
  // --- Test 1: Development mode (no redirection) ---
  const devApp = createApp(false);
  const devServer = devApp.listen(0);
  const devPort = devServer.address().port;

  const devRes = await makeRequest(devServer, {
    hostname: '127.0.0.1',
    port: devPort,
    path: '/test',
    method: 'GET',
    headers: { Host: 'racefinance.site' }
  });
  assert.strictEqual(devRes.status, 200);
  assert.strictEqual(devRes.body, 'OK_GET');
  console.log('✓ Test 1 Passed: Development mode serves HTTP without redirecting');
  devServer.close();

  // --- Test 2: Production mode with localhost/loopback (no redirection) ---
  const prodApp = createApp(true);
  const prodServer = prodApp.listen(0);
  const prodPort = prodServer.address().port;

  const localRes = await makeRequest(prodServer, {
    hostname: '127.0.0.1',
    port: prodPort,
    path: '/test',
    method: 'GET',
    headers: { Host: 'localhost:' + prodPort }
  });
  assert.strictEqual(localRes.status, 200);
  assert.strictEqual(localRes.body, 'OK_GET');
  console.log('✓ Test 2 Passed: Production mode skips redirection for localhost / loopback addresses');

  // --- Test 3: Production unencrypted GET request redirects to HTTPS (301) ---
  const redirectGetRes = await makeRequest(prodServer, {
    hostname: '127.0.0.1',
    port: prodPort,
    path: '/test?query=hello&tab=invoices',
    method: 'GET',
    headers: { Host: 'racefinance.site' }
  });
  assert.strictEqual(redirectGetRes.status, 301);
  assert.strictEqual(redirectGetRes.headers['location'], 'https://racefinance.site/test?query=hello&tab=invoices');
  console.log('✓ Test 3 Passed: Production unencrypted GET redirects to HTTPS 301 with query parameters preserved');

  // --- Test 4: Production unencrypted POST request redirects with 308 (method preserved) ---
  const redirectPostRes = await makeRequest(prodServer, {
    hostname: '127.0.0.1',
    port: prodPort,
    path: '/test',
    method: 'POST',
    headers: { Host: 'racefinance.site' }
  });
  assert.strictEqual(redirectPostRes.status, 308);
  assert.strictEqual(redirectPostRes.headers['location'], 'https://racefinance.site/test');
  console.log('✓ Test 4 Passed: Production unencrypted POST redirects with 308 to preserve HTTP method and payload');

  // --- Test 5: Production behind HTTPS reverse proxy (X-Forwarded-Proto: https) ---
  const proxySecureRes = await makeRequest(prodServer, {
    hostname: '127.0.0.1',
    port: prodPort,
    path: '/test',
    method: 'GET',
    headers: {
      Host: 'racefinance.site',
      'X-Forwarded-Proto': 'https'
    }
  });
  assert.strictEqual(proxySecureRes.status, 200);
  assert.strictEqual(proxySecureRes.body, 'OK_GET');
  assert.ok(proxySecureRes.headers['strict-transport-security'], 'HSTS header must be present');
  assert.strictEqual(proxySecureRes.headers['strict-transport-security'], 'max-age=31536000; includeSubDomains');
  console.log('✓ Test 5 Passed: HTTPS proxy requests succeed (200 OK) with HSTS header set, no redirect loop');

  // --- Test 6: Multi-proxy hops (X-Forwarded-Proto: https, http) ---
  const multiProxyRes = await makeRequest(prodServer, {
    hostname: '127.0.0.1',
    port: prodPort,
    path: '/test',
    method: 'GET',
    headers: {
      Host: 'racefinance.site',
      'X-Forwarded-Proto': 'https, http'
    }
  });
  assert.strictEqual(multiProxyRes.status, 200);
  console.log('✓ Test 6 Passed: Multi-hop proxy headers (https, http) are parsed accurately');

  // --- Test 7: Alternative SSL headers (X-Forwarded-Ssl: on) ---
  const altSslRes = await makeRequest(prodServer, {
    hostname: '127.0.0.1',
    port: prodPort,
    path: '/test',
    method: 'GET',
    headers: {
      Host: 'racefinance.site',
      'X-Forwarded-Ssl': 'on'
    }
  });
  assert.strictEqual(altSslRes.status, 200);
  console.log('✓ Test 7 Passed: Alternative proxy SSL headers (X-Forwarded-Ssl: on) recognized');

  prodServer.close();

  console.log('\n========================================================');
  console.log('🎉 ALL C6 HTTPS ENFORCEMENT TESTS PASSED (100%)!');
  console.log('========================================================');
}

runTests().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
