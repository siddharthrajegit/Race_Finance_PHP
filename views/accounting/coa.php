<?php $canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_accounting', $userPermissions ?? [], true); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-diagram-3 text-primary me-2"></i>Chart of Accounts</h3>
    <small class="text-muted">Double-entry ledger structure &amp; financial statements</small>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="/accounting/journals" class="btn btn-outline-primary btn-sm">Journals</a>
    <a href="/accounting/trial-balance" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
    <a href="/accounting/profit-loss" class="btn btn-outline-success btn-sm">Profit &amp; Loss</a>
    <a href="/accounting/balance-sheet" class="btn btn-outline-dark btn-sm">Balance Sheet</a>
    <a href="/accounting/cash-flow" class="btn btn-outline-info btn-sm">Cash Flow</a>
    <a href="/accounting/reconciliation" class="btn btn-outline-warning btn-sm text-dark">Bank Reconciliation</a>
  </div>
</div>

<?php if ($canManage): ?>
  <div class="alert alert-light border d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 mb-3">
    <span class="small text-muted">Need to sync older pre-existing bills, payments, or expenses into journals?</span>
    <form action="/accounting/backfill" method="POST" class="m-0">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-outline-primary btn-sm">Sync Missing Historical Journals</button>
    </form>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.875rem;">
          <thead class="table-light">
            <tr>
              <th>Code</th>
              <th>Account Name</th>
              <th>Type</th>
              <th class="text-end">Opening Balance</th>
              <th>Origin</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($accounts as $acc): ?>
              <tr>
                <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($acc['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($acc['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge bg-light text-dark border text-uppercase"><?= htmlspecialchars($acc['account_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="text-end">
                  <?= number_format((float)$acc['opening_balance'], 2) ?>
                  <span class="small text-muted"><?= strtoupper(substr((string)$acc['opening_side'], 0, 2)) ?></span>
                </td>
                <td>
                  <?= !empty($acc['is_system']) ? '<span class="badge bg-secondary-subtle text-secondary">System</span>' : '<span class="badge bg-info-subtle text-info">Custom</span>' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($canManage): ?>
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold small text-uppercase">Add Custom Ledger Account</div>
        <form action="/accounting/accounts/create" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="card-body p-3">
            <div class="mb-2">
              <label class="form-label small fw-semibold">Account Code <span class="text-danger">*</span></label>
              <input type="text" name="code" class="form-control form-control-sm font-monospace" required placeholder="e.g. 1150">
            </div>
            <div class="mb-2">
              <label class="form-label small fw-semibold">Account Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. HDFC Current A/C">
            </div>
            <div class="mb-2">
              <label class="form-label small fw-semibold">Account Type <span class="text-danger">*</span></label>
              <select name="account_type" class="form-select form-select-sm" required>
                <option value="asset">Asset</option>
                <option value="liability">Liability</option>
                <option value="equity">Equity / Capital</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
              </select>
            </div>
            <div class="row g-2">
              <div class="col-7">
                <label class="form-label small fw-semibold">Opening Balance</label>
                <input type="number" name="opening_balance" class="form-control form-control-sm" step="0.01" min="0" value="0.00">
              </div>
              <div class="col-5">
                <label class="form-label small fw-semibold">Normal Side</label>
                <select name="opening_side" class="form-select form-select-sm">
                  <option value="">Auto</option>
                  <option value="debit">Debit</option>
                  <option value="credit">Credit</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-footer bg-light p-3 text-end">
            <button type="submit" class="btn btn-primary btn-sm fw-semibold">Add Account</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
