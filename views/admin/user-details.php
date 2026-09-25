<?php include __DIR__ . '/_nav.php'; ?>

<!-- Top Back Action & User Title -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
  <div class="d-flex align-items-center gap-3">
    <a href="/admin/users" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
      <i class="bi bi-arrow-left me-1"></i> All Subscribers
    </a>
    <div>
      <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
        <span><?= htmlspecialchars($targetUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        <?php if (($targetUser['role'] ?? '') === 'admin'): ?>
          <span class="badge bg-warning text-dark px-2 py-1 fs-6">ADMIN</span>
        <?php else: ?>
          <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 fs-6">SUBSCRIBER</span>
        <?php endif; ?>
        <?php if (($targetUser['status'] ?? '') === 'active'): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">ACTIVE</span>
        <?php else: ?>
          <span class="badge bg-danger text-white px-2 py-1 fs-6">SUSPENDED</span>
        <?php endif; ?>
        <?php if (($targetUser['role'] ?? '') !== 'admin' && !empty($targetUser['subscription_expires_at'])): ?>
          <?php if (!empty($targetUser['is_expired'])): ?>
            <span class="badge bg-danger text-white px-2 py-1 fs-6"><i class="bi bi-x-circle me-1"></i> PLAN EXPIRED</span>
          <?php elseif (!empty($targetUser['is_expiring_soon'])): ?>
            <span class="badge bg-warning text-dark px-2 py-1 fs-6"><i class="bi bi-clock-history me-1"></i> EXPIRING (<?= htmlspecialchars((string)($targetUser['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d)</span>
          <?php else: ?>
            <span class="badge bg-success text-white px-2 py-1 fs-6"><i class="bi bi-check-circle me-1"></i> PLAN VALID (<?= htmlspecialchars((string)($targetUser['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d)</span>
          <?php endif; ?>
        <?php endif; ?>
      </h4>
      <div class="text-muted small">
        Subscriber ID: <strong class="font-monospace text-dark">#<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
        Phone (Login ID): <strong class="font-monospace text-dark"><?= htmlspecialchars($targetUser['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong> |
        Email: <strong class="text-dark"><?= htmlspecialchars($targetUser['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong> |
        Plan Validity: <strong class="font-monospace text-dark"><?= !empty($targetUser['subscription_expires_at']) ? date('d M Y', strtotime($targetUser['subscription_expires_at'])) : (($targetUser['role'] ?? '') === 'admin' ? 'Lifetime (Admin)' : 'Not Set') ?></strong> |
        Joined: <span class="font-monospace"><?= !empty($targetUser['created_at']) ? date('d M Y', strtotime($targetUser['created_at'])) : '--' ?></span>
      </div>
    </div>
  </div>

  <!-- Account Action Controls -->
  <?php if (($targetUser['id'] ?? 0) != ($user['id'] ?? 0)): ?>
    <div class="d-flex align-items-center gap-2">
      <?php if (($targetUser['role'] ?? '') !== 'admin'): ?>
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-semibold btn-renew-subscriber"
          data-id="<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          data-name="<?= htmlspecialchars($targetUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          data-phone="<?= htmlspecialchars($targetUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          data-expiry="<?= !empty($targetUser['subscription_expires_at']) ? date('d M Y', strtotime($targetUser['subscription_expires_at'])) : 'None' ?>">
          <i class="bi bi-arrow-repeat me-1"></i> Renew Plan (+1 Year)
        </button>
      <?php endif; ?>

      <form action="/admin/users/<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/status" method="POST" class="m-0">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-sm <?= ($targetUser['status'] ?? '') === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-pill px-3">
          <i class="bi <?= ($targetUser['status'] ?? '') === 'active' ? 'bi-lock-fill' : 'bi-unlock-fill' ?> me-1"></i>
          <?= ($targetUser['status'] ?? '') === 'active' ? 'Suspend Account' : 'Activate Account' ?>
        </button>
      </form>

      <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3" onclick="openResetPasswordModal('<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($targetUser['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
        <i class="bi bi-key-fill me-1"></i> Reset Password
      </button>
    </div>
  <?php endif; ?>
</div>

<!-- Subscriber Financial & Business Overview KPI Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3 bg-light rounded-3">
      <div class="text-muted small fw-bold text-uppercase">Total Billed Sales</div>
      <h3 class="fw-bold text-success mb-0 mt-1">₹ <?= number_format((float)($financials['total_sales'] ?? 0), 2) ?></h3>
      <small class="text-muted"><?= htmlspecialchars((string)($financials['total_invoices'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Total Records Logged</small>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3 bg-light rounded-3">
      <div class="text-muted small fw-bold text-uppercase">Total Purchases</div>
      <h3 class="fw-bold text-primary mb-0 mt-1">₹ <?= number_format((float)($financials['total_purchases'] ?? 0), 2) ?></h3>
      <small class="text-muted">Procurement Volume</small>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3 bg-light rounded-3">
      <div class="text-muted small fw-bold text-uppercase">Customer Outstanding (Due)</div>
      <h3 class="fw-bold text-danger mb-0 mt-1">₹ <?= number_format((float)($financials['total_due'] ?? 0), 2) ?></h3>
      <small class="text-muted">Receivable from parties</small>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3 bg-light rounded-3">
      <div class="d-flex justify-content-between align-items-start">
        <div class="text-muted small fw-bold text-uppercase">Storage Used (200 MB)</div>
        <?php
          $storageUsagePct = (float)($storage['totalUsedPercentage'] ?? 0);
          $isOverQuota = !empty($storage['isOverQuota']);
          $storageBadgeClass = $isOverQuota ? 'bg-danger' : ($storageUsagePct > 60 ? 'bg-warning text-dark' : 'bg-primary');
        ?>
        <span class="badge <?= $storageBadgeClass ?>">
          <?= htmlspecialchars((string)$storageUsagePct, ENT_QUOTES, 'UTF-8') ?>%
        </span>
      </div>
      <h3 class="fw-bold text-dark mb-1 mt-1 font-monospace"><?= htmlspecialchars((string)($storage['totalUsedMB'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?> <span class="fs-6 fw-normal text-muted">/ 200 MB</span></h3>
      <div class="progress mb-1" style="height: 6px;">
        <div class="progress-bar <?= $isOverQuota ? 'bg-danger' : ($storageUsagePct > 60 ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= max($storageUsagePct, 3) ?>%;"></div>
      </div>
      <small class="text-muted" style="font-size: 0.72rem;">
        <?= count($firms ?? []) <= 1 ? 'Single firm quota: 200 MB' : '2 Firms: 100 MB each (200 MB total)' ?>
      </small>
    </div>
  </div>
</div>

<!-- 360-Degree Deep Information Tab Navigation -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
  <div class="card-header bg-white border-bottom p-3">
    <ul class="nav nav-tabs card-header-tabs" id="subscriberTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold py-2 px-3 text-dark" id="firms-tab" data-bs-toggle="tab" data-bs-target="#firms-content" type="button" role="tab">
          <i class="bi bi-buildings-fill text-primary me-2"></i> Registered Firms (<?= count($firms ?? []) ?>)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2 px-3 text-dark" id="parties-tab" data-bs-toggle="tab" data-bs-target="#parties-content" type="button" role="tab">
          <i class="bi bi-people-fill text-success me-2"></i> Customers &amp; Parties (<?= count($parties ?? []) ?>)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2 px-3 text-dark" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-content" type="button" role="tab">
          <i class="bi bi-box-seam-fill text-warning me-2"></i> Inventory Items (<?= count($items ?? []) ?>)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2 px-3 text-dark" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-content" type="button" role="tab">
          <i class="bi bi-receipt-cutoff text-danger me-2"></i> All Business Records (<?= count($invoices ?? []) ?>)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2 px-3 text-dark" id="storage-tab" data-bs-toggle="tab" data-bs-target="#storage-content" type="button" role="tab">
          <i class="bi bi-hdd-fill text-info me-2"></i> Storage Allocation (<?= htmlspecialchars((string)($storage['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> / 200 MB)
        </button>
      </li>
    </ul>
  </div>

  <div class="card-body p-4">
    <div class="tab-content" id="subscriberTabContent">

      <!-- TAB 1: Registered Business Firms -->
      <div class="tab-pane fade show active" id="firms-content" role="tabpanel">
        <?php if (!empty($firms)): ?>
          <div class="row g-4">
            <?php foreach ($firms as $idx => $f): ?>
              <div class="col-lg-6">
                <div class="card border border-2 shadow-none h-100 rounded-3">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary">Firm #<?= $idx + 1 ?></span>
                      <strong class="text-dark fs-6"><?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($f['is_default'])): ?>
                        <span class="badge bg-secondary small">Default</span>
                      <?php endif; ?>
                    </div>
                    <span class="badge bg-white text-dark border font-monospace">ID: <?= htmlspecialchars((string)($f['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <div class="card-body p-3 small">
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <span class="text-muted d-block">GSTIN Number:</span>
                        <strong class="text-dark font-monospace"><?= htmlspecialchars($f['gstin'] ?? 'Non-GST', ENT_QUOTES, 'UTF-8') ?></strong>
                      </div>
                      <div class="col-sm-6">
                        <span class="text-muted d-block">PAN:</span>
                        <strong class="text-dark font-monospace"><?= htmlspecialchars($f['pan'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></strong>
                      </div>
                      <div class="col-sm-6">
                        <span class="text-muted d-block">State &amp; Code:</span>
                        <span class="text-dark"><?= !empty($f['state']) ? htmlspecialchars($f['state'] . ' (Code ' . ($f['state_code'] ?? '--') . ')', ENT_QUOTES, 'UTF-8') : 'None' ?></span>
                      </div>
                      <div class="col-sm-6">
                        <span class="text-muted d-block">Contact:</span>
                        <span class="text-dark"><?= htmlspecialchars($f['phone'] ?? '--', ENT_QUOTES, 'UTF-8') ?> <?= !empty($f['email']) ? '| ' . htmlspecialchars($f['email'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                      </div>
                      <div class="col-12">
                        <span class="text-muted d-block">Address:</span>
                        <span class="text-dark"><?= htmlspecialchars($f['address'] ?? '--', ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($f['city'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= !empty($f['pincode']) ? '- ' . htmlspecialchars($f['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                      </div>
                    </div>

                    <!-- Bank & UPI -->
                    <div class="p-2 bg-light rounded-2 mb-3">
                      <div class="fw-bold text-dark mb-1"><i class="bi bi-bank me-1"></i> Banking &amp; Payment Info:</div>
                      <div>Bank: <strong><?= htmlspecialchars($f['bank_name'] ?? 'Not Configured', ENT_QUOTES, 'UTF-8') ?></strong></div>
                      <div>Account #: <span class="font-monospace"><strong><?= htmlspecialchars($f['bank_account_no'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></strong></span> | IFSC: <span class="font-monospace"><?= htmlspecialchars($f['bank_ifsc'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></span></div>
                      <div>UPI ID: <span class="font-monospace text-primary"><strong><?= htmlspecialchars($f['upi_id'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></strong></span></div>
                    </div>

                    <!-- Images: Logo & Signature -->
                    <div class="row g-2 text-center pt-2 border-top">
                      <div class="col-6">
                        <span class="text-muted d-block small mb-1">Company Logo:</span>
                        <?php if (!empty($f['logo_path'])): ?>
                          <a href="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                            <img src="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="rounded border p-1 bg-white" style="max-height: 48px; max-width: 100px; object-fit: contain;">
                          </a>
                        <?php else: ?>
                          <span class="text-muted small fst-italic">No Logo Uploaded</span>
                        <?php endif; ?>
                      </div>
                      <div class="col-6">
                        <span class="text-muted d-block small mb-1">Digital Signature:</span>
                        <?php if (!empty($f['signature_path'])): ?>
                          <a href="<?= htmlspecialchars($f['signature_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                            <img src="<?= htmlspecialchars($f['signature_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Signature" class="rounded border p-1 bg-white" style="max-height: 48px; max-width: 100px; object-fit: contain;">
                          </a>
                        <?php else: ?>
                          <span class="text-muted small fst-italic">No Signature Uploaded</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <div class="card-footer bg-white py-2 px-3 d-flex justify-content-between small text-muted">
                    <span><?= htmlspecialchars((string)($f['items_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Items | <?= htmlspecialchars((string)($f['parties_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Parties</span>
                    <span>Turnover: <strong class="text-success">₹ <?= number_format((float)($f['total_turnover'] ?? 0), 0) ?></strong></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-buildings fs-1 d-block mb-2 text-secondary opacity-50"></i>
            This subscriber has not registered any business firms yet.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 2: Customers & Parties Directory -->
      <div class="tab-pane fade" id="parties-content" role="tabpanel">
        <?php if (!empty($parties)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-dark">
                <tr>
                  <th>Party Name</th>
                  <th>Firm</th>
                  <th>Type</th>
                  <th>Contact Info</th>
                  <th>GSTIN / PAN</th>
                  <th>State</th>
                  <th class="text-end">Balance (₹)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($parties as $p): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($p['billing_address'])): ?>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($p['billing_address'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                      <?php
                        $pType = $p['type'] ?? 'customer';
                        $pBadge = $pType === 'customer' ? 'bg-primary-subtle text-primary' : ($pType === 'supplier' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info');
                      ?>
                      <span class="badge <?= $pBadge ?>">
                        <?= strtoupper($pType) ?>
                      </span>
                    </td>
                    <td class="font-monospace">
                      <div><?= htmlspecialchars($p['phone'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-muted"><?= htmlspecialchars($p['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td class="font-monospace">
                      <div><?= htmlspecialchars($p['gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-muted"><?= htmlspecialchars($p['pan'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td><?= !empty($p['state']) ? htmlspecialchars($p['state'] . ' (' . ($p['state_code'] ?? '') . ')', ENT_QUOTES, 'UTF-8') : '--' ?></td>
                    <?php
                      $opBal = (float)($p['opening_balance'] ?? 0);
                      $balColor = $opBal > 0 ? 'text-success' : ($opBal < 0 ? 'text-danger' : 'text-muted');
                    ?>
                    <td class="text-end fw-bold font-monospace <?= $balColor ?>">
                      ₹ <?= number_format($opBal, 2) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
            No customers or parties registered by this subscriber.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 3: Inventory Items Catalog -->
      <div class="tab-pane fade" id="items-content" role="tabpanel">
        <?php if (!empty($items)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-dark">
                <tr>
                  <th>Item / Product Name</th>
                  <th>Firm</th>
                  <th>Item Code / HSN</th>
                  <th>Tax Rate</th>
                  <th class="text-end">Sale Price (₹)</th>
                  <th class="text-end">Purchase Price (₹)</th>
                  <th class="text-center">Current Stock</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $it): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($it['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($it['description'])): ?>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($it['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="font-monospace">
                      <div>Code: <?= htmlspecialchars($it['item_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-muted">HSN: <?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border"><?= htmlspecialchars((string)($it['tax_rate'] ?? 0), ENT_QUOTES, 'UTF-8') ?>%</span>
                      <small class="text-muted d-block"><?= !empty($it['tax_inclusive']) ? 'Incl.' : 'Excl.' ?></small>
                    </td>
                    <td class="text-end fw-bold font-monospace text-success">
                      ₹ <?= number_format((float)($it['sale_price'] ?? 0), 2) ?>
                    </td>
                    <td class="text-end font-monospace text-muted">
                      ₹ <?= number_format((float)($it['purchase_price'] ?? 0), 2) ?>
                    </td>
                    <td class="text-center font-monospace">
                      <?php
                        $currStock = (float)($it['current_stock'] ?? 0);
                        $lowStock = (float)($it['low_stock_threshold'] ?? 0);
                      ?>
                      <span class="badge <?= $currStock <= $lowStock ? 'bg-danger text-white' : 'bg-success-subtle text-success' ?> px-2 py-1">
                        <?= htmlspecialchars((string)$currStock, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary opacity-50"></i>
            No inventory items created by this subscriber.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 4: Records Ledger -->
      <div class="tab-pane fade" id="invoices-content" role="tabpanel">
        <?php if (!empty($invoices)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-dark">
                <tr>
                  <th>Record #</th>
                  <th>Firm</th>
                  <th>Party Details</th>
                  <th>Date</th>
                  <th class="text-center">Type</th>
                  <th class="text-end">Total Amount (₹)</th>
                  <th class="text-end">Balance Due (₹)</th>
                  <th class="text-center">Status</th>
                  <th class="text-end px-3">Inspect</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($invoices as $inv): ?>
                  <tr>
                    <td class="font-monospace fw-bold text-primary">
                      <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="text-decoration-none"><?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($inv['party_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($inv['party_phone'])): ?><div class="text-muted font-monospace" style="font-size: 0.75rem;"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </td>
                    <td class="text-muted font-monospace"><?= htmlspecialchars($inv['invoice_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-center">
                      <span class="badge <?= ($inv['type'] ?? '') === 'sale' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' ?>">
                        <?= strtoupper($inv['type'] ?? 'sale') ?>
                      </span>
                    </td>
                    <td class="text-end fw-bold font-monospace text-dark">
                      ₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?>
                    </td>
                    <td class="text-end font-monospace <?= (float)($inv['balance_due'] ?? 0) > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                      ₹ <?= number_format((float)($inv['balance_due'] ?? 0), 2) ?>
                    </td>
                    <td class="text-center">
                      <?php if (($inv['payment_status'] ?? '') === 'paid'): ?>
                        <span class="badge bg-success-subtle text-success px-2 py-1">PAID</span>
                      <?php elseif (($inv['payment_status'] ?? '') === 'partial'): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1">PARTIAL</span>
                      <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger px-2 py-1">UNPAID</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end px-3">
                      <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3" target="_blank" title="Inspect &amp; View Record">
                        <i class="bi bi-eye me-1"></i> Inspect
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary opacity-50"></i>
            No records logged by this subscriber.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 5: Storage Allocation & Footprint -->
      <div class="tab-pane fade" id="storage-content" role="tabpanel">
        <div class="row g-4 mb-4">
          <div class="col-lg-5">
            <div class="card border border-2 shadow-none rounded-3 h-100 p-3 bg-light">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-hdd-network-fill text-primary me-2"></i> User Storage Pool Overview</h6>
                <span class="badge bg-primary px-2 py-1">200 MB Allotted</span>
              </div>
              <div class="text-center py-3">
                <div class="display-5 fw-bold font-monospace text-dark mb-1">
                  <?= htmlspecialchars((string)($storage['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <span class="fs-4 text-muted">/ 200 MB</span>
                </div>
                <div class="text-muted small mb-3">Total Storage Consumed Across All Firms</div>
                <div class="progress mb-2 mx-auto" style="height: 10px; max-width: 320px;">
                  <div class="progress-bar <?= $isOverQuota ? 'bg-danger' : ($storageUsagePct > 60 ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= max($storageUsagePct, 4) ?>%;"></div>
                </div>
                <span class="badge bg-white text-dark border font-monospace px-3 py-1">
                  <?= htmlspecialchars((string)$storageUsagePct, ENT_QUOTES, 'UTF-8') ?>% of 200 MB Quota Utilized
                </span>
              </div>
              <div class="border-top pt-3 mt-2 small">
                <div class="d-flex justify-content-between py-1 border-bottom">
                  <span class="text-muted">Registered Firms:</span>
                  <strong><?= count($firms ?? []) ?> / 2 Firms</strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                  <span class="text-muted">Quota Strategy:</span>
                  <strong><?= count($firms ?? []) <= 1 ? '1 Firm (200 MB Allotted)' : '2 Firms (100 MB Each)' ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                  <span class="text-muted">Quota Status:</span>
                  <span class="fw-bold <?= $isOverQuota ? 'text-danger' : 'text-success' ?>">
                    <?= $isOverQuota ? 'OVER QUOTA LIMIT' : 'HEALTHY (&lt; 200 MB)' ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="card border border-2 shadow-none rounded-3 h-100 p-3">
              <h6 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-3-fill text-success me-2"></i> Per-Firm Storage Quota Allocation</h6>
              <?php if (!empty($storage['firmsStorage'])): ?>
                <div class="d-flex flex-column gap-3">
                  <?php foreach ($storage['firmsStorage'] as $fsInfo):
                    $fsUsedPct = (float)($fsInfo['usedPercentage'] ?? 0);
                    $fsBarClass = $fsUsedPct > 85 ? 'bg-danger' : ($fsUsedPct > 60 ? 'bg-warning' : 'bg-success');
                  ?>
                    <div class="p-3 border rounded-3 bg-white">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                          <strong class="text-dark"><?= htmlspecialchars($fsInfo['firmName'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                          <span class="badge bg-light text-secondary border ms-1 font-monospace">ID: <?= htmlspecialchars((string)($fsInfo['firmId'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle font-monospace">
                          <?= htmlspecialchars((string)($fsInfo['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB / <?= htmlspecialchars((string)($fsInfo['quotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB
                        </span>
                      </div>
                      <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar <?= $fsBarClass ?>" role="progressbar" style="width: <?= max($fsUsedPct, 3) ?>%;"></div>
                      </div>
                      <div class="row g-2 text-muted small mt-1" style="font-size: 0.75rem;">
                        <div class="col-sm-4">
                          <i class="bi bi-database me-1 text-primary"></i> DB Records: <strong><?= htmlspecialchars((string)($fsInfo['dbAllocatedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</strong>
                        </div>
                        <div class="col-sm-4">
                          <i class="bi bi-image me-1 text-success"></i> Media &amp; Logos: <strong><?= htmlspecialchars((string)($fsInfo['mediaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</strong>
                        </div>
                        <div class="col-sm-4">
                          <i class="bi bi-archive me-1 text-warning"></i> Backups: <strong><?= htmlspecialchars((string)($fsInfo['backupsMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</strong>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-center py-4 text-muted">
                  <i class="bi bi-hdd fs-2 d-block mb-2 text-secondary opacity-50"></i>
                  No business firms registered under this subscriber account.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal: Admin Reset Password -->
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

<!-- Modal: Renew / Extend Subscription -->
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

  document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
      const renewBtn = e.target.closest('.btn-renew-subscriber');
      if (renewBtn) {
        const { id, name, phone, expiry } = renewBtn.dataset;
        openRenewModal(id, name, phone, expiry);
      }
    });
  });
</script>
