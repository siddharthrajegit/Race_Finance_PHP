<?php
/**
 * Admin Invoice Inspect View
 * Read-only detailed inspection of any subscriber's invoice.
 * RACE FINANCE - Billing & Inventory System
 */

include __DIR__ . '/_nav.php';

$inv     = $invoice;
$isGst   = !empty($inv['is_gst_bill']);
$isPurch = ($inv['type'] ?? 'sale') === 'purchase';
$items   = $inv['items'] ?? [];
$due     = (float)($inv['balance_due'] ?? 0);
$status  = $inv['payment_status'] ?? 'unpaid';

$statusClass = match($status) {
    'paid'    => 'bg-success-subtle text-success border border-success-subtle',
    'partial' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
    default   => 'bg-danger-subtle text-danger border border-danger-subtle',
};
$statusLabel = strtoupper($status);
?>

<!-- Top Toolbar -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <a href="/admin/invoices" class="text-decoration-none text-muted small">
      &larr; Back to Platform Records
    </a>
    <h4 class="fw-bold mb-0 mt-1">
      <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>
      Invoice Inspector
      <span class="font-monospace text-primary fs-5"><?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </h4>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="badge <?= $statusClass ?> px-3 py-2 fs-6"><?= $statusLabel ?></span>
    <span class="badge bg-<?= $isPurch ? 'warning text-dark' : 'success' ?> px-3 py-2 fs-6">
      <?= $isPurch ? 'PURCHASE' : 'SALE' ?>
    </span>
    <?php if ($isGst): ?>
      <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">GST Bill</span>
    <?php endif; ?>
  </div>
</div>

