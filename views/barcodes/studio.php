<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4 no-print">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-upc-scan text-[#2B526E]"></i>
        <span>Barcode Label Printing Studio</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Generate crisp vector Code128B printable sticker labels (no PHP GD extension needed)</p>
    </div>
    <div class="flex items-center gap-2">
      <button type="button" onclick="window.print()" class="rf-btn rf-btn-sage-solid rf-btn-sm">
        <i class="bi bi-printer"></i> Print Label Sheet
      </button>
      <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Items</a>
    </div>
  </div>

  <form action="/barcodes" method="GET" class="rf-card p-5 no-print">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
      <div class="md:col-span-6">
        <label class="rf-label-plain text-xs">Select Product</label>
        <select name="item_id" class="rf-select rf-select-sm">
          <?php foreach ($items as $it): ?>
            <option value="<?= (int)$it['id'] ?>" <?= ($selectedItem && (int)$selectedItem['id'] === (int)$it['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>
              (Code: <?= htmlspecialchars($it['item_code'] ?: ('ITM-' . str_pad((string)$it['id'], 5, '0', STR_PAD_LEFT)), ENT_QUOTES, 'UTF-8') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">Labels Count (1–60)</label>
        <input type="number" name="qty" class="rf-input rf-input-sm tabular-nums" min="1" max="60" value="<?= (int)$labelQty ?>">
      </div>
      <div class="md:col-span-3">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm w-full">Generate Labels</button>
      </div>
    </div>
  </form>

  <?php if (empty($previewLabels)): ?>
    <div class="rf-card p-10 text-center text-sm text-[#78746C]">
      Add at least one item in Items &amp; Inventory to print barcode labels.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="barcodeSheet">
      <?php foreach ($previewLabels as $lbl): ?>
        <div class="border border-[#D4D1C9] rounded-xl bg-white p-3 text-center break-inside-avoid">
          <div class="text-[11px] font-semibold text-[#5C5A54] truncate"><?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="text-xs font-medium text-[#1C1B18] truncate mb-1"><?= htmlspecialchars($lbl['name'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="flex justify-center my-1"><?= $lbl['svg'] ?></div>
          <div class="text-xs font-semibold font-mono tabular-nums text-[#1C1B18]">
            MRP: <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$lbl['price'], 2) ?> / <?= htmlspecialchars($lbl['unit'], ENT_QUOTES, 'UTF-8') ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
