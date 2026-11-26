<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-cash-coin text-info me-2"></i>Cash Flow Statement (Direct Method)</h3>
    <small class="text-muted">Tracks actual inflows &amp; outflows on Cash (1000) and Bank (1100) ledger accounts</small>
  </div>
  <a href="/accounting" class="btn btn-outline-secondary btn-sm">Accounting Hub</a>
</div>

<div class="alert alert-info small py-2 mb-3">
  <strong>Scope Notice:</strong> Computed using the direct method from posted Cash (1000) and Bank (1100) journal movements. Unpaid credit invoices and non-cash depreciation are excluded by definition.
</div>

<form action="/accounting/cash-flow" method="GET" class="card shadow-sm border-0 mb-3">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4"><label class="form-label small mb-1">From Date</label><input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4"><label class="form-label small mb-1">To Date</label><input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Generate</button>
        <a href="/accounting/cash-flow" class="btn btn-outline-secondary btn-sm">All Time</a>
      </div>
    </div>
  </div>
</form>

<div class="card shadow-sm border-0" style="max-width: 720px;">
  <table class="table mb-0">
    <tbody>
      <tr><td>Opening Cash &amp; Bank Balance</td><td class="text-end fw-semibold"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['opening_cash'], 2) ?></td></tr>
      <tr><td class="ps-4 text-success">Operating Cash &amp; Bank Inflows (Receipts)</td><td class="text-end text-success">+ <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['operating_inflows'], 2) ?></td></tr>
      <tr><td class="ps-4 text-danger">Operating Cash &amp; Bank Outflows (Payments &amp; Expenses)</td><td class="text-end text-danger">- <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['operating_outflows'], 2) ?></td></tr>
      <tr class="table-light fw-bold"><td>Net Operating Cash Flow</td><td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['net_operating_cash'], 2) ?></td></tr>
      <tr><td>Other Manual Cash / Capital Movements</td><td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['other_manual_cash_net'], 2) ?></td></tr>
      <tr class="table-light fw-bold"><td>Closing Cash &amp; Bank Balance</td><td class="text-end text-primary fs-5"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['closing_cash'], 2) ?></td></tr>
    </tbody>
  </table>
</div>
