<?php include __DIR__ . '/_nav.php'; ?>

<form action="/admin/settings" method="POST" class="max-w-4xl space-y-6">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <!-- Subscriber Resource Quotas -->
  <div class="rf-card">
    <div class="rf-card-header flex items-center gap-2.5">
      <span class="w-8 h-8 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted">
        <i class="bi bi-sliders2 text-sm"></i>
      </span>
      <div>
        <h2 class="text-[15px] font-semibold text-ink mb-0">SaaS Limits &amp; Subscriber Quotas</h2>
        <p class="text-xs text-ink-muted mb-0">Configure global constraints across registered subscriber accounts.</p>
      </div>
    </div>
    <div class="rf-card-body grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <label for="max_firms_limit" class="rf-label">Max Business Firms Per Subscriber</label>
        <input type="number" id="max_firms_limit" name="max_firms_limit" class="rf-input font-mono" min="1" max="50"
               value="<?= htmlspecialchars((string)($settings['max_firms_limit'] ?? 2), ENT_QUOTES, 'UTF-8') ?>" required>
        <p class="rf-help">Default limit is 2 business firms per subscriber account.</p>
      </div>

      <div>
        <label for="max_upload_size_mb" class="rf-label">Max Single Media Upload Size (MB)</label>
        <input type="number" id="max_upload_size_mb" name="max_upload_size_mb" class="rf-input font-mono" min="1" max="20"
               value="<?= htmlspecialchars((string)($settings['max_upload_size_mb'] ?? 2), ENT_QUOTES, 'UTF-8') ?>" required>
        <p class="rf-help">Applies to firm logos, digital signatures, and QR code uploads.</p>
      </div>
    </div>
  </div>

  <!-- Global Announcement Banner -->
  <div class="rf-card">
    <div class="rf-card-header flex items-center justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-canvas-alt border border-border flex items-center justify-center text-ink-muted">
          <i class="bi bi-megaphone text-sm"></i>
        </span>
        <div>
          <h2 class="text-[15px] font-semibold text-ink mb-0">Platform-Wide Announcement Banner</h2>
          <p class="text-xs text-ink-muted mb-0">Display an informational banner across all subscriber dashboards.</p>
        </div>
      </div>

      <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
        <span class="text-xs font-medium text-ink">Enable Banner</span>
        <span class="rf-switch">
          <input type="checkbox" name="enable_announcement" value="1" <?= ($settings['enable_announcement'] ?? '0') == '1' ? 'checked' : '' ?>>
          <span class="rf-switch-thumb"></span>
        </span>
      </label>
    </div>

    <div class="rf-card-body space-y-4">
      <div>
        <label for="platform_announcement" class="rf-label">Announcement Message</label>
        <textarea id="platform_announcement" name="platform_announcement" rows="3" class="rf-textarea" placeholder="e.g. Scheduled system upgrade on Sunday at 2:00 AM IST."><?= htmlspecialchars($settings['platform_announcement'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="max-w-xs">
        <label for="platform_announcement_type" class="rf-label">Banner Severity / Tone</label>
        <?php $annType = $settings['platform_announcement_type'] ?? 'info'; ?>
        <select id="platform_announcement_type" name="platform_announcement_type" class="rf-select">
          <option value="info" <?= $annType === 'info' ? 'selected' : '' ?>>Informational (Slate)</option>
          <option value="warning" <?= $annType === 'warning' ? 'selected' : '' ?>>Warning Notice (Amber)</option>
          <option value="success" <?= $annType === 'success' ? 'selected' : '' ?>>Positive Update (Sage)</option>
          <option value="danger" <?= $annType === 'danger' ? 'selected' : '' ?>>Critical Alert (Rose)</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Maintenance Mode Guard -->
  <div class="rf-card border-rose-border/70">
    <div class="rf-card-body flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="font-semibold text-ink flex items-center gap-2">
          <i class="bi bi-cone-striped text-amber"></i> Maintenance Mode
        </div>
        <p class="text-xs text-ink-muted mb-0 mt-1">
          When enabled, standard subscriber logins are temporarily paused while platform administrators retain full console access.
        </p>
      </div>

      <label class="inline-flex items-center gap-2.5 cursor-pointer select-none shrink-0">
        <span class="text-xs font-medium text-ink">Active</span>
        <span class="rf-switch">
          <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?>>
          <span class="rf-switch-thumb"></span>
        </span>
      </label>
    </div>
  </div>

  <!-- Form Footer Actions -->
  <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
    <a href="/admin" class="rf-btn rf-btn-secondary">Cancel</a>
    <button type="submit" class="rf-btn rf-btn-primary">
      <i class="bi bi-check2-circle"></i> Save Governance Settings
    </button>
  </div>
</form>
