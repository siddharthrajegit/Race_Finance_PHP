<?php include __DIR__ . '/_nav.php'; ?>

<!-- 1. Top KPI Summary Grid -->
<div class="row g-3 mb-4">
  <!-- Total Business Subscribers -->
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #fff;">
      <div class="card-body p-3 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.72rem;">Subscribers &amp; Users</div>
            <h2 class="fw-bold mb-0 text-white"><?= htmlspecialchars((string)($metrics['users']['total'] ?? 0), ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
          <div class="rounded-circle bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
            <i class="bi bi-people-fill fs-5"></i>
          </div>
        </div>
        <div class="mt-3 pt-2 border-top border-white border-opacity-25 d-flex justify-content-between small text-white-50">
          <span><strong class="text-white"><?= htmlspecialchars((string)($metrics['users']['active'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong> Active</span>
          <span><strong class="text-warning"><?= htmlspecialchars((string)($metrics['users']['suspended'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong> Suspended</span>
          <span><strong class="text-white"><?= htmlspecialchars((string)($metrics['users']['admins'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong> Admins</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Registered Firms -->
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #065f46, #10b981); color: #fff;">
      <div class="card-body p-3 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.72rem;">Registered Businesses</div>
            <h2 class="fw-bold mb-0 text-white"><?= htmlspecialchars((string)($metrics['firms']['total'] ?? 0), ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
          <div class="rounded-circle bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
            <i class="bi bi-buildings-fill fs-5"></i>
          </div>
        </div>
        <div class="mt-3 pt-2 border-top border-white border-opacity-25 d-flex justify-content-between small text-white-50">
          <span>Max 2 Firms / User</span>
          <span><strong class="text-white"><?= htmlspecialchars((string)($metrics['items']['total_items'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong> Items Logged</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Platform Sales & Turnover -->
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #5c3a21, #b45309); color: #fff;">
      <div class="card-body p-3 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Billed Volume</div>
            <h2 class="fw-bold mb-0 text-white">₹ <?= number_format((float)($metrics['invoices']['total_sales_amount'] ?? 0), 0) ?></h2>
          </div>
          <div class="rounded-circle bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
            <i class="bi bi-currency-rupee fs-5"></i>
          </div>
        </div>
        <div class="mt-3 pt-2 border-top border-white border-opacity-25 d-flex justify-content-between small text-white-50">
          <span><strong class="text-white"><?= htmlspecialchars((string)($metrics['invoices']['sales_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong> Sales Bills</span>
          <span>Due: <strong class="text-warning">₹ <?= number_format((float)($metrics['invoices']['total_balance_due'] ?? 0), 0) ?></strong></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Storage & Quota Pool Diagnostics -->
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #0f172a, #334155); color: #fff;">
      <div class="card-body p-3 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.72rem;">User Storage Pool</div>
            <h2 class="fw-bold mb-0 text-white"><?= htmlspecialchars((string)($metrics['storage']['totalPoolUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <span class="fs-6 fw-normal text-white-50">/ <?= htmlspecialchars((string)($metrics['storage']['totalPoolQuotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</span></h2>
          </div>
          <div class="rounded-circle bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
            <i class="bi bi-hdd-rack-fill fs-5"></i>
          </div>
        </div>
        <div class="mt-3 pt-2 border-top border-white border-opacity-25 d-flex justify-content-between small text-white-50">
          <span>Cap: <strong class="text-white">200 MB / User</strong></span>
          <span>Pool Used: <strong class="text-warning"><?= htmlspecialchars((string)($metrics['storage']['poolUsagePercentage'] ?? 0), ENT_QUOTES, 'UTF-8') ?>%</strong></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 2. Real-Time Activity Feed & Quick Admin Actions -->
<div class="row g-4 mb-4">
  <!-- Left: Real-Time Platform Activity Stream -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0">
          <i class="bi bi-activity text-primary me-2"></i> Real-Time Platform Activity Stream
        </h5>
        <span class="badge bg-light text-dark border small">Latest 12 events</span>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php if (!empty($recentActivities)): ?>
            <?php foreach ($recentActivities as $act): ?>
              <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                  <?php if (($act['type'] ?? '') === 'user_registered'): ?>
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                      <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                      <div class="fw-semibold text-dark">New Subscriber: <?= htmlspecialchars($act['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($act['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                  <?php elseif (($act['type'] ?? '') === 'firm_created'): ?>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                      <i class="bi bi-building-add"></i>
                    </div>
                    <div>
                      <div class="fw-semibold text-dark">Firm Registered: <?= htmlspecialchars($act['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                      <small class="text-muted">Owner: <?= htmlspecialchars($act['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                  <?php elseif (($act['type'] ?? '') === 'invoice_created'): ?>
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                      <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div>
                      <div class="fw-semibold text-dark">Bill Generated: <?= htmlspecialchars($act['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($act['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="text-end">
                  <span class="badge bg-light text-muted border font-monospace small"><?= !empty($act['created_at']) ? date('d M, h:i A', strtotime($act['created_at'])) : '' ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="p-4 text-center text-muted">No recent platform activity found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Fast-Track Controls & Audit -->
  <div class="col-lg-4">
    <!-- Quick Actions Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
      <div class="card-header bg-white py-3 border-0">
        <h5 class="fw-bold text-dark mb-0">
          <i class="bi bi-lightning-charge-fill text-warning me-2"></i> Fast-Track Controls
        </h5>
      </div>
      <div class="card-body p-3 pt-0 d-grid gap-2">
        <a href="/admin/users" class="btn btn-outline-primary text-start d-flex align-items-center justify-content-between py-2 rounded-3">
          <span><i class="bi bi-people me-2"></i> Inspect All Subscribers</span>
          <i class="bi bi-chevron-right small"></i>
        </a>
        <a href="/admin/firms" class="btn btn-outline-success text-start d-flex align-items-center justify-content-between py-2 rounded-3">
          <span><i class="bi bi-buildings me-2"></i> Business Firms Directory</span>
          <i class="bi bi-chevron-right small"></i>
        </a>
        <a href="/admin/invoices" class="btn btn-outline-secondary text-start d-flex align-items-center justify-content-between py-2 rounded-3">
          <span><i class="bi bi-receipt me-2"></i> Global Invoices Ledger</span>
          <i class="bi bi-chevron-right small"></i>
        </a>
        <a href="/admin/system" class="btn btn-outline-dark text-start d-flex align-items-center justify-content-between py-2 rounded-3">
          <span><i class="bi bi-hdd-network me-2"></i> System Health &amp; Storage Tools</span>
          <i class="bi bi-chevron-right small"></i>
        </a>
        <a href="/admin/settings" class="btn btn-outline-warning text-start text-dark d-flex align-items-center justify-content-between py-2 rounded-3">
          <span><i class="bi bi-sliders me-2"></i> SaaS Governance &amp; Broadcast</span>
          <i class="bi bi-chevron-right small"></i>
        </a>
      </div>
    </div>

    <!-- Recent Admin Audit Logs -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0">
          <i class="bi bi-shield-check text-success me-2"></i> Recent Admin Logs
        </h6>
        <a href="/admin/system" class="small text-decoration-none">View all</a>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush small">
          <?php if (!empty($recentLogs)): ?>
            <?php foreach ($recentLogs as $log): ?>
              <div class="list-group-item px-3 py-2">
                <div class="d-flex justify-content-between">
                  <strong class="text-dark"><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                  <span class="text-muted font-monospace" style="font-size: 0.68rem;"><?= !empty($log['created_at']) ? date('h:i A', strtotime($log['created_at'])) : '' ?></span>
                </div>
                <div class="text-muted text-truncate" style="font-size: 0.72rem;"><?= htmlspecialchars($log['details'] ?? (!empty($log['admin_name']) ? 'By ' . $log['admin_name'] : ''), ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="p-3 text-center text-muted small">No audit log records yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
