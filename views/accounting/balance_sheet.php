<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-columns-gap text-primary me-2"></i>Balance Sheet</h3>
    <small class="text-muted">Statement of financial position as of <?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?></small>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <form action="/accounting/balance-sheet" method="GET" class="d-flex gap-2">
      <input type="date" name="as_of" class="form-control form-control-sm" value="<?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-primary btn-sm">Apply</button>
    </form>
    <a href="/accounting" class="btn btn-outline-secondary btn-sm">Accounting Hub</a>
  </div>
</div>

<div class="alert <?= !empty($bs['is_balanced']) ? 'alert-success' : 'alert-warning' ?> py-2 small fw-semibold">
  <?= !empty($bs['is_balanced']) ? 'Accounting Equation Verified: Total Assets = Total Liabilities + Equity.' : 'Balance Sheet shows variance — verify opening balances.' ?>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white fw-bold text-primary">Assets</div>
      <table class="table table-sm mb-0">
        <tbody>
          <?php foreach ($bs['assets'] as $r): ?>
            <tr>
              <td class="font-monospace small text-muted"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end fw-semibold"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['balance'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr><td colspan="2">Total Assets</td><td class="text-end text-primary"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$bs['total_assets'], 2) ?></td></tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white fw-bold text-dark">Liabilities &amp; Owner Equity</div>
      <table class="table table-sm mb-0">
        <tbody>
          <tr class="table-secondary"><td colspan="3" class="fw-bold small">Liabilities</td></tr>
          <?php foreach ($bs['liabilities'] as $r): ?>
            <tr>
              <td class="font-monospace small text-muted"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end fw-semibold"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['balance'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr class="table-secondary"><td colspan="3" class="fw-bold small">Equity &amp; Earnings</td></tr>
          <?php foreach ($bs['equity'] as $r): ?>
            <tr>
              <td class="font-monospace small text-muted"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end fw-semibold"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['balance'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr><td colspan="2">Total Liabilities + Equity</td><td class="text-end text-primary"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$bs['total_liabilities_and_equity'], 2) ?></td></tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
