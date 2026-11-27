<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Data Backup &amp; Cloud Sync</h1>
    <p class="text-sm text-[#5C5A54] mt-1">Protect your business data with instant JSON exports and 1-click Google Drive synchronization</p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Google Drive Backup -->
    <div class="rf-card p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center gap-3.5 mb-4">
          <div class="w-12 h-12 rounded-xl bg-[#FBEAEA] border border-[#ECD0D0] text-[#8F2D2B] flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-google"></i>
          </div>
          <div>
            <h2 class="text-base font-semibold text-[#1C1B18]">Google Drive 1-Click Backup</h2>
            <p class="text-xs text-[#78746C]">Direct cloud backup to your Google Drive account</p>
          </div>
        </div>
        <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
          Safely upload a complete snapshot of your firms, invoices, inventory items, customer balances, and payment records into a dedicated <strong class="text-[#1C1B18]">"RACE FINANCE Backups"</strong> folder in Google Drive.
        </p>
      </div>

      <div>
        <?php if (!empty($googleToken) && !empty($googleToken['access_token'])): ?>
          <div class="rf-alert rf-alert-success mb-4">
            <i class="bi bi-check-circle text-base shrink-0"></i>
            <div class="text-xs">
              <strong>Google Account Connected:</strong><br>
              <span><?= htmlspecialchars($googleToken['email'] ?? ($user['email'] ?? 'Connected'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>

          <form action="/backup/google-drive" method="POST">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="rf-btn rf-btn-primary w-full">
              <i class="bi bi-cloud-arrow-up"></i> Upload Backup to Google Drive
            </button>
          </form>
        <?php else: ?>
          <div class="rf-alert rf-alert-warning mb-4 text-xs">
            <i class="bi bi-info-circle shrink-0"></i>
            <span>Connect your Google account to enable automatic 1-click Google Drive sync.</span>
          </div>

          <?php if (!empty($hasGoogleAuth)): ?>
            <a href="/auth/google" class="rf-btn rf-btn-secondary w-full">
              <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
              Connect Google Account
            </a>
          <?php else: ?>
            <div class="text-xs text-[#5C5A54] border border-[#E6E4DF] p-3 rounded-lg bg-[#FAF9F6]">
              <i class="bi bi-gear mr-1"></i> Add <code class="font-mono">GOOGLE_CLIENT_ID</code> and <code class="font-mono">GOOGLE_CLIENT_SECRET</code> to <code class="font-mono">.env</code> to activate live Google Drive uploads.
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Local JSON Backup -->
    <div class="rf-card p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center gap-3.5 mb-4">
          <div class="w-12 h-12 rounded-xl bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-file-earmark-code"></i>
          </div>
          <div>
            <h2 class="text-base font-semibold text-[#1C1B18]">Local JSON Backup Download</h2>
            <p class="text-xs text-[#78746C]">Offline readable JSON export of your whole database</p>
          </div>
        </div>
        <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
          Download a complete portable <code class="font-mono text-xs bg-[#F3F1EC] px-1.5 py-0.5 rounded">.json</code> file containing all registered firms, party ledgers, product catalog, stock records, invoices, and payments. Works completely offline.
        </p>
      </div>

      <a href="/backup/export" class="rf-btn rf-btn-sage-solid w-full">
        <i class="bi bi-download"></i> Download JSON Backup File
      </a>
    </div>
  </div>

  <!-- Restore Backup -->
  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-base font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-arrow-counterclockwise text-[#8A5A00]"></i> Restore Data from JSON Backup
      </h2>
    </div>
    <div class="rf-card-body space-y-4">
      <div class="rf-alert rf-alert-warning text-xs mb-0">
        <i class="bi bi-exclamation-triangle shrink-0"></i>
        <span><strong>Notice:</strong> Restoring a backup file will import all firms, items, parties, and bills from the JSON file into your account.</span>
      </div>

      <form action="/backup/restore?_csrf=<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="form-delete-confirm" data-confirm-message="Are you sure you want to restore data from this JSON backup?">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <div class="md:col-span-8">
            <label for="backup_file" class="rf-label-plain">Select Backup JSON File (.json)</label>
            <input type="file" class="rf-input py-2 text-xs" id="backup_file" name="backup_file" accept=".json,application/json" required>
          </div>
          <div class="md:col-span-4">
            <button type="submit" class="rf-btn rf-btn-amber w-full">
              <i class="bi bi-upload"></i> Upload &amp; Restore Backup
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Advisory -->
  <div class="rf-card-muted p-6 flex items-start gap-4">
    <i class="bi bi-shield-check text-xl text-[#2B526E] mt-0.5 shrink-0"></i>
    <div>
      <h3 class="text-sm font-semibold text-[#1C1B18] mb-1">Data Ownership, Portability &amp; 1-Year Retention Policy</h3>
      <p class="text-xs text-[#5C5A54] leading-relaxed m-0">
        You retain 100% ownership over your business data. Download regular <code class="font-mono bg-white px-1 rounded">.json</code> offline snapshots to your computer. Per our <a href="/terms" class="text-[#2B526E] font-medium hover:underline">Terms &amp; Conditions</a>, accounts expired and inactive for over <strong>1 continuous year (12 months)</strong> are permanently purged from platform servers.
      </p>
    </div>
  </div>
</div>
