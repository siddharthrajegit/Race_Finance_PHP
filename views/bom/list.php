<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-boxes text-[#2B526E]"></i>
        <span>Manufacturing &amp; Bill of Materials (BOM)</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Define assembly recipes and run atomic production orders consuming raw materials</p>
    </div>
    <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Items</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
    <!-- Execute Production Order -->
    <form action="/bom/produce" method="POST" class="lg:col-span-5 rf-card flex flex-col">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#2E5A36]">Execute Production Order</h2>
      </div>
      <div class="rf-card-body space-y-3.5 flex-1">
        <div>
          <label class="rf-label-plain text-xs">Select BOM / Recipe <span class="text-[#8F2D2B]">*</span></label>
          <select name="bom_id" class="rf-select rf-select-sm" required>
            <option value="">-- Select BOM --</option>
            <?php foreach ($boms as $b): ?>
              <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['bom_name'] . ' -> ' . $b['finished_item_name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="rf-label-plain text-xs">Quantity to Produce <span class="text-[#8F2D2B]">*</span></label>
            <input type="number" name="produced_qty" class="rf-input rf-input-sm text-right tabular-nums" min="0.01" step="0.01" required placeholder="10">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Production Date</label>
            <input type="date" name="production_date" class="rf-input rf-input-sm" value="<?= date('Y-m-d') ?>">
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
        <div>
          <label class="rf-label-plain text-xs">Notes</label>
          <input type="text" name="notes" class="rf-input rf-input-sm" placeholder="Shift / batch note">
        </div>
      </div>
      <div class="rf-card-footer justify-end py-3">
        <button type="submit" class="rf-btn rf-btn-sage-solid rf-btn-sm" <?= empty($boms) ? 'disabled' : '' ?>>
          Run Production &amp; Update Stock
        </button>
      </div>
    </form>

    <!-- Create / Update BOM Recipe -->
    <form action="/bom/save" method="POST" class="lg:col-span-7 rf-card flex flex-col">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#2B526E]">Create / Update BOM Recipe</h2>
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5 text-xs" id="addBomCompBtn">+ Component</button>
      </div>
      <div class="rf-card-body space-y-4 flex-1">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
          <div class="md:col-span-5">
            <label class="rf-label-plain text-xs">Finished Product <span class="text-[#8F2D2B]">*</span></label>
            <select name="finished_item_id" class="rf-select rf-select-sm" required>
              <option value="">-- Finished Item --</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">BOM Name</label>
            <input type="text" name="bom_name" class="rf-input rf-input-sm" placeholder="e.g. Standard Assembly">
          </div>
          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">Batch Output Qty</label>
            <input type="number" name="output_qty" class="rf-input rf-input-sm text-right tabular-nums" step="0.01" min="0.01" value="1">
          </div>
        </div>

        <div class="rf-table-wrap border border-[#E2DFD7] rounded-xl overflow-hidden">
          <table class="rf-table" id="bomCompTable">
            <thead>
              <tr>
                <th>Raw Material Component</th>
                <th class="w-40 text-right">Qty Per Output Batch</th>
                <th class="w-12 text-center"></th>
              </tr>
            </thead>
            <tbody>
              <tr class="bom-comp-row">
                <td>
                  <select name="component_item_id[]" class="rf-select rf-select-sm" required>
                    <option value="">-- Select Raw Material --</option>
                    <?php foreach ($items as $it): ?>
                      <option value="<?= (int)$it['id'] ?>">
                        <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?> (Stock: <?= (float)$it['current_stock'] ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <input type="number" name="quantity_per_unit[]" class="rf-input rf-input-sm text-right tabular-nums" step="0.001" min="0.001" required placeholder="1">
                </td>
                <td class="text-center">
                  <button type="button" class="w-7 h-7 rounded-lg border border-[#E2DFD7] text-[#8F2D2B] hover:bg-[#FDF2F2] inline-flex items-center justify-center bg-white cursor-pointer btn-rem-bom" title="Remove component">
                    <i class="bi bi-x"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="rf-card-footer justify-end py-3">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Save BOM Recipe</button>
      </div>
    </form>
  </div>

  <!-- Saved BOMs -->
  <div class="rf-card">
    <div class="rf-card-header py-3">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Saved BOM Recipes (<?= count($boms) ?>)</h2>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>BOM Name</th>
            <th>Finished Good</th>
            <th>Output Batch</th>
            <th>Components</th>
            <th>Finished Stock</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($boms)): ?>
            <tr><td colspan="6" class="text-center py-8 text-[#78746C]">No BOM recipes defined yet.</td></tr>
          <?php else: foreach ($boms as $b): ?>
            <tr>
              <td class="font-semibold text-[#1C1B18]"><?= htmlspecialchars($b['bom_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($b['finished_item_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="tabular-nums"><?= (float)$b['output_qty'] ?> <?= htmlspecialchars($b['finished_item_unit'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$b['component_count'] ?> item(s)</td>
              <td class="tabular-nums"><?= number_format((float)$b['finished_current_stock'], 2) ?> <?= htmlspecialchars($b['finished_item_unit'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-right">
                <form action="/bom/delete/<?= (int)$b['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Delete BOM <?= htmlspecialchars($b['bom_name'], ENT_QUOTES, 'UTF-8') ?>?">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="rf-btn rf-btn-danger rf-btn-sm py-1 px-2.5 text-xs">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Production Order History -->
  <div class="rf-card">
    <div class="rf-card-header py-3">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Production Orders Log</h2>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Date</th>
            <th>Finished Product</th>
            <th class="text-right">Produced Qty</th>
            <th class="text-right">Component Cost</th>
            <th class="text-right">Unit Cost</th>
            <th>Warehouse</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="7" class="text-center py-8 text-[#78746C]">No production runs recorded yet.</td></tr>
          <?php else: foreach ($orders as $o): ?>
            <tr>
              <td class="font-mono font-semibold text-[#2E5A36]">#<?= htmlspecialchars($o['order_number'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars(formatDate($o['production_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($o['finished_item_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-right font-semibold tabular-nums"><?= number_format((float)$o['produced_qty'], 2) ?> <?= htmlspecialchars($o['finished_item_unit'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-right font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$o['total_component_cost'], 2) ?></td>
              <td class="text-right font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$o['unit_cost'], 2) ?></td>
              <td class="text-xs text-[#78746C]"><?= htmlspecialchars($o['warehouse_name'] ?? 'Default', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#bomCompTable tbody');
  document.getElementById('addBomCompBtn').addEventListener('click', () => {
    const clone = tbody.querySelector('.bom-comp-row').cloneNode(true);
    clone.querySelector('select').value = '';
    clone.querySelector('input').value = '';
    tbody.appendChild(clone);
  });
  tbody.addEventListener('click', e => {
    const btn = e.target.closest('.btn-rem-bom');
    if (btn && tbody.querySelectorAll('.bom-comp-row').length > 1) {
      btn.closest('.bom-comp-row').remove();
    }
  });
});
</script>
