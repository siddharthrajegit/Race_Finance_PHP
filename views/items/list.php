<?php
$itemCount = !empty($items) ? count($items) : 0;
$lowStock = $lowStockCount ?? 0;
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Items &amp; Inventory</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Track stock levels, pricing, GST tax rates, categories, and HSN codes</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <?php if (defined('ENABLE_BARCODE') && ENABLE_BARCODE): ?>
        <a href="/barcodes" class="rf-btn rf-btn-secondary rf-btn-sm"><i class="bi bi-upc-scan"></i> Barcodes</a>
      <?php endif; ?>
      <?php if (defined('ENABLE_WAREHOUSE') && ENABLE_WAREHOUSE): ?>
        <a href="/warehouses" class="rf-btn rf-btn-secondary rf-btn-sm"><i class="bi bi-buildings"></i> Warehouses</a>
      <?php endif; ?>
      <?php if (defined('ENABLE_BATCH_EXPIRY') && ENABLE_BATCH_EXPIRY): ?>
        <a href="/batches" class="rf-btn rf-btn-secondary rf-btn-sm"><i class="bi bi-calendar2-week"></i> Batches</a>
      <?php endif; ?>
      <?php if (defined('ENABLE_SERIAL_TRACKING') && ENABLE_SERIAL_TRACKING): ?>
        <a href="/serials" class="rf-btn rf-btn-secondary rf-btn-sm"><i class="bi bi-hash"></i> Serials</a>
      <?php endif; ?>
      <?php if (defined('ENABLE_BOM') && ENABLE_BOM): ?>
        <a href="/bom" class="rf-btn rf-btn-secondary rf-btn-sm"><i class="bi bi-boxes"></i> BOM</a>
      <?php endif; ?>
      <a href="/estimates/builder" class="rf-btn rf-btn-secondary">
        <i class="bi bi-file-earmark-spreadsheet"></i> Rate List &amp; Estimate
      </a>
      <a href="/items/create" class="rf-btn rf-btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Item
      </a>
    </div>
  </div>

  <!-- Search & Summary Bar -->
  <div class="rf-card p-4">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
      <div class="md:col-span-5 relative">
        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="text" class="rf-input pl-9 py-2 text-xs" placeholder="Quick search items by name, code, or HSN..." data-table-search="itemsTable">
      </div>
      <div class="md:col-span-3">
        <select class="rf-select rf-select-sm" id="categoryFilter" onchange="window.location='/items' + (this.value ? '?category=' + encodeURIComponent(this.value) : '')">
          <option value="" <?= empty($selectedCategory) ? 'selected' : '' ?>>All Categories</option>
          <option value="__none__" <?= ($selectedCategory === '__none__') ? 'selected' : '' ?>>Uncategorized</option>
          <?php foreach (($categories ?? []) as $cat): ?>
            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedCategory === $cat) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-4 flex flex-wrap items-center justify-start md:justify-end gap-2">
        <span class="rf-badge rf-badge-neutral py-1.5 px-3 tabular-nums">
          Total Products: <strong id="visibleRecordsCount_itemsTable" class="ml-1"><?= $itemCount ?></strong>
        </span>
        <?php if ($lowStock > 0): ?>
          <span class="rf-badge rf-badge-rose py-1.5 px-3 tabular-nums">
            <i class="bi bi-exclamation-triangle"></i> <?= (int)$lowStock ?> Low Stock
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Items Table -->
  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table" id="itemsTable">
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Item Code</th>
            <th>HSN/SAC</th>
            <th class="text-right">Sale Price</th>
            <th class="text-right">Purchase Price</th>
            <th class="text-center">GST Rate</th>
            <th class="text-center">Stock</th>
            <th class="w-12 text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $it): ?>
              <?php $isLowStock = (float)($it['current_stock'] ?? 0) <= (float)($it['low_stock_threshold'] ?? 0); ?>
              <tr>
                <td>
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-[#1C1B18]"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($it['category'])): ?>
                      <span class="rf-badge rf-badge-neutral text-[10px]"><?= htmlspecialchars($it['category'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($it['sub_unit']) && !empty($it['sub_unit_qty'])): ?>
                    <div class="text-xs text-[#2B526E] mt-0.5">
                      <i class="bi bi-box mr-1"></i>1 <?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?> = <?= (float)$it['sub_unit_qty'] ?> <?= htmlspecialchars($it['sub_unit'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($it['description'])): ?>
                    <div class="text-xs text-[#78746C] truncate max-w-[230px] mt-0.5"><?= htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($it['item_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold tabular-nums whitespace-nowrap">₹ <?= number_format((float)($it['sale_price'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums whitespace-nowrap">₹ <?= number_format((float)($it['purchase_price'] ?? 0), 2) ?></td>
                <td class="text-center">
                  <?php if ((float)($it['tax_rate'] ?? 0) > 0): ?>
                    <span class="rf-badge rf-badge-slate tabular-nums"><?= htmlspecialchars((string)$it['tax_rate'], ENT_QUOTES, 'UTF-8') ?>%</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-neutral tabular-nums">0%</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="rf-badge <?= $isLowStock ? 'rf-badge-rose' : 'rf-badge-sage' ?> tabular-nums">
                    <?= htmlspecialchars((string)($it['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </td>
                <td class="text-center">
                  <div class="rf-dropdown" data-ui-dropdown>
                    <button class="w-8 h-8 rounded-full hover:bg-[#EFECE6] inline-flex items-center justify-center text-[#5C5A54] bg-transparent border-0 cursor-pointer transition-colors" type="button" data-ui-dropdown-toggle aria-expanded="false" title="Actions">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="rf-dropdown-menu" data-ui-dropdown-menu>
                      <button type="button" class="rf-dropdown-item" data-ui-modal="#adjustStockModal<?= $it['id'] ?>">
                        <i class="bi bi-sliders text-[#5C5A54]"></i> Adjust Stock
                      </button>
                      <a class="rf-dropdown-item" href="/items/edit/<?= $it['id'] ?>">
                        <i class="bi bi-pencil text-[#2B526E]"></i> Edit Item
                      </a>
                      <div class="rf-dropdown-divider"></div>
                      <form action="/items/delete/<?= $it['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
                          <i class="bi bi-trash"></i> Delete Item
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-14 text-[#78746C]">
                <i class="bi bi-box-seam text-3xl block mb-2 text-[#B5B0A6]"></i>
                No items added yet. Click <strong class="text-[#1C1B18]">Add New Item</strong> to create your product inventory.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick Stock Adjust Modals (placed outside table overflow) -->
<?php if (!empty($items)): ?>
  <?php foreach ($items as $it): ?>
    <div class="rf-modal hidden" id="adjustStockModal<?= $it['id'] ?>" tabindex="-1" aria-hidden="true">
      <div class="rf-modal-dialog max-w-sm">
        <form action="/items/adjust/<?= $it['id'] ?>" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="rf-modal-header">
            <h3 class="rf-modal-title text-sm">Adjust Stock: <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <button type="button" class="text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" data-ui-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
          </div>
          <div class="rf-modal-body space-y-4">
            <p class="text-xs text-[#5C5A54]">Current Stock: <strong class="text-[#1C1B18] tabular-nums"><?= htmlspecialchars((string)($it['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if (!empty($warehouses) && count($warehouses) > 1): ?>
              <div>
                <label class="rf-label-plain text-xs">Warehouse</label>
                <select name="warehouse_id" class="rf-select rf-select-sm">
                  <?php foreach ($warehouses as $wh): ?>
                    <option value="<?= (int)$wh['id'] ?>" <?= !empty($wh['is_default']) ? 'selected' : '' ?>><?= htmlspecialchars($wh['name'], ENT_QUOTES, 'UTF-8') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>
            <div>
              <label class="rf-label-plain text-xs">Action</label>
              <select name="action" class="rf-select rf-select-sm">
                <option value="add">Add (+ Increase Stock)</option>
                <option value="reduce">Reduce (- Damage / Return)</option>
              </select>
            </div>
            <div>
              <label class="rf-label-plain text-xs">Quantity (<?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</label>
              <input type="number" name="adjustment" class="rf-input rf-input-sm tabular-nums" min="0.01" step="any" placeholder="e.g. 5" required>
            </div>
          </div>
          <div class="rf-modal-footer">
            <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Cancel</button>
            <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Update Stock</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
