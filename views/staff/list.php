<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Staff &amp; Dedicated CA Access</h3>
    <small class="text-muted">Create named employee logins with role-based permissions and firm restrictions</small>
  </div>
  <div class="d-flex gap-2">
    <a href="/audit-logs" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-journal-text me-1"></i> Audit Logs
    </a>
    <a href="/staff/create" class="btn btn-primary btn-sm fw-semibold">
      <i class="bi bi-person-plus me-1"></i> Add Staff / CA User
    </a>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Login Contact</th>
          <th>Assigned Role</th>
          <th>Accessible Firms</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($staffList)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">No staff or CA team logins created yet.</td></tr>
        <?php else: ?>
          <?php foreach ($staffList as $s): ?>
            <tr>
              <td class="fw-semibold">
                <?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($s['permissions_json'])): ?>
                  <span class="badge bg-info-subtle text-info border ms-1" style="font-size:0.65rem;">Custom Perms</span>
                <?php endif; ?>
              </td>
              <td class="small">
                <?php if (!empty($s['phone'])): ?><div><i class="bi bi-phone me-1 text-muted"></i><?= htmlspecialchars($s['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if (!empty($s['email'])): ?><div><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              </td>
              <td>
                <span class="badge bg-primary-subtle text-primary border">
                  <?= htmlspecialchars($roles[$s['role']] ?? ucfirst($s['role']), ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
              <td>
                <?php foreach ($s['firms'] as $f): ?>
                  <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($f['firm_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
              </td>
              <td>
                <?php if (!empty($s['is_active'])): ?>
                  <span class="badge bg-success">Active</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Paused</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a href="/staff/edit/<?= (int)$s['user_id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2">Edit</a>
                  <form action="/staff/delete/<?= (int)$s['user_id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Revoke access and remove staff account <?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?>?">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">Remove</button>
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
