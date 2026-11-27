<?php include __DIR__ . '/_nav.php'; ?>

<!-- Filter & Search Toolbar -->
<div class="rf-card mb-6">
  <div class="rf-card-body">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
      <form action="/admin/invoices" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
        <div class="relative w-full sm:w-72">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none">
            <i class="bi bi-search text-xs"></i>
          </span>
          <input type="text" name="search" class="rf-input rf-input-sm pl-8" placeholder="Search invoice #, party, firm..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <select name="type" class="rf-select rf-select-sm w-36" onchange="this.form.submit()">
          <option value="">All Types</option>
          <option value="sale" <?= ($type ?? '') === 'sale' ? 'selected' : '' ?>>Sales</option>
          <option value="purchase" <?= ($type ?? '') === 'purchase' ? 'selected' : '' ?>>Purchases</option>
        </select>

        <select name="payment_status" class="rf-select rf-select-sm w-40" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="paid" <?= ($payment_status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
          <option value="partial" <?= ($payment_status ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
          <option value="unpaid" <?= ($payment_status ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
        </select>

        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Filter</button>
        <?php if (!empty($search) || !empty($type) || !empty($payment_status)): ?>
          <a href="/admin/invoices" class="rf-btn rf-btn-ghost rf-btn-sm">
            <i class="bi bi-x-circle"></i> Clear
          </a>
        <?php endif; ?>
      </form>

      <div class="text-xs text-ink-muted font-mono shrink-0">
        <strong class="text-ink"><?= count($invoices ?? []) ?></strong> records shown
      </div>
    </div>
  </div>
</div>

<!-- Platform Invoices & Ledger Table -->
<div class="rf-card overflow-hidden">
  <div class="rf-table-wrap border-0 rounded-none">
    <table class="rf-table">
      <thead>
        <tr>
          <th>Record #</th>
          <th>Firm &amp; Subscriber</th>
          <th>Party Details</th>
          <th>Date</th>
          <th class="text-center">Type</th>
          <th class="text-right">Grand Total (₹)</th>
          <th class="text-right">Balance Due (₹)</th>
          <th class="text-center">Status</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($invoices)): ?>
          <?php foreach ($invoices as $inv):
            $invType = $inv['type'] ?? 'sale';
            $pStatus = $inv['payment_status'] ?? 'unpaid';
            $due = (float)($inv['balance_due'] ?? 0);
          ?>
            <tr>
              <td class="font-mono font-semibold text-ink whitespace-nowrap">
                <a href="/admin/invoices/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="hover:underline">
                  <?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>
              <td>
                <div class="font-medium text-ink text-xs"><?= htmlspecialchars($inv['firm_name'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-ink-muted text-[11px] mt-0.5">
                  <?= htmlspecialchars($inv['user_name'] ?? '--', ENT_QUOTES, 'UTF-8') ?>
                  &bull;
                  <span class="font-mono"><?= htmlspecialchars($inv['user_phone'] ?? ($inv['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              </td>
              <td>
                <div class="font-medium text-ink text-xs"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($inv['party_phone'])): ?>
                  <div class="text-ink-muted font-mono text-[11px]"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
              </td>
              <td class="text-ink-muted font-mono text-xs whitespace-nowrap">
                <?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td class="text-center">
                <span class="rf-badge <?= $invType === 'purchase' ? 'rf-badge-amber' : 'rf-badge-sage' ?>">
                  <?= strtoupper($invType) ?>
                </span>
              </td>
              <td class="text-right font-semibold font-mono text-ink whitespace-nowrap">
                ₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?>
              </td>
              <td class="text-right font-mono whitespace-nowrap <?= $due > 0 ? 'text-rose font-semibold' : 'text-ink-muted' ?>">
                ₹ <?= number_format($due, 2) ?>
              </td>
              <td class="text-center">
                <?php if ($pStatus === 'paid'): ?>
                  <span class="rf-badge rf-badge-sage">PAID</span>
                <?php elseif ($pStatus === 'partial'): ?>
                  <span class="rf-badge rf-badge-amber">PARTIAL</span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-rose">UNPAID</span>
                <?php endif; ?>
              </td>
              <td class="text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5">
                  <a href="/admin/invoices/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm" title="Read-Only Admin Inspector">
                    <i class="bi bi-eye"></i> Inspect
                  </a>
                  <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="rf-btn rf-btn-ghost rf-btn-sm" title="Open Print / Download View">
                    <i class="bi bi-printer"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center py-12 text-ink-muted">
              <i class="bi bi-receipt text-3xl block mb-2 text-ink-subtle"></i>
              No invoice records match your filters.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
