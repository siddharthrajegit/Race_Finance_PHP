<?php include __DIR__ . '/_nav.php'; ?>

<!-- Filter & Search Toolbar -->
<div class="rf-card mb-6">
  <div class="rf-card-body">
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3">
      <!-- Search & Filters Form -->
      <form action="/admin/users" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
        <div class="relative w-full sm:w-72">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none">
            <i class="bi bi-search text-xs"></i>
          </span>
          <input type="text" name="search" class="rf-input rf-input-sm pl-8" placeholder="Search by name, phone, email..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <select name="role" class="rf-select rf-select-sm w-36" onchange="this.form.submit()">
          <option value="">All Roles</option>
          <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admins Only</option>
          <option value="user" <?= ($role ?? '') === 'user' ? 'selected' : '' ?>>Standard Users</option>
        </select>

        <select name="status" class="rf-select rf-select-sm w-36" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>

        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Filter</button>
        <?php if (!empty($search) || !empty($role) || !empty($status)): ?>
          <a href="/admin/users" class="rf-btn rf-btn-ghost rf-btn-sm" title="Clear Filters">
            <i class="bi bi-x-circle"></i> Clear
          </a>
        <?php endif; ?>
      </form>

      <!-- Add New User Trigger Button -->
      <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-sm shrink-0" data-ui-modal="#createUserModal">
        <i class="bi bi-person-plus"></i> Add Subscriber Account
      </button>
    </div>
  </div>
</div>

