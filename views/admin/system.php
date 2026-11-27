<?php include __DIR__ . '/_nav.php'; ?>

<!-- 1. System Health & Server Diagnostics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <!-- Server Environment & Memory -->
  <div class="rf-card flex flex-col">
    <div class="rf-card-header flex items-center justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted">
          <i class="bi bi-cpu text-sm"></i>
        </span>
        <h2 class="text-[15px] font-semibold text-ink mb-0">Server Environment &amp; Diagnostics</h2>
      </div>
      <span class="rf-badge rf-badge-sage">OPERATIONAL</span>
    </div>
    <div class="rf-card-body flex-1">
      <dl class="divide-y divide-border-subtle text-sm">
        <div class="py-2.5 flex items-center justify-between gap-4">
          <dt class="text-ink-muted font-normal">Process Uptime</dt>
          <dd class="font-medium text-ink font-mono text-right mb-0">
            <?= floor((float)($sysInfo['uptime'] ?? 0) / 60) ?> mins
            <span class="text-ink-muted text-xs">(<?= htmlspecialchars((string)($sysInfo['uptime'] ?? 0), ENT_QUOTES, 'UTF-8') ?>s)</span>
          </dd>
        </div>
        <div class="py-2.5 flex items-center justify-between gap-4">
          <dt class="text-ink-muted font-normal">CPU Processors</dt>
          <dd class="font-medium text-ink font-mono text-right mb-0">
            <?= htmlspecialchars((string)($sysInfo['cpuCount'] ?? 1), ENT_QUOTES, 'UTF-8') ?> Cores
          </dd>
        </div>
        <div class="py-2.5 flex items-center justify-between gap-4">
          <dt class="text-ink-muted font-normal">System RAM Free / Total</dt>
          <dd class="font-medium text-ink font-mono text-right mb-0">
            <?= htmlspecialchars($sysInfo['freeMem'] ?? '--', ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($sysInfo['totalMem'] ?? '--', ENT_QUOTES, 'UTF-8') ?>
          </dd>
        </div>
        <div class="py-2.5 flex items-center justify-between gap-4">
          <dt class="text-ink-muted font-normal">Active Database Engine</dt>
          <dd class="font-semibold text-ink font-mono text-right mb-0">
            <?= strtoupper(DB::getDriver()) ?><?= DB::isMysql() ? ' (cPanel MySQL / MariaDB)' : ' (SQLite file)' ?>
          </dd>
        </div>
      </dl>
    </div>
  </div>

  <!-- Storage Footprint & Maintenance Actions -->
  <div class="rf-card flex flex-col">
    <div class="rf-card-header flex items-center gap-2.5">
      <span class="w-8 h-8 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted">
        <i class="bi bi-hdd text-sm"></i>
      </span>
      <h2 class="text-[15px] font-semibold text-ink mb-0">Storage Management &amp; Database Tools</h2>
    </div>
    <div class="rf-card-body flex-1 flex flex-col justify-between gap-4">
      <div class="rounded-lg bg-canvas-alt/70 border border-border-subtle p-4 space-y-2 text-xs">
        <div class="flex items-center justify-between gap-3">
          <span class="text-ink-muted">Database File / DB Storage</span>
          <strong class="text-ink font-mono"><?= htmlspecialchars($storage['dbSize'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="flex items-center justify-between gap-3">
          <span class="text-ink-muted">Subscriber Uploads (<code class="font-mono text-[11px]">/public/uploads</code>)</span>
          <strong class="text-ink font-mono">
            <?= htmlspecialchars($storage['uploads']['sizeFormatted'] ?? '0 MB', ENT_QUOTES, 'UTF-8') ?>
            (<?= htmlspecialchars((string)($storage['uploads']['fileCount'] ?? 0), ENT_QUOTES, 'UTF-8') ?> files)
          </strong>
        </div>
        <div class="flex items-center justify-between gap-3">
          <span class="text-ink-muted">Subscriber Quota Pool (<?= htmlspecialchars((string)($storage['subscribersCount'] ?? 0), ENT_QUOTES, 'UTF-8') ?> users &times; 200 MB)</span>
          <strong class="text-ink font-mono">
            <?= htmlspecialchars((string)($storage['poolUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string)($storage['poolQuotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB
            (<?= htmlspecialchars((string)($storage['poolUsagePercentage'] ?? 0), ENT_QUOTES, 'UTF-8') ?>%)
          </strong>
        </div>
        <div class="flex items-center justify-between gap-3 pt-2 mt-1 border-t border-border">
          <span class="font-semibold text-ink text-sm">Total Storage Footprint</span>
          <strong class="text-ink font-mono text-sm"><?= htmlspecialchars($storage['totalStorage'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
      </div>

      <div class="space-y-2">
        <form action="/admin/system/vacuum" method="POST" class="m-0">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="rf-btn rf-btn-secondary w-full justify-between px-3.5 py-2">
            <span class="flex items-center gap-2"><i class="bi bi-magic text-ink-muted"></i> Optimize Database Storage</span>
            <span class="rf-badge rf-badge-neutral">Execute</span>
          </button>
        </form>

        <form action="/admin/system/clean-orphans" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Scan and remove all unreferenced files in uploads folder?">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="rf-btn rf-btn-amber w-full justify-between px-3.5 py-2">
            <span class="flex items-center gap-2"><i class="bi bi-trash2"></i> Purge Orphan Media Uploads</span>
            <span class="rf-badge rf-badge-amber">Clean Storage</span>
          </button>
        </form>

        <button type="button" class="rf-btn rf-btn-sage w-full justify-between px-3.5 py-2" data-ui-modal="#downloadDbModal">
          <span class="flex items-center gap-2"><i class="bi bi-download"></i> Download Full Database Snapshot (.db / .sql)</span>
          <span class="rf-badge rf-badge-sage">Requires Password</span>
        </button>

        <?php $pendingCount = count(array_diff($availableMigrations ?? [], array_keys($appliedMigrations ?? []))); ?>
        <form action="/admin/system/run-migrations" method="POST" class="m-0">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="rf-btn rf-btn-secondary w-full justify-between px-3.5 py-2">
            <span class="flex items-center gap-2"><i class="bi bi-database-check text-ink-muted"></i> Database Schema Migrations (<?= count($appliedMigrations ?? []) ?>/<?= count($availableMigrations ?? []) ?> applied)</span>
            <span class="rf-badge <?= $pendingCount > 0 ? 'rf-badge-amber' : 'rf-badge-sage' ?>">
              <?= $pendingCount > 0 ? ($pendingCount . ' Pending — Run Now') : 'Up to Date' ?>
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Download Raw Database Password Confirmation Modal (H4) -->
<div class="rf-modal hidden" id="downloadDbModal" tabindex="-1" aria-labelledby="downloadDbModalLabel" aria-hidden="true">
  <div class="rf-modal-dialog">
    <div class="rf-modal-header">
      <h5 class="text-base font-semibold text-ink flex items-center gap-2 mb-0" id="downloadDbModalLabel">
        <i class="bi bi-shield-lock text-rose"></i> Authorize Database Export
      </h5>
      <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg text-sm"></i>
      </button>
    </div>
    <form action="/admin/system/download-db" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-modal-body space-y-4">
        <div class="rf-alert rf-alert-warning mb-0">
          <i class="bi bi-exclamation-triangle mt-0.5 shrink-0"></i>
          <div class="text-xs leading-relaxed">
            <strong>Sensitive Security Action:</strong> The database snapshot contains all registered users, financial records, customer profiles, and ledger balances. Please enter your administrator password to authorize this export.
          </div>
        </div>
        <div>
          <label for="admin_password" class="rf-label">Administrator Password</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none">
              <i class="bi bi-key text-sm"></i>
            </span>
            <input type="password" class="rf-input pl-9" id="admin_password" name="admin_password" placeholder="Enter your administrator password" required autofocus autocomplete="current-password">
          </div>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-danger-solid">
          <i class="bi bi-download"></i> Authorize &amp; Download
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 2. Subscriber Storage Quota Consumption Matrix (200 MB / User) -->
<div class="rf-card mb-6 overflow-hidden">
  <div class="rf-card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
      <h2 class="text-[15px] font-semibold text-ink mb-0 flex items-center gap-2">
        <i class="bi bi-hdd-network text-ink-muted"></i> Subscriber Storage Quota Matrix (200 MB / User Quota)
      </h2>
      <p class="text-xs text-ink-muted mb-0 mt-1">Rule: 200 MB per user (100 MB per firm if 2 firms registered, or 200 MB if 1 firm)</p>
    </div>
    <span class="rf-badge rf-badge-neutral self-start sm:self-center font-mono">
      Pool: <?= htmlspecialchars((string)($storage['poolUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB of <?= htmlspecialchars((string)($storage['poolQuotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB Used
    </span>
  </div>
  <div class="rf-table-wrap border-0 rounded-none">
    <table class="rf-table">
      <thead>
        <tr>
          <th>Subscriber</th>
          <th>Contact</th>
          <th class="text-center">Firms</th>
          <th>Quota Strategy</th>
          <th class="min-w-[165px]">Used / 200 MB Cap</th>
          <th class="text-center">Usage %</th>
          <th class="text-right">Inspect</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($storage['topStorageUsers'])): ?>
          <?php foreach ($storage['topStorageUsers'] as $u):
            $st = $u['storage'] ?? [];
            $firmsCount = (int)($u['firms_count'] ?? 0);
            $totalUsedMB = $st['totalUsedMB'] ?? 0;
            $totalUsedPercentage = (float)($st['totalUsedPercentage'] ?? 0);
            $isOverQuota = !empty($st['isOverQuota']);
            $barColor = $isOverQuota ? 'bg-rose' : ($totalUsedPercentage > 60 ? 'bg-amber' : 'bg-sage');
            $badgeClass = $isOverQuota ? 'rf-badge-rose' : ($totalUsedPercentage > 60 ? 'rf-badge-amber' : 'rf-badge-sage');
          ?>
            <tr>
              <td class="font-medium text-ink">
                <?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td class="font-mono text-xs text-ink-muted">
                <?= htmlspecialchars($u['phone'] ?? ($u['email'] ?? '--'), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td class="text-center font-mono">
                <span class="rf-badge rf-badge-neutral"><?= $firmsCount ?>/2</span>
              </td>
              <td class="text-xs text-ink-muted">
                <?= $firmsCount <= 1 ? '1 Firm (200 MB Allotted)' : '2 Firms (100 MB Each)' ?>
              </td>
              <td>
                <div class="flex items-center justify-between text-xs mb-1">
                  <span class="font-mono font-semibold text-ink"><?= htmlspecialchars((string)$totalUsedMB, ENT_QUOTES, 'UTF-8') ?> MB</span>
                  <span class="text-ink-subtle font-mono text-[11px]">/ 200 MB</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-canvas-alt overflow-hidden">
                  <div class="h-full rounded-full <?= $barColor ?>" style="width: <?= min(100, max($totalUsedPercentage, 3)) ?>%;"></div>
                </div>
              </td>
              <td class="text-center">
                <span class="rf-badge <?= $badgeClass ?> font-mono">
                  <?= htmlspecialchars((string)$totalUsedPercentage, ENT_QUOTES, 'UTF-8') ?>%
                </span>
              </td>
              <td class="text-right">
                <a href="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm">
                  <i class="bi bi-eye"></i> Inspect
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center py-8 text-ink-muted">No subscribers registered yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 3. Admin Security Audit Trail Logs -->
<div class="rf-card overflow-hidden">
  <div class="rf-card-header flex items-center justify-between gap-2">
    <h2 class="text-[15px] font-semibold text-ink mb-0 flex items-center gap-2">
      <i class="bi bi-shield-check text-sage"></i> Security &amp; Governance Audit Trail
    </h2>
    <span class="rf-badge rf-badge-neutral font-mono">Recent <?= !empty($auditLogs) ? count($auditLogs) : 0 ?> records</span>
  </div>
  <div class="rf-table-wrap border-0 rounded-none">
    <table class="rf-table">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>Admin User</th>
          <th>Action</th>
          <th>Target</th>
          <th>Details</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($auditLogs)): ?>
          <?php foreach ($auditLogs as $log): ?>
            <tr>
              <td class="font-mono text-xs text-ink-muted whitespace-nowrap">
                <?= !empty($log['created_at']) ? date('d M Y, h:i:s A', strtotime($log['created_at'])) : '--' ?>
              </td>
              <td class="font-medium text-ink whitespace-nowrap"><?= htmlspecialchars($log['admin_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <span class="rf-badge rf-badge-neutral font-mono"><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td class="whitespace-nowrap">
                <?php if (!empty($log['target_type'])): ?>
                  <span class="font-mono text-xs font-medium text-ink">
                    <?= htmlspecialchars($log['target_type'], ENT_QUOTES, 'UTF-8') ?> #<?= htmlspecialchars((string)($log['target_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  </span>
                <?php else: ?>
                  <span class="text-ink-subtle">--</span>
                <?php endif; ?>
              </td>
              <td class="text-ink-muted text-xs"><?= htmlspecialchars($log['details'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="font-mono text-xs text-ink-muted whitespace-nowrap"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center py-8 text-ink-muted">No security audit records logged yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
