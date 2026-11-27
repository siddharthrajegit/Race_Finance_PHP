<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-shop text-[#2E5A36]"></i>
        <span>Online Product Catalogue</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Share a clean read-only product &amp; rate link with WhatsApp inquiry checkout</p>
    </div>
    <?php if (!empty($catSettings['is_published'])): ?>
      <a href="/c/<?= htmlspecialchars($catSettings['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="rf-btn rf-btn-sage rf-btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> Open Public Storefront
      </a>
    <?php endif; ?>
  </div>

  <div class="rf-card max-w-3xl">
    <form action="/catalogue" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-card-body space-y-5">
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-[#ECEAE4]">
          <div>
            <div class="text-sm font-semibold text-[#1C1B18]">Publish Online Catalogue</div>
            <div class="text-xs text-[#78746C] mt-0.5">Make product catalog accessible via public link without requiring login</div>
          </div>
          <label class="rf-switch">
            <input type="checkbox" name="is_published" value="1" <?= !empty($catSettings['is_published']) ? 'checked' : '' ?>>
            <span class="rf-switch-thumb"></span>
          </label>
        </div>

        <div>
          <label class="rf-label-plain text-xs">Public URL Slug <span class="text-[#8F2D2B]">*</span></label>
          <div class="flex flex-col sm:flex-row sm:items-stretch">
            <span class="inline-flex items-center px-3 py-2 sm:py-0 bg-[#F3F1EC] border border-b-0 sm:border-b sm:border-r-0 border-[#D9D6CE] rounded-t-lg sm:rounded-l-lg sm:rounded-tr-none text-xs text-[#5C5A54] font-mono break-all"><?= htmlspecialchars(rtrim(APP_URL, '/') . '/c/', ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="slug" class="rf-input rf-input-sm rounded-t-none sm:rounded-t-lg sm:rounded-l-none font-mono flex-1 min-w-0" required value="<?= htmlspecialchars($catSettings['slug'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <div>
          <label class="rf-label-plain text-xs">Storefront Headline</label>
          <input type="text" name="headline" class="rf-input rf-input-sm" value="<?= htmlspecialchars($catSettings['headline'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
          <label class="rf-label-plain text-xs">Store Description / Delivery Info</label>
          <textarea name="description" class="rf-textarea text-sm" rows="2" placeholder="Wholesale & retail inquiry catalogue"><?= htmlspecialchars($catSettings['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div>
          <label class="rf-label-plain text-xs">WhatsApp Order / Inquiry Number (with or without 91)</label>
          <input type="text" name="whatsapp_number" class="rf-input rf-input-sm" value="<?= htmlspecialchars($catSettings['whatsapp_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 9876543210">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
          <label class="inline-flex items-center gap-2 text-xs text-[#1C1B18] cursor-pointer">
            <input class="rf-checkbox" type="checkbox" id="showPricesChk" name="show_prices" value="1" <?= !empty($catSettings['show_prices']) ? 'checked' : '' ?>>
            <span>Show Prices</span>
          </label>
          <label class="inline-flex items-center gap-2 text-xs text-[#1C1B18] cursor-pointer">
            <input class="rf-checkbox" type="checkbox" id="showStockChk" name="show_stock_badge" value="1" <?= !empty($catSettings['show_stock_badge']) ? 'checked' : '' ?>>
            <span>Show Stock Status</span>
          </label>
          <label class="inline-flex items-center gap-2 text-xs text-[#1C1B18] cursor-pointer">
            <input class="rf-checkbox" type="checkbox" id="hideOutChk" name="hide_out_of_stock" value="1" <?= !empty($catSettings['hide_out_of_stock']) ? 'checked' : '' ?>>
            <span>Hide Out-of-Stock Items</span>
          </label>
        </div>
      </div>
      <div class="rf-card-footer justify-end">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Save Catalogue Settings</button>
      </div>
    </form>
  </div>
</div>
