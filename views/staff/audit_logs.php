<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-journal-text text-[#5C5A54]"></i>
        <span>Firm Activity Audit Logs</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Showing latest <?= count($logs) ?> of <?= (int)$totalCount ?> logged actions (read-only security trail)</p>
    </div>
    <a href="/staff" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Team Access</a>
  </div>

  <form action="/audit-logs" method="GET" class="rf-card p-5">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">Action Type</label>
        <select name="action" class="rf-select rf-select-sm">
          <option value="">All Actions</option>
          <?php foreach (['CREATE', 'UPDATE', 'DELETE', 'ADJUST_STOCK', 'TRANSFER', 'EXPORT', 'RESTORE', 'LOGIN_FAILED'] as $act): ?>
            <option value="<?= $act ?>" <?= (($filters['action'] ?? '') === $act) ? 'selected' : '' ?>><?= $act ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">From Date</label>
        <input type="date" name="from_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs">To Date</label>
        <input type="date" name="to_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="md:col-span-3 flex items-center gap-2">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm flex-1">Filter Logs</button>
        <a href="/audit-logs" class="rf-btn rf-btn-secondary rf-btn-sm">Reset</a>
      </div>
    </div>
  </form>

  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
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
            <tr>
              <td colspan="7" class="text-center py-10 text-[#78746C]">No activity logs recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td class="whitespace-nowrap font-mono text-xs text-[#78746C]"><?= htmlspecialchars($log['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($log['user_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="rf-badge rf-badge-neutral"><?= htmlspecialchars($log['staff_role'] ?? '--', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="rf-badge rf-badge-slate"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars(($log['target_type'] ?? '') . (!empty($log['target_id']) ? ' #' . $log['target_id'] : ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#1C1B18]"><?= htmlspecialchars($log['details'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono text-xs text-[#78746C]"><?= htmlspecialchars($log['ip_address'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
