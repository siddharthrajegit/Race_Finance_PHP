<?php
$billedAmount = (float)($total_billed ?? 0);
$paidAmount = (float)($total_paid ?? 0);
$closingBal = (float)($closing_balance ?? 0);
$openingBal = (float)($opening_balance ?? 0);
$pendingList = !empty($pending_bills) ? $pending_bills : [];
$txList = !empty($transactions) ? $transactions : [];
$partyType = $party['type'] ?? 'customer';
$firmName = $firm['name'] ?? 'RACE FINANCE';

$totalDebits = 0;
$totalCredits = 0;
foreach ($txList as $tx) {
    $totalDebits += (float)($tx['debit'] ?? 0);
    $totalCredits += (float)($tx['credit'] ?? 0);
}
?>

<style>
/* Print-Specific Styles: All Black and Dark Blue, High Contrast for B&W Printing */
@media print {
  @page {
    margin: 10mm 8mm;
    size: A4 portrait;
  }
  body {
    background-color: #ffffff !important;
    color: #000000 !important;
    font-size: 8.5pt !important;
  }
  .d-print-none, .app-sidebar, .sidebar-backdrop, .navbar, .top-action-bar, header, footer {
    display: none !important;
  }
  .app-main-wrapper, .app-layout, main {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  .container, .container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
  }
  .card {
    border: 1px solid #000000 !important;
    box-shadow: none !important;
  }
  .card-header {
    background-color: #f1f5f9 !important;
    border-bottom: 1.5px solid #000000 !important;
    color: #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  thead th {
    background-color: #0f172a !important;
    color: #ffffff !important;
    border: 1px solid #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    font-size: 8pt !important;
    padding: 4px 6px !important;
  }
  tbody td, tfoot td {
    border: 1px solid #333333 !important;
    color: #000000 !important;
    padding: 4px 6px !important;
    font-size: 8pt !important;
  }
  tr {
    page-break-inside: avoid;
  }
  /* In print, badges and text become black / dark blue */
  .badge {
    border: 1px solid #000000 !important;
    color: #000000 !important;
    background: transparent !important;
    font-size: 7pt !important;
    padding: 2px 4px !important;
  }
  .text-primary, a {
    color: #1e3a8a !important;
    text-decoration: none !important;
  }
  .text-success, .text-warning, .text-danger, .text-info {
    color: #000000 !important;
  }
  .print-text-dark-blue {
    color: #1e3a8a !important;
  }
  .print-table-total {
    background-color: #f1f5f9 !important;
    border-top: 2px solid #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<!-- =============================================================
     1. ON-SCREEN HEADER (Hidden in Print)
============================================================= -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 d-print-none">
  <div>
    <a href="/parties" class="text-decoration-none text-muted small">&larr; Back to Parties</a>
    <h3 class="fw-bold mb-1 mt-1">Party Statement & Ledger</h3>
    <div class="text-muted"><?= htmlspecialchars($party['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= strtoupper(htmlspecialchars($partyType, ENT_QUOTES, 'UTF-8')) ?>)</div>
  </div>
  <div class="d-flex gap-2">
    <a href="/payments/create?party_id=<?= $party['id'] ?>&type=<?= ($partyType === 'supplier') ? 'payment_out' : 'payment_in' ?>" class="btn btn-success shadow-sm fw-semibold">
      <i class="bi bi-cash-stack me-1"></i> Record Payment
    </a>
    <a href="/<?= ($partyType === 'supplier') ? 'purchases' : 'sales' ?>/create" class="btn btn-primary shadow-sm fw-semibold">
      <i class="bi bi-plus-circle me-1"></i> New <?= ($partyType === 'supplier') ? 'Purchase Bill' : 'Sale Bill' ?>
    </a>
    <button type="button" class="btn btn-outline-secondary btn-trigger-print shadow-sm">
      <i class="bi bi-printer me-1"></i> Print Statement
    </button>
  </div>
</div>

<!-- =============================================================
     1b. FILTER & PRINT CONTROLS (Custom Date Range & Last N Invoices)
============================================================= -->
<div class="card statement-filter-card mb-3 d-print-none shadow-sm">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2 rounded-circle">
          <i class="bi bi-funnel-fill"></i>
        </span>
        <div>
          <span class="fw-bold text-dark small">Statement Filter &amp; Range</span>
          <span class="text-muted ms-1 small d-none d-md-inline">&bull; Filter by date range or recent transactions</span>
        </div>
      </div>
      <?php if (!empty($from) || !empty($to) || !empty($lastN)): ?>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold small">
            <i class="bi bi-check-circle me-1"></i> Active Filter Applied
          </span>
          <a href="/parties/ledger/<?= $party['id'] ?>" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small">
            <i class="bi bi-x-circle me-1"></i> Reset
          </a>
        </div>
      <?php endif; ?>
    </div>

    <form method="GET" action="/parties/ledger/<?= $party['id'] ?>" class="row g-2 align-items-center pt-1">
      <div class="col-lg-3 col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white text-dark"><i class="bi bi-calendar-event text-primary me-1"></i> From</span>
          <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white text-dark"><i class="bi bi-calendar-event text-primary me-1"></i> To</span>
          <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white text-dark"><i class="bi bi-collection text-primary me-1"></i> Limit</span>
          <select name="last_n" class="form-select">
            <option value="">All Transactions</option>
            <option value="5" <?= (($lastN ?? null) === 5) ? 'selected' : '' ?>>Last 5 Invoices</option>
            <option value="10" <?= (($lastN ?? null) === 10) ? 'selected' : '' ?>>Last 10 Invoices</option>
            <option value="20" <?= (($lastN ?? null) === 20) ? 'selected' : '' ?>>Last 20 Invoices</option>
            <option value="50" <?= (($lastN ?? null) === 50) ? 'selected' : '' ?>>Last 50 Invoices</option>
          </select>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 d-flex gap-2">
        <button type="submit" class="btn btn-statement-filter flex-grow-1 shadow-sm d-flex align-items-center justify-content-center">
          <i class="bi bi-funnel me-1"></i> Apply Filter
        </button>
        <?php if (!empty($from) || !empty($to) || !empty($lastN)): ?>
          <a href="/parties/ledger/<?= $party['id'] ?>" class="btn btn-outline-secondary px-2" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        <?php endif; ?>
        <button type="submit" name="print" value="1" class="btn btn-dark shadow-sm d-flex align-items-center" title="Filter & Print Statement">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>
    </form>
  </div>
</div>

<!-- =============================================================
     2. PRINTABLE STATEMENT HEADER (Visible ONLY When Printing)
        - Party Name on top instead of Party Statement & Ledger
        - Color: Pure Black & Dark Blue (#1e3a8a)
============================================================= -->
<div class="d-none d-print-block mb-3">
  <!-- Firm Header -->
  <div class="row align-items-start mb-2 pb-2 border-bottom border-dark">
    <div class="col-8">
      <h3 class="fw-bold text-dark mb-0"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h3>
      <div class="small text-muted" style="font-size: 8pt; line-height: 1.35;">
        <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if (!empty($firm['city']) || !empty($firm['state'])): ?>
          <div><?= htmlspecialchars(implode(', ', array_filter([$firm['city'] ?? '', $firm['state'] ?? ''])), ENT_QUOTES, 'UTF-8') ?><?= !empty($firm['pincode']) ? ' - ' . htmlspecialchars($firm['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></div>
        <?php endif; ?>
        <?php if (!empty($firm['phone'])): ?><div>Phone: <strong><?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
        <?php if (!empty($firm['gstin'])): ?><div>GSTIN: <strong><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
      </div>
    </div>
    <div class="col-4 text-end">
      <div class="border border-dark px-2 py-1 text-uppercase fw-bold d-inline-block" style="font-size: 8pt; color: #1e3a8a;">
        Account Statement
      </div>
      <div class="small text-muted mt-1" style="font-size: 8pt;">
        <div>Date: <strong><?= date('d M Y') ?></strong></div>
        <?php if (!empty($from) || !empty($to)): ?>
          <div>Period: <strong><?= !empty($from) ? formatDate($from) : 'Beginning' ?></strong> to <strong><?= !empty($to) ? formatDate($to) : 'Today' ?></strong></div>
        <?php elseif (!empty($lastN)): ?>
          <div>Statement: <strong>Last <?= $lastN ?> Invoices / Records</strong></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Party Details Banner (Party Name on TOP) -->
  <div class="p-2 mb-2 border border-dark rounded" style="background-color: #f8fafc;">
    <div class="row align-items-center">
      <div class="col-8">
        <div class="small fw-bold text-uppercase" style="font-size: 7.5pt; color: #1e3a8a;">Statement Of:</div>
        <h2 class="fw-bold text-dark mb-0" style="font-size: 14pt;"><?= htmlspecialchars($party['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="small text-muted" style="font-size: 8pt;">
          <strong class="text-dark">(<?= strtoupper(htmlspecialchars($partyType, ENT_QUOTES, 'UTF-8')) ?>)</strong>
          <?php if (!empty($party['phone'])): ?> &bull; Phone: <?= htmlspecialchars($party['phone'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
          <?php if (!empty($party['gstin'])): ?> &bull; GSTIN: <?= htmlspecialchars($party['gstin'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
          <?php if (!empty($party['billing_address'])): ?> &bull; Address: <?= htmlspecialchars($party['billing_address'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
        </div>
      </div>
      <div class="col-4 text-end">
        <div class="small text-uppercase text-muted" style="font-size: 7pt;">Net Balance Left (Due):</div>
        <div class="fw-bold text-dark" style="font-size: 13pt;">
          ₹ <?= number_format(abs($closingBal), 2) ?>
          <span style="color: #1e3a8a; font-size: 9pt;"><?= $closingBal > 0 ? '(Dr)' : ($closingBal < 0 ? '(Cr)' : '(Nil)') ?></span>
        </div>
        <div class="small fw-semibold" style="font-size: 7.5pt; color: #1e3a8a;">
          <?= $closingBal > 0 ? 'To Collect from Party' : ($closingBal < 0 ? 'To Pay to Party' : 'Fully Settled / No Dues') ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- =============================================================
     3. BALANCE SUMMARY KPI CARDS (Website Only - Hidden on Print)
============================================================= -->
<div class="row g-2 mb-3 d-print-none">
  <!-- Total Billed -->
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100 p-3 bg-white border-start border-4 border-primary">
      <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total <?= ($partyType === 'supplier') ? 'Purchases' : 'Sales' ?> Billed</div>
      <h4 class="fw-bold text-dark mb-0 mt-1">₹ <?= number_format($billedAmount, 2) ?></h4>
      <div class="small text-muted mt-1" style="font-size: 0.72rem;">Total invoiced business</div>
    </div>
  </div>

  <!-- Total Payments Done -->
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100 p-3 bg-white border-start border-4 border-success">
      <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Payment Done (<?= ($partyType === 'supplier') ? 'Paid' : 'Received' ?>)</div>
      <h4 class="fw-bold text-success mb-0 mt-1">₹ <?= number_format($paidAmount, 2) ?></h4>
      <div class="small text-muted mt-1" style="font-size: 0.72rem;">Cash / Bank / UPI settled</div>
    </div>
  </div>

  <!-- Overall Net Due / Balance Left -->
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100 p-3 bg-white border-start border-4 <?= $closingBal > 0 ? 'border-warning' : ($closingBal < 0 ? 'border-danger' : 'border-secondary') ?>">
      <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Overall Balance Left (Due)</div>
      <h4 class="fw-bold mb-0 mt-1 <?= $closingBal > 0 ? 'text-warning' : ($closingBal < 0 ? 'text-danger' : 'text-success') ?>">
        ₹ <?= number_format(abs($closingBal), 2) ?>
      </h4>
      <div class="small fw-medium mt-1 <?= $closingBal > 0 ? 'text-warning' : ($closingBal < 0 ? 'text-danger' : 'text-success') ?>" style="font-size: 0.72rem;">
        <?= $closingBal > 0 ? 'To Collect from Customer' : ($closingBal < 0 ? 'To Pay to Supplier' : 'Fully Settled / No Dues') ?>
      </div>
    </div>
  </div>
</div>

<!-- =============================================================
     4. ACTIVE UNPAID BILLS (Website Only - Hidden on Print)
============================================================= -->
<div class="card shadow-sm border-0 mb-3 d-print-none">
  <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
    <div class="fw-bold text-dark small">
      <i class="bi bi-receipt me-1 text-primary"></i> Current Active Bills with Balance Due (Settled Oldest First)
    </div>
    <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size: 0.72rem;"><?= count($pendingList) ?> Unsettled Bills</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="padding: 0.5rem 0.65rem;">Bill Number</th>
            <th style="padding: 0.5rem 0.65rem;">Date</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">Type</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Total Bill (₹)</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Paid (₹)</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end text-danger fw-bold">Balance Left (₹)</th>
            <th style="width: 45px; padding: 0.5rem 0.25rem;" class="text-center">Status</th>
            <th style="width: 60px; padding: 0.5rem 0.35rem;" class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($pendingList)): ?>
            <?php foreach ($pendingList as $b): ?>
              <tr>
                <td style="padding: 0.45rem 0.65rem;" class="fw-bold font-monospace">
                  <a href="/invoices/view/<?= $b['id'] ?>" class="text-decoration-none text-primary">
                    <?= htmlspecialchars($b['voucher_no'] ?? $b['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="small text-muted"><?= htmlspecialchars(formatDate($b['date'] ?? $b['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center">
                  <span class="badge <?= ($b['type'] === 'sale') ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info border border-info-subtle' ?>" style="font-size: 0.68rem; padding: 0.2em 0.45em;">
                    <?= ($b['type'] === 'sale') ? 'Sale' : 'Purchase' ?>
                  </span>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end fw-semibold">₹ <?= number_format((float)($b['grand_total'] ?? 0), 2) ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end text-success">₹ <?= number_format((float)($b['paid_amount'] ?? 0), 2) ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end text-danger fw-bold fs-6">₹ <?= number_format((float)($b['balance_due'] ?? 0), 2) ?></td>
                <td style="width: 45px; padding: 0.45rem 0.25rem;" class="text-center">
                  <?php if (($b['payment_status'] ?? '') === 'paid'): ?>
                    <span class="badge badge-status badge-status-paid" style="font-size: 0.72rem; padding: 0.2em 0.45em;" title="Paid">P</span>
                  <?php elseif (($b['payment_status'] ?? '') === 'partial'): ?>
                    <span class="badge badge-status badge-status-partial" style="font-size: 0.72rem; padding: 0.2em 0.45em;" title="Partially Paid">PP</span>
                  <?php else: ?>
                    <span class="badge badge-status badge-status-unpaid" style="font-size: 0.72rem; padding: 0.2em 0.45em;" title="Unpaid">UP</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.35rem;" class="text-end">
                  <a href="/invoices/view/<?= $b['id'] ?>" class="btn btn-outline-primary btn-sm p-1" title="View">
                    <i class="bi bi-eye"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-3 text-muted">
                <i class="bi bi-check-circle-fill text-success me-1"></i> All bills for this party are 100% paid and cleared!
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- =============================================================
     5. TRANSACTION STATEMENT & LEDGER
        - Website: Shows complete table including FIFO Allocation column and Action buttons
        - Print: Hides FIFO column & Action column, shows clean Black & Dark Blue design
============================================================= -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-bold mb-0 text-dark small">
      <i class="bi bi-journal-text me-1 text-primary d-print-none"></i> Complete Chronological Transaction Ledger
    </h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="padding: 0.5rem 0.65rem;">Date</th>
            <th style="padding: 0.5rem 0.65rem;">Voucher / Ref No</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">Type</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Debit (+) (₹)</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Credit (-) (₹)</th>
            <!-- FIFO Allocation column: visible on website, hidden on print -->
            <th style="padding: 0.5rem 0.65rem;" class="d-print-none">Payment &amp; Settlement Details (FIFO Allocation)</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Running Balance (₹)</th>
            <th style="width: 80px; padding: 0.5rem 0.5rem;" class="text-end d-print-none">Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- Opening Balance Row -->
          <tr class="table-light">
            <td style="padding: 0.45rem 0.65rem;" colspan="3" class="fw-semibold">Opening Balance</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted">--</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted">--</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-muted small d-print-none">Initial account balance</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-end fw-bold">
              ₹ <?= number_format(abs($openingBal), 2) ?>
              <span class="small fw-semibold print-text-dark-blue"><?= $openingBal > 0 ? '(Dr)' : ($openingBal < 0 ? '(Cr)' : '') ?></span>
            </td>
            <td style="padding: 0.45rem 0.5rem;" class="text-end text-muted small d-print-none">--</td>
          </tr>

          <?php if (!empty($txList)): ?>
            <?php foreach ($txList as $tx): 
              $slipUrl = ($tx['entry_type'] === 'invoice') ? ('/invoices/view/' . $tx['id']) : ('/payments/view/' . $tx['id']);
              $deb = (float)($tx['debit'] ?? 0);
              $cred = (float)($tx['credit'] ?? 0);
              $runBal = (float)($tx['running_balance'] ?? 0);
            ?>
              <tr class="ledger-slip-row" data-href="<?= $slipUrl ?>" style="cursor: pointer;" title="Click to open <?= ($tx['entry_type'] === 'invoice') ? 'Bill' : 'Payment Slip' ?> <?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <td style="padding: 0.45rem 0.65rem;" class="small text-muted text-nowrap"><?= htmlspecialchars(formatDate($tx['date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="font-monospace fw-bold">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <a href="/invoices/view/<?= $tx['id'] ?>" class="text-decoration-none text-primary">
                      <i class="bi bi-receipt me-1 d-print-none"></i><?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php else: ?>
                    <a href="/payments/view/<?= $tx['id'] ?>" class="text-decoration-none text-success">
                      <i class="bi bi-file-earmark-text me-1 d-print-none"></i><?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php endif; ?>
                  <!-- Compact payment mode shown in print -->
                  <?php if (!empty($tx['payment_mode'])): ?>
                    <div class="d-none d-print-block small text-muted" style="font-size: 7pt; font-weight: normal;">
                      <?= strtoupper(htmlspecialchars($tx['payment_mode'], ENT_QUOTES, 'UTF-8')) ?><?= !empty($tx['reference_no']) ? ' &bull; Ref: ' . htmlspecialchars($tx['reference_no'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <span class="badge <?= ($tx['type'] === 'sale') ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info border border-info-subtle' ?>" style="font-size: 0.68rem; padding: 0.2em 0.45em;">
                      <?= ($tx['type'] === 'sale') ? 'Sales Record' : 'Purchase Record' ?>
                    </span>
                  <?php else: ?>
                    <span class="badge <?= ($tx['type'] === 'payment_in') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?>" style="font-size: 0.68rem; padding: 0.2em 0.45em;">
                      <?= ($tx['type'] === 'payment_in') ? 'Receipt (In)' : 'Voucher (Out)' ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end fw-medium <?= $deb > 0 ? 'text-primary' : 'text-muted' ?> text-nowrap">
                  <?= $deb > 0 ? '₹ ' . number_format($deb, 2) : '--' ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end fw-medium <?= $cred > 0 ? 'text-success' : 'text-muted' ?> text-nowrap">
                  <?= $cred > 0 ? '₹ ' . number_format($cred, 2) : '--' ?>
                </td>
                <!-- FIFO Allocation column (Website only) -->
                <td style="padding: 0.45rem 0.65rem;" class="small d-print-none">
                  <?php if (!empty($tx['fifo_details'])): ?>
                    <div class="fw-medium text-dark"><?= htmlspecialchars($tx['fifo_details'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                  <?php 
                    $details = array_filter([
                      !empty($tx['payment_mode']) ? strtoupper($tx['payment_mode']) : null,
                      $tx['reference_no'] ?? null,
                      $tx['notes'] ?? null
                    ]);
                    if (!empty($details)):
                  ?>
                    <div class="text-muted" style="font-size: 0.72rem;">
                      <?= htmlspecialchars(implode(' | ', $details), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end fw-bold text-nowrap <?= $runBal > 0 ? 'text-warning' : ($runBal < 0 ? 'text-danger' : 'text-success') ?>">
                  ₹ <?= number_format(abs($runBal), 2) ?>
                  <div class="small fw-normal text-muted" style="font-size: 0.7rem;">
                    <?= $runBal > 0 ? '(Due)' : ($runBal < 0 ? '(Advance)' : '(Cleared)') ?>
                  </div>
                </td>
                <td style="padding: 0.45rem 0.5rem;" class="text-end d-print-none">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <a href="/invoices/view/<?= $tx['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2 small" title="Open Bill">
                      <i class="bi bi-eye"></i> View
                    </a>
                  <?php else: ?>
                    <a href="/payments/view/<?= $tx['id'] ?>" class="btn btn-outline-success btn-sm py-0 px-2 small" title="Open Slip">
                      <i class="bi bi-file-earmark-text"></i> Slip
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                No billing or payment transactions recorded for this party yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
        <!-- Table Total & Closing Summary -->
        <tfoot class="table-light print-table-total">
          <tr class="fw-bold">
            <td colspan="3" class="text-uppercase" style="padding: 0.5rem 0.65rem; color: #1e3a8a;">
              Total &amp; Closing Balance
            </td>
            <td class="text-end" style="padding: 0.5rem 0.65rem;">
              ₹ <?= number_format($totalDebits, 2) ?>
            </td>
            <td class="text-end" style="padding: 0.5rem 0.65rem;">
              ₹ <?= number_format($totalCredits, 2) ?>
            </td>
            <td class="d-print-none small text-muted" style="padding: 0.5rem 0.65rem;">--</td>
            <td class="text-end print-text-dark-blue fs-6" style="padding: 0.5rem 0.65rem;">
              ₹ <?= number_format(abs($closingBal), 2) ?>
              <span class="small"><?= $closingBal > 0 ? '(Dr)' : ($closingBal < 0 ? '(Cr)' : '') ?></span>
            </td>
            <td class="d-print-none"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<script>
  // Click anywhere on a transaction row to open the slip
  document.addEventListener('DOMContentLoaded', () => {
    const slipRows = document.querySelectorAll('.ledger-slip-row');
    slipRows.forEach(row => {
      row.addEventListener('click', (e) => {
        // Prevent double trigger if clicking directly on a link or button inside the row
        if (e.target.closest('a') || e.target.closest('button')) return;
        const href = row.dataset.href;
        if (href) {
          window.location.href = href;
        }
      });
    });

    <?php if (!empty($autoPrint)): ?>
      setTimeout(() => {
        window.print();
      }, 350);
    <?php endif; ?>
  });
</script>
