<?php
$invType = $invoice['type'] ?? 'sale';
$isPurchase = $invType === 'purchase';
$isGst = !empty($invoice['is_gst_bill']);
$items = !empty($invoice['items']) ? $invoice['items'] : [];
$due = (float)($invoice['balance_due'] ?? 0);
?>
<!-- Top Action Toolbar -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 no-print">
  <div>
    <a href="<?= $isPurchase ? '/purchases' : '/sales' ?>" class="text-decoration-none text-muted small">&larr; Back to <?= $isPurchase ? 'Purchases' : 'Sales' ?></a>
    <h3 class="fw-bold mb-0 mt-1">
      <?= $isPurchase ? 'Purchase Record' : 'Sales Record' ?>: <span class="font-monospace text-primary"><?= htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8') ?></span>
    </h3>
  </div>

  <div class="d-flex flex-wrap gap-2">
    <?php if ($due > 0.001): ?>
      <a href="/payments/create?party_id=<?= $invoice['party_id'] ?>&invoice_id=<?= $invoice['id'] ?>&type=<?= $isPurchase ? 'payment_out' : 'payment_in' ?>" class="btn btn-success shadow-sm">
        <i class="bi bi-cash-stack me-1"></i> Record Payment
      </a>
    <?php endif; ?>
    <a href="/invoices/download/<?= $invoice['id'] ?>" class="btn btn-primary shadow-sm">
      <i class="bi bi-download me-1"></i> Download Record
    </a>
    <a href="/invoices/edit/<?= $invoice['id'] ?>" class="btn btn-outline-warning shadow-sm">
      <i class="bi bi-pencil-square me-1"></i> Edit Record
    </a>
    <a href="/invoices/print/<?= $invoice['id'] ?>" target="_blank" class="btn btn-outline-secondary shadow-sm">
      <i class="bi bi-printer me-1"></i> Print Record
    </a>
    <form action="/invoices/delete/<?= $invoice['id'] ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-message="Are you sure you want to delete this record? Item inventory stock will be restored automatically.">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-outline-danger" title="Delete Record">
        <i class="bi bi-trash me-1"></i> Delete
      </button>
    </form>
  </div>
</div>

