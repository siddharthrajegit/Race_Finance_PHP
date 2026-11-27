<?php
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canTransfer = in_array('*', $userPermissions ?? [], true) || in_array('transfer_stock', $userPermissions ?? [], true);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-arrow-left-right text-[#2B526E]"></i>
        <span>Inter-Warehouse Stock Transfers</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Complete audit history of stock movements between firm godowns</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="/warehouses" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Warehouses</a>
      <?php if ($canTransfer): ?>
        <a href="/warehouses/transfers/create" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-plus-circle"></i> New Transfer
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Transfer #</th>
            <th>Date</th>
            <th>Source Warehouse</th>
            <th>Destination Warehouse</th>
            <th class="text-center">Items</th>
            <th class="text-right">Total Qty</th>
            <th>Created By</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($transfers)): ?>
            <tr>
              <td colspan="8" class="text-center py-10 text-[#78746C]">No inter-warehouse stock transfers recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($transfers as $tr): ?>
              <tr>
                <td class="font-mono font-semibold text-[#2B526E]"><?= htmlspecialchars($tr['transfer_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="whitespace-nowrap text-xs text-[#5C5A54]"><?= htmlspecialchars(formatDate($tr['transfer_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($tr['source_warehouse_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-medium text-[#2E5A36]"><?= htmlspecialchars($tr['dest_warehouse_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><span class="rf-badge rf-badge-neutral tabular-nums"><?= (int)$tr['items_count'] ?></span></td>
                <td class="text-right font-semibold tabular-nums"><?= number_format((float)$tr['total_qty'], 2) ?></td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($tr['created_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#78746C]"><?= htmlspecialchars($tr['notes'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
