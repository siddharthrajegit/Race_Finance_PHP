<?php $dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-stars text-warning me-2"></i>Customer Loyalty Rewards</h3>
    <small class="text-muted">Configure points earned on sales invoices and track customer reward balances</small>
  </div>
  <a href="/parties" class="btn btn-outline-secondary btn-sm">Back to Parties</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <form action="/loyalty/program" method="POST" class="card shadow-sm border-0 h-100">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="card-header bg-white fw-bold small text-uppercase">Program Settings</div>
      <div class="card-body p-3">
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="loyaltyEnable" name="is_enabled" value="1" <?= !empty($program['is_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold small" for="loyaltyEnable">Enable Automatic Points on Sales Invoices</label>
        </div>
        <div class="row g-2">
          <div class="col-12 mb-2">
            <label class="form-label small fw-semibold">Program Name</label>
            <input type="text" name="program_name" class="form-control form-control-sm" value="<?= htmlspecialchars($program['program_name'] ?? 'Rewards Club', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Points Earned</label>
            <input type="number" name="points_per_currency" class="form-control form-control-sm" step="0.1" min="0" value="<?= htmlspecialchars((string)$program['points_per_currency'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Per Spend Unit (₹)</label>
            <input type="number" name="currency_unit" class="form-control form-control-sm" step="1" min="1" value="<?= htmlspecialchars((string)$program['currency_unit'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-4">
            <label class="form-label small fw-semibold">Point Value (₹)</label>
            <input type="number" name="point_value" class="form-control form-control-sm" step="0.01" min="0.01" value="<?= htmlspecialchars((string)$program['point_value'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-4">
            <label class="form-label small fw-semibold">Min Redeem Pts</label>
            <input type="number" name="min_redeem_points" class="form-control form-control-sm" step="1" min="0" value="<?= htmlspecialchars((string)$program['min_redeem_points'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-4">
            <label class="form-label small fw-semibold">Max Bill %</label>
            <input type="number" name="max_redeem_pct" class="form-control form-control-sm" step="1" min="1" max="100" value="<?= htmlspecialchars((string)$program['max_redeem_pct'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>
      </div>
      <div class="card-footer bg-light text-end py-2">
        <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Program</button>
      </div>
    </form>
  </div>

  <div class="col-lg-6">
    <form action="/loyalty/adjust" method="POST" class="card shadow-sm border-0 h-100">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="card-header bg-white fw-bold small text-uppercase">Manual Point Adjustment / Redemption</div>
      <div class="card-body p-3">
        <div class="mb-2">
          <label class="form-label small fw-semibold">Customer <span class="text-danger">*</span></label>
          <select name="party_id" class="form-select form-select-sm" required>
            <option value="">-- Select Customer --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= (int)$c['id'] ?>">
                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?> (Balance: <?= number_format((float)($c['loyalty_points'] ?? 0), 2) ?> pts)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label small fw-semibold">Action</label>
            <select name="action" class="form-select form-select-sm">
              <option value="add">Award (+ Bonus Points)</option>
              <option value="deduct">Redeem / Deduct (- Points)</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Points</label>
            <input type="number" name="points" class="form-control form-control-sm" min="0.01" step="0.01" required>
          </div>
        </div>
        <div>
          <label class="form-label small fw-semibold">Notes</label>
          <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason for manual bonus or redemption">
        </div>
      </div>
      <div class="card-footer bg-light text-end py-2">
        <button type="submit" class="btn btn-success btn-sm fw-semibold">Update Points</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white fw-bold small text-uppercase">Recent Loyalty Point Ledger</div>
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.86rem;">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Customer</th>
          <th>Type</th>
          <th class="text-end">Points Delta</th>
          <th class="text-end">Balance After</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($transactions)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">No loyalty transactions recorded yet.</td></tr>
        <?php else: foreach ($transactions as $lt): ?>
          <tr>
            <td class="small text-muted"><?= htmlspecialchars($lt['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($lt['party_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge bg-light text-dark border text-uppercase"><?= htmlspecialchars($lt['txn_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td class="text-end fw-bold <?= (float)$lt['points_delta'] >= 0 ? 'text-success' : 'text-danger' ?>">
              <?= (float)$lt['points_delta'] >= 0 ? '+' : '' ?><?= number_format((float)$lt['points_delta'], 2) ?>
            </td>
            <td class="text-end fw-semibold"><?= number_format((float)$lt['balance_after'], 2) ?></td>
            <td class="small text-muted"><?= htmlspecialchars($lt['notes'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