<!-- Subscriber & Firm Info Banner -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
  <div class="card-body p-3">
    <div class="row g-3 align-items-center">
      <div class="col-sm-auto">
        <span class="badge bg-warning text-dark px-2 py-1 rounded-2">
          <i class="bi bi-shield-lock-fill me-1"></i> ADMIN VIEW
        </span>
      </div>
      <div class="col">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="small text-muted fw-semibold text-uppercase">Subscriber</div>
            <div class="fw-bold text-dark"><?= htmlspecialchars($inv['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="small text-muted">
              <?= htmlspecialchars($inv['user_phone'] ?? ($inv['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
              &bull;
              <span class="badge bg-<?= ($inv['user_status'] ?? '') === 'active' ? 'success' : 'danger' ?>-subtle text-<?= ($inv['user_status'] ?? '') === 'active' ? 'success' : 'danger' ?> border">
                <?= strtoupper($inv['user_status'] ?? 'unknown') ?>
              </span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="small text-muted fw-semibold text-uppercase">Business Firm</div>
            <div class="fw-bold text-dark"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($inv['firm_gstin'])): ?>
              <div class="small text-muted font-monospace">GSTIN: <?= htmlspecialchars($inv['firm_gstin'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
          </div>
          <div class="col-md-4">
            <div class="small text-muted fw-semibold text-uppercase">Subscriber Profile</div>
            <a href="/admin/users/<?= (int)($inv['user_id'] ?? 0) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 mt-1">
              <i class="bi bi-person-badge me-1"></i> View Subscriber
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Invoice Card -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
  <div class="card-body p-4 p-md-5">

    <!-- Header: Firm & Invoice Meta -->
    <div class="row border-bottom pb-4 mb-4">
      <div class="col-md-7">
        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h5>
        <?php if (!empty($inv['firm_gstin'])): ?>
          <div class="small text-muted font-monospace mb-1">GSTIN: <strong><?= htmlspecialchars($inv['firm_gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div>
        <?php endif; ?>
        <div class="small text-muted">
          <?php if (!empty($inv['firm_address'])): ?>
            <div><?= htmlspecialchars($inv['firm_address'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php
            $loc = array_filter([$inv['firm_city'] ?? '', $inv['firm_state'] ?? '']);
            if (!empty($loc)):
          ?>
            <div>
              <?= htmlspecialchars(implode(', ', $loc), ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($inv['firm_pincode'])): ?>
                - <?= htmlspecialchars($inv['firm_pincode'], ENT_QUOTES, 'UTF-8') ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($inv['firm_phone'])): ?><div>Phone: <?= htmlspecialchars($inv['firm_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($inv['firm_email'])): ?><div>Email: <?= htmlspecialchars($inv['firm_email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>
      </div>

      <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <h5 class="fw-bold text-uppercase text-primary mb-2">
          <?= $isGst ? ($isPurch ? 'GST Purchase Record' : 'GST Sales Record') : ($isPurch ? 'Purchase Record' : 'Sales Record') ?>
        </h5>
        <div class="small">
          <div><strong>Record No:</strong> <span class="font-monospace"><?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
          <div><strong>Date:</strong> <?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($inv['due_date'])): ?>
            <div><strong>Due Date:</strong> <?= htmlspecialchars(formatDate($inv['due_date']), ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div><strong>Payment Mode:</strong> <?= strtoupper(htmlspecialchars($inv['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></div>
          <div class="mt-2">
            <strong>Status:</strong>
            <span class="badge <?= $statusClass ?> px-2 py-1"><?= $statusLabel ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Party Section -->
    <div class="row border-bottom pb-4 mb-4">
      <div class="col-12">
        <h6 class="fw-bold text-uppercase text-secondary small mb-2">
          <?= $isPurch ? 'Party (Supplier / Vendor)' : 'Party (Customer / Client)' ?>
        </h6>
        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="small text-muted">
          <?php if (!empty($inv['party_phone'])): ?>
            <div>Phone: <?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php if (!empty($inv['party_gstin'])): ?>
            <div>GSTIN: <span class="font-monospace fw-medium"><?= htmlspecialchars($inv['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div>
          <?php endif; ?>
          <?php if (!empty($inv['party_address'])): ?>
            <div>Address: <?= htmlspecialchars($inv['party_address'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php if (!empty($inv['party_state'])): ?>
            <div>State: <?= htmlspecialchars($inv['party_state'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($inv['party_state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Line Items Table -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th class="text-center" style="width: 40px;">#</th>
            <th>Item Description</th>
            <th class="text-center">HSN/SAC</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Rate (₹)</th>
            <?php if ($isGst): ?>
              <th class="text-center">Disc %</th>
              <th class="text-center">GST %</th>
              <th class="text-end">Tax (₹)</th>
            <?php else: ?>
              <th class="text-center">Disc %</th>
            <?php endif; ?>
            <th class="text-end">Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): ?>
              <tr>
                <td class="text-center text-muted small"><?= $index + 1 ?></td>
                <td>
                  <div class="fw-semibold text-dark"><?= htmlspecialchars($item['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($item['description'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center font-monospace small"><?= htmlspecialchars($item['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <strong><?= number_format((float)($item['quantity'] ?? 0), 2) ?></strong>
                  <span class="text-muted small"><?= htmlspecialchars($item['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="text-end">₹ <?= number_format((float)($item['rate'] ?? 0), 2) ?></td>
                <?php if ($isGst): ?>
                  <td class="text-center small text-muted">
                    <?= (float)($item['discount_percent'] ?? 0) > 0 ? number_format((float)$item['discount_percent'], 1) . '%' : '—' ?>
                  </td>
                  <td class="text-center small">
                    <?= ((float)($item['tax_rate'] ?? 0) == (int)($item['tax_rate'] ?? 0)) ? number_format((float)($item['tax_rate'] ?? 0), 0) : (float)($item['tax_rate'] ?? 0) ?>%
                  </td>
                  <td class="text-end text-muted small">
                    ₹ <?= number_format((float)($item['cgst_amount'] ?? 0) + (float)($item['sgst_amount'] ?? 0) + (float)($item['igst_amount'] ?? 0), 2) ?>
                  </td>
                <?php else: ?>
                  <td class="text-center small text-muted">
                    <?= (float)($item['discount_percent'] ?? 0) > 0 ? number_format((float)$item['discount_percent'], 1) . '%' : '—' ?>
                  </td>
                <?php endif; ?>
                <td class="text-end fw-bold">₹ <?= number_format((float)($item['total_amount'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= $isGst ? 9 : 7 ?>" class="text-center text-muted py-4">No line items found for this invoice.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Totals & Notes -->
    <div class="row g-4">
      <div class="col-md-6">
        <?php if (!empty($inv['notes'])): ?>
          <div class="mb-3">
            <h6 class="fw-bold small text-uppercase text-muted mb-1">Notes / Remarks</h6>
            <p class="small text-muted mb-0"><?= htmlspecialchars($inv['notes'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php endif; ?>
        <?php if (!empty($inv['terms'])): ?>
          <div class="mb-3">
            <h6 class="fw-bold small text-uppercase text-muted mb-1">Terms &amp; Conditions</h6>
            <div class="small text-muted" style="white-space: pre-line;"><?= htmlspecialchars($inv['terms'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <!-- GST Breakdown (if GST bill) -->
        <?php if ($isGst && (float)($inv['tax_amount'] ?? 0) > 0): ?>
          <div class="mt-3">
            <h6 class="fw-bold small text-uppercase text-muted mb-2">GST Breakdown</h6>
            <table class="table table-sm table-bordered text-center" style="max-width: 360px;">
              <thead class="table-light">
                <tr>
                  <th>Tax Head</th>
                  <th>Rate</th>
                  <th>Amount (₹)</th>
                </tr>
              </thead>
              <tbody class="small">
                <?php if (!empty($inv['is_interstate'])): ?>
                  <tr>
                    <td>IGST</td>
                    <td>—</td>
                    <td>₹ <?= number_format((float)($inv['igst_amount'] ?? 0), 2) ?></td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td>CGST</td>
                    <td>—</td>
                    <td>₹ <?= number_format((float)($inv['cgst_amount'] ?? 0), 2) ?></td>
                  </tr>
                  <tr>
                    <td>SGST</td>
                    <td>—</td>
                    <td>₹ <?= number_format((float)($inv['sgst_amount'] ?? 0), 2) ?></td>
                  </tr>
                <?php endif; ?>
                <tr class="fw-bold table-light">
                  <td colspan="2">Total GST</td>
                  <td>₹ <?= number_format((float)($inv['tax_amount'] ?? 0), 2) ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <div class="alert alert-warning border small mt-3 mb-0" style="border-radius: 10px;">
          <i class="bi bi-shield-exclamation me-1"></i>
          <strong>Admin Read-Only Access:</strong> This invoice belongs to a subscriber. No edits or deletions can be made from the admin panel. This access is logged.
        </div>
      </div>

      <div class="col-md-6">
        <div class="card bg-light border-0 p-3" style="border-radius: 10px;">
          <div class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted small">Subtotal:</span>
            <span class="fw-medium">₹ <?= number_format((float)($inv['subtotal'] ?? 0), 2) ?></span>
          </div>

          <?php if ((float)($inv['discount_amount'] ?? 0) > 0): ?>
            <div class="d-flex justify-content-between py-1 border-bottom text-danger">
              <span class="small">
                Discount
                <?= ($inv['discount_type'] ?? '') === 'percentage' ? '(' . number_format((float)($inv['discount_value'] ?? 0), 1) . '%)' : '' ?>:
              </span>
              <span>- ₹ <?= number_format((float)$inv['discount_amount'], 2) ?></span>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted small">Taxable Amount:</span>
            <span class="fw-medium">₹ <?= number_format((float)($inv['taxable_amount'] ?? 0), 2) ?></span>
          </div>

          <?php if ($isGst && (float)($inv['tax_amount'] ?? 0) > 0): ?>
            <?php if (!empty($inv['is_interstate'])): ?>
              <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
                <span>IGST:</span>
                <span>₹ <?= number_format((float)($inv['igst_amount'] ?? 0), 2) ?></span>
              </div>
            <?php else: ?>
              <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
                <span>CGST:</span>
                <span>₹ <?= number_format((float)($inv['cgst_amount'] ?? 0), 2) ?></span>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
                <span>SGST:</span>
                <span>₹ <?= number_format((float)($inv['sgst_amount'] ?? 0), 2) ?></span>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (abs((float)($inv['round_off'] ?? 0)) > 0): ?>
            <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
              <span>Round Off:</span>
              <span><?= (float)($inv['round_off'] ?? 0) >= 0 ? '+' : '' ?>₹ <?= number_format((float)($inv['round_off'] ?? 0), 2) ?></span>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="fw-bold fs-6">Grand Total:</span>
            <span class="fw-bold fs-5 text-primary">₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?></span>
          </div>

          <div class="d-flex justify-content-between py-1">
            <span class="text-success small fw-semibold">Amount Paid:</span>
            <span class="fw-semibold text-success">₹ <?= number_format((float)($inv['paid_amount'] ?? 0), 2) ?></span>
          </div>

          <div class="d-flex justify-content-between py-1">
            <span class="text-danger small fw-semibold">Balance Due:</span>
            <span class="fw-bold text-danger">₹ <?= number_format($due, 2) ?></span>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.card-body -->
</div><!-- /.card -->

<!-- Footer Actions -->
<div class="d-flex justify-content-between align-items-center mt-2 mb-5">
  <a href="/admin/invoices" class="btn btn-outline-secondary rounded-pill px-4">
    <i class="bi bi-arrow-left me-1"></i> Back to Platform Records
  </a>
  <a href="/admin/users/<?= (int)($inv['user_id'] ?? 0) ?>" class="btn btn-outline-primary rounded-pill px-4">
    <i class="bi bi-person-badge me-1"></i> View Subscriber Profile
  </a>
</div>
