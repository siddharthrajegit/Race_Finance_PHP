<?php
/**
 * Application Front Controller & Router Dispatcher
 * RACE FINANCE - Small Business Billing & Inventory System (PHP Edition)
 */

// If running with PHP's built-in development server, serve static files directly from public/
if (php_sapi_name() === 'cli-server') {
    $reqUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $staticPath = __DIR__ . '/public' . $reqUri;
    if ($reqUri !== '/' && file_exists($staticPath) && is_file($staticPath)) {
        $mimes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'ico' => 'image/x-icon',
            'json' => 'application/json'
        ];
        $ext = strtolower(pathinfo($staticPath, PATHINFO_EXTENSION));
        $mime = $mimes[$ext] ?? mime_content_type($staticPath);
        header("Content-Type: $mime");
        readfile($staticPath);
        exit;
    }
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Auth.php';

// Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/FirmController.php';
require_once __DIR__ . '/controllers/ItemController.php';
require_once __DIR__ . '/controllers/PartyController.php';
require_once __DIR__ . '/controllers/InvoiceController.php';
require_once __DIR__ . '/controllers/PaymentController.php';
require_once __DIR__ . '/controllers/ReportController.php';
require_once __DIR__ . '/controllers/SettingController.php';
require_once __DIR__ . '/controllers/BackupController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/LegalController.php';

// -----------------------------------------------------------------------------
// Root & Dashboard
// -----------------------------------------------------------------------------
Router::get('/', function() {
    if (Auth::isAuthenticated()) {
        header('Location: ' . (Auth::isAdmin() ? '/admin' : '/dashboard'));
        exit;
    }
    header('Location: /auth/login');
    exit;
});

Router::get('/dashboard', [ReportController::class, 'getDashboard']);

// -----------------------------------------------------------------------------
// Authentication Routes
// -----------------------------------------------------------------------------
Router::get('/auth/login', [AuthController::class, 'getLogin']);
Router::post('/auth/login', [AuthController::class, 'postLogin']);
Router::get('/auth/register', [AuthController::class, 'getRegister']);
Router::post('/auth/register', [AuthController::class, 'postRegister']);
Router::get('/auth/google', [AuthController::class, 'googleAuth']);
Router::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
Router::get('/auth/logout', [AuthController::class, 'logout']);
Router::post('/auth/logout', [AuthController::class, 'logout']);

// -----------------------------------------------------------------------------
// Firm / Business Routes
// -----------------------------------------------------------------------------
Router::get('/firms', [FirmController::class, 'listFirms']);
Router::get('/firms/create', [FirmController::class, 'getCreate']);
Router::post('/firms/create', [FirmController::class, 'postCreate']);
Router::get('/firms/edit/:id', [FirmController::class, 'getEdit']);
Router::post('/firms/edit/:id', [FirmController::class, 'postEdit']);
Router::post('/firms/switch/:id', [FirmController::class, 'postSwitch']);
Router::post('/firms/default/:id', [FirmController::class, 'postSetDefault']);
Router::post('/firms/delete/:id', [FirmController::class, 'postDelete']);

// -----------------------------------------------------------------------------
// Items & Inventory Routes
// -----------------------------------------------------------------------------
Router::get('/items', [ItemController::class, 'listItems']);
Router::get('/items/create', [ItemController::class, 'getCreate']);
Router::post('/items/create', [ItemController::class, 'postCreate']);
Router::post('/items/quick-create', [ItemController::class, 'postQuickCreate']);
Router::get('/items/edit/:id', [ItemController::class, 'getEdit']);
Router::post('/items/edit/:id', [ItemController::class, 'postEdit']);
Router::post('/items/adjust/:id', [ItemController::class, 'postAdjustStock']);
Router::post('/items/delete/:id', [ItemController::class, 'postDelete']);
Router::get('/items/api/search', [ItemController::class, 'apiGetItems']);

// -----------------------------------------------------------------------------
// Parties (Customers & Suppliers) Routes
// -----------------------------------------------------------------------------
Router::get('/parties', [PartyController::class, 'listParties']);
Router::get('/parties/create', [PartyController::class, 'getCreate']);
Router::post('/parties/create', [PartyController::class, 'postCreate']);
Router::post('/parties/quick-create', [PartyController::class, 'postQuickCreate']);
Router::get('/parties/edit/:id', [PartyController::class, 'getEdit']);
Router::post('/parties/edit/:id', [PartyController::class, 'postEdit']);
Router::get('/parties/ledger/:id', [PartyController::class, 'getLedger']);
Router::post('/parties/delete/:id', [PartyController::class, 'postDelete']);
Router::get('/parties/api/search', [PartyController::class, 'apiGetParties']);

// -----------------------------------------------------------------------------
// Invoices (Sales & Purchases) Routes
// -----------------------------------------------------------------------------
Router::get('/sales', [InvoiceController::class, 'listSales']);
Router::get('/sales/create', [InvoiceController::class, 'getCreateSale']);
Router::get('/purchases', [InvoiceController::class, 'listPurchases']);
Router::get('/purchases/create', [InvoiceController::class, 'getCreatePurchase']);
Router::post('/invoices/create', [InvoiceController::class, 'postCreate']);
Router::get('/invoices/edit/:id', [InvoiceController::class, 'getEdit']);
Router::post('/invoices/edit/:id', [InvoiceController::class, 'postEdit']);
Router::get('/invoices/view/:id', [InvoiceController::class, 'getView']);
Router::get('/invoices/download/:id', [InvoiceController::class, 'getDownload']);
Router::get('/invoices/print/:id', [InvoiceController::class, 'getPrintA4']);
Router::post('/invoices/delete/:id', [InvoiceController::class, 'postDelete']);

