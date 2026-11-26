<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 d-print-none">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-upc-scan text-primary me-2"></i>Barcode Label Printing Studio</h3>
    <small class="text-muted">Generate crisp vector Code128B printable sticker labels (no PHP GD extension needed)</small>
  </div>
  <div class="d-flex gap-2">
    <button type="button" onclick="window.print()" class="btn btn-success btn-sm fw-semibold">
      <i class="bi bi-printer me-1"></i> Print Label Sheet
    </button>
    <a href="/items" class="btn btn-outline-secondary btn-sm">Back to Items</a>
  </div>
</div>

<form action="/barcodes" method="GET" class="card shadow-sm border-0 mb-3 d-print-none">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label small fw-semibold mb-1">Select Product</label>
        <select name="item_id" class="form-select form-select-sm">
          <?php foreach ($items as $it): ?>
            <option value="<?= (int)$it['id'] ?>" <?= ($selectedItem && (int)$selectedItem['id'] === (int)$it['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>
              (Code: <?= htmlspecialchars($it['item_code'] ?: ('ITM-' . str_pad((string)$it['id'], 5, '0', STR_PAD_LEFT)), ENT_QUOTES, 'UTF-8') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold mb-1">Labels Count (1–60)</label>
        <input type="number" name="qty" class="form-control form-control-sm" min="1" max="60" value="<?= (int)$labelQty ?>">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary btn-sm w-100">Generate Labels</button>
      </div>
    </div>
  </div>
</form>

<?php if (empty($previewLabels)): ?>
  <div class="card border-0 shadow-sm p-4 text-center text-muted">
    Add at least one item in Items &amp; Inventory to print barcode labels.
  </div>
<?php else: ?>
  <div class="row g-2" id="barcodeSheet">
    <?php foreach ($previewLabels as $lbl): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="border rounded bg-white p-2 text-center shadow-sm" style="break-inside: avoid;">
          <div class="small fw-bold text-truncate"><?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="small text-truncate mb-1"><?= htmlspecialchars($lbl['name'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="d-flex justify-content-center my-1"><?= $lbl['svg'] ?></div>
          <div class="fw-bold small">MRP: <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$lbl['price'], 2) ?> / <?= htmlspecialchars($lbl['unit'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
