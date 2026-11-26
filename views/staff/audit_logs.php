<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-secondary me-2"></i>Firm Activity Audit Logs</h3>
    <small class="text-muted">Showing latest <?= count($logs) ?> of <?= (int)$totalCount ?> logged actions (read-only security trail)</small>
  </div>
  <a href="/staff" class="btn btn-outline-secondary btn-sm">Back to Team Access</a>
</div>

<form action="/audit-logs" method="GET" class="card shadow-sm border-0 mb-3">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-1">Action Type</label>
        <select name="action" class="form-select form-select-sm">
          <option value="">All Actions</option>
          <?php foreach (['CREATE', 'UPDATE', 'DELETE', 'ADJUST_STOCK', 'TRANSFER', 'EXPORT', 'RESTORE', 'LOGIN_FAILED'] as $act): ?>
            <option value="<?= $act ?>" <?= (($filters['action'] ?? '') === $act) ? 'selected' : '' ?>><?= $act ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">From Date</label>
        <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">To Date</label>
        <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter Logs</button>
        <a href="/audit-logs" class="btn btn-outline-secondary btn-sm">Reset</a>
      </div>
    </div>
  </div>
</form>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
      <thead class="table-light">
        <tr>
          <th>Timestamp</th>
          <th>User</th>
          <th>Role</th>
          <th>Action</th>
          <th>Target</th>
          <th>Details</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">No activity logs recorded yet.</td></tr>
        <?php else: ?>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td class="text-nowrap font-monospace small text-muted"><?= htmlspecialchars($log['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="fw-semibold"><?= htmlspecialchars($log['user_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($log['staff_role'] ?? '--', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td class="small"><?= htmlspecialchars(($log['target_type'] ?? '') . (!empty($log['target_id']) ? ' #' . $log['target_id'] : ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small"><?= htmlspecialchars($log['details'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="font-monospace small text-muted"><?= htmlspecialchars($log['ip_address'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
