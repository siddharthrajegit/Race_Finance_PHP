<?php $canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_items', $userPermissions ?? [], true); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-hash text-primary me-2"></i>Serial Numbers &amp; IMEI Register</h3>
    <small class="text-muted">Unique per-unit serial/IMEI tracking across purchases, sales &amp; warranty status</small>
  </div>
  <a href="/items" class="btn btn-outline-secondary btn-sm">Back to Items</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <span class="fw-bold small text-uppercase">Serial Register (<?= count($serials) ?>)</span>
        <div class="btn-group btn-group-sm">
          <a href="/serials" class="btn <?= empty($selectedStatus) ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
          <a href="/serials?status=in_stock" class="btn <?= ($selectedStatus === 'in_stock') ? 'btn-success' : 'btn-outline-success' ?>">In Stock</a>
          <a href="/serials?status=sold" class="btn <?= ($selectedStatus === 'sold') ? 'btn-primary' : 'btn-outline-primary' ?>">Sold</a>
          <a href="/serials?status=damaged" class="btn <?= ($selectedStatus === 'damaged') ? 'btn-danger' : 'btn-outline-danger' ?>">Damaged</a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.86rem;">
          <thead class="table-light">
            <tr>
              <th>Serial / IMEI #</th>
              <th>Product</th>
              <th>Warehouse</th>
              <th>Status</th>
              <?php if ($canManage): ?><th class="text-end">Change Status</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($serials)): ?>
              <tr><td colspan="<?= $canManage ? 5 : 4 ?>" class="text-center py-4 text-muted">No serial numbers registered yet.</td></tr>
            <?php else: foreach ($serials as $s): ?>
              <tr>
                <td class="font-monospace fw-bold text-dark"><?= htmlspecialchars($s['serial_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($s['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="small text-muted"><?= htmlspecialchars($s['warehouse_name'] ?? 'Default', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php
                    $badge = match ($s['status']) {
                        'in_stock' => 'bg-success-subtle text-success',
                        'sold'     => 'bg-primary-subtle text-primary',
                        'returned' => 'bg-warning-subtle text-dark',
                        default    => 'bg-danger-subtle text-danger',
                    };
                  ?>
                  <span class="badge <?= $badge ?> text-uppercase"><?= htmlspecialchars($s['status'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <?php if ($canManage): ?>
                  <td class="text-end">
                    <form action="/serials/status" method="POST" class="d-inline-flex gap-1">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="serial_id" value="<?= (int)$s['id'] ?>">
                      <select name="status" class="form-select form-select-sm py-0" style="width:auto;">
                        <?php foreach (['in_stock', 'sold', 'returned', 'damaged'] as $stOpt): ?>
                          <option value="<?= $stOpt ?>" <?= ($s['status'] === $stOpt) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $stOpt)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2">Save</button>
                    </form>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($canManage): ?>
    <div class="col-lg-4">
      <form action="/serials/register" method="POST" class="card shadow-sm border-0">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="card-header bg-white fw-bold small text-uppercase">Register Serials / IMEIs</div>
        <div class="card-body p-3">
          <div class="mb-2">
            <label class="form-label small fw-semibold">Product <span class="text-danger">*</span></label>
            <select name="item_id" class="form-select form-select-sm" required>
              <option value="">-- Select Product --</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (count($warehouses) > 1): ?>
            <div class="mb-2">
              <label class="form-label small fw-semibold">Warehouse</label>
              <select name="warehouse_id" class="form-select form-select-sm">
                <?php foreach ($warehouses as $w): ?>
                  <option value="<?= (int)$w['id'] ?>"><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Serial Numbers (one per line or comma separated) <span class="text-danger">*</span></label>
            <textarea name="serial_numbers" class="form-control form-control-sm font-monospace" rows="4" required placeholder="IMEI-10001&#10;IMEI-10002"></textarea>
          </div>
          <div>
            <label class="form-label small fw-semibold">Notes</label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional warranty / supplier lot note">
          </div>
        </div>
        <div class="card-footer bg-light text-end py-2">
          <button type="submit" class="btn btn-primary btn-sm fw-semibold">Register Serials</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