// -----------------------------------------------------------------------------
// Payments Routes
// -----------------------------------------------------------------------------
Router::get('/payments', [PaymentController::class, 'listPayments']);
Router::get('/payments/create', [PaymentController::class, 'getCreate']);
Router::post('/payments/create', [PaymentController::class, 'postCreate']);
Router::get('/payments/view/:id', [PaymentController::class, 'getView']);
Router::post('/payments/delete/:id', [PaymentController::class, 'postDelete']);

// -----------------------------------------------------------------------------
// Reports Routes
// -----------------------------------------------------------------------------
Router::get('/reports', [ReportController::class, 'getReportsIndex']);
Router::get('/reports/parties', [ReportController::class, 'getPartyReport']);
Router::get('/reports/tax', [ReportController::class, 'getTaxReport']);
Router::get('/reports/items', [ReportController::class, 'getItemReport']);

// -----------------------------------------------------------------------------
// Backup Routes
// -----------------------------------------------------------------------------
Router::get('/backup', [BackupController::class, 'getIndex']);
Router::get('/backup/export', [BackupController::class, 'exportJson']);
Router::post('/backup/restore', [BackupController::class, 'restoreJson']);
Router::post('/backup/google-drive', [BackupController::class, 'uploadGoogleDrive']);

// -----------------------------------------------------------------------------
// Settings Routes
// -----------------------------------------------------------------------------
Router::get('/settings', [SettingController::class, 'getSettings']);
Router::post('/settings', [SettingController::class, 'postSettings']);

// -----------------------------------------------------------------------------
// Admin Platform Management Routes
// -----------------------------------------------------------------------------
Router::get('/admin', [AdminController::class, 'getDashboard']);
Router::get('/admin/dashboard', [AdminController::class, 'getDashboard']);
Router::get('/admin/users', [AdminController::class, 'getUsers']);
Router::post('/admin/users/create', [AdminController::class, 'postCreateUser']);
Router::get('/admin/users/:id', [AdminController::class, 'getUserDetails']);
Router::post('/admin/users/:id/renew', [AdminController::class, 'postRenewSubscription']);
Router::post('/admin/users/:id/role', [AdminController::class, 'postToggleUserRole']);
Router::post('/admin/users/:id/status', [AdminController::class, 'postToggleUserStatus']);
Router::post('/admin/users/:id/reset-password', [AdminController::class, 'postResetUserPassword']);
Router::post('/admin/users/:id/delete', [AdminController::class, 'postDeleteUser']);
Router::get('/admin/firms', [AdminController::class, 'getFirms']);
Router::get('/admin/invoices', [AdminController::class, 'getInvoices']);
Router::get('/admin/system', [AdminController::class, 'getSystemHealth']);
Router::post('/admin/system/vacuum', [AdminController::class, 'postVacuumDb']);
Router::post('/admin/system/clean-orphans', [AdminController::class, 'postCleanOrphans']);
Router::get('/admin/system/download-db', [AdminController::class, 'getDownloadDb']);
Router::post('/admin/system/download-db', [AdminController::class, 'postDownloadDb']);
Router::get('/admin/settings', [AdminController::class, 'getSettings']);
Router::post('/admin/settings', [AdminController::class, 'postSettings']);

// -----------------------------------------------------------------------------
// Legal & Public Policies
// -----------------------------------------------------------------------------
Router::get('/about', [LegalController::class, 'getAbout']);
Router::get('/pricing', [LegalController::class, 'getPricing']);
Router::get('/contact', [LegalController::class, 'getContact']);
Router::get('/terms', [LegalController::class, 'getTerms']);
Router::get('/privacy', [LegalController::class, 'getPrivacy']);
Router::get('/refund-policy', [LegalController::class, 'getRefund']);
Router::get('/disclaimer', [LegalController::class, 'getDisclaimer']);
Router::get('/security', [LegalController::class, 'getSecurity']);
Router::get('/legal', [LegalController::class, 'getLegalPage']);
Router::get('/legal/:section', [LegalController::class, 'getLegalPage']);

// -----------------------------------------------------------------------------
// SEO Crawlers & Sitemaps
// -----------------------------------------------------------------------------
Router::get('/robots.txt', function() {
    $host = $_SERVER['HTTP_HOST'] ?? 'racefinance.site';
    $base = APP_URL;
    header('Content-Type: text/plain');
    echo "# Robots.txt for {$host} ({$base})\nUser-agent: *\nDisallow: /admin/\nDisallow: /invoices/\nDisallow: /purchases/\nDisallow: /sales/\nDisallow: /parties/\nDisallow: /items/\nDisallow: /reports/\nDisallow: /settings/\nDisallow: /firms/\nDisallow: /backup/\nDisallow: /dashboard\nAllow: /\nAllow: /about\nAllow: /pricing\nAllow: /contact\nAllow: /terms\nAllow: /privacy\nAllow: /refund-policy\nAllow: /disclaimer\nAllow: /security\nAllow: /auth/login\n\nSitemap: {$base}/sitemap.xml\n";
    exit;
});

Router::get('/sitemap.xml', function() {
    $base = APP_URL;
    $today = date('Y-m-d');
    $pages = ['', '/about', '/pricing', '/contact', '/auth/login', '/terms', '/privacy', '/refund-policy', '/disclaimer', '/security'];
    header('Content-Type: application/xml');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($pages as $p) {
        $freq = ($p === '') ? 'weekly' : 'monthly';
        $prio = ($p === '') ? '1.0' : '0.8';
        echo "  <url>\n    <loc>{$base}{$p}</loc>\n    <lastmod>{$today}</lastmod>\n    <changefreq>{$freq}</changefreq>\n    <priority>{$prio}</priority>\n  </url>\n";
    }
    echo "</urlset>";
    exit;
});

// Dispatch the current request
Router::dispatch();