<!-- Subscribers Table -->
<div class="rf-card">
  <div class="rf-table-wrap border-0 rounded-xl xl:overflow-visible">
    <table class="rf-table">
      <thead>
        <tr>
          <th class="w-16 text-center">ID</th>
          <th>Subscriber / Owner</th>
          <th>Login ID (Phone)</th>
          <th class="text-center">Role</th>
          <th class="text-center">Status</th>
          <th class="text-center">Plan Validity</th>
          <th class="text-center">Firms</th>
          <th class="text-center">Records</th>
          <th class="min-w-[140px]">Storage (200 MB)</th>
          <th class="text-right">Gross Sales (₹)</th>
          <th>Registered</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $u):
            $isSuspendedOrExpired = ($u['status'] ?? '') === 'suspended' || !empty($u['is_expired']);
            $currentUserId = $user['id'] ?? 0;
            $st = $u['storage'] ?? null;
          ?>
            <tr class="<?= $isSuspendedOrExpired ? 'bg-rose-bg/40' : '' ?>">
              <td class="text-center font-mono text-xs text-ink-muted">#<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div class="flex items-center gap-2.5">
                  <?php if (!empty($u['avatar'])): ?>
                    <img src="<?= htmlspecialchars($u['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-9 h-9 rounded-full object-cover border border-border">
                  <?php else: ?>
                    <div class="w-9 h-9 rounded-full bg-canvas-alt border border-border text-ink font-semibold text-xs flex items-center justify-center shrink-0">
                      <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                  <div class="min-w-0">
                    <div class="font-medium text-ink flex items-center gap-1.5">
                      <a href="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="hover:underline text-ink truncate">
                        <?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                      </a>
                      <?php if (($u['id'] ?? 0) == $currentUserId): ?>
                        <span class="rf-badge rf-badge-neutral text-[10px]">You</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td class="text-xs">
                <div class="font-medium text-ink font-mono flex items-center gap-1">
                  <i class="bi bi-phone text-ink-muted"></i>
                  <?= !empty($u['phone']) ? htmlspecialchars($u['phone'], ENT_QUOTES, 'UTF-8') : '<span class="text-ink-subtle font-sans">No Phone</span>' ?>
                </div>
                <div class="text-ink-muted text-[11px] mt-0.5">
                  <?= !empty($u['email']) ? htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') : '<span class="text-ink-subtle italic">No Email</span>' ?>
                </div>
              </td>
              <td class="text-center">
                <?php if (($u['role'] ?? '') === 'admin'): ?>
                  <span class="rf-badge rf-badge-amber">
                    <i class="bi bi-shield-check"></i> ADMIN
                  </span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-slate">USER</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (($u['status'] ?? '') === 'active'): ?>
                  <span class="rf-badge rf-badge-sage">ACTIVE</span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-rose">SUSPENDED</span>
                <?php endif; ?>
              </td>
              <td class="text-center text-xs">
                <?php if (($u['role'] ?? '') === 'admin'): ?>
                  <span class="rf-badge rf-badge-neutral">Lifetime (Admin)</span>
                <?php elseif (!empty($u['subscription_expires_at'])): ?>
                  <?php if (!empty($u['is_expired'])): ?>
                    <span class="rf-badge rf-badge-rose mb-1">
                      <i class="bi bi-x-circle"></i> Expired
                    </span>
                    <div class="text-rose font-medium font-mono text-[11px]">
                      <?= date('d M Y', strtotime($u['subscription_expires_at'])) ?>
                    </div>
                  <?php elseif (!empty($u['is_expiring_soon'])): ?>
                    <span class="rf-badge rf-badge-amber mb-1">
                      <i class="bi bi-clock-history"></i> <?= htmlspecialchars((string)($u['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d left
                    </span>
                    <div class="text-ink-muted font-mono text-[11px]">
                      <?= date('d M Y', strtotime($u['subscription_expires_at'])) ?>
                    </div>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-sage mb-1">
                      <i class="bi bi-check-circle"></i> <?= htmlspecialchars((string)($u['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d left
                    </span>
                    <div class="text-ink-muted font-mono text-[11px]">
                      <?= date('d M Y', strtotime($u['subscription_expires_at'])) ?>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="rf-badge rf-badge-neutral">None Set</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="rf-badge rf-badge-neutral font-mono"><?= htmlspecialchars((string)($u['firms_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>/2</span>
              </td>
              <td class="text-center">
                <span class="rf-badge rf-badge-neutral font-mono"><?= htmlspecialchars((string)($u['invoices_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?php if ($st):
                  $pct = (float)($st['totalUsedPercentage'] ?? 0);
                  $barColor = $pct > 85 ? 'bg-rose' : ($pct > 60 ? 'bg-amber' : 'bg-sage');
                ?>
                  <div>
                    <div class="flex items-center justify-between mb-1 text-xs">
                      <span class="font-semibold font-mono text-ink"><?= htmlspecialchars((string)($st['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</span>
                      <span class="text-ink-muted font-mono text-[11px]"><?= htmlspecialchars((string)$pct, ENT_QUOTES, 'UTF-8') ?>%</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-canvas-alt overflow-hidden">
                      <div class="h-full rounded-full <?= $barColor ?>" style="width: <?= min(100, max($pct, 3)) ?>%;"></div>
                    </div>
                    <div class="text-ink-subtle mt-1 text-[11px]">
                      <?= ($st['firmCount'] ?? 0) === 1 ? '1 Firm (200MB)' : (($st['firmCount'] ?? 0) === 2 ? '2 Firms (100MB ea)' : 'Cap: 200MB') ?>
                    </div>
                  </div>
                <?php else: ?>
                  <span class="text-ink-subtle text-xs font-mono">0.00 / 200 MB</span>
                <?php endif; ?>
              </td>
              <td class="text-right font-semibold font-mono text-ink whitespace-nowrap">
                ₹ <?= number_format((float)($u['total_turnover'] ?? 0), 0) ?>
              </td>
              <td class="text-xs text-ink-muted font-mono whitespace-nowrap">
                <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '--' ?>
              </td>
              <td class="text-right">
                <div class="rf-dropdown inline-block" data-ui-dropdown>
                  <button class="rf-btn rf-btn-ghost rf-btn-sm w-8 h-8 p-0 justify-center rounded-full" type="button" data-ui-dropdown-toggle aria-expanded="false" aria-label="Subscriber actions">
                    <i class="bi bi-three-dots-vertical text-ink-muted"></i>
                  </button>
                  <div class="rf-dropdown-menu w-60" data-ui-dropdown-menu>
                    <a class="rf-dropdown-item font-medium" href="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                      <i class="bi bi-search text-ink-muted"></i> Inspect Subscriber &amp; Businesses
                    </a>
                    <?php if (($u['role'] ?? '') !== 'admin'): ?>
                      <button class="rf-dropdown-item text-sage font-medium btn-renew-subscriber" type="button"
                        data-id="<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-name="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-phone="<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-expiry="<?= !empty($u['subscription_expires_at']) ? date('d M Y', strtotime($u['subscription_expires_at'])) : 'None' ?>">
                        <i class="bi bi-arrow-repeat text-sage"></i> Renew / Extend Plan (+1 Year)
                      </button>
                    <?php endif; ?>
                    <button class="rf-dropdown-item" type="button" onclick="openResetPasswordModal('<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                      <i class="bi bi-key text-amber"></i> Reset Password
                    </button>

                    <?php if (($u['id'] ?? 0) != $currentUserId): ?>
                      <div class="rf-dropdown-divider"></div>

                      <!-- Role Toggle -->
                      <form action="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/role" method="POST" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item <?= ($u['role'] ?? '') === 'admin' ? 'text-rose' : '' ?>">
                          <i class="bi <?= ($u['role'] ?? '') === 'admin' ? 'bi-shield-slash' : 'bi-shield-plus' ?>"></i>
                          <?= ($u['role'] ?? '') === 'admin' ? 'Demote to Standard User' : 'Promote to Admin' ?>
                        </button>
                      </form>

                      <!-- Status Toggle -->
                      <form action="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/status" method="POST" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item <?= ($u['status'] ?? '') === 'active' ? 'text-rose' : 'text-sage' ?>">
                          <i class="bi <?= ($u['status'] ?? '') === 'active' ? 'bi-lock' : 'bi-unlock' ?>"></i>
                          <?= ($u['status'] ?? '') === 'active' ? 'Suspend Account' : 'Activate Account' ?>
                        </button>
                      </form>

                      <div class="rf-dropdown-divider"></div>

                      <!-- Delete User -->
                      <form action="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/delete" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete subscriber '<?= htmlspecialchars(addslashes($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>'? All registered business firms, inventory items, and invoices will be purged.">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item text-rose">
                          <i class="bi bi-trash"></i> Delete Account
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="12" class="text-center py-12 text-ink-muted">
              <i class="bi bi-people text-3xl block mb-2 text-ink-subtle"></i>
              No subscriber accounts match the criteria.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal 1: Create New Subscriber by Admin (Phone ID & Manual Password) -->
<div class="rf-modal hidden" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="rf-modal-dialog">
    <div class="rf-modal-header">
      <h5 class="text-base font-semibold text-ink flex items-center gap-2 mb-0" id="createUserModalLabel">
        <i class="bi bi-person-plus text-sage"></i> Add Subscriber (Phone ID &amp; Password)
      </h5>
      <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg text-sm"></i>
      </button>
    </div>
    <form action="/admin/users/create" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-modal-body space-y-4">
        <div>
          <label class="rf-label">Business Owner / Name <span class="text-rose">*</span></label>
          <input type="text" name="name" class="rf-input" placeholder="e.g. Ramesh Sharma" required>
        </div>

        <!-- Phone Number as Primary User ID -->
        <div>
          <label class="rf-label flex items-center justify-between">
            <span>Mobile Phone Number (Login ID) <span class="text-rose">*</span></span>
            <span class="rf-badge rf-badge-neutral text-[10px]">Primary User ID</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none">
              <i class="bi bi-telephone text-xs"></i>
            </span>
            <input type="tel" name="phone" id="createPhoneInput" class="rf-input pl-9 font-mono font-medium" placeholder="e.g. 9876543210" maxlength="10" pattern="[0-9]{10}" required>
          </div>
          <p class="rf-help flex items-center gap-1.5">
            <i class="bi bi-info-circle"></i>
            This 10-digit number will be used by the subscriber to log into RACE FINANCE.
          </p>
        </div>

        <!-- Password Manual Entry with View Toggle & Generator -->
        <div>
          <label class="rf-label flex items-center justify-between">
            <span>Password (Manually Created) <span class="text-rose">*</span></span>
            <button type="button" class="text-xs font-medium text-ink underline underline-offset-2 hover:text-ink-muted" onclick="generateRandomPassword('createPasswordInput')">
              <i class="bi bi-magic mr-1"></i>Generate Password
            </button>
          </label>
          <div class="flex gap-1.5">
            <div class="relative flex-1">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none">
                <i class="bi bi-lock text-xs"></i>
              </span>
              <input type="password" name="password" id="createPasswordInput" class="rf-input pl-9 font-mono" placeholder="Min 8 chars, letters &amp; numbers" required minlength="8">
            </div>
            <button class="rf-btn rf-btn-secondary px-3" type="button" onclick="togglePasswordVisibility('createPasswordInput', 'toggleCreatePassIcon')" aria-label="Toggle password visibility">
              <i class="bi bi-eye" id="toggleCreatePassIcon"></i>
            </button>
          </div>
          <p class="rf-help">You will be able to copy or WhatsApp this password to the subscriber immediately after creation.</p>
        </div>

        <!-- Subscription Plan Period -->
        <div>
          <label class="rf-label flex items-center justify-between">
            <span>Subscription Duration <span class="text-rose">*</span></span>
            <span class="rf-badge rf-badge-sage text-[10px]">Auto-Expiry Guard</span>
          </label>
          <select name="subscription_duration" id="subscriptionDurationSelect" class="rf-select font-medium" onchange="handleSubscriptionDurationChange(this)">
            <option value="365" selected>1 Year (365 Days) — Standard Annual Plan</option>
            <option value="730">2 Years (730 Days)</option>
            <option value="180">6 Months (180 Days)</option>
            <option value="30">1 Month (30 Days Trial)</option>
            <option value="custom">Custom Date Range...</option>
          </select>
          <p class="rf-help">After this period, login will be gated while <strong>all invoices and data remain safely stored</strong>.</p>
        </div>

        <div class="hidden" id="customExpiryGroup">
          <label class="rf-label">Custom Expiration Date <span class="text-rose">*</span></label>
          <input type="date" name="custom_expiry_date" id="customExpiryInput" class="rf-input">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="rf-label">Email Address (Optional)</label>
            <input type="email" name="email" class="rf-input" placeholder="e.g. ramesh@example.com">
          </div>
          <div>
            <label class="rf-label">Account Role</label>
            <select name="role" class="rf-select">
              <option value="user" selected>Standard Business User</option>
              <option value="admin">Platform Administrator</option>
            </select>
          </div>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-sage-solid">
          <i class="bi bi-check-circle"></i> Register Subscriber
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Renew / Extend Subscription -->
<div class="rf-modal hidden" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
  <div class="rf-modal-dialog">
    <div class="rf-modal-header">
      <h5 class="text-base font-semibold text-ink flex items-center gap-2 mb-0" id="renewModalLabel">
        <i class="bi bi-arrow-repeat text-sage"></i> Renew / Extend Subscriber Plan
      </h5>
      <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg text-sm"></i>
      </button>
    </div>
    <form id="renewForm" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-modal-body space-y-4">
        <div class="rf-card-muted p-3.5 space-y-1">
          <div class="text-[11px] uppercase tracking-wider text-ink-muted font-medium">Subscriber</div>
          <div class="font-semibold text-ink" id="renewUserName"></div>
          <div class="font-mono text-ink-muted text-xs">
            Phone (User ID): <strong class="text-ink" id="renewUserPhone"></strong> | Current Expiry: <strong class="text-ink" id="renewCurrentExpiry"></strong>
          </div>
        </div>

        <div>
          <label class="rf-label">Extend Subscription By</label>
          <select name="duration_days" class="rf-select font-medium">
            <option value="365" selected>+1 Year (365 Days) — Standard Renewal</option>
            <option value="730">+2 Years (730 Days)</option>
            <option value="180">+6 Months (180 Days)</option>
            <option value="30">+1 Month (30 Days)</option>
          </select>
          <p class="rf-help">Access will be extended from their current expiry date (or from today if already expired), and their account will immediately be active.</p>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-sage-solid">
          <i class="bi bi-check-circle"></i> Confirm Renewal
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 3: Share Credentials Popup (Triggered automatically after creating user) -->
<?php if (!empty($newlyCreatedUser)):
  $waText = rawurlencode(
    "Hello " . ($newlyCreatedUser['name'] ?? '') . ",\n\n" .
    "Your RACE FINANCE Business Billing & Accounting account is ready!\n\n" .
    "📲 Login Portal: " . ($appUrl ?? 'https://racefinance.site') . "/auth/login\n" .
    "👤 User ID (Phone): " . ($newlyCreatedUser['phone'] ?? '') . "\n" .
    "🔑 Password: " . ($newlyCreatedUser['rawPassword'] ?? '') . "\n" .
    "📅 Plan Validity: " . ($newlyCreatedUser['expiresAt'] ?? '') . "\n\n" .
    "Please log in to start creating your GST bills and managing inventory."
  );
  $waUrl = "https://wa.me/91" . ($newlyCreatedUser['phone'] ?? '') . "?text=" . $waText;
?>
  <div class="rf-modal hidden" id="shareCredentialsModal" tabindex="-1" aria-labelledby="shareCredentialsModalLabel" aria-hidden="true">
    <div class="rf-modal-dialog">
      <div class="rf-modal-header">
        <h5 class="text-base font-semibold text-ink flex items-center gap-2 mb-0" id="shareCredentialsModalLabel">
          <i class="bi bi-shield-check text-sage"></i> Account Created &amp; Ready to Share
        </h5>
        <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>
      <div class="rf-modal-body space-y-4">
        <div class="rf-alert rf-alert-success mb-0">
          <i class="bi bi-check-circle mt-0.5 shrink-0"></i>
          <div>Subscriber <strong><?= htmlspecialchars($newlyCreatedUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> has been registered successfully.</div>
        </div>

        <div class="rf-card-muted p-4 font-mono text-xs space-y-2">
          <div class="flex items-center justify-between py-1 border-b border-border-subtle">
            <span class="text-ink-muted">Login User ID (Phone)</span>
            <strong class="text-ink"><?= htmlspecialchars($newlyCreatedUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
          <div class="flex items-center justify-between py-1 border-b border-border-subtle">
            <span class="text-ink-muted">Password</span>
            <strong class="text-ink text-sm"><?= htmlspecialchars($newlyCreatedUser['rawPassword'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
          <div class="flex items-center justify-between py-1">
            <span class="text-ink-muted">Valid Until</span>
            <strong class="text-sage"><?= htmlspecialchars($newlyCreatedUser['expiresAt'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
        </div>

        <textarea id="shareCredentialsText" class="hidden">User ID: <?= htmlspecialchars($newlyCreatedUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
Password: <?= htmlspecialchars($newlyCreatedUser['rawPassword'] ?? '', ENT_QUOTES, 'UTF-8') ?>
Portal: <?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/auth/login
Validity: <?= htmlspecialchars($newlyCreatedUser['expiresAt'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

        <div class="space-y-2">
          <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="rf-btn rf-btn-sage-solid w-full justify-center py-2.5">
            <i class="bi bi-whatsapp"></i> Share Credentials on WhatsApp
          </a>
          <button type="button" class="rf-btn rf-btn-secondary w-full justify-center py-2.5" onclick="copyCredentialsFromElement('shareCredentialsText')">
            <i class="bi bi-clipboard"></i> Copy Details to Clipboard
          </button>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary" data-ui-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Modal 4: Admin Reset Password -->
<div class="rf-modal hidden" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="rf-modal-dialog">
    <div class="rf-modal-header">
      <h5 class="text-base font-semibold text-ink flex items-center gap-2 mb-0" id="resetPasswordModalLabel">
        <i class="bi bi-key text-amber"></i> Reset Subscriber Password
      </h5>
      <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg text-sm"></i>
      </button>
    </div>
    <form id="resetPasswordForm" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-modal-body space-y-4">
        <p class="text-ink-muted text-sm mb-0">
          Overriding password for subscriber: <strong id="resetUserName" class="text-ink"></strong>
        </p>
        <div>
          <label class="rf-label">New Password <span class="text-rose">*</span></label>
          <input type="password" name="new_password" class="rf-input" placeholder="Enter new password (min 8 chars, letters &amp; numbers)" required minlength="8">
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-primary">
          <i class="bi bi-check-circle"></i> Update Password
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openResetPasswordModal(userId, userName) {
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetPasswordForm').action = `/admin/users/${userId}/reset-password`;
    if (window.RFUI) window.RFUI.openModal('resetPasswordModal');
  }

  function openRenewModal(userId, userName, userPhone, currentExpiry) {
    document.getElementById('renewUserName').textContent = userName;
    document.getElementById('renewUserPhone').textContent = userPhone;
    document.getElementById('renewCurrentExpiry').textContent = currentExpiry;
    document.getElementById('renewForm').action = `/admin/users/${userId}/renew`;
    if (window.RFUI) window.RFUI.openModal('renewModal');
  }

  function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye';
    }
  }

  function generateRandomPassword(inputId) {
    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lower = 'abcdefghijkmnopqrstuvwxyz';
    const digits = '23456789';
    const specials = '@#$%!';
    const all = upper + lower + digits + specials;
    let pass = upper.charAt(Math.floor(Math.random() * upper.length)) +
               lower.charAt(Math.floor(Math.random() * lower.length)) +
               digits.charAt(Math.floor(Math.random() * digits.length)) +
               specials.charAt(Math.floor(Math.random() * specials.length));
    for (let i = 4; i < 10; i++) {
      pass += all.charAt(Math.floor(Math.random() * all.length));
    }
    const input = document.getElementById(inputId);
    if (input) {
      input.type = 'text';
      input.value = pass;
      const icon = document.getElementById('toggleCreatePassIcon');
      if (icon) icon.className = 'bi bi-eye-slash';
    }
  }

  function handleSubscriptionDurationChange(selectElem) {
    const customGroup = document.getElementById('customExpiryGroup');
    const customInput = document.getElementById('customExpiryInput');
    if (selectElem.value === 'custom') {
      customGroup.classList.remove('hidden');
      customInput.required = true;
    } else {
      customGroup.classList.add('hidden');
      customInput.required = false;
    }
  }

  function copyCredentialsFromElement(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.value || el.textContent;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(() => {
        if (window.RFUI && window.RFUI.toast) {
          window.RFUI.toast('Subscriber credentials copied to clipboard!', 'success');
        } else {
          alert('Subscriber credentials copied to clipboard!');
        }
      }).catch(() => {
        prompt('Copy credentials:', text);
      });
    } else {
      prompt('Copy credentials:', text);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
      const renewBtn = e.target.closest('.btn-renew-subscriber');
      if (renewBtn) {
        const { id, name, phone, expiry } = renewBtn.dataset;
        openRenewModal(id, name, phone, expiry);
      }
    });

    const shareModalElem = document.getElementById('shareCredentialsModal');
    if (shareModalElem && window.RFUI) {
      window.RFUI.openModal('shareCredentialsModal');
    }
  });
</script>
