const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');

router.get('/login', authController.getLogin);
router.post('/login', authController.postLogin);

router.get('/register', authController.getRegister);
router.post('/register', authController.postRegister);

router.get('/google', authController.googleAuth);
router.get('/google/callback', authController.googleCallback);

// Sign out requires POST with CSRF token to prevent CSRF Logout attacks (H3)
router.post('/logout', authController.logout);

// Safe fallback for GET /logout: does NOT destroy session, redirecting safely
router.get('/logout', (req, res) => {
  if (req.isAuthenticated()) {
    req.flash('info_msg', 'To sign out safely, please click Sign Out from the account menu.');
    return res.redirect(req.user.role === 'admin' ? '/admin' : '/dashboard');
  }
  res.redirect('/auth/login');
});

module.exports = router;