<!-- Record Card Preview -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-body p-4 p-md-5">
    <!-- Header: Business Info & Record Info -->
    <div class="row border-bottom pb-4 mb-4">
      <div class="col-md-7">
        <div class="d-flex align-items-center mb-3">
          <?php if (!empty($firm['logo_path'])): ?>
            <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded border p-1 me-3 bg-white" height="50">
          <?php endif; ?>
          <div>
            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!empty($firm['gstin'])): ?>
              <div class="small text-muted font-monospace">GSTIN: <strong><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="small text-muted">
          <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php 
            $loc = array_filter([$firm['city'] ?? '', $firm['state'] ?? '']);
            if (!empty($loc)):
          ?>
            <div><?= htmlspecialchars(implode(', ', $loc), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($firm['pincode'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php if (!empty($firm['phone'])): ?><div>Phone: <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firm['email'])): ?><div>Email: <?= htmlspecialchars($firm['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>
      </div>

      <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <h5 class="fw-bold text-uppercase text-primary mb-2">
          <?= $isGst ? ($isPurchase ? 'GST Purchase Record' : 'GST Sales Record') : ($isPurchase ? 'Purchase Record' : 'Sales Record') ?>
        </h5>
        <div class="small">
          <div><strong>Record No:</strong> <span class="font-monospace"><?= htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8') ?></span></div>
          <div><strong>Record Date:</strong> <?= htmlspecialchars($invoice['invoice_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($invoice['due_date'])): ?><div><strong>Due Date:</strong> <?= htmlspecialchars($invoice['due_date'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <div><strong>Payment Mode:</strong> <?= strtoupper(htmlspecialchars($invoice['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></div>
          <div class="mt-2">
            <strong>Status:</strong>
            <?php if (($invoice['payment_status'] ?? '') === 'paid'): ?>
              <span class="badge badge-status badge-status-paid">Paid</span>
            <?php elseif (($invoice['payment_status'] ?? '') === 'partial'): ?>
              <span class="badge badge-status badge-status-partial">Partial</span>
            <?php else: ?>
              <span class="badge badge-status badge-status-unpaid">Unpaid</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Billed To Section -->
    <div class="row border-bottom pb-4 mb-4">
      <div class="col-12">
        <h6 class="fw-bold text-uppercase text-secondary small mb-2">
          <?= $isPurchase ? 'Party (Supplier / Vendor)' : 'Party (Customer / Client)' ?>
        </h6>
        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($invoice['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="small text-muted">
          <?php if (!empty($invoice['party_phone'])): ?><div>Phone: <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($invoice['party_gstin'])): ?><div>GSTIN: <span class="font-monospace fw-medium"><?= htmlspecialchars($invoice['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
          <?php if (!empty($invoice['party_address'])): ?><div>Address: <?= htmlspecialchars($invoice['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($invoice['party_state'])): ?><div>State: <?= htmlspecialchars($invoice['party_state'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($invoice['party_state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div><?php endif; ?>
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
              <th class="text-center">GST %</th>
              <th class="text-end">Tax (₹)</th>
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
                  <div class="fw-semibold text-dark"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($item['description'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center font-monospace small"><?= htmlspecialchars($item['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <strong><?= number_format((float)($item['quantity'] ?? 0), 2) ?></strong>
                  <span class="text-muted small"><?= htmlspecialchars($item['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="text-end">₹ <?= number_format((float)($item['price'] ?? 0), 2) ?></td>
                <?php if ($isGst): ?>
                  <td class="text-center small"><?= number_format((float)($item['tax_rate'] ?? 0), 0) ?>%</td>
                  <td class="text-end text-muted small">₹ <?= number_format((float)($item['tax_amount'] ?? 0), 2) ?></td>
                <?php endif; ?>
                <td class="text-end fw-bold">₹ <?= number_format((float)($item['total_amount'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= $isGst ? 8 : 6 ?>" class="text-center text-muted py-4">No items listed in this record.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Totals Summary & Terms -->
    <div class="row g-4">
      <div class="col-md-6">
        <?php if (!empty($invoice['notes'])): ?>
          <div class="mb-3">
            <h6 class="fw-bold small text-uppercase text-muted mb-1">Notes / Remarks</h6>
            <p class="small text-muted mb-0"><?= htmlspecialchars($invoice['notes'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php endif; ?>

        <?php 
          $termsText = $firm['terms_and_conditions'] ?? ($firm['terms'] ?? '');
          if (!empty($termsText)): 
        ?>
          <div class="mb-3">
            <h6 class="fw-bold small text-uppercase text-muted mb-1">Terms & Conditions</h6>
            <div class="small text-muted" style="white-space: pre-line;"><?= htmlspecialchars($termsText, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <!-- Digital Reference Record Notice -->
        <div class="alert alert-light border small text-muted mt-3 mb-0">
          <i class="bi bi-info-circle me-1 text-primary"></i>
          <strong>Digital Reference Record:</strong> This document is created for internal business bookkeeping and accounting data entry. Use these figures for filing returns or sharing with your tax professional.
        </div>
      </div>

      <div class="col-md-6">
        <div class="card bg-light border-0 p-3">
          <div class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted small">Taxable Subtotal:</span>
            <span class="fw-medium">₹ <?= number_format((float)($invoice['taxable_amount'] ?? ($invoice['subtotal'] ?? 0)), 2) ?></span>
          </div>

          <?php if ((float)($invoice['discount_amount'] ?? 0) > 0): ?>
            <div class="d-flex justify-content-between py-1 border-bottom text-danger">
              <span class="small">Discount:</span>
              <span>- ₹ <?= number_format((float)$invoice['discount_amount'], 2) ?></span>
            </div>
          <?php endif; ?>

          <?php if ($isGst && (float)($invoice['tax_amount'] ?? 0) > 0): ?>
            <?php if (!empty($invoice['is_interstate'])): ?>
              <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
                <span>IGST (Integrated Tax):</span>
                <span>₹ <?= number_format((float)($invoice['igst_amount'] ?? $invoice['tax_amount']), 2) ?></span>
              </div>
            <?php else: ?>
              <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
                <span>CGST:</span>
                <span>₹ <?= number_format((float)($invoice['cgst_amount'] ?? 0), 2) ?></span>
              </div>
              <div class="d-flex justify-content-between py-1 border-bottom text-muted small">
                <span>SGST:</span>
                <span>₹ <?= number_format((float)($invoice['sgst_amount'] ?? 0), 2) ?></span>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="fw-bold fs-6">Grand Total:</span>
            <span class="fw-bold fs-5 text-primary">₹ <?= number_format((float)($invoice['grand_total'] ?? 0), 2) ?></span>
          </div>

          <div class="d-flex justify-content-between py-1">
            <span class="text-success small fw-semibold">Amount Paid:</span>
            <span class="fw-semibold text-success">₹ <?= number_format((float)($invoice['paid_amount'] ?? 0), 2) ?></span>
          </div>

          <div class="d-flex justify-content-between py-1">
            <span class="text-danger small fw-semibold">Balance Due:</span>
            <span class="fw-bold text-danger">₹ <?= number_format($due, 2) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
