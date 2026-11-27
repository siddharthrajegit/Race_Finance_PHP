<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-truck text-[#2B526E]"></i>
        <span>Transfer Stock Between Warehouses</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Moves inventory atomically from source warehouse to destination warehouse</p>
    </div>
    <a href="/warehouses/transfers" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Transfer History</a>
  </div>

  <form action="/warehouses/transfers/create" method="POST" id="transferForm" class="space-y-5">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div class="rf-card">
      <div class="rf-card-body">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">Transfer Number <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" name="transfer_number" class="rf-input rf-input-sm font-mono" required value="<?= htmlspecialchars($nextTransferNumber ?? 'TRF-0001', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">Transfer Date <span class="text-[#8F2D2B]">*</span></label>
            <input type="date" name="transfer_date" class="rf-input rf-input-sm" required value="<?= date('Y-m-d') ?>">
          </div>
          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">From (Source Warehouse) <span class="text-[#8F2D2B]">*</span></label>
            <select name="source_warehouse_id" id="srcWh" class="rf-select rf-select-sm" required>
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= (int)$w['id'] ?>" <?= !empty($w['is_default']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?> (<?= number_format((float)$w['total_units'], 0) ?> units)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">To (Destination Warehouse) <span class="text-[#8F2D2B]">*</span></label>
            <select name="dest_warehouse_id" id="dstWh" class="rf-select rf-select-sm" required>
              <?php foreach ($warehouses as $idx => $w): ?>
                <option value="<?= (int)$w['id'] ?>" <?= ($idx === 1 || (empty($w['is_default']) && $idx === 0)) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="md:col-span-12">
            <label class="rf-label-plain text-xs">Notes / Dispatch Reference</label>
            <input type="text" name="notes" class="rf-input rf-input-sm" placeholder="Optional vehicle no., driver note, or reason for transfer">
          </div>
        </div>
      </div>
    </div>

    <div class="rf-card">
      <div class="rf-card-header">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Items to Transfer</h2>
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" id="addTransferRowBtn">
          <i class="bi bi-plus-circle"></i> Add Item Row
        </button>
      </div>
      <div class="rf-table-wrap">
        <table class="rf-table" id="transferItemsTable">
          <thead>
            <tr>
              <th>Product / Item</th>
              <th class="w-44">Quantity</th>
              <th class="w-12 text-center"></th>
            </tr>
          </thead>
          <tbody>
            <tr class="transfer-row">
              <td>
                <select name="item_id[]" class="rf-select rf-select-sm" required>
                  <option value="">-- Select Item --</option>
                  <?php foreach ($items as $it): ?>
                    <option value="<?= (int)$it['id'] ?>">
                      <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?> (Total Stock: <?= (float)$it['current_stock'] ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <input type="number" name="quantity[]" class="rf-input rf-input-sm text-right tabular-nums" min="0.01" step="0.01" required placeholder="0.00">
              </td>
              <td class="text-center">
                <button type="button" class="w-7 h-7 rounded-lg border border-[#E2DFD7] text-[#8F2D2B] hover:bg-[#FDF2F2] inline-flex items-center justify-center bg-white cursor-pointer btn-remove-row" title="Remove row">
                  <i class="bi bi-x"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="rf-card-footer justify-end gap-2">
        <a href="/warehouses" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-check2-circle"></i> Execute Stock Transfer
        </button>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#transferItemsTable tbody');
  document.getElementById('addTransferRowBtn').addEventListener('click', () => {
    const firstRow = tbody.querySelector('.transfer-row');
    const clone = firstRow.cloneNode(true);
    clone.querySelector('select').value = '';
    clone.querySelector('input').value = '';
    tbody.appendChild(clone);
  });
  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-remove-row');
    if (btn && tbody.querySelectorAll('.transfer-row').length > 1) {
      btn.closest('.transfer-row').remove();
    }
  });
  document.getElementById('transferForm').addEventListener('submit', (e) => {
    if (document.getElementById('srcWh').value === document.getElementById('dstWh').value) {
      e.preventDefault();
      alert('Source and destination warehouses must be different.');
    }
  });
});
</script>
