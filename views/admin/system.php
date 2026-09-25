<?php include __DIR__ . '/_nav.php'; ?>

<!-- 1. System Health & Server Diagnostics -->
<div class="row g-4 mb-4">
  <!-- Server Environment & Memory -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0">
          <i class="bi bi-cpu-fill text-primary me-2"></i> Server Environment &amp; Diagnostics
        </h5>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">OPERATIONAL</span>
      </div>
      <div class="card-body p-3 pt-0">
        <table class="table table-sm table-borderless mb-0 small">
          <tbody>
            <tr>
              <td class="text-muted">Process Uptime:</td>
              <td class="text-end fw-semibold text-dark"><?= floor((float)($sysInfo['uptime'] ?? 0) / 60) ?> mins (<?= htmlspecialchars((string)($sysInfo['uptime'] ?? 0), ENT_QUOTES, 'UTF-8') ?>s)</td>
            </tr>
            <tr>
              <td class="text-muted">CPU Processors:</td>
              <td class="text-end fw-semibold text-dark"><?= htmlspecialchars((string)($sysInfo['cpuCount'] ?? 1), ENT_QUOTES, 'UTF-8') ?> Cores</td>
            </tr>
            <tr>
              <td class="text-muted">System RAM Free / Total:</td>
              <td class="text-end fw-semibold text-dark"><?= htmlspecialchars($sysInfo['freeMem'] ?? '--', ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($sysInfo['totalMem'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
              <td class="text-muted">Active Database Engine:</td>
              <td class="text-end fw-bold text-primary"><?= strtoupper(DB::getDriver()) ?><?= DB::isMysql() ? ' (cPanel MySQL / MariaDB)' : ' (SQLite file)' ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Storage Footprint & Maintenance Actions -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
      <div class="card-header bg-white py-3 border-0">
        <h5 class="fw-bold text-dark mb-0">
          <i class="bi bi-hdd-fill text-warning me-2"></i> Storage Management &amp; Database Tools
        </h5>
      </div>
      <div class="card-body p-3 pt-0">
        <!-- Storage summary bars -->
        <div class="p-3 bg-light rounded-3 mb-3">
          <div class="d-flex justify-content-between mb-1 small">
            <span class="text-muted">Database File / DB Storage:</span>
            <strong class="text-dark"><?= htmlspecialchars($storage['dbSize'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-1 small">
            <span class="text-muted">Subscriber Uploads (`/public/uploads`):</span>
            <strong class="text-dark"><?= htmlspecialchars($storage['uploads']['sizeFormatted'] ?? '0 MB', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string)($storage['uploads']['fileCount'] ?? 0), ENT_QUOTES, 'UTF-8') ?> files)</strong>
          </div>
          <div class="d-flex justify-content-between mb-1 small">
            <span class="text-muted">Subscriber Quota Pool (<?= htmlspecialchars((string)($storage['subscribersCount'] ?? 0), ENT_QUOTES, 'UTF-8') ?> users &times; 200 MB):</span>
            <strong class="text-dark"><?= htmlspecialchars((string)($storage['poolUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string)($storage['poolQuotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB (<?= htmlspecialchars((string)($storage['poolUsagePercentage'] ?? 0), ENT_QUOTES, 'UTF-8') ?>%)</strong>
          </div>
          <div class="d-flex justify-content-between border-top pt-1 mt-1 small">
            <span class="fw-bold text-dark">Total Storage Footprint:</span>
            <strong class="text-primary font-monospace fs-6"><?= htmlspecialchars($storage['totalStorage'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
        </div>

        <!-- Maintenance Tools -->
        <div class="d-grid gap-2">
          <!-- 1. Vacuum DB -->
          <form action="/admin/system/vacuum" method="POST" class="m-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-outline-primary btn-sm w-100 rounded-pill text-start d-flex align-items-center justify-content-between py-2 px-3">
              <span><i class="bi bi-magic me-2"></i> Optimize Database Storage</span>
              <span class="badge bg-primary-subtle text-primary">Execute</span>
            </button>
          </form>

          <!-- 2. Clean Orphan Files -->
          <form action="/admin/system/clean-orphans" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Scan and remove all unreferenced files in uploads folder?">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-outline-warning text-dark btn-sm w-100 rounded-pill text-start d-flex align-items-center justify-content-between py-2 px-3">
              <span><i class="bi bi-trash2 me-2"></i> Purge Orphan Media Uploads</span>
              <span class="badge bg-warning-subtle text-dark">Clean Storage</span>
            </button>
          </form>

          <!-- 3. Download Raw DB (Requires Password Re-Authentication) -->
          <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-pill text-start d-flex align-items-center justify-content-between py-2 px-3" data-bs-toggle="modal" data-bs-target="#downloadDbModal">
            <span><i class="bi bi-download me-2"></i> Download Full Database Snapshot (.db / .sql)</span>
            <span class="badge bg-success-subtle text-success">Requires Password</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Download Raw Database Password Confirmation Modal (H4) -->
<div class="modal fade" id="downloadDbModal" tabindex="-1" aria-labelledby="downloadDbModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="downloadDbModalLabel">
          <i class="bi bi-shield-lock-fill text-danger me-2 fs-4"></i> Authorize Database Export
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/admin/system/download-db" method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="modal-body pt-3 pb-2">
          <div class="alert alert-warning small d-flex align-items-start mb-3">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1 flex-shrink-0"></i>
            <div>
              <strong>Sensitive Security Action:</strong> The database snapshot contains all registered users, financial records, customer profiles, and ledger balances. Please enter your administrator password to authorize this export.
            </div>
          </div>
          <div class="mb-3">
            <label for="admin_password" class="form-label fw-semibold text-dark">Administrator Password</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
              <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Enter your administrator password" required autofocus autocomplete="current-password">
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm">
            <i class="bi bi-download me-1"></i> Authorize &amp; Download
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. Subscriber Storage Quota Consumption Matrix (200 MB / User) -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
  <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
    <div>
      <h5 class="fw-bold text-dark mb-0">
        <i class="bi bi-hdd-network text-primary me-2"></i> Subscriber Storage Quota Matrix (200 MB / User Quota)
      </h5>
      <p class="text-muted small mb-0 mt-1">Rule: 200 MB per user (100 MB per firm if 2 firms registered, or 200 MB if 1 firm)</p>
    </div>
    <span class="badge bg-light text-dark border">
      Pool: <?= htmlspecialchars((string)($storage['poolUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB of <?= htmlspecialchars((string)($storage['poolQuotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB Used
    </span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead class="table-dark">
        <tr>
          <th>Subscriber</th>
          <th>Contact</th>
          <th class="text-center">Firms</th>
          <th>Quota Strategy</th>
          <th style="min-width: 160px;">Used / 200 MB Cap</th>
          <th class="text-center">Usage %</th>
          <th class="text-end px-3">Inspect</th>
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
          ?>
            <tr>
              <td>
                <strong class="text-dark"><?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
              </td>
              <td class="font-monospace text-muted">
                <?= htmlspecialchars($u['phone'] ?? ($u['email'] ?? '--'), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td class="text-center font-monospace">
                <span class="badge bg-light text-dark border"><?= $firmsCount ?>/2</span>
              </td>
              <td>
                <?= $firmsCount <= 1 ? '1 Firm (200 MB Allotted)' : '2 Firms (100 MB Each)' ?>
              </td>
              <td>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="font-monospace fw-bold"><?= htmlspecialchars((string)$totalUsedMB, ENT_QUOTES, 'UTF-8') ?> MB</span>
                  <span class="text-muted" style="font-size: 0.7rem;">/ 200 MB</span>
                </div>
                <div class="progress" style="height: 5px;">
                  <div class="progress-bar <?= $isOverQuota ? 'bg-danger' : ($totalUsedPercentage > 60 ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= max($totalUsedPercentage, 3) ?>%;"></div>
                </div>
              </td>
              <td class="text-center">
                <span class="badge <?= $isOverQuota ? 'bg-danger' : ($totalUsedPercentage > 60 ? 'bg-warning text-dark' : 'bg-success-subtle text-success') ?>">
                  <?= htmlspecialchars((string)$totalUsedPercentage, ENT_QUOTES, 'UTF-8') ?>%
                </span>
              </td>
              <td class="text-end px-3">
                <a href="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
                  <i class="bi bi-eye me-1"></i> Inspect
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">No subscribers registered yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 3. Admin Security Audit Trail Logs -->
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
  <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
    <h5 class="fw-bold text-dark mb-0">
      <i class="bi bi-shield-check text-success me-2"></i> Security &amp; Governance Audit Trail
    </h5>
    <span class="badge bg-light text-dark border">Recent <?= !empty($auditLogs) ? count($auditLogs) : 0 ?> records</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead class="table-dark">
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
              <td class="font-monospace text-muted">
                <?= !empty($log['created_at']) ? date('d M Y, h:i:s A', strtotime($log['created_at'])) : '--' ?>
              </td>
              <td class="fw-bold text-dark"><?= htmlspecialchars($log['admin_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?php if (!empty($log['target_type'])): ?>
                  <span class="text-primary fw-semibold"><?= htmlspecialchars($log['target_type'], ENT_QUOTES, 'UTF-8') ?> #<?= htmlspecialchars((string)($log['target_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                  <span class="text-muted">--</span>
                <?php endif; ?>
              </td>
              <td class="text-muted"><?= htmlspecialchars($log['details'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="font-monospace text-muted"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">No security audit records logged yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
