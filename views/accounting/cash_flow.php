<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-cash-coin text-[#2B526E]"></i>
        <span>Cash Flow Statement (Direct Method)</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Tracks actual inflows &amp; outflows on Cash (1000) and Bank (1100) ledger accounts</p>
    </div>
    <a href="/accounting" class="rf-btn rf-btn-secondary rf-btn-sm">Accounting Hub</a>
  </div>

  <div class="rf-alert border-[#CFDFEB] bg-[#F4F8FB] text-[#2B526E] mb-0">
    <i class="bi bi-info-circle mt-0.5 shrink-0"></i>
    <div class="text-xs leading-relaxed">
      <strong>Scope Notice:</strong> Computed using the direct method from posted Cash (1000) and Bank (1100) journal movements. Unpaid credit invoices and non-cash depreciation are excluded by definition.
    </div>
  </div>

  <form action="/accounting/cash-flow" method="GET" class="rf-card p-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <div>
        <label class="rf-label-plain text-xs">From Date</label>
        <input type="date" name="from_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div>
        <label class="rf-label-plain text-xs">To Date</label>
        <input type="date" name="to_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="flex items-center gap-2">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm flex-1">Generate</button>
        <a href="/accounting/cash-flow" class="rf-btn rf-btn-secondary rf-btn-sm">All Time</a>
      </div>
    </div>
  </form>

  <div class="rf-card max-w-3xl">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <tbody>
          <tr>
            <td class="font-medium text-[#1C1B18]">Opening Cash &amp; Bank Balance</td>
            <td class="text-right font-semibold font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['opening_cash'], 2) ?></td>
          </tr>
          <tr>
            <td class="pl-8 text-[#2E5A36]">Operating Cash &amp; Bank Inflows (Receipts)</td>
            <td class="text-right font-mono tabular-nums text-[#2E5A36]">+ <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['operating_inflows'], 2) ?></td>
          </tr>
          <tr>
            <td class="pl-8 text-[#8F2D2B]">Operating Cash &amp; Bank Outflows (Payments &amp; Expenses)</td>
            <td class="text-right font-mono tabular-nums text-[#8F2D2B]">- <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['operating_outflows'], 2) ?></td>
          </tr>
          <tr class="bg-[#FAF9F6] font-semibold">
            <td class="text-[#1C1B18]">Net Operating Cash Flow</td>
            <td class="text-right font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['net_operating_cash'], 2) ?></td>
          </tr>
          <tr>
            <td class="text-[#5C5A54]">Other Manual Cash / Capital Movements</td>
            <td class="text-right font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['other_manual_cash_net'], 2) ?></td>
          </tr>
          <tr class="bg-[#F2F0EB] font-semibold border-t border-[#E2DFD7]">
            <td class="text-[#1C1B18]">Closing Cash &amp; Bank Balance</td>
            <td class="text-right text-lg font-mono tabular-nums text-[#2B526E]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$cf['closing_cash'], 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
