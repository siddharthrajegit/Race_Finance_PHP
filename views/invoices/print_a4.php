<?php
$invType = $invoice['type'] ?? 'sale';
$isPurchase = $invType === 'purchase';
$isGst = !empty($invoice['is_gst_bill']);
$items = !empty($invoice['items']) ? $invoice['items'] : [];
$firmName = $firm['name'] ?? 'RACE FINANCE';
$invNum = $invoice['invoice_number'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Record - <?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/tailwind.css">
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <style>
    body {
      background-color: #F7F6F3;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      color: #111;
      margin: 0;
      padding: 20px 0 40px 0;
    }
    .print-wrapper {
      max-width: 210mm;
      margin: 0 auto;
      background: #fff;
      padding: 15mm;
      box-shadow: 0 8px 24px -8px rgba(28, 27, 24, 0.08);
      border: 1px solid #E6E4DF;
      border-radius: 8px;
    }
    .tax-invoice-box {
      border: 1px solid #1C1B18;
    }
    .bordered-table {
      width: 100%;
      border-collapse: collapse;
    }
    .bordered-table th, .bordered-table td {
      border: 1px solid #1C1B18;
      padding: 5px 7px;
      font-size: 11px;
    }
    .bordered-table th {
      background-color: #FAF9F6;
      font-weight: 600;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
    }
    .meta-table td {
      padding: 2px 0;
      vertical-align: top;
    }
    @media print {
      body {
        background-color: #fff !important;
        padding: 0 !important;
      }
      .print-wrapper {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Toolbar -->
  <div class="mx-auto mb-4 px-3 no-print" style="max-width: 210mm;">
    <div class="flex flex-wrap items-center justify-between gap-2 bg-[#1C1B18] text-[#F7F6F3] px-4 py-2.5 rounded-xl border border-[#302E29]">
      <div class="flex items-center gap-2 text-sm">
        <i class="bi bi-printer text-amber-300"></i>
        <span class="font-medium">Print Digital Record <span class="font-mono text-white/75">(<?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?>)</span></span>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white text-ink text-xs font-semibold hover:bg-white/90 transition-colors cursor-pointer">
          <i class="bi bi-printer"></i> Print / Save PDF
        </button>
        <button onclick="window.close()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-white/20 text-white/90 text-xs font-medium hover:bg-white/10 transition-colors cursor-pointer">
          Close
        </button>
      </div>
    </div>
  </div>

  <div class="print-wrapper">
    <div class="tax-invoice-box">
      <!-- Record Title Bar -->
      <div class="text-center py-1.5 border-b border-[#1C1B18] bg-[#FAF9F6]">
        <h5 class="font-bold mb-0 uppercase tracking-wider text-sm">
          <?= $isGst ? ($isPurchase ? 'DIGITAL PURCHASE RECORD' : 'DIGITAL SALES RECORD') : ($isPurchase ? 'PURCHASE RECORD' : 'SALES RECORD') ?>
        </h5>
        <div class="text-ink-muted text-[10px]">(Digital Reference Copy)</div>
      </div>

      <!-- Seller & Invoice Header -->
      <div class="grid grid-cols-12 border-b border-[#1C1B18]">
        <!-- Left: Seller Info -->
        <div class="col-span-7 p-2.5 border-r border-[#1C1B18]">
          <div class="flex items-center gap-2 mb-1">
            <?php if (!empty($firm['logo_path'])): ?>
              <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="max-h-10 max-w-[80px] object-contain">
            <?php endif; ?>
            <h6 class="font-bold mb-0 uppercase text-[13px]"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h6>
          </div>
          <div class="text-[11px] leading-snug">
            <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php 
              $fLoc = array_filter([$firm['city'] ?? '', $firm['state'] ?? '']);
              if (!empty($fLoc)):
            ?>
              <div><?= htmlspecialchars(implode(', ', $fLoc), ENT_QUOTES, 'UTF-8') ?> <?= !empty($firm['pincode']) ? '- ' . htmlspecialchars($firm['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></div>
            <?php endif; ?>
            <?php if (!empty($firm['phone'])): ?><div><strong>Phone:</strong> <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if (!empty($firm['email'])): ?><div><strong>Email:</strong> <?= htmlspecialchars($firm['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if (!empty($firm['gstin'])): ?><div><strong>GSTIN:</strong> <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if (!empty($firm['pan'])): ?><div><strong>PAN:</strong> <?= htmlspecialchars($firm['pan'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <div><strong>State:</strong> <?= htmlspecialchars($firm['state'] ?? '--', ENT_QUOTES, 'UTF-8') ?> (Code: <?= htmlspecialchars($firm['state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div>
          </div>
        </div>

        <!-- Right: Invoice Metadata -->
        <div class="col-span-5 p-2.5 text-[11px] leading-snug">
          <table class="meta-table">
            <tr>
              <td class="font-bold w-[45%]">Record No:</td>
              <td class="font-mono font-bold"><?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
              <td class="font-bold">Record Date:</td>
              <td><?= htmlspecialchars(formatDate($invoice['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php if (!empty($invoice['due_date'])): ?>
              <tr>
                <td class="font-bold">Due Date:</td>
                <td><?= htmlspecialchars(formatDate($invoice['due_date']), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endif; ?>
            <tr>
              <td class="font-bold">Payment Mode:</td>
              <td class="uppercase"><?= strtoupper(htmlspecialchars($invoice['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></td>
            </tr>
            <tr>
              <td class="font-bold">Place of Supply:</td>
              <td><?= htmlspecialchars($invoice['party_state'] ?? ($firm['state'] ?? '--'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($invoice['party_state_code'] ?? ($firm['state_code'] ?? '--'), ENT_QUOTES, 'UTF-8') ?>)</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Buyer Details (Billed To) -->
      <div class="p-2.5 border-b border-[#1C1B18] text-[11px] leading-snug">
        <div class="font-bold uppercase text-[11px] mb-1">
          <?= $isPurchase ? 'Billed From (Supplier Details):' : 'Billed To (Customer Details):' ?>
        </div>
        <div class="grid grid-cols-12 gap-2">
          <div class="col-span-7">
            <div class="font-bold text-sm"><?= htmlspecialchars($invoice['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($invoice['party_address'])): ?><div><?= htmlspecialchars($invoice['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if (!empty($invoice['party_phone'])): ?><div><strong>Phone:</strong> <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          </div>
          <div class="col-span-5">
            <?php if (!empty($invoice['party_gstin'])): ?><div><strong>GSTIN / UIN:</strong> <?= htmlspecialchars($invoice['party_gstin'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <div><strong>State:</strong> <?= htmlspecialchars($invoice['party_state'] ?? '--', ENT_QUOTES, 'UTF-8') ?> (Code: <?= htmlspecialchars($invoice['party_state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div>
          </div>
        </div>
      </div>

      <!-- Line Items Table -->
      <table class="bordered-table">
        <thead>
          <tr class="text-center">
            <th class="w-8">#</th>
            <th class="text-left">Description of Goods / Services</th>
            <th class="w-[70px]">HSN/SAC</th>
            <th class="w-[45px]">Qty</th>
            <th class="w-[45px]">Unit</th>
            <th class="w-[65px] text-right">Rate (₹)</th>
            <th class="w-[50px] text-right">Disc %</th>
            <?php if ($isGst): ?>
              <th class="w-[70px] text-right">Taxable (₹)</th>
              <?php if (!empty($invoice['is_interstate'])): ?>
                <th class="w-[70px] text-right">IGST (₹)</th>
              <?php else: ?>
                <th class="w-[55px] text-right">CGST (₹)</th>
                <th class="w-[55px] text-right">SGST (₹)</th>
              <?php endif; ?>
            <?php endif; ?>
            <th class="w-[75px] text-right">Amount (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): 
              $disc = (float)($item['discount_percent'] ?? 0);
              $rateVal = (float)($item['rate'] ?? ($item['price'] ?? 0));
            ?>
              <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td>
                  <strong><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                </td>
                <td class="text-center font-mono"><?= htmlspecialchars($item['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars((string)($item['quantity'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($item['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right"><?= number_format($rateVal, 2) ?></td>
                <td class="text-right"><?= $disc > 0 ? $disc . '%' : '--' ?></td>
                <?php if ($isGst): ?>
                  <td class="text-right"><?= number_format((float)($item['taxable_amount'] ?? 0), 2) ?></td>
                  <?php if (!empty($invoice['is_interstate'])): ?>
                    <td class="text-right"><?= number_format((float)($item['igst_amount'] ?? 0), 2) ?></td>
                  <?php else: ?>
                    <td class="text-right"><?= number_format((float)($item['cgst_amount'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= number_format((float)($item['sgst_amount'] ?? 0), 2) ?></td>
                  <?php endif; ?>
                <?php endif; ?>
                <td class="text-right font-bold"><?= number_format((float)($item['total_amount'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Total & Calculation Summary -->
      <div class="grid grid-cols-12 border-t border-[#1C1B18]">
        <!-- Left: Bank Details & Terms -->
        <div class="col-span-7 p-2.5 border-r border-[#1C1B18] text-[11px]">
          <?php
            require_once __DIR__ . '/../../core/SvgQrCode.php';
            $upiAmt = ((float)($invoice['balance_due'] ?? 0) > 0) ? (float)$invoice['balance_due'] : (float)($invoice['grand_total'] ?? 0);
            $dynUpiQr = (defined('ENABLE_UPI_QR') && ENABLE_UPI_QR && !empty($firm['upi_id']))
                ? SvgQrCode::renderUpiWidget($firm, $upiAmt, (string)$invoice['invoice_number'], 84)
                : '';
          ?>
          <?php if (!empty($firm['bank_name']) || !empty($firm['bank_account_no']) || !empty($firm['upi_id']) || $dynUpiQr !== ''): ?>
            <div class="mb-2 flex justify-between items-start gap-2">
              <div class="space-y-0.5">
                <strong class="uppercase text-[10px]">Bank Account Details:</strong>
                <div>Bank: <strong><?= htmlspecialchars($firm['bank_name'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($firm['bank_branch'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</div>
                <div>A/C No: <strong><?= htmlspecialchars($firm['bank_account_no'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div>IFSC Code: <strong><?= htmlspecialchars($firm['bank_ifsc'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <?php if (!empty($firm['upi_id'])): ?><div>UPI ID: <strong><?= htmlspecialchars($firm['upi_id'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
              </div>
              <?php if ($dynUpiQr !== ''): ?>
                <div class="text-center border border-[#D4D1C9] p-1 rounded bg-white shrink-0">
                  <?= $dynUpiQr ?>
                  <div class="text-[8px] text-[#5C5A54]">UPI Pay (not payment proof)</div>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php 
            $termsText = !empty($invoice['terms']) ? $invoice['terms'] : ($firm['terms'] ?? '');
            $footerNote = trim((string)($firmSettings['print']['footer_notes'] ?? ''));
            if (!empty($termsText)): 
          ?>
            <div class="mt-2 text-ink-muted text-[10px]">
              <strong class="text-ink">Terms &amp; Conditions:</strong>
              <div class="whitespace-pre-line"><?= htmlspecialchars($termsText, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          <?php endif; ?>
          <?php if ($footerNote !== ''): ?>
            <div class="mt-2 text-ink-muted text-[10px]">
              <div class="whitespace-pre-line"><?= htmlspecialchars($footerNote, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right: Summary Totals -->
        <div class="col-span-5 p-2.5 text-[11px]">
          <table class="meta-table">
            <?php if ((float)($invoice['discount_amount'] ?? 0) > 0): ?>
              <tr>
                <td>Subtotal:</td>
                <td class="text-right font-semibold">₹ <?= number_format((float)($invoice['subtotal'] ?? 0), 2) ?></td>
              </tr>
              <tr>
                <td class="text-rose">Discount:</td>
                <td class="text-right text-rose">- ₹ <?= number_format((float)$invoice['discount_amount'], 2) ?></td>
              </tr>
              <tr>
                <td>Taxable Amount:</td>
                <td class="text-right font-semibold">₹ <?= number_format((float)($invoice['taxable_amount'] ?? 0), 2) ?></td>
              </tr>
            <?php else: ?>
              <tr>
                <td>Taxable Amount:</td>
                <td class="text-right font-semibold">₹ <?= number_format((float)($invoice['taxable_amount'] ?? ($invoice['subtotal'] ?? 0)), 2) ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($isGst && (float)($invoice['tax_amount'] ?? 0) > 0): ?>
              <?php if (!empty($invoice['is_interstate'])): ?>
                <tr>
                  <td>IGST (Integrated Tax):</td>
                  <td class="text-right">₹ <?= number_format((float)($invoice['igst_amount'] ?? $invoice['tax_amount']), 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td>CGST:</td>
                  <td class="text-right">₹ <?= number_format((float)($invoice['cgst_amount'] ?? 0), 2) ?></td>
                </tr>
                <tr>
                  <td>SGST:</td>
                  <td class="text-right">₹ <?= number_format((float)($invoice['sgst_amount'] ?? 0), 2) ?></td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ((float)($invoice['round_off'] ?? 0) != 0.0): ?>
              <tr>
                <td>Round Off:</td>
                <td class="text-right">₹ <?= number_format((float)$invoice['round_off'], 2) ?></td>
              </tr>
            <?php endif; ?>
            <tr class="border-t border-[#1C1B18]">
              <td class="pt-1 font-bold text-sm">Grand Total:</td>
              <td class="pt-1 text-right font-bold text-sm">₹ <?= number_format((float)($invoice['grand_total'] ?? 0), 2) ?></td>
            </tr>
            <tr>
              <td class="text-sage">Received / Paid:</td>
              <td class="text-right text-sage font-semibold">₹ <?= number_format((float)($invoice['paid_amount'] ?? 0), 2) ?></td>
            </tr>
            <tr>
              <td class="text-rose font-bold">Balance Due:</td>
              <td class="text-right text-rose font-bold">₹ <?= number_format((float)($invoice['balance_due'] ?? 0), 2) ?></td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Signatures Footer -->
      <div class="grid grid-cols-12 border-t border-[#1C1B18]">
        <div class="col-span-7 p-2.5 border-r border-[#1C1B18] text-ink-muted text-[10px]">
          <div>Customer / Recipient Signature</div>
          <div class="h-9"></div>
        </div>
        <div class="col-span-5 p-2.5 text-right text-[11px]">
          <div class="font-bold">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="h-6"></div>
          <div class="text-[10px] font-semibold">Authorized Signatory</div>
        </div>
      </div>

      <!-- Digital Reference Utility Note -->
      <div class="p-1.5 text-center border-t border-[#1C1B18] bg-[#FAF9F6] text-ink-muted text-[9px]">
        * Note: This document is a digital business record generated for internal management and accounting data entry. It is not an official tax invoice.
      </div>
    </div>
  </div>

  <script src="/js/vendor/qrcode.min.js"></script>
  <script src="/js/barcode.js"></script>
</body>
</html>
