<?php
$canTransfer = in_array('*', $userPermissions ?? [], true) || in_array('transfer_stock', $userPermissions ?? [], true);
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right text-primary me-2"></i>Inter-Warehouse Stock Transfers</h3>
    <small class="text-muted">Audit log of atomic stock movements between storage locations</small>
  </div>
  <div class="d-flex gap-2">
    <a href="/warehouses" class="btn btn-outline-secondary btn-sm">Back to Warehouses</a>
    <?php if ($canTransfer): ?>
      <a href="/warehouses/transfers/create" class="btn btn-primary btn-sm fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> New Transfer
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>Transfer #</th>
          <th>Date</th>
          <th>From Warehouse</th>
          <th>To Warehouse</th>
          <th class="text-center">Items</th>
          <th class="text-end">Total Qty</th>
          <th>Recorded By</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($transfers)): ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No stock transfers recorded yet.</td></tr>
        <?php else: ?>
          <?php foreach ($transfers as $t): ?>
            <tr>
              <td class="fw-bold font-monospace text-primary">#<?= htmlspecialchars($t['transfer_number'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(formatDate($t['transfer_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge bg-warning-subtle text-dark border"><?= htmlspecialchars($t['source_warehouse_name'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="badge bg-success-subtle text-success border"><?= htmlspecialchars($t['dest_warehouse_name'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td class="text-center"><?= (int)$t['items_count'] ?></td>
              <td class="text-end fw-semibold"><?= number_format((float)$t['total_qty'], 2) ?></td>
              <td class="small text-muted"><?= htmlspecialchars($t['created_by_name'] ?? 'Owner', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small text-muted"><?= htmlspecialchars($t['notes'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
