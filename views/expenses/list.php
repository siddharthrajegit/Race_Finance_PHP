<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_expenses', $userPermissions ?? [], true);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-wallet2 text-[#8F2D2B]"></i>
        <span>Business Expenses &amp; Vouchers</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Track operating expenses, attach receipts, and automatically post accounting vouchers</p>
    </div>
    <?php if ($canManage): ?>
      <a href="/expenses/create" class="rf-btn rf-btn-primary rf-btn-sm">
        <i class="bi bi-plus-circle"></i> Record Expense
      </a>
    <?php endif; ?>
  </div>

  <!-- Summary KPIs -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rf-card p-5">
      <div class="text-xs font-medium text-[#78746C]">Filtered Expense Vouchers</div>
      <div class="text-2xl font-semibold text-[#1C1B18] tabular-nums mt-1"><?= (int)($summary['total_count'] ?? 0) ?></div>
    </div>
    <div class="rf-card p-5">
      <div class="text-xs font-medium text-[#78746C]">Total Expense Outflow</div>
      <div class="text-2xl font-semibold text-[#8F2D2B] font-mono tabular-nums mt-1"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)($summary['total_amount'] ?? 0), 2) ?></div>
    </div>
    <div class="rf-card p-5">
      <div class="text-xs font-medium text-[#78746C]">Active Filter Period</div>
      <div class="text-sm font-medium text-[#1C1B18] mt-2">
        <?php if (!empty($filters['from_date']) || !empty($filters['to_date'])): ?>
          <?= htmlspecialchars($filters['from_date'] ?: 'Start', ENT_QUOTES, 'UTF-8') ?> &rarr; <?= htmlspecialchars($filters['to_date'] ?: 'Present', ENT_QUOTES, 'UTF-8') ?>
        <?php else: ?>
          All Recorded Expenses
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Filter Bar -->
  <form action="/expenses" method="GET" class="rf-card p-5">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">Category</label>
        <select name="category" class="rf-select rf-select-sm">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <?php $catName = is_array($c) ? (string)($c['name'] ?? '') : (string)$c; ?>
            <option value="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>" <?= (($filters['category'] ?? '') === $catName) ? 'selected' : '' ?>>
              <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
            </option>
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
        <a href="/expenses" class="rf-btn rf-btn-secondary rf-btn-sm">Reset</a>
      </div>
    </div>
  </form>

  <!-- Expense Table -->
  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Expense #</th>
            <th>Category</th>
            <th>Payee / Vendor</th>
            <th>Mode</th>
            <th class="text-right">Amount</th>
            <th>Bill</th>
            <?php if ($canManage): ?><th class="text-right">Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($expenses)): ?>
            <tr>
              <td colspan="<?= $canManage ? 8 : 7 ?>" class="text-center py-10 text-[#78746C]">No business expenses recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($expenses as $ex): ?>
              <tr>
                <td class="whitespace-nowrap text-xs text-[#5C5A54]"><?= htmlspecialchars(formatDate($ex['expense_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono font-semibold text-[#1C1B18]"><?= htmlspecialchars($ex['expense_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="rf-badge rf-badge-neutral"><?= htmlspecialchars($ex['category'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                  <div class="font-medium text-[#1C1B18]"><?= htmlspecialchars($ex['payee'] ?: '--', ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($ex['reference_no'])): ?>
                    <div class="text-xs text-[#78746C] font-mono">Ref: <?= htmlspecialchars($ex['reference_no'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-xs uppercase text-[#5C5A54]"><?= htmlspecialchars(str_replace('_', ' ', $ex['payment_mode'] ?? 'cash'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-semibold font-mono text-[#8F2D2B] tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$ex['amount'], 2) ?></td>
                <td>
                  <?php if (!empty($ex['attachment_path'])): ?>
                    <a href="/uploads/expenses/<?= htmlspecialchars(rawurlencode(basename($ex['attachment_path'])), ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2 text-xs">
                      <i class="bi bi-paperclip"></i> View
                    </a>
                  <?php else: ?>
                    <span class="text-xs text-[#78746C]">--</span>
                  <?php endif; ?>
                </td>
                <?php if ($canManage): ?>
                  <td class="text-right">
                    <div class="inline-flex items-center gap-1.5">
                      <a href="/expenses/edit/<?= (int)$ex['id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5 text-xs">Edit</a>
                      <form action="/expenses/delete/<?= (int)$ex['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Delete expense <?= htmlspecialchars($ex['expense_number'], ENT_QUOTES, 'UTF-8') ?>?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-btn rf-btn-danger rf-btn-sm py-1 px-2.5 text-xs">Delete</button>
                      </form>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
