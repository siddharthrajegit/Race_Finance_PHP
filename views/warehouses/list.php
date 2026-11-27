<?php
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_warehouses', $userPermissions ?? [], true);
$canTransfer = in_array('*', $userPermissions ?? [], true) || in_array('transfer_stock', $userPermissions ?? [], true);
$canAdjust = in_array('*', $userPermissions ?? [], true) || in_array('adjust_stock', $userPermissions ?? [], true);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-buildings text-[#2B526E]"></i>
        <span>Multi-Warehouse &amp; Godown Management</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Manage storage locations, inspect godown-level stock, and track inter-godown transfers</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/warehouses/transfers" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-arrow-left-right"></i> Transfer History
      </a>
      <?php if ($canTransfer): ?>
        <a href="/warehouses/transfers/create" class="rf-btn rf-btn-amber rf-btn-sm">
          <i class="bi bi-truck"></i> Transfer Stock
        </a>
      <?php endif; ?>
      <a href="/warehouses/valuation" class="rf-btn rf-btn-sage rf-btn-sm">
        <i class="bi bi-cash-coin"></i> Stock Valuation
      </a>
      <?php if ($canManage): ?>
        <a href="/warehouses/create" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-plus-circle"></i> Add Warehouse
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Warehouse Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($warehouses as $w): ?>
      <?php $isSelected = ($selectedWarehouse && (int)$selectedWarehouse['id'] === (int)$w['id']); ?>
      <div class="rf-card flex flex-col <?= $isSelected ? 'ring-2 ring-[#1C1B18] border-[#1C1B18]' : '' ?>">
        <div class="rf-card-body flex-1 space-y-3">
          <div class="flex items-start justify-between gap-2">
            <div>
              <h2 class="text-sm font-semibold text-[#1C1B18] flex flex-wrap items-center gap-1.5">
                <span><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($w['is_default'])): ?>
                  <span class="rf-badge rf-badge-slate text-[10px]">Default</span>
                <?php endif; ?>
                <?php if (empty($w['is_active'])): ?>
                  <span class="rf-badge rf-badge-rose text-[10px]">Inactive</span>
                <?php endif; ?>
              </h2>
              <div class="text-xs text-[#78746C] font-mono mt-0.5">Code: <?= htmlspecialchars($w['code'] ?: ('WH-' . $w['id']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php if ($canManage): ?>
              <div class="rf-dropdown" data-ui-dropdown>
                <button class="w-7 h-7 rounded-full hover:bg-[#EFECE6] inline-flex items-center justify-center text-[#5C5A54] bg-transparent border-0 cursor-pointer transition-colors" type="button" data-ui-dropdown-toggle aria-expanded="false" title="Actions">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="rf-dropdown-menu" data-ui-dropdown-menu>
                  <a class="rf-dropdown-item" href="/warehouses/edit/<?= (int)$w['id'] ?>">
                    <i class="bi bi-pencil text-[#2B526E]"></i> Edit Warehouse
                  </a>
                  <?php if (empty($w['is_default'])): ?>
                    <div class="rf-dropdown-divider"></div>
                    <form action="/warehouses/delete/<?= (int)$w['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Delete warehouse <?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?>?">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
                        <i class="bi bi-trash"></i> Delete Warehouse
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <?php if (!empty($w['address'])): ?>
            <div class="text-xs text-[#5C5A54] leading-relaxed"><?= htmlspecialchars($w['address'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>

          <div class="grid grid-cols-2 gap-3 pt-2 border-t border-[#ECEAE4]">
            <div>
              <div class="text-[11px] uppercase tracking-wider text-[#78746C] font-semibold">Active Items</div>
              <div class="text-base font-semibold text-[#1C1B18] tabular-nums mt-0.5"><?= (int)$w['active_items_count'] ?></div>
            </div>
            <div class="text-right">
              <div class="text-[11px] uppercase tracking-wider text-[#78746C] font-semibold">Total Units</div>
              <div class="text-base font-semibold text-[#2B526E] tabular-nums mt-0.5"><?= number_format((float)$w['total_units'], 2) ?></div>
            </div>
          </div>
        </div>

        <div class="rf-card-footer py-2.5 justify-between">
          <a href="/warehouses?warehouse_id=<?= (int)$w['id'] ?>" class="rf-btn rf-btn-sm w-full <?= $isSelected ? 'rf-btn-primary' : 'rf-btn-secondary' ?>">
            <?= $isSelected ? 'Viewing This Warehouse' : 'View Warehouse Stock' ?>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Selected Warehouse Stock Table -->
  <?php if ($selectedWarehouse): ?>
    <div class="rf-card">
      <div class="rf-card-header">
        <div class="flex items-center gap-2">
          <i class="bi bi-boxes text-[#2B526E]"></i>
          <h2 class="text-sm font-semibold text-[#1C1B18]">Stock in <?= htmlspecialchars($selectedWarehouse['name'], ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <?php if ($canAdjust): ?>
          <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">Quick Adjust Stock on Items Page</a>
        <?php endif; ?>
      </div>
      <div class="rf-table-wrap">
        <table class="rf-table">
          <thead>
            <tr>
              <th>Item Name</th>
              <th>SKU / Code</th>
              <th>Category</th>
              <th class="text-right">Qty in This Warehouse</th>
              <th class="text-right">Total Across All Warehouses</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($stockRows)): ?>
              <tr>
                <td colspan="5" class="text-center py-10 text-[#78746C]">No items found in this firm yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($stockRows as $row): ?>
                <tr>
                  <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($row['item_code'] ?: '--', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($row['category'] ?: 'General', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-right font-semibold tabular-nums <?= (float)$row['warehouse_qty'] > 0 ? 'text-[#2E5A36]' : 'text-[#78746C]' ?>">
                    <?= number_format((float)$row['warehouse_qty'], 2) ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td class="text-right text-xs text-[#5C5A54] tabular-nums">
                    <?= number_format((float)$row['total_stock'], 2) ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
