<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right text-primary me-2"></i>New Stock Transfer</h3>
    <small class="text-muted">Move stock safely between warehouses inside an atomic transaction</small>
  </div>
  <a href="/warehouses/transfers" class="btn btn-outline-secondary btn-sm">Back to Transfers</a>
</div>

<form action="/warehouses/transfers/create" method="POST" id="transferForm">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-sm-6 col-md-3">
          <label class="form-label small fw-semibold">Transfer # <span class="text-danger">*</span></label>
          <input type="text" name="transfer_number" class="form-control form-control-sm font-monospace" required value="<?= htmlspecialchars($nextTransferNumber, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-sm-6 col-md-3">
          <label class="form-label small fw-semibold">Transfer Date <span class="text-danger">*</span></label>
          <input type="date" name="transfer_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-sm-6 col-md-3">
          <label class="form-label small fw-semibold">Source Warehouse (From) <span class="text-danger">*</span></label>
          <select name="source_warehouse_id" class="form-select form-select-sm" required>
            <?php foreach ($warehouses as $w): ?>
              <option value="<?= (int)$w['id'] ?>" <?= !empty($w['is_default']) ? 'selected' : '' ?>><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-6 col-md-3">
          <label class="form-label small fw-semibold">Destination Warehouse (To) <span class="text-danger">*</span></label>
          <select name="dest_warehouse_id" class="form-select form-select-sm" required>
            <?php foreach ($warehouses as $idx => $w): ?>
              <option value="<?= (int)$w['id'] ?>" <?= ($idx === 1) ? 'selected' : '' ?>><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Notes</label>
          <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason or vehicle / challan reference">
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
      <span class="fw-bold small text-uppercase text-secondary">Items to Transfer</span>
      <button type="button" class="btn btn-outline-primary btn-sm" id="addTransferRowBtn">
        <i class="bi bi-plus-circle me-1"></i> Add Row
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0" id="transferItemsTable">
        <thead class="table-light">
          <tr>
            <th>Item</th>
            <th style="width: 180px;">Quantity</th>
            <th style="width: 60px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr class="transfer-row">
            <td>
              <select name="item_id[]" class="form-select form-select-sm" required>
                <option value="">-- Select Item --</option>
                <?php foreach ($items as $it): ?>
                  <option value="<?= (int)$it['id'] ?>">
                    <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?> (Total Stock: <?= number_format((float)$it['current_stock'], 2) ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="quantity[]" class="form-control form-control-sm" min="0.01" step="any" required placeholder="0.00"></td>
            <td class="text-center">
              <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-row" title="Remove"><i class="bi bi-x"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-light p-3 text-end">
      <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> Complete Transfer
      </button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#transferItemsTable tbody');
  document.getElementById('addTransferRowBtn').addEventListener('click', () => {
    const first = tbody.querySelector('.transfer-row');
    const clone = first.cloneNode(true);
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
});
</script>
