<?php
/**
 * Estimate Detail View
 * RACE FINANCE - Billing & Inventory System
 */

$items = $estimate['items'] ?? [];
$firmName = $firm['name'] ?? 'RACE FINANCE';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <div class="d-flex align-items-center gap-2">
      <h3 class="fw-bold mb-0">Estimate #<?= htmlspecialchars($estimate['estimate_number'], ENT_QUOTES, 'UTF-8') ?></h3>
      <?php if (($estimate['status'] ?? 'draft') === 'converted'): ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle">Converted to Invoice</span>
      <?php else: ?>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Draft Estimate</span>
      <?php endif; ?>
    </div>
    <p class="text-muted small mb-0">Created on <?= htmlspecialchars(formatDate($estimate['estimate_date']), ENT_QUOTES, 'UTF-8') ?></p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="/estimates" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <a href="/estimates/print/<?= $estimate['id'] ?>?mode=estimate" target="_blank" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-printer me-1"></i> Print Estimate
    </a>
    <a href="/estimates/print/<?= $estimate['id'] ?>?mode=ratelist" target="_blank" class="btn btn-outline-info btn-sm">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i> Print Rate List
    </a>
    <a href="/sales/create?estimate_id=<?= $estimate['id'] ?>" class="btn btn-success btn-sm shadow-sm fw-semibold">
      <i class="bi bi-receipt me-1"></i> Convert to Invoice
    </a>
  </div>
</div>

<div class="card shadow-sm border-0 mb-3">
  <div class="card-body p-4">
    <!-- Meta Header -->
    <div class="row g-3 mb-4 pb-3 border-bottom">
      <div class="col-md-6">
        <div class="text-muted small mb-1">From Business / Firm:</div>
        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h5>
        <div class="small text-muted">
          <?php if (!empty($firm['gstin'])): ?>GSTIN: <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?> | <?php endif; ?>
          <?php if (!empty($firm['phone'])): ?>Ph: <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <div class="text-muted small mb-1">Customer / Recipient Firm:</div>
        <?php if (!empty($estimate['customer_name'])): ?>
          <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($estimate['customer_name'], ENT_QUOTES, 'UTF-8') ?></h5>
        <?php else: ?>
          <div class="fst-italic text-muted">-- No Customer Specified (General Rate Card) --</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="width: 30px;" class="text-center">#</th>
            <th>Item Details</th>
            <th style="width: 75px;" class="text-center">HSN/SAC</th>
            <th style="width: 70px;" class="text-center">Qty</th>
            <th style="width: 90px;" class="text-center">Unit</th>
            <th style="width: 90px;" class="text-end">Rate (₹)</th>
            <th style="width: 70px;" class="text-end">Disc %</th>
            <th style="width: 95px;" class="text-end">Taxable</th>
            <th style="width: 80px;" class="text-center">GST</th>
            <th style="width: 110px;" class="text-end">Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $idx => $it): 
              $subQtyStr = '';
              if (!empty($it['sub_unit']) && !empty($it['sub_unit_qty'])) {
                  $totSub = (float)$it['sub_unit_qty'] * (float)$it['quantity'];
                  $subQtyStr = "= {$totSub} " . $it['sub_unit'];
              }
            ?>
              <tr>
                <td class="text-center text-muted font-monospace"><?= $idx + 1 ?></td>
                <td>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($it['item_name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($subQtyStr)): ?>
                    <div class="text-primary small" style="font-size: 0.72rem;">(<?= htmlspecialchars($subQtyStr, ENT_QUOTES, 'UTF-8') ?>)</div>
                  <?php endif; ?>
                </td>
                <td class="text-center font-monospace text-muted small"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center fw-bold"><?= (float)$it['quantity'] ?></td>
                <td class="text-center"><?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end font-monospace">₹ <?= number_format((float)$it['rate'], 2) ?></td>
                <td class="text-end text-muted"><?= (float)($it['discount_percent'] ?? 0) > 0 ? (float)$it['discount_percent'] . '%' : '--' ?></td>
                <td class="text-end font-monospace">₹ <?= number_format((float)$it['taxable_amount'], 2) ?></td>
                <td class="text-center">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">
                    <?= (float)$it['tax_rate'] ?>%
                  </span>
                </td>
                <td class="text-end fw-bold font-monospace text-dark">₹ <?= number_format((float)$it['total_amount'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Summary Box -->
    <div class="row g-3 justify-content-between align-items-start">
      <div class="col-md-6">
        <?php if (!empty($estimate['notes'])): ?>
          <div class="p-3 bg-light rounded border">
            <h6 class="fw-bold small mb-1">Notes / Terms:</h6>
            <div class="text-muted small" style="white-space: pre-line;"><?= htmlspecialchars($estimate['notes'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>
      </div>
      <div class="col-md-5">
        <div class="p-3 bg-light rounded border">
          <div class="d-flex justify-content-between text-muted small mb-1">
            <span>Subtotal (Pre-GST):</span>
            <span class="font-monospace fw-semibold">₹ <?= number_format((float)($estimate['subtotal'] ?? 0), 2) ?></span>
          </div>
          <?php if ((float)($estimate['discount_amount'] ?? 0) > 0): ?>
            <div class="d-flex justify-content-between text-danger small mb-1">
              <span>Total Discount:</span>
              <span class="font-monospace">- ₹ <?= number_format((float)$estimate['discount_amount'], 2) ?></span>
            </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between text-muted small mb-1">
            <span>Estimated GST Amount:</span>
            <span class="font-monospace fw-semibold text-primary">₹ <?= number_format((float)($estimate['tax_amount'] ?? 0), 2) ?></span>
          </div>
          <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-2 mt-2 text-dark">
            <span>Grand Total:</span>
            <span class="font-monospace text-success">₹ <?= number_format((float)($estimate['grand_total'] ?? 0), 2) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
