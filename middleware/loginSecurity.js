/**
 * Smart Progressive CAPTCHA & Brute-Force Login Protection (H2)
 *
 * Implements a progressive security challenge:
 * - First 3 attempts: Clean, seamless login for humans.
 * - After 3 failed attempts: Triggers a dynamic server-side Math Challenge.
 * - Tracks by Session AND Client IP / Identifier to stop headless bots rotating sessions.
 */

const WINDOW_MS = 15 * 60 * 1000; // 15-minute sliding window
const MAX_FAILED_ATTEMPTS = 3;

// In-memory sliding trackers
const ipTracker = new Map();
const userTracker = new Map();

// Periodic cleanup of expired entries (every 10 minutes, unreferenced timer)
const cleanupTimer = setInterval(() => {
  const now = Date.now();
  for (const [key, record] of ipTracker.entries()) {
    if (now - record.lastAttempt > WINDOW_MS) {
      ipTracker.delete(key);
    }
  }
  for (const [key, record] of userTracker.entries()) {
    if (now - record.lastAttempt > WINDOW_MS) {
      userTracker.delete(key);
    }
  }
}, 10 * 60 * 1000);
if (cleanupTimer.unref) {
  cleanupTimer.unref();
}

function getClientIp(req) {
  return req.ip || (req.connection && req.connection.remoteAddress) || 'unknown';
}

function normalizeIdentifier(identifier) {
  return typeof identifier === 'string' ? identifier.trim().toLowerCase() : '';
}

/**
 * Generates a random math challenge and stores the question & answer in the session.
 */
function generateCaptcha(req) {
  const operators = ['+', '-', '×'];
  const op = operators[Math.floor(Math.random() * operators.length)];
  let num1, num2, answer;

  if (op === '+') {
    num1 = Math.floor(Math.random() * 15) + 3; // 3 to 17
    num2 = Math.floor(Math.random() * 15) + 2; // 2 to 16
    answer = num1 + num2;
  } else if (op === '-') {
    num1 = Math.floor(Math.random() * 15) + 10; // 10 to 24
    num2 = Math.floor(Math.random() * 9) + 1;   // 1 to 9
    answer = num1 - num2;
  } else {
    num1 = Math.floor(Math.random() * 8) + 2;  // 2 to 9
    num2 = Math.floor(Math.random() * 7) + 2;  // 2 to 8
    answer = num1 * num2;
  }

  const question = `What is ${num1} ${op} ${num2} = ?`;

  if (req && req.session) {
    req.session.captchaQuestion = question;
    req.session.captchaAnswer = String(answer);
  }

  return { question, answer: String(answer) };
}

/**
 * Checks whether CAPTCHA is required based on session, IP, or user identifier failures.
 */
function isCaptchaRequired(req, identifier) {
  if (req && req.session && (req.session.loginFailedAttempts || 0) >= MAX_FAILED_ATTEMPTS) {
    return true;
  }

  const ip = getClientIp(req);
  const ipRecord = ipTracker.get(ip);
  if (ipRecord && ipRecord.count >= MAX_FAILED_ATTEMPTS) {
    if (Date.now() - ipRecord.lastAttempt <= WINDOW_MS) {
      return true;
    }
  }

  const normId = normalizeIdentifier(identifier);
  if (normId) {
    const userRecord = userTracker.get(normId);
    if (userRecord && userRecord.count >= MAX_FAILED_ATTEMPTS) {
      if (Date.now() - userRecord.lastAttempt <= WINDOW_MS) {
        return true;
      }
    }
  }

  return false;
}

/**
 * Validates the submitted CAPTCHA against the session answer.
 */
function validateCaptcha(req, submittedAnswer) {
  if (!req || !req.session || !req.session.captchaAnswer) {
    return false;
  }

  const expected = String(req.session.captchaAnswer).trim();
  const given = String(submittedAnswer || '').trim();

  const isValid = given.length > 0 && given === expected;

  // Once attempted, immediately rotate or consume the captcha answer
  delete req.session.captchaAnswer;
  delete req.session.captchaQuestion;

  return isValid;
}

/**
 * Records a failed login attempt across Session, IP, and Identifier.
 */
function recordFailedAttempt(req, identifier) {
  const now = Date.now();

  // Session
  if (req && req.session) {
    req.session.loginFailedAttempts = (req.session.loginFailedAttempts || 0) + 1;
  }

  // IP
  const ip = getClientIp(req);
  const ipRecord = ipTracker.get(ip) || { count: 0, lastAttempt: now };
  if (now - ipRecord.lastAttempt > WINDOW_MS) {
    ipRecord.count = 1;
  } else {
    ipRecord.count += 1;
  }
  ipRecord.lastAttempt = now;
  ipTracker.set(ip, ipRecord);

  // Identifier (phone/email)
  const normId = normalizeIdentifier(identifier);
  if (normId) {
    const userRecord = userTracker.get(normId) || { count: 0, lastAttempt: now };
    if (now - userRecord.lastAttempt > WINDOW_MS) {
      userRecord.count = 1;
    } else {
      userRecord.count += 1;
    }
    userRecord.lastAttempt = now;
    userTracker.set(normId, userRecord);
  }

  // If threshold reached, ensure a captcha is prepared
  if (isCaptchaRequired(req, identifier)) {
    generateCaptcha(req);
  }
}

/**
 * Clears failed attempt tracking upon successful authentication.
 */
function clearFailedAttempts(req, identifier) {
  if (req && req.session) {
    delete req.session.loginFailedAttempts;
    delete req.session.captchaAnswer;
    delete req.session.captchaQuestion;
  }

  const ip = getClientIp(req);
  ipTracker.delete(ip);

  const normId = normalizeIdentifier(identifier);
  if (normId) {
    userTracker.delete(normId);
  }
}

module.exports = {
  MAX_FAILED_ATTEMPTS,
  WINDOW_MS,
  generateCaptcha,
  isCaptchaRequired,
  validateCaptcha,
  recordFailedAttempt,
  clearFailedAttempts
};
