const assert = require('assert');
const http = require('http');
const loginSecurity = require('./middleware/loginSecurity');

console.log('========================================================');
console.log('🔒 VERIFYING H2: SMART PROGRESSIVE CAPTCHA PROTECTION');
console.log('========================================================\n');

// 1. Test Unit Functions of loginSecurity
console.log('--- Test 1: Unit Testing Math Challenge Generation ---');
const fakeReq = { session: {} };
for (let i = 0; i < 20; i++) {
  const challenge = loginSecurity.generateCaptcha(fakeReq);
  assert.ok(challenge.question.startsWith('What is '));
  assert.ok(challenge.question.endsWith(' = ?'));
  assert.strictEqual(fakeReq.session.captchaQuestion, challenge.question);
  assert.strictEqual(fakeReq.session.captchaAnswer, challenge.answer);
  // Verify answer is integer
  const ansNum = parseInt(challenge.answer, 10);
  assert.ok(!isNaN(ansNum));
  assert.ok(ansNum >= 0);
}
console.log('✓ Test 1 Passed: Random math challenges generated with correct integers & session bindings');

// 2. Test Threshold Progression
console.log('\n--- Test 2: Testing 3-Attempt Progressive Threshold ---');
const sessionReq = {
  ip: '192.168.1.100',
  session: {}
};
const identifier = '9998887770';

// Initial state: not required
assert.strictEqual(loginSecurity.isCaptchaRequired(sessionReq, identifier), false);
console.log('✓ Attempt 0: CAPTCHA is NOT required');

// Fail 1
loginSecurity.recordFailedAttempt(sessionReq, identifier);
assert.strictEqual(loginSecurity.isCaptchaRequired(sessionReq, identifier), false);
console.log('✓ Attempt 1 (failed): CAPTCHA is NOT required');

// Fail 2
loginSecurity.recordFailedAttempt(sessionReq, identifier);
assert.strictEqual(loginSecurity.isCaptchaRequired(sessionReq, identifier), false);
console.log('✓ Attempt 2 (failed): CAPTCHA is NOT required');

// Fail 3
loginSecurity.recordFailedAttempt(sessionReq, identifier);
assert.strictEqual(loginSecurity.isCaptchaRequired(sessionReq, identifier), true);
assert.ok(sessionReq.session.captchaQuestion, 'Session must have captcha question prepared');
assert.ok(sessionReq.session.captchaAnswer, 'Session must have captcha answer prepared');
console.log(`✓ Attempt 3 (failed): CAPTCHA ACTIVATED! Question: "${sessionReq.session.captchaQuestion}"`);

// 3. Test CAPTCHA Validation
console.log('\n--- Test 3: Testing CAPTCHA Answer Validation ---');
const correctAnswer = sessionReq.session.captchaAnswer;

// Test with wrong answer
assert.strictEqual(loginSecurity.validateCaptcha(sessionReq, '999999'), false);
console.log('✓ Wrong answer rejected');

// Regenerate question for next attempt
loginSecurity.generateCaptcha(sessionReq);
const newCorrectAnswer = sessionReq.session.captchaAnswer;

// Test with correct answer
assert.strictEqual(loginSecurity.validateCaptcha(sessionReq, newCorrectAnswer), true);
console.log('✓ Correct answer accepted and consumed from session');

// Verify consumed
assert.strictEqual(sessionReq.session.captchaAnswer, undefined);

// 4. Test Clearing on Successful Login
console.log('\n--- Test 4: Testing State Reset on Success ---');
loginSecurity.clearFailedAttempts(sessionReq, identifier);
assert.strictEqual(loginSecurity.isCaptchaRequired(sessionReq, identifier), false);
assert.strictEqual(sessionReq.session.loginFailedAttempts, undefined);
console.log('✓ Failed attempts and CAPTCHA state completely cleared on success');

// 5. Test IP-Level Protection Against Headless Cookie-Dropping Bots
console.log('\n--- Test 5: Testing Headless Bot Defense (Rotating Cookies) ---');
const botIp = '203.0.113.42';

// 3 requests from bot without cookies (different empty sessions)
for (let i = 1; i <= 3; i++) {
  const botReq = { ip: botIp, session: {} };
  loginSecurity.recordFailedAttempt(botReq, 'admin@racefinance.com');
}

// 4th request: bot starts a fresh session without cookies
const freshBotReq = { ip: botIp, session: {} };
assert.strictEqual(loginSecurity.isCaptchaRequired(freshBotReq, 'admin@racefinance.com'), true);
console.log('✓ Bot rotating/dropping session cookies is STILL forced to solve CAPTCHA by IP tracker!');

// Cleanup
loginSecurity.clearFailedAttempts({ ip: botIp, session: {} }, 'admin@racefinance.com');

console.log('\n========================================================');
console.log('🎉 ALL H2 SMART PROGRESSIVE CAPTCHA TESTS PASSED (100%)!');
console.log('========================================================');
