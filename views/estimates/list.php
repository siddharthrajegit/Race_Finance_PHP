<?php
/**
 * Estimates List View
 * RACE FINANCE - Billing & Inventory System
 */

$estimatesList = $estimates ?? [];
$estimateCount = count($estimatesList);
$totalEstimateValue = 0.0;
foreach ($estimatesList as $est) {
    $totalEstimateValue += (float)($est['grand_total'] ?? 0);
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold mb-1">
      <i class="bi bi-file-earmark-text me-2 text-primary"></i>Estimates &amp; Quotations
    </h3>
    <p class="text-muted small mb-0">Manage customer price estimates, quotations, and convert them to sales bills</p>
  </div>
  <div class="d-flex gap-2">
    <a href="/estimates/builder" class="btn btn-primary shadow-sm">
      <i class="bi bi-plus-circle me-1"></i> New Rate List / Estimate
    </a>
  </div>
</div>

<!-- Search & Summary Bar -->
<div class="card shadow-sm border-0 mb-3">
  <div class="card-body p-2 p-md-3">
    <div class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" placeholder="Search estimates by number or customer name..." data-table-search="estimatesTable">
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <span class="badge bg-light text-dark border p-2 me-2">
          Total Estimates: <strong id="visibleRecordsCount_estimatesTable"><?= $estimateCount ?></strong>
        </span>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2">
          Total Quoted: <strong>₹ <?= number_format($totalEstimateValue, 2) ?></strong>
        </span>
      </div>
    </div>
  </div>
</div>

<!-- Estimates Table -->
<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div style="overflow-x: hidden;">
      <table class="table table-hover align-middle mb-0 w-100" id="estimatesTable" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="padding: 0.5rem 0.65rem;">Estimate #</th>
            <th style="padding: 0.5rem 0.65rem;">Date</th>
            <th style="padding: 0.5rem 0.65rem;">Customer Firm Name</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">Items</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Taxable Amount</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end">Grand Total</th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center">Status</th>
            <th style="width: 36px; padding: 0.5rem 0.35rem;" class="text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($estimatesList)): ?>
            <?php foreach ($estimatesList as $est): ?>
              <tr>
                <td style="padding: 0.45rem 0.65rem;">
                  <a href="/estimates/view/<?= $est['id'] ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                    #<?= htmlspecialchars($est['estimate_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-muted small">
                  <?= htmlspecialchars(formatDate($est['estimate_date']), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;">
                  <?php if (!empty($est['customer_name'])): ?>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($est['customer_name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php else: ?>
                    <span class="text-muted fst-italic">-- Not Specified --</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center font-monospace">
                  <span class="badge bg-light text-dark border"><?= (int)($est['item_count'] ?? 0) ?> items</span>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end font-monospace text-muted">
                  ₹ <?= number_format((float)($est['subtotal'] ?? 0), 2) ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end font-monospace fw-bold text-dark">
                  ₹ <?= number_format((float)($est['grand_total'] ?? 0), 2) ?>
                </td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center">
                  <?php if (($est['status'] ?? 'draft') === 'converted'): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Converted</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.7rem;">Draft Estimate</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.35rem;" class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-light btn-sm p-1 border-0 rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" style="width: 28px; height: 28px; line-height: 1;">
                      <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1">
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/estimates/view/<?= $est['id'] ?>">
                          <i class="bi bi-eye text-primary me-2"></i> View Details
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/estimates/print/<?= $est['id'] ?>?mode=estimate" target="_blank">
                          <i class="bi bi-printer text-success me-2"></i> Print Estimate
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/estimates/print/<?= $est['id'] ?>?mode=ratelist" target="_blank">
                          <i class="bi bi-file-earmark-spreadsheet text-info me-2"></i> Print Rate List
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center text-success fw-semibold" href="/sales/create?estimate_id=<?= $est['id'] ?>">
                          <i class="bi bi-receipt me-2"></i> Convert to Invoice
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/estimates/builder?from_estimate=<?= $est['id'] ?>">
                          <i class="bi bi-copy text-secondary me-2"></i> Clone in Builder
                        </a>
                      </li>
                      <li><hr class="dropdown-divider my-1"></li>
                      <li>
                        <form action="/estimates/delete/<?= $est['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete Estimate #<?= htmlspecialchars($est['estimate_number'], ENT_QUOTES, 'UTF-8') ?>?">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item py-1 px-3 small d-flex align-items-center text-danger border-0 bg-transparent w-100">
                            <i class="bi bi-trash me-2"></i> Delete Estimate
                          </button>
                        </form>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-spreadsheet fs-2 d-block mb-2 text-secondary"></i>
                No estimates created yet.
                <div class="mt-2">
                  <a href="/estimates/builder" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Rate List / Estimate
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
