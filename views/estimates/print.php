<?php
/**
 * Rate List & Estimate Printable Layout (A4 format)
 * Supports 3 Templates (Detailed, Simple Portrait, Simple Horizontal 3-in-1)
 * Dynamically hides GST & Discount sections if all values are 0
 * RACE FINANCE - Billing & Inventory System
 */

$firmName = $firm['name'] ?? 'RACE FINANCE';
$firmAddress = array_filter([
    $firm['address'] ?? '',
    $firm['city'] ?? '',
    $firm['state'] ?? ''
]);
$firmPhone = $firm['phone'] ?? '';
$firmEmail = $firm['email'] ?? '';
$firmGstin = $firm['gstin'] ?? '';
$mode = $mode ?? ($_GET['mode'] ?? 'estimate');
$isRateList = ($mode === 'ratelist');
$items = $estimate['items'] ?? [];
$customerName = trim($estimate['customer_name'] ?? '');
$estimateNumber = $estimate['estimate_number'] ?? '';
$estimateDate = $estimate['estimate_date'] ?? date('Y-m-d');
$notes = trim($estimate['notes'] ?? '');

if (!function_exists('amountToIndianWords')) {
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

$hasAnyGst = false;
$hasAnyDiscount = false;

$totalQtyCount = 0;
$calculatedSubtotal = 0.0;
$calculatedTaxTotal = 0.0;
$calculatedDiscTotal = 0.0;
$calculatedGrandTotal = 0.0;

$normalizedItems = [];
foreach ($items as $idx => $it) {
    $qty = (float)($it['quantity'] ?? 1);
    if ($qty <= 0) $qty = 1;
    $rate = (float)($it['rate'] ?? ($it['sale_price'] ?? 0));
    $taxRate = (float)($it['tax_rate'] ?? 0);
    $discPercent = (float)($it['discount_percent'] ?? 0);

    $lineBase = $qty * $rate;
    $lineDisc = (float)($it['discount_amount'] ?? round($lineBase * ($discPercent / 100), 2));
    $lineTaxable = (float)($it['taxable_amount'] ?? max(0, $lineBase - $lineDisc));
    $lineTax = (float)($it['tax_amount'] ?? round($lineTaxable * ($taxRate / 100), 2));
    $lineTotal = (float)($it['total_amount'] ?? round($lineTaxable + $lineTax, 2));

    if ($taxRate > 0 || $lineTax > 0) {
        $hasAnyGst = true;
    }
    if ($discPercent > 0 || $lineDisc > 0) {
        $hasAnyDiscount = true;
    }

    $totalQtyCount += $qty;
    $calculatedSubtotal += $lineTaxable;
    $calculatedTaxTotal += $lineTax;
    $calculatedDiscTotal += $lineDisc;
    $calculatedGrandTotal += $lineTotal;

    $netPreGst = $discPercent > 0 ? round($rate * (1 - ($discPercent / 100)), 2) : $rate;
    $postGst = round($netPreGst * (1 + ($taxRate / 100)), 2);
    $origPostGst = round($rate * (1 + ($taxRate / 100)), 2);

    $unitStr = strtoupper($it['unit'] ?? 'PCS');
    $subUnitText = '--';
    if (!empty($it['sub_unit']) && !empty($it['sub_unit_qty'])) {
        $subUnitText = "1 " . $unitStr . " = " . (float)$it['sub_unit_qty'] . " " . strtoupper($it['sub_unit']);
    }

    $normalizedItems[] = [
        'index' => $idx + 1,
        'item_id' => $it['item_id'] ?? ($it['id'] ?? null),
        'item_name' => $it['item_name'] ?? ($it['name'] ?? 'Product'),
        'category' => $it['category'] ?? '',
        'hsn_code' => $it['hsn_code'] ?? '',
        'unit' => $unitStr,
        'sub_unit' => $it['sub_unit'] ?? '',
        'sub_unit_qty' => $it['sub_unit_qty'] ?? null,
        'subUnitText' => $subUnitText,
        'quantity' => $qty,
        'rate' => $rate,
        'tax_rate' => $taxRate,
        'discount_percent' => $discPercent,
        'discount_amount' => $lineDisc,
        'taxable_amount' => $lineTaxable,
        'tax_amount' => $lineTax,
        'total_amount' => $lineTotal,
        'net_pre_gst' => $netPreGst,
        'post_gst' => $postGst,
        'orig_post_gst' => $origPostGst
    ];
}

$cleanSubtotal = (float)($estimate['subtotal'] ?? $calculatedSubtotal);
$cleanDiscTotal = (float)($estimate['discount_amount'] ?? $calculatedDiscTotal);
$cleanTaxTotal = (float)($estimate['tax_amount'] ?? $calculatedTaxTotal);
$cleanGrandTotal = (float)($estimate['grand_total'] ?? $calculatedGrandTotal);

if ($cleanTaxTotal > 0) $hasAnyGst = true;
if ($cleanDiscTotal > 0) $hasAnyDiscount = true;

$docTitle = $isRateList ? 'PRODUCT RATE LIST' : 'ESTIMATE / QUOTATION';
$docRef = (!empty($estimateNumber)) ? "Ref No: *#{$estimateNumber}*\n" : "";
$waDefaultMessage = "*{$docTitle} from {$firmName}*\n" .
    $docRef .
    "Date: " . formatDate($estimateDate) . "\n" .
    (!empty($customerName) ? "Customer: *{$customerName}*\n" : "") .
    (!$isRateList ? "Total Amount: *₹ " . number_format($cleanGrandTotal, 2) . "*\n" : "") .
    "\nThank you for your business!";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isRateList ? 'Rate List - ' . htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') : 'Estimate ' . htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <!-- Compiled Tailwind CSS -->
  <link rel="stylesheet" href="/css/tailwind.css">
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <!-- html2pdf.js for instant PDF generation -->
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
    .customer-single-line {
      background-color: var(--theme-light, #FAF9F6);
      border: 1px solid var(--theme-border, #E6E4DF);
      border-left: 4px solid var(--theme-primary, #1C1B18);
      padding: 6px 12px;
      margin-bottom: 14px;
      font-size: 12px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      border-radius: 4px;
    }
    @media print {
      body {
        background-color: #FFFFFF !important;
      }
    }
  </style>
</head>
<body class="print-mode-detailed">

  <!-- Top Action Toolbar -->
  <header class="top-action-bar no-print">
    <div class="max-w-7xl mx-auto px-2 flex flex-wrap items-center justify-between gap-3">
      <!-- Left: Back link and Template Selector -->
      <div class="flex flex-wrap items-center gap-3">
        <a href="/estimates" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-xs font-medium text-white/90 hover:bg-white/10 transition-colors">
          <i class="bi bi-arrow-left"></i> Back
        </a>

        <!-- Template Layout Selector Buttons -->
        <div class="template-switcher-bar">
          <button type="button" class="template-btn active" id="tabDetailed" onclick="switchInvoiceTemplate('detailed')">
            <i class="bi bi-file-earmark-spreadsheet mr-1"></i> <?= $isRateList ? 'Detailed Rate List' : 'Detailed Quotation' ?>
          </button>
          <button type="button" class="template-btn" id="tabSimple" onclick="switchInvoiceTemplate('simple')">
            <i class="bi bi-file-text mr-1"></i> Simple (Portrait)
          </button>
          <button type="button" class="template-btn" id="tabHorizontal" onclick="switchInvoiceTemplate('horizontal')">
            <i class="bi bi-layout-three-columns mr-1"></i> Horizontal (3-in-1)
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
        <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
          <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/20 text-white/90 text-xs font-medium hover:bg-white/10 transition-colors cursor-pointer" id="btnToggleBankQr" onclick="toggleBankQr()" title="Show or hide Bank Details and QR Code">
            <i class="bi bi-qr-code text-sky-300" id="bankQrToggleIcon"></i>
            <span id="bankQrToggleText">Hide Bank &amp; QR</span>
          </button>
        <?php endif; ?>

        <!-- Download PDF Button -->
        <button id="btnDownloadPdf" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white text-ink text-xs font-semibold hover:bg-white/90 transition-colors cursor-pointer">
          <i class="bi bi-download"></i> Download PDF
        </button>

        <!-- Print Options Dropdown -->
        <div class="rf-dropdown" data-ui-dropdown>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 text-white text-xs font-medium hover:bg-white/25 transition-colors cursor-pointer" type="button" data-ui-dropdown-toggle aria-expanded="false">
            <i class="bi bi-printer"></i> Print <i class="bi bi-chevron-down text-[10px] opacity-70"></i>
          </button>
          <div class="rf-dropdown-menu w-72" data-ui-dropdown-menu>
            <button type="button" class="rf-dropdown-item" onclick="triggerPrintMode('detailed'); window.RFUI && RFUI.closeAllDropdowns();">
              <i class="bi bi-file-earmark-spreadsheet text-ink-muted"></i> Print Detailed <?= $isRateList ? 'Rate List' : 'Quotation' ?> (Portrait)
            </button>
            <button type="button" class="rf-dropdown-item" onclick="triggerPrintMode('simple'); window.RFUI && RFUI.closeAllDropdowns();">
              <i class="bi bi-file-text text-sage"></i> Print Simple Format (Portrait)
            </button>
            <button type="button" class="rf-dropdown-item" onclick="triggerPrintMode('horizontal'); window.RFUI && RFUI.closeAllDropdowns();">
              <i class="bi bi-layout-three-columns text-amber"></i> Print Simple Slips (Horizontal 3-in-1)
            </button>
          </div>
        </div>

        <!-- Send WhatsApp Modal Trigger -->
        <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#2E5A36] text-white text-xs font-semibold hover:bg-[#254A2C] transition-colors cursor-pointer" data-ui-modal="#sendWhatsAppModal" title="Send details on WhatsApp">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </button>
      </div>
    </div>
  </header>


  <!-- =============================================================
       1. DETAILED PREVIEW (VYAPAR / HIGH-END STYLE)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="detailedInvoiceArea">
    <div class="invoice-paper" id="detailedPaperArea">
      <div class="vyapar-top-strip"></div>

      <div class="invoice-inner">
        <!-- 1. Header: Firm Profile & Document Metadata -->
        <div class="grid grid-cols-12 gap-4 items-start mb-4 pb-3 border-b border-border">
          <div class="col-span-7">
            <div class="flex items-center gap-3 mb-2">
              <?php if (!empty($firm["logo_path"])): ?>
                <img src="<?= $firm["logo_path"] ?>" alt="<?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-border p-1 bg-white max-h-[52px] max-w-[90px] object-contain">
              <?php endif; ?>
              <div>
                <h3 class="firm-name mb-0"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (!empty($firmGstin)): ?>
                  <div class="text-xs font-semibold text-ink-muted font-mono mt-1">
                    GSTIN: <span class="text-ink"><?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-xs text-ink-muted pr-3 leading-relaxed space-y-0.5">
              <?php if (!empty($firmAddress)): ?><div><?= htmlspecialchars(implode(', ', $firmAddress), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firmPhone)): ?><div><i class="bi bi-telephone text-ink-subtle mr-1"></i> <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firmEmail)): ?><div><i class="bi bi-envelope text-ink-subtle mr-1"></i> <?= htmlspecialchars($firmEmail, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
          </div>

          <div class="col-span-5 text-right">
            <div class="invoice-title-badge mb-2">
              <?= $docTitle ?>
            </div>
            <div class="text-xs text-ink-muted space-y-0.5">
              <?php if (!$isRateList && !empty($estimateNumber)): ?>
                <div><strong class="text-ink">Estimate No:</strong> <span class="font-mono font-bold text-ink">#<?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php endif; ?>
              <div><strong class="text-ink">Date:</strong> <span class="text-ink"><?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php if (!empty($customerName)): ?>
                <div class="pt-1">
                  <strong class="text-ink">Customer:</strong> <span class="font-bold text-ink"><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($customerName)): ?>
          <div class="customer-single-line">
            <span class="text-ink-muted uppercase font-semibold text-[10.5px] tracking-wide">Customer:</span>
            <strong class="text-ink ml-1 text-[13px]"><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
        <?php endif; ?>

        <!-- Line Items Table -->
        <div class="overflow-x-auto mb-4">
          <table class="vyapar-table">
            <thead>
              <tr class="text-center">
                <th class="w-9">#</th>
                <th class="text-left"><?= $isRateList ? 'Item / Product Name' : 'Item Description' ?></th>
                <th class="w-[75px]">HSN/SAC</th>
                <?php if (!$isRateList): ?>
                  <th class="w-14">Qty</th>
                  <th class="w-14">Unit</th>
                  <th class="w-[85px] text-right">Rate (₹)</th>
                <?php else: ?>
                  <th class="w-[95px] text-right">Pre-GST Price</th>
                <?php endif; ?>
                <?php if ($hasAnyDiscount): ?>
                  <th class="w-16 text-center">Disc %</th>
                <?php endif; ?>
                <?php if ($hasAnyGst): ?>
                  <th class="w-16 <?= $isRateList ? 'text-center' : 'text-right' ?>"><?= $isRateList ? 'GST %' : 'GST' ?></th>
                <?php endif; ?>
                <?php if (!$isRateList): ?>
                  <th class="w-[95px] text-right">Amount (₹)</th>
                <?php else: ?>
                  <th class="w-[95px] text-right">Post-GST Price</th>
                  <th class="w-[135px] text-left">Unit &amp; Packaging</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($normalizedItems)): ?>
                <?php foreach ($normalizedItems as $item): ?>
                  <tr>
                    <td class="text-center text-ink-muted text-xs"><?= $item['index'] ?></td>
                    <td>
                      <div class="font-semibold text-ink"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php if (!empty($item['category'])): ?>
                        <span class="rf-badge rf-badge-neutral text-[9px]"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center font-mono text-xs text-ink-muted"><?= htmlspecialchars($item['hsn_code'] ?: '--', ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if (!$isRateList): ?>
                      <td class="text-center font-bold"><?= $item['quantity'] ?></td>
                      <td class="text-center text-ink-muted text-xs"><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="text-right font-mono">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php else: ?>
                      <td class="text-right font-mono font-semibold">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php endif; ?>
                    <?php if ($hasAnyDiscount): ?>
                      <td class="text-center <?= $item['discount_percent'] > 0 ? 'text-rose font-semibold' : 'text-ink-muted' ?>">
                        <?= $item['discount_percent'] > 0 ? ($item['discount_percent'] . '%') : '--' ?>
                      </td>
                    <?php endif; ?>
                    <?php if ($hasAnyGst): ?>
                      <?php if ($isRateList): ?>
                        <td class="text-center font-mono"><?= $item['tax_rate'] > 0 ? ($item['tax_rate'] . '%') : '--' ?></td>
                      <?php else: ?>
                        <td class="text-right text-xs">
                          <?= $item['tax_rate'] > 0 ? "₹ " . number_format($item['tax_amount'], 2) . "<br><span class='text-ink-muted text-[9px]'>(" . $item['tax_rate'] . "%)</span>" : '--' ?>
                        </td>
                      <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!$isRateList): ?>
                      <td class="text-right font-bold text-ink font-mono">₹ <?= number_format($item['total_amount'], 2) ?></td>
                    <?php else: ?>
                      <td class="text-right font-bold text-ink font-mono">
                        ₹ <?= number_format($item['post_gst'], 2) ?>
                        <?php if ($item['discount_percent'] > 0): ?>
                          <div class="text-ink-muted line-through text-[9px]">₹ <?= number_format($item['orig_post_gst'], 2) ?></div>
                        <?php endif; ?>
                      </td>
                      <td class="text-left">
                        <strong><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if ($item['subUnitText'] !== '--'): ?>
                          <div class="text-ink-muted text-[9.5px]"><?= htmlspecialchars($item['subUnitText'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="10" class="text-center py-6 text-ink-muted">No items in this list.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- 4. Summary & Bank / QR Area -->
        <div class="grid grid-cols-12 gap-4 mb-4">
          <div class="col-span-6 space-y-3">
            <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
              <div class="bank-box bank-qr-block">
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
                    <?php if (!empty($firm["bank_name"])): ?><div>Bank Name: <strong class="text-ink"><?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["bank_account_no"])): ?><div>A/C Number: <strong class="text-ink font-mono"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["bank_ifsc"])): ?><div>IFSC Code: <strong class="text-ink font-mono"><?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["upi_id"])): ?><div>UPI ID: <strong class="text-slate font-mono"><?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                  </div>
                  <?php if (!empty($firm["qr_code_path"])): ?>
                    <div class="text-center pl-3 border-l border-border flex flex-col items-center">
                      <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="Scan & Pay QR" class="rounded border border-border p-1 bg-white w-[72px] h-[72px] object-contain">
                      <span class="uppercase font-semibold text-ink-muted mt-1 text-[10px]">Scan to Pay</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($notes)): ?>
              <div class="p-2.5 border border-border rounded-lg bg-white">
                <div class="uppercase font-bold text-ink-muted mb-1 text-[11px]">Terms / Notes:</div>
                <div class="text-ink-muted text-xs leading-snug whitespace-pre-line">
                  <?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?>
                </div>
              </div>
            <?php else: ?>
              <div class="text-ink-muted text-[10px] leading-relaxed">
                * <?= $isRateList ? 'Rates mentioned above are current as of ' . htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') . ' and subject to standard terms &amp; availability.' : 'This is a computerized commercial quotation/estimate and not an official tax invoice.' ?><br>
                * Subject to standard commercial terms and stock availability.
              </div>
            <?php endif; ?>

            <?php if (!$isRateList): ?>
              <div class="text-ink-muted text-xs">
                <strong class="text-ink">Amount in Words:</strong> <?= amountToIndianWords($cleanGrandTotal) ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="col-span-6">
            <?php if (!$isRateList): ?>
              <div class="total-highlight-card">
                <table class="paper-table-reset text-xs">
                  <tr>
                    <td class="text-ink-muted p-1">Taxable Subtotal:</td>
                    <td class="text-right font-semibold font-mono p-1">₹ <?= number_format($cleanSubtotal, 2) ?></td>
                  </tr>
                  <?php if ($hasAnyDiscount && $cleanDiscTotal > 0): ?>
                    <tr>
                      <td class="text-rose p-1">Discount Total:</td>
                      <td class="text-right text-rose font-semibold font-mono p-1">- ₹ <?= number_format($cleanDiscTotal, 2) ?></td>
                    </tr>
                  <?php endif; ?>
                  <?php if ($hasAnyGst && $cleanTaxTotal > 0): ?>
                    <tr>
                      <td class="text-ink-muted p-1">Estimated GST:</td>
                      <td class="text-right font-mono p-1">₹ <?= number_format($cleanTaxTotal, 2) ?></td>
                    </tr>
                  <?php endif; ?>
                  <tr class="border-t border-border-strong">
                    <td class="p-1 pt-2 font-bold text-ink text-sm">Grand Total:</td>
                    <td class="text-right p-1 pt-2 font-mono total-amount-display">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
                  </tr>
                </table>
              </div>
            <?php else: ?>
              <div class="p-4 border border-border rounded-lg bg-[#FAF9F6] text-center">
                <div class="font-bold text-ink text-xs">Product Rate List &amp; Price Catalog</div>
                <div class="text-ink-muted text-xs mt-1">Total <?= count($normalizedItems) ?> items configured. Rates reflect current inventory catalog.</div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- 5. Signatures Footer -->
        <div class="grid grid-cols-12 pt-3 border-t border-border mt-3 items-end">
          <div class="col-span-6 text-ink-muted text-[10px]">
            <?= $isRateList ? 'Verified &amp; Issued by' : 'Customer Acceptance (Signature)' ?>
          </div>
          <div class="col-span-6 text-right">
            <div class="inline-block text-center">
              <?php if (!empty($firm["signature_path"])): ?>
                <img src="<?= $firm["signature_path"] ?>" alt="Signature" class="max-h-11 max-w-[140px] mx-auto mb-1 object-contain">
              <?php else: ?>
                <div class="h-9"></div>
              <?php endif; ?>
              <div class="sign-box">
                <div class="font-bold text-ink text-xs">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-ink-muted text-[11px]">Authorized Signatory</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- =============================================================
       2. SIMPLE FORMAT PREVIEW (PORTRAIT)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="simpleInvoiceArea" style="display: none;">
    <div class="invoice-paper simple-bill-paper" id="simplePaperArea">
      <div class="grid grid-cols-12 gap-3 items-start mb-4">
        <div class="col-span-4">
          <div class="simple-bill-header-title"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($firmPhone)): ?><div>Phone no. : <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firmAddress)): ?><div>Address : <?= htmlspecialchars(implode(', ', $firmAddress), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firmEmail)): ?><div>Email : <?= htmlspecialchars($firmEmail, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firmGstin)): ?><div>GSTIN : <?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>

        <div class="col-span-4">
          <div class="simple-bill-header-title"><?= !empty($customerName) ? htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') : 'Customer' ?></div>
          <?php if (!empty($customerName)): ?>
            <div>Customer Details : Verified</div>
          <?php endif; ?>
          <div>Place of supply: <?= !empty($firm['state']) ? htmlspecialchars($firm['state'], ENT_QUOTES, 'UTF-8') : 'Local' ?></div>
        </div>

        <div class="col-span-4 text-right">
          <div class="simple-bill-header-title"><?= $docTitle ?></div>
          <?php if (!$isRateList && !empty($estimateNumber)): ?>
            <div>Estimate No. : #<?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div>Date : <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>

      <table class="simple-bill-table">
        <thead>
          <tr>
            <th class="w-9">#</th>
            <th>Item name</th>
            <?php if (!$isRateList): ?>
              <th class="w-[70px] text-center">Qty</th>
              <th class="w-16 text-center">Unit</th>
              <th class="w-[100px] text-right">Price/Unit</th>
            <?php else: ?>
              <th class="w-[110px] text-right">Pre-GST Price</th>
            <?php endif; ?>
            <?php if ($hasAnyDiscount): ?>
              <th class="w-16 text-center">Disc %</th>
            <?php endif; ?>
            <?php if ($hasAnyGst): ?>
              <th class="w-[75px] <?= $isRateList ? 'text-center' : 'text-right' ?>"><?= $isRateList ? 'GST %' : 'GST' ?></th>
            <?php endif; ?>
            <?php if (!$isRateList): ?>
              <th class="w-[110px] text-right">Amount</th>
            <?php else: ?>
              <th class="w-[110px] text-right">Post-GST Price</th>
              <th class="w-[110px] text-center">Unit</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($normalizedItems)): ?>
            <?php foreach ($normalizedItems as $item): ?>
              <tr>
                <td><?= $item['index'] ?></td>
                <td class="font-bold"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <?php if (!$isRateList): ?>
                  <td class="text-center"><?= $item['quantity'] ?></td>
                  <td class="text-center"><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-right">₹ <?= number_format($item['rate'], 2) ?></td>
                <?php else: ?>
                  <td class="text-right font-semibold">₹ <?= number_format($item['rate'], 2) ?></td>
                <?php endif; ?>
                <?php if ($hasAnyDiscount): ?>
                  <td class="text-center"><?= $item['discount_percent'] > 0 ? ($item['discount_percent'] . '%') : '--' ?></td>
                <?php endif; ?>
                <?php if ($hasAnyGst): ?>
                  <?php if ($isRateList): ?>
                    <td class="text-center"><?= $item['tax_rate'] > 0 ? ($item['tax_rate'] . '%') : '--' ?></td>
                  <?php else: ?>
                    <td class="text-right">₹ <?= number_format($item['tax_amount'], 2) ?></td>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if (!$isRateList): ?>
                  <td class="text-right font-bold">₹ <?= number_format($item['total_amount'], 2) ?></td>
                <?php else: ?>
                  <td class="text-right font-bold">₹ <?= number_format($item['post_gst'], 2) ?></td>
                  <td class="text-center"><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if (!$isRateList): ?>
            <tr class="total-row">
              <td></td>
              <td>Total</td>
              <td class="text-center"><?= $totalQtyCount ?></td>
              <td colspan="<?= 2 + ($hasAnyDiscount ? 1 : 0) + ($hasAnyGst ? 1 : 0) ?>"></td>
              <td class="text-right">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="grid grid-cols-12 gap-4 items-start mt-4">
        <div class="col-span-6">
          <?php if (!$isRateList): ?>
            <div class="mb-2">
              <strong>Invoice Amount In Words</strong>
              <div><?= amountToIndianWords($cleanGrandTotal) ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"])): ?>
            <div class="bank-qr-block mt-3 pt-2 border-t border-border text-xs text-ink-muted">
              <strong class="text-ink">Bank Details:</strong><br>
              <?php if (!empty($firm["bank_name"])): ?>Bank: <?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?> | <?php endif; ?>
              <?php if (!empty($firm["bank_account_no"])): ?>A/C: <?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?> | <?php endif; ?>
              <?php if (!empty($firm["bank_ifsc"])): ?>IFSC: <?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-span-6 text-right">
          <?php if (!$isRateList): ?>
            <table class="paper-table-reset mb-3 ml-auto text-xs" style="max-width: 260px;">
              <tr>
                <td class="text-ink-muted p-1">Sub Total</td>
                <td class="text-right p-1">₹ <?= number_format($cleanSubtotal, 2) ?></td>
              </tr>
              <?php if ($hasAnyDiscount && $cleanDiscTotal > 0): ?>
                <tr>
                  <td class="text-rose p-1">Discount</td>
                  <td class="text-right text-rose p-1">- ₹ <?= number_format($cleanDiscTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($hasAnyGst && $cleanTaxTotal > 0): ?>
                <tr>
                  <td class="text-ink-muted p-1">GST Total</td>
                  <td class="text-right p-1">₹ <?= number_format($cleanTaxTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="simple-total-bar">
                <td class="py-1 px-2">Total</td>
                <td class="text-right py-1 px-2">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
              </tr>
            </table>
          <?php endif; ?>

          <div class="mt-4 pt-4">
            <div class="font-bold">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="h-7"></div>
            <div class="text-ink-muted text-xs">Authorized Signatory</div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- =============================================================
       3. SIMPLE HORIZONTAL 3-IN-1 SLIPS PREVIEW (LANDSCAPE)
  ============================================================= -->
  <div class="invoice-preview-container-horizontal print-template-target" id="simpleHorizontalInvoiceArea" style="display: none;">
    <div class="horizontal-tri-grid">
      <?php 
        $slipLabels = [
          'ORIGINAL / PREVIEW COPY',
          'DUPLICATE / OFFICE COPY',
          'TRIPLICATE / ARCHIVE COPY'
        ];
        for ($slipIdx = 0; $slipIdx < 3; $slipIdx++): 
      ?>
        <div class="horizontal-slip-card">
          <div class="flex items-center justify-between horizontal-slip-badge">
            <span><?= $slipLabels[$slipIdx] ?></span>
            <span><?= $docTitle ?></span>
          </div>

          <div class="flex items-start justify-between mb-1">
            <div>
              <strong class="text-[13px] text-black"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></strong>
              <?php if (!empty($firmPhone)): ?>
                <div class="text-[9.5px] text-ink-muted">Ph: <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <?php if (!empty($firmGstin)): ?>
                <div class="text-[9.5px] text-ink-muted">GSTIN: <?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            </div>
            <div class="text-right text-[10px] leading-tight">
              <?php if (!$isRateList && !empty($estimateNumber)): ?>
                <div><strong>Est #:</strong> <?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <div><strong>Date:</strong> <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>

          <?php if (!empty($customerName)): ?>
            <div class="mb-1 py-1 px-1.5 bg-[#FAF9F6] border border-border rounded text-[10px]">
              <strong>Customer:</strong> <?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <table class="horizontal-slip-table">
            <thead>
              <tr>
                <th style="width: 14px;">#</th>
                <th>Item</th>
                <?php if (!$isRateList): ?>
                  <th style="width: 25px;" class="text-center">Qty</th>
                  <th style="width: 45px;" class="text-right">Price</th>
                <?php else: ?>
                  <th style="width: 45px;" class="text-right">Pre-GST</th>
                <?php endif; ?>
                <?php if ($hasAnyDiscount): ?>
                  <th style="width: 28px;" class="text-center">Disc</th>
                <?php endif; ?>
                <?php if ($hasAnyGst): ?>
                  <th style="width: 28px;" class="text-center">GST</th>
                <?php endif; ?>
                <th style="width: 48px;" class="text-right"><?= $isRateList ? 'Post-GST' : 'Amount' ?></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($normalizedItems)): ?>
                <?php foreach ($normalizedItems as $item): ?>
                  <tr>
                    <td><?= $item['index'] ?></td>
                    <td class="font-bold break-words whitespace-normal"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if (!$isRateList): ?>
                      <td class="text-center"><?= $item['quantity'] ?></td>
                      <td class="text-right">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php else: ?>
                      <td class="text-right">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php endif; ?>
                    <?php if ($hasAnyDiscount): ?>
                      <td class="text-center"><?= $item['discount_percent'] > 0 ? ($item['discount_percent'] . '%') : '--' ?></td>
                    <?php endif; ?>
                    <?php if ($hasAnyGst): ?>
                      <td class="text-center"><?= $item['tax_rate'] > 0 ? ($item['tax_rate'] . '%') : '--' ?></td>
                    <?php endif; ?>
                    <td class="text-right font-bold">₹ <?= number_format($isRateList ? $item['post_gst'] : $item['total_amount'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <?php if (!$isRateList): ?>
            <table class="paper-table-reset ml-auto text-[10.5px]">
              <tr>
                <td class="p-0">Sub Total</td>
                <td class="text-right p-0">₹ <?= number_format($cleanSubtotal, 2) ?></td>
              </tr>
              <?php if ($hasAnyDiscount && $cleanDiscTotal > 0): ?>
                <tr>
                  <td class="p-0 text-rose">Discount</td>
                  <td class="text-right p-0 text-rose">- ₹ <?= number_format($cleanDiscTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($hasAnyGst && $cleanTaxTotal > 0): ?>
                <tr>
                  <td class="p-0">GST</td>
                  <td class="text-right p-0">₹ <?= number_format($cleanTaxTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="simple-total-bar text-[11px]">
                <td class="py-1 px-1">Total</td>
                <td class="text-right py-1 px-1">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
              </tr>
            </table>
          <?php else: ?>
            <div class="text-ink-muted text-center py-1 border-t border-border text-[9px]">
              Prices valid as of <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
            <div class="bank-qr-block horizontal-bank-qr mt-2 pt-1 border-t border-border text-[10px] leading-tight">
              <div class="flex items-center justify-between gap-1">
                <div>
                  <?php if (!empty($firm["bank_name"])): ?><div><strong>Bank:</strong> <?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  <?php if (!empty($firm["bank_account_no"])): ?><div><strong>A/C:</strong> <span class="font-mono"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                  <?php if (!empty($firm["upi_id"])): ?><div><strong>UPI:</strong> <?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <?php if (!empty($firm["qr_code_path"])): ?>
                  <div class="text-center">
                    <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="QR" class="border border-border p-0.5 bg-white rounded h-10 w-10 object-contain">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="flex items-end justify-between mt-2 pt-1 border-t border-border text-[9px]">
            <span>E.&amp;O.E.</span>
            <span>Auth. Signatory</span>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>


  <!-- =============================================================
       MODALS: HSV COLOR STUDIO & WHATSAPP SHARING
  ============================================================= -->
  <!-- HSV Custom Theme Modal -->
  <div class="rf-modal hidden" id="hsvCustomThemeModal" tabindex="-1" aria-labelledby="hsvCustomThemeModalLabel" aria-hidden="true">
    <div class="rf-modal-dialog rf-modal-dialog-lg">
      <div class="rf-modal-header">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-amber-bg border border-border text-amber flex items-center justify-center shrink-0">
            <i class="bi bi-sliders2 text-base"></i>
          </div>
          <div>
            <h5 class="text-base font-semibold text-ink mb-0" id="hsvCustomThemeModalLabel">Customise Theme Palette</h5>
            <p class="text-xs text-ink-muted mb-0">Select Hue, Saturation &amp; Brightness for document styling</p>
          </div>
        </div>
        <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>

      <div class="rf-modal-body">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <div class="lg:col-span-7 space-y-4">
            <div>
              <label class="rf-label mb-1.5">
                <i class="bi bi-palette mr-1 text-ink-muted"></i> Interactive Saturation &amp; Value Pad
              </label>
              <div id="hsvColorPad" class="hsv-color-pad">
                <div id="hsvCursor" class="hsv-cursor"></div>
              </div>
            </div>

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

          <div class="lg:col-span-5 space-y-4">
            <div class="p-4 rounded-xl text-white flex items-center justify-between shadow-sm min-h-[72px]" id="hsvCurrentColorBox" style="background-color: #5c3a21;">
              <div>
                <div class="text-[10px] uppercase tracking-wider text-white/70 font-semibold">Active Colour</div>
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
              <label class="rf-label">Quick Tuning</label>
              <div class="flex flex-wrap gap-1.5">
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('v', -5)">Darker (-5%)</button>
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('v', 5)">Brighter (+5%)</button>
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('s', -10)">Mute (-10%)</button>
                <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm text-xs" onclick="stepHsv('s', 10)">Vibrant (+10%)</button>
              </div>
            </div>

            <div>
              <label class="rf-label">Starting Swatches</label>
              <div class="flex flex-wrap gap-2">
                <button type="button" class="palette-swatch-btn" style="background: #5c3a21;" title="Classy Brown" onclick="setHsvValues(24, 68, 35)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #78350f;" title="Cognac Leather" onclick="setHsvValues(22, 88, 47)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #1e3a8a;" title="Royal Blue" onclick="setHsvValues(224, 78, 54)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #065f46;" title="Forest Emerald" onclick="setHsvValues(165, 89, 37)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #831843;" title="Imperial Ruby" onclick="setHsvValues(335, 82, 51)"></button>
                <button type="button" class="palette-swatch-btn" style="background: #0f172a;" title="Executive Slate" onclick="setHsvValues(222, 47, 16)"></button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="rf-modal-footer justify-between">
        <button type="button" class="rf-btn rf-btn-ghost rf-btn-sm" onclick="setHsvValues(24, 68, 35)">
          <i class="bi bi-arrow-counterclockwise"></i> Reset Default
        </button>
        <div class="flex items-center gap-2">
          <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Close</button>
          <button type="button" class="rf-btn rf-btn-primary rf-btn-sm" id="btnSaveHsvTheme">
            <i class="bi bi-check2-circle"></i> Apply &amp; Save
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Send WhatsApp Modal -->
  <div class="rf-modal hidden" id="sendWhatsAppModal" tabindex="-1" aria-labelledby="sendWhatsAppModalLabel" aria-hidden="true">
    <div class="rf-modal-dialog">
      <div class="rf-modal-header">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-sage-bg text-sage flex items-center justify-center shrink-0">
            <i class="bi bi-whatsapp text-base"></i>
          </div>
          <div>
            <h5 class="text-base font-semibold text-ink mb-0" id="sendWhatsAppModalLabel">Send on WhatsApp</h5>
            <p class="text-xs text-ink-muted mb-0">Share summary with customer or contacts</p>
          </div>
        </div>
        <button type="button" class="rf-modal-close" data-ui-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>
      <div class="rf-modal-body space-y-4">
        <div>
          <label class="rf-label">Recipient Mobile Number</label>
          <div class="flex items-center">
            <span class="px-3 py-2 text-xs font-mono font-semibold border border-r-0 border-border rounded-l-lg bg-canvas-alt text-ink">+91</span>
            <input type="tel" id="waCustomPhoneInput" class="rf-input font-mono font-semibold rounded-l-none" placeholder="9876543210" maxlength="10">
          </div>
        </div>
        <div>
          <label class="rf-label">Message Preview</label>
          <textarea id="waMessageText" class="rf-textarea font-mono text-xs bg-canvas-alt/60" rows="5"></textarea>
        </div>
        <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-lg w-full justify-center" id="btnLaunchWhatsApp">
          <i class="bi bi-whatsapp"></i> Open WhatsApp Web / App
        </button>
      </div>
    </div>
  </div>

  <script>
    window.RACE_INVOICE_DOWNLOAD = {
      invoiceNumber: <?= json_encode($estimateNumber ?: 'QUOTATION') ?>,
      whatsAppMessage: <?= json_encode($waDefaultMessage) ?>
    };
  </script>
  <script src="/js/ui.js"></script>
  <script src="/js/invoice-download.js"></script>
</body>
</html>
