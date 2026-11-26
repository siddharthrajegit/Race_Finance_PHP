<?php
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_items', $userPermissions ?? [], true);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-calendar2-week text-primary me-2"></i>Batch &amp; Expiry Tracking (FEFO)</h3>
    <small class="text-muted">First-Expired, First-Out (FEFO) stock consumption &amp; expiry alert monitor</small>
  </div>
  <a href="/items" class="btn btn-outline-secondary btn-sm">Back to Items</a>
</div>

<?php if (!empty($expiring)): ?>
  <div class="alert alert-warning border-warning shadow-sm mb-3">
    <div class="fw-bold small mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Expiring Within 30 Days or Already Expired (<?= count($expiring) ?> batches)</div>
    <div class="d-flex flex-wrap gap-2">
      <?php foreach (array_slice($expiring, 0, 8) as $eb): ?>
        <span class="badge bg-white text-dark border border-warning">
          <?= htmlspecialchars($eb['item_name'], ENT_QUOTES, 'UTF-8') ?> &bull; Batch <?= htmlspecialchars($eb['batch_number'], ENT_QUOTES, 'UTF-8') ?>
          (Exp: <?= htmlspecialchars(formatDate($eb['expiry_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?>, Qty: <?= (float)$eb['remaining_qty'] ?>)
        </span>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-2 fw-bold small text-uppercase">Active Batches (Sorted by Earliest Expiry)</div>
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.86rem;">
          <thead class="table-light">
            <tr>
              <th>Item</th>
              <th>Batch #</th>
              <th>Mfg Date</th>
              <th>Expiry Date</th>
              <th>Warehouse</th>
              <th class="text-end">Remaining Qty</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($batches)): ?>
              <tr><td colspan="6" class="text-center py-4 text-muted">No product batches recorded yet.</td></tr>
            <?php else: foreach ($batches as $b): ?>
              <?php $isExp = !empty($b['expiry_date']) && $b['expiry_date'] < date('Y-m-d') && (float)$b['remaining_qty'] > 0; ?>
              <tr class="<?= $isExp ? 'table-danger' : '' ?>">
                <td class="fw-semibold"><?= htmlspecialchars($b['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($b['batch_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="small text-muted"><?= !empty($b['mfg_date']) ? htmlspecialchars(formatDate($b['mfg_date'], $dateFmt), ENT_QUOTES, 'UTF-8') : '--' ?></td>
                <td class="small fw-semibold <?= $isExp ? 'text-danger' : '' ?>"><?= !empty($b['expiry_date']) ? htmlspecialchars(formatDate($b['expiry_date'], $dateFmt), ENT_QUOTES, 'UTF-8') : 'No Expiry' ?></td>
                <td class="small text-muted"><?= htmlspecialchars($b['warehouse_name'] ?? 'Default', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end fw-bold"><?= number_format((float)$b['remaining_qty'], 2) ?> <?= htmlspecialchars($b['item_unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($canManage): ?>
    <div class="col-lg-4">
      <form action="/batches/create" method="POST" class="card shadow-sm border-0">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="card-header bg-white fw-bold small text-uppercase">Register New Batch</div>
        <div class="card-body p-3">
          <div class="mb-2">
            <label class="form-label small fw-semibold">Product <span class="text-danger">*</span></label>
            <select name="item_id" class="form-select form-select-sm" required>
              <option value="">-- Select Product --</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Batch Number <span class="text-danger">*</span></label>
            <input type="text" name="batch_number" class="form-control form-control-sm font-monospace" required placeholder="e.g. B-2026-A">
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label small fw-semibold">Mfg Date</label><input type="date" name="mfg_date" class="form-control form-control-sm"></div>
            <div class="col-6"><label class="form-label small fw-semibold">Expiry Date</label><input type="date" name="expiry_date" class="form-control form-control-sm"></div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label><input type="number" name="qty" class="form-control form-control-sm" min="0.01" step="0.01" required></div>
            <div class="col-6"><label class="form-label small fw-semibold">Unit Cost</label><input type="number" name="purchase_rate" class="form-control form-control-sm" min="0" step="0.01" value="0"></div>
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
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="syncBatchStock" name="sync_stock" value="1" checked>
            <label class="form-check-label small" for="syncBatchStock">Also add quantity to item stock</label>
          </div>
        </div>
        <div class="card-footer bg-light text-end py-2">
          <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Batch</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
