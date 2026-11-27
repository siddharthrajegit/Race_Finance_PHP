<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-columns-gap text-[#2B526E]"></i>
        <span>Balance Sheet</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Statement of financial position as of <?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <form action="/accounting/balance-sheet" method="GET" class="flex items-center gap-2 m-0">
        <input type="date" name="as_of" class="rf-input rf-input-sm" value="<?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Apply</button>
      </form>
      <a href="/accounting" class="rf-btn rf-btn-secondary rf-btn-sm">Accounting Hub</a>
    </div>
  </div>

  <div class="rf-alert mb-0 <?= !empty($bs['is_balanced']) ? 'border-[#C8E0CB] bg-[#F2F8F3] text-[#2E5A36]' : 'rf-alert-warning' ?>">
    <i class="bi <?= !empty($bs['is_balanced']) ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
    <span class="text-xs font-semibold">
      <?= !empty($bs['is_balanced']) ? 'Accounting Equation Verified: Total Assets = Total Liabilities + Equity.' : 'Balance Sheet shows variance — verify opening balances.' ?>
    </span>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">
    <div class="rf-card flex flex-col">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#2B526E]">Assets</h2>
      </div>
      <div class="rf-table-wrap flex-1">
        <table class="rf-table">
          <tbody>
            <?php foreach ($bs['assets'] as $r): ?>
              <tr>
                <td class="w-20 font-mono text-xs text-[#78746C]"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-[#1C1B18]"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['balance'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="bg-[#FAF9F6] font-semibold border-t border-[#E2DFD7]">
            <tr>
              <td colspan="2" class="text-[#1C1B18]">Total Assets</td>
              <td class="text-right font-mono tabular-nums text-[#2B526E]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$bs['total_assets'], 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="rf-card flex flex-col">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#1C1B18]">Liabilities &amp; Owner Equity</h2>
      </div>
      <div class="rf-table-wrap flex-1">
        <table class="rf-table">
          <tbody>
            <tr class="bg-[#F2F0EB]">
              <td colspan="3" class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Liabilities</td>
            </tr>
            <?php foreach ($bs['liabilities'] as $r): ?>
              <tr>
                <td class="w-20 font-mono text-xs text-[#78746C]"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-[#1C1B18]"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['balance'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="bg-[#F2F0EB]">
              <td colspan="3" class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Equity &amp; Earnings</td>
            </tr>
            <?php foreach ($bs['equity'] as $r): ?>
              <tr>
                <td class="w-20 font-mono text-xs text-[#78746C]"><?= htmlspecialchars($r['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-[#1C1B18]"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$r['balance'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="bg-[#FAF9F6] font-semibold border-t border-[#E2DFD7]">
            <tr>
              <td colspan="2" class="text-[#1C1B18]">Total Liabilities + Equity</td>
              <td class="text-right font-mono tabular-nums text-[#2B526E]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$bs['total_liabilities_and_equity'], 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
