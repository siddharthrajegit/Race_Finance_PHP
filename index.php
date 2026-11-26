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
require_once __DIR__ . '/controllers/HomeController.php';
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
require_once __DIR__ . '/controllers/UnitController.php';
require_once __DIR__ . '/controllers/EstimateController.php';
require_once __DIR__ . '/controllers/WarehouseController.php';
require_once __DIR__ . '/controllers/ExpenseController.php';
require_once __DIR__ . '/controllers/StaffController.php';

// -----------------------------------------------------------------------------
// Root & Dashboard
// -----------------------------------------------------------------------------
Router::get('/', [HomeController::class, 'index']);

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
// Measurement Units API Routes (User-Scoped Custom Units)
// -----------------------------------------------------------------------------
Router::get('/api/units', [UnitController::class, 'apiGetUnits']);
Router::post('/api/units/create', [UnitController::class, 'postCreate']);
Router::post('/api/units/delete/:id', [UnitController::class, 'postDelete']);

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
Router::get('/invoices/bulk-print', [InvoiceController::class, 'getBulkPrint']);
Router::post('/invoices/create', [InvoiceController::class, 'postCreate']);
Router::get('/invoices/edit/:id', [InvoiceController::class, 'getEdit']);
Router::post('/invoices/edit/:id', [InvoiceController::class, 'postEdit']);
Router::get('/invoices/view/:id', [InvoiceController::class, 'getView']);
Router::get('/invoices/download/:id', [InvoiceController::class, 'getDownload']);
Router::get('/invoices/print/:id', [InvoiceController::class, 'getPrintA4']);
Router::post('/invoices/delete/:id', [InvoiceController::class, 'postDelete']);

// -----------------------------------------------------------------------------
// Estimates & Rate List Routes
// -----------------------------------------------------------------------------
Router::get('/estimates', [EstimateController::class, 'listEstimates']);
Router::get('/estimates/builder', [EstimateController::class, 'getBuilder']);
Router::get('/rate-list', [EstimateController::class, 'getBuilder']);
Router::post('/estimates/save', [EstimateController::class, 'postSave']);
Router::get('/estimates/view/:id', [EstimateController::class, 'getView']);
Router::get('/estimates/print/:id', [EstimateController::class, 'getPrint']);
Router::post('/estimates/print-direct', [EstimateController::class, 'postPrintDirect']);
Router::post('/estimates/delete/:id', [EstimateController::class, 'postDelete']);
Router::get('/api/items/by-category', [EstimateController::class, 'apiItemsByCategory']);

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
// Warehouses, Transfers & Stock Valuation Routes
// -----------------------------------------------------------------------------
Router::get('/warehouses', [WarehouseController::class, 'listWarehouses']);
Router::get('/warehouses/create', [WarehouseController::class, 'getCreate']);
Router::post('/warehouses/create', [WarehouseController::class, 'postCreate']);
Router::get('/warehouses/edit/:id', [WarehouseController::class, 'getEdit']);
Router::post('/warehouses/edit/:id', [WarehouseController::class, 'postEdit']);
Router::post('/warehouses/delete/:id', [WarehouseController::class, 'postDelete']);
Router::get('/warehouses/transfers', [WarehouseController::class, 'listTransfers']);
Router::get('/warehouses/transfers/create', [WarehouseController::class, 'getCreateTransfer']);
Router::post('/warehouses/transfers/create', [WarehouseController::class, 'postCreateTransfer']);
Router::get('/warehouses/valuation', [WarehouseController::class, 'getValuation']);
Router::get('/api/warehouses/item/:id', [WarehouseController::class, 'apiItemBreakdown']);

// -----------------------------------------------------------------------------
// Business Expense Routes
// -----------------------------------------------------------------------------
Router::get('/expenses', [ExpenseController::class, 'listExpenses']);
Router::get('/expenses/create', [ExpenseController::class, 'getCreate']);
Router::post('/expenses/create', [ExpenseController::class, 'postCreate']);
Router::get('/expenses/edit/:id', [ExpenseController::class, 'getEdit']);
Router::post('/expenses/edit/:id', [ExpenseController::class, 'postEdit']);
Router::post('/expenses/delete/:id', [ExpenseController::class, 'postDelete']);

