<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_accounting', $userPermissions ?? [], true);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-book text-[#2B526E]"></i>
        <span>Double-Entry Journal Ledger</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Immutable balanced postings (historical adjustments use offsetting reversal entries)</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="/accounting" class="rf-btn rf-btn-secondary rf-btn-sm">Chart of Accounts</a>
      <?php if ($canManage): ?>
        <a href="/accounting/journals/create" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-plus-lg"></i> Post Manual Journal
        </a>
      <?php endif; ?>
    </div>
  </div>

  <form action="/accounting/journals" method="GET" class="rf-card p-5">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">Source</label>
        <select name="source_type" class="rf-select rf-select-sm">
          <option value="">All Sources</option>
          <?php foreach (['invoice', 'payment', 'expense', 'manual', 'reversal'] as $st): ?>
            <option value="<?= $st ?>" <?= (($filters['source_type'] ?? '') === $st) ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">From Date</label>
        <input type="date" name="from_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">To Date</label>
        <input type="date" name="to_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="md:col-span-3 flex items-center gap-2">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm flex-1">Filter</button>
        <a href="/accounting/journals" class="rf-btn rf-btn-secondary rf-btn-sm">Reset</a>
      </div>
    </div>
  </form>

  <?php if (empty($entries)): ?>
    <div class="rf-card p-10 text-center text-sm text-[#78746C]">
      No journal entries found for this filter. Use <strong class="text-[#1C1B18]">Sync Missing Historical Journals</strong> under Chart of Accounts or create a new invoice/payment/manual journal.
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($entries as $je): ?>
        <div class="rf-card">
          <div class="rf-card-header py-3 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-mono font-semibold text-[#2B526E]">JE #<?= htmlspecialchars($je['entry_number'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="text-xs text-[#78746C]"><?= htmlspecialchars(formatDate($je['entry_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></span>
              <span class="rf-badge rf-badge-neutral uppercase"><?= htmlspecialchars($je['source_type'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php if (!empty($je['is_reversed'])): ?>
                <span class="rf-badge rf-badge-rose">Reversed</span>
              <?php endif; ?>
              <?php if (!empty($je['narration'])): ?>
                <span class="text-xs font-medium text-[#1C1B18]"><?= htmlspecialchars($je['narration'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-xs text-[#5C5A54]">
                Total: <strong class="font-mono text-[#1C1B18] tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$je['total_debit'], 2) ?></strong>
              </span>
              <?php if ($canManage && empty($je['is_reversed']) && $je['source_type'] !== 'reversal'): ?>
                <form action="/accounting/journals/reverse/<?= (int)$je['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Post an offsetting reversal entry for JE #<?= htmlspecialchars($je['entry_number'], ENT_QUOTES, 'UTF-8') ?>?">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="rf-btn rf-btn-amber rf-btn-sm py-1 px-2.5 text-xs">Reverse</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <div class="rf-table-wrap">
            <table class="rf-table">
              <tbody>
                <?php foreach ($je['lines'] as $ln): ?>
                  <tr>
                    <td class="w-24 font-mono text-xs text-[#78746C]"><?= htmlspecialchars($ln['account_code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= (float)$ln['credit'] > 0 ? 'pl-8 text-[#5C5A54]' : 'font-medium text-[#1C1B18]' ?>">
                      <?= htmlspecialchars($ln['account_name'], ENT_QUOTES, 'UTF-8') ?>
                      <?php if (!empty($ln['description'])): ?>
                        <span class="text-xs text-[#78746C] ml-2">&mdash; <?= htmlspecialchars($ln['description'], ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="w-36 text-right font-mono tabular-nums"><?= (float)$ln['debit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['debit'], 2) : '' ?></td>
                    <td class="w-36 text-right font-mono tabular-nums"><?= (float)$ln['credit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['credit'], 2) : '' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
