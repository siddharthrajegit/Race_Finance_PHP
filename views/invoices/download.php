<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Download Bill - <?= htmlspecialchars($invoice["invoice_number"] ?? '', ENT_QUOTES, 'UTF-8') ?> | RACE FINANCE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <!-- Compiled Tailwind CSS -->
  <link rel="stylesheet" href="/css/tailwind.css">
  <!-- Bootstrap Icons (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <!-- html2pdf.js for instant PDF generation (Offline Local with CDN Fallback) -->
  <script src="/vendor/html2pdf/html2pdf.bundle.min.js"></script>
  <script>if(typeof html2pdf==='undefined'){document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');}</script>
  <link rel="stylesheet" href="/css/invoice-download.css?v=3">
  <style>
    body {
      background-color: #F7F6F3 !important;
      color: #1C1B18;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .top-action-bar {
      background: #1C1B18 !important;
      border-bottom: 1px solid #302E29 !important;
      box-shadow: 0 2px 10px rgba(28, 27, 24, 0.08);
    }
    .theme-dot {
      display: inline-block;
      width: 12px;
      height: 12px;
      border-radius: 9999px;
      margin-right: 8px;
      border: 1px solid rgba(0,0,0,0.18);
      flex-shrink: 0;
    }
    .paper-table-reset {
      width: 100%;
      border-collapse: collapse;
    }
    .paper-table-reset td {
      padding: 2px 4px;
      vertical-align: top;
    }
    .wa-chip-btn.active {
      background-color: #EDF3EC;
      color: #2E5A36;
      border-color: #BFD6BD;
      font-weight: 600;
    }
    @media print {
      body {
        background-color: #FFFFFF !important;
      }
    }
  </style>
</head>
<body class="print-mode-detailed">

  <?php if (!function_exists("amountToIndianWords")) {
    function amountToIndianWords($num) {
        if (!$num || !is_numeric($num)) return "Zero Rupees only";
        $num = (int)round((float)$num);
        if ($num === 0) return "Zero Rupees only";

        $a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
            "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
        $b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

        $convertTwoDigits = function($n) use ($a, $b) {
            if ($n < 20) return $a[$n];
            return $b[(int)floor($n / 10)] . ($n % 10 !== 0 ? " " . $a[$n % 10] : "");
        };

        $words = "";
        if (floor($num / 10000000) > 0) {
            $words .= $convertTwoDigits((int)floor($num / 10000000)) . " Crore ";
            $num %= 10000000;
        }
        if (floor($num / 100000) > 0) {
            $words .= $convertTwoDigits((int)floor($num / 100000)) . " Lakh ";
            $num %= 100000;
        }
        if (floor($num / 1000) > 0) {
            $words .= $convertTwoDigits((int)floor($num / 1000)) . " Thousand ";
            $num %= 1000;
        }
        if (floor($num / 100) > 0) {
            $words .= $a[(int)floor($num / 100)] . " Hundred ";
            $num %= 100;
        }
        if ($num > 0) {
            $words .= $convertTwoDigits($num);
        }
        return trim($words) . " Rupees only";
    }
}

$items = !empty($invoice["items"]) ? $invoice["items"] : [];
$totalQtyCount = 0;
$hasDiscount = false;
foreach ($items as $it) {
    $totalQtyCount += (float)($it["quantity"] ?? 0);
    if (!empty($it["discount_percent"]) && (float)$it["discount_percent"] > 0) {
        $hasDiscount = true;
    }
}
$firstTaxRate = !empty($items[0]["tax_rate"]) ? (float)$items[0]["tax_rate"] : 18;

$cleanTaxable = (float)($invoice["taxable_amount"] ?? ($invoice["subtotal"] ?? 0));
$cleanCgst = (float)($invoice["cgst_amount"] ?? 0);
$cleanSgst = (float)($invoice["sgst_amount"] ?? 0);
$cleanIgst = (float)($invoice["igst_amount"] ?? ($invoice["tax_amount"] ?? 0));
$cleanRoundOff = (float)($invoice["round_off"] ?? 0);
$cleanGrandTotal = (float)($invoice["grand_total"] ?? 0);
$cleanPaid = (float)($invoice["paid_amount"] ?? 0);
$cleanDue = (float)($invoice["balance_due"] ?? 0);
$wordsTotal = amountToIndianWords($cleanGrandTotal);

$firmName = $firm["name"] ?? "RACE FINANCE";
$invNum = $invoice["invoice_number"] ?? "";
$invDate = formatDate($invoice["invoice_date"] ?? "");
$partyName = $invoice["party_name"] ?? "Customer";
$payStatus = !empty($invoice["payment_status"]) ? strtoupper($invoice["payment_status"]) : "PENDING";

$waDefaultMessage = "*Tax Invoice from {$firmName}*\n" .
  "Invoice No: *{$invNum}*\n" .
  "Invoice Date: {$invDate}\n" .
  "Total Bill Amount: *₹ " . number_format($cleanGrandTotal, 2) . "*\n" .
  "Payment Received: *₹ " . number_format($cleanPaid, 2) . "*\n" .
  "Balance Due: *₹ " . number_format($cleanDue, 2) . "*\n" .
  "Status: *{$payStatus}*\n\n" .
  "Dear {$partyName}, please find your invoice details above. Thank you for your business!";
?>

  <!-- Top Static Action Toolbar -->
  <header class="top-action-bar no-print">
    <div class="max-w-7xl mx-auto px-2 flex flex-wrap items-center justify-between gap-3">
      <!-- Left: Back link and Template Selector -->
      <div class="flex flex-wrap items-center gap-3">
        <a href="<?= $invoice["type"] === 'purchase' ? '/purchases' : '/sales' ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-xs font-medium text-white/90 hover:bg-white/10 transition-colors">
          <i class="bi bi-arrow-left"></i> Back
        </a>

        <!-- Template Layout Selector Buttons -->
        <div class="template-switcher-bar">
          <button type="button" class="template-btn active" id="tabDetailed" onclick="switchInvoiceTemplate('detailed')">
            <i class="bi bi-file-earmark-spreadsheet mr-1"></i> Detailed Tax Bill
          </button>
          <button type="button" class="template-btn" id="tabSimple" onclick="switchInvoiceTemplate('simple')">
            <i class="bi bi-file-text mr-1"></i> Simple Bill (Portrait)
          </button>
          <button type="button" class="template-btn" id="tabHorizontal" onclick="switchInvoiceTemplate('horizontal')">
            <i class="bi bi-layout-three-columns mr-1"></i> Simple Horizontal (3-in-1)
          </button>
        </div>
      </div>

      <!-- Right: Action Buttons -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Theme Selector Dropdown -->
        <div class="rf-dropdown" data-ui-dropdown>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-xs font-medium text-white/90 hover:bg-white/10 transition-colors cursor-pointer" type="button" data-ui-dropdown-toggle aria-expanded="false">
            <i class="bi bi-palette text-amber-300"></i> Theme <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
          </button>
          <div class="rf-dropdown-menu w-60" data-ui-dropdown-menu>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('brown'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #5c3a21;"></span> Classy Espresso Brown
            </button>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('leather'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #78350f;"></span> Warm Cognac Leather
            </button>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('blue'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #1e3a8a;"></span> Classic Vyapar Blue
            </button>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('emerald'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #065f46;"></span> Royal Emerald
            </button>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('slate'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #0f172a;"></span> Executive Slate
            </button>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('crimson'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #831843;"></span> Imperial Crimson
            </button>
            <button type="button" class="rf-dropdown-item" onclick="setInvoiceTheme('purple'); window.RFUI && RFUI.closeAllDropdowns();">
              <span class="theme-dot" style="background: #4c1d95;"></span> Regal Purple
            </button>
            <div class="rf-dropdown-divider"></div>
            <button type="button" class="rf-dropdown-item font-medium" data-ui-modal="#hsvCustomThemeModal">
              <i class="bi bi-sliders2 text-amber"></i> Customise HSV Colour...
            </button>
          </div>
        </div>

        <!-- Customise HSV Theme Quick Button -->
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-amber-300/40 text-amber-200 text-xs font-medium hover:bg-amber-400/10 transition-colors cursor-pointer" data-ui-modal="#hsvCustomThemeModal" title="Set exact HSV colour">
          <i class="bi bi-sliders2"></i> Customise
        </button>

        <!-- Bank Details & QR Code Toggle -->
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-white/90 text-xs font-medium hover:bg-white/10 transition-colors cursor-pointer" id="btnToggleBankQr" onclick="toggleBankQr()" title="Show or hide Bank Details and QR Code on invoice">
          <i class="bi bi-qr-code text-sky-300" id="bankQrToggleIcon"></i>
          <span id="bankQrToggleText">Hide Bank &amp; QR</span>
        </button>

        <!-- 1. Download PDF Button -->
        <button id="btnDownloadPdf" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white text-ink text-xs font-semibold hover:bg-white/90 transition-colors cursor-pointer">
          <i class="bi bi-download"></i> Download PDF
        </button>

        <!-- 2. Print Options Dropdown -->
        <div class="rf-dropdown" data-ui-dropdown>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 text-white text-xs font-medium hover:bg-white/25 transition-colors cursor-pointer" type="button" data-ui-dropdown-toggle aria-expanded="false">
            <i class="bi bi-printer"></i> Print Bill <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
          </button>
          <div class="rf-dropdown-menu w-72" data-ui-dropdown-menu>
            <button type="button" class="rf-dropdown-item" onclick="triggerPrintMode('detailed'); window.RFUI && RFUI.closeAllDropdowns();">
              <i class="bi bi-file-earmark-spreadsheet text-ink-muted"></i> Print Detailed Tax Bill (Portrait)
            </button>
            <button type="button" class="rf-dropdown-item" onclick="triggerPrintMode('simple'); window.RFUI && RFUI.closeAllDropdowns();">
              <i class="bi bi-file-text text-sage"></i> Print Simple Format (Portrait)
            </button>
            <button type="button" class="rf-dropdown-item" onclick="triggerPrintMode('horizontal'); window.RFUI && RFUI.closeAllDropdowns();">
              <i class="bi bi-layout-three-columns text-amber"></i> Print Simple Bill (Horizontal 3-in-1)
            </button>
          </div>
        </div>

        <!-- 3. Send to WhatsApp to Anyone Button -->
        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#2E5A36] text-white text-xs font-semibold hover:bg-[#254A2C] transition-colors cursor-pointer" data-ui-modal="#sendWhatsAppModal" title="Send bill on WhatsApp to anyone">
          <i class="bi bi-whatsapp"></i> Send to WhatsApp
        </button>

        <!-- Edit Bill -->
        <a href="/invoices/edit/<?= $invoice["id"] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-white/90 text-xs font-medium hover:bg-white/10 transition-colors">
          <i class="bi bi-pencil-square"></i> Edit
        </a>
      </div>
    </div>
  </header>

  <!-- =============================================================
       1. PROPER DETAILED BILL PREVIEW (VYAPAR STYLE)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="detailedInvoiceArea">
    <div class="invoice-paper" id="detailedPaperArea">
      <div class="vyapar-top-strip"></div>

      <div class="invoice-inner">
        <!-- 1. Header: Firm Profile & Invoice Metadata -->
        <div class="grid grid-cols-12 gap-4 items-start mb-4 pb-3 border-b border-border">
          <!-- Left: Business Info -->
          <div class="col-span-7">
            <div class="flex items-center gap-3 mb-2">
              <?php if (!empty($firm["logo_path"])): ?>
                <img src="<?= $firm["logo_path"] ?>" alt="<?= htmlspecialchars($firm["name"] ?? $firmName, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-border p-1 bg-white max-h-[52px] max-w-[90px] object-contain">
              <?php endif; ?>
              <div>
                <h3 class="firm-name mb-0"><?= htmlspecialchars($firm["name"] ?? $firmName, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (!empty($firm["gstin"])): ?>
                  <div class="text-xs font-semibold text-ink-muted font-mono mt-1">
                    GSTIN: <span class="text-ink"><?= $firm["gstin"] ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-xs text-ink-muted pr-3 leading-relaxed space-y-0.5">
              <?php if (!empty($firm["address"])): ?><div><?= $firm["address"] ?></div><?php endif; ?>
              <?php if (!empty($firm["city"]) || !empty($firm["state"])): ?><div><?= implode(', ', array_filter([$firm['city'] ?? '', $firm['state'] ?? ''])) ?> - <?= ($firm["pincode"] ?: '') ?></div><?php endif; ?>
              <?php if (!empty($firm["phone"])): ?><div><i class="bi bi-telephone text-ink-subtle mr-1"></i> <?= $firm["phone"] ?></div><?php endif; ?>
              <?php if (!empty($firm["email"])): ?><div><i class="bi bi-envelope text-ink-subtle mr-1"></i> <?= $firm["email"] ?></div><?php endif; ?>
            </div>
          </div>

          <!-- Right: Invoice Title & Number Details -->
          <div class="col-span-5 text-right">
            <div class="invoice-title-badge mb-2">
              <?= $invoice["is_gst_bill"] ? 'TAX INVOICE' : 'RETAIL INVOICE' ?>
            </div>
            <div class="text-xs text-ink-muted space-y-0.5">
              <div><strong class="text-ink">Invoice No:</strong> <span class="font-mono font-bold text-ink"><?= $invoice["invoice_number"] ?></span></div>
              <div><strong class="text-ink">Invoice Date:</strong> <span class="text-ink"><?= formatDate($invoice["invoice_date"]) ?></span></div>
              <?php if (!empty($invoice["due_date"])): ?>
                <div><strong class="text-ink">Due Date:</strong> <span class="text-ink"><?= formatDate($invoice["due_date"]) ?></span></div>
              <?php endif; ?>
              <div><strong class="text-ink">Payment Mode:</strong> <span class="uppercase font-semibold text-ink"><?= ($invoice["payment_mode"] ?: 'Cash') ?></span></div>
              <div class="pt-1.5">
                <strong class="text-ink mr-1">Status:</strong>
                <?php if (($invoice["payment_status"] ?? '') === 'paid'): ?>
                  <span class="rf-badge rf-badge-sage">PAID IN FULL</span>
                <?php elseif (($invoice["payment_status"] ?? '') === 'partial'): ?>
                  <span class="rf-badge rf-badge-amber">PARTIALLY PAID</span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-rose">PAYMENT UNPAID</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Dual Party Cards -->
        <div class="grid grid-cols-12 gap-3 mb-4">
          <div class="col-span-7">
            <div class="info-card">
              <div class="info-card-header">
                <i class="bi bi-person-check mr-1"></i> <?= $invoice["type"] === 'purchase' ? 'Billed From (Supplier)' : 'Billed To (Customer)' ?>
              </div>
              <div class="font-bold text-ink text-sm"><?= $invoice["party_name"] ?></div>
              <div class="text-xs text-ink-muted mt-1 leading-relaxed space-y-0.5">
                <?php if (!empty($invoice["party_phone"])): ?><div><strong class="text-ink">Phone:</strong> <?= $invoice["party_phone"] ?></div><?php endif; ?>
                <?php if (!empty($invoice["party_gstin"])): ?><div><strong class="text-ink">GSTIN:</strong> <span class="font-mono font-medium text-ink"><?= $invoice["party_gstin"] ?></span></div><?php endif; ?>
                <?php if (!empty($invoice["party_address"])): ?><div><strong class="text-ink">Address:</strong> <?= $invoice["party_address"] ?></div><?php endif; ?>
                <div><strong class="text-ink">State:</strong> <?= ($invoice["party_state"] ?: '--') ?> <?php if (!empty($invoice["party_state_code"])): ?>(Code: <?= $invoice["party_state_code"] ?>)<?php endif; ?></div>
              </div>
            </div>
          </div>

          <div class="col-span-5">
            <div class="info-card">
              <div class="info-card-header">
                <i class="bi bi-shield-check mr-1"></i> Tax &amp; Place of Supply
              </div>
              <div class="text-xs leading-relaxed space-y-0.5 text-ink">
                <div><strong>Tax Regime:</strong> <?= $invoice["is_gst_bill"] ? 'GST Registered' : 'Composition / Non-GST' ?></div>
                <div><strong>Supply Type:</strong> <?= $invoice["is_interstate"] ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' ?></div>
                <div><strong>Place of Supply:</strong> <?= (!empty($invoice['party_state']) ? $invoice['party_state'] : (!empty($firm['state']) ? $firm['state'] : 'Local')) ?></div>
                <div class="mt-2 pt-2 border-t border-border-subtle text-ink-muted text-[11px]">
                  Original for Recipient &bull; Computer Generated
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Line Items Table -->
        <div class="overflow-x-auto mb-4">
          <table class="vyapar-table">
            <thead>
              <tr class="text-center">
                <th class="w-9">#</th>
                <th class="text-left">Item Description</th>
                <th class="w-20">HSN/SAC</th>
                <th class="w-16">Qty</th>
                <th class="w-14">Unit</th>
                <th class="w-[85px] text-right">Rate (₹)</th>
                <?php if ($hasDiscount): ?>
                  <th class="w-16 text-right">Disc %</th>
                <?php endif; ?>
                <?php if (!empty($invoice["is_gst_bill"])): ?>
                  <th class="w-16 text-center">GST %</th>
                <?php endif; ?>
                <th class="w-[100px] text-right">Amount (₹)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($items)): ?>
                <?php foreach ($items as $idx => $item): ?>
                  <tr>
                    <td class="text-center text-ink-muted text-xs"><?= $idx + 1 ?></td>
                    <td>
                      <div class="font-semibold text-ink"><?= $item["item_name"] ?></div>
                    </td>
                    <td class="text-center font-mono text-xs text-ink-muted"><?= ($item["hsn_code"] ?: '--') ?></td>
                    <td class="text-center font-bold"><?= $item["quantity"] ?></td>
                    <td class="text-center text-ink-muted text-xs"><?= ($item["unit"] ?: 'PCS') ?></td>
                    <td class="text-right font-mono">₹ <?= number_format((float)($item["rate"] ?? 0), 2) ?></td>
                    <?php if ($hasDiscount): ?>
                      <td class="text-right text-rose"><?= (!empty($item["discount_percent"]) && (float)$item["discount_percent"] > 0) ? ($item["discount_percent"] . '%') : '--' ?></td>
                    <?php endif; ?>
                    <?php if (!empty($invoice["is_gst_bill"])): ?>
                      <td class="text-center font-mono"><?= ((float)($item["tax_rate"] ?? 0) == (int)($item["tax_rate"] ?? 0)) ? number_format((float)($item["tax_rate"] ?? 0), 0) : (float)($item["tax_rate"] ?? 0) ?>%</td>
                    <?php endif; ?>
                    <td class="text-right font-bold font-mono text-ink">₹ <?= number_format((float)($item["total_amount"] ?? 0), 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- 4. Calculation Summary & Bank/QR Area -->
        <div class="grid grid-cols-12 gap-4 mb-4">
          <!-- Left: Bank Details & Terms -->
          <div class="col-span-6">
            <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
              <div class="bank-box bank-qr-block mb-3">
                <div class="flex items-center justify-between mb-2">
                  <div class="text-xs font-bold uppercase text-ink flex items-center gap-1.5">
                    <i class="bi bi-bank2 text-slate"></i> Bank &amp; Payment Details
                  </div>
                  <?php if (!empty($firm["upi_id"])): ?>
                    <span class="rf-badge rf-badge-slate text-[10px]">UPI Accepted</span>
                  <?php endif; ?>
                </div>
                <div class="flex items-center justify-between gap-3">
                  <div class="text-xs text-ink-muted flex-1 leading-relaxed space-y-0.5">
                    <?php if (!empty($firm["bank_name"])): ?><div>Bank Name: <strong class="text-ink"><?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($firm["bank_branch"] ?: '', ENT_QUOTES, 'UTF-8') ?>)</div><?php endif; ?>
                    <?php if (!empty($firm["bank_account_no"])): ?><div>A/C Number: <strong class="text-ink font-mono"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["bank_ifsc"])): ?><div>IFSC Code: <strong class="text-ink font-mono"><?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["upi_id"])): ?><div>UPI ID: <strong class="text-slate font-mono"><?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                  </div>
                  <?php
                    $showDynQr = defined('ENABLE_UPI_QR') && ENABLE_UPI_QR && !empty($firm['upi_id']) && !empty($settings['print']['show_upi_qr'] ?? true);
                    if ($showDynQr):
                      require_once __DIR__ . '/../../core/SvgQrCode.php';
                      $dynAmt = $cleanDue > 0.01 ? $cleanDue : $cleanGrandTotal;
                  ?>
                    <div class="text-center pl-3 border-l border-border flex flex-col items-center">
                      <?= SvgQrCode::renderUpiWidget($firm, $dynAmt, (string)($invoice['invoice_number'] ?? ''), 78) ?>
                      <span class="uppercase font-semibold text-ink-muted mt-1 text-[10px] tracking-wide">Dynamic UPI QR</span>
                    </div>
                  <?php elseif (!empty($firm["qr_code_path"])): ?>
                    <div class="text-center pl-3 border-l border-border flex flex-col items-center">
                      <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="Scan & Pay QR" class="rounded border border-border p-1 bg-white w-[78px] h-[78px] object-contain">
                      <span class="uppercase font-semibold text-ink-muted mt-1 text-[10px] tracking-wide">Scan to Pay</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php 
              $termsText = !empty($invoice['terms']) ? $invoice['terms'] : (!empty($firm['terms']) ? $firm['terms'] : '');
              if (!empty($termsText)): 
            ?>
              <div class="p-2.5 border border-border rounded-lg bg-white">
                <div class="uppercase font-bold text-ink-muted mb-1 text-[11px]">Terms &amp; Conditions:</div>
                <div class="text-ink-muted text-xs leading-snug whitespace-pre-line">
                  <?= htmlspecialchars($termsText, ENT_QUOTES, 'UTF-8') ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Right: Summary Table & Grand Total Highlight -->
          <div class="col-span-6">
            <div class="total-highlight-card">
              <table class="paper-table-reset text-xs">
                <?php if ((float)($invoice["discount_amount"] ?? 0) > 0): ?>
                  <tr>
                    <td class="text-ink-muted p-1">Subtotal:</td>
                    <td class="text-right font-semibold font-mono p-1">₹ <?= number_format((float)($invoice["subtotal"] ?? 0), 2) ?></td>
                  </tr>
                  <tr>
                    <td class="text-rose p-1">Overall Discount:</td>
                    <td class="text-right text-rose font-semibold font-mono p-1">- ₹ <?= number_format((float)($invoice["discount_amount"]), 2) ?></td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td class="text-ink-muted p-1">Taxable Subtotal:</td>
                  <td class="text-right font-semibold font-mono p-1">₹ <?= number_format($cleanTaxable, 2) ?></td>
                </tr>
                <?php if (!empty($invoice["is_gst_bill"]) && (float)($invoice["tax_amount"] ?? 0) > 0): ?>
                  <?php if (!empty($invoice["is_interstate"])): ?>
                    <tr>
                      <td class="text-ink-muted p-1">IGST (Integrated Tax):</td>
                      <td class="text-right font-mono p-1">₹ <?= number_format($cleanIgst, 2) ?></td>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <td class="text-ink-muted p-1">CGST (Central Tax):</td>
                      <td class="text-right font-mono p-1">₹ <?= number_format($cleanCgst, 2) ?></td>
                    </tr>
                    <tr>
                      <td class="text-ink-muted p-1">SGST (State Tax):</td>
                      <td class="text-right font-mono p-1">₹ <?= number_format($cleanSgst, 2) ?></td>
                    </tr>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($cleanRoundOff !== 0): ?>
                  <tr>
                    <td class="text-ink-muted p-1">Round Off:</td>
                    <td class="text-right font-mono p-1">₹ <?= number_format($cleanRoundOff, 2) ?></td>
                  </tr>
                <?php endif; ?>
                <tr class="border-t-2 border-border-strong">
                  <td class="font-bold text-sm p-1 pt-2 text-ink">Grand Total:</td>
                  <td class="text-right p-1 pt-2 font-mono total-amount-display">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
                </tr>
                <tr>
                  <td class="text-sage font-medium p-1">Received / Paid:</td>
                  <td class="text-right text-sage font-bold font-mono p-1">₹ <?= number_format($cleanPaid, 2) ?></td>
                </tr>
                <tr class="border-t border-border">
                  <td class="text-rose font-bold p-1 pt-1.5">Balance Due:</td>
                  <td class="text-right text-rose font-bold font-mono text-sm p-1 pt-1.5">₹ <?= number_format($cleanDue, 2) ?></td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- 5. Signatory & Bottom Acknowledgement -->
        <?php $footerNote = trim((string)($settings['print']['footer_notes'] ?? ($firmSettings['print']['footer_notes'] ?? ''))); ?>
        <div class="grid grid-cols-12 items-end mt-4 pt-3 border-t border-border">
          <div class="col-span-7">
            <div class="text-xs text-ink-muted leading-relaxed">
              <?php if ($footerNote !== ''): ?>
                <div class="text-ink font-medium mb-1 whitespace-pre-line"><?= htmlspecialchars($footerNote, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              Thank you for doing business with <strong class="text-ink"><?= htmlspecialchars($firm["name"] ?? $firmName, ENT_QUOTES, 'UTF-8') ?></strong>.<br>
              This is a digitally generated invoice, authorized signature verified.
            </div>
          </div>
          <div class="col-span-5 text-right">
            <div class="inline-block text-center">
              <?php if (!empty($firm["signature_path"])): ?>
                <img src="<?= $firm["signature_path"] ?>" alt="Signature" class="max-h-11 max-w-[140px] mx-auto mb-1 object-contain">
              <?php else: ?>
                <div class="h-9"></div>
              <?php endif; ?>
              <div class="sign-box">
                <div class="font-bold text-ink text-xs">For <?= htmlspecialchars($firm["name"] ?? $firmName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-ink-muted text-[11px]">Authorized Signatory</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- =============================================================
       2. SIMPLE BILL PREVIEW (PORTRAIT)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="simpleInvoiceArea" style="display: none;">
    <div class="invoice-paper simple-bill-paper" id="simplePaperArea">
      <!-- Top 3 Columns: Seller, Buyer, Invoice Details -->
      <div class="grid grid-cols-12 gap-3 items-start mb-4">
        <div class="col-span-4">
          <div class="simple-bill-header-title"><?= htmlspecialchars($firm["name"] ?? $firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($firm["phone"])): ?><div>Phone no. : <?= $firm["phone"] ?></div><?php endif; ?>
          <?php if (!empty($firm["address"])): ?><div>Address : <?= $firm["address"] ?></div><?php endif; ?>
          <?php if (!empty($firm["email"])): ?><div>Email : <?= $firm["email"] ?></div><?php endif; ?>
          <?php if (!empty($firm["gstin"])): ?><div>GSTIN : <?= $firm["gstin"] ?></div><?php endif; ?>
        </div>

        <div class="col-span-4">
          <div class="simple-bill-header-title"><?= $invoice["party_name"] ?></div>
          <?php if (!empty($invoice["party_address"])): ?><div>Address : <?= $invoice["party_address"] ?></div><?php endif; ?>
          <?php if (!empty($invoice["party_phone"])): ?><div>Phone no. : <?= $invoice["party_phone"] ?></div><?php endif; ?>
          <?php if (!empty($invoice["party_gstin"])): ?><div>GSTIN : <?= $invoice["party_gstin"] ?></div><?php endif; ?>
          <?php if (!empty($invoice["party_state"])): ?><div>State: <?= $invoice["party_state"] ?></div><?php endif; ?>
        </div>

        <div class="col-span-4 text-right">
          <div class="simple-bill-header-title">Invoice Details</div>
          <div>Invoice No. : <?= $invoice["invoice_number"] ?></div>
          <div>Date : <?= formatDate($invoice["invoice_date"]) ?></div>
          <div>Place of supply: <?= (!empty($invoice['party_state']) ? $invoice['party_state'] : (!empty($firm['state']) ? $firm['state'] : 'Local')) ?></div>
        </div>
      </div>

      <!-- Items Table -->
      <table class="simple-bill-table">
        <thead>
          <tr>
            <th class="w-9">#</th>
            <th>Item name</th>
            <th class="w-20 text-center">Quantity</th>
            <th class="w-16 text-center">Unit</th>
            <th class="w-[120px] text-right">Price/ Unit</th>
            <th class="w-[130px] text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $idx => $item): ?>
              <tr>
                <td><?= $idx + 1 ?></td>
                <td class="font-bold"><?= htmlspecialchars($item["item_name"] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $item["quantity"] ?></td>
                <td class="text-center"><?= (!empty($item["unit"]) ? htmlspecialchars($item["unit"], ENT_QUOTES, 'UTF-8') : 'PCS') ?></td>
                <td class="text-right">₹ <?= number_format((float)($item["rate"] ?? 0), 2) ?></td>
                <td class="text-right">₹ <?= number_format((float)($item["total_amount"] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          <tr class="total-row">
            <td></td>
            <td>Total</td>
            <td class="text-center"><?= $totalQtyCount ?></td>
            <td></td>
            <td></td>
            <td class="text-right">₹ <?= number_format((float)$cleanTaxable, 2) ?></td>
          </tr>
        </tbody>
      </table>

      <!-- Bottom 2-Column Section -->
      <div class="grid grid-cols-12 gap-4 items-start mt-4">
        <div class="col-span-6">
          <div class="font-bold mb-1">Invoice Amount In Words</div>
          <div class="mb-3"><?= $wordsTotal ?></div>

          <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
            <div class="bank-qr-block mb-3 p-2.5 border border-border rounded-lg bg-[#FAF9F6] text-xs">
              <div class="font-bold mb-1.5 pb-1 border-b border-border-subtle flex items-center justify-between gap-2">
                <span>Bank &amp; Payment Details</span>
                <?php if (!empty($firm["upi_id"])): ?>
                  <span class="rf-badge rf-badge-neutral text-[10px]">UPI: <?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <div class="flex items-center justify-between gap-2 mt-1">
                <div class="leading-snug text-xs space-y-0.5">
                  <?php if (!empty($firm["bank_name"])): ?><div>Bank: <strong><?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></strong> <?= !empty($firm["bank_branch"]) ? '(' . htmlspecialchars($firm["bank_branch"], ENT_QUOTES, 'UTF-8') . ')' : '' ?></div><?php endif; ?>
                  <?php if (!empty($firm["bank_account_no"])): ?><div>A/C: <span class="font-mono font-bold"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                  <?php if (!empty($firm["bank_ifsc"])): ?><div>IFSC: <span class="font-mono"><?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                </div>
                <?php if ($showDynQr ?? false): ?>
                  <div class="text-center pl-2 border-l border-border flex flex-col items-center">
                    <?= SvgQrCode::renderUpiWidget($firm, $cleanDue > 0.01 ? $cleanDue : $cleanGrandTotal, (string)($invoice['invoice_number'] ?? ''), 68) ?>
                    <span class="text-[10px] text-ink-muted mt-1">Dynamic UPI QR</span>
                  </div>
                <?php elseif (!empty($firm["qr_code_path"])): ?>
                  <div class="text-center pl-2 border-l border-border flex flex-col items-center">
                    <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="QR Code" class="border border-border p-1 bg-white rounded w-[68px] h-[68px] object-contain">
                    <span class="text-[10px] text-ink-muted mt-1">Scan to Pay</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="font-bold mb-1">Terms and Conditions</div>
          <div class="whitespace-pre-line"><?= htmlspecialchars(!empty($invoice['terms']) ? $invoice['terms'] : (!empty($firm['terms']) ? $firm['terms'] : 'Thanks for doing business with us!'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <div class="col-span-6">
          <table class="paper-table-reset ml-auto text-xs" style="max-width: 320px;">
            <tr>
              <td>Sub Total</td>
              <td class="text-right">₹ <?= number_format($cleanTaxable, 2) ?></td>
            </tr>
            <?php if (!empty($invoice["is_gst_bill"]) && ($cleanSgst > 0 || $cleanCgst > 0 || $cleanIgst > 0)): ?>
              <?php if (!empty($invoice["is_interstate"])): ?>
                <tr>
                  <td>IGST@<?= $firstTaxRate ?>%</td>
                  <td class="text-right">₹ <?= number_format($cleanIgst, 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td>SGST@<?= ($firstTaxRate / 2) ?>%</td>
                  <td class="text-right">₹ <?= number_format($cleanSgst, 2) ?></td>
                </tr>
                <tr>
                  <td>CGST@<?= ($firstTaxRate / 2) ?>%</td>
                  <td class="text-right">₹ <?= number_format($cleanCgst, 2) ?></td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
            <tr>
              <td>Round off</td>
              <td class="text-right">₹ <?= number_format($cleanRoundOff, 2) ?></td>
            </tr>
            <tr class="simple-total-bar">
              <td>Total</td>
              <td class="text-right">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
            </tr>
            <tr>
              <td>Received</td>
              <td class="text-right">₹ <?= number_format($cleanPaid, 2) ?></td>
            </tr>
            <tr>
              <td>Balance</td>
              <td class="text-right">₹ <?= number_format($cleanDue, 2) ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>


  <!-- =============================================================
       3. SIMPLE BILL HORIZONTAL (3-IN-1 SLIPS LANDSCAPE)
  ============================================================= -->
  <div class="invoice-preview-container-horizontal print-template-target" id="simpleHorizontalInvoiceArea" style="display: none;">
    <div class="horizontal-tri-grid" id="horizontalPaperArea">
      <?php foreach ([1, 2, 3] as $slipIndex): ?>
        <div class="horizontal-slip-card">
          <div class="flex items-center justify-between mb-1 text-[11px]">
            <div><strong>Invoice No. :</strong> <?= $invoice["invoice_number"] ?></div>
            <div><strong>Date :</strong> <?= formatDate($invoice["invoice_date"]) ?></div>
          </div>

          <div class="grid grid-cols-12 gap-1 mb-2 text-[11px]">
            <div class="col-span-6">
              <div class="font-bold text-ink"><?= htmlspecialchars($firm["name"] ?? $firmName, ENT_QUOTES, 'UTF-8') ?></div>
              <?php if (!empty($firm["phone"])): ?><div>Phone : <?= $firm["phone"] ?></div><?php endif; ?>
              <?php if (!empty($firm["address"])): ?><div>Address : <?= $firm["address"] ?></div><?php endif; ?>
              <?php if (!empty($firm["email"])): ?><div>Email : <?= $firm["email"] ?></div><?php endif; ?>
              <?php if (!empty($firm["gstin"])): ?><div>GSTIN : <?= $firm["gstin"] ?></div><?php endif; ?>
            </div>
            <div class="col-span-6">
              <div class="font-bold text-ink"><?= $invoice["party_name"] ?></div>
              <?php if (!empty($invoice["party_address"])): ?><div>Address : <?= $invoice["party_address"] ?></div><?php endif; ?>
              <?php if (!empty($invoice["party_phone"])): ?><div>Phone : <?= $invoice["party_phone"] ?></div><?php endif; ?>
              <?php if (!empty($invoice["party_gstin"])): ?><div>GSTIN : <?= $invoice["party_gstin"] ?></div><?php endif; ?>
              <?php if (!empty($invoice["party_state"])): ?><div>State: <?= $invoice["party_state"] ?></div><?php endif; ?>
            </div>
          </div>

          <table class="horizontal-slip-table" style="border-bottom: 1px solid #000000; margin-bottom: 5px;">
            <thead>
              <tr>
                <th style="width: 15px;">#</th>
                <th>Item</th>
                <th style="width: 28px;" class="text-center">Qty</th>
                <th style="width: 22px;" class="text-center">Unit</th>
                <th style="width: 55px; white-space:nowrap;" class="text-right">Price/Unit</th>
                <th style="width: 55px; white-space:nowrap;" class="text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($items)): ?>
                <?php foreach ($items as $idx => $item): ?>
                  <tr>
                    <td><?= $idx + 1 ?></td>
                    <td class="font-bold break-words whitespace-normal"><?= htmlspecialchars($item["item_name"] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-center" style="width:28px;"><?= $item["quantity"] ?></td>
                    <td class="text-center" style="width:22px;"><?= (!empty($item["unit"]) ? htmlspecialchars($item["unit"], ENT_QUOTES, 'UTF-8') : 'PCS') ?></td>
                    <td class="text-right whitespace-nowrap" style="width:55px;">₹ <?= number_format((float)($item["rate"] ?? 0), 2) ?></td>
                    <td class="text-right whitespace-nowrap" style="width:55px;">₹ <?= number_format((float)($item["total_amount"] ?? 0), 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <table class="paper-table-reset ml-auto text-[10.5px]">
            <tr>
              <td class="p-0">Sub Total</td>
              <td class="text-right p-0 whitespace-nowrap">₹ <?= number_format($cleanTaxable, 2) ?></td>
            </tr>
            <?php if (!empty($invoice["is_gst_bill"]) && ($cleanSgst > 0 || $cleanCgst > 0 || $cleanIgst > 0)): ?>
              <?php if (!empty($invoice["is_interstate"])): ?>
                <tr>
                  <td class="p-0">IGST</td>
                  <td class="text-right p-0 whitespace-nowrap">₹ <?= number_format($cleanIgst, 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td class="p-0">SGST</td>
                  <td class="text-right p-0 whitespace-nowrap">₹ <?= number_format($cleanSgst, 2) ?></td>
                </tr>
                <tr>
                  <td class="p-0">CGST</td>
                  <td class="text-right p-0 whitespace-nowrap">₹ <?= number_format($cleanCgst, 2) ?></td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
            <tr>
              <td class="p-0">Received</td>
              <td class="text-right p-0 whitespace-nowrap">₹ <?= number_format($cleanPaid, 2) ?></td>
            </tr>
            <tr class="simple-total-bar text-[11px]">
              <td class="py-1 px-1">Total</td>
              <td class="text-right py-1 px-1 whitespace-nowrap">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
            </tr>
          </table>

          <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
            <div class="bank-qr-block horizontal-bank-qr mt-2 pt-1 border-t border-border text-[10px] leading-tight">
              <div class="flex items-center justify-between gap-1">
                <div>
                  <?php if (!empty($firm["bank_name"])): ?><div><strong>Bank:</strong> <?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  <?php if (!empty($firm["bank_account_no"])): ?><div><strong>A/C:</strong> <span class="font-mono"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                  <?php if (!empty($firm["bank_ifsc"])): ?><div><strong>IFSC:</strong> <span class="font-mono"><?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                  <?php if (!empty($firm["upi_id"])): ?><div><strong>UPI:</strong> <?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <?php if (!empty($firm["qr_code_path"])): ?>
                  <div class="text-center">
                    <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="QR" class="border border-border p-0.5 bg-white rounded h-11 w-11 object-contain">
                    <div class="text-[8px] text-ink-muted">Scan &amp; Pay</div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>


  <!-- =============================================================
       MODALS: HSV COLOR STUDIO, WHATSAPP TO ANYONE & POST-DOWNLOAD
  ============================================================= -->

  <!-- Comprehensive HSV Custom Theme Studio Modal -->
  <div class="rf-modal hidden" id="hsvCustomThemeModal" tabindex="-1" aria-labelledby="hsvCustomThemeModalLabel" aria-hidden="true">
    <div class="rf-modal-dialog rf-modal-dialog-lg">
      <div class="rf-modal-header">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-amber-bg border border-border text-amber flex items-center justify-center shrink-0">
            <i class="bi bi-sliders2 text-base"></i>
          </div>
          <div>
            <h5 class="text-base font-semibold text-ink mb-0" id="hsvCustomThemeModalLabel">HSV Custom Colour Studio</h5>
            <p class="text-xs text-ink-muted mb-0">Set your exact brand theme by tuning Hue (H), Saturation (S), and Value (V)</p>
          </div>
        </div>
        <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>

      <div class="rf-modal-body">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left Column: Interactive 2D HSV Pad & Sliders -->
          <div class="lg:col-span-7 space-y-4">
            <div>
              <label class="rf-label flex items-center justify-between mb-1.5">
                <span><i class="bi bi-grid-3x3 mr-1 text-ink-muted"></i> 2D Saturation &amp; Brightness Surface</span>
                <span class="text-ink-muted text-[11px] font-normal">Click or drag cursor anywhere</span>
              </label>
              <div class="hsv-color-pad" id="hsvColorPad">
                <div class="hsv-cursor" id="hsvCursor"></div>
              </div>
            </div>

            <!-- Hue (H) Slider -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="rf-label mb-0">Hue (H) &mdash; Color Spectrum</label>
                <div class="flex items-center w-24">
                  <input type="number" id="hsvHNum" class="rf-input rf-input-sm text-right font-mono font-semibold rounded-r-none" min="0" max="360" value="28">
                  <span class="px-2 py-1 text-xs border border-l-0 border-border rounded-r-lg bg-canvas-alt text-ink-muted">°</span>
                </div>
              </div>
              <input type="range" id="hsvHSlider" class="hsv-slider-hue" min="0" max="360" value="28">
            </div>

            <!-- Saturation (S) Slider -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="rf-label mb-0">Saturation (S) &mdash; Chroma / Intensity</label>
                <div class="flex items-center w-24">
                  <input type="number" id="hsvSNum" class="rf-input rf-input-sm text-right font-mono font-semibold rounded-r-none" min="0" max="100" value="64">
                  <span class="px-2 py-1 text-xs border border-l-0 border-border rounded-r-lg bg-canvas-alt text-ink-muted">%</span>
                </div>
              </div>
              <input type="range" id="hsvSSlider" class="hsv-slider-bar" min="0" max="100" value="64">
            </div>

            <!-- Value (V) Slider -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="rf-label mb-0">Value / Brightness (V)</label>
                <div class="flex items-center w-24">
                  <input type="number" id="hsvVNum" class="rf-input rf-input-sm text-right font-mono font-semibold rounded-r-none" min="0" max="100" value="36">
                  <span class="px-2 py-1 text-xs border border-l-0 border-border rounded-r-lg bg-canvas-alt text-ink-muted">%</span>
                </div>
              </div>
              <input type="range" id="hsvVSlider" class="hsv-slider-bar" min="0" max="100" value="36">
            </div>
          </div>

          <!-- Right Column: Exact Color Codes, Preset Shortcuts & Live Preview -->
          <div class="lg:col-span-5 space-y-4">
            <div class="p-4 rounded-xl text-white flex items-center justify-between shadow-sm min-h-[72px]" id="hsvCurrentColorBox" style="background-color: #5c3a21;">
              <div>
                <div class="text-[10px] uppercase tracking-wider text-white/70 font-semibold">Active Brand Colour</div>
                <div class="text-xl font-bold font-mono" id="hsvHexDisplay">#5C3A21</div>
              </div>
              <div class="text-right font-mono text-xs">
                <div id="hsvRgbDisplay">rgb(92, 58, 33)</div>
                <div class="text-white/70" id="hsvValDisplay">H:28° S:64% V:36%</div>
              </div>
            </div>

            <div>
              <label class="rf-label">Enter Exact HEX Code</label>
              <div class="flex items-center gap-1.5">
                <div class="flex flex-1 items-center">
                  <span class="px-2.5 py-1.5 text-xs font-mono border border-r-0 border-border rounded-l-lg bg-canvas-alt text-ink-muted">#</span>
                  <input type="text" class="rf-input rf-input-sm font-mono font-semibold rounded-l-none" id="hsvHexInput" value="5C3A21" maxlength="6">
                </div>
                <button class="rf-btn rf-btn-secondary rf-btn-sm" type="button" id="btnApplyHexInput">Set</button>
              </div>
            </div>

            <div>
              <label class="rf-label">Quick Tuning Steps</label>
              <div class="flex flex-wrap gap-1.5">
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('v', -5)">Darker (-5% V)</button>
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('v', 5)">Brighter (+5% V)</button>
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('s', -10)">Mute (-10% S)</button>
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('s', 10)">Vibrant (+10% S)</button>
              </div>
            </div>

            <div>
              <label class="rf-label">Classical Starting Points</label>
              <div class="flex flex-wrap gap-2">
                <button type="button" class="palette-swatch-btn" style="background: #5c3a21;" title="Classy Espresso Brown (H:24 S:68 V:35)" onclick="setHsvValues(24, 68, 35)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #78350f;" title="Cognac Leather (H:22 S:88 V:47)" onclick="setHsvValues(22, 88, 47)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #451a03;" title="Roasted Mocha (H:19 S:75 V:27)" onclick="setHsvValues(19, 75, 27)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #9e6616;" title="Golden Ochre (H:38 S:78 V:62)" onclick="setHsvValues(38, 78, 62)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #1e3a8a;" title="Royal Sapphire (H:224 S:78 V:54)" onclick="setHsvValues(224, 78, 54)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #065f46;" title="Forest Emerald (H:165 S:89 V:37)" onclick="setHsvValues(165, 89, 37)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #831843;" title="Imperial Ruby (H:335 S:82 V:51)" onclick="setHsvValues(335, 82, 51)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #0f172a;" title="Executive Charcoal (H:222 S:47 V:16)" onclick="setHsvValues(222, 47, 16)"></button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="rf-modal-footer justify-between">
        <button type="button" class="rf-btn rf-btn-ghost rf-btn-sm" onclick="setHsvValues(24, 68, 35)">
          <i class="bi bi-arrow-counterclockwise"></i> Reset to Classy Brown
        </button>
        <div class="flex items-center gap-2">
          <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Close</button>
          <button type="button" class="rf-btn rf-btn-primary rf-btn-sm" id="btnSaveHsvTheme">
            <i class="bi bi-check2-circle"></i> Apply &amp; Save Theme
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Send WhatsApp to Anyone Modal -->
  <div class="rf-modal hidden" id="sendWhatsAppModal" tabindex="-1" aria-labelledby="sendWhatsAppModalLabel" aria-hidden="true">
    <div class="rf-modal-dialog">
      <div class="rf-modal-header">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-sage-bg text-sage flex items-center justify-center shrink-0">
            <i class="bi bi-whatsapp text-base"></i>
          </div>
          <div>
            <h5 class="text-base font-semibold text-ink mb-0" id="sendWhatsAppModalLabel">Send Bill on WhatsApp</h5>
            <p class="text-xs text-ink-muted mb-0">Send this bill summary to any customer, accountant, or contact</p>
          </div>
        </div>
        <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>

      <div class="rf-modal-body space-y-4">
        <div>
          <label class="rf-label">Quick Recipient Selection</label>
          <div class="flex flex-wrap gap-2">
            <?php if (!empty($invoice["party_phone"])): ?>
              <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm wa-chip-btn active" id="chipPartyPhone" onclick="setRecipientPhone('<?= htmlspecialchars($invoice["party_phone"], ENT_QUOTES, 'UTF-8') ?>')">
                <i class="bi bi-person-check"></i> <?= htmlspecialchars($invoice["party_name"] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($invoice["party_phone"], ENT_QUOTES, 'UTF-8') ?>)
              </button>
            <?php endif; ?>
            <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm wa-chip-btn" id="chipCustomPhone" onclick="focusCustomPhone()">
              <i class="bi bi-telephone-plus"></i> Enter Other Number
            </button>
          </div>
        </div>

        <div>
          <label class="rf-label">Recipient Mobile Number (Send to Anyone)</label>
          <div class="flex items-center">
            <span class="px-3 py-2 text-xs font-mono font-semibold border border-r-0 border-border rounded-l-lg bg-canvas-alt text-ink flex items-center gap-1">
              <i class="bi bi-phone text-sage"></i> +91
            </span>
            <input type="tel" class="rf-input font-mono rounded-l-none" id="waCustomPhoneInput" placeholder="Enter 10-digit mobile number" value="<?= preg_replace('/[^0-9]/', '', $invoice['party_phone'] ?? '') ?>" maxlength="10">
          </div>
          <p class="rf-help" id="waPhoneHelpText">You can enter any 10-digit number here to send the bill.</p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="rf-label mb-0">Message Preview (Editable)</label>
            <button type="button" class="text-xs font-medium text-ink underline underline-offset-2 hover:text-ink-muted cursor-pointer" id="btnCopyWaText">
              <i class="bi bi-clipboard mr-1"></i> <span id="copyBtnText">Copy Text</span>
            </button>
          </div>
          <textarea class="rf-textarea font-mono text-xs bg-canvas-alt/60" id="waMessageText" rows="6"></textarea>
        </div>

        <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-lg w-full justify-center" id="btnLaunchWhatsApp">
          <i class="bi bi-whatsapp"></i> Open &amp; Send in WhatsApp
        </button>
      </div>

      <div class="rf-modal-footer justify-between">
        <span class="text-xs text-ink-muted flex items-center gap-1"><i class="bi bi-shield-check text-sage"></i> Opens directly in WhatsApp App or Web</span>
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Post-Download WhatsApp Modal Prompt -->
  <div class="rf-modal hidden" id="postDownloadModal" tabindex="-1" aria-labelledby="postDownloadModalLabel" aria-hidden="true">
    <div class="rf-modal-dialog">
      <div class="rf-modal-header">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-full bg-sage text-white flex items-center justify-center shrink-0">
            <i class="bi bi-check-lg text-base"></i>
          </div>
          <h5 class="text-base font-semibold text-ink mb-0" id="postDownloadModalLabel">Bill PDF Downloaded</h5>
        </div>
        <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>
      <div class="rf-modal-body text-center space-y-4">
        <p class="text-ink-muted text-sm mb-0">
          Invoice <strong class="text-ink"><?= htmlspecialchars($invoice["invoice_number"] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> has been saved to your computer.<br>
          Send these bill details directly to anyone on WhatsApp:
        </p>

        <div class="text-left">
          <label class="rf-label">Recipient Mobile Number</label>
          <div class="flex items-center">
            <span class="px-3 py-2 text-xs font-mono font-semibold border border-r-0 border-border rounded-l-lg bg-canvas-alt text-ink flex items-center gap-1">
              <i class="bi bi-phone text-sage"></i> +91
            </span>
            <input type="tel" class="rf-input font-mono rounded-l-none" id="postWaPhoneInput" placeholder="Enter 10-digit mobile number" value="<?= preg_replace('/[^0-9]/', '', $invoice['party_phone'] ?? '') ?>" maxlength="10">
          </div>
        </div>

        <div class="rf-card-muted p-3.5 text-left text-xs font-mono space-y-1">
          <div><strong>Party:</strong> <?= htmlspecialchars($invoice["party_name"] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <div><strong>Total Bill:</strong> ₹ <?= number_format($cleanGrandTotal, 2) ?></div>
          <div><strong>Balance Due:</strong> ₹ <?= number_format($cleanDue, 2) ?></div>
        </div>

        <div class="space-y-2">
          <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-lg w-full justify-center" id="btnSendPostDownloadWa">
            <i class="bi bi-whatsapp"></i> Send to WhatsApp Now
          </button>
          <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm w-full justify-center" data-ui-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.RACE_INVOICE_DOWNLOAD = {
      invoiceNumber: <?= json_encode($invoice['invoice_number'] ?? '') ?>,
      whatsAppMessage: <?= json_encode($waDefaultMessage) ?>
    };
  </script>
  <script src="/js/vendor/qrcode.min.js"></script>
  <script src="/js/barcode.js"></script>
  <script src="/js/ui.js"></script>
  <script src="/js/invoice-download.js"></script>
</body>
</html>
