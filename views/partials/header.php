<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#0d6efd">
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
  <!-- Canonical URL -->
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

  <!-- Schema.org Structured Data (JSON-LD) for Google Rich Snippets & Sitelinks -->
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/uploads/logo-1787158589640-807857696.png">
  <link rel="apple-touch-icon" href="/uploads/logo-1787158589640-807857696.png">
  
  <!-- Bootstrap 5 CSS (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <!-- Bootstrap Icons (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <!-- Custom Styles -->
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php if (!empty($user)): ?>
<div class="app-layout">
  <!-- Mobile Sidebar Backdrop Overlay -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- Vertical Sidebar Navigation -->
  <aside class="app-sidebar p-3 text-white" id="appSidebar">
    <!-- Sidebar Header & Brand Logo -->
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-25">
      <a class="text-decoration-none text-white d-flex align-items-center fw-bold fs-5" href="<?= !empty($isAdmin) ? '/admin' : '/dashboard' ?>">
        <img src="/uploads/logo-1787158589640-807857696.png" alt="RACE FINANCE Logo" class="rounded-2 me-2 shadow-sm" width="34" height="34" style="object-fit: contain; background: #ffffff; padding: 2px; border: 1px solid rgba(255,255,255,0.2);">
        <span>RACE <span class="text-primary-light">FINANCE</span></span>
      </a>
      <button type="button" class="btn-close btn-close-white d-lg-none btn-sidebar-toggle" aria-label="Close Sidebar"></button>
    </div>

    <?php if (!empty($isAdmin)): ?>
      <!-- Admin Mode Badge -->
      <div class="card bg-dark border border-warning border-opacity-50 p-2 mb-3 shadow-sm rounded-3">
        <div class="d-flex align-items-center">
          <div class="bg-warning text-dark rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
            <i class="bi bi-shield-lock-fill fs-6"></i>
          </div>
          <div>
            <div class="fw-bold text-white small">Platform Admin</div>
            <div class="text-warning small" style="font-size: 0.7rem;">Control & Governance</div>
          </div>
        </div>
      </div>

      <!-- Navigation Menu Items for Admin -->
      <div class="sidebar-nav flex-grow-1">
        <ul class="nav flex-column mb-auto">
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'admin-dashboard') ? 'active' : '' ?>" href="/admin">
              <i class="bi bi-speedometer2"></i> Command Center
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'admin-users') ? 'active' : '' ?>" href="/admin/users">
              <i class="bi bi-people-fill"></i> Subscribers & Users
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'admin-firms') ? 'active' : '' ?>" href="/admin/firms">
              <i class="bi bi-buildings-fill"></i> Business Firms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'admin-invoices') ? 'active' : '' ?>" href="/admin/invoices">
              <i class="bi bi-receipt"></i> Platform Records
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'admin-system') ? 'active' : '' ?>" href="/admin/system">
              <i class="bi bi-hdd-network-fill"></i> System & Storage
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'admin-settings') ? 'active' : '' ?>" href="/admin/settings">
              <i class="bi bi-sliders"></i> Platform Settings
            </a>
          </li>
        </ul>
      </div>
    <?php else: ?>
      <!-- Active Firm Switcher Card for Regular Users -->
      <?php if (!empty($userFirms)): ?>
        <div class="dropdown mb-3">
          <div class="sidebar-firm-card dropdown-toggle d-flex align-items-center justify-content-between" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center text-truncate me-2">
              <i class="bi bi-buildings fs-5 text-warning me-2"></i>
              <div class="text-truncate">
                <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($activeFirm['name'] ?? 'Select Firm', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars(!empty($activeFirm['gstin']) ? $activeFirm['gstin'] : (!empty($activeFirm['state']) ? $activeFirm['state'] : 'Switch Business'), ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
            <i class="bi bi-chevron-expand text-muted small"></i>
          </div>

          <ul class="dropdown-menu dropdown-menu-dark shadow border-secondary w-100">
            <li class="dropdown-header text-uppercase text-secondary small fw-bold">Switch Business Firm</li>
            <?php foreach ($userFirms as $firm): ?>
              <li>
                <form action="/firms/switch/<?= $firm['id'] ?>" method="POST" class="m-0">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="dropdown-item py-2 d-flex align-items-center justify-content-between <?= (!empty($activeFirm) && $activeFirm['id'] == $firm['id']) ? 'active bg-primary' : '' ?>">
                    <span class="text-truncate me-2"><?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($firm['is_default'])): ?>
                      <span class="badge bg-secondary ms-1 small">Default</span>
                    <?php endif; ?>
                  </button>
                </form>
              </li>
            <?php endforeach; ?>
            <li><hr class="dropdown-divider border-secondary"></li>
            <li>
              <a class="dropdown-item text-primary-light d-flex align-items-center py-2" href="/firms/create">
                <i class="bi bi-plus-circle me-2"></i> Register New Firm
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center py-2" href="/firms">
                <i class="bi bi-gear me-2"></i> Manage All Firms
              </a>
            </li>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Quick CTA Button -->
      <a href="/sales/create" class="btn btn-success btn-sm w-100 mb-3 shadow-sm d-flex align-items-center justify-content-center py-2 fw-semibold">
        <i class="bi bi-plus-circle-fill me-1"></i> New Sales Record
      </a>

      <!-- Navigation Menu Items for Users -->
      <div class="sidebar-nav flex-grow-1">
        <ul class="nav flex-column mb-auto">
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'dashboard') ? 'active' : '' ?>" href="/dashboard">
              <i class="bi bi-grid"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'sales') ? 'active' : '' ?>" href="/sales">
              <i class="bi bi-cart-check"></i> Sales Records
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'purchases') ? 'active' : '' ?>" href="/purchases">
              <i class="bi bi-bag-plus"></i> Purchase Records
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'items') ? 'active' : '' ?>" href="/items">
              <i class="bi bi-box-seam"></i> Items & Inventory
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'parties') ? 'active' : '' ?>" href="/parties">
              <i class="bi bi-people"></i> Parties & Customers
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'payments') ? 'active' : '' ?>" href="/payments">
              <i class="bi bi-cash-stack"></i> Payments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'reports') ? 'active' : '' ?>" href="/reports">
              <i class="bi bi-bar-chart-line"></i> Reports & GST
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'backup') ? 'active' : '' ?>" href="/backup">
              <i class="bi bi-cloud-arrow-up"></i> Backup & Cloud
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($activeMenu === 'settings') ? 'active' : '' ?>" href="/settings">
              <i class="bi bi-gear"></i> Settings
            </a>
          </li>
        </ul>
      </div>
    <?php endif; ?>

    <!-- User Profile & Footer Section inside Sidebar -->
    <div class="mt-auto pt-3 border-top border-secondary border-opacity-25">
      <div class="dropdown">
        <div class="d-flex align-items-center justify-content-between p-2 rounded sidebar-nav" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
          <div class="d-flex align-items-center text-truncate me-2">
            <?php if (!empty($user['avatar'])): ?>
              <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle me-2" width="28" height="28">
            <?php else: ?>
              <i class="bi bi-person-circle fs-5 text-secondary me-2"></i>
            <?php endif; ?>
            <div class="text-truncate">
              <div class="fw-semibold text-white small text-truncate d-flex align-items-center gap-1">
                <span><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($isAdmin)): ?>
                  <span class="badge bg-warning text-dark" style="font-size: 0.6rem;">ADMIN</span>
                <?php endif; ?>
              </div>
              <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($user['email'] ?? ($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
          <i class="bi bi-three-dots-vertical text-muted"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary w-100">
          <?php if (!empty($isAdmin)): ?>
            <li><a class="dropdown-item py-2 text-warning fw-bold" href="/admin"><i class="bi bi-shield-lock-fill me-2"></i> Admin Command Center</a></li>
          <?php else: ?>
            <li><a class="dropdown-item py-2" href="/settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
            <li><a class="dropdown-item py-2" href="/firms"><i class="bi bi-buildings me-2"></i> Business Firms</a></li>
            <li><a class="dropdown-item py-2" href="/backup"><i class="bi bi-cloud-arrow-up me-2"></i> Data Backup</a></li>
          <?php endif; ?>
          <li>
            <form action="/auth/logout" method="POST" class="m-0 p-0">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="dropdown-item py-2 text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i> Sign Out
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </aside>

  <!-- Main Content Layout Area -->
  <div class="app-main-wrapper">
    <!-- Top Mobile Navbar Header -->
    <header class="d-lg-none bg-dark text-white p-2 px-3 d-flex align-items-center justify-content-between sticky-top shadow-sm">
      <button class="btn btn-outline-light btn-sm btn-sidebar-toggle me-2" type="button" aria-label="Toggle navigation">
        <i class="bi bi-list fs-5"></i>
      </button>
      <div class="fw-bold d-flex align-items-center">
        <img src="/uploads/logo-1787158589640-807857696.png" alt="Logo" class="rounded-1 me-2 shadow-sm" width="26" height="26" style="object-fit: contain; background: #ffffff; padding: 1px;">
        <span><?= !empty($isAdmin) ? 'RACE FINANCE Admin' : (!empty($activeFirm) ? htmlspecialchars($activeFirm['name'], ENT_QUOTES, 'UTF-8') : 'RACE FINANCE') ?></span>
      </div>
      <?php if (empty($isAdmin)): ?>
        <a href="/sales/create" class="btn btn-success btn-sm">
          <i class="bi bi-plus-lg"></i> Record
        </a>
      <?php else: ?>
        <div></div>
      <?php endif; ?>
    </header>

    <main class="py-4 flex-grow-1">
      <div class="container-fluid px-lg-4">
        <?php if (!empty($platformSettings['enable_announcement']) && $platformSettings['enable_announcement'] === '1' && !empty($platformSettings['platform_announcement'])): ?>
          <div class="alert alert-<?= htmlspecialchars($platformSettings['platform_announcement_type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show shadow-sm mb-4 border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-megaphone-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($platformSettings['platform_announcement'], ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <?php require __DIR__ . '/alerts.php'; ?>
<?php else: ?>
<!-- Public / Guest Simple Transparent Navigation Header -->
<nav class="navbar navbar-expand-lg bg-transparent border-0 py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center text-dark text-decoration-none fw-bold" href="/">
      <img src="/uploads/logo-1787158589640-807857696.png" alt="RACE FINANCE Logo" class="rounded-2 me-2" width="28" height="28" style="object-fit: contain;">
      <span>RACE FINANCE</span>
    </a>
    <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbarNav" aria-controls="guestNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list fs-3"></i>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="guestNavbarNav">
      <div class="navbar-nav align-items-lg-center gap-3 gap-lg-4 mt-2 mt-lg-0 small">
        <a class="text-decoration-none <?= (!empty($activeTab) && $activeTab === 'home') ? 'text-dark fw-bold' : 'text-secondary' ?>" href="/">Home</a>
        <a class="text-decoration-none <?= (!empty($activeTab) && $activeTab === 'about') ? 'text-dark fw-bold' : 'text-secondary' ?>" href="/about">About</a>
        <a class="text-decoration-none <?= (!empty($activeTab) && $activeTab === 'pricing') ? 'text-dark fw-bold' : 'text-secondary' ?>" href="/pricing">Pricing</a>
        <a class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold <?= (!empty($activeTab) && $activeTab === 'free-trial') ? 'active' : '' ?>" href="/free-trial">
          <i class="bi bi-gift-fill me-1"></i> 1-Month Free Trial
        </a>
        <a class="text-decoration-none <?= (!empty($activeTab) && $activeTab === 'contact') ? 'text-dark fw-bold' : 'text-secondary' ?>" href="/contact">Contact</a>
        <a class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-semibold" href="/auth/login">Sign In</a>
      </div>
    </div>
  </div>
</nav>

<!-- Non-Logged In Container (Login, Legal, About, Error Pages) -->
<main class="py-3">
  <div class="container">
    <?php require __DIR__ . '/alerts.php'; ?>
<?php endif; ?>