// -----------------------------------------------------------------------------
// Staff, RBAC & Audit Log Routes
// -----------------------------------------------------------------------------
Router::get('/staff', [StaffController::class, 'listStaff']);
Router::get('/staff/create', [StaffController::class, 'getCreate']);
Router::post('/staff/create', [StaffController::class, 'postCreate']);
Router::get('/staff/edit/:id', [StaffController::class, 'getEdit']);
Router::post('/staff/edit/:id', [StaffController::class, 'postEdit']);
Router::post('/staff/delete/:id', [StaffController::class, 'postDelete']);
Router::get('/audit-logs', [StaffController::class, 'listAuditLogs']);

// -----------------------------------------------------------------------------
// Double-Entry Accounting & Bank Reconciliation Routes
// -----------------------------------------------------------------------------
Router::get('/accounting', [AccountingController::class, 'index']);
Router::post('/accounting/accounts/create', [AccountingController::class, 'postCreateAccount']);
Router::post('/accounting/backfill', [AccountingController::class, 'postBackfill']);
Router::get('/accounting/journals', [AccountingController::class, 'listJournals']);
Router::get('/accounting/journals/create', [AccountingController::class, 'getCreateJournal']);
Router::post('/accounting/journals/create', [AccountingController::class, 'postCreateJournal']);
Router::post('/accounting/journals/reverse/:id', [AccountingController::class, 'postReverseJournal']);
Router::get('/accounting/trial-balance', [AccountingController::class, 'getTrialBalance']);
Router::get('/accounting/profit-loss', [AccountingController::class, 'getProfitAndLoss']);
Router::get('/accounting/balance-sheet', [AccountingController::class, 'getBalanceSheet']);
Router::get('/accounting/cash-flow', [AccountingController::class, 'getCashFlow']);
Router::get('/accounting/reconciliation', [AccountingController::class, 'listBankReconciliation']);
Router::post('/accounting/reconciliation/import', [AccountingController::class, 'postImportCsv']);
Router::post('/accounting/reconciliation/manual-line', [AccountingController::class, 'postAddManualLine']);
Router::post('/accounting/reconciliation/match', [AccountingController::class, 'postMatchLine']);
Router::post('/accounting/reconciliation/status', [AccountingController::class, 'postSetLineStatus']);
Router::get('/api/accounting/reconcile-candidates/:id', [AccountingController::class, 'apiCandidates']);

// -----------------------------------------------------------------------------
// Thermal Receipt, Dynamic UPI, Barcode, Catalogue & Loyalty Routes
// -----------------------------------------------------------------------------
Router::get('/invoices/receipt/:id', [InvoiceController::class, 'getPrintReceipt']);
Router::get('/barcodes', [BarcodeController::class, 'studio']);
Router::get('/api/barcodes/lookup', [BarcodeController::class, 'apiLookup']);
Router::get('/catalogue', [CatalogueController::class, 'manage']);
Router::post('/catalogue', [CatalogueController::class, 'postUpdate']);
Router::get('/c/:slug', [CatalogueController::class, 'publicView']);
Router::get('/loyalty', [LoyaltyController::class, 'index']);
Router::post('/loyalty/program', [LoyaltyController::class, 'postSaveProgram']);
Router::post('/loyalty/adjust', [LoyaltyController::class, 'postAdjustPoints']);

// -----------------------------------------------------------------------------
// Batches & Expiry (FEFO), Serial / IMEI, and Manufacturing BOM Routes
// -----------------------------------------------------------------------------
Router::get('/batches', [InventoryAdvancedController::class, 'listBatches']);
Router::post('/batches/create', [InventoryAdvancedController::class, 'postCreateBatch']);
Router::get('/serials', [InventoryAdvancedController::class, 'listSerials']);
Router::post('/serials/register', [InventoryAdvancedController::class, 'postRegisterSerials']);
Router::post('/serials/status', [InventoryAdvancedController::class, 'postUpdateSerialStatus']);
Router::get('/bom', [InventoryAdvancedController::class, 'listBoms']);
Router::post('/bom/save', [InventoryAdvancedController::class, 'postSaveBom']);
Router::post('/bom/delete/:id', [InventoryAdvancedController::class, 'postDeleteBom']);
Router::post('/bom/produce', [InventoryAdvancedController::class, 'postExecuteProduction']);

