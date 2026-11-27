<?php
$invType = $invoice['type'] ?? 'sale';
$isPurchase = $invType === 'purchase';
$isGst = !empty($invoice['is_gst_bill']);
$items = !empty($invoice['items']) ? $invoice['items'] : [];
$due = (float)($invoice['balance_due'] ?? 0);
?>
<div class="space-y-6">
  <!-- Top Action Toolbar -->
  <div class="flex flex-wrap items-center justify-between gap-4 no-print">
    <div>
      <a href="<?= $isPurchase ? '/purchases' : '/sales' ?>" class="text-xs text-[#78746C] hover:text-[#1C1B18] no-underline">&larr; Back to <?= $isPurchase ? 'Purchases' : 'Sales' ?></a>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight mt-1">
        <?= $isPurchase ? 'Purchase Record' : 'Sales Record' ?>: <span class="font-mono text-[#2B526E]"><?= htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8') ?></span>
      </h1>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <?php if ($due > 0.001): ?>
        <a href="/payments/create?party_id=<?= $invoice['party_id'] ?>&invoice_id=<?= $invoice['id'] ?>&type=<?= $isPurchase ? 'payment_out' : 'payment_in' ?>" class="rf-btn rf-btn-sage rf-btn-sm">
          <i class="bi bi-cash-stack"></i> Record Payment
        </a>
      <?php endif; ?>
      <a href="/invoices/download/<?= $invoice['id'] ?>" class="rf-btn rf-btn-primary rf-btn-sm">
        <i class="bi bi-download"></i> Download Record
      </a>
      <a href="/invoices/edit/<?= $invoice['id'] ?>" class="rf-btn rf-btn-amber rf-btn-sm">
        <i class="bi bi-pencil-square"></i> Edit Record
      </a>
      <?php if (defined('ENABLE_THERMAL_RECEIPT') && ENABLE_THERMAL_RECEIPT): ?>
        <a href="/invoices/receipt/<?= $invoice['id'] ?>" target="_blank" class="rf-btn rf-btn-secondary rf-btn-sm">
          <i class="bi bi-receipt"></i> Thermal Receipt
        </a>
      <?php endif; ?>
      <a href="/invoices/print/<?= $invoice['id'] ?>" target="_blank" class="rf-btn rf-btn-secondary rf-btn-sm">
        <i class="bi bi-printer"></i> Print Record
      </a>
      <?php
        $waPhone = preg_replace('/[^0-9]/', '', (string)($invoice['party_phone'] ?? ''));
        if (strlen($waPhone) === 10) $waPhone = '91' . $waPhone;
        if (defined('ENABLE_WHATSAPP_REMINDER') && ENABLE_WHATSAPP_REMINDER && !$isPurchase && $due > 0.01):
          $waText = "Hello {$invoice['party_name']}, payment reminder from {$firm['name']} for Bill #{$invoice['invoice_number']} (Total: ₹" . number_format((float)$invoice['grand_total'], 2) . ", Balance Due: ₹" . number_format($due, 2) . ").";
          if (!empty($firm['upi_id'])) {
            $waText .= " UPI ID: {$firm['upi_id']}.";
          }
          $waLink = 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waText);
      ?>
        <a href="<?= htmlspecialchars($waLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="rf-btn rf-btn-sage rf-btn-sm" title="Send payment reminder via WhatsApp Web/App">
          <i class="bi bi-whatsapp"></i> Remind on WhatsApp
        </a>
      <?php endif; ?>
      <form action="/invoices/delete/<?= $invoice['id'] ?>" method="POST" class="inline form-delete-confirm" data-confirm-message="Are you sure you want to delete this record? Item inventory stock will be restored automatically.">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="rf-btn rf-btn-danger rf-btn-sm" title="Delete Record">
          <i class="bi bi-trash"></i> Delete
        </button>
      </form>
    </div>
  </div>

  <!-- Record Card Preview -->
  <div class="rf-card p-6 sm:p-10 space-y-8">
    <!-- Business & Record Meta -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pb-7 border-b border-[#ECEAE4]">
      <div class="md:col-span-7 space-y-3">
        <div class="flex items-center gap-3.5">
          <?php if (!empty($firm['logo_path'])): ?>
            <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="h-12 w-auto rounded-lg border border-[#E6E4DF] p-1 bg-white object-contain">
          <?php endif; ?>
          <div>
            <h2 class="text-lg font-semibold text-[#1C1B18]"><?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if (!empty($firm['gstin'])): ?>
              <div class="text-xs text-[#5C5A54] font-mono">GSTIN: <strong class="text-[#1C1B18]"><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="text-xs text-[#5C5A54] space-y-0.5 leading-relaxed">
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

      <div class="md:col-span-5 md:text-right space-y-2">
        <div class="text-xs font-semibold uppercase tracking-wider text-[#2B526E]">
          <?= $isGst ? ($isPurchase ? 'GST Purchase Record' : 'GST Sales Record') : ($isPurchase ? 'Purchase Record' : 'Sales Record') ?>
        </div>
        <div class="text-xs text-[#5C5A54] space-y-1">
          <div><strong class="text-[#1C1B18]">Record No:</strong> <span class="font-mono"><?= htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8') ?></span></div>
          <div><strong class="text-[#1C1B18]">Record Date:</strong> <?= htmlspecialchars(formatDate($invoice['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($invoice['due_date'])): ?><div><strong class="text-[#1C1B18]">Due Date:</strong> <?= htmlspecialchars(formatDate($invoice['due_date']), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <div><strong class="text-[#1C1B18]">Payment Mode:</strong> <?= strtoupper(htmlspecialchars($invoice['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></div>
          <div class="pt-1">
            <?php if (($invoice['payment_status'] ?? '') === 'paid'): ?>
              <span class="rf-badge rf-badge-sage">Paid</span>
            <?php elseif (($invoice['payment_status'] ?? '') === 'partial'): ?>
              <span class="rf-badge rf-badge-amber">Partial</span>
            <?php else: ?>
              <span class="rf-badge rf-badge-rose">Unpaid</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Party Section -->
    <div class="pb-7 border-b border-[#ECEAE4]">
      <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C] mb-2">
        <?= $isPurchase ? 'Party (Supplier / Vendor)' : 'Party (Customer / Client)' ?>
      </div>
      <div class="text-base font-semibold text-[#1C1B18]"><?= htmlspecialchars($invoice['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
      <div class="text-xs text-[#5C5A54] space-y-0.5 mt-1">
        <?php if (!empty($invoice['party_phone'])): ?><div>Phone: <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if (!empty($invoice['party_gstin'])): ?><div>GSTIN: <span class="font-mono font-medium text-[#1C1B18]"><?= htmlspecialchars($invoice['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
        <?php if (!empty($invoice['party_address'])): ?><div>Address: <?= htmlspecialchars($invoice['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if (!empty($invoice['party_state'])): ?><div>State: <?= htmlspecialchars($invoice['party_state'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($invoice['party_state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div><?php endif; ?>
      </div>
    </div>

    <!-- Line Items Table -->
    <div class="border border-[#E6E4DF] rounded-xl overflow-hidden">
      <div class="rf-table-wrap">
        <table class="rf-table">
          <thead>
            <tr>
              <th class="w-10 text-center">#</th>
              <th>Item Description</th>
              <th class="text-center">HSN/SAC</th>
              <th class="text-center">Qty</th>
              <th class="text-right">Rate (₹)</th>
              <?php if ($isGst): ?>
                <th class="text-center">GST %</th>
                <th class="text-right">Tax (₹)</th>
              <?php endif; ?>
              <th class="text-right">Total (₹)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $index => $item): ?>
                <tr>
                  <td class="text-center text-xs text-[#78746C] tabular-nums"><?= $index + 1 ?></td>
                  <td>
                    <div class="font-medium text-[#1C1B18]"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($item['description'])): ?>
                      <div class="text-xs text-[#78746C]"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="text-center font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($item['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center tabular-nums">
                    <span class="font-semibold"><?= number_format((float)($item['quantity'] ?? 0), 2) ?></span>
                    <span class="text-xs text-[#78746C]"><?= htmlspecialchars($item['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td class="text-right tabular-nums">₹ <?= number_format((float)($item['rate'] ?? 0), 2) ?></td>
                  <?php if ($isGst): ?>
                    <td class="text-center text-xs tabular-nums"><?= ((float)($item['tax_rate'] ?? 0) == (int)($item['tax_rate'] ?? 0)) ? number_format((float)($item['tax_rate'] ?? 0), 0) : (float)($item['tax_rate'] ?? 0) ?>%</td>
                    <td class="text-right text-xs text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($item['cgst_amount'] ?? 0) + (float)($item['sgst_amount'] ?? 0) + (float)($item['igst_amount'] ?? 0), 2) ?></td>
                  <?php endif; ?>
                  <td class="text-right font-semibold tabular-nums">₹ <?= number_format((float)($item['total_amount'] ?? 0), 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= $isGst ? 8 : 6 ?>" class="text-center text-[#78746C] py-8">No items listed in this record.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Summary & Terms -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
      <div class="md:col-span-6 space-y-4">
        <?php if (!empty($invoice['notes'])): ?>
          <div>
            <div class="rf-label">Notes / Remarks</div>
            <p class="text-xs text-[#5C5A54] leading-relaxed"><?= htmlspecialchars($invoice['notes'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php endif; ?>

        <?php
          $termsText = !empty($invoice['terms']) ? $invoice['terms'] : ($firm['terms'] ?? '');
          $footerNote = trim((string)($firmSettings['print']['footer_notes'] ?? ''));
          if (!empty($termsText)):
        ?>
          <div>
            <div class="rf-label">Terms &amp; Conditions</div>
            <div class="text-xs text-[#5C5A54] leading-relaxed whitespace-pre-line"><?= htmlspecialchars($termsText, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <?php if ($footerNote !== ''): ?>
          <div>
            <div class="rf-label">Footer Note</div>
            <div class="text-xs text-[#5C5A54] leading-relaxed whitespace-pre-line"><?= htmlspecialchars($footerNote, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <div class="rf-card-muted p-4 text-xs text-[#5C5A54] leading-relaxed">
          <i class="bi bi-info-circle mr-1 text-[#2B526E]"></i>
          <strong class="text-[#1C1B18]">Digital Reference Record:</strong> Created for internal bookkeeping and accounting data entry. Use these figures when filing returns with your tax professional.
        </div>
      </div>

      <div class="md:col-span-6">
        <div class="rf-card-muted p-5 space-y-2.5 text-sm">
          <?php if ((float)($invoice['discount_amount'] ?? 0) > 0): ?>
            <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7]">
              <span class="text-[#5C5A54] text-xs">Subtotal</span>
              <span class="font-medium tabular-nums">₹ <?= number_format((float)($invoice['subtotal'] ?? 0), 2) ?></span>
            </div>
            <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7] text-[#8F2D2B] text-xs">
              <span>Discount</span>
              <span class="tabular-nums">- ₹ <?= number_format((float)$invoice['discount_amount'], 2) ?></span>
            </div>
            <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7]">
              <span class="text-[#5C5A54] text-xs">Taxable Subtotal</span>
              <span class="font-medium tabular-nums">₹ <?= number_format((float)($invoice['taxable_amount'] ?? 0), 2) ?></span>
            </div>
          <?php else: ?>
            <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7]">
              <span class="text-[#5C5A54] text-xs">Taxable Subtotal</span>
              <span class="font-medium tabular-nums">₹ <?= number_format((float)($invoice['taxable_amount'] ?? ($invoice['subtotal'] ?? 0)), 2) ?></span>
            </div>
          <?php endif; ?>

          <?php if ($isGst && (float)($invoice['tax_amount'] ?? 0) > 0): ?>
            <?php if (!empty($invoice['is_interstate'])): ?>
              <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7] text-xs text-[#5C5A54]">
                <span>IGST (Integrated Tax)</span>
                <span class="tabular-nums">₹ <?= number_format((float)($invoice['igst_amount'] ?? $invoice['tax_amount']), 2) ?></span>
              </div>
            <?php else: ?>
              <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7] text-xs text-[#5C5A54]">
                <span>CGST</span>
                <span class="tabular-nums">₹ <?= number_format((float)($invoice['cgst_amount'] ?? 0), 2) ?></span>
              </div>
              <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7] text-xs text-[#5C5A54]">
                <span>SGST</span>
                <span class="tabular-nums">₹ <?= number_format((float)($invoice['sgst_amount'] ?? 0), 2) ?></span>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ((float)($invoice['round_off'] ?? 0) != 0.0): ?>
            <div class="flex justify-between pb-2.5 border-b border-[#E2DFD7] text-xs text-[#5C5A54]">
              <span>Round Off</span>
              <span class="tabular-nums">₹ <?= number_format((float)$invoice['round_off'], 2) ?></span>
            </div>
          <?php endif; ?>

          <div class="flex justify-between items-center py-2 border-b border-[#E2DFD7]">
            <span class="font-semibold text-[#1C1B18]">Grand Total</span>
            <span class="text-lg font-semibold text-[#1C1B18] tabular-nums">₹ <?= number_format((float)($invoice['grand_total'] ?? 0), 2) ?></span>
          </div>

          <div class="flex justify-between text-xs">
            <span class="text-[#2E5A36] font-medium">Amount Paid</span>
            <span class="font-semibold text-[#2E5A36] tabular-nums">₹ <?= number_format((float)($invoice['paid_amount'] ?? 0), 2) ?></span>
          </div>

          <div class="flex justify-between text-xs">
            <span class="text-[#8F2D2B] font-medium">Balance Due</span>
            <span class="font-semibold text-[#8F2D2B] tabular-nums">₹ <?= number_format($due, 2) ?></span>
          </div>

          <?php
            if (defined('ENABLE_UPI_QR') && ENABLE_UPI_QR && !$isPurchase && !empty($firm['upi_id'])):
              require_once __DIR__ . '/../../core/SvgQrCode.php';
              $upiPayAmt = $due > 0.01 ? $due : (float)($invoice['grand_total'] ?? 0);
              $upiDeepLink = SvgQrCode::buildUpiUri((string)$firm['upi_id'], (string)($firm['name'] ?? 'Merchant'), $upiPayAmt, "Bill #{$invoice['invoice_number']}");
              $upiWidgetHtml = SvgQrCode::renderUpiWidget($firm, $upiPayAmt, (string)$invoice['invoice_number'], 96);
          ?>
            <div class="mt-3 pt-3 border-t border-[#E2DFD7] flex justify-between items-center gap-3 no-print">
              <div>
                <div class="text-xs font-semibold text-[#1C1B18]">Scan or Pay via UPI</div>
                <div class="text-[11px] text-[#78746C] mt-0.5">Amount: ₹<?= number_format($upiPayAmt, 2) ?> (not auto-verified)</div>
                <a href="<?= htmlspecialchars($upiDeepLink, ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm mt-2">
                  <i class="bi bi-qr-code-scan"></i> Open UPI App
                </a>
              </div>
              <div class="bg-white p-1.5 border border-[#E2DFD7] rounded-lg shrink-0">
                <?= $upiWidgetHtml ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
