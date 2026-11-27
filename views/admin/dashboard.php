<?php include __DIR__ . '/_nav.php'; ?>

<!-- 1. Top KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
  <!-- Registered Subscribers -->
  <div class="rf-card p-5 flex flex-col justify-between gap-3">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Subscribers</div>
        <div class="text-3xl font-semibold font-mono text-ink mt-1">
          <?= (int)($metrics['users']['total'] ?? 0) ?>
        </div>
      </div>
      <span class="w-9 h-9 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted shrink-0">
        <i class="bi bi-people text-base"></i>
      </span>
    </div>
    <div class="flex flex-wrap items-center gap-1.5 text-xs pt-2 border-t border-border-subtle">
      <span class="rf-badge rf-badge-sage font-mono"><?= (int)($metrics['users']['active'] ?? 0) ?> Active</span>
      <?php if ((int)($metrics['users']['suspended'] ?? 0) > 0): ?>
        <span class="rf-badge rf-badge-rose font-mono"><?= (int)$metrics['users']['suspended'] ?> Suspended</span>
      <?php endif; ?>
      <span class="rf-badge rf-badge-neutral font-mono"><?= (int)($metrics['users']['admins'] ?? 0) ?> Admins</span>
    </div>
  </div>

  <!-- Business Firms & Items -->
  <div class="rf-card p-5 flex flex-col justify-between gap-3">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Registered Firms</div>
        <div class="text-3xl font-semibold font-mono text-ink mt-1">
          <?= (int)($metrics['firms']['total'] ?? 0) ?>
        </div>
      </div>
      <span class="w-9 h-9 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted shrink-0">
        <i class="bi bi-buildings text-base"></i>
      </span>
    </div>
    <div class="text-xs text-ink-muted pt-2 border-t border-border-subtle flex items-center justify-between">
      <span>Catalog Items Across Firms</span>
      <strong class="font-mono text-ink"><?= number_format((int)($metrics['items']['total_items'] ?? 0)) ?></strong>
    </div>
  </div>

  <!-- Total Billed Volume -->
  <div class="rf-card p-5 flex flex-col justify-between gap-3">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Platform Sales Volume</div>
        <div class="text-2xl font-semibold font-mono text-sage mt-1">
          ₹ <?= number_format((float)($metrics['invoices']['total_sales_amount'] ?? 0), 0) ?>
        </div>
      </div>
      <span class="w-9 h-9 rounded-lg bg-sage-bg border border-border flex items-center justify-center text-sage shrink-0">
        <i class="bi bi-graph-up-arrow text-base"></i>
      </span>
    </div>
    <div class="text-xs text-ink-muted pt-2 border-t border-border-subtle flex items-center justify-between gap-2">
      <span><?= number_format((int)($metrics['invoices']['sales_count'] ?? 0)) ?> sales</span>
      <span class="font-mono text-rose">Due: ₹ <?= number_format((float)($metrics['invoices']['total_balance_due'] ?? 0), 0) ?></span>
    </div>
  </div>

  <!-- Platform Storage Pool -->
  <div class="rf-card p-5 flex flex-col justify-between gap-3">
    <?php
      $poolPct = (float)($metrics['storage']['poolUsagePercentage'] ?? 0);
      $poolBar = $poolPct > 85 ? 'bg-rose' : ($poolPct > 60 ? 'bg-amber' : 'bg-sage');
    ?>
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Storage Footprint</div>
        <div class="text-2xl font-semibold font-mono text-ink mt-1">
          <?= htmlspecialchars($storage['totalStorage'] ?? '--', ENT_QUOTES, 'UTF-8') ?>
        </div>
      </div>
      <span class="w-9 h-9 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted shrink-0">
        <i class="bi bi-hdd-stack text-base"></i>
      </span>
    </div>
    <div class="pt-2 border-t border-border-subtle space-y-1.5">
      <div class="flex items-center justify-between text-[11px] text-ink-muted font-mono">
        <span>Pool: <?= htmlspecialchars((string)($metrics['storage']['totalPoolUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string)($metrics['storage']['totalPoolQuotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</span>
        <span><?= htmlspecialchars((string)$poolPct, ENT_QUOTES, 'UTF-8') ?>%</span>
      </div>
      <div class="w-full h-1.5 rounded-full bg-canvas-alt overflow-hidden">
        <div class="h-full rounded-full <?= $poolBar ?>" style="width: <?= min(100, max($poolPct, 3)) ?>%;"></div>
      </div>
    </div>
  </div>
</div>

<!-- 2. Fast-Track Operations Bar -->
<div class="rf-card mb-6">
  <div class="rf-card-body py-3.5 px-5 flex flex-wrap items-center justify-between gap-3">
    <div class="text-xs font-medium uppercase tracking-wider text-ink-muted">Quick Administration</div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/admin/users" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-person-plus"></i> Manage Subscribers
      </a>
      <a href="/admin/firms" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-building"></i> Explore Firms
      </a>
      <a href="/admin/invoices" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-receipt"></i> Browse Records
      </a>
      <a href="/admin/system" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-hdd-network"></i> Storage &amp; Health
      </a>
      <a href="/admin/settings" class="rf-btn rf-btn-primary rf-btn-sm">
        <i class="bi bi-sliders2"></i> Governance Settings
      </a>
    </div>
  </div>
</div>

<!-- 3. Activity Feed & Security Audit Trail -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
  <!-- Live Platform Activity -->
  <div class="lg:col-span-6 rf-card flex flex-col">
    <div class="rf-card-header flex items-center justify-between">
      <h2 class="text-[15px] font-semibold text-ink mb-0 flex items-center gap-2">
        <i class="bi bi-activity text-ink-muted"></i> Recent Platform Activity
      </h2>
      <span class="rf-badge rf-badge-neutral font-mono"><?= count($recentActivities ?? []) ?> events</span>
    </div>
    <div class="divide-y divide-border-subtle flex-1">
      <?php if (!empty($recentActivities)): ?>
        <?php foreach ($recentActivities as $act):
          $aType = $act['type'] ?? '';
          $aIcon = match($aType) {
              'user_registered' => 'bi-person-plus text-sage bg-sage-bg',
              'firm_created'    => 'bi-building text-slate bg-slate-bg',
              'invoice_created' => 'bi-receipt text-amber bg-amber-bg',
              default           => 'bi-dot text-ink-muted bg-canvas-alt',
          };
        ?>
          <div class="px-5 py-3.5 flex items-start gap-3">
            <span class="w-8 h-8 rounded-lg border border-border flex items-center justify-center shrink-0 <?= $aIcon ?>">
              <i class="bi <?= explode(' ', $aIcon)[0] ?> text-xs"></i>
            </span>
            <div class="min-w-0 flex-1">
              <div class="text-sm font-medium text-ink truncate"><?= htmlspecialchars($act['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
              <?php if (!empty($act['subtitle'])): ?>
                <div class="text-xs text-ink-muted truncate mt-0.5"><?= htmlspecialchars($act['subtitle'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            </div>
            <div class="text-[11px] font-mono text-ink-subtle shrink-0 whitespace-nowrap">
              <?= !empty($act['created_at']) ? date('d M, h:i A', strtotime($act['created_at'])) : '--' ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="py-10 text-center text-sm text-ink-muted">No recent platform activities recorded.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Admin Audit Trail -->
  <div class="lg:col-span-6 rf-card flex flex-col">
    <div class="rf-card-header flex items-center justify-between">
      <h2 class="text-[15px] font-semibold text-ink mb-0 flex items-center gap-2">
        <i class="bi bi-shield-check text-sage"></i> Recent Admin Audit Trail
      </h2>
      <a href="/admin/system" class="text-xs font-medium text-ink-muted hover:text-ink underline underline-offset-2">
        View Full Audit
      </a>
    </div>
    <div class="divide-y divide-border-subtle flex-1">
      <?php if (!empty($recentLogs)): ?>
        <?php foreach ($recentLogs as $log): ?>
          <div class="px-5 py-3.5 space-y-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="rf-badge rf-badge-neutral font-mono"><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-xs font-medium text-ink"><?= htmlspecialchars($log['admin_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <span class="text-[11px] font-mono text-ink-subtle">
                <?= !empty($log['created_at']) ? date('d M Y, h:i A', strtotime($log['created_at'])) : '--' ?>
              </span>
            </div>
            <?php if (!empty($log['details'])): ?>
              <p class="text-xs text-ink-muted mb-0 leading-relaxed"><?= htmlspecialchars($log['details'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="py-10 text-center text-sm text-ink-muted">No admin actions logged yet.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
