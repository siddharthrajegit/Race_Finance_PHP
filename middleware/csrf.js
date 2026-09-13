const crypto = require('crypto');

/**
 * CSRF Protection Middleware
 * - Uses session to store a secret/token
 * - Exposes res.locals.csrfToken to all EJS templates
 * - Validates POST/PUT/DELETE/PATCH against req.body._csrf, req.query._csrf, or X-CSRF-Token / csrf-token headers
 * - Timing-safe comparison to prevent timing attacks
 * - Handles both standard forms (redirect with flash) and AJAX/JSON (403 JSON)
 */
function csrfProtection(req, res, next) {
  if (!req.session) {
    return next(new Error('Session middleware is required for CSRF protection'));
  }

  // Generate or retrieve existing CSRF token for this session
  if (!req.session.csrfToken) {
    req.session.csrfToken = crypto.randomBytes(32).toString('hex');
  }

  // Make token available to all templates via res.locals
  res.locals.csrfToken = req.session.csrfToken;

  // Safe HTTP methods do not mutate state
  const safeMethods = ['GET', 'HEAD', 'OPTIONS'];
  if (safeMethods.includes(req.method)) {
    return next();
  }

  // Extract token from body, query string, or headers
  const token =
    (req.body && req.body._csrf) ||
    (req.query && req.query._csrf) ||
    req.headers['x-csrf-token'] ||
    req.headers['csrf-token'];

  const sessionToken = req.session.csrfToken;

  // Check if token exists and matches
  let isValid = false;
  if (typeof token === 'string' && typeof sessionToken === 'string') {
    try {
      const tokenBuf = Buffer.from(token, 'utf8');
      const sessionBuf = Buffer.from(sessionToken, 'utf8');
      if (tokenBuf.length === sessionBuf.length) {
        isValid = crypto.timingSafeEqual(tokenBuf, sessionBuf);
      }
    } catch (e) {
      isValid = false;
    }
  }

  if (isValid) {
    return next();
  }

  // Handle invalid or missing CSRF token
  const isAjax =
    req.xhr ||
    (req.headers.accept && req.headers.accept.includes('application/json')) ||
    (req.headers['content-type'] && req.headers['content-type'].includes('application/json'));

  if (isAjax) {
    return res.status(403).json({
      success: false,
      error: 'Invalid or missing CSRF security token. Please refresh the page and try again.'
    });
  }

  // Form submission: flash error and redirect back to previous page or dashboard safely
  if (typeof req.flash === 'function') {
    req.flash('error_msg', 'Your session or security token was invalid/expired. Please try again.');
  }

  let returnUrl = '/';
  const referer = req.header('Referer');
  if (referer) {
    try {
      const parsedReferer = new URL(referer);
      const host = req.header('host');
      if (parsedReferer.host === host) {
        returnUrl = parsedReferer.pathname + parsedReferer.search + parsedReferer.hash;
      }
    } catch (e) {
      returnUrl = '/';
    }
  }
  return res.redirect(returnUrl);
}

module.exports = csrfProtection;
