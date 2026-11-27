<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#F7F6F3">
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars(!empty($title) ? (str_contains($title, 'RACE FINANCE') ? $title : $title . ' - RACE FINANCE') : 'RACE FINANCE - Smart Small Business Management, Billing & Inventory', ENT_QUOTES, 'UTF-8') ?></title>

  <!-- Favicon & PWA Manifest -->
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/logo.png">
  <link rel="manifest" href="/manifest.json">

  <!-- Primary SEO Meta Tags -->
  <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'RACE FINANCE is a modern business management, billing and inventory utility for traders and small businesses. Features dual-firm management, FIFO party ledgers, and GST registers.', ENT_QUOTES, 'UTF-8') ?>">
  <meta name="keywords" content="RACE FINANCE, <?= htmlspecialchars($appDomain ?? 'racefinance.site', ENT_QUOTES, 'UTF-8') ?>, small business billing, inventory management, bookkeeping utility, digital sales records, FIFO ledgers, GST tax utility, India small business management">
  <meta name="author" content="RACE FINANCE">
  <meta name="robots" content="<?= !empty($user) ? 'noindex, nofollow' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1' ?>">

  <?php
    $canonicalPath = '/';
    if (!empty($activeTab)) {
      if ($activeTab === 'home') $canonicalPath = '/';
      elseif ($activeTab === 'free-trial') $canonicalPath = '/free-trial';
      elseif ($activeTab === 'about') $canonicalPath = '/about';
      elseif ($activeTab === 'pricing') $canonicalPath = '/pricing';
      elseif ($activeTab === 'contact') $canonicalPath = '/contact';
      elseif ($activeTab === 'terms') $canonicalPath = '/terms';
      elseif ($activeTab === 'privacy') $canonicalPath = '/privacy';
      elseif ($activeTab === 'refund') $canonicalPath = '/refund-policy';
      elseif ($activeTab === 'disclaimer') $canonicalPath = '/disclaimer';
      elseif ($activeTab === 'security') $canonicalPath = '/security';
      else $canonicalPath = '/' . ltrim($activeTab, '/');
    } elseif (str_contains($currentUrl ?? '', '/auth/login')) {
      $canonicalPath = '/auth/login';
    }
    $pageCanonicalUrl = rtrim($appUrl ?? 'https://racefinance.site', '/') . ($canonicalPath === '/' ? '' : $canonicalPath);
  ?>
  <link rel="canonical" href="<?= htmlspecialchars($pageCanonicalUrl, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($pageCanonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name" content="RACE FINANCE">
  <meta property="og:title" content="<?= htmlspecialchars(!empty($title) ? (str_contains($title, 'RACE FINANCE') ? $title : $title . ' - RACE FINANCE') : 'RACE FINANCE - Small Business Billing & Inventory System', ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'Modern small business billing, inventory tracking, and bookkeeping utility for Indian merchants & traders.', ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/uploads/logo-1787158589640-807857696.png">
  <meta property="og:image:width" content="512">
  <meta property="og:image:height" content="512">
  <meta property="og:locale" content="en_IN">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="<?= htmlspecialchars($pageCanonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:title" content="<?= htmlspecialchars(!empty($title) ? (str_contains($title, 'RACE FINANCE') ? $title : $title . ' - RACE FINANCE') : 'RACE FINANCE - Small Business Billing & Inventory', ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription ?? 'Modern small business billing, inventory tracking, and bookkeeping utility.', ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/uploads/logo-1787158589640-807857696.png">

  <!-- Schema.org Structured Data (JSON-LD) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/#organization",
        "name": "RACE FINANCE",
        "url": "<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>",
        "logo": "<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/uploads/logo-1787158589640-807857696.png",
        "description": "Smart small business billing, GST invoicing, and inventory bookkeeping software for Indian merchants.",
        "contactPoint": {
          "@type": "ContactPoint",
          "telephone": "+<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>",
          "contactType": "customer support",
          "areaServed": "IN",
          "availableLanguage": ["English", "Hindi"]
        }
      },
      {
        "@type": "WebSite",
        "@id": "<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/#website",
        "url": "<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>",
        "name": "RACE FINANCE",
        "description": "Smart small business billing, inventory accounting, and FIFO party ledgers.",
        "publisher": {
          "@id": "<?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/#organization"
        }
      },
      {
        "@type": "SoftwareApplication",
        "name": "RACE FINANCE",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "All modern web browsers",
        "offers": {
          "@type": "Offer",
          "price": "0",
          "priceCurrency": "INR",
          "name": "1-Month Complimentary Free Trial",
          "description": "Full-featured 30-day evaluation trial with zero setup fee."
        }
      }
    ]
  }
  </script>

  <!-- Typography & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/uploads/logo-1787158589640-807857696.png">
  <link rel="apple-touch-icon" href="/uploads/logo-1787158589640-807857696.png">

  <!-- Icons & Datepicker (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <link rel="stylesheet" href="/vendor/flatpickr/flatpickr.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';">
  <!-- Compiled Tailwind CSS -->
  <link rel="stylesheet" href="/css/tailwind.css">
</head>
<body class="bg-[#F7F6F3] text-[#1C1B18] min-h-screen flex flex-col">

<?php if (!empty($user)): ?>
<div class="flex min-h-screen w-full">
  <!-- Mobile Sidebar Backdrop -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- Calm Warm-Stone Sidebar Navigation -->
  <aside class="app-sidebar w-[264px] shrink-0 bg-[#F2F0EB] border-r border-[#E5E2DC] lg:sticky lg:top-0 lg:h-screen overflow-y-auto flex flex-col p-4 z-40" id="appSidebar">
    <!-- Brand Header -->
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E2DFD7]">
      <a class="flex items-center gap-2.5 text-[#1C1B18] no-underline font-semibold tracking-tight text-base" href="<?= !empty($isAdmin) ? '/admin' : '/dashboard' ?>">
        <img src="/uploads/logo-1787158589640-807857696.png" alt="RACE FINANCE Logo" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 border border-[#E2DFD7]">
        <span>RACE <span class="text-[#5C5A54] font-medium">FINANCE</span></span>
      </a>
      <button type="button" class="lg:hidden btn-sidebar-toggle p-1.5 rounded-lg text-[#5C5A54] hover:text-[#1C1B18] hover:bg-[#E7E4DC] bg-transparent border-0 cursor-pointer" aria-label="Close Sidebar">
        <i class="bi bi-x-lg text-base"></i>
      </button>
    </div>

    <?php if (!empty($isAdmin)): ?>
      <!-- Admin Mode Identity -->
      <div class="bg-white border border-[#E2DFD7] rounded-xl p-3 mb-4 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-[#FBF3DB] text-[#8A5A00] border border-[#EAE0B8] flex items-center justify-center shrink-0">
          <i class="bi bi-shield-lock text-sm"></i>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-semibold text-[#1C1B18] truncate">Platform Admin</div>
          <div class="text-[11px] text-[#78746C] truncate">Control & Governance</div>
        </div>
      </div>

      <nav class="flex-1 space-y-1">
        <a class="rf-nav-link <?= ($activeMenu === 'admin-dashboard') ? 'active' : '' ?>" href="/admin">
          <i class="bi bi-speedometer2 text-base"></i><span>Command Center</span>
        </a>
        <a class="rf-nav-link <?= ($activeMenu === 'admin-users') ? 'active' : '' ?>" href="/admin/users">
          <i class="bi bi-people text-base"></i><span>Subscribers & Users</span>
        </a>
        <a class="rf-nav-link <?= ($activeMenu === 'admin-firms') ? 'active' : '' ?>" href="/admin/firms">
          <i class="bi bi-buildings text-base"></i><span>Business Firms</span>
        </a>
        <a class="rf-nav-link <?= ($activeMenu === 'admin-invoices') ? 'active' : '' ?>" href="/admin/invoices">
          <i class="bi bi-receipt text-base"></i><span>Platform Records</span>
        </a>
        <a class="rf-nav-link <?= ($activeMenu === 'admin-system') ? 'active' : '' ?>" href="/admin/system">
          <i class="bi bi-hdd-network text-base"></i><span>System & Storage</span>
        </a>
        <a class="rf-nav-link <?= ($activeMenu === 'admin-settings') ? 'active' : '' ?>" href="/admin/settings">
          <i class="bi bi-sliders text-base"></i><span>Platform Settings</span>
        </a>
      </nav>
    <?php else: ?>
      <?php
        $canPerm = function(string $perm) use ($userPermissions): bool {
            $list = $userPermissions ?? [];
            return in_array('*', $list, true) || in_array($perm, $list, true);
        };
        $isOwnerAccount = (($user['role'] ?? 'user') !== 'staff');
        $advanceMenuKeys = [
            'warehouses', 'batches', 'serials', 'bom', 'barcodes',
            'loyalty', 'catalogue', 'expenses', 'accounting', 'staff', 'audit_logs',
        ];
        $isAdvanceActive = in_array($activeMenu ?? '', $advanceMenuKeys, true);
        $hasAdvanceItems =
            (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE && $canPerm('view_warehouses')) ||
            (defined('ENABLE_BATCH_EXPIRY') && ENABLE_BATCH_EXPIRY && $canPerm('view_items')) ||
            (defined('ENABLE_SERIAL_TRACKING') && ENABLE_SERIAL_TRACKING && $canPerm('view_items')) ||
            (defined('ENABLE_BOM') && ENABLE_BOM && $canPerm('manage_bom')) ||
            (defined('ENABLE_BARCODE') && ENABLE_BARCODE && $canPerm('view_items')) ||
            (defined('ENABLE_LOYALTY') && ENABLE_LOYALTY && $canPerm('manage_loyalty')) ||
            (defined('ENABLE_CATALOGUE') && ENABLE_CATALOGUE && $canPerm('manage_catalogue')) ||
            (defined('ENABLE_EXPENSES') && ENABLE_EXPENSES && $canPerm('view_expenses')) ||
            (defined('ENABLE_ACCOUNTING') && ENABLE_ACCOUNTING && $canPerm('view_accounting')) ||
            ($isOwnerAccount && defined('ENABLE_RBAC') && ENABLE_RBAC) ||
            (defined('ENABLE_AUDIT_LOGS') && ENABLE_AUDIT_LOGS && $canPerm('view_audit_logs'));
      ?>
      <!-- Active Firm Switcher -->
      <?php if (!empty($userFirms)): ?>
        <div class="rf-dropdown w-full mb-3.5" data-ui-dropdown>
          <button type="button" class="w-full bg-white border border-[#E2DFD7] hover:border-[#C8C4BA] rounded-xl px-3 py-2.5 flex items-center justify-between gap-2 text-left transition-colors cursor-pointer" data-ui-dropdown-toggle aria-expanded="false">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-7 h-7 rounded-lg bg-[#F3F1EC] text-[#5C5A54] flex items-center justify-center shrink-0">
                <i class="bi bi-buildings text-sm"></i>
              </div>
              <div class="min-w-0">
                <div class="text-xs font-semibold text-[#1C1B18] truncate"><?= htmlspecialchars($activeFirm['name'] ?? 'Select Firm', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-[11px] text-[#78746C] truncate font-mono"><?= htmlspecialchars(!empty($activeFirm['gstin']) ? $activeFirm['gstin'] : (!empty($activeFirm['state']) ? $activeFirm['state'] : 'Switch Business'), ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
            <i class="bi bi-chevron-expand text-xs text-[#87847C] shrink-0"></i>
          </button>

          <div class="rf-dropdown-menu w-full left-0 right-0" data-ui-dropdown-menu>
            <div class="px-3.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-[#87847C]">Switch Business Firm</div>
            <?php foreach ($userFirms as $firmOption): ?>
              <form action="/firms/switch/<?= $firmOption['id'] ?>" method="POST" class="m-0">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="rf-dropdown-item justify-between <?= (!empty($activeFirm) && $activeFirm['id'] == $firmOption['id']) ? 'bg-[#F3F1EC] font-semibold text-[#1C1B18]' : '' ?>">
                  <span class="truncate"><?= htmlspecialchars($firmOption['name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (!empty($firmOption['is_default'])): ?>
                    <span class="rf-badge rf-badge-neutral text-[10px]">Default</span>
                  <?php endif; ?>
                </button>
              </form>
            <?php endforeach; ?>
            <?php if ($isOwnerAccount): ?>
              <div class="rf-dropdown-divider"></div>
              <a class="rf-dropdown-item text-[#2E5A36]" href="/firms/create">
                <i class="bi bi-plus-circle"></i> Register New Firm
              </a>
              <a class="rf-dropdown-item" href="/firms">
                <i class="bi bi-gear"></i> Manage All Firms
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Primary CTA & Offline Indicator -->
      <?php if ($canPerm('create_sales')): ?>
        <a href="/sales/create" class="rf-btn rf-btn-primary w-full mb-2 py-2.5 text-xs">
          <i class="bi bi-plus-lg"></i> New Sales Record
        </a>
      <?php endif; ?>
      <?php if (defined('ENABLE_OFFLINE') && ENABLE_OFFLINE): ?>
        <button type="button" id="offlineStatusBadge" class="rf-btn rf-btn-sage rf-btn-sm w-full mb-3 py-1.5 justify-center text-xs" data-ui-modal="#offlineQueueModal">
          <i class="bi bi-wifi"></i> Online
        </button>
      <?php endif; ?>

      <!-- Sidebar Navigation -->
      <nav class="flex-1 space-y-1">
        <?php if ($canPerm('view_dashboard')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'dashboard') ? 'active' : '' ?>" href="/dashboard">
            <i class="bi bi-grid text-base"></i><span>Dashboard</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_sales')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'sales') ? 'active' : '' ?>" href="/sales">
            <i class="bi bi-receipt text-base"></i><span>Sales Records</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_purchases')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'purchases') ? 'active' : '' ?>" href="/purchases">
            <i class="bi bi-bag text-base"></i><span>Purchase Records</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_items')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'items') ? 'active' : '' ?>" href="/items">
            <i class="bi bi-box-seam text-base"></i><span>Items &amp; Inventory</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_estimates') || $canPerm('manage_estimates')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'estimates') ? 'active' : '' ?>" href="/estimates/builder">
            <i class="bi bi-file-earmark-spreadsheet text-base"></i><span>Rate List &amp; Estimate</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_parties')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'parties') ? 'active' : '' ?>" href="/parties">
            <i class="bi bi-people text-base"></i><span>Parties &amp; Customers</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_payments')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'payments') ? 'active' : '' ?>" href="/payments">
            <i class="bi bi-cash-stack text-base"></i><span>Payments</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('view_reports')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'reports') ? 'active' : '' ?>" href="/reports">
            <i class="bi bi-bar-chart text-base"></i><span>Reports &amp; GST</span>
          </a>
        <?php endif; ?>
        <?php if ($isOwnerAccount): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'backup') ? 'active' : '' ?>" href="/backup">
            <i class="bi bi-cloud-arrow-up text-base"></i><span>Backup &amp; Cloud</span>
          </a>
        <?php endif; ?>
        <?php if ($canPerm('manage_settings')): ?>
          <a class="rf-nav-link <?= ($activeMenu === 'settings') ? 'active' : '' ?>" href="/settings">
            <i class="bi bi-gear text-base"></i><span>Settings</span>
          </a>
        <?php endif; ?>

        <?php if ($hasAdvanceItems): ?>
          <div>
            <button
              type="button"
              class="rf-nav-link w-full justify-between text-left bg-transparent border-0 cursor-pointer <?= $isAdvanceActive ? 'bg-[#E7E4DC]/75 text-[#1C1B18] font-semibold' : '' ?>"
              data-ui-collapse="#sidebarAdvanceMenu"
              aria-expanded="<?= $isAdvanceActive ? 'true' : 'false' ?>"
              aria-controls="sidebarAdvanceMenu"
            >
              <span class="flex items-center gap-3">
                <i class="bi bi-sliders2 text-base"></i>
                <span>Advance</span>
              </span>
              <i class="bi bi-chevron-down advance-chevron text-[11px] text-[#78746C] transition-transform duration-150" aria-hidden="true"></i>
            </button>
            <div class="<?= $isAdvanceActive ? '' : 'hidden' ?> mt-1 space-y-0.5 pl-4" id="sidebarAdvanceMenu" data-ui-collapse-panel>
              <?php if (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE && $canPerm('view_warehouses')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'warehouses') ? 'active' : '' ?>" href="/warehouses">
                  <i class="bi bi-buildings text-sm"></i><span>Warehouses</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_BATCH_EXPIRY') && ENABLE_BATCH_EXPIRY && $canPerm('view_items')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'batches') ? 'active' : '' ?>" href="/batches">
                  <i class="bi bi-calendar2-week text-sm"></i><span>Batches &amp; Expiry</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_SERIAL_TRACKING') && ENABLE_SERIAL_TRACKING && $canPerm('view_items')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'serials') ? 'active' : '' ?>" href="/serials">
                  <i class="bi bi-hash text-sm"></i><span>Serial / IMEI</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_BOM') && ENABLE_BOM && $canPerm('manage_bom')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'bom') ? 'active' : '' ?>" href="/bom">
                  <i class="bi bi-boxes text-sm"></i><span>Manufacturing BOM</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_BARCODE') && ENABLE_BARCODE && $canPerm('view_items')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'barcodes') ? 'active' : '' ?>" href="/barcodes">
                  <i class="bi bi-upc-scan text-sm"></i><span>Barcode Labels</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_LOYALTY') && ENABLE_LOYALTY && $canPerm('manage_loyalty')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'loyalty') ? 'active' : '' ?>" href="/loyalty">
                  <i class="bi bi-stars text-sm"></i><span>Loyalty Rewards</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_CATALOGUE') && ENABLE_CATALOGUE && $canPerm('manage_catalogue')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'catalogue') ? 'active' : '' ?>" href="/catalogue">
                  <i class="bi bi-shop text-sm"></i><span>Online Catalogue</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_EXPENSES') && ENABLE_EXPENSES && $canPerm('view_expenses')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'expenses') ? 'active' : '' ?>" href="/expenses">
                  <i class="bi bi-wallet2 text-sm"></i><span>Expenses</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_ACCOUNTING') && ENABLE_ACCOUNTING && $canPerm('view_accounting')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'accounting') ? 'active' : '' ?>" href="/accounting">
                  <i class="bi bi-diagram-3 text-sm"></i><span>Accounting &amp; P&amp;L</span>
                </a>
              <?php endif; ?>
              <?php if ($isOwnerAccount && defined('ENABLE_RBAC') && ENABLE_RBAC): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'staff') ? 'active' : '' ?>" href="/staff">
                  <i class="bi bi-person-badge text-sm"></i><span>Staff &amp; CA Team</span>
                </a>
              <?php endif; ?>
              <?php if (defined('ENABLE_AUDIT_LOGS') && ENABLE_AUDIT_LOGS && $canPerm('view_audit_logs')): ?>
                <a class="rf-nav-link py-1.5 text-xs <?= ($activeMenu === 'audit_logs') ? 'active' : '' ?>" href="/audit-logs">
                  <i class="bi bi-journal-text text-sm"></i><span>Audit Logs</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

    <!-- Account Footer Menu -->
    <div class="mt-auto pt-4 border-t border-[#E2DFD7]">
      <div class="rf-dropdown w-full" data-ui-dropdown>
        <button type="button" class="w-full flex items-center justify-between gap-2 px-2.5 py-2 rounded-xl hover:bg-[#E7E4DC]/70 transition-colors text-left cursor-pointer bg-transparent border-0" data-ui-dropdown-toggle aria-expanded="false">
          <div class="flex items-center gap-2.5 min-w-0">
            <?php if (!empty($user['avatar'])): ?>
              <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-7 h-7 rounded-full object-cover border border-[#D4D1C9]">
            <?php else: ?>
              <div class="w-7 h-7 rounded-full bg-[#E5E2DC] text-[#5C5A54] flex items-center justify-center shrink-0">
                <i class="bi bi-person text-sm"></i>
              </div>
            <?php endif; ?>
            <div class="min-w-0">
              <div class="text-xs font-semibold text-[#1C1B18] truncate flex items-center gap-1.5">
                <span class="truncate"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($isAdmin)): ?>
                  <span class="rf-badge rf-badge-amber text-[9px] px-1.5 py-0">ADMIN</span>
                <?php elseif (($user['role'] ?? 'user') === 'staff'): ?>
                  <span class="rf-badge rf-badge-slate text-[9px] px-1.5 py-0"><?= htmlspecialchars(strtoupper($staffRole ?? 'STAFF'), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <div class="text-[11px] text-[#78746C] truncate"><?= htmlspecialchars($user['email'] ?? ($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
          <i class="bi bi-three-dots-vertical text-xs text-[#87847C] shrink-0"></i>
        </button>

        <div class="rf-dropdown-menu w-full left-0 right-0" data-ui-dropdown-menu>
          <?php if (!empty($isAdmin)): ?>
            <a class="rf-dropdown-item font-semibold text-[#8A5A00]" href="/admin"><i class="bi bi-shield-lock"></i> Admin Command Center</a>
          <?php else: ?>
            <?php if (in_array('*', $userPermissions ?? [], true) || in_array('manage_settings', $userPermissions ?? [], true)): ?>
              <a class="rf-dropdown-item" href="/settings"><i class="bi bi-gear"></i> Settings</a>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'user') !== 'staff'): ?>
              <a class="rf-dropdown-item" href="/firms"><i class="bi bi-buildings"></i> Business Firms</a>
              <a class="rf-dropdown-item" href="/backup"><i class="bi bi-cloud-arrow-up"></i> Data Backup</a>
            <?php endif; ?>
          <?php endif; ?>
          <div class="rf-dropdown-divider"></div>
          <form action="/auth/logout" method="POST" class="m-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
              <i class="bi bi-box-arrow-right"></i> Sign Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Workspace Area -->
  <div class="flex-1 min-w-0 flex flex-col bg-[#F7F6F3]">
    <!-- Mobile Top Header -->
    <header class="lg:hidden sticky top-0 z-30 bg-[#FAF9F6]/95 backdrop-blur-sm border-b border-[#E6E4DF] px-4 py-3 flex items-center justify-between gap-3">
      <button class="btn-sidebar-toggle rf-btn rf-btn-secondary rf-btn-sm px-2.5 py-2" type="button" aria-label="Toggle navigation">
        <i class="bi bi-list text-base"></i>
      </button>
      <div class="flex items-center gap-2 font-semibold text-sm text-[#1C1B18] truncate">
        <img src="/uploads/logo-1787158589640-807857696.png" alt="Logo" class="w-6 h-6 rounded object-contain bg-white p-0.5 border border-[#E2DFD7]">
        <span class="truncate"><?= !empty($isAdmin) ? 'RACE FINANCE Admin' : (!empty($activeFirm) ? htmlspecialchars($activeFirm['name'], ENT_QUOTES, 'UTF-8') : 'RACE FINANCE') ?></span>
      </div>
      <?php if (empty($isAdmin)): ?>
        <a href="/sales/create" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-plus-lg"></i> Record
        </a>
      <?php else: ?>
        <div class="w-8"></div>
      <?php endif; ?>
    </header>

    <main class="flex-1 py-7 px-4 sm:px-7 lg:px-10">
      <div class="max-w-7xl mx-auto">
        <?php if (!empty($platformSettings['enable_announcement']) && $platformSettings['enable_announcement'] === '1' && !empty($platformSettings['platform_announcement'])): ?>
          <?php
            $annType = $platformSettings['platform_announcement_type'] ?? 'info';
            $annClass = match ($annType) {
              'success' => 'rf-alert-success',
              'danger' => 'rf-alert-danger',
              'warning' => 'rf-alert-warning',
              default => 'rf-alert-info',
            };
          ?>
          <div class="rf-alert <?= $annClass ?>" role="alert" data-ui-alert>
            <i class="bi bi-megaphone text-base mt-0.5 shrink-0"></i>
            <div class="flex-1"><?= htmlspecialchars($platformSettings['platform_announcement'], ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="bg-transparent border-0 text-current opacity-60 hover:opacity-100 cursor-pointer p-0.5" data-ui-dismiss="alert" aria-label="Close">
              <i class="bi bi-x-lg text-xs"></i>
            </button>
          </div>
        <?php endif; ?>
        <?php require __DIR__ . '/alerts.php'; ?>
<?php else: ?>
<!-- Guest Navigation Header -->
<header class="sticky top-0 z-30 bg-[#F7F6F3]/90 backdrop-blur-md border-b border-[#E6E4DF]">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
    <a class="flex items-center gap-2.5 text-[#1C1B18] no-underline font-semibold tracking-tight text-base" href="/">
      <img src="/uploads/logo-1787158589640-807857696.png" alt="RACE FINANCE Logo" class="w-7 h-7 rounded-lg object-contain bg-white p-0.5 border border-[#E2DFD7]">
      <span>RACE FINANCE</span>
    </a>

    <button class="lg:hidden rf-btn rf-btn-secondary rf-btn-sm px-2.5 py-1.5" type="button" data-ui-collapse="#guestNavbarNav" aria-controls="guestNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list text-lg"></i>
    </button>

    <div class="hidden lg:flex w-full lg:w-auto flex-col lg:flex-row lg:items-center gap-3 lg:gap-6 pt-2 lg:pt-0 border-t lg:border-t-0 border-[#E6E4DF] text-sm" id="guestNavbarNav" data-ui-collapse-panel>
      <a class="no-underline transition-colors <?= (!empty($activeTab) && $activeTab === 'home') ? 'text-[#1C1B18] font-semibold' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>" href="/">Home</a>
      <a class="no-underline transition-colors <?= (!empty($activeTab) && $activeTab === 'about') ? 'text-[#1C1B18] font-semibold' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>" href="/about">About</a>
      <a class="no-underline transition-colors <?= (!empty($activeTab) && $activeTab === 'pricing') ? 'text-[#1C1B18] font-semibold' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>" href="/pricing">Pricing</a>
      <a class="rf-btn rf-btn-sage rf-btn-sm rounded-full px-3.5" href="/free-trial">
        <i class="bi bi-gift"></i> 1-Month Free Trial
      </a>
      <a class="no-underline transition-colors <?= (!empty($activeTab) && $activeTab === 'contact') ? 'text-[#1C1B18] font-semibold' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>" href="/contact">Contact</a>
      <a class="rf-btn rf-btn-secondary rf-btn-sm px-4" href="/auth/login">Sign In</a>
    </div>
  </div>
</header>

<main class="flex-1 py-8 sm:py-10 px-4 sm:px-6 lg:px-8">
  <div class="max-w-6xl mx-auto">
    <?php require __DIR__ . '/alerts.php'; ?>
<?php endif; ?>
