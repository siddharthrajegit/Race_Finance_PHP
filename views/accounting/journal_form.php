<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Post Manual Journal Entry</h3>
    <small class="text-muted">Total debits must equal total credits before posting</small>
  </div>
  <a href="/accounting/journals" class="btn btn-outline-secondary btn-sm">Back to Journals</a>
</div>

<form action="/accounting/journals/create" method="POST" id="manualJournalForm">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Entry #</label>
          <input type="text" name="entry_number" class="form-control form-control-sm font-monospace" required value="<?= htmlspecialchars($nextEntryNumber, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Entry Date <span class="text-danger">*</span></label>
          <input type="date" name="entry_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Reference #</label>
          <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="e.g. JV-CONTRA-01">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Narration <span class="text-danger">*</span></label>
          <input type="text" name="narration" class="form-control form-control-sm" required placeholder="Reason for manual adjustment or contra entry">
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
      <span class="fw-bold small text-uppercase text-secondary">Debit / Credit Lines</span>
      <button type="button" class="btn btn-outline-primary btn-sm" id="addJeLineBtn">
        <i class="bi bi-plus-circle me-1"></i> Add Line
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0" id="jeLinesTable">
        <thead class="table-light">
          <tr>
            <th>Ledger Account</th>
            <th>Line Description</th>
            <th style="width: 150px;">Debit</th>
            <th style="width: 150px;">Credit</th>
            <th style="width: 45px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php for ($r = 0; $r < 2; $r++): ?>
            <tr class="je-row">
              <td>
                <select name="account_id[]" class="form-select form-select-sm" required>
                  <option value="">-- Select Account --</option>
                  <?php foreach ($accounts as $acc): ?>
                    <option value="<?= (int)$acc['id'] ?>"><?= htmlspecialchars($acc['code'] . ' — ' . $acc['name'], ENT_QUOTES, 'UTF-8') ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="text" name="line_description[]" class="form-control form-control-sm" placeholder="Optional line note"></td>
              <td><input type="number" name="debit[]" class="form-control form-control-sm text-end je-debit" min="0" step="0.01" value="0.00"></td>
              <td><input type="number" name="credit[]" class="form-control form-control-sm text-end je-credit" min="0" step="0.01" value="0.00"></td>
              <td><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-je"><i class="bi bi-x"></i></button></td>
            </tr>
          <?php endfor; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="2" class="text-end">Totals:</td>
            <td class="text-end" id="jeTotalDebit">0.00</td>
            <td class="text-end" id="jeTotalCredit">0.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
      <span id="jeBalanceMsg" class="small fw-semibold text-muted">Enter equal debit &amp; credit amounts</span>
      <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold" id="btnPostJe" disabled>
        <i class="bi bi-check2-circle me-1"></i> Post Balanced Entry
      </button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#jeLinesTable tbody');
  const totDrEl = document.getElementById('jeTotalDebit');
  const totCrEl = document.getElementById('jeTotalCredit');
  const msgEl = document.getElementById('jeBalanceMsg');
  const submitBtn = document.getElementById('btnPostJe');

  function recalc() {
    let dr = 0, cr = 0;
    tbody.querySelectorAll('.je-debit').forEach(i => dr += parseFloat(i.value || 0));
    tbody.querySelectorAll('.je-credit').forEach(i => cr += parseFloat(i.value || 0));
    totDrEl.textContent = dr.toFixed(2);
    totCrEl.textContent = cr.toFixed(2);
    const diff = Math.abs(dr - cr);
    const ok = (dr > 0 && diff < 0.01);
    submitBtn.disabled = !ok;
    msgEl.textContent = ok ? 'Entry balanced and ready to post.' : `Difference: ${diff.toFixed(2)} (must be 0.00)`;
    msgEl.className = 'small fw-semibold ' + (ok ? 'text-success' : 'text-danger');
  }

  document.getElementById('addJeLineBtn').addEventListener('click', () => {
    const clone = tbody.querySelector('.je-row').cloneNode(true);
    clone.querySelector('select').value = '';
    clone.querySelectorAll('input').forEach(i => { i.value = i.type === 'number' ? '0.00' : ''; });
    tbody.appendChild(clone);
    recalc();
  });

  tbody.addEventListener('input', recalc);
  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-remove-je');
    if (btn && tbody.querySelectorAll('.je-row').length > 2) {
      btn.closest('.je-row').remove();
      recalc();
    }
  });
});
</script>
