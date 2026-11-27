<?php $canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_accounting', $userPermissions ?? [], true); ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-diagram-3 text-[#2B526E]"></i>
        <span>Chart of Accounts</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Double-entry ledger structure &amp; financial statements</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/accounting/journals" class="rf-btn rf-btn-secondary rf-btn-sm">Journals</a>
      <a href="/accounting/trial-balance" class="rf-btn rf-btn-secondary rf-btn-sm">Trial Balance</a>
      <a href="/accounting/profit-loss" class="rf-btn rf-btn-sage rf-btn-sm">Profit &amp; Loss</a>
      <a href="/accounting/balance-sheet" class="rf-btn rf-btn-secondary rf-btn-sm">Balance Sheet</a>
      <a href="/accounting/cash-flow" class="rf-btn rf-btn-secondary rf-btn-sm">Cash Flow</a>
      <a href="/accounting/reconciliation" class="rf-btn rf-btn-amber rf-btn-sm">Bank Reconciliation</a>
    </div>
  </div>

  <?php if ($canManage): ?>
    <div class="rf-card-muted px-4 py-3 flex flex-wrap items-center justify-between gap-3">
      <span class="text-xs text-[#5C5A54]">Need to sync older pre-existing bills, payments, or expenses into journals?</span>
      <form action="/accounting/backfill" method="POST" class="m-0">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="rf-btn rf-btn-secondary rf-btn-sm">Sync Missing Historical Journals</button>
      </form>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
    <div class="<?= $canManage ? 'lg:col-span-8' : 'lg:col-span-12' ?> rf-card">
      <div class="rf-table-wrap">
        <table class="rf-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Account Name</th>
              <th>Type</th>
              <th class="text-right">Opening Balance</th>
              <th>Origin</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($accounts as $acc): ?>
              <tr>
                <td class="font-mono font-semibold text-[#2B526E]"><?= htmlspecialchars($acc['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($acc['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="rf-badge rf-badge-neutral uppercase"><?= htmlspecialchars($acc['account_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="text-right font-mono tabular-nums">
                  <?= number_format((float)$acc['opening_balance'], 2) ?>
                  <span class="text-xs text-[#78746C]"><?= strtoupper(substr((string)$acc['opening_side'], 0, 2)) ?></span>
                </td>
                <td>
                  <?= !empty($acc['is_system']) ? '<span class="rf-badge rf-badge-neutral">System</span>' : '<span class="rf-badge rf-badge-slate">Custom</span>' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($canManage): ?>
      <form action="/accounting/accounts/create" method="POST" class="lg:col-span-4 rf-card">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="rf-card-header py-3">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Add Custom Ledger Account</h2>
        </div>
        <div class="rf-card-body space-y-3.5">
          <div>
            <label class="rf-label-plain text-xs">Account Code <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" name="code" class="rf-input rf-input-sm font-mono" required placeholder="e.g. 1150">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Account Name <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" name="name" class="rf-input rf-input-sm" required placeholder="e.g. HDFC Current A/C">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Account Type <span class="text-[#8F2D2B]">*</span></label>
            <select name="account_type" class="rf-select rf-select-sm" required>
              <option value="asset">Asset</option>
              <option value="liability">Liability</option>
              <option value="equity">Equity / Capital</option>
              <option value="income">Income</option>
              <option value="expense">Expense</option>
            </select>
          </div>
          <div class="grid grid-cols-12 gap-3">
            <div class="col-span-7">
              <label class="rf-label-plain text-xs">Opening Balance</label>
              <input type="number" name="opening_balance" class="rf-input rf-input-sm text-right font-mono tabular-nums" step="0.01" min="0" value="0.00">
            </div>
            <div class="col-span-5">
              <label class="rf-label-plain text-xs">Normal Side</label>
              <select name="opening_side" class="rf-select rf-select-sm">
                <option value="">Auto</option>
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
              </select>
            </div>
          </div>
        </div>
        <div class="rf-card-footer justify-end py-3">
          <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Add Account</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
