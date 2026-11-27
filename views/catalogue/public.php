<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
</head>
<body class="bg-light">
  <?php
    $formatWaNumber = static function (?string $raw): string {
        $digits = preg_replace('/\D+/', '', (string)$raw);
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        return strlen($digits) === 10 ? ('91' . $digits) : $digits;
    };
    $waNumber = !empty($settings['whatsapp_number']) ? $formatWaNumber((string)$settings['whatsapp_number']) : '';
  ?>
  <nav class="navbar bg-dark navbar-dark py-3 shadow-sm">
    <div class="container">
      <div>
        <span class="navbar-brand fw-bold mb-0"><?= htmlspecialchars($settings['headline'] ?: $settings['firm_name'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php if (!empty($settings['city']) || !empty($settings['state'])): ?>
          <span class="text-secondary small ms-2"><?= htmlspecialchars(trim(($settings['city'] ?? '') . ' ' . ($settings['state'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
      </div>
      <?php if ($waNumber !== ''): ?>
        <a href="https://wa.me/<?= htmlspecialchars($waNumber, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm fw-semibold">
          <i class="bi bi-whatsapp me-1"></i> Chat on WhatsApp
        </a>
      <?php endif; ?>
    </div>
  </nav>

  <main class="container py-4">
    <?php if (!empty($settings['description'])): ?>
      <div class="alert alert-light border shadow-sm mb-3 small"><?= nl2br(htmlspecialchars($settings['description'], ENT_QUOTES, 'UTF-8')) ?></div>
    <?php endif; ?>

    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <input type="search" id="catSearch" class="form-control form-control-sm" placeholder="Search items by name or code...">
      </div>
      <div class="col-md-4">
        <select id="catFilter" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row g-3" id="catGrid">
      <?php if (empty($items)): ?>
        <div class="col-12 text-center py-5 text-muted">No products currently listed.</div>
      <?php else: foreach ($items as $it): ?>
        <?php $inStock = (float)$it['current_stock'] > 0; ?>
        <div class="col-sm-6 col-md-4 col-lg-3 cat-card" data-name="<?= htmlspecialchars(strtolower($it['name'] . ' ' . ($it['item_code'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" data-cat="<?= htmlspecialchars($it['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                <h6 class="fw-bold mb-0"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></h6>
                <?php if (!empty($settings['show_stock_badge'])): ?>
                  <span class="badge <?= $inStock ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>" style="font-size:0.65rem;">
                    <?= $inStock ? 'In Stock' : 'On Order' ?>
                  </span>
                <?php endif; ?>
              </div>
              <?php if (!empty($it['category'])): ?>
                <div class="small text-muted mb-2"><?= htmlspecialchars($it['category'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <?php if (!empty($it['description'])): ?>
                <p class="small text-secondary mb-2 flex-grow-1"><?= htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php else: ?>
                <div class="flex-grow-1"></div>
              <?php endif; ?>

              <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                <?php if (!empty($settings['show_prices'])): ?>
                  <span class="fw-bold text-primary">₹ <?= number_format((float)$it['sale_price'], 2) ?> <small class="text-muted fw-normal">/ <?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></small></span>
                <?php else: ?>
                  <span class="small text-muted">Price on request</span>
                <?php endif; ?>
                <?php if ($waNumber !== ''): ?>
                  <a href="https://wa.me/<?= htmlspecialchars($waNumber, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode("Hi, I'm interested in {$it['name']}") ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm py-0 px-2">Inquire</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </main>

  <script>
    const searchEl = document.getElementById('catSearch');
    const catEl = document.getElementById('catFilter');
    function filterCatalogue() {
      const q = (searchEl.value || '').toLowerCase();
      const c = catEl.value;
      document.querySelectorAll('.cat-card').forEach(card => {
        const matchQ = !q || card.dataset.name.includes(q);
        const matchC = !c || card.dataset.cat === c;
        card.style.display = (matchQ && matchC) ? '' : 'none';
      });
    }
    searchEl.addEventListener('input', filterCatalogue);
    catEl.addEventListener('change', filterCatalogue);
  </script>
</body>
</html>
