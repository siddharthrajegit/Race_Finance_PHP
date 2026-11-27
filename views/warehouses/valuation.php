<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-cash-coin text-[#2E5A36]"></i>
        <span>Inventory Valuation (FIFO &amp; Weighted Average)</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Real-time closing stock asset value using purchase cost layers</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/warehouses/valuation?method=fifo" class="rf-btn rf-btn-sm <?= ($valuation['method'] === 'fifo') ? 'rf-btn-primary' : 'rf-btn-secondary' ?>">FIFO Method</a>
      <a href="/warehouses/valuation?method=weighted_avg" class="rf-btn rf-btn-sm <?= ($valuation['method'] === 'weighted_avg') ? 'rf-btn-primary' : 'rf-btn-secondary' ?>">Weighted Average</a>
      <a href="/warehouses" class="rf-btn rf-btn-secondary rf-btn-sm">Warehouses</a>
      <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">Items</a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rf-card p-5">
      <div class="text-xs font-medium text-[#78746C]">Active Valuation Method</div>
      <div class="text-lg font-semibold text-[#1C1B18] mt-1">
        <?= ($valuation['method'] === 'weighted_avg') ? 'Weighted Average Cost' : 'First-In, First-Out (FIFO)' ?>
      </div>
      <div class="text-xs text-[#78746C] mt-1 tabular-nums">Total Stock Units: <?= number_format((float)$valuation['total_qty'], 2) ?></div>
    </div>

    <div class="rf-card p-5">
      <div class="text-xs font-medium text-[#78746C]">Total Inventory Cost Value</div>
      <div class="text-xl font-semibold text-[#2B526E] font-mono tabular-nums mt-1">
        <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$valuation['total_value'], 2) ?>
      </div>
      <div class="text-xs text-[#78746C] mt-1">Accounting Asset Value on Hand</div>
    </div>

    <div class="rf-card p-5">
      <div class="text-xs font-medium text-[#78746C]">Potential Sales Value (Retail)</div>
      <div class="text-xl font-semibold text-[#2E5A36] font-mono tabular-nums mt-1">
        <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$valuation['total_sale_potential'], 2) ?>
      </div>
      <div class="text-xs text-[#78746C] mt-1">At configured item sale prices</div>
    </div>
  </div>

  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Category</th>
            <th class="text-right">On-Hand Qty</th>
            <th class="text-right">Valuation Unit Cost</th>
            <th class="text-right">Stock Asset Value</th>
            <th class="text-right">Sale Value Potential</th>
            <th class="text-right">Est. Margin</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($valuation['items'])): ?>
            <tr>
              <td colspan="7" class="text-center py-10 text-[#78746C]">No items with inventory found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($valuation['items'] as $row): ?>
              <tr>
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($row['category'] ?: 'General', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right tabular-nums"><?= number_format((float)$row['on_hand_qty'], 2) ?> <?= htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['unit_cost'], 2) ?></td>
                <td class="text-right font-semibold font-mono text-[#2B526E] tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['stock_value'], 2) ?></td>
                <td class="text-right font-mono tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['sale_potential'], 2) ?></td>
                <td class="text-right font-semibold font-mono tabular-nums <?= (float)$row['estimated_margin'] >= 0 ? 'text-[#2E5A36]' : 'text-[#8F2D2B]' ?>">
                  <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$row['estimated_margin'], 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
