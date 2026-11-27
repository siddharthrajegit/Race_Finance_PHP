<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-graph-up-arrow text-[#2E5A36]"></i>
        <span>Profit &amp; Loss Statement</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Income and expense accounts compiled from double-entry journal postings</p>
    </div>
    <a href="/accounting" class="rf-btn rf-btn-secondary rf-btn-sm">Accounting Hub</a>
  </div>

  <form action="/accounting/profit-loss" method="GET" class="rf-card p-5">
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
        <a href="/accounting/profit-loss" class="rf-btn rf-btn-secondary rf-btn-sm">All Time</a>
      </div>
    </div>
  </form>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">
    <div class="rf-card flex flex-col">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#2E5A36]">Revenue &amp; Income</h2>
      </div>
      <div class="rf-table-wrap flex-1">
        <table class="rf-table">
          <tbody>
            <?php if (empty($pnl['income'])): ?>
              <tr><td class="text-center py-8 text-[#78746C]">No revenue entries</td></tr>
            <?php else: foreach ($pnl['income'] as $r): ?>
              <tr>
                <td class="w-20 font-mono text-xs text-[#78746C]"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-[#1C1B18]"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['amount'], 2) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <tfoot class="bg-[#FAF9F6] font-semibold border-t border-[#E2DFD7]">
            <tr>
              <td colspan="2" class="text-[#1C1B18]">Total Income</td>
              <td class="text-right font-mono tabular-nums text-[#2E5A36]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$pnl['total_income'], 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="rf-card flex flex-col">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#8F2D2B]">Purchases &amp; Operating Expenses</h2>
      </div>
      <div class="rf-table-wrap flex-1">
        <table class="rf-table">
          <tbody>
            <?php if (empty($pnl['expenses'])): ?>
              <tr><td class="text-center py-8 text-[#78746C]">No expense entries</td></tr>
            <?php else: foreach ($pnl['expenses'] as $r): ?>
              <tr>
                <td class="w-20 font-mono text-xs text-[#78746C]"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-[#1C1B18]"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['amount'], 2) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <tfoot class="bg-[#FAF9F6] font-semibold border-t border-[#E2DFD7]">
            <tr>
              <td colspan="2" class="text-[#1C1B18]">Total Expenses</td>
              <td class="text-right font-mono tabular-nums text-[#8F2D2B]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$pnl['total_expense'], 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="rf-card p-5 flex items-center justify-between gap-4">
    <h2 class="text-base font-semibold text-[#1C1B18]">Net Profit / (Loss)</h2>
    <div class="text-2xl font-semibold font-mono tabular-nums <?= $pnl['net_profit'] >= 0 ? 'text-[#2E5A36]' : 'text-[#8F2D2B]' ?>">
      <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$pnl['net_profit'], 2) ?>
    </div>
  </div>
</div>
