const bcrypt = require('bcryptjs');
const passport = require('passport');
const { User } = require('../models');
const loginSecurity = require('../middleware/loginSecurity');

/**
 * Validates and sanitizes post-login redirect targets to prevent Open Redirect vulnerabilities (C7).
 * Ensures destination is strictly a safe internal relative path.
 */
function getSafeRedirectUrl(targetUrl, defaultRedirect, userRole) {
  if (!targetUrl || typeof targetUrl !== 'string') {
    return defaultRedirect;
  }

  const trimmed = targetUrl.trim();

  // Must begin with a single '/', cannot be protocol-relative ('//') or contain backslashes ('\')
  if (
    !trimmed.startsWith('/') ||
    trimmed.startsWith('//') ||
    trimmed.startsWith('/\\') ||
    trimmed.includes('\\') ||
    /^\/[/\\]/.test(trimmed) ||
    /[\u0000-\u001F\u007F-\u009F]/.test(trimmed)
  ) {
    return defaultRedirect;
  }

  try {
    const parsed = new URL(trimmed, 'https://racefinance.site');
    // Ensure origin matches and pathname is a valid single-slash relative path
    if (parsed.origin !== 'https://racefinance.site' || !parsed.pathname.startsWith('/') || parsed.pathname.startsWith('//')) {
      return defaultRedirect;
    }

    const safePath = parsed.pathname + parsed.search + parsed.hash;

    // Prevent non-admin users from being redirected to /admin
    if (userRole && userRole !== 'admin' && safePath.startsWith('/admin')) {
      return defaultRedirect;
    }

    return safePath;
  } catch (e) {
    return defaultRedirect;
  }
}

const authController = {
  getLogin: (req, res) => {
    if (req.isAuthenticated()) {
      return res.redirect(req.user.role === 'admin' ? '/admin' : '/dashboard');
    }
    if (req.query.logged_out) {
      res.locals.success_msg = ['You have been logged out successfully.'];
    }
    const hasGoogleAuth = !!(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET && process.env.GOOGLE_CLIENT_ID !== 'your_google_client_id_here');

    // Smart Progressive CAPTCHA: Activate after 3 failed attempts
    const showCaptcha = loginSecurity.isCaptchaRequired(req);
    let captchaQuestion = null;
    if (showCaptcha) {
      if (!req.session.captchaQuestion || !req.session.captchaAnswer) {
        loginSecurity.generateCaptcha(req);
      }
      captchaQuestion = req.session.captchaQuestion;
    }

    res.render('auth/login', {
      title: 'Sign In - RACE FINANCE',
      hasGoogleAuth,
      showCaptcha,
      captchaQuestion
    });
  },

  postLogin: (req, res, next) => {
    const identifier = req.body.identifier;
    const captchaRequired = loginSecurity.isCaptchaRequired(req, identifier);

    if (captchaRequired) {
      const captchaInput = req.body.captcha;
      const isCaptchaValid = loginSecurity.validateCaptcha(req, captchaInput);

      if (!isCaptchaValid) {
        loginSecurity.recordFailedAttempt(req, identifier);
        req.flash('error_msg', 'Incorrect security verification answer. Please solve the math challenge to proceed.');
        return res.redirect('/auth/login');
      }
    }

    passport.authenticate('local', (err, user, info) => {
      if (err) return next(err);
      if (!user) {
        loginSecurity.recordFailedAttempt(req, identifier);
        req.flash('error_msg', info ? info.message : 'Invalid login credentials.');
        return res.redirect('/auth/login');
      }
      req.logIn(user, (err) => {
        if (err) return next(err);
        loginSecurity.clearFailedAttempts(req, identifier);
        req.flash('success_msg', `Welcome back, ${user.name}!`);
        const defaultRedirect = user.role === 'admin' ? '/admin' : '/dashboard';
        const redirectUrl = getSafeRedirectUrl(req.session.returnTo, defaultRedirect, user.role);
        delete req.session.returnTo;
        res.redirect(redirectUrl);
      });
    })(req, res, next);
  },

  getRegister: (req, res) => {
    req.flash('info_msg', 'Direct online registration is disabled. Accounts are manually provisioned by the administrator. Please contact support on WhatsApp to request your trial credentials.');
    res.redirect('/auth/login');
  },

  postRegister: (req, res) => {
    req.flash('error_msg', 'Direct online registration is disabled. Please contact support on WhatsApp to request access.');
    res.redirect('/auth/login');
  },

  googleAuth: (req, res, next) => {
    if (!process.env.GOOGLE_CLIENT_ID || process.env.GOOGLE_CLIENT_ID === 'your_google_client_id_here') {
      req.flash('error_msg', 'Google OAuth is not configured yet. Please use Phone/Email login or add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to .env');
      return res.redirect('/auth/login');
    }
    passport.authenticate('google', {
      scope: ['profile', 'email', 'https://www.googleapis.com/auth/drive.file'],
      accessType: 'offline',
      prompt: 'consent'
    })(req, res, next);
  },

  googleCallback: (req, res, next) => {
    passport.authenticate('google', {
      failureRedirect: '/auth/login',
      failureFlash: 'Google sign-in failed or was cancelled.'
    })(req, res, () => {
      loginSecurity.clearFailedAttempts(req, req.user && req.user.email);
      req.flash('success_msg', `Signed in with Google successfully!`);
      const defaultRedirect = req.user.role === 'admin' ? '/admin' : '/dashboard';
      const redirectUrl = getSafeRedirectUrl(req.session.returnTo, defaultRedirect, req.user.role);
      delete req.session.returnTo;
      res.redirect(redirectUrl);
    });
  },

  logout: (req, res, next) => {
    req.logout((err) => {
      if (err) return next(err);
      if (req.session) {
        req.session.destroy((destroyErr) => {
          if (destroyErr) {
            console.error('Session destroy error on logout:', destroyErr);
          }
          res.clearCookie('connect.sid');
          res.redirect('/auth/login?logged_out=1');
        });
      } else {
        res.clearCookie('connect.sid');
        res.redirect('/auth/login?logged_out=1');
      }
    });
  }
};

module.exports = authController;
