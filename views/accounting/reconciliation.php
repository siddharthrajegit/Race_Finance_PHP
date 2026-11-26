<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_accounting', $userPermissions ?? [], true);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-bank text-warning me-2"></i>Bank Reconciliation (CSV Import)</h3>
    <small class="text-muted">Upload bank statement CSVs and manually match entries against recorded payments &amp; expenses (no live bank APIs required)</small>
  </div>
  <a href="/accounting" class="btn btn-outline-secondary btn-sm">Accounting Hub</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2"><div class="small text-muted">Total Lines</div><div class="fs-5 fw-bold"><?= (int)$summary['total_lines'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2"><div class="small text-muted">Matched</div><div class="fs-5 fw-bold text-success"><?= (int)$summary['matched_count'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2"><div class="small text-muted">Unmatched</div><div class="fs-5 fw-bold text-danger"><?= (int)$summary['unmatched_count'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2"><div class="small text-muted">Ignored</div><div class="fs-5 fw-bold text-secondary"><?= (int)$summary['ignored_count'] ?></div></div></div></div>
</div>

<?php if ($canManage): ?>
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <form action="/accounting/reconciliation/import" method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 h-100">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="card-header bg-white small fw-bold text-uppercase">Import Bank Statement CSV</div>
        <div class="card-body p-3">
          <input type="file" name="statement_csv" class="form-control form-control-sm mb-2" accept=".csv,.txt" required>
          <div class="small text-muted">CSV headers detected automatically: Date, Description/Narration, Reference, Debit/Withdrawal, Credit/Deposit, Balance.</div>
        </div>
        <div class="card-footer bg-light text-end py-2">
          <button type="submit" class="btn btn-primary btn-sm">Upload &amp; Import CSV</button>
        </div>
      </form>
    </div>

    <div class="col-md-6">
      <form action="/accounting/reconciliation/manual-line" method="POST" class="card shadow-sm border-0 h-100">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="card-header bg-white small fw-bold text-uppercase">Quick Add Bank Entry</div>
        <div class="card-body p-3">
          <div class="row g-2">
            <div class="col-4"><input type="date" name="txn_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
            <div class="col-8"><input type="text" name="description" class="form-control form-control-sm" required placeholder="Bank narration / UTR note"></div>
            <div class="col-4"><input type="text" name="reference_no" class="form-control form-control-sm" placeholder="Ref / UTR"></div>
            <div class="col-4"><input type="number" name="debit_amount" class="form-control form-control-sm" step="0.01" min="0" placeholder="Debit (Out)"></div>
            <div class="col-4"><input type="number" name="credit_amount" class="form-control form-control-sm" step="0.01" min="0" placeholder="Credit (In)"></div>
          </div>
        </div>
        <div class="card-footer bg-light text-end py-2">
          <button type="submit" class="btn btn-outline-primary btn-sm">Add Line</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
    <span class="fw-bold small text-uppercase">Statement Entries</span>
    <div class="btn-group btn-group-sm">
      <a href="/accounting/reconciliation" class="btn <?= empty($filters['match_status']) ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
      <a href="/accounting/reconciliation?match_status=unmatched" class="btn <?= ($filters['match_status'] ?? '') === 'unmatched' ? 'btn-danger' : 'btn-outline-danger' ?>">Unmatched</a>
      <a href="/accounting/reconciliation?match_status=matched" class="btn <?= ($filters['match_status'] ?? '') === 'matched' ? 'btn-success' : 'btn-outline-success' ?>">Matched</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Description &amp; Reference</th>
          <th class="text-end">Debit (Out)</th>
          <th class="text-end">Credit (In)</th>
          <th>Status</th>
          <?php if ($canManage): ?><th class="text-end">Reconcile</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($lines)): ?>
          <tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center py-4 text-muted">No bank statement lines imported yet.</td></tr>
        <?php else: ?>
          <?php foreach ($lines as $ln): ?>
            <tr>
              <td class="text-nowrap"><?= htmlspecialchars(formatDate($ln['txn_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($ln['description'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($ln['reference_no'])): ?>
                  <small class="text-muted font-monospace">Ref: <?= htmlspecialchars($ln['reference_no'], ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>
              </td>
              <td class="text-end text-danger"><?= (float)$ln['debit_amount'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['debit_amount'], 2) : '--' ?></td>
              <td class="text-end text-success"><?= (float)$ln['credit_amount'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['credit_amount'], 2) : '--' ?></td>
              <td>
                <?php if ($ln['match_status'] === 'matched'): ?>
                  <span class="badge bg-success">Matched (<?= htmlspecialchars((string)$ln['matched_source_type'] . ' #' . $ln['matched_source_id'], ENT_QUOTES, 'UTF-8') ?>)</span>
                <?php elseif ($ln['match_status'] === 'ignored'): ?>
                  <span class="badge bg-secondary">Ignored</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border">Unmatched</span>
                <?php endif; ?>
              </td>
              <?php if ($canManage): ?>
                <td class="text-end">
                  <?php if ($ln['match_status'] === 'unmatched'): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 btn-match-modal" data-line-id="<?= (int)$ln['id'] ?>" data-desc="<?= htmlspecialchars($ln['description'], ENT_QUOTES, 'UTF-8') ?>">
                      Match
                    </button>
                    <form action="/accounting/reconciliation/status" method="POST" class="d-inline">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="line_id" value="<?= (int)$ln['id'] ?>">
                      <input type="hidden" name="status" value="ignored">
                      <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2">Ignore</button>
                    </form>
                  <?php else: ?>
                    <form action="/accounting/reconciliation/status" method="POST" class="d-inline">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="line_id" value="<?= (int)$ln['id'] ?>">
                      <input type="hidden" name="status" value="unmatched">
                      <button type="submit" class="btn btn-outline-warning btn-sm py-0 px-2 text-dark">Reset</button>
                    </form>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Match Modal -->
<div class="modal fade" id="reconcileMatchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="/accounting/reconciliation/match" method="POST" class="modal-content">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="line_id" id="modalMatchLineId">
      <div class="modal-header py-2">
        <h6 class="modal-title fw-bold">Match Bank Line to Book Transaction</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="small text-muted mb-2" id="modalMatchDesc"></div>
        <label class="form-label small fw-semibold">Matching Book Payments / Expenses</label>
        <select name="match_target" id="modalMatchSelect" class="form-select form-select-sm" required></select>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success btn-sm">Confirm Match</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('reconcileMatchModal');
  const modal = new bootstrap.Modal(modalEl);
  document.querySelectorAll('.btn-match-modal').forEach(btn => {
    btn.addEventListener('click', async () => {
      const lineId = btn.dataset.lineId;
      document.getElementById('modalMatchLineId').value = lineId;
      document.getElementById('modalMatchDesc').textContent = btn.dataset.desc;
      const sel = document.getElementById('modalMatchSelect');
      sel.innerHTML = '<option value="">Loading candidates...</option>';
      modal.show();
      const res = await fetch('/api/accounting/reconcile-candidates/' + lineId);
      const data = await res.json();
      sel.innerHTML = '';
      if (!data.candidates || !data.candidates.length) {
        sel.innerHTML = '<option value="">No close amount matches found in Payments/Expenses</option>';
      } else {
        data.candidates.forEach(c => {
          const opt = document.createElement('option');
          opt.value = `${c.source_type}:${c.source_id}`;
          opt.textContent = `${c.label} — ${c.txn_date} (${c.amount.toFixed(2)})`;
          sel.appendChild(opt);
        });
      }
    });
  });
});
</script>
