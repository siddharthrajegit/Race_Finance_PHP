<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-check2-square text-[#2B526E]"></i>
        <span>Trial Balance</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">As of <?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <form action="/accounting/trial-balance" method="GET" class="flex items-center gap-2 m-0">
        <input type="date" name="as_of" class="rf-input rf-input-sm" value="<?= htmlspecialchars($asOf, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Apply</button>
      </form>
      <a href="/accounting" class="rf-btn rf-btn-secondary rf-btn-sm">Accounting Hub</a>
    </div>
  </div>

  <div class="rf-alert mb-0 <?= !empty($tb['is_balanced']) ? 'border-[#C8E0CB] bg-[#F2F8F3] text-[#2E5A36]' : 'border-[#F0C8C7] bg-[#FDF2F2] text-[#8F2D2B]' ?>">
    <i class="bi <?= !empty($tb['is_balanced']) ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
    <span class="text-xs font-semibold">
      <?= !empty($tb['is_balanced']) ? 'Trial Balance is IN BALANCE (Total Debits = Total Credits).' : 'Trial Balance variance detected.' ?>
    </span>
  </div>

  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Account Name</th>
            <th>Group</th>
            <th class="text-right">Debit Balance</th>
            <th class="text-right">Credit Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tb['rows'] as $row): ?>
            <tr>
              <td class="font-mono text-xs text-[#78746C]"><?= htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-xs uppercase text-[#78746C]"><?= htmlspecialchars($row['account_type'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-right font-mono tabular-nums"><?= $row['net_debit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$row['net_debit'], 2) : '--' ?></td>
              <td class="text-right font-mono tabular-nums"><?= $row['net_credit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$row['net_credit'], 2) : '--' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="bg-[#FAF9F6] font-semibold border-t border-[#E2DFD7]">
          <tr>
            <td colspan="3" class="text-right text-xs uppercase tracking-wider text-[#5C5A54]">Totals:</td>
            <td class="text-right font-mono tabular-nums text-[#2B526E]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$tb['total_debit'], 2) ?></td>
            <td class="text-right font-mono tabular-nums text-[#2B526E]"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$tb['total_credit'], 2) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
