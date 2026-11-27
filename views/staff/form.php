<?php
$isEdit = !empty($staff);
$assignedFirmIds = $isEdit ? array_map(fn($f) => (int)$f['firm_id'], $staff['firms'] ?? []) : array_map(fn($f) => (int)$f['id'], $ownerFirms);
$selectedRole = $isEdit ? ($staff['role'] ?? 'cashier') : 'cashier';
$customPerms = ($isEdit && !empty($staff['permissions_json'])) ? (json_decode((string)$staff['permissions_json'], true) ?: []) : null;
$useCustom = ($customPerms !== null);
$activePerms = $useCustom ? $customPerms : ($roleDefaults[$selectedRole] ?? []);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-person-badge text-[#2B526E]"></i>
        <span><?= $isEdit ? 'Edit Staff / CA Account' : 'Create Staff / CA Account' ?></span>
      </h1>
    </div>
    <a href="/staff" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Team</a>
  </div>

  <form action="<?= $isEdit ? '/staff/edit/' . (int)$staff['user_id'] : '/staff/create' ?>" method="POST">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
      <div class="lg:col-span-5 rf-card">
        <div class="rf-card-header py-3">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Account Credentials &amp; Firms</h2>
        </div>
        <div class="rf-card-body space-y-4">
          <div>
            <label class="rf-label-plain text-xs">Full Name <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" name="name" class="rf-input rf-input-sm" required value="<?= htmlspecialchars($staff['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <?php if (!$isEdit): ?>
            <div>
              <label class="rf-label-plain text-xs">10-Digit Mobile Phone (for Login)</label>
              <input type="text" name="phone" class="rf-input rf-input-sm font-mono" maxlength="10" placeholder="e.g. 9876543210">
            </div>
            <div>
              <label class="rf-label-plain text-xs">Email Address (Optional / for Login)</label>
              <input type="email" name="email" class="rf-input rf-input-sm" placeholder="staff@company.com">
            </div>
            <div>
              <label class="rf-label-plain text-xs">Password <span class="text-[#8F2D2B]">*</span></label>
              <input type="password" name="password" class="rf-input rf-input-sm" required minlength="8" placeholder="Min 8 chars, letters + numbers">
            </div>
          <?php else: ?>
            <div class="rounded-lg bg-[#F7F6F3] border border-[#E2DFD7] px-3 py-2 text-xs text-[#5C5A54]">
              Login Contact: <strong class="text-[#1C1B18]"><?= htmlspecialchars($staff['phone'] ?: ($staff['email'] ?: '--'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div>
              <label class="rf-label-plain text-xs">Reset Password (Optional)</label>
              <input type="password" name="new_password" class="rf-input rf-input-sm" minlength="8" placeholder="Leave blank to keep existing password">
            </div>
            <div class="flex items-center justify-between gap-4 pt-1">
              <label class="text-xs font-semibold text-[#1C1B18] cursor-pointer" for="staffActiveChk">Account Active</label>
              <label class="rf-switch">
                <input type="checkbox" id="staffActiveChk" name="is_active" value="1" <?= !empty($staff['is_active']) ? 'checked' : '' ?>>
                <span class="rf-switch-thumb"></span>
              </label>
            </div>
          <?php endif; ?>

          <div>
            <label class="rf-label-plain text-xs">Role Preset <span class="text-[#8F2D2B]">*</span></label>
            <select name="role" id="staffRoleSelect" class="rf-select rf-select-sm">
              <?php foreach ($roles as $rk => $rlabel): ?>
                <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedRole === $rk) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rlabel, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="rf-label-plain text-xs">Allowed Business Firms <span class="text-[#8F2D2B]">*</span></label>
            <div class="space-y-1.5 mt-1">
              <?php foreach ($ownerFirms as $of): ?>
                <label class="flex items-center gap-2 text-xs text-[#1C1B18] cursor-pointer" for="firmChk<?= (int)$of['id'] ?>">
                  <input class="w-4 h-4 rounded border-[#D4D1C9] accent-[#1C1B18]" type="checkbox" name="firm_ids[]" value="<?= (int)$of['id'] ?>" id="firmChk<?= (int)$of['id'] ?>" <?= in_array((int)$of['id'], $assignedFirmIds, true) ? 'checked' : '' ?>>
                  <span><?= htmlspecialchars($of['name'], ENT_QUOTES, 'UTF-8') ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div>
            <label class="rf-label-plain text-xs">Notes</label>
            <textarea name="notes" class="rf-textarea text-xs" rows="2"><?= htmlspecialchars($staff['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </div>

      <div class="lg:col-span-7 rf-card">
        <div class="rf-card-header py-3 flex flex-wrap items-center justify-between gap-2">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Granular Permissions</h2>
          <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-[#1C1B18]">
            <span>Customize Permissions (Override Role Preset)</span>
            <span class="rf-switch">
              <input type="checkbox" id="useCustomPermsChk" name="use_custom_permissions" value="1" <?= $useCustom ? 'checked' : '' ?>>
              <span class="rf-switch-thumb"></span>
            </span>
          </label>
        </div>

        <div class="rf-card-body">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="permMatrixContainer">
            <?php foreach ($permissionGroups as $groupName => $perms): ?>
              <div class="rounded-xl border border-[#E2DFD7] bg-[#F7F6F3] p-3.5 space-y-2">
                <div class="text-xs font-semibold text-[#2B526E]"><?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="space-y-1.5">
                  <?php foreach ($perms as $pKey => $pLabel): ?>
                    <label class="flex items-start gap-2 text-xs text-[#1C1B18] cursor-pointer" for="perm_<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>">
                      <input class="w-4 h-4 rounded border-[#D4D1C9] accent-[#1C1B18] mt-0.5 perm-checkbox" type="checkbox" name="permissions[]" value="<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>" id="perm_<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($pKey, $activePerms, true) ? 'checked' : '' ?>>
                      <span><?= htmlspecialchars($pLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="rf-card-footer justify-end">
          <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
            <i class="bi bi-check2-circle"></i> <?= $isEdit ? 'Save Staff Permissions' : 'Create Staff Account' ?>
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const roleDefaults = <?= json_encode($roleDefaults) ?>;
  const roleSelect = document.getElementById('staffRoleSelect');
  const customChk = document.getElementById('useCustomPermsChk');
  const boxes = document.querySelectorAll('.perm-checkbox');

  function syncBoxesToRole() {
    if (customChk.checked) return;
    const allowed = roleDefaults[roleSelect.value] || [];
    boxes.forEach(cb => {
      cb.checked = allowed.includes(cb.value);
    });
  }

  roleSelect.addEventListener('change', syncBoxesToRole);
  boxes.forEach(cb => cb.addEventListener('change', () => {
    customChk.checked = true;
  }));
});
</script>
