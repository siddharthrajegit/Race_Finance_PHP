<?php
$itemCount = !empty($items) ? count($items) : 0;
$lowStock = $lowStockCount ?? 0;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold mb-1">Items & Inventory</h3>
    <p class="text-muted small mb-0">Track stock levels, pricing, GST tax rates, and HSN codes</p>
  </div>
  <div class="d-flex gap-2">
    <a href="/items/create" class="btn btn-primary shadow-sm">
      <i class="bi bi-plus-circle me-1"></i> Add New Item
    </a>
  </div>
</div>

<!-- Search & Summary Bar -->
<div class="card shadow-sm border-0 mb-3">
  <div class="card-body p-2 p-md-3">
    <div class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" placeholder="Quick search items by name, code, or HSN..." data-table-search="itemsTable">
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <span class="badge bg-light text-dark border p-2 me-2">
          Total Products: <strong><?= $itemCount ?></strong>
        </span>
        <?php if ($lowStock > 0): ?>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle p-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= (int)$lowStock ?> Low Stock Items
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Items Table -->
<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div style="overflow-x: hidden;">
      <table class="table table-hover align-middle mb-0 w-100" id="itemsTable" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="padding: 0.5rem 0.65rem;">Item Name</th>
            <th style="padding: 0.5rem 0.65rem;">Item Code</th>
            <th style="padding: 0.5rem 0.65rem;">HSN/SAC</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Sale Price</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Purchase Price</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">GST Rate</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">Stock</th>
            <th style="width: 36px; padding: 0.5rem 0.35rem;" class="text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $it): ?>
              <?php $isLowStock = (float)($it['current_stock'] ?? 0) <= (float)($it['low_stock_threshold'] ?? 0); ?>
              <tr>
                <td style="padding: 0.45rem 0.65rem;">
                  <div class="fw-bold text-dark"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($it['description'])): ?>
                    <div class="small text-muted text-truncate" style="max-width: 180px;"><?= htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="font-monospace text-muted small"><?= htmlspecialchars($it['item_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="font-monospace text-muted small"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end fw-semibold text-nowrap">₹ <?= number_format((float)($it['sale_price'] ?? 0), 2) ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end text-muted text-nowrap">₹ <?= number_format((float)($it['purchase_price'] ?? 0), 2) ?></td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center">
                  <?php if ((float)($it['tax_rate'] ?? 0) > 0): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;"><?= htmlspecialchars((string)$it['tax_rate'], ENT_QUOTES, 'UTF-8') ?>%</span>
                  <?php else: ?>
                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">0%</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center">
                  <span class="badge <?= $isLowStock ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' ?> px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                    <?= htmlspecialchars((string)($it['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </td>
                <!-- 3-Dot Action Menu -->
                <td style="padding: 0.45rem 0.35rem;" class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-light btn-sm p-1 border-0 rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" style="width: 28px; height: 28px; line-height: 1;">
                      <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1">
                      <li>
                        <button type="button" class="dropdown-item py-1 px-3 small d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#adjustStockModal<?= $it['id'] ?>">
                          <i class="bi bi-sliders text-secondary me-2"></i> Adjust Stock
                        </button>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/items/edit/<?= $it['id'] ?>">
                          <i class="bi bi-pencil text-primary me-2"></i> Edit Item
                        </a>
                      </li>
                      <li><hr class="dropdown-divider my-1"></li>
                      <li>
                        <form action="/items/delete/<?= $it['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>?">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item py-1 px-3 small d-flex align-items-center text-danger border-0 bg-transparent w-100">
                            <i class="bi bi-trash me-2"></i> Delete Item
                          </button>
                        </form>
                      </li>
                    </ul>
                  </div>

                  <!-- Quick Stock Adjust Modal -->
                  <div class="modal fade" id="adjustStockModal<?= $it['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm text-start">
                      <div class="modal-content">
                        <form action="/items/adjust/<?= $it['id'] ?>" method="POST">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <div class="modal-header py-2">
                            <h6 class="modal-title fw-bold">Adjust Stock: <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body py-3">
                            <p class="small text-muted mb-2">Current Stock: <strong><?= htmlspecialchars((string)($it['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>
                            <div class="mb-3">
                              <label class="form-label small">Action</label>
                              <select name="action" class="form-select form-select-sm">
                                <option value="add">Add (+ Increase Stock)</option>
                                <option value="reduce">Reduce (- Damage / Return)</option>
                              </select>
                            </div>
                            <div class="mb-2">
                              <label class="form-label small">Quantity (<?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</label>
                              <input type="number" name="adjustment" class="form-control form-control-sm" min="0.01" step="any" placeholder="e.g. 5" required>
                            </div>
                          </div>
                          <div class="modal-footer py-2">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Update Stock</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-box-seam fs-2 d-block mb-2 text-secondary"></i>
                No items added yet. Click <strong>"Add New Item"</strong> to create your product inventory.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
