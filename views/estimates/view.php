<?php
/**
 * Estimate Detail View
 * RACE FINANCE - Billing & Inventory System
 */

$items = $estimate['items'] ?? [];
$firmName = $firm['name'] ?? 'RACE FINANCE';
?>

<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="flex flex-wrap items-center gap-2.5">
        <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight font-mono">Estimate #<?= htmlspecialchars($estimate['estimate_number'], ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (($estimate['status'] ?? 'draft') === 'converted'): ?>
          <span class="rf-badge rf-badge-sage">Converted to Invoice</span>
        <?php else: ?>
          <span class="rf-badge rf-badge-neutral">Draft Estimate</span>
        <?php endif; ?>
      </div>
      <p class="text-xs text-[#78746C] mt-1">Created on <?= htmlspecialchars(formatDate($estimate['estimate_date']), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="/estimates" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-arrow-left"></i> Back to List
      </a>
      <a href="/estimates/print/<?= $estimate['id'] ?>?mode=estimate" target="_blank" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-printer"></i> Print Estimate
      </a>
      <a href="/estimates/print/<?= $estimate['id'] ?>?mode=ratelist" target="_blank" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Print Rate List
      </a>
      <a href="/sales/create?estimate_id=<?= $estimate['id'] ?>" class="rf-btn rf-btn-sage-solid rf-btn-sm">
        <i class="bi bi-receipt"></i> Convert to Invoice
      </a>
    </div>
  </div>

  <div class="rf-card p-6 sm:p-8 space-y-7">
    <!-- Meta Header -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-[#ECEAE4]">
      <div>
        <div class="text-xs text-[#78746C] mb-1">From Business / Firm</div>
        <div class="text-base font-semibold text-[#1C1B18]"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="text-xs text-[#5C5A54] mt-1">
          <?php if (!empty($firm['gstin'])): ?>GSTIN: <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?> | <?php endif; ?>
          <?php if (!empty($firm['phone'])): ?>Ph: <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
        </div>
      </div>
      <div class="md:text-right">
        <div class="text-xs text-[#78746C] mb-1">Customer / Recipient Firm</div>
        <?php if (!empty($estimate['customer_name'])): ?>
          <div class="text-base font-semibold text-[#1C1B18]"><?= htmlspecialchars($estimate['customer_name'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php else: ?>
          <div class="text-xs italic text-[#78746C]">-- No Customer Specified (General Rate Card) --</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Items Table -->
    <div class="border border-[#E6E4DF] rounded-xl overflow-hidden">
      <div class="rf-table-wrap">
        <table class="rf-table">
          <thead>
            <tr>
              <th class="w-10 text-center">#</th>
              <th>Item Details</th>
              <th class="text-center">HSN/SAC</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Unit</th>
              <th class="text-right">Rate (₹)</th>
              <th class="text-right">Disc %</th>
              <th class="text-right">Taxable</th>
              <th class="text-center">GST</th>
              <th class="text-right">Total (₹)</th>
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
                  <td class="text-center text-xs text-[#78746C] font-mono tabular-nums"><?= $idx + 1 ?></td>
                  <td>
                    <div class="font-medium text-[#1C1B18]"><?= htmlspecialchars($it['item_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($subQtyStr)): ?>
                      <div class="text-xs text-[#2B526E]">(<?= htmlspecialchars($subQtyStr, ENT_QUOTES, 'UTF-8') ?>)</div>
                    <?php endif; ?>
                  </td>
                  <td class="text-center font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center font-semibold tabular-nums"><?= (float)$it['quantity'] ?></td>
                  <td class="text-center text-xs"><?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-right font-mono tabular-nums">₹ <?= number_format((float)$it['rate'], 2) ?></td>
                  <td class="text-right text-xs text-[#5C5A54] tabular-nums"><?= (float)($it['discount_percent'] ?? 0) > 0 ? (float)$it['discount_percent'] . '%' : '--' ?></td>
                  <td class="text-right font-mono tabular-nums">₹ <?= number_format((float)$it['taxable_amount'], 2) ?></td>
                  <td class="text-center">
                    <span class="rf-badge rf-badge-slate tabular-nums"><?= (float)$it['tax_rate'] ?>%</span>
                  </td>
                  <td class="text-right font-mono font-semibold text-[#1C1B18] tabular-nums">₹ <?= number_format((float)$it['total_amount'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Summary Box -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
      <div class="md:col-span-6">
        <?php if (!empty($estimate['notes'])): ?>
          <div class="rf-card-muted p-4">
            <div class="rf-label">Notes / Terms</div>
            <div class="text-xs text-[#5C5A54] whitespace-pre-line leading-relaxed"><?= htmlspecialchars($estimate['notes'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>
      </div>
      <div class="md:col-span-6">
        <div class="rf-card-muted p-5 space-y-2.5">
          <div class="flex justify-between text-xs text-[#5C5A54]">
            <span>Subtotal (Pre-GST)</span>
            <span class="font-mono font-medium tabular-nums text-[#1C1B18]">₹ <?= number_format((float)($estimate['subtotal'] ?? 0), 2) ?></span>
          </div>
          <?php if ((float)($estimate['discount_amount'] ?? 0) > 0): ?>
            <div class="flex justify-between text-xs text-[#8F2D2B]">
              <span>Total Discount</span>
              <span class="font-mono tabular-nums">- ₹ <?= number_format((float)$estimate['discount_amount'], 2) ?></span>
            </div>
          <?php endif; ?>
          <div class="flex justify-between text-xs text-[#5C5A54]">
            <span>Estimated GST Amount</span>
            <span class="font-mono font-medium tabular-nums text-[#2B526E]">₹ <?= number_format((float)($estimate['tax_amount'] ?? 0), 2) ?></span>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-[#E2DFD7]">
            <span class="text-sm font-semibold text-[#1C1B18]">Grand Total</span>
            <span class="text-lg font-mono font-semibold text-[#2E5A36] tabular-nums">₹ <?= number_format((float)($estimate['grand_total'] ?? 0), 2) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
