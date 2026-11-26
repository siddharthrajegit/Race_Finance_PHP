<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-shop text-success me-2"></i>Online Product Catalogue</h3>
    <small class="text-muted">Share a clean read-only product &amp; rate link with WhatsApp inquiry checkout</small>
  </div>
  <?php if (!empty($catSettings['is_published'])): ?>
    <a href="/c/<?= htmlspecialchars($catSettings['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-outline-success btn-sm fw-semibold">
      <i class="bi bi-box-arrow-up-right me-1"></i> Open Public Storefront
    </a>
  <?php endif; ?>
</div>

<div class="card shadow-sm border-0" style="max-width: 720px;">
  <form action="/catalogue" method="POST">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="card-body p-4">
      <div class="mb-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
          <label class="form-label fw-bold mb-0">Publish Online Catalogue</label>
          <div class="small text-muted">Make product catalog accessible via public link without requiring login</div>
        </div>
        <div class="form-check form-switch fs-4">
          <input class="form-check-input" type="checkbox" name="is_published" value="1" <?= !empty($catSettings['is_published']) ? 'checked' : '' ?>>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Public URL Slug <span class="text-danger">*</span></label>
        <div class="input-group input-group-sm">
          <span class="input-group-text"><?= htmlspecialchars(rtrim(APP_URL, '/') . '/c/', ENT_QUOTES, 'UTF-8') ?></span>
          <input type="text" name="slug" class="form-control font-monospace" required value="<?= htmlspecialchars($catSettings['slug'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Storefront Headline</label>
        <input type="text" name="headline" class="form-control form-control-sm" value="<?= htmlspecialchars($catSettings['headline'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Store Description / Delivery Info</label>
        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Wholesale & retail inquiry catalogue"><?= htmlspecialchars($catSettings['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">WhatsApp Order / Inquiry Number (with or without 91)</label>
        <input type="text" name="whatsapp_number" class="form-control form-control-sm" value="<?= htmlspecialchars($catSettings['whatsapp_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 9876543210">
      </div>

      <div class="row g-2">
        <div class="col-md-4 form-check">
          <input class="form-check-input" type="checkbox" id="showPricesChk" name="show_prices" value="1" <?= !empty($catSettings['show_prices']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="showPricesChk">Show Prices</label>
        </div>
        <div class="col-md-4 form-check">
          <input class="form-check-input" type="checkbox" id="showStockChk" name="show_stock_badge" value="1" <?= !empty($catSettings['show_stock_badge']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="showStockChk">Show Stock Status</label>
        </div>
        <div class="col-md-4 form-check">
          <input class="form-check-input" type="checkbox" id="hideOutChk" name="hide_out_of_stock" value="1" <?= !empty($catSettings['hide_out_of_stock']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="hideOutChk">Hide Out-of-Stock Items</label>
        </div>
      </div>
    </div>
    <div class="card-footer bg-light p-3 text-end">
      <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">Save Catalogue Settings</button>
    </div>
  </form>
</div>
