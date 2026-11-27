<?php include __DIR__ . '/_nav.php'; ?>

<!-- Top Back Action & User Title -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
  <div class="flex flex-wrap items-start gap-3">
    <a href="/admin/users" class="rf-btn rf-btn-secondary rf-btn-sm shrink-0">
      <i class="bi bi-arrow-left"></i> All Subscribers
    </a>
    <div>
      <div class="flex flex-wrap items-center gap-2">
        <h2 class="text-xl font-semibold text-ink mb-0"><?= htmlspecialchars($targetUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (($targetUser['role'] ?? '') === 'admin'): ?>
          <span class="rf-badge rf-badge-amber">ADMIN</span>
        <?php else: ?>
          <span class="rf-badge rf-badge-slate">SUBSCRIBER</span>
        <?php endif; ?>
        <?php if (($targetUser['status'] ?? '') === 'active'): ?>
          <span class="rf-badge rf-badge-sage">ACTIVE</span>
        <?php else: ?>
          <span class="rf-badge rf-badge-rose">SUSPENDED</span>
        <?php endif; ?>
        <?php if (($targetUser['role'] ?? '') !== 'admin' && !empty($targetUser['subscription_expires_at'])): ?>
          <?php if (!empty($targetUser['is_expired'])): ?>
            <span class="rf-badge rf-badge-rose"><i class="bi bi-x-circle"></i> PLAN EXPIRED</span>
          <?php elseif (!empty($targetUser['is_expiring_soon'])): ?>
            <span class="rf-badge rf-badge-amber"><i class="bi bi-clock-history"></i> EXPIRING (<?= htmlspecialchars((string)($targetUser['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d)</span>
          <?php else: ?>
            <span class="rf-badge rf-badge-sage"><i class="bi bi-check-circle"></i> PLAN VALID (<?= htmlspecialchars((string)($targetUser['subscription_days_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?>d)</span>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <div class="text-ink-muted text-xs mt-1.5 flex flex-wrap items-center gap-x-2.5 gap-y-1">
        <span>Subscriber ID: <strong class="font-mono text-ink">#<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span class="text-border-strong">&bull;</span>
        <span>Phone: <strong class="font-mono text-ink"><?= htmlspecialchars($targetUser['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span class="text-border-strong">&bull;</span>
        <span>Email: <strong class="text-ink"><?= htmlspecialchars($targetUser['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span class="text-border-strong">&bull;</span>
        <span>Plan Validity: <strong class="font-mono text-ink"><?= !empty($targetUser['subscription_expires_at']) ? date('d M Y', strtotime($targetUser['subscription_expires_at'])) : (($targetUser['role'] ?? '') === 'admin' ? 'Lifetime (Admin)' : 'Not Set') ?></strong></span>
        <span class="text-border-strong">&bull;</span>
        <span>Joined: <span class="font-mono text-ink"><?= !empty($targetUser['created_at']) ? date('d M Y', strtotime($targetUser['created_at'])) : '--' ?></span></span>
      </div>
    </div>
  </div>

  <!-- Account Action Controls -->
  <?php if (($targetUser['id'] ?? 0) != ($user['id'] ?? 0)): ?>
    <div class="flex flex-wrap items-center gap-2 shrink-0">
      <?php if (($targetUser['role'] ?? '') !== 'admin'): ?>
        <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-sm btn-renew-subscriber"
          data-id="<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          data-name="<?= htmlspecialchars($targetUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          data-phone="<?= htmlspecialchars($targetUser['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          data-expiry="<?= !empty($targetUser['subscription_expires_at']) ? date('d M Y', strtotime($targetUser['subscription_expires_at'])) : 'None' ?>">
          <i class="bi bi-arrow-repeat"></i> Renew Plan (+1 Year)
        </button>
      <?php endif; ?>

      <form action="/admin/users/<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/status" method="POST" class="m-0">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="rf-btn rf-btn-sm <?= ($targetUser['status'] ?? '') === 'active' ? 'rf-btn-danger' : 'rf-btn-sage' ?>">
          <i class="bi <?= ($targetUser['status'] ?? '') === 'active' ? 'bi-lock' : 'bi-unlock' ?>"></i>
          <?= ($targetUser['status'] ?? '') === 'active' ? 'Suspend Account' : 'Activate Account' ?>
        </button>
      </form>

      <button type="button" class="rf-btn rf-btn-amber rf-btn-sm" onclick="openResetPasswordModal('<?= htmlspecialchars((string)($targetUser['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($targetUser['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')">
        <i class="bi bi-key"></i> Reset Password
      </button>
    </div>
  <?php endif; ?>
</div>

<!-- Subscriber Financial & Business Overview KPI Cards -->
<?php
  $storageUsagePct = (float)($storage['totalUsedPercentage'] ?? 0);
  $isOverQuota = !empty($storage['isOverQuota']);
  $storageBadgeClass = $isOverQuota ? 'rf-badge-rose' : ($storageUsagePct > 60 ? 'rf-badge-amber' : 'rf-badge-neutral');
  $storageBarClass = $isOverQuota ? 'bg-rose' : ($storageUsagePct > 60 ? 'bg-amber' : 'bg-sage');
?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="rf-card p-4">
    <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Total Billed Sales</div>
    <div class="text-2xl font-semibold font-mono text-sage mt-1">₹ <?= number_format((float)($financials['total_sales'] ?? 0), 2) ?></div>
    <div class="text-xs text-ink-muted mt-1"><?= htmlspecialchars((string)($financials['total_invoices'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Total Records Logged</div>
  </div>
  <div class="rf-card p-4">
    <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Total Purchases</div>
    <div class="text-2xl font-semibold font-mono text-ink mt-1">₹ <?= number_format((float)($financials['total_purchases'] ?? 0), 2) ?></div>
    <div class="text-xs text-ink-muted mt-1">Procurement Volume</div>
  </div>
  <div class="rf-card p-4">
    <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Customer Outstanding (Due)</div>
    <div class="text-2xl font-semibold font-mono text-rose mt-1">₹ <?= number_format((float)($financials['total_due'] ?? 0), 2) ?></div>
    <div class="text-xs text-ink-muted mt-1">Receivable from parties</div>
  </div>
  <div class="rf-card p-4">
    <div class="flex items-center justify-between gap-2">
      <span class="text-[11px] font-medium uppercase tracking-wider text-ink-muted">Storage Used (200 MB)</span>
      <span class="rf-badge <?= $storageBadgeClass ?> font-mono"><?= htmlspecialchars((string)$storageUsagePct, ENT_QUOTES, 'UTF-8') ?>%</span>
    </div>
    <div class="text-2xl font-semibold font-mono text-ink mt-1">
      <?= htmlspecialchars((string)($storage['totalUsedMB'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>
      <span class="text-xs font-normal text-ink-muted">/ 200 MB</span>
    </div>
    <div class="w-full h-1.5 rounded-full bg-canvas-alt overflow-hidden mt-2 mb-1">
      <div class="h-full rounded-full <?= $storageBarClass ?>" style="width: <?= min(100, max($storageUsagePct, 3)) ?>%;"></div>
    </div>
    <div class="text-[11px] text-ink-muted">
      <?= count($firms ?? []) <= 1 ? 'Single firm quota: 200 MB' : '2 Firms: 100 MB each (200 MB total)' ?>
    </div>
  </div>
</div>

<!-- 360-Degree Deep Information Tab Navigation -->
<div class="rf-card overflow-hidden mb-6">
  <div class="px-5 pt-4 border-b border-border overflow-x-auto">
    <div class="rf-tabs border-b-0" id="subscriberTab" data-ui-tabs data-ui-tabs-target="#subscriberTabContent">
      <button class="rf-tab-btn active" id="firms-tab" data-ui-tab="#firms-content" type="button">
        <i class="bi bi-buildings"></i> Registered Firms (<?= count($firms ?? []) ?>)
      </button>
      <button class="rf-tab-btn" id="parties-tab" data-ui-tab="#parties-content" type="button">
        <i class="bi bi-people"></i> Customers &amp; Parties (<?= count($parties ?? []) ?>)
      </button>
      <button class="rf-tab-btn" id="items-tab" data-ui-tab="#items-content" type="button">
        <i class="bi bi-box-seam"></i> Inventory Items (<?= count($items ?? []) ?>)
      </button>
      <button class="rf-tab-btn" id="invoices-tab" data-ui-tab="#invoices-content" type="button">
        <i class="bi bi-receipt"></i> All Business Records (<?= count($invoices ?? []) ?>)
      </button>
      <button class="rf-tab-btn" id="storage-tab" data-ui-tab="#storage-content" type="button">
        <i class="bi bi-hdd"></i> Storage Allocation (<?= htmlspecialchars((string)($storage['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> / 200 MB)
      </button>
    </div>
  </div>

  <div class="rf-card-body p-5 md:p-6">
    <div id="subscriberTabContent">

      <!-- TAB 1: Registered Business Firms -->
      <div id="firms-content" data-ui-tab-panel>
        <?php if (!empty($firms)): ?>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <?php foreach ($firms as $idx => $f): ?>
              <div class="rf-card-muted flex flex-col h-full">
                <div class="px-4 py-3 border-b border-border flex items-center justify-between gap-2 bg-surface/60 rounded-t-xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="rf-badge rf-badge-neutral">Firm #<?= $idx + 1 ?></span>
                    <strong class="text-ink text-sm"><?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if (!empty($f['is_default'])): ?>
                      <span class="rf-badge rf-badge-sage text-[10px]">Default</span>
                    <?php endif; ?>
                  </div>
                  <span class="rf-badge rf-badge-neutral font-mono">ID: <?= htmlspecialchars((string)($f['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="p-4 text-xs space-y-4 flex-1">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                      <span class="text-ink-muted block mb-0.5">GSTIN Number</span>
                      <strong class="text-ink font-mono"><?= htmlspecialchars($f['gstin'] ?? 'Non-GST', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div>
                      <span class="text-ink-muted block mb-0.5">PAN</span>
                      <strong class="text-ink font-mono"><?= htmlspecialchars($f['pan'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div>
                      <span class="text-ink-muted block mb-0.5">State &amp; Code</span>
                      <span class="text-ink"><?= !empty($f['state']) ? htmlspecialchars($f['state'] . ' (Code ' . ($f['state_code'] ?? '--') . ')', ENT_QUOTES, 'UTF-8') : 'None' ?></span>
                    </div>
                    <div>
                      <span class="text-ink-muted block mb-0.5">Contact</span>
                      <span class="text-ink"><?= htmlspecialchars($f['phone'] ?? '--', ENT_QUOTES, 'UTF-8') ?> <?= !empty($f['email']) ? '| ' . htmlspecialchars($f['email'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                    </div>
                    <div class="sm:col-span-2">
                      <span class="text-ink-muted block mb-0.5">Address</span>
                      <span class="text-ink"><?= htmlspecialchars($f['address'] ?? '--', ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($f['city'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= !empty($f['pincode']) ? '- ' . htmlspecialchars($f['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                    </div>
                  </div>

                  <!-- Bank & UPI -->
                  <div class="rounded-lg bg-surface border border-border-subtle p-3 space-y-1">
                    <div class="font-semibold text-ink mb-1 flex items-center gap-1.5">
                      <i class="bi bi-bank text-ink-muted"></i> Banking &amp; Payment Info
                    </div>
                    <div class="text-ink-muted">Bank: <strong class="text-ink"><?= htmlspecialchars($f['bank_name'] ?? 'Not Configured', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="text-ink-muted">Account #: <strong class="font-mono text-ink"><?= htmlspecialchars($f['bank_account_no'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></strong> | IFSC: <span class="font-mono text-ink"><?= htmlspecialchars($f['bank_ifsc'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="text-ink-muted">UPI ID: <strong class="font-mono text-ink"><?= htmlspecialchars($f['upi_id'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></strong></div>
                  </div>

                  <!-- Images: Logo & Signature -->
                  <div class="grid grid-cols-2 gap-3 text-center pt-3 border-t border-border-subtle">
                    <div>
                      <span class="text-ink-muted block text-[11px] mb-1.5">Company Logo</span>
                      <?php if (!empty($f['logo_path'])): ?>
                        <a href="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="inline-block">
                          <img src="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="rounded border border-border p-1 bg-surface max-h-12 max-w-[100px] object-contain mx-auto">
                        </a>
                      <?php else: ?>
                        <span class="text-ink-subtle italic">No Logo Uploaded</span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <span class="text-ink-muted block text-[11px] mb-1.5">Digital Signature</span>
                      <?php if (!empty($f['signature_path'])): ?>
                        <a href="<?= htmlspecialchars($f['signature_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="inline-block">
                          <img src="<?= htmlspecialchars($f['signature_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Signature" class="rounded border border-border p-1 bg-surface max-h-12 max-w-[100px] object-contain mx-auto">
                        </a>
                      <?php else: ?>
                        <span class="text-ink-subtle italic">No Signature Uploaded</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="px-4 py-2.5 bg-surface/70 border-t border-border rounded-b-xl flex items-center justify-between text-xs text-ink-muted">
                  <span><?= htmlspecialchars((string)($f['items_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Items &bull; <?= htmlspecialchars((string)($f['parties_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Parties</span>
                  <span>Turnover: <strong class="font-mono text-sage">₹ <?= number_format((float)($f['total_turnover'] ?? 0), 0) ?></strong></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-12 text-ink-muted">
            <i class="bi bi-buildings text-3xl block mb-2 text-ink-subtle"></i>
            This subscriber has not registered any business firms yet.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 2: Customers & Parties Directory -->
      <div class="hidden" id="parties-content" data-ui-tab-panel>
        <?php if (!empty($parties)): ?>
          <div class="rf-table-wrap">
            <table class="rf-table">
              <thead>
                <tr>
                  <th>Party Name</th>
                  <th>Firm</th>
                  <th>Type</th>
                  <th>Contact Info</th>
                  <th>GSTIN / PAN</th>
                  <th>State</th>
                  <th class="text-right">Balance (₹)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($parties as $p): ?>
                  <tr>
                    <td>
                      <strong class="text-ink"><?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($p['billing_address'])): ?>
                        <div class="text-ink-muted text-xs mt-0.5"><?= htmlspecialchars($p['billing_address'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php endif; ?>
                    </td>
                    <td><span class="rf-badge rf-badge-neutral"><?= htmlspecialchars($p['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                      <?php
                        $pType = $p['type'] ?? 'customer';
                        $pBadge = $pType === 'customer' ? 'rf-badge-slate' : ($pType === 'supplier' ? 'rf-badge-amber' : 'rf-badge-neutral');
                      ?>
                      <span class="rf-badge <?= $pBadge ?>"><?= strtoupper($pType) ?></span>
                    </td>
                    <td class="font-mono text-xs">
                      <div><?= htmlspecialchars($p['phone'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-ink-muted"><?= htmlspecialchars($p['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td class="font-mono text-xs">
                      <div><?= htmlspecialchars($p['gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-ink-muted"><?= htmlspecialchars($p['pan'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td class="text-xs"><?= !empty($p['state']) ? htmlspecialchars($p['state'] . ' (' . ($p['state_code'] ?? '') . ')', ENT_QUOTES, 'UTF-8') : '--' ?></td>
                    <?php
                      $opBal = (float)($p['opening_balance'] ?? 0);
                      $balColor = $opBal > 0 ? 'text-sage' : ($opBal < 0 ? 'text-rose' : 'text-ink-muted');
                    ?>
                    <td class="text-right font-semibold font-mono <?= $balColor ?> whitespace-nowrap">
                      ₹ <?= number_format($opBal, 2) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-12 text-ink-muted">
            <i class="bi bi-people text-3xl block mb-2 text-ink-subtle"></i>
            No customers or parties registered by this subscriber.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 3: Inventory Items Catalog -->
      <div class="hidden" id="items-content" data-ui-tab-panel>
        <?php if (!empty($items)): ?>
          <div class="rf-table-wrap">
            <table class="rf-table">
              <thead>
                <tr>
                  <th>Item / Product Name</th>
                  <th>Firm</th>
                  <th>Item Code / HSN</th>
                  <th>Tax Rate</th>
                  <th class="text-right">Sale Price (₹)</th>
                  <th class="text-right">Purchase Price (₹)</th>
                  <th class="text-center">Current Stock</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $it): ?>
                  <tr>
                    <td>
                      <strong class="text-ink"><?= htmlspecialchars($it['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($it['description'])): ?>
                        <div class="text-ink-muted text-xs mt-0.5"><?= htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php endif; ?>
                    </td>
                    <td><span class="rf-badge rf-badge-neutral"><?= htmlspecialchars($it['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="font-mono text-xs">
                      <div>Code: <?= htmlspecialchars($it['item_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="text-ink-muted">HSN: <?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td>
                      <span class="rf-badge rf-badge-neutral font-mono"><?= htmlspecialchars((string)($it['tax_rate'] ?? 0), ENT_QUOTES, 'UTF-8') ?>%</span>
                      <div class="text-ink-muted text-[11px] mt-0.5"><?= !empty($it['tax_inclusive']) ? 'Incl.' : 'Excl.' ?></div>
                    </td>
                    <td class="text-right font-semibold font-mono text-sage whitespace-nowrap">
                      ₹ <?= number_format((float)($it['sale_price'] ?? 0), 2) ?>
                    </td>
                    <td class="text-right font-mono text-ink-muted whitespace-nowrap">
                      ₹ <?= number_format((float)($it['purchase_price'] ?? 0), 2) ?>
                    </td>
                    <td class="text-center font-mono">
                      <?php
                        $currStock = (float)($it['current_stock'] ?? 0);
                        $lowStock = (float)($it['low_stock_threshold'] ?? 0);
                      ?>
                      <span class="rf-badge <?= $currStock <= $lowStock ? 'rf-badge-rose' : 'rf-badge-sage' ?>">
                        <?= htmlspecialchars((string)$currStock, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-12 text-ink-muted">
            <i class="bi bi-box-seam text-3xl block mb-2 text-ink-subtle"></i>
            No inventory items created by this subscriber.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 4: Records Ledger -->
      <div class="hidden" id="invoices-content" data-ui-tab-panel>
        <?php if (!empty($invoices)): ?>
          <div class="rf-table-wrap">
            <table class="rf-table">
              <thead>
                <tr>
                  <th>Record #</th>
                  <th>Firm</th>
                  <th>Party Details</th>
                  <th>Date</th>
                  <th class="text-center">Type</th>
                  <th class="text-right">Total Amount (₹)</th>
                  <th class="text-right">Balance Due (₹)</th>
                  <th class="text-center">Status</th>
                  <th class="text-right">Inspect</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($invoices as $inv): ?>
                  <tr>
                    <td class="font-mono font-semibold text-ink whitespace-nowrap">
                      <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="hover:underline">
                        <?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </td>
                    <td><span class="rf-badge rf-badge-neutral"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                      <strong class="text-ink"><?= htmlspecialchars($inv['party_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($inv['party_phone'])): ?><div class="text-ink-muted font-mono text-xs"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </td>
                    <td class="text-ink-muted font-mono text-xs whitespace-nowrap"><?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-center">
                      <span class="rf-badge <?= ($inv['type'] ?? '') === 'sale' ? 'rf-badge-sage' : 'rf-badge-slate' ?>">
                        <?= strtoupper($inv['type'] ?? 'sale') ?>
                      </span>
                    </td>
                    <td class="text-right font-semibold font-mono text-ink whitespace-nowrap">
                      ₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?>
                    </td>
                    <td class="text-right font-mono whitespace-nowrap <?= (float)($inv['balance_due'] ?? 0) > 0 ? 'text-rose font-semibold' : 'text-ink-muted' ?>">
                      ₹ <?= number_format((float)($inv['balance_due'] ?? 0), 2) ?>
                    </td>
                    <td class="text-center">
                      <?php if (($inv['payment_status'] ?? '') === 'paid'): ?>
                        <span class="rf-badge rf-badge-sage">PAID</span>
                      <?php elseif (($inv['payment_status'] ?? '') === 'partial'): ?>
                        <span class="rf-badge rf-badge-amber">PARTIAL</span>
                      <?php else: ?>
                        <span class="rf-badge rf-badge-rose">UNPAID</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-right">
                      <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm" target="_blank" title="Inspect &amp; View Record">
                        <i class="bi bi-eye"></i> Inspect
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-12 text-ink-muted">
            <i class="bi bi-receipt text-3xl block mb-2 text-ink-subtle"></i>
            No records logged by this subscriber.
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB 5: Storage Allocation & Footprint -->
      <div class="hidden" id="storage-content" data-ui-tab-panel>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
          <div class="lg:col-span-5">
            <div class="rf-card-muted p-5 h-full flex flex-col justify-between">
              <div class="flex items-center justify-between gap-2 mb-4">
                <h6 class="text-sm font-semibold text-ink mb-0 flex items-center gap-2">
                  <i class="bi bi-hdd-network text-ink-muted"></i> User Storage Pool Overview
                </h6>
                <span class="rf-badge rf-badge-neutral font-mono">200 MB Allotted</span>
              </div>
              <div class="text-center py-4">
                <div class="text-4xl font-semibold font-mono text-ink mb-1">
                  <?= htmlspecialchars((string)($storage['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
                  <span class="text-lg font-normal text-ink-muted">/ 200 MB</span>
                </div>
                <div class="text-ink-muted text-xs mb-3">Total Storage Consumed Across All Firms</div>
                <div class="w-full max-w-xs h-2.5 rounded-full bg-surface border border-border-subtle overflow-hidden mx-auto mb-3">
                  <div class="h-full rounded-full <?= $storageBarClass ?>" style="width: <?= min(100, max($storageUsagePct, 4)) ?>%;"></div>
                </div>
                <span class="rf-badge rf-badge-neutral font-mono">
                  <?= htmlspecialchars((string)$storageUsagePct, ENT_QUOTES, 'UTF-8') ?>% of 200 MB Quota Utilized
                </span>
              </div>
              <div class="border-t border-border pt-3 mt-3 text-xs space-y-2">
                <div class="flex items-center justify-between py-1 border-b border-border-subtle">
                  <span class="text-ink-muted">Registered Firms</span>
                  <strong class="text-ink"><?= count($firms ?? []) ?> / 2 Firms</strong>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-border-subtle">
                  <span class="text-ink-muted">Quota Strategy</span>
                  <strong class="text-ink"><?= count($firms ?? []) <= 1 ? '1 Firm (200 MB Allotted)' : '2 Firms (100 MB Each)' ?></strong>
                </div>
                <div class="flex items-center justify-between py-1">
                  <span class="text-ink-muted">Quota Status</span>
                  <span class="font-semibold <?= $isOverQuota ? 'text-rose' : 'text-sage' ?>">
                    <?= $isOverQuota ? 'OVER QUOTA LIMIT' : 'HEALTHY (&lt; 200 MB)' ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="lg:col-span-7">
            <div class="rf-card p-5 h-full">
              <h6 class="text-sm font-semibold text-ink mb-4 flex items-center gap-2">
                <i class="bi bi-diagram-3 text-ink-muted"></i> Per-Firm Storage Quota Allocation
              </h6>
              <?php if (!empty($storage['firmsStorage'])): ?>
                <div class="space-y-3">
                  <?php foreach ($storage['firmsStorage'] as $fsInfo):
                    $fsUsedPct = (float)($fsInfo['usedPercentage'] ?? 0);
                    $fsBarClass = $fsUsedPct > 85 ? 'bg-rose' : ($fsUsedPct > 60 ? 'bg-amber' : 'bg-sage');
                  ?>
                    <div class="p-4 rounded-lg border border-border bg-canvas/40">
                      <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                          <strong class="text-ink text-sm"><?= htmlspecialchars($fsInfo['firmName'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                          <span class="rf-badge rf-badge-neutral font-mono">ID: <?= htmlspecialchars((string)($fsInfo['firmId'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <span class="rf-badge rf-badge-slate font-mono">
                          <?= htmlspecialchars((string)($fsInfo['totalUsedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB / <?= htmlspecialchars((string)($fsInfo['quotaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB
                        </span>
                      </div>
                      <div class="w-full h-1.5 rounded-full bg-canvas-alt overflow-hidden mb-3">
                        <div class="h-full rounded-full <?= $fsBarClass ?>" style="width: <?= min(100, max($fsUsedPct, 3)) ?>%;"></div>
                      </div>
                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-ink-muted text-xs">
                        <div>
                          <i class="bi bi-database mr-1"></i> DB Records: <strong class="text-ink font-mono"><?= htmlspecialchars((string)($fsInfo['dbAllocatedMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</strong>
                        </div>
                        <div>
                          <i class="bi bi-image mr-1"></i> Media &amp; Logos: <strong class="text-ink font-mono"><?= htmlspecialchars((string)($fsInfo['mediaMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</strong>
                        </div>
                        <div>
                          <i class="bi bi-archive mr-1"></i> Backups: <strong class="text-ink font-mono"><?= htmlspecialchars((string)($fsInfo['backupsMB'] ?? 0), ENT_QUOTES, 'UTF-8') ?> MB</strong>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-center py-8 text-ink-muted">
                  <i class="bi bi-hdd text-2xl block mb-2 text-ink-subtle"></i>
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

<!-- Modal: Renew / Extend Subscription -->
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
