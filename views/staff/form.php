<?php
$isEdit = !empty($staff);
$assignedFirmIds = $isEdit ? array_map(fn($f) => (int)$f['firm_id'], $staff['firms'] ?? []) : array_map(fn($f) => (int)$f['id'], $ownerFirms);
$selectedRole = $isEdit ? ($staff['role'] ?? 'cashier') : 'cashier';
$customPerms = ($isEdit && !empty($staff['permissions_json'])) ? (json_decode((string)$staff['permissions_json'], true) ?: []) : null;
$useCustom = ($customPerms !== null);
$activePerms = $useCustom ? $customPerms : ($roleDefaults[$selectedRole] ?? []);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold text-dark mb-0">
    <i class="bi bi-person-badge text-primary me-2"></i><?= $isEdit ? 'Edit Staff / CA Account' : 'Create Staff / CA Account' ?>
  </h3>
  <a href="/staff" class="btn btn-outline-secondary btn-sm">Back to Team</a>
</div>

<form action="<?= $isEdit ? '/staff/edit/' . (int)$staff['user_id'] : '/staff/create' ?>" method="POST">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold small text-uppercase">Account Credentials &amp; Firms</div>
        <div class="card-body p-3">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-sm" required value="<?= htmlspecialchars($staff['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <?php if (!$isEdit): ?>
            <div class="mb-3">
              <label class="form-label small fw-semibold">10-Digit Mobile Phone (for Login)</label>
              <input type="text" name="phone" class="form-control form-control-sm" maxlength="10" placeholder="e.g. 9876543210">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Email Address (Optional / for Login)</label>
              <input type="email" name="email" class="form-control form-control-sm" placeholder="staff@company.com">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control form-control-sm" required minlength="8" placeholder="Min 8 chars, letters + numbers">
            </div>
          <?php else: ?>
            <div class="mb-3 small text-muted bg-light p-2 rounded">
              Login Contact: <strong><?= htmlspecialchars($staff['phone'] ?: ($staff['email'] ?: '--'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Reset Password (Optional)</label>
              <input type="password" name="new_password" class="form-control form-control-sm" minlength="8" placeholder="Leave blank to keep existing password">
            </div>
            <div class="mb-3 form-check form-switch">
              <input class="form-check-input" type="checkbox" id="staffActiveChk" name="is_active" value="1" <?= !empty($staff['is_active']) ? 'checked' : '' ?>>
              <label class="form-check-label small fw-semibold" for="staffActiveChk">Account Active</label>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Role Preset <span class="text-danger">*</span></label>
            <select name="role" id="staffRoleSelect" class="form-select form-select-sm">
              <?php foreach ($roles as $rk => $rlabel): ?>
                <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedRole === $rk) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rlabel, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Allowed Business Firms <span class="text-danger">*</span></label>
            <?php foreach ($ownerFirms as $of): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="firm_ids[]" value="<?= (int)$of['id'] ?>" id="firmChk<?= (int)$of['id'] ?>" <?= in_array((int)$of['id'], $assignedFirmIds, true) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="firmChk<?= (int)$of['id'] ?>">
                  <?= htmlspecialchars($of['name'], ENT_QUOTES, 'UTF-8') ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mb-2">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($staff['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
          <span class="fw-bold small text-uppercase">Granular Permissions</span>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="useCustomPermsChk" name="use_custom_permissions" value="1" <?= $useCustom ? 'checked' : '' ?>>
            <label class="form-check-label small fw-semibold" for="useCustomPermsChk">Customize Permissions (Override Role Preset)</label>
          </div>
        </div>
        <div class="card-body p-3">
          <div class="row g-3" id="permMatrixContainer">
            <?php foreach ($permissionGroups as $groupName => $perms): ?>
              <div class="col-md-6">
                <div class="border rounded p-2 h-100 bg-light-subtle">
                  <div class="fw-bold small text-primary mb-2"><?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?></div>
                  <?php foreach ($perms as $pKey => $pLabel): ?>
                    <div class="form-check mb-1">
                      <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>" id="perm_<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($pKey, $activePerms, true) ? 'checked' : '' ?>>
                      <label class="form-check-label small" for="perm_<?= htmlspecialchars($pKey, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($pLabel, ENT_QUOTES, 'UTF-8') ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-footer bg-light p-3 text-end">
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
            <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Staff Permissions' : 'Create Staff Account' ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

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
