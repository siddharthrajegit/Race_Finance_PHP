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

<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Estimates &amp; Quotations</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Manage customer price estimates, quotations, and convert them to sales bills</p>
    </div>
    <a href="/estimates/builder" class="rf-btn rf-btn-primary">
      <i class="bi bi-plus-circle"></i> New Rate List / Estimate
    </a>
  </div>

  <!-- Search & Summary Bar -->
  <div class="rf-card p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="relative flex-1 max-w-md">
        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="text" class="rf-input pl-9 py-2 text-xs" placeholder="Search estimates by number or customer name..." data-table-search="estimatesTable">
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span class="rf-badge rf-badge-neutral py-1.5 px-3 tabular-nums">
          Total Estimates: <strong id="visibleRecordsCount_estimatesTable" class="ml-1"><?= $estimateCount ?></strong>
        </span>
        <span class="rf-badge rf-badge-slate py-1.5 px-3 tabular-nums">
          Total Quoted: <strong class="ml-1">₹ <?= number_format($totalEstimateValue, 2) ?></strong>
        </span>
      </div>
    </div>
  </div>

  <!-- Estimates Table -->
  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table" id="estimatesTable">
        <thead>
          <tr>
            <th>Estimate #</th>
            <th>Date</th>
            <th>Customer Firm Name</th>
            <th class="text-center">Items</th>
            <th class="text-right">Taxable Amount</th>
            <th class="text-right">Grand Total</th>
            <th class="text-center">Status</th>
            <th class="w-12 text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($estimatesList)): ?>
            <?php foreach ($estimatesList as $est): ?>
              <tr>
                <td>
                  <a href="/estimates/view/<?= $est['id'] ?>" class="font-mono font-semibold text-[#1C1B18] hover:underline no-underline">
                    #<?= htmlspecialchars($est['estimate_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td class="text-xs text-[#5C5A54] whitespace-nowrap">
                  <?= htmlspecialchars(formatDate($est['estimate_date']), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td>
                  <?php if (!empty($est['customer_name'])): ?>
                    <span class="font-medium text-[#1C1B18]"><?= htmlspecialchars($est['customer_name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php else: ?>
                    <span class="text-xs text-[#87847C] italic">-- Not Specified --</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="rf-badge rf-badge-neutral font-mono tabular-nums"><?= (int)($est['item_count'] ?? 0) ?> items</span>
                </td>
                <td class="text-right font-mono text-[#5C5A54] tabular-nums">
                  ₹ <?= number_format((float)($est['subtotal'] ?? 0), 2) ?>
                </td>
                <td class="text-right font-mono font-semibold text-[#1C1B18] tabular-nums">
                  ₹ <?= number_format((float)($est['grand_total'] ?? 0), 2) ?>
                </td>
                <td class="text-center">
                  <?php if (($est['status'] ?? 'draft') === 'converted'): ?>
                    <span class="rf-badge rf-badge-sage">Converted</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-neutral">Draft Estimate</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="rf-dropdown" data-ui-dropdown>
                    <button class="w-8 h-8 rounded-full hover:bg-[#EFECE6] inline-flex items-center justify-center text-[#5C5A54] bg-transparent border-0 cursor-pointer transition-colors" type="button" data-ui-dropdown-toggle aria-expanded="false" title="Actions">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="rf-dropdown-menu" data-ui-dropdown-menu>
                      <a class="rf-dropdown-item" href="/estimates/view/<?= $est['id'] ?>">
                        <i class="bi bi-eye text-[#2B526E]"></i> View Details
                      </a>
                      <a class="rf-dropdown-item" href="/estimates/print/<?= $est['id'] ?>?mode=estimate" target="_blank">
                        <i class="bi bi-printer text-[#2E5A36]"></i> Print Estimate
                      </a>
                      <a class="rf-dropdown-item" href="/estimates/print/<?= $est['id'] ?>?mode=ratelist" target="_blank">
                        <i class="bi bi-file-earmark-spreadsheet text-[#5C5A54]"></i> Print Rate List
                      </a>
                      <a class="rf-dropdown-item font-semibold text-[#2E5A36]" href="/sales/create?estimate_id=<?= $est['id'] ?>">
                        <i class="bi bi-receipt"></i> Convert to Invoice
                      </a>
                      <a class="rf-dropdown-item" href="/estimates/builder?from_estimate=<?= $est['id'] ?>">
                        <i class="bi bi-copy text-[#5C5A54]"></i> Clone in Builder
                      </a>
                      <div class="rf-dropdown-divider"></div>
                      <form action="/estimates/delete/<?= $est['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete Estimate #<?= htmlspecialchars($est['estimate_number'], ENT_QUOTES, 'UTF-8') ?>?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
                          <i class="bi bi-trash"></i> Delete Estimate
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-14 text-[#78746C]">
                <i class="bi bi-file-earmark-spreadsheet text-3xl block mb-2 text-[#B5B0A6]"></i>
                <p class="mb-4">No estimates created yet.</p>
                <a href="/estimates/builder" class="rf-btn rf-btn-primary rf-btn-sm">
                  <i class="bi bi-plus-circle"></i> Create Rate List / Estimate
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
