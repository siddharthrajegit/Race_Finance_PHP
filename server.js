require('dotenv').config();
const express = require('express');
const path = require('path');
const session = require('express-session');
const SqliteSessionStore = require('./config/sessionStore');
const flash = require('connect-flash');
const passport = require('./config/passport');
const { ensureAuthenticated, ensureAdmin, ensureUserOnly, ensureActiveFirm } = require('./middleware/auth');
const csrfProtection = require('./middleware/csrf');
const reportController = require('./controllers/reportController');

// Route modules
const authRoutes = require('./routes/authRoutes');
const firmRoutes = require('./routes/firmRoutes');
const itemRoutes = require('./routes/itemRoutes');
const partyRoutes = require('./routes/partyRoutes');
const invoiceRoutes = require('./routes/invoiceRoutes');
const paymentRoutes = require('./routes/paymentRoutes');
const reportRoutes = require('./routes/reportRoutes');
const backupRoutes = require('./routes/backupRoutes');
const settingRoutes = require('./routes/settingRoutes');
const adminRoutes = require('./routes/adminRoutes');
const legalRoutes = require('./routes/legalRoutes');
const { Admin, Firm } = require('./models');

const app = express();
const PORT = process.env.PORT || 3000;

const isProduction = process.env.NODE_ENV === 'production';

// Trust reverse proxy (cPanel / Apache / Passenger / Nginx / Cloud Load Balancers)
app.set('trust proxy', 1);

// Production HTTPS Enforcement & HSTS Security Header (Vulnerability C6)
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

// View engine setup
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Body parser
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(express.json({ limit: '10mb' }));

// Static files
app.use(express.static(path.join(__dirname, 'public')));

// Session configuration - 6-Month Persistent SQLite Store (180 Days)
const SIX_MONTHS_MS = 1000 * 60 * 60 * 24 * 180; // 180 days (~6 months)

let sessionSecret = process.env.SESSION_SECRET;
if (isProduction) {
  if (!sessionSecret || sessionSecret.length < 32 || sessionSecret.includes('secret_key')) {
    throw new Error('FATAL: A strong, unguessable SESSION_SECRET (at least 32 characters) must be set in your environment variables for production.');
  }
} else if (!sessionSecret) {
  const crypto = require('crypto');
  sessionSecret = crypto.randomBytes(32).toString('hex');
  console.warn('⚠️ [DEV WARNING] SESSION_SECRET not set in .env. Generated temporary development secret.');
}

app.use(
  session({
    store: new SqliteSessionStore({ ttl: SIX_MONTHS_MS }),
    secret: sessionSecret,
    resave: false,
    saveUninitialized: false,
    rolling: true,
    cookie: {
      maxAge: SIX_MONTHS_MS,
      httpOnly: true,
      secure: isProduction,
      sameSite: 'lax'
    }
  })
);

// Flash messages
app.use(flash());

// Passport middleware
app.use(passport.initialize());
app.use(passport.session());

// CSRF Protection middleware
app.use(csrfProtection);

// Global template variables middleware
app.use((req, res, next) => {
  res.locals.user = req.user || null;
  res.locals.isAdmin = req.user && req.user.role === 'admin';
  res.locals.success_msg = req.flash('success_msg');
  res.locals.error_msg = req.flash('error_msg');
  res.locals.info_msg = req.flash('info_msg');
  res.locals.currentUrl = req.originalUrl;
  res.locals.activeMenu = '';
  res.locals.activeFirm = null;
  res.locals.userFirms = [];
  if (req.user && req.user.role !== 'admin') {
    try {
      const userFirms = Firm.getByUserId(req.user.id);
      res.locals.userFirms = userFirms || [];
      let activeFirmId = req.session.activeFirmId;
      let activeFirm = null;
      if (activeFirmId && userFirms) {
        activeFirm = userFirms.find(f => f.id === parseInt(activeFirmId));
      }
      if (!activeFirm && userFirms && userFirms.length > 0) {
        activeFirm = userFirms.find(f => f.is_default === 1) || userFirms[0];
      }
      res.locals.activeFirm = activeFirm;
    } catch (err) {
      console.error('Error setting global firm variables:', err);
    }
  }
  try {
    res.locals.platformSettings = Admin.getAllPlatformSettings();
  } catch (e) {
    res.locals.platformSettings = {};
  }
  next();
});

// Routes
app.use('/admin', adminRoutes);
app.use('/auth', authRoutes);
app.use('/firms', firmRoutes);
app.use('/items', itemRoutes);
app.use('/parties', partyRoutes);
app.use('/payments', paymentRoutes);
app.use('/reports', reportRoutes);
app.use('/backup', backupRoutes);
app.use('/settings', settingRoutes);

// Legal & Public Policies
app.use('/legal', legalRoutes);
app.get('/about', (req, res) => res.redirect('/legal?tab=about'));
app.get('/pricing', (req, res) => res.redirect('/legal?tab=pricing'));
app.get('/contact', (req, res) => res.redirect('/legal?tab=contact'));
app.get('/terms', (req, res) => res.redirect('/legal?tab=terms'));
app.get('/privacy', (req, res) => res.redirect('/legal?tab=privacy'));
app.get('/refund-policy', (req, res) => res.redirect('/legal?tab=refund'));
app.get('/disclaimer', (req, res) => res.redirect('/legal?tab=disclaimer'));
app.get('/security', (req, res) => res.redirect('/legal?tab=security'));

// SEO Crawlers & Sitemaps
app.get('/robots.txt', (req, res) => {
  res.type('text/plain');
  res.sendFile(path.join(__dirname, 'public', 'robots.txt'));
});
app.get('/sitemap.xml', (req, res) => {
  res.type('application/xml');
  res.sendFile(path.join(__dirname, 'public', 'sitemap.xml'));
});

app.use('/', invoiceRoutes);

// Dashboard Route (Root redirect / Dashboard)
app.get('/dashboard', ensureUserOnly, ensureActiveFirm, reportController.getDashboard);
app.get('/', (req, res) => {
  if (req.isAuthenticated()) {
    if (req.user.role === 'admin') {
      res.redirect('/admin');
    } else {
      res.redirect('/dashboard');
    }
  } else {
    res.redirect('/auth/login');
  }
});

// 404 Handler
app.use((req, res) => {
  res.status(404).render('404', {
    title: '404 - Page Not Found',
    activeMenu: ''
  });
});

// General Error Handler
app.use((err, req, res, next) => {
  console.error('Server error:', err);
  res.status(500).render('error', {
    title: 'Error - Something went wrong',
    error: process.env.NODE_ENV === 'development' ? err : {},
    activeMenu: ''
  });
});

if (require.main === module) {
  app.listen(PORT, () => {
    console.log(`=======================================================`);
    console.log(`🚀 RACE FINANCE Server is running!`);
    console.log(`🌐 Local URL: http://localhost:${PORT}`);
    console.log(`=======================================================`);
  });
}

module.exports = app;
