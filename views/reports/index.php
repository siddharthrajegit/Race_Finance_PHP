<div class="mb-4">
  <h3 class="fw-bold mb-1">Business & Tax Reports</h3>
  <p class="text-muted small mb-0">Generate financial summaries, customer balances, and GST tax statements</p>
</div>

<div class="row g-4">
  <!-- Party / Customer Wise Report -->
  <div class="col-md-4">
    <div class="card h-100 shadow-sm border-0 card-hover">
      <div class="card-body p-4">
        <div class="rounded-3 bg-primary-subtle text-primary p-3 d-inline-block mb-3" style="font-size: 1.6rem;">
          <i class="bi bi-people"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Party & Customer Report</h5>
        <p class="text-muted small mb-4">
          View outstanding receivables, payables, and detailed ledger balances for all your customers and suppliers.
        </p>
        <a href="/reports/parties" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
          View Party Report &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- GSTR-1 Tax Summary Report -->
  <div class="col-md-4">
    <div class="card h-100 shadow-sm border-0 card-hover">
      <div class="card-body p-4">
        <div class="rounded-3 bg-success-subtle text-success p-3 d-inline-block mb-3" style="font-size: 1.6rem;">
          <i class="bi bi-file-earmark-text"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">GSTR-1 & Tax Summary</h5>
        <p class="text-muted small mb-4">
          Export taxable values, CGST, SGST, and IGST breakdowns for quick GST return filing and audits.
        </p>
        <a href="/reports/tax" class="btn btn-outline-success btn-sm w-100 fw-semibold">
          View Tax Report &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- Item Wise Sales Report -->
  <div class="col-md-4">
    <div class="card h-100 shadow-sm border-0 card-hover">
      <div class="card-body p-4">
        <div class="rounded-3 bg-info-subtle text-info p-3 d-inline-block mb-3" style="font-size: 1.6rem;">
          <i class="bi bi-box-seam"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Item-Wise Sales Report</h5>
        <p class="text-muted small mb-4">
          Identify your top-selling products, sales quantities, total revenue, and remaining warehouse inventory.
        </p>
        <a href="/reports/items" class="btn btn-outline-info btn-sm w-100 fw-semibold">
          View Item Report &rarr;
        </a>
      </div>
    </div>
  </div>

  <?php if (defined('ENABLE_COMPLIANCE_EXPORTS') && ENABLE_COMPLIANCE_EXPORTS): ?>
    <!-- GSTR-1 / 2B / 3B Summary Exports -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 card-hover">
        <div class="card-body p-4">
          <div class="rounded-3 bg-warning-subtle text-warning-emphasis p-3 d-inline-block mb-3" style="font-size: 1.6rem;">
            <i class="bi bi-file-earmark-spreadsheet"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">GSTR-1 / 2B / 3B CSV Exports</h5>
          <p class="text-muted small mb-4">
            Download software-compiled outward sales, purchase ITC, and liability working CSV sheets for CA review.
          </p>
          <a href="/reports/gst-exports" class="btn btn-outline-warning text-dark btn-sm w-100 fw-semibold">
            Open GST Exports &rarr;
          </a>
        </div>
      </div>
    </div>

    <!-- Tally XML Export -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 card-hover">
        <div class="card-body p-4">
          <div class="rounded-3 bg-secondary-subtle text-dark p-3 d-inline-block mb-3" style="font-size: 1.6rem;">
            <i class="bi bi-filetype-xml"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Tally-Compatible XML Export</h5>
          <p class="text-muted small mb-4">
            Export Sundry Debtor/Creditor masters and Sales/Purchase vouchers in standard Tally XML schema.
          </p>
          <a href="/reports/tally-export" class="btn btn-outline-dark btn-sm w-100 fw-semibold">
            Export Tally XML &rarr;
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
