<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-success me-2"></i>Profit &amp; Loss Statement</h3>
    <small class="text-muted">Income and expense accounts compiled from double-entry journal postings</small>
  </div>
  <a href="/accounting" class="btn btn-outline-secondary btn-sm">Accounting Hub</a>
</div>

<form action="/accounting/profit-loss" method="GET" class="card shadow-sm border-0 mb-3">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4"><label class="form-label small mb-1">From Date</label><input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4"><label class="form-label small mb-1">To Date</label><input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Generate</button>
        <a href="/accounting/profit-loss" class="btn btn-outline-secondary btn-sm">All Time</a>
      </div>
    </div>
  </div>
</form>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white fw-bold text-success">Revenue &amp; Income</div>
      <table class="table table-sm mb-0">
        <tbody>
          <?php if (empty($pnl['income'])): ?>
            <tr><td class="text-muted text-center py-3">No revenue entries</td></tr>
          <?php else: foreach ($pnl['income'] as $r): ?>
            <tr>
              <td class="font-monospace small text-muted"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end fw-semibold"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['amount'], 2) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr><td colspan="2">Total Income</td><td class="text-end text-success"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$pnl['total_income'], 2) ?></td></tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white fw-bold text-danger">Purchases &amp; Operating Expenses</div>
      <table class="table table-sm mb-0">
        <tbody>
          <?php if (empty($pnl['expenses'])): ?>
            <tr><td class="text-muted text-center py-3">No expense entries</td></tr>
          <?php else: foreach ($pnl['expenses'] as $r): ?>
            <tr>
              <td class="font-monospace small text-muted"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end fw-semibold"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['amount'], 2) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr><td colspan="2">Total Expenses</td><td class="text-end text-danger"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$pnl['total_expense'], 2) ?></td></tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0 mt-3">
  <div class="card-body d-flex justify-content-between align-items-center">
    <h5 class="fw-bold mb-0">Net Profit / (Loss)</h5>
    <h4 class="fw-bold mb-0 <?= $pnl['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
      <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$pnl['net_profit'], 2) ?>
    </h4>
  </div>
</div>
