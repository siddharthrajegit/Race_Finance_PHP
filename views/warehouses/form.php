<?php $isEdit = !empty($warehouse); ?>
<div class="max-w-xl mx-auto">
  <div class="rf-card">
    <div class="rf-card-header">
      <h1 class="text-lg font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-building text-[#2B526E]"></i>
        <span><?= $isEdit ? 'Edit Warehouse' : 'Create Warehouse / Godown' ?></span>
      </h1>
      <a href="/warehouses" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
    </div>

    <form action="<?= $isEdit ? '/warehouses/edit/' . (int)$warehouse['id'] : '/warehouses/create' ?>" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

      <div class="rf-card-body space-y-4">
        <div>
          <label for="wh_name" class="rf-label-plain">Warehouse / Godown Name <span class="text-[#8F2D2B]">*</span></label>
          <input type="text" id="wh_name" name="name" class="rf-input" required placeholder="e.g. Main Store / North Godown" value="<?= htmlspecialchars($warehouse['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
          <label for="wh_code" class="rf-label-plain">Warehouse Code</label>
          <input type="text" id="wh_code" name="code" class="rf-input font-mono" placeholder="e.g. WH-01" value="<?= htmlspecialchars($warehouse['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
          <label for="wh_address" class="rf-label-plain">Address / Location Notes</label>
          <textarea id="wh_address" name="address" class="rf-textarea" rows="2" placeholder="Street address, floor, or dock notes..."><?= htmlspecialchars($warehouse['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="flex items-center justify-between gap-4 pt-2 border-t border-[#ECEAE4]">
          <div>
            <label for="whDefault" class="text-sm font-medium text-[#1C1B18] cursor-pointer">Set as Default Warehouse</label>
            <p class="text-xs text-[#78746C] m-0">Used automatically for new invoices and purchases</p>
          </div>
          <label class="rf-switch">
            <input type="checkbox" id="whDefault" name="is_default" value="1" <?= !empty($warehouse['is_default']) ? 'checked' : '' ?>>
            <span class="rf-switch-thumb"></span>
          </label>
        </div>

        <?php if ($isEdit): ?>
          <div class="flex items-center justify-between gap-4 pt-2 border-t border-[#ECEAE4]">
            <div>
              <label for="whActive" class="text-sm font-medium text-[#1C1B18] cursor-pointer">Warehouse Active</label>
              <p class="text-xs text-[#78746C] m-0">Inactive godowns stay hidden in quick selectors</p>
            </div>
            <label class="rf-switch">
              <input type="checkbox" id="whActive" name="is_active" value="1" <?= !empty($warehouse['is_active']) ? 'checked' : '' ?>>
              <span class="rf-switch-thumb"></span>
            </label>
          </div>
        <?php endif; ?>
      </div>

      <div class="rf-card-footer justify-end gap-2">
        <a href="/warehouses" class="rf-btn rf-btn-secondary rf-btn-sm">Back</a>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-check2-circle"></i> <?= $isEdit ? 'Save Changes' : 'Create Warehouse' ?>
        </button>
      </div>
    </form>
  </div>
</div>
