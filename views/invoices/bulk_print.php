<?php
$invCount = count($invoices ?? []);
$firmName = $firm['name'] ?? 'RACE FINANCE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk Print Invoices (<?= $invCount ?>) | <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></title>
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
      padding: 16px 0 36px 0;
    }
    .bulk-invoice-page {
      max-width: 210mm;
      margin: 0 auto 28px auto;
      background: #fff;
      padding: 12mm 15mm;
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
      padding: 4px 6px;
      font-size: 11px;
    }
    .bordered-table th {
      background-color: #FAF9F6;
      font-weight: 600;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10.5px;
    }
    .meta-table td {
      padding: 1.5px 0;
      vertical-align: top;
    }
    @media print {
      @page {
        margin: 8mm 8mm;
        size: A4 portrait;
      }
      body {
        background-color: #fff !important;
        padding: 0 !important;
      }
      .bulk-invoice-page {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        page-break-after: always;
        break-after: page;
      }
      .bulk-invoice-page:last-child {
        page-break-after: auto;
        break-after: auto;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Sticky Action Bar for Bulk Printing -->
  <div class="sticky top-0 z-30 py-3 mb-4 no-print bg-[#F7F6F3]/90 backdrop-blur-md">
    <div class="mx-auto px-3" style="max-width: 210mm;">
      <div class="flex flex-wrap items-center justify-between gap-2 bg-[#1C1B18] text-[#F7F6F3] px-4 py-2 rounded-full border border-[#302E29] shadow-md">
        <div class="flex items-center gap-2.5 text-xs">
          <span class="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center"><i class="bi bi-printer"></i></span>
          <span class="font-medium">Bulk Print Queue: <strong class="text-amber-300 font-mono"><?= $invCount ?></strong> invoices ready</span>
        </div>
        <div class="flex items-center gap-2">
          <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#2E5A36] text-white text-xs font-semibold hover:bg-[#254A2C] transition-colors cursor-pointer">
            <i class="bi bi-printer"></i> Print All Now
          </button>
          <button onclick="window.close()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-white/90 text-xs font-medium hover:bg-white/10 transition-colors cursor-pointer">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php foreach ($invoices as $idx => $invoice): 
    $invType = $invoice['type'] ?? 'sale';
    $isPurchase = $invType === 'purchase';
    $isGst = !empty($invoice['is_gst_bill']);
    $items = !empty($invoice['items']) ? $invoice['items'] : [];
    $invNum = $invoice['invoice_number'] ?? '';
    $cleanTaxable = (float)($invoice['taxable_amount'] ?? ($invoice['subtotal'] ?? 0));
    $cleanGrand = (float)($invoice['grand_total'] ?? 0);
    $cleanPaid = (float)($invoice['paid_amount'] ?? 0);
    $cleanDue = (float)($invoice['balance_due'] ?? 0);
  ?>
    <div class="bulk-invoice-page">
      <div class="tax-invoice-box">
        <!-- Title Bar -->
        <div class="flex items-center justify-between px-2.5 py-1.5 border-b border-[#1C1B18] bg-[#FAF9F6]">
          <div class="text-ink-muted font-mono text-[10px]">Doc <?= $idx + 1 ?> of <?= $invCount ?></div>
          <h5 class="font-bold mb-0 uppercase tracking-wider text-[13px]">
            <?= $isGst ? ($isPurchase ? 'TAX INVOICE / PURCHASE RECORD' : 'TAX INVOICE') : ($isPurchase ? 'PURCHASE BILL' : 'RETAIL INVOICE') ?>
          </h5>
          <div class="text-ink-muted text-[10px]">Original for Recipient</div>
        </div>

        <!-- Header: Firm Profile & Invoice Metadata -->
        <div class="grid grid-cols-12 border-b border-[#1C1B18]">
          <!-- Left: Firm Details -->
          <div class="col-span-7 p-2.5 border-r border-[#1C1B18]">
            <div class="flex items-center gap-2 mb-1">
              <?php if (!empty($firm['logo_path'])): ?>
                <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="max-h-9 max-w-[75px] object-contain">
              <?php endif; ?>
              <h6 class="font-bold mb-0 uppercase text-[13px]"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h6>
            </div>
            <div class="text-[10.5px] leading-snug">
              <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php 
                $fLoc = array_filter([$firm['city'] ?? '', $firm['state'] ?? '']);
                if (!empty($fLoc)):
              ?>
                <div><?= htmlspecialchars(implode(', ', $fLoc), ENT_QUOTES, 'UTF-8') ?> <?= !empty($firm['pincode']) ? '- ' . htmlspecialchars($firm['pincode'], ENT_QUOTES, 'UTF-8') : '' ?></div>
              <?php endif; ?>
              <?php if (!empty($firm['phone'])): ?><div><strong>Phone:</strong> <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firm['gstin'])): ?><div><strong>GSTIN:</strong> <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
          </div>

          <!-- Right: Invoice Metadata -->
          <div class="col-span-5 p-2.5 leading-snug">
            <table class="meta-table">
              <tr>
                <td class="font-bold w-[45%]">Invoice No:</td>
                <td class="font-mono font-bold"><?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
              <tr>
                <td class="font-bold">Invoice Date:</td>
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
                <td><?= htmlspecialchars($invoice['party_state'] ?? ($firm['state'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Billed To Party Details -->
        <div class="p-2.5 border-b border-[#1C1B18] bg-[#FAF9F6] text-[11px]">
          <div class="font-bold uppercase text-[9.5px] text-ink-muted"><?= $isPurchase ? 'Billed From (Supplier):' : 'Billed To (Customer):' ?></div>
          <div class="font-bold text-ink text-sm mb-1"><?= htmlspecialchars($invoice['party_name'] ?? 'Counter Sale', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="grid grid-cols-12 gap-2">
            <div class="col-span-6">
              <?php if (!empty($invoice['party_address'])): ?><div>Address: <?= htmlspecialchars($invoice['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($invoice['party_phone'])): ?><div>Phone: <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-span-6">
              <?php if (!empty($invoice['party_gstin'])): ?><div>GSTIN: <span class="font-mono font-bold"><?= htmlspecialchars($invoice['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
              <div>State: <?= htmlspecialchars($invoice['party_state'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <table class="bordered-table">
          <thead>
            <tr class="text-center">
              <th class="w-7">#</th>
              <th class="text-left">Item Description</th>
              <th class="w-[70px]">HSN/SAC</th>
              <th class="w-[50px]">Qty</th>
              <th class="w-[45px]">Unit</th>
              <th class="w-[75px] text-right">Rate (₹)</th>
              <?php if ($isGst): ?>
                <th class="w-[55px]">GST %</th>
              <?php endif; ?>
              <th class="w-[85px] text-right">Amount (₹)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $iIdx => $it): ?>
                <tr>
                  <td class="text-center"><?= $iIdx + 1 ?></td>
                  <td class="font-semibold"><?= htmlspecialchars($it['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center font-mono"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center font-bold"><?= htmlspecialchars((string)($it['quantity'] ?? 1), ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center"><?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-right">₹ <?= number_format((float)($it['rate'] ?? 0), 2) ?></td>
                  <?php if ($isGst): ?>
                    <td class="text-center"><?= (float)($it['tax_rate'] ?? 0) ?>%</td>
                  <?php endif; ?>
                  <td class="text-right font-bold">₹ <?= number_format((float)($it['total_amount'] ?? 0), 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Summary & Totals -->
        <div class="grid grid-cols-12">
          <div class="col-span-7 p-2.5 border-r border-[#1C1B18] text-[10px]">
            <?php if (!empty($firm['bank_name']) || !empty($firm['bank_account_no']) || !empty($firm['qr_code_path'])): ?>
              <div class="p-1.5 border border-border rounded bg-white mb-2 flex items-center justify-between gap-2">
                <div>
                  <strong>Bank:</strong> <?= htmlspecialchars($firm['bank_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> | 
                  <strong>A/C:</strong> <?= htmlspecialchars($firm['bank_account_no'] ?? '', ENT_QUOTES, 'UTF-8') ?> | 
                  <strong>IFSC:</strong> <?= htmlspecialchars($firm['bank_ifsc'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  <?php if (!empty($firm['upi_id'])): ?> | <strong>UPI:</strong> <?= htmlspecialchars($firm['upi_id'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                </div>
                <?php if (!empty($firm['qr_code_path'])): ?>
                  <img src="<?= htmlspecialchars($firm['qr_code_path'], ENT_QUOTES, 'UTF-8') ?>" alt="QR" class="h-9 w-9 object-contain border border-border bg-white shrink-0">
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <div>Terms: <?= htmlspecialchars(!empty($invoice['terms']) ? $invoice['terms'] : 'Thank you for your business!', ENT_QUOTES, 'UTF-8') ?></div>
          </div>

          <div class="col-span-5 p-2.5">
            <table class="meta-table text-[11px]">
              <tr>
                <td class="text-ink-muted">Sub Total:</td>
                <td class="text-right font-semibold">₹ <?= number_format($cleanTaxable, 2) ?></td>
              </tr>
              <?php if ($isGst && (float)($invoice['tax_amount'] ?? 0) > 0): ?>
                <?php if (!empty($invoice['is_interstate'])): ?>
                  <tr>
                    <td class="text-ink-muted">IGST:</td>
                    <td class="text-right">₹ <?= number_format((float)($invoice['igst_amount'] ?? 0), 2) ?></td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td class="text-ink-muted">CGST + SGST:</td>
                    <td class="text-right">₹ <?= number_format((float)($invoice['cgst_amount'] ?? 0) + (float)($invoice['sgst_amount'] ?? 0), 2) ?></td>
                  </tr>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ((float)($invoice['round_off'] ?? 0) != 0): ?>
                <tr>
                  <td class="text-ink-muted">Round Off:</td>
                  <td class="text-right">₹ <?= number_format((float)$invoice['round_off'], 2) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="border-t border-[#1C1B18]">
                <td class="pt-1 font-bold text-sm">Grand Total:</td>
                <td class="pt-1 text-right font-bold text-sm">₹ <?= number_format($cleanGrand, 2) ?></td>
              </tr>
              <tr>
                <td class="text-sage font-medium">Paid:</td>
                <td class="text-right text-sage font-bold">₹ <?= number_format($cleanPaid, 2) ?></td>
              </tr>
              <tr>
                <td class="text-rose font-bold">Balance Due:</td>
                <td class="text-right text-rose font-bold">₹ <?= number_format($cleanDue, 2) ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Footer Signatory -->
        <div class="grid grid-cols-12 border-t border-[#1C1B18] p-2.5 items-end text-[10px]">
          <div class="col-span-8">
            Digitally generated record &bull; <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?>
          </div>
          <div class="col-span-4 text-right">
            <div class="h-6"></div>
            <div class="font-bold">Authorized Signatory</div>
          </div>
        </div>

      </div>
    </div>
  <?php endforeach; ?>

  <script>
    window.addEventListener('load', () => {
      setTimeout(() => {
        window.print();
      }, 400);
    });
  </script>
</body>
</html>
