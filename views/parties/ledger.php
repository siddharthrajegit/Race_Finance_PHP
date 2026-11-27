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
@media print {
  @page { margin: 10mm 8mm; size: A4 portrait; }
  body { background-color: #ffffff !important; color: #000000 !important; font-size: 8.5pt !important; }
  .print-hide, .app-sidebar, .sidebar-backdrop, header, footer { display: none !important; }
  main { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
  .rf-card { border: 1px solid #000000 !important; box-shadow: none !important; border-radius: 0 !important; }
  .rf-card-header { background-color: #f1f5f9 !important; border-bottom: 1.5px solid #000000 !important; color: #000000 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
  table { width: 100% !important; border-collapse: collapse !important; }
  thead th { background-color: #1c1b18 !important; color: #ffffff !important; border: 1px solid #000000 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; font-size: 8pt !important; padding: 4px 6px !important; }
  tbody td, tfoot td { border: 1px solid #333333 !important; color: #000000 !important; padding: 4px 6px !important; font-size: 8pt !important; }
  tr { page-break-inside: avoid; }
  .rf-badge { border: 1px solid #000000 !important; color: #000000 !important; background: transparent !important; font-size: 7pt !important; padding: 2px 4px !important; }
  a { color: #1e3a8a !important; text-decoration: none !important; }
  .print-table-total { background-color: #f1f5f9 !important; border-top: 2px solid #000000 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<div class="space-y-6">
  <!-- On-Screen Header -->
  <div class="flex flex-wrap items-center justify-between gap-4 print:hidden">
    <div>
      <a href="/parties" class="text-xs text-[#78746C] hover:text-[#1C1B18] no-underline">&larr; Back to Parties</a>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight mt-1">Party Statement &amp; Ledger</h1>
      <div class="text-sm text-[#5C5A54]"><?= htmlspecialchars($party['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> <span class="text-xs font-mono uppercase text-[#78746C]">(<?= htmlspecialchars($partyType, ENT_QUOTES, 'UTF-8') ?>)</span></div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/payments/create?party_id=<?= $party['id'] ?>&type=<?= ($partyType === 'supplier') ? 'payment_out' : 'payment_in' ?>" class="rf-btn rf-btn-sage rf-btn-sm">
        <i class="bi bi-cash-stack"></i> Record Payment
      </a>
      <a href="/<?= ($partyType === 'supplier') ? 'purchases' : 'sales' ?>/create" class="rf-btn rf-btn-primary rf-btn-sm">
        <i class="bi bi-plus-circle"></i> New <?= ($partyType === 'supplier') ? 'Purchase Bill' : 'Sale Bill' ?>
      </a>
      <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm btn-trigger-print">
        <i class="bi bi-printer"></i> Print Statement
      </button>
    </div>
  </div>

  <!-- Filter Controls -->
  <div class="rf-card p-5 space-y-4 print:hidden">
    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#ECEAE4]">
      <div class="flex items-center gap-2.5">
        <span class="w-7 h-7 rounded-full bg-[#F3F1EC] border border-[#E2DFD7] text-[#5C5A54] flex items-center justify-center text-xs"><i class="bi bi-funnel"></i></span>
        <div>
          <span class="text-sm font-semibold text-[#1C1B18]">Statement Filter &amp; Range</span>
          <span class="text-xs text-[#78746C] hidden md:inline ml-2">&bull; Filter by date range or recent transactions</span>
        </div>
      </div>
      <?php if (!empty($from) || !empty($to) || !empty($lastN)): ?>
        <div class="flex items-center gap-2">
          <span class="rf-badge rf-badge-slate"><i class="bi bi-check-circle"></i> Filter Active</span>
          <a href="/parties/ledger/<?= $party['id'] ?>" class="text-xs text-[#8F2D2B] hover:underline no-underline"><i class="bi bi-x-circle mr-0.5"></i>Reset</a>
        </div>
      <?php endif; ?>
    </div>

    <form method="GET" action="/parties/ledger/<?= $party['id'] ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
      <div>
        <label class="rf-label text-[10px]">From Date</label>
        <input type="date" name="from" class="rf-input rf-input-sm" value="<?= htmlspecialchars($from ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div>
        <label class="rf-label text-[10px]">To Date</label>
        <input type="date" name="to" class="rf-input rf-input-sm" value="<?= htmlspecialchars($to ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div>
        <label class="rf-label text-[10px]">Transaction Limit</label>
        <select name="last_n" class="rf-select rf-select-sm">
          <option value="">All Transactions</option>
          <option value="5" <?= (($lastN ?? null) === 5) ? 'selected' : '' ?>>Last 5 Invoices</option>
          <option value="10" <?= (($lastN ?? null) === 10) ? 'selected' : '' ?>>Last 10 Invoices</option>
          <option value="20" <?= (($lastN ?? null) === 20) ? 'selected' : '' ?>>Last 20 Invoices</option>
          <option value="50" <?= (($lastN ?? null) === 50) ? 'selected' : '' ?>>Last 50 Invoices</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm flex-1">
          <i class="bi bi-funnel"></i> Apply
        </button>
        <?php if (!empty($from) || !empty($to) || !empty($lastN)): ?>
          <a href="/parties/ledger/<?= $party['id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm px-2.5" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        <?php endif; ?>
        <button type="submit" name="print" value="1" class="rf-btn rf-btn-secondary rf-btn-sm" title="Filter & Print Statement">
          <i class="bi bi-printer"></i> Print
        </button>
      </div>
    </form>
  </div>

  <!-- Printable Statement Header (Print Only) -->
  <div class="hidden print:block mb-3">
    <div class="flex justify-between items-start pb-2 mb-2 border-b border-black">
      <div>
        <h3 class="font-bold text-black text-base mb-0"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="text-[8pt] text-gray-700 leading-snug">
          <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firm['city']) || !empty($firm['state'])): ?>
            <div><?= htmlspecialchars(implode(', ', array_filter([$firm['city'] ?? '', $firm['state'] ?? ''])), ENT_QUOTES, 'UTF-8') ?><?= !empty($firm['pincode']) ? ' - ' . htmlspecialchars($firm['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></div>
          <?php endif; ?>
          <?php if (!empty($firm['phone'])): ?><div>Phone: <strong><?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
          <?php if (!empty($firm['gstin'])): ?><div>GSTIN: <strong><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
        </div>
      </div>
      <div class="text-right">
        <div class="border border-black px-2 py-1 uppercase font-bold inline-block text-[8pt] text-[#1e3a8a]">Account Statement</div>
        <div class="text-[8pt] text-gray-700 mt-1">
          <div>Date: <strong><?= date('d M Y') ?></strong></div>
          <?php if (!empty($from) || !empty($to)): ?>
            <div>Period: <strong><?= !empty($from) ? formatDate($from) : 'Beginning' ?></strong> to <strong><?= !empty($to) ? formatDate($to) : 'Today' ?></strong></div>
          <?php elseif (!empty($lastN)): ?>
            <div>Statement: <strong>Last <?= $lastN ?> Invoices / Records</strong></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="p-2 mb-2 border border-black bg-slate-50 flex justify-between items-center">
      <div>
        <div class="text-[7.5pt] font-bold uppercase text-[#1e3a8a]">Statement Of:</div>
        <div class="font-bold text-black text-[14pt]"><?= htmlspecialchars($party['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="text-[8pt] text-gray-700">
          <strong class="text-black">(<?= strtoupper(htmlspecialchars($partyType, ENT_QUOTES, 'UTF-8')) ?>)</strong>
          <?php if (!empty($party['phone'])): ?> &bull; Phone: <?= htmlspecialchars($party['phone'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
          <?php if (!empty($party['gstin'])): ?> &bull; GSTIN: <?= htmlspecialchars($party['gstin'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
          <?php if (!empty($party['billing_address'])): ?> &bull; Address: <?= htmlspecialchars($party['billing_address'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
        </div>
      </div>
      <div class="text-right">
        <div class="text-[7pt] uppercase text-gray-600">Net Balance Left (Due):</div>
        <div class="font-bold text-black text-[13pt]">
          ₹ <?= number_format(abs($closingBal), 2) ?>
          <span class="text-[#1e3a8a] text-[9pt]"><?= $closingBal > 0 ? '(Dr)' : ($closingBal < 0 ? '(Cr)' : '(Nil)') ?></span>
        </div>
        <div class="text-[7.5pt] font-semibold text-[#1e3a8a]">
          <?= $closingBal > 0 ? 'To Collect from Party' : ($closingBal < 0 ? 'To Pay to Party' : 'Fully Settled / No Dues') ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 print:hidden">
    <div class="rf-card p-5">
      <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Total <?= ($partyType === 'supplier') ? 'Purchases' : 'Sales' ?> Billed</div>
      <div class="text-2xl font-semibold text-[#1C1B18] mt-1.5 tabular-nums">₹ <?= number_format($billedAmount, 2) ?></div>
      <div class="text-xs text-[#78746C] mt-1">Total invoiced business</div>
    </div>

    <div class="rf-card p-5">
      <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Total Payment Done (<?= ($partyType === 'supplier') ? 'Paid' : 'Received' ?>)</div>
      <div class="text-2xl font-semibold text-[#2E5A36] mt-1.5 tabular-nums">₹ <?= number_format($paidAmount, 2) ?></div>
      <div class="text-xs text-[#78746C] mt-1">Cash / Bank / UPI settled</div>
    </div>

    <div class="rf-card p-5">
      <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Overall Balance Left (Due)</div>
      <div class="text-2xl font-semibold mt-1.5 tabular-nums <?= $closingBal > 0 ? 'text-[#8A5A00]' : ($closingBal < 0 ? 'text-[#8F2D2B]' : 'text-[#2E5A36]') ?>">
        ₹ <?= number_format(abs($closingBal), 2) ?>
      </div>
      <div class="text-xs font-medium mt-1 <?= $closingBal > 0 ? 'text-[#8A5A00]' : ($closingBal < 0 ? 'text-[#8F2D2B]' : 'text-[#2E5A36]') ?>">
        <?= $closingBal > 0 ? 'To Collect from Customer' : ($closingBal < 0 ? 'To Pay to Supplier' : 'Fully Settled / No Dues') ?>
      </div>
    </div>
  </div>

  <!-- Active Unsettled Bills -->
  <div class="rf-card print:hidden">
    <div class="rf-card-header">
      <div class="text-sm font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-receipt text-[#5C5A54]"></i>
        <span>Current Active Bills with Balance Due (Settled Oldest First)</span>
      </div>
      <span class="rf-badge rf-badge-rose tabular-nums"><?= count($pendingList) ?> Unsettled</span>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Bill Number</th>
            <th>Date</th>
            <th class="text-center">Type</th>
            <th class="text-right">Total Bill (₹)</th>
            <th class="text-right">Paid (₹)</th>
            <th class="text-right">Balance Left (₹)</th>
            <th class="text-center">Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($pendingList)): ?>
            <?php foreach ($pendingList as $b): ?>
              <tr>
                <td>
                  <a href="/invoices/view/<?= $b['id'] ?>" class="font-mono font-semibold text-[#1C1B18] hover:underline no-underline">
                    <?= htmlspecialchars($b['voucher_no'] ?? $b['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars(formatDate($b['date'] ?? $b['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <span class="rf-badge <?= ($b['type'] === 'sale') ? 'rf-badge-slate' : 'rf-badge-neutral' ?>">
                    <?= ($b['type'] === 'sale') ? 'Sale' : 'Purchase' ?>
                  </span>
                </td>
                <td class="text-right font-medium tabular-nums">₹ <?= number_format((float)($b['grand_total'] ?? 0), 2) ?></td>
                <td class="text-right text-[#2E5A36] tabular-nums">₹ <?= number_format((float)($b['paid_amount'] ?? 0), 2) ?></td>
                <td class="text-right font-semibold text-[#8F2D2B] tabular-nums">₹ <?= number_format((float)($b['balance_due'] ?? 0), 2) ?></td>
                <td class="text-center">
                  <?php if (($b['payment_status'] ?? '') === 'paid'): ?>
                    <span class="rf-badge rf-badge-sage">Paid</span>
                  <?php elseif (($b['payment_status'] ?? '') === 'partial'): ?>
                    <span class="rf-badge rf-badge-amber">Partial</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-rose">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td class="text-right">
                  <a href="/invoices/view/<?= $b['id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5">
                    <i class="bi bi-eye"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-8 text-xs text-[#78746C]">
                <i class="bi bi-check-circle text-[#2E5A36] mr-1"></i> All bills for this party are 100% paid and cleared.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Chronological Ledger -->
  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-sm font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-journal-text text-[#5C5A54] print:hidden"></i>
        <span>Complete Chronological Transaction Ledger</span>
      </h2>
    </div>

    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Voucher / Ref No</th>
            <th class="text-center">Type</th>
            <th class="text-right">Debit (+) (₹)</th>
            <th class="text-right">Credit (-) (₹)</th>
            <th class="print:hidden">Payment &amp; Settlement (FIFO)</th>
            <th class="text-right">Running Balance (₹)</th>
            <th class="text-right print:hidden">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr class="bg-[#FAF9F6]">
            <td colspan="3" class="font-semibold text-[#1C1B18]">Opening Balance</td>
            <td class="text-right text-[#87847C]">--</td>
            <td class="text-right text-[#87847C]">--</td>
            <td class="text-xs text-[#78746C] print:hidden">Initial account balance</td>
            <td class="text-right font-semibold tabular-nums">
              ₹ <?= number_format(abs($openingBal), 2) ?>
              <span class="text-xs font-medium text-[#5C5A54]"><?= $openingBal > 0 ? '(Dr)' : ($openingBal < 0 ? '(Cr)' : '') ?></span>
            </td>
            <td class="text-right text-xs text-[#87847C] print:hidden">--</td>
          </tr>

          <?php if (!empty($txList)): ?>
            <?php foreach ($txList as $tx):
              $slipUrl = ($tx['entry_type'] === 'invoice') ? ('/invoices/view/' . $tx['id']) : ('/payments/view/' . $tx['id']);
              $deb = (float)($tx['debit'] ?? 0);
              $cred = (float)($tx['credit'] ?? 0);
              $runBal = (float)($tx['running_balance'] ?? 0);
            ?>
              <tr class="ledger-slip-row cursor-pointer" data-href="<?= $slipUrl ?>" title="Click to open <?= ($tx['entry_type'] === 'invoice') ? 'Bill' : 'Payment Slip' ?> <?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <td class="text-xs text-[#5C5A54] whitespace-nowrap"><?= htmlspecialchars(formatDate($tx['date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono font-semibold">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <a href="/invoices/view/<?= $tx['id'] ?>" class="text-[#1C1B18] hover:underline no-underline">
                      <i class="bi bi-receipt mr-1 text-[#78746C] print:hidden"></i><?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php else: ?>
                    <a href="/payments/view/<?= $tx['id'] ?>" class="text-[#2E5A36] hover:underline no-underline">
                      <i class="bi bi-file-earmark-text mr-1 print:hidden"></i><?= htmlspecialchars($tx['voucher_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($tx['payment_mode'])): ?>
                    <div class="hidden print:block text-[7pt] font-normal text-gray-600">
                      <?= strtoupper(htmlspecialchars($tx['payment_mode'], ENT_QUOTES, 'UTF-8')) ?><?= !empty($tx['reference_no']) ? ' &bull; Ref: ' . htmlspecialchars($tx['reference_no'], ENT_QUOTES, 'UTF-8') : '' ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <span class="rf-badge <?= ($tx['type'] === 'sale') ? 'rf-badge-slate' : 'rf-badge-neutral' ?>">
                      <?= ($tx['type'] === 'sale') ? 'Sales Record' : 'Purchase Record' ?>
                    </span>
                  <?php else: ?>
                    <span class="rf-badge <?= ($tx['type'] === 'payment_in') ? 'rf-badge-sage' : 'rf-badge-amber' ?>">
                      <?= ($tx['type'] === 'payment_in') ? 'Receipt (In)' : 'Voucher (Out)' ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td class="text-right font-medium tabular-nums whitespace-nowrap <?= $deb > 0 ? 'text-[#2B526E]' : 'text-[#87847C]' ?>">
                  <?= $deb > 0 ? '₹ ' . number_format($deb, 2) : '--' ?>
                </td>
                <td class="text-right font-medium tabular-nums whitespace-nowrap <?= $cred > 0 ? 'text-[#2E5A36]' : 'text-[#87847C]' ?>">
                  <?= $cred > 0 ? '₹ ' . number_format($cred, 2) : '--' ?>
                </td>
                <td class="text-xs print:hidden">
                  <?php if (!empty($tx['fifo_details'])): ?>
                    <div class="font-medium text-[#1C1B18]"><?= htmlspecialchars($tx['fifo_details'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                  <?php
                    $details = array_filter([
                      !empty($tx['payment_mode']) ? strtoupper($tx['payment_mode']) : null,
                      $tx['reference_no'] ?? null,
                      $tx['notes'] ?? null
                    ]);
                    if (!empty($details)):
                  ?>
                    <div class="text-[11px] text-[#78746C]"><?= htmlspecialchars(implode(' | ', $details), ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-right font-semibold tabular-nums whitespace-nowrap <?= $runBal > 0 ? 'text-[#8A5A00]' : ($runBal < 0 ? 'text-[#8F2D2B]' : 'text-[#2E5A36]') ?>">
                  ₹ <?= number_format(abs($runBal), 2) ?>
                  <div class="text-[10px] font-normal text-[#78746C]">
                    <?= $runBal > 0 ? '(Due)' : ($runBal < 0 ? '(Advance)' : '(Cleared)') ?>
                  </div>
                </td>
                <td class="text-right print:hidden">
                  <?php if ($tx['entry_type'] === 'invoice'): ?>
                    <a href="/invoices/view/<?= $tx['id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5" title="Open Bill">
                      <i class="bi bi-eye"></i> View
                    </a>
                  <?php else: ?>
                    <a href="/payments/view/<?= $tx['id'] ?>" class="rf-btn rf-btn-sage rf-btn-sm py-1 px-2.5" title="Open Slip">
                      <i class="bi bi-file-earmark-text"></i> Slip
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-10 text-xs text-[#78746C]">
                No billing or payment transactions recorded for this party yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot class="bg-[#F8F7F4] border-t border-[#E2DFD7] print-table-total">
          <tr class="font-semibold text-[#1C1B18]">
            <td colspan="3" class="px-5 py-3.5 text-xs uppercase tracking-wider">Total &amp; Closing Balance</td>
            <td class="px-5 py-3.5 text-right tabular-nums">₹ <?= number_format($totalDebits, 2) ?></td>
            <td class="px-5 py-3.5 text-right tabular-nums">₹ <?= number_format($totalCredits, 2) ?></td>
            <td class="px-5 py-3.5 text-xs text-[#78746C] print:hidden">--</td>
            <td class="px-5 py-3.5 text-right tabular-nums text-base">
              ₹ <?= number_format(abs($closingBal), 2) ?>
              <span class="text-xs"><?= $closingBal > 0 ? '(Dr)' : ($closingBal < 0 ? '(Cr)' : '') ?></span>
            </td>
            <td class="print:hidden"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const slipRows = document.querySelectorAll('.ledger-slip-row');
    slipRows.forEach(row => {
      row.addEventListener('click', (e) => {
        if (e.target.closest('a') || e.target.closest('button')) return;
        const href = row.dataset.href;
        if (href) window.location.href = href;
      });
    });

    <?php if (!empty($autoPrint)): ?>
      setTimeout(() => { window.print(); }, 350);
    <?php endif; ?>
  });
</script>
