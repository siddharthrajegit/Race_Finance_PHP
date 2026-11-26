<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-boxes text-primary me-2"></i>Manufacturing &amp; Bill of Materials (BOM)</h3>
    <small class="text-muted">Define assembly recipes and run atomic production orders consuming raw materials</small>
  </div>
  <a href="/items" class="btn btn-outline-secondary btn-sm">Back to Items</a>
</div>

<div class="row g-3 mb-3">
  <!-- Run Production Order -->
  <div class="col-lg-5">
    <form action="/bom/produce" method="POST" class="card shadow-sm border-0 h-100">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="card-header bg-white fw-bold small text-uppercase text-success">Execute Production Order</div>
      <div class="card-body p-3">
        <div class="mb-2">
          <label class="form-label small fw-semibold">Select BOM / Recipe <span class="text-danger">*</span></label>
          <select name="bom_id" class="form-select form-select-sm" required>
            <option value="">-- Select BOM --</option>
            <?php foreach ($boms as $b): ?>
              <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['bom_name'] . ' -> ' . $b['finished_item_name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label small fw-semibold">Quantity to Produce <span class="text-danger">*</span></label>
            <input type="number" name="produced_qty" class="form-control form-control-sm" min="0.01" step="0.01" required placeholder="10">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Production Date</label>
            <input type="date" name="production_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <?php if (count($warehouses) > 1): ?>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Warehouse</label>
            <select name="warehouse_id" class="form-select form-select-sm">
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= (int)$w['id'] ?>"><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <div>
          <label class="form-label small fw-semibold">Notes</label>
          <input type="text" name="notes" class="form-control form-control-sm" placeholder="Shift / batch note">
        </div>
      </div>
      <div class="card-footer bg-light text-end py-2">
        <button type="submit" class="btn btn-success btn-sm fw-semibold" <?= empty($boms) ? 'disabled' : '' ?>>Run Production &amp; Update Stock</button>
      </div>
    </form>
  </div>

  <!-- Create / Update BOM Recipe -->
  <div class="col-lg-7">
    <form action="/bom/save" method="POST" class="card shadow-sm border-0 h-100">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="card-header bg-white fw-bold small text-uppercase text-primary d-flex justify-content-between align-items-center">
        <span>Create / Update BOM Recipe</span>
        <button type="button" class="btn btn-outline-primary btn-sm py-0" id="addBomCompBtn">+ Component</button>
      </div>
      <div class="card-body p-3">
        <div class="row g-2 mb-3">
          <div class="col-md-5">
            <label class="form-label small fw-semibold">Finished Product <span class="text-danger">*</span></label>
            <select name="finished_item_id" class="form-select form-select-sm" required>
              <option value="">-- Finished Item --</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">BOM Name</label>
            <input type="text" name="bom_name" class="form-control form-control-sm" placeholder="e.g. Standard Assembly">
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Batch Output Qty</label>
            <input type="number" name="output_qty" class="form-control form-control-sm" step="0.01" min="0.01" value="1">
          </div>
        </div>

        <table class="table table-sm mb-0" id="bomCompTable">
          <thead class="table-light"><tr><th>Raw Material Component</th><th style="width:140px;">Qty Per Output Batch</th><th style="width:45px;"></th></tr></thead>
          <tbody>
            <tr class="bom-comp-row">
              <td>
                <select name="component_item_id[]" class="form-select form-select-sm" required>
                  <option value="">-- Select Raw Material --</option>
                  <?php foreach ($items as $it): ?>
                    <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?> (Stock: <?= (float)$it['current_stock'] ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="number" name="quantity_per_unit[]" class="form-control form-control-sm" step="0.001" min="0.001" required placeholder="1"></td>
              <td><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-rem-bom"><i class="bi bi-x"></i></button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer bg-light text-end py-2">
        <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save BOM Recipe</button>
      </div>
    </form>
  </div>
</div>

<!-- Saved BOMs -->
<div class="card shadow-sm border-0 mb-3">
  <div class="card-header bg-white fw-bold small text-uppercase">Saved BOM Recipes (<?= count($boms) ?>)</div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0" style="font-size: 0.86rem;">
      <thead class="table-light"><tr><th>BOM Name</th><th>Finished Good</th><th>Output Batch</th><th>Components</th><th>Finished Stock</th><th class="text-end">Action</th></tr></thead>
      <tbody>
        <?php if (empty($boms)): ?>
          <tr><td colspan="6" class="text-center py-3 text-muted">No BOM recipes defined yet.</td></tr>
        <?php else: foreach ($boms as $b): ?>
          <tr>
            <td class="fw-bold"><?= htmlspecialchars($b['bom_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($b['finished_item_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (float)$b['output_qty'] ?> <?= htmlspecialchars($b['finished_item_unit'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$b['component_count'] ?> item(s)</td>
            <td><?= number_format((float)$b['finished_current_stock'], 2) ?> <?= htmlspecialchars($b['finished_item_unit'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end">
              <form action="/bom/delete/<?= (int)$b['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Delete BOM <?= htmlspecialchars($b['bom_name'], ENT_QUOTES, 'UTF-8') ?>?">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Production Order History -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-white fw-bold small text-uppercase">Production Orders Log</div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0" style="font-size: 0.86rem;">
      <thead class="table-light"><tr><th>Order #</th><th>Date</th><th>Finished Product</th><th class="text-end">Produced Qty</th><th class="text-end">Component Cost</th><th class="text-end">Unit Cost</th><th>Warehouse</th></tr></thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="7" class="text-center py-3 text-muted">No production runs recorded yet.</td></tr>
        <?php else: foreach ($orders as $o): ?>
          <tr>
            <td class="font-monospace fw-bold text-success">#<?= htmlspecialchars($o['order_number'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars(formatDate($o['production_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($o['finished_item_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end fw-bold"><?= number_format((float)$o['produced_qty'], 2) ?> <?= htmlspecialchars($o['finished_item_unit'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$o['total_component_cost'], 2) ?></td>
            <td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$o['unit_cost'], 2) ?></td>
            <td class="small text-muted"><?= htmlspecialchars($o['warehouse_name'] ?? 'Default', ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
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
