<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4 no-print">
    <div>
      <a href="/reports" class="text-xs text-[#78746C] hover:text-[#1C1B18] no-underline">&larr; Back to Reports</a>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight mt-1">Item-Wise Sales Report</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Track product performance, units sold, and total sales revenue</p>
    </div>
    <button type="button" class="rf-btn rf-btn-secondary btn-trigger-print">
      <i class="bi bi-printer"></i> Print Report
    </button>
  </div>

  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-sm font-semibold text-[#1C1B18]">Product Sales &amp; Inventory</h2>
      <div class="relative w-full sm:w-72">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="text" class="rf-input rf-input-sm pl-8" placeholder="Filter items..." data-table-search="itemReportTable">
      </div>
    </div>

    <div class="rf-table-wrap">
      <table class="rf-table" id="itemReportTable">
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Item Code</th>
            <th class="text-center">Current Stock</th>
            <th class="text-center">Total Sold Qty</th>
            <th class="text-right">Total Sales Revenue (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $it): ?>
              <tr>
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($it['item_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <span class="rf-badge rf-badge-neutral tabular-nums">
                    <?= htmlspecialchars((string)($it['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </td>
                <td class="text-center font-medium text-[#2B526E] tabular-nums">
                  <?= htmlspecialchars((string)($it['total_sold_qty'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="text-right font-semibold text-[#2E5A36] tabular-nums">
                  ₹ <?= number_format((float)($it['total_sales_value'] ?? 0), 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center py-12 text-[#78746C]">No item sales recorded yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
