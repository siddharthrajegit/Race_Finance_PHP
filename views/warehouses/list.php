<?php
$canManageWh = in_array('*', $userPermissions ?? [], true) || in_array('manage_warehouses', $userPermissions ?? [], true);
$canTransfer = in_array('*', $userPermissions ?? [], true) || in_array('transfer_stock', $userPermissions ?? [], true);
$canValuation = in_array('*', $userPermissions ?? [], true) || in_array('adjust_stock', $userPermissions ?? [], true);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-buildings text-primary me-2"></i>Warehouses &amp; Godowns</h3>
    <small class="text-muted">Track stock balances per storage location and execute inter-warehouse transfers</small>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="/warehouses/transfers" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left-right me-1"></i> Transfers History
    </a>
    <?php if ($canTransfer): ?>
      <a href="/warehouses/transfers/create" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-box-arrow-right me-1"></i> Transfer Stock
      </a>
    <?php endif; ?>
    <?php if ($canValuation): ?>
      <a href="/warehouses/valuation" class="btn btn-outline-success btn-sm">
        <i class="bi bi-calculator me-1"></i> Stock Valuation
      </a>
    <?php endif; ?>
    <?php if ($canManageWh): ?>
      <a href="/warehouses/create" class="btn btn-primary btn-sm fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> Add Warehouse
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <!-- Warehouse List -->
  <div class="col-lg-5">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-2 fw-bold text-dark small text-uppercase">
        Locations (<?= count($warehouses) ?>)
      </div>
      <div class="list-group list-group-flush">
        <?php foreach ($warehouses as $wh): ?>
          <?php $isSel = $selectedWarehouse && (int)$selectedWarehouse['id'] === (int)$wh['id']; ?>
          <div class="list-group-item <?= $isSel ? 'bg-primary-subtle' : '' ?> p-3">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div>
                <a href="/warehouses?warehouse_id=<?= (int)$wh['id'] ?>" class="fw-bold text-decoration-none text-dark d-flex align-items-center gap-1">
                  <span><?= htmlspecialchars($wh['name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (!empty($wh['code'])): ?>
                    <span class="badge bg-light text-secondary border font-monospace"><?= htmlspecialchars($wh['code'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                  <?php if (!empty($wh['is_default'])): ?>
                    <span class="badge bg-primary" style="font-size:0.65rem;">DEFAULT</span>
                  <?php endif; ?>
                  <?php if (empty($wh['is_active'])): ?>
                    <span class="badge bg-secondary" style="font-size:0.65rem;">INACTIVE</span>
                  <?php endif; ?>
                </a>
                <?php if (!empty($wh['address'])): ?>
                  <div class="small text-muted mt-1"><?= htmlspecialchars($wh['address'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="small text-secondary mt-1">
                  Active SKUs: <strong><?= (int)$wh['active_items_count'] ?></strong> &bull;
                  Total Units: <strong><?= number_format((float)$wh['total_units'], 2) ?></strong>
                </div>
              </div>

              <?php if ($canManageWh): ?>
                <div class="d-flex gap-1">
                  <a href="/warehouses/edit/<?= (int)$wh['id'] ?>" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <?php if (empty($wh['is_default'])): ?>
                    <form action="/warehouses/delete/<?= (int)$wh['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Delete warehouse <?= htmlspecialchars($wh['name'], ENT_QUOTES, 'UTF-8') ?>?">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Selected Warehouse Stock Table -->
  <div class="col-lg-7">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark small text-uppercase">
          Stock in: <?= htmlspecialchars($selectedWarehouse['name'] ?? 'Selected Warehouse', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="text-muted small"><?= count($stockRows) ?> items</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.875rem;">
          <thead class="table-light">
            <tr>
              <th>Item Name</th>
              <th>Code / Category</th>
              <th class="text-end">In This Location</th>
              <th class="text-end">Total Firm Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($stockRows)): ?>
              <tr><td colspan="4" class="text-center text-muted py-4">No inventory items found.</td></tr>
            <?php else: ?>
              <?php foreach ($stockRows as $row): ?>
                <?php $qty = (float)$row['warehouse_qty']; ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="small text-muted">
                    <?= htmlspecialchars($row['item_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($row['category'])): ?>
                      <span class="badge bg-light text-secondary border ms-1"><?= htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end fw-bold <?= $qty > 0 ? 'text-success' : ($qty < 0 ? 'text-danger' : 'text-muted') ?>">
                    <?= number_format($qty, 2) ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td class="text-end text-muted">
                    <?= number_format((float)$row['total_stock'], 2) ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
