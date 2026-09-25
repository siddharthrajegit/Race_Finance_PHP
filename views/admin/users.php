<?php include __DIR__ . '/_nav.php'; ?>

<!-- Filter & Search Toolbar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <!-- Search & Filters Form -->
      <form action="/admin/users" method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
        <div class="input-group input-group-sm" style="max-width: 320px;">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, phone, email..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <select name="role" class="form-select form-select-sm" style="max-width: 140px;" onchange="this.form.submit()">
          <option value="">All Roles</option>
          <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admins Only</option>
          <option value="user" <?= ($role ?? '') === 'user' ? 'selected' : '' ?>>Standard Users</option>
        </select>

        <select name="status" class="form-select form-select-sm" style="max-width: 140px;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>

        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Filter</button>
        <?php if (!empty($search) || !empty($role) || !empty($status)): ?>
          <a href="/admin/users" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Clear Filters">
            <i class="bi bi-x-circle"></i> Clear
          </a>
        <?php endif; ?>
      </form>

      <!-- Add New User Trigger Button -->
      <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus-fill me-1"></i> Add Subscriber Account
      </button>
    </div>
  </div>
</div>

<!-- Subscribers Table -->
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th style="width: 60px;" class="text-center">ID</th>
          <th>Subscriber / Owner</th>
          <th>Login ID (Phone)</th>
          <th class="text-center">Role</th>
          <th class="text-center">Status</th>
          <th class="text-center">Plan Validity</th>
          <th class="text-center">Firms</th>
          <th class="text-center">Records</th>
          <th style="min-width: 130px;">Storage (200 MB)</th>
          <th class="text-end">Gross Sales (₹)</th>
          <th>Registered</th>
          <th class="text-end px-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $u):
            $isSuspendedOrExpired = ($u['status'] ?? '') === 'suspended' || !empty($u['is_expired']);
            $currentUserId = $user['id'] ?? 0;
            $st = $u['storage'] ?? null;
          ?>
            <tr class="<?= $isSuspendedOrExpired ? 'table-danger bg-opacity-25' : '' ?>">
              <td class="text-center font-monospace small text-muted">#<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($u['avatar'])): ?>
                    <img src="<?= htmlspecialchars($u['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle" width="36" height="36">
                  <?php else: ?>
                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.9rem;">
                      <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                  <div>
                    <div class="fw-bold text-dark">
                      <a href="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                      <?php if (($u['id'] ?? 0) == $currentUserId): ?>
                        <span class="badge bg-secondary ms-1 small" style="font-size: 0.65rem;">You</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td class="small">
                <div class="fw-bold text-dark font-monospace">
                  <i class="bi bi-phone text-primary me-1"></i><?= !empty($u['phone']) ? htmlspecialchars($u['phone'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">No Phone</span>' ?>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;"><?= !empty($u['email']) ? htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted fst-italic">No Email</span>' ?></div>
              </td>
              <td class="text-center">
                <?php if (($u['role'] ?? '') === 'admin'): ?>
                  <span class="badge bg-warning text-dark border border-warning-subtle px-2 py-1">
                    <i class="bi bi-shield-fill-check me-1"></i> ADMIN
                  </span>
                <?php else: ?>
                  <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                    USER
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (($u['status'] ?? '') === 'active'): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">ACTIVE</span>
                <?php else: ?>
                  <span class="badge bg-danger text-white px-2 py-1">SUSPENDED</span>
                <?php endif; ?>
              </td>
              <td class="text-center small">
                <?php if (($u['role'] ?? '') === 'admin'): ?>
                  <span class="badge bg-light text-secondary border">Lifetime (Admin)</span>
                <?php elseif (!empty($u['subscription_expires_at'])): ?>
                  <?php if (!empty($u['is_expired'])): ?>
                    <span class="badge bg-danger text-white mb-1 d-inline-block px-2 py-1">
                      <i class="bi bi-x-circle me-1"></i> Expired
                    </span>
                    <div class="text-danger fw-semibold font-monospace" style="font-size: 0.72rem;">
                      <?= date('d M Y', strtotime($u['subscription_expires_at'])) ?>
                    </div>
                  <?php elseif (!empty($u['is_expiring_soon'])): ?>
                    <span class="badge bg-warning text-dark mb-1 d-inline-block px-2 py-1">
                      <i class="bi bi-clock-history me-1"></i> <?= htmlspecialchars((string)($u['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d left
                    </span>
                    <div class="text-muted font-monospace" style="font-size: 0.72rem;">
                      <?= date('d M Y', strtotime($u['subscription_expires_at'])) ?>
                    </div>
                  <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-1 d-inline-block px-2 py-1">
                      <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars((string)($u['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d left
                    </span>
                    <div class="text-muted font-monospace" style="font-size: 0.72rem;">
                      <?= date('d M Y', strtotime($u['subscription_expires_at'])) ?>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge bg-light text-muted border">None Set</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars((string)($u['firms_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?>/2</span>
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars((string)($u['invoices_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?php if ($st):
                  $pct = (float)($st['totalUsedPercentage'] ?? 0);
                  $barColor = $pct > 85 ? 'bg-danger' : ($pct > 60 ? 'bg-warning' : 'bg-success');
                ?>
                  <div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                      <span class="fw-bold font-monospace" style="font-size: 0.75rem;"><?= htmlspecialchars((string)($st['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</span>
                      <span class="text-muted" style="font-size: 0.68rem;"><?= htmlspecialchars((string)$pct, ENT_QUOTES, 'UTF-8') ?>%</span>
                    </div>
                    <div class="progress" style="height: 5px;">
                      <div class="progress-bar <?= $barColor ?>" role="progressbar" style="width: <?= max($pct, 3) ?>%;"></div>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.68rem;">
                      <?= ($st['firmCount'] ?? 0) === 1 ? '1 Firm (200MB)' : (($st['firmCount'] ?? 0) === 2 ? '2 Firms (100MB ea)' : 'Cap: 200MB') ?>
                    </div>
                  </div>
                <?php else: ?>
                  <span class="text-muted small">0.00 / 200 MB</span>
                <?php endif; ?>
              </td>
              <td class="text-end fw-bold font-monospace text-dark">
                ₹ <?= number_format((float)($u['total_turnover'] ?? 0), 0) ?>
              </td>
              <td class="small text-muted font-monospace">
                <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '--' ?>
              </td>
              <td class="text-end px-3">
                <div class="dropdown">
                  <button class="btn btn-light btn-sm rounded-circle p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                    <i class="bi bi-three-dots-vertical text-muted"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" style="border-radius: 10px;">
                    <li>
                      <a class="dropdown-item py-2 small fw-semibold text-primary" href="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-search me-2"></i> Inspect Subscriber &amp; Businesses
                      </a>
                    </li>
                    <?php if (($u['role'] ?? '') !== 'admin'): ?>
                      <li>
                        <button class="dropdown-item py-2 small text-success fw-semibold btn-renew-subscriber" type="button"
                          data-id="<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                          data-name="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-phone="<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-expiry="<?= !empty($u['subscription_expires_at']) ? date('d M Y', strtotime($u['subscription_expires_at'])) : 'None' ?>">
                          <i class="bi bi-arrow-repeat me-2 text-success"></i> Renew / Extend Plan (+1 Year)
                        </button>
                      </li>
                    <?php endif; ?>
                    <li>
                      <button class="dropdown-item py-2 small" type="button" onclick="openResetPasswordModal('<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
                        <i class="bi bi-key-fill me-2 text-warning"></i> Reset Password
                      </button>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>

                    <!-- Role Toggle -->
                    <?php if (($u['id'] ?? 0) != $currentUserId): ?>
                      <li>
                        <form action="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/role" method="POST" class="m-0">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item py-2 small <?= ($u['role'] ?? '') === 'admin' ? 'text-danger' : 'text-primary' ?>">
                            <i class="bi <?= ($u['role'] ?? '') === 'admin' ? 'bi-shield-slash' : 'bi-shield-fill-plus' ?> me-2"></i>
                            <?= ($u['role'] ?? '') === 'admin' ? 'Demote to Standard User' : 'Promote to Admin' ?>
                          </button>
                        </form>
                      </li>

                      <!-- Status Toggle -->
                      <li>
                        <form action="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/status" method="POST" class="m-0">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item py-2 small <?= ($u['status'] ?? '') === 'active' ? 'text-danger' : 'text-success' ?>">
                            <i class="bi <?= ($u['status'] ?? '') === 'active' ? 'bi-lock-fill' : 'bi-unlock-fill' ?> me-2"></i>
                            <?= ($u['status'] ?? '') === 'active' ? 'Suspend Account' : 'Activate Account' ?>
                          </button>
                        </form>
                      </li>

                      <li><hr class="dropdown-divider my-1"></li>

                      <!-- Delete User -->
                      <li>
                        <form action="/admin/users/<?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/delete" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete subscriber '<?= htmlspecialchars(addslashes($u['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>'? All registered business firms, inventory items, and invoices will be purged.">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item py-2 small text-danger">
                            <i class="bi bi-trash-fill me-2"></i> Delete Account
                          </button>
                        </form>
                      </li>
                    <?php endif; ?>
                  </ul>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="12" class="text-center py-5 text-muted">
              <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
              No subscriber accounts match the criteria.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal 1: Create New Subscriber by Admin (Phone ID & Manual Password) -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header bg-dark text-white border-0 py-3">
        <h5 class="modal-title fw-bold" id="createUserModalLabel">
          <i class="bi bi-person-plus-fill me-2 text-success"></i> Add Subscriber (Phone ID &amp; Password)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/admin/users/create" method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Business Owner / Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Sharma" required>
          </div>

          <!-- Phone Number as Primary User ID -->
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
              <span>Mobile Phone Number (Login ID) <span class="text-danger">*</span></span>
              <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem;">Primary User ID</span>
            </label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone-fill"></i></span>
              <input type="tel" name="phone" id="createPhoneInput" class="form-control font-monospace fw-semibold" placeholder="e.g. 9876543210" maxlength="10" pattern="[0-9]{10}" required>
            </div>
            <div class="form-text small text-muted">
              <i class="bi bi-info-circle me-1 text-primary"></i>This 10-digit number will be used by the subscriber to log into RACE FINANCE.
            </div>
          </div>

          <!-- Password Manual Entry with View Toggle & Generator -->
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
              <span>Password (Manually Created) <span class="text-danger">*</span></span>
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" onclick="generateRandomPassword('createPasswordInput')">
                <i class="bi bi-magic me-1"></i>Generate Password
              </button>
            </label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="password" id="createPasswordInput" class="form-control font-monospace" placeholder="Enter password (min 8 chars, letters & numbers)" required minlength="8">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('createPasswordInput', 'toggleCreatePassIcon')">
                <i class="bi bi-eye" id="toggleCreatePassIcon"></i>
              </button>
            </div>
            <div class="form-text small text-muted">You will be able to copy or WhatsApp this password to the subscriber immediately after creation.</div>
          </div>

          <!-- Subscription Plan Period -->
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
              <span>Subscription Duration <span class="text-danger">*</span></span>
              <span class="badge bg-success-subtle text-success" style="font-size: 0.7rem;">Auto-Expiry Guard</span>
            </label>
            <select name="subscription_duration" id="subscriptionDurationSelect" class="form-select fw-semibold" onchange="handleSubscriptionDurationChange(this)">
              <option value="365" selected>1 Year (365 Days) — Standard Annual Plan</option>
              <option value="730">2 Years (730 Days)</option>
              <option value="180">6 Months (180 Days)</option>
              <option value="30">1 Month (30 Days Trial)</option>
              <option value="custom">Custom Date Range...</option>
            </select>
            <div class="form-text small text-muted">
              After this period, the account login will be gated. <strong>All invoices and data remain safely stored</strong> without deletion.
            </div>
          </div>

          <div class="mb-3 d-none" id="customExpiryGroup">
            <label class="form-label fw-bold small text-dark">Custom Expiration Date <span class="text-danger">*</span></label>
            <input type="date" name="custom_expiry_date" id="customExpiryInput" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Email Address (Optional)</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. ramesh@example.com">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Account Role</label>
            <select name="role" class="form-select">
              <option value="user" selected>Standard Business User</option>
              <option value="admin">Platform Administrator</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 bg-light">
          <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
            <i class="bi bi-check-circle me-1"></i> Register Subscriber
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Renew / Extend Subscription -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header bg-success text-white border-0 py-3">
        <h5 class="modal-title fw-bold" id="renewModalLabel">
          <i class="bi bi-arrow-repeat me-2"></i> Renew / Extend Subscriber Plan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="renewForm" method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="modal-body p-4">
          <div class="p-3 bg-light rounded-3 mb-3 border">
            <div class="small text-muted mb-1">Subscriber:</div>
            <div class="fw-bold text-dark fs-6" id="renewUserName"></div>
            <div class="font-monospace text-muted small mt-1">
              Phone (User ID): <strong class="text-dark" id="renewUserPhone"></strong> | Current Expiry: <strong class="text-dark" id="renewCurrentExpiry"></strong>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Extend Subscription By</label>
            <select name="duration_days" class="form-select fw-semibold">
              <option value="365" selected>+1 Year (365 Days) — Standard Renewal</option>
              <option value="730">+2 Years (730 Days)</option>
              <option value="180">+6 Months (180 Days)</option>
              <option value="30">+1 Month (30 Days)</option>
            </select>
            <div class="form-text small text-muted">
              Access will be extended from their current expiry date (or from today if already expired), and their account will immediately be active.
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 bg-light">
          <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-check-circle me-1"></i> Confirm Renewal
          </button>
        </div>
      </form>
    </div>
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
  <div class="modal fade" id="shareCredentialsModal" tabindex="-1" aria-labelledby="shareCredentialsModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
        <div class="modal-header bg-success text-white border-0 py-3">
          <h5 class="modal-title fw-bold" id="shareCredentialsModalLabel">
            <i class="bi bi-shield-check me-2"></i> Account Created &amp; Ready to Share!
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="alert alert-success d-flex align-items-center mb-3 py-2 px-3">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>Subscriber <strong><?= htmlspecialchars($newlyCreatedUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> has been registered successfully.</div>
          </div>

          <div class="card border-0 bg-light rounded-3 p-3 mb-3 font-monospace">
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Login User ID (Phone):</span>
              <strong class="text-dark"><?= htmlspecialchars($newlyCreatedUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Password:</span>
              <strong class="text-primary fs-6"><?= htmlspecialchars($newlyCreatedUser['rawPassword'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">Valid Until:</span>
              <strong class="text-success"><?= htmlspecialchars($newlyCreatedUser['expiresAt'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
          </div>

          <textarea id="shareCredentialsText" class="d-none">User ID: <?= htmlspecialchars($newlyCreatedUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
Password: <?= htmlspecialchars($newlyCreatedUser['rawPassword'] ?? '', ENT_QUOTES, 'UTF-8') ?>
Portal: <?= htmlspecialchars($appUrl ?? 'https://racefinance.site', ENT_QUOTES, 'UTF-8') ?>/auth/login
Validity: <?= htmlspecialchars($newlyCreatedUser['expiresAt'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

          <div class="d-grid gap-2">
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-success fw-bold py-2 shadow-sm rounded-pill d-flex align-items-center justify-content-center">
              <i class="bi bi-whatsapp fs-5 me-2"></i> Share Credentials on WhatsApp
            </a>
            <button type="button" class="btn btn-outline-dark fw-semibold py-2 rounded-pill" onclick="copyCredentialsFromElement('shareCredentialsText')">
              <i class="bi bi-clipboard me-2"></i> Copy Details to Clipboard
            </button>
          </div>
        </div>
        <div class="modal-footer border-0 bg-light">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Done</button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Modal 4: Admin Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header bg-warning text-dark border-0 py-3">
        <h5 class="modal-title fw-bold" id="resetPasswordModalLabel"><i class="bi bi-key-fill me-2"></i> Reset Subscriber Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="resetPasswordForm" method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Overriding password for subscriber: <strong id="resetUserName" class="text-dark"></strong>
          </p>
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">New Password <span class="text-danger">*</span></label>
            <input type="password" name="new_password" class="form-control" placeholder="Enter new password (min 8 chars, letters & numbers)" required minlength="8">
          </div>
        </div>
        <div class="modal-footer border-0 bg-light">
          <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-check-circle me-1"></i> Update Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openResetPasswordModal(userId, userName) {
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetPasswordForm').action = `/admin/users/${userId}/reset-password`;
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
  }

  function openRenewModal(userId, userName, userPhone, currentExpiry) {
    document.getElementById('renewUserName').textContent = userName;
    document.getElementById('renewUserPhone').textContent = userPhone;
    document.getElementById('renewCurrentExpiry').textContent = currentExpiry;
    document.getElementById('renewForm').action = `/admin/users/${userId}/renew`;
    const modal = new bootstrap.Modal(document.getElementById('renewModal'));
    modal.show();
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
      customGroup.classList.remove('d-none');
      customInput.required = true;
    } else {
      customGroup.classList.add('d-none');
      customInput.required = false;
    }
  }

  function copyCredentialsFromElement(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.value || el.textContent;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Subscriber credentials copied to clipboard!');
      }).catch(() => {
        prompt('Copy credentials:', text);
      });
    } else {
      prompt('Copy credentials:', text);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Handle Renew button clicks
    document.addEventListener('click', (e) => {
      const renewBtn = e.target.closest('.btn-renew-subscriber');
      if (renewBtn) {
        const { id, name, phone, expiry } = renewBtn.dataset;
        openRenewModal(id, name, phone, expiry);
      }
    });

    const shareModalElem = document.getElementById('shareCredentialsModal');
    if (shareModalElem) {
      const shareModal = new bootstrap.Modal(shareModalElem);
      shareModal.show();
    }
  });
</script>
