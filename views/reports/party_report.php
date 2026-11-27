<?php
$rec = (float)($totalReceivables ?? 0);
$pay = (float)($totalPayables ?? 0);
$partyList = !empty($parties) ? $parties : [];
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4 no-print">
    <div>
      <a href="/reports" class="text-xs text-[#78746C] hover:text-[#1C1B18] no-underline">&larr; Back to Reports</a>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight mt-1">Party &amp; Customer Wise Report</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Overview of all customer and supplier accounts and net balances</p>
    </div>
    <button type="button" class="rf-btn rf-btn-secondary btn-trigger-print">
      <i class="bi bi-printer"></i> Print Report
    </button>
  </div>

  <!-- Balance Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="rf-card p-6 flex items-center justify-between gap-4">
      <div>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Total Receivables (To Collect)</div>
        <div class="text-2xl font-semibold text-[#8A5A00] mt-1.5 tabular-nums">₹ <?= number_format($rec, 2) ?></div>
      </div>
      <div class="w-11 h-11 rounded-xl bg-[#FBF3DB] border border-[#EAE0B8] text-[#8A5A00] flex items-center justify-center text-xl shrink-0">
        <i class="bi bi-arrow-down-left-circle"></i>
      </div>
    </div>

    <div class="rf-card p-6 flex items-center justify-between gap-4">
      <div>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Total Payables (To Pay)</div>
        <div class="text-2xl font-semibold text-[#8F2D2B] mt-1.5 tabular-nums">₹ <?= number_format($pay, 2) ?></div>
      </div>
      <div class="w-11 h-11 rounded-xl bg-[#FBEAEA] border border-[#ECD0D0] text-[#8F2D2B] flex items-center justify-center text-xl shrink-0">
        <i class="bi bi-arrow-up-right-circle"></i>
      </div>
    </div>
  </div>

  <!-- Parties Summary Table -->
  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-sm font-semibold text-[#1C1B18]">Party Balances</h2>
      <div class="relative w-full sm:w-72">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="text" class="rf-input rf-input-sm pl-8" placeholder="Filter parties..." data-table-search="partyReportTable">
      </div>
    </div>

    <div class="rf-table-wrap">
      <table class="rf-table" id="partyReportTable">
        <thead>
          <tr>
            <th>Party Name</th>
            <th>Type</th>
            <th>Contact</th>
            <th>GSTIN</th>
            <th>State</th>
            <th class="text-right">Net Balance (₹)</th>
            <th class="text-right">Ledger</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($partyList)): ?>
            <?php foreach ($partyList as $p):
              $cBal = (float)($p['closing_balance'] ?? 0);
            ?>
              <tr>
                <td class="font-medium">
                  <a href="/parties/ledger/<?= $p['id'] ?>" class="text-[#1C1B18] hover:underline no-underline">
                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td>
                  <?php if ($p['type'] === 'customer'): ?>
                    <span class="rf-badge rf-badge-sage">Customer</span>
                  <?php elseif ($p['type'] === 'supplier'): ?>
                    <span class="rf-badge rf-badge-slate">Supplier</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-neutral">Both</span>
                  <?php endif; ?>
                </td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($p['phone'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($p['gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($p['state'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold tabular-nums <?= $cBal > 0 ? 'text-[#2E5A36]' : ($cBal < 0 ? 'text-[#8F2D2B]' : 'text-[#78746C]') ?>">
                  ₹ <?= number_format(abs($cBal), 2) ?>
                  <span class="text-[11px] font-normal text-[#78746C]"><?= $cBal > 0 ? '(Receivable)' : ($cBal < 0 ? '(Payable)' : '(Settled)') ?></span>
                </td>
                <td class="text-right">
                  <a href="/parties/ledger/<?= $p['id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm">
                    <i class="bi bi-journal-text"></i> Statement
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-12 text-[#78746C]">No parties found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
