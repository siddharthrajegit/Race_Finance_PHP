<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-filetype-xml text-primary me-2"></i>Tally-Compatible XML Export</h3>
    <small class="text-muted">Export Party Ledgers and Sales/Purchase Vouchers in Tally XML import schema</small>
  </div>
  <a href="/reports" class="btn btn-outline-secondary btn-sm">Back to Reports</a>
</div>

<div class="alert alert-info small py-2 mb-3">
  <strong>Import Guidance:</strong> Generates Tally-compatible <code>&lt;ENVELOPE&gt;</code> XML containing Sundry Debtor/Creditor masters and Sales/Purchase vouchers for the selected period.
</div>

<div class="card shadow-sm border-0" style="max-width: 640px;">
  <form action="/reports/tally-export/download" method="GET">
    <div class="card-body p-4">
      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label small fw-semibold">From Date</label>
          <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">To Date</label>
          <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="small text-muted mb-3">
        Selected Period Ready: <strong><?= (int)$salesCount ?></strong> Sales &amp; <strong><?= (int)$purchasesCount ?></strong> Purchase voucher(s).
      </div>
      <button type="submit" class="btn btn-primary btn-sm fw-semibold">
        <i class="bi bi-download me-1"></i> Download Tally XML File
      </button>
    </div>
  </form>
</div>
