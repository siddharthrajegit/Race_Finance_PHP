<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-person-badge text-[#2B526E]"></i>
        <span>Staff &amp; Dedicated CA Access</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Create named employee logins with role-based permissions and firm restrictions</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="/audit-logs" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-journal-text"></i> Audit Logs
      </a>
      <a href="/staff/create" class="rf-btn rf-btn-primary rf-btn-sm">
        <i class="bi bi-person-plus"></i> Add Staff / CA User
      </a>
    </div>
  </div>

  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Login Contact</th>
            <th>Assigned Role</th>
            <th>Accessible Firms</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($staffList)): ?>
            <tr>
              <td colspan="6" class="text-center py-10 text-[#78746C]">No staff or CA team logins created yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($staffList as $s): ?>
              <tr>
                <td class="font-medium text-[#1C1B18]">
                  <span><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (!empty($s['permissions_json'])): ?>
                    <span class="rf-badge rf-badge-slate text-[10px] ml-1">Custom Perms</span>
                  <?php endif; ?>
                </td>
                <td class="text-xs text-[#5C5A54] space-y-0.5">
                  <?php if (!empty($s['phone'])): ?><div><i class="bi bi-phone mr-1 text-[#78746C]"></i><?= htmlspecialchars($s['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  <?php if (!empty($s['email'])): ?><div><i class="bi bi-envelope mr-1 text-[#78746C]"></i><?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </td>
                <td>
                  <span class="rf-badge rf-badge-slate">
                    <?= htmlspecialchars($roles[$s['role']] ?? ucfirst($s['role']), ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </td>
                <td>
                  <div class="flex flex-wrap gap-1">
                    <?php foreach ($s['firms'] as $f): ?>
                      <span class="rf-badge rf-badge-neutral"><?= htmlspecialchars($f['firm_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td>
                  <?php if (!empty($s['is_active'])): ?>
                    <span class="rf-badge rf-badge-sage">Active</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-neutral">Paused</span>
                  <?php endif; ?>
                </td>
                <td class="text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <a href="/staff/edit/<?= (int)$s['user_id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5 text-xs">Edit</a>
                    <form action="/staff/delete/<?= (int)$s['user_id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Revoke access and remove staff account <?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?>?">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="rf-btn rf-btn-danger rf-btn-sm py-1 px-2.5 text-xs">Remove</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
