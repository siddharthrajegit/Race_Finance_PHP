<?php $isEdit = !empty($warehouse); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold text-dark mb-0">
    <i class="bi bi-building text-primary me-2"></i><?= $isEdit ? 'Edit Warehouse' : 'Add Warehouse / Godown' ?>
  </h3>
  <a href="/warehouses" class="btn btn-outline-secondary btn-sm">Back to Warehouses</a>
</div>

<div class="card shadow-sm border-0" style="max-width: 640px;">
  <form action="<?= $isEdit ? '/warehouses/edit/' . (int)$warehouse['id'] : '/warehouses/create' ?>" method="POST">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="card-body p-4">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold small">Warehouse / Godown Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control form-control-sm" required value="<?= htmlspecialchars($warehouse['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. City Shop / Godown 2">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold small">Short Code</label>
          <input type="text" name="code" class="form-control form-control-sm text-uppercase font-monospace" maxlength="20" value="<?= htmlspecialchars($warehouse['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. GD2">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold small">Location / Address</label>
          <textarea name="address" class="form-control form-control-sm" rows="2" placeholder="Optional storage location address"><?= htmlspecialchars($warehouse['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="col-12">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="whDefaultChk" name="is_default" value="1" <?= !empty($warehouse['is_default']) ? 'checked' : '' ?>>
            <label class="form-check-label small fw-semibold" for="whDefaultChk">Set as Default Warehouse for Billing</label>
          </div>
        </div>
        <?php if ($isEdit): ?>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="whActiveChk" name="is_active" value="1" <?= !empty($warehouse['is_active']) ? 'checked' : '' ?>>
              <label class="form-check-label small" for="whActiveChk">Warehouse Active</label>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-footer bg-light p-3 text-end">
      <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Create Warehouse' ?>
      </button>
    </div>
  </form>
</div>
