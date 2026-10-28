<?php
$closingBal = (float)($closing_balance ?? 0);
$openingBal = (float)($opening_balance ?? 0);
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
/* High-contrast Black & Dark Blue Styling for Screen & B&W Printing */
.ledger-statement-card {
  border: 1px solid #0f172a !important;
  border-radius: 8px;
  overflow: hidden;
  background-color: #ffffff;
}

.statement-table th {
  background-color: #0f172a !important;
  color: #ffffff !important;
  font-weight: 600;
  border: 1px solid #0f172a !important;
  -webkit-print-color-adjust: exact !important;
  print-color-adjust: exact !important;
}

.statement-table td {
  border: 1px solid #cbd5e1;
  color: #000000;
}

.statement-table tfoot td {
  background-color: #f1f5f9 !important;
  border-top: 2px solid #0f172a !important;
  -webkit-print-color-adjust: exact !important;
  print-color-adjust: exact !important;
}

.text-dark-blue {
  color: #1e3a8a !important;
}

@media print {
  @page {
    margin: 12mm 10mm;
    size: A4 portrait;
  }
  body {
    background-color: #ffffff !important;
    color: #000000 !important;
    font-size: 10pt !important;
  }
  .no-print, .app-sidebar, .sidebar-backdrop, .navbar, .top-action-bar, header, footer {
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
  .ledger-statement-card {
    border: 1.5px solid #000000 !important;
    box-shadow: none !important;
    padding: 0 !important;
  }
  .statement-table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  .statement-table th {
    background-color: #000000 !important;
    color: #ffffff !important;
    border: 1px solid #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  .statement-table td {
    border: 1px solid #000000 !important;
    color: #000000 !important;
    padding: 4px 6px !important;
  }
  .statement-table tr {
    page-break-inside: avoid;
  }
}
</style>

<!-- Top Action Bar (Screen Only) -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
  <div>
    <a href="/parties" class="text-decoration-none text-muted small">&larr; Back to Parties</a>
  </div>
  <div class="d-flex gap-2">
    <a href="/payments/create?party_id=<?= $party['id'] ?>&type=<?= ($partyType === 'supplier') ? 'payment_out' : 'payment_in' ?>" class="btn btn-sm shadow-sm fw-semibold text-white" style="background-color: #1e3a8a; border-color: #1e3a8a;">
      <i class="bi bi-cash-stack me-1"></i> Record Payment
    </a>
    <a href="/<?= ($partyType === 'supplier') ? 'purchases' : 'sales' ?>/create" class="btn btn-sm btn-outline-dark shadow-sm fw-semibold">
      <i class="bi bi-plus-circle me-1"></i> New <?= ($partyType === 'supplier') ? 'Purchase Bill' : 'Sale Bill' ?>
    </a>
    <button type="button" class="btn btn-sm btn-dark btn-trigger-print shadow-sm fw-semibold">
      <i class="bi bi-printer me-1"></i> Print Statement
    </button>
  </div>
</div>

<!-- Statement Container -->
<div class="ledger-statement-card p-3 p-md-4 shadow-sm mb-4">
  <!-- Firm Header -->
  <div class="row align-items-start mb-3 pb-3 border-bottom border-dark">
    <div class="col-8">
      <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="small text-muted mt-1" style="line-height: 1.4;">
        <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if (!empty($firm['city']) || !empty($firm['state'])): ?>
          <div><?= htmlspecialchars(implode(', ', array_filter([$firm['city'] ?? '', $firm['state'] ?? ''])), ENT_QUOTES, 'UTF-8') ?><?= !empty($firm['pincode']) ? ' - ' . htmlspecialchars($firm['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></div>
        <?php endif; ?>
        <?php if (!empty($firm['phone'])): ?><div>Phone: <strong class="text-dark"><?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
        <?php if (!empty($firm['gstin'])): ?><div class="font-monospace">GSTIN: <strong class="text-dark"><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
      </div>
    </div>
    <div class="col-4 text-end">
      <div class="badge border border-dark text-dark px-3 py-1 text-uppercase fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
        Account Statement
      </div>
      <div class="small text-muted">
        <div><strong>Date:</strong> <?= date('d M Y') ?></div>
      </div>
    </div>
  </div>

  <!-- Party Details Banner (Party Name on TOP instead of "Party Statement & Ledger") -->
  <div class="p-3 mb-3 border border-dark rounded" style="background-color: #f8fafc;">
    <div class="row align-items-center">
      <div class="col-md-8">
        <div class="small fw-bold text-uppercase text-dark-blue letter-spacing-1" style="font-size: 0.72rem;">
          Statement Of:
        </div>
        <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">
          <?= htmlspecialchars($party['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <div class="small text-muted" style="line-height: 1.4;">
          <span class="badge border border-dark text-dark me-2 font-monospace" style="font-size: 0.68rem;">
            <?= strtoupper(htmlspecialchars($partyType, ENT_QUOTES, 'UTF-8')) ?>
          </span>
          <?php if (!empty($party['phone'])): ?>
            <span class="me-3"><i class="bi bi-telephone me-1 text-dark"></i><?= htmlspecialchars($party['phone'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <?php if (!empty($party['gstin'])): ?>
            <span class="me-3 font-monospace"><i class="bi bi-receipt me-1 text-dark"></i>GSTIN: <?= htmlspecialchars($party['gstin'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <?php if (!empty($party['billing_address'])): ?>
            <div><i class="bi bi-geo-alt me-1 text-dark"></i><?= htmlspecialchars($party['billing_address'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-2 mt-md-0">
        <div class="small text-uppercase fw-semibold text-muted" style="font-size: 0.72rem;">Closing Balance Due:</div>
        <div class="fs-4 fw-bold text-dark">
          ₹ <?= number_format(abs($closingBal), 2) ?>
          <span class="fs-6 fw-bold text-dark-blue">
            <?= $closingBal > 0 ? '(Dr)' : ($closingBal < 0 ? '(Cr)' : '(Settled)') ?>
          </span>
        </div>
        <div class="small fw-medium text-dark-blue" style="font-size: 0.75rem;">
          <?= $closingBal > 0 ? 'Receivable from Party' : ($closingBal < 0 ? 'Payable to Party' : 'Account Cleared / No Dues') ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Transactions Table (Cleaned: FIFO Column removed, Black & Dark Blue) -->
  <div class="table-responsive">
    <table class="table align-middle mb-0 w-100 statement-table" style="font-size: 0.85rem;">
      <thead>
        <tr>
          <th style="padding: 0.55rem 0.65rem;">Date</th>
          <th style="padding: 0.55rem 0.65rem;">Voucher / Ref No</th>
          <th style="padding: 0.55rem 0.4rem;" class="text-center">Type</th>
          <th style="padding: 0.55rem 0.65rem;" class="text-end">Debit (+) (₹)</th>
          <th style="padding: 0.55rem 0.65rem;" class="text-end">Credit (-) (₹)</th>
          <th style="padding: 0.55rem 0.65rem;" class="text-end">Running Balance (₹)</th>
          <th style="width: 70px; padding: 0.55rem 0.5rem;" class="text-end no-print">Action</th>
        </tr>
      </thead>
      <tbody>
        <!-- Opening Balance Row -->
        <tr style="background-color: #f8fafc;">
          <td style="padding: 0.45rem 0.65rem;" colspan="3" class="fw-bold text-dark">Opening Balance</td>
          <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted">--</td>
          <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted">--</td>
          <td style="padding: 0.45rem 0.65rem;" class="text-end fw-bold text-dark">
            ₹ <?= number_format(abs($openingBal), 2) ?>
            <span class="small text-dark-blue"><?= $openingBal > 0 ? '(Dr)' : ($openingBal < 0 ? '(Cr)' : '') ?></span>
          </td>
          <td style="padding: 0.45rem 0.5rem;" class="text-end text-muted small no-print">--</td>
        </tr>

        <?php if (!empty($txList)): ?>
          <?php foreach ($txList as $tx): 
            $slipUrl = ($tx['entry_type'] === 'invoice') ? ('/invoices/view/' . $tx['id']) : ('/payments/view/' . $tx['id']);
            $deb = (float)($tx['debit'] ?? 0);
            $cred = (float)($tx['credit'] ?? 0);
            $runBal = (float)($tx['running_balance'] ?? 0);
          ?>
            <tr class="ledger-slip-row" data-href="<?= $slipUrl ?>" style="cursor: pointer;" title="Click to open <?= ($tx['entry_type'] === 'invoice') ? 'Bill' : 'Payment Slip' ?> <?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <td style="padding: 0.45rem 0.65rem;" class="small text-dark text-nowrap"><?= htmlspecialchars(formatDate($tx['date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 0.45rem 0.65rem;" class="font-monospace fw-bold">
                <a href="<?= $slipUrl ?>" class="text-decoration-none text-dark-blue">
                  <?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php if (!empty($tx['payment_mode'])): ?>
                  <div class="small text-muted fw-normal" style="font-size: 0.72rem;">
                    <?= strtoupper(htmlspecialchars($tx['payment_mode'], ENT_QUOTES, 'UTF-8')) ?><?= !empty($tx['reference_no']) ? ' &bull; Ref: ' . htmlspecialchars($tx['reference_no'], ENT_QUOTES, 'UTF-8') : '' ?>
                  </div>
                <?php endif; ?>
              </td>
              <td style="padding: 0.45rem 0.4rem;" class="text-center">
                <span class="badge border border-dark text-dark" style="font-size: 0.68rem; padding: 0.25em 0.5em; font-weight: 600;">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <?= ($tx['type'] === 'sale') ? 'SALE' : 'PURCHASE' ?>
                  <?php else: ?>
                    <?= ($tx['type'] === 'payment_in') ? 'RECEIPT (IN)' : 'PAYMENT (OUT)' ?>
                  <?php endif; ?>
                </span>
              </td>
              <td style="padding: 0.45rem 0.65rem;" class="text-end fw-semibold text-dark text-nowrap">
                <?= $deb > 0 ? '₹ ' . number_format($deb, 2) : '--' ?>
              </td>
              <td style="padding: 0.45rem 0.65rem;" class="text-end fw-semibold text-dark text-nowrap">
                <?= $cred > 0 ? '₹ ' . number_format($cred, 2) : '--' ?>
              </td>
              <td style="padding: 0.45rem 0.65rem;" class="text-end fw-bold text-dark text-nowrap">
                ₹ <?= number_format(abs($runBal), 2) ?>
                <span class="small text-dark-blue fw-semibold">
                  <?= $runBal > 0 ? '(Dr)' : ($runBal < 0 ? '(Cr)' : '(Nil)') ?>
                </span>
              </td>
              <td style="padding: 0.45rem 0.5rem;" class="text-end no-print">
                <a href="<?= $slipUrl ?>" class="btn btn-outline-dark btn-sm py-0 px-2 small" title="View details">
                  <i class="bi bi-eye"></i> View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">
              No billing or payment transactions recorded for this party yet.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>

      <!-- Table Summary Footer -->
      <tfoot>
        <tr class="fw-bold">
          <td colspan="3" class="text-uppercase text-dark" style="padding: 0.55rem 0.65rem;">
            Total &amp; Closing Balance
          </td>
          <td class="text-end text-dark" style="padding: 0.55rem 0.65rem;">
            ₹ <?= number_format($totalDebits, 2) ?>
          </td>
          <td class="text-end text-dark" style="padding: 0.55rem 0.65rem;">
            ₹ <?= number_format($totalCredits, 2) ?>
          </td>
          <td class="text-end text-dark-blue fs-6" style="padding: 0.55rem 0.65rem;">
            ₹ <?= number_format(abs($closingBal), 2) ?>
            <span class="small"><?= $closingBal > 0 ? '(Dr)' : ($closingBal < 0 ? '(Cr)' : '') ?></span>
          </td>
          <td class="no-print"></td>
        </tr>
      </tfoot>
    </table>
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
