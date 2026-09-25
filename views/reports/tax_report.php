<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
  <div>
    <a href="/reports" class="text-decoration-none text-muted small">&larr; Back to Reports</a>
    <h3 class="fw-bold mb-1 mt-1">GSTR-1 & GST Tax Summary Report</h3>
    <p class="text-muted small mb-0">Taxable turnover, CGST, SGST, and IGST breakdowns for tax filing</p>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-trigger-print shadow-sm">
      <i class="bi bi-printer me-1"></i> Print Tax Statement
    </button>
  </div>
</div>

<!-- Date Filter Form -->
<div class="card shadow-sm border-0 mb-4 no-print">
  <div class="card-body p-3">
    <form action="/reports/tax" method="GET" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label for="from_date" class="form-label small mb-1">From Date</label>
        <input type="date" class="form-control form-control-sm" id="from_date" name="from_date" value="<?= htmlspecialchars($fromDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-4">
        <label for="to_date" class="form-label small mb-1">To Date</label>
        <input type="date" class="form-control form-control-sm" id="to_date" name="to_date" value="<?= htmlspecialchars($toDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm px-3">
          <i class="bi bi-filter me-1"></i> Apply Filter
        </button>
        <a href="/reports/tax" class="btn btn-light btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Tax Summary Cards -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0 text-dark">Turnover & Tax Breakdown Summary</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Type</th>
            <th class="text-center">Bills Count</th>
            <th class="text-end">Taxable Amount (₹)</th>
            <th class="text-end">CGST (₹)</th>
            <th class="text-end">SGST (₹)</th>
            <th class="text-end">IGST (₹)</th>
            <th class="text-end">Total Tax (₹)</th>
            <th class="text-end">Gross Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($summary)): ?>
            <?php foreach ($summary as $s): ?>
              <tr>
                <td class="fw-bold">
                  <?= !empty($s['is_gst_bill']) ? 'GST Sales Records' : 'Non-GST / Exempt Records' ?>
                </td>
                <td class="text-center"><?= (int)($s['invoice_count'] ?? 0) ?></td>
                <td class="text-end fw-semibold">₹ <?= number_format((float)($s['total_taxable'] ?? 0), 2) ?></td>
                <td class="text-end text-muted">₹ <?= number_format((float)($s['total_cgst'] ?? 0), 2) ?></td>
                <td class="text-end text-muted">₹ <?= number_format((float)($s['total_sgst'] ?? 0), 2) ?></td>
                <td class="text-end text-muted">₹ <?= number_format((float)($s['total_igst'] ?? 0), 2) ?></td>
                <td class="text-end fw-bold text-primary">₹ <?= number_format((float)($s['total_tax'] ?? 0), 2) ?></td>
                <td class="text-end fw-bold">₹ <?= number_format((float)($s['total_gross'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                No sales records found for the selected period.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Detailed Records Table -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0 text-dark">Record Wise GST Register</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Record No</th>
            <th>Customer Name</th>
            <th>Customer GSTIN</th>
            <th class="text-end">Taxable Value (₹)</th>
            <th class="text-end">CGST (₹)</th>
            <th class="text-end">SGST (₹)</th>
            <th class="text-end">IGST (₹)</th>
            <th class="text-end">Gross Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($invoices)): ?>
            <?php foreach ($invoices as $inv): ?>
              <tr>
                <td class="small text-muted"><?= htmlspecialchars($inv['invoice_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a href="/invoices/view/<?= $inv['id'] ?>" class="font-monospace fw-semibold text-decoration-none text-primary">
                    <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td class="fw-medium text-dark"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-monospace text-muted small"><?= htmlspecialchars($inv['party_gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end">₹ <?= number_format((float)($inv['taxable_amount'] ?? 0), 2) ?></td>
                <td class="text-end text-muted">₹ <?= number_format((float)($inv['cgst_amount'] ?? 0), 2) ?></td>
                <td class="text-end text-muted">₹ <?= number_format((float)($inv['sgst_amount'] ?? 0), 2) ?></td>
                <td class="text-end text-muted">₹ <?= number_format((float)($inv['igst_amount'] ?? 0), 2) ?></td>
                <td class="text-end fw-bold">₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">
                No invoices found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Statutory Tax & Utility Disclaimer -->
<div class="card bg-light border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body p-3 p-md-4">
    <div class="d-flex align-items-start">
      <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3 mt-1 flex-shrink-0"></i>
      <div>
        <h6 class="fw-bold text-dark mb-1">Statutory Record-Management & Billing Utility Notice:</h6>
        <p class="text-muted small mb-0 lh-base">
          <strong>Disclaimer:</strong> This software is intended for basic business record-keeping and billing purposes. It is not a substitute for a Chartered Accountant, tax professional, accountant, or official government tax/GST system. Users are responsible for verifying the accuracy, legality, and tax treatment of records generated using the software.
        </p>
      </div>
    </div>
  </div>
</div>
