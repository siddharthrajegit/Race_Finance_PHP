<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <link rel="stylesheet" href="/css/tailwind.css">
</head>
<body class="bg-[#F7F6F3] text-[#1C1B18] min-h-screen flex flex-col">
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
  <header class="sticky top-0 z-30 bg-[#1C1B18] text-white border-b border-[#32302B]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-3">
      <div>
        <div class="text-base font-semibold tracking-tight"><?= htmlspecialchars($settings['headline'] ?: $settings['firm_name'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($settings['city']) || !empty($settings['state'])): ?>
          <div class="text-xs text-[#B5B0A6]"><?= htmlspecialchars(trim(($settings['city'] ?? '') . ' ' . ($settings['state'] ?? '')), ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
      </div>
      <?php if ($waNumber !== ''): ?>
        <a href="https://wa.me/<?= htmlspecialchars($waNumber, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="rf-btn rf-btn-sage-solid rf-btn-sm">
          <i class="bi bi-whatsapp"></i> Chat on WhatsApp
        </a>
      <?php endif; ?>
    </div>
  </header>

  <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <?php if (!empty($settings['description'])): ?>
      <div class="rf-card-muted p-4 text-xs text-[#5C5A54] leading-relaxed"><?= nl2br(htmlspecialchars($settings['description'], ENT_QUOTES, 'UTF-8')) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
      <div class="md:col-span-7 relative">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="search" id="catSearch" class="rf-input rf-input-sm pl-8" placeholder="Search items by name or code...">
      </div>
      <div class="md:col-span-5">
        <select id="catFilter" class="rf-select rf-select-sm">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="catGrid">
      <?php if (empty($items)): ?>
        <div class="col-span-full rf-card p-12 text-center text-sm text-[#78746C]">No products currently listed.</div>
      <?php else: foreach ($items as $it): ?>
        <?php $inStock = (float)$it['current_stock'] > 0; ?>
        <div class="cat-card rf-card p-4 flex flex-col justify-between gap-3" data-name="<?= htmlspecialchars(strtolower($it['name'] . ' ' . ($it['item_code'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" data-cat="<?= htmlspecialchars($it['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div>
            <div class="flex items-start justify-between gap-2 mb-1">
              <h2 class="text-sm font-semibold text-[#1C1B18] m-0"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <?php if (!empty($settings['show_stock_badge'])): ?>
                <span class="rf-badge <?= $inStock ? 'rf-badge-sage' : 'rf-badge-neutral' ?> text-[10px] shrink-0">
                  <?= $inStock ? 'In Stock' : 'On Order' ?>
                </span>
              <?php endif; ?>
            </div>
            <?php if (!empty($it['category'])): ?>
              <div class="text-xs text-[#78746C] mb-1.5"><?= htmlspecialchars($it['category'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($it['description'])): ?>
              <p class="text-xs text-[#5C5A54] leading-relaxed m-0"><?= htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>

          <div class="flex items-center justify-between gap-2 pt-3 border-t border-[#ECEAE4]">
            <?php if (!empty($settings['show_prices'])): ?>
              <span class="text-sm font-semibold text-[#1C1B18] font-mono tabular-nums">
                ₹ <?= number_format((float)$it['sale_price'], 2) ?>
                <span class="text-[11px] font-sans font-normal text-[#78746C]">/ <?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
              </span>
            <?php else: ?>
              <span class="text-xs text-[#78746C]">Price on request</span>
            <?php endif; ?>
            <?php if ($waNumber !== ''): ?>
              <a href="https://wa.me/<?= htmlspecialchars($waNumber, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode("Hi, I'm interested in {$it['name']}") ?>" target="_blank" rel="noopener noreferrer" class="rf-btn rf-btn-sage rf-btn-sm py-1 px-2.5">Inquire</a>
            <?php endif; ?>
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
