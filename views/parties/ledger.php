<?php
$billedAmount = (float)($total_billed ?? 0);
$paidAmount = (float)($total_paid ?? 0);
$closingBal = (float)($closing_balance ?? 0);
$openingBal = (float)($opening_balance ?? 0);
$pendingList = !empty($pending_bills) ? $pending_bills : [];
$txList = !empty($transactions) ? $transactions : [];
$partyType = $party['type'] ?? 'customer';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
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

<!-- Balance Summary KPI Cards -->
<div class="row g-2 mb-3">
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

<!-- Pending / Unpaid Bills Breakdown Card (FIFO Status) -->
<div class="card shadow-sm border-0 mb-3">
  <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
    <div class="fw-bold text-dark small">
      <i class="bi bi-receipt me-1 text-primary"></i> Current Active Bills with Balance Due (Settled Oldest First)
    </div>
    <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size: 0.72rem;"><?= count($pendingList) ?> Unsettled Bills</span>
  </div>
  <div class="card-body p-0">
    <div style="overflow-x: hidden;">
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
                <td style="padding: 0.45rem 0.65rem;" class="small text-muted"><?= htmlspecialchars($b['date'] ?? $b['invoice_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
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

<!-- Detailed Transaction Statement & FIFO Settlement Ledger -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-white py-2 px-3">
    <h6 class="fw-bold mb-0 text-dark small">
      <i class="bi bi-journal-text me-1 text-primary"></i> Complete Chronological Transaction Ledger
    </h6>
  </div>
  <div class="card-body p-0">
    <div style="overflow-x: hidden;">
      <table class="table table-hover align-middle mb-0 w-100" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="padding: 0.5rem 0.65rem;">Date</th>
            <th style="padding: 0.5rem 0.65rem;">Voucher / Ref No</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">Type</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Debit (+)</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Credit (-)</th>
            <th style="padding: 0.5rem 0.65rem;">Payment & Settlement Details (FIFO Allocation)</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Running Balance (₹)</th>
            <th style="width: 95px; padding: 0.5rem 0.5rem;" class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- Opening Balance Row -->
          <tr class="table-light">
            <td style="padding: 0.45rem 0.65rem;" colspan="3" class="fw-semibold">Opening Balance</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted">--</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted">--</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-muted small">Initial account balance</td>
            <td style="padding: 0.45rem 0.65rem;" class="text-end fw-bold">₹ <?= number_format($openingBal, 2) ?></td>
            <td style="padding: 0.45rem 0.5rem;" class="text-end text-muted small">--</td>
          </tr>

          <?php if (!empty($txList)): ?>
            <?php foreach ($txList as $tx): 
              $slipUrl = ($tx['entry_type'] === 'invoice') ? ('/invoices/view/' . $tx['id']) : ('/payments/view/' . $tx['id']);
              $deb = (float)($tx['debit'] ?? 0);
              $cred = (float)($tx['credit'] ?? 0);
              $runBal = (float)($tx['running_balance'] ?? 0);
            ?>
              <tr class="ledger-slip-row" data-href="<?= $slipUrl ?>" style="cursor: pointer;" title="Click to open <?= ($tx['entry_type'] === 'invoice') ? 'Bill' : 'Payment Slip' ?> <?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <td style="padding: 0.45rem 0.65rem;" class="small text-muted"><?= htmlspecialchars($tx['date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="font-monospace fw-bold">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <a href="/invoices/view/<?= $tx['id'] ?>" class="text-decoration-none text-primary">
                      <i class="bi bi-receipt me-1"></i><?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php else: ?>
                    <a href="/payments/view/<?= $tx['id'] ?>" class="text-decoration-none text-success">
                      <i class="bi bi-file-earmark-text me-1"></i><?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
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
                <td style="padding: 0.45rem 0.65rem;" class="small">
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
                <td style="padding: 0.45rem 0.5rem;" class="text-end">
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
  });
</script>