// -----------------------------------------------------------------------------
// Offline PWA Sync API
// -----------------------------------------------------------------------------
Router::post('/api/invoices/sync', [InvoiceController::class, 'apiSyncOfflineInvoice']);

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
Router::get('/admin/invoices/:id', [AdminController::class, 'getInspectInvoice']);
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
Router::get('/free-trial', [LegalController::class, 'getFreeTrial']);
Router::get('/about', [LegalController::class, 'getAbout']);
Router::get('/pricing', [LegalController::class, 'getPricing']);
Router::get('/contact', [LegalController::class, 'getContact']);
Router::get('/terms', [LegalController::class, 'getTerms']);
Router::get('/privacy', [LegalController::class, 'getPrivacy']);
Router::get('/refund-policy', [LegalController::class, 'getRefund']);
Router::get('/refund', function() {
    header('Location: /refund-policy', true, 301);
    exit;
});
Router::get('/disclaimer', [LegalController::class, 'getDisclaimer']);
Router::get('/security', [LegalController::class, 'getSecurity']);
Router::get('/legal', [LegalController::class, 'getLegalPage']);
Router::get('/legal/:section', [LegalController::class, 'getLegalPage']);

// -----------------------------------------------------------------------------
// SEO Crawlers & Sitemaps
// -----------------------------------------------------------------------------
Router::get('/robots.txt', function() {
    $host = $_SERVER['HTTP_HOST'] ?? 'racefinance.site';
    $base = rtrim(APP_URL, '/');
    header('Content-Type: text/plain');
    echo "# Robots.txt for {$host} ({$base})\n";
    echo "User-agent: *\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /invoices/\n";
    echo "Disallow: /purchases/\n";
    echo "Disallow: /sales/\n";
    echo "Disallow: /parties/\n";
    echo "Disallow: /items/\n";
    echo "Disallow: /reports/\n";
    echo "Disallow: /settings/\n";
    echo "Disallow: /firms/\n";
    echo "Disallow: /backup/\n";
    echo "Disallow: /dashboard\n";
    echo "Allow: /\n";
    echo "Allow: /free-trial\n";
    echo "Allow: /about\n";
    echo "Allow: /pricing\n";
    echo "Allow: /contact\n";
    echo "Allow: /terms\n";
    echo "Allow: /privacy\n";
    echo "Allow: /refund-policy\n";
    echo "Allow: /disclaimer\n";
    echo "Allow: /security\n";
    echo "Allow: /auth/login\n\n";
    echo "Sitemap: {$base}/sitemap.xml\n";
    exit;
});

Router::get('/sitemap.xml', function() {
    $base = rtrim(APP_URL, '/');
    $today = date('Y-m-d');
    $pages = [
        ['loc' => '', 'freq' => 'weekly', 'prio' => '1.0'],
        ['loc' => '/free-trial', 'freq' => 'weekly', 'prio' => '0.9'],
        ['loc' => '/pricing', 'freq' => 'monthly', 'prio' => '0.9'],
        ['loc' => '/about', 'freq' => 'monthly', 'prio' => '0.8'],
        ['loc' => '/contact', 'freq' => 'monthly', 'prio' => '0.8'],
        ['loc' => '/auth/login', 'freq' => 'monthly', 'prio' => '0.7'],
        ['loc' => '/terms', 'freq' => 'monthly', 'prio' => '0.6'],
        ['loc' => '/privacy', 'freq' => 'monthly', 'prio' => '0.6'],
        ['loc' => '/refund-policy', 'freq' => 'monthly', 'prio' => '0.6'],
        ['loc' => '/disclaimer', 'freq' => 'monthly', 'prio' => '0.6'],
        ['loc' => '/security', 'freq' => 'monthly', 'prio' => '0.6']
    ];
    header('Content-Type: application/xml');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($pages as $p) {
        $url = $base . $p['loc'];
        echo "  <url>\n    <loc>{$url}</loc>\n    <lastmod>{$today}</lastmod>\n    <changefreq>{$p['freq']}</changefreq>\n    <priority>{$p['prio']}</priority>\n  </url>\n";
    }
    echo "</urlset>";
    exit;
});

// Dispatch the current request
Router::dispatch();
