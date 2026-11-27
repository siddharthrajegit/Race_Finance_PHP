<?php
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_items', $userPermissions ?? [], true);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-calendar2-week text-[#2B526E]"></i>
        <span>Batch &amp; Expiry Tracking (FEFO)</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">First-Expired, First-Out (FEFO) stock consumption &amp; expiry alert monitor</p>
    </div>
    <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Items</a>
  </div>

  <?php if (!empty($expiring)): ?>
    <div class="rf-alert rf-alert-warning mb-0 flex-col items-start gap-2">
      <div class="text-xs font-semibold flex items-center gap-1.5">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>Expiring Within 30 Days or Already Expired (<?= count($expiring) ?> batches)</span>
      </div>
      <div class="flex flex-wrap gap-1.5">
        <?php foreach (array_slice($expiring, 0, 8) as $eb): ?>
          <span class="rf-badge rf-badge-neutral bg-white border-[#E6D5AD]">
            <?= htmlspecialchars($eb['item_name'], ENT_QUOTES, 'UTF-8') ?> &bull; Batch <?= htmlspecialchars($eb['batch_number'], ENT_QUOTES, 'UTF-8') ?>
            (Exp: <?= htmlspecialchars(formatDate($eb['expiry_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?>, Qty: <?= (float)$eb['remaining_qty'] ?>)
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
    <div class="<?= $canManage ? 'lg:col-span-8' : 'lg:col-span-12' ?> rf-card">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Active Batches (Sorted by Earliest Expiry)</h2>
      </div>
      <div class="rf-table-wrap">
        <table class="rf-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Batch #</th>
              <th>Mfg Date</th>
              <th>Expiry Date</th>
              <th>Warehouse</th>
              <th class="text-right">Remaining Qty</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($batches)): ?>
              <tr>
                <td colspan="6" class="text-center py-10 text-[#78746C]">No product batches recorded yet.</td>
              </tr>
            <?php else: foreach ($batches as $b): ?>
              <?php $isExp = !empty($b['expiry_date']) && $b['expiry_date'] < date('Y-m-d') && (float)$b['remaining_qty'] > 0; ?>
              <tr class="<?= $isExp ? 'bg-[#FDF2F2]' : '' ?>">
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($b['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono font-semibold text-[#2B526E]"><?= htmlspecialchars($b['batch_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#78746C]"><?= !empty($b['mfg_date']) ? htmlspecialchars(formatDate($b['mfg_date'], $dateFmt), ENT_QUOTES, 'UTF-8') : '--' ?></td>
                <td class="text-xs font-semibold <?= $isExp ? 'text-[#8F2D2B]' : 'text-[#1C1B18]' ?>">
                  <?= !empty($b['expiry_date']) ? htmlspecialchars(formatDate($b['expiry_date'], $dateFmt), ENT_QUOTES, 'UTF-8') : 'No Expiry' ?>
                </td>
                <td class="text-xs text-[#78746C]"><?= htmlspecialchars($b['warehouse_name'] ?? 'Default', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold tabular-nums"><?= number_format((float)$b['remaining_qty'], 2) ?> <?= htmlspecialchars($b['item_unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($canManage): ?>
      <form action="/batches/create" method="POST" class="lg:col-span-4 rf-card">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="rf-card-header py-3">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Register New Batch</h2>
        </div>
        <div class="rf-card-body space-y-3.5">
          <div>
            <label class="rf-label-plain text-xs">Product <span class="text-[#8F2D2B]">*</span></label>
            <select name="item_id" class="rf-select rf-select-sm" required>
              <option value="">-- Select Product --</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="rf-label-plain text-xs">Batch Number <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" name="batch_number" class="rf-input rf-input-sm font-mono" required placeholder="e.g. B-2026-A">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="rf-label-plain text-xs">Mfg Date</label>
              <input type="date" name="mfg_date" class="rf-input rf-input-sm">
            </div>
            <div>
              <label class="rf-label-plain text-xs">Expiry Date</label>
              <input type="date" name="expiry_date" class="rf-input rf-input-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="rf-label-plain text-xs">Quantity <span class="text-[#8F2D2B]">*</span></label>
              <input type="number" name="qty" class="rf-input rf-input-sm text-right tabular-nums" min="0.01" step="0.01" required>
            </div>
            <div>
              <label class="rf-label-plain text-xs">Unit Cost</label>
              <input type="number" name="purchase_rate" class="rf-input rf-input-sm text-right tabular-nums" min="0" step="0.01" value="0">
            </div>
          </div>
          <?php if (count($warehouses) > 1): ?>
            <div>
              <label class="rf-label-plain text-xs">Warehouse</label>
              <select name="warehouse_id" class="rf-select rf-select-sm">
                <?php foreach ($warehouses as $w): ?>
                  <option value="<?= (int)$w['id'] ?>"><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <label class="flex items-center gap-2 text-xs text-[#1C1B18] cursor-pointer pt-1" for="syncBatchStock">
            <input class="w-4 h-4 rounded border-[#D4D1C9] accent-[#1C1B18]" type="checkbox" id="syncBatchStock" name="sync_stock" value="1" checked>
            <span>Also add quantity to item stock</span>
          </label>
        </div>
        <div class="rf-card-footer justify-end py-3">
          <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Save Batch</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
