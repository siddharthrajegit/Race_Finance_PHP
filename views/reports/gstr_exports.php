<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>GST Summary Exports (GSTR-1 / 2B / 3B)</h3>
    <small class="text-muted">Local software-compiled tax summaries for CA reconciliation</small>
  </div>
  <a href="/reports" class="btn btn-outline-secondary btn-sm">Back to Reports</a>
</div>

<div class="alert alert-warning small py-2 mb-3">
  <strong>Important Disclaimer:</strong> These CSV downloads are <strong>software-generated working summaries</strong> from your RACE FINANCE bills for accountant review. They are <strong>not</strong> official GSTN portal filings, e-Invoices (IRN), or e-Way Bills.
</div>

<form action="/reports/gst-exports" method="GET" class="card shadow-sm border-0 mb-3">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4"><label class="form-label small mb-1">From Date</label><input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4"><label class="form-label small mb-1">To Date</label><input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4"><button type="submit" class="btn btn-primary btn-sm">Update Period Summary</button></div>
    </div>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="fw-bold text-primary mb-1">GSTR-1 Working Summary</div>
        <div class="small text-muted mb-2"><?= count($data['sales']) ?> outward sales record(s) &bull; Output Tax: <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)($data['outward']['cgst'] + $data['outward']['sgst'] + $data['outward']['igst']), 2) ?></div>
        <a href="/reports/gst-exports/download?report=gstr1&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="btn btn-outline-primary btn-sm w-100">
          <i class="bi bi-download me-1"></i> Download GSTR-1 CSV
        </a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="fw-bold text-success mb-1">GSTR-2B Purchase Register</div>
        <div class="small text-muted mb-2"><?= count($data['purchases']) ?> inward purchase bill(s) &bull; Input ITC: <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)($data['inward']['cgst'] + $data['inward']['sgst'] + $data['inward']['igst']), 2) ?></div>
        <a href="/reports/gst-exports/download?report=gstr2b&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="btn btn-outline-success btn-sm w-100">
          <i class="bi bi-download me-1"></i> Download GSTR-2B CSV
        </a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="fw-bold text-dark mb-1">GSTR-3B Liability Summary</div>
        <div class="small text-muted mb-2">Estimated Net Tax Liability: <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$data['net_payable']['total_tax'], 2) ?></div>
        <a href="/reports/gst-exports/download?report=gstr3b&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="btn btn-outline-dark btn-sm w-100">
          <i class="bi bi-download me-1"></i> Download GSTR-3B CSV
        </a>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white fw-bold small text-uppercase">Period Tax Position Preview</div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead class="table-light"><tr><th>Section</th><th class="text-end">Taxable</th><th class="text-end">CGST</th><th class="text-end">SGST</th><th class="text-end">IGST</th></tr></thead>
      <tbody>
        <tr><td>Outward Sales (3.1a)</td><td class="text-end"><?= number_format($data['outward']['taxable'], 2) ?></td><td class="text-end"><?= number_format($data['outward']['cgst'], 2) ?></td><td class="text-end"><?= number_format($data['outward']['sgst'], 2) ?></td><td class="text-end"><?= number_format($data['outward']['igst'], 2) ?></td></tr>
        <tr><td>Inward Purchases ITC (4A)</td><td class="text-end"><?= number_format($data['inward']['taxable'], 2) ?></td><td class="text-end"><?= number_format($data['inward']['cgst'], 2) ?></td><td class="text-end"><?= number_format($data['inward']['sgst'], 2) ?></td><td class="text-end"><?= number_format($data['inward']['igst'], 2) ?></td></tr>
        <tr class="table-light fw-bold"><td>Estimated Net Payable</td><td class="text-end">--</td><td class="text-end"><?= number_format($data['net_payable']['cgst'], 2) ?></td><td class="text-end"><?= number_format($data['net_payable']['sgst'], 2) ?></td><td class="text-end"><?= number_format($data['net_payable']['igst'], 2) ?></td></tr>
      </tbody>
    </table>
  </div>
</div>
