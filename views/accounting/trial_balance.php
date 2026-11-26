<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-check2-square text-primary me-2"></i>Trial Balance</h3>
    <small class="text-muted">As of <?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?></small>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <form action="/accounting/trial-balance" method="GET" class="d-flex gap-2">
      <input type="date" name="as_of" class="form-control form-control-sm" value="<?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-primary btn-sm">Apply</button>
    </form>
    <a href="/accounting" class="btn btn-outline-secondary btn-sm">Accounting Hub</a>
  </div>
</div>

<div class="alert <?= !empty($tb['is_balanced']) ? 'alert-success' : 'alert-danger' ?> py-2 small fw-semibold">
  <?= !empty($tb['is_balanced']) ? 'Trial Balance is IN BALANCE (Total Debits = Total Credits).' : 'Trial Balance variance detected.' ?>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>Code</th>
          <th>Account Name</th>
          <th>Group</th>
          <th class="text-end">Debit Balance</th>
          <th class="text-end">Credit Balance</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tb['rows'] as $row): ?>
          <tr>
            <td class="font-monospace text-muted"><?= htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="small text-uppercase text-muted"><?= htmlspecialchars($row['account_type'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end"><?= $row['net_debit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$row['net_debit'], 2) : '--' ?></td>
            <td class="text-end"><?= $row['net_credit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$row['net_credit'], 2) : '--' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="3" class="text-end">Totals:</td>
          <td class="text-end text-primary"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$tb['total_debit'], 2) ?></td>
          <td class="text-end text-primary"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$tb['total_credit'], 2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
