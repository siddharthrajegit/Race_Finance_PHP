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

// -------------------------------------------------------------
// Calculate and normalize items + check if ANY GST or DISCOUNT exists
// -------------------------------------------------------------
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

    // Rate list calculations (per unit)
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
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <!-- html2pdf.js for instant PDF generation -->
  <script src="/vendor/html2pdf/html2pdf.bundle.min.js"></script>
  <script>if(typeof html2pdf==='undefined'){document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');}</script>
  <link rel="stylesheet" href="/css/invoice-download.css?v=2">

  <style>
    /* Customer Single Line Bar */
    .customer-single-line {
      background-color: var(--theme-light, #f8fafc);
      border: 1px solid var(--theme-border, #e2e8f0);
      border-left: 4px solid var(--theme-primary, #0f172a);
      padding: 6px 12px;
      margin-bottom: 14px;
      font-size: 12px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      border-radius: 4px;
    }
  </style>
</head>
<body class="print-mode-detailed">

  <!-- Top Action Toolbar -->
  <header class="top-action-bar no-print">
    <div class="container-fluid px-lg-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <!-- Left: Back link and Template Selector -->
      <div class="d-flex align-items-center gap-3">
        <a href="/estimates" class="btn btn-outline-light btn-sm d-flex align-items-center rounded-pill px-3">
          <i class="bi bi-arrow-left me-1"></i> Back
        </a>

        <!-- Template Layout Selector Buttons -->
        <div class="template-switcher-bar">
          <button type="button" class="template-btn active" id="tabDetailed" onclick="switchInvoiceTemplate('detailed')">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> <?= $isRateList ? 'Detailed Rate List' : 'Detailed Quotation' ?>
          </button>
          <button type="button" class="template-btn" id="tabSimple" onclick="switchInvoiceTemplate('simple')">
            <i class="bi bi-file-text me-1"></i> Simple (Portrait)
          </button>
          <button type="button" class="template-btn" id="tabHorizontal" onclick="switchInvoiceTemplate('horizontal')">
            <i class="bi bi-layout-three-columns me-1"></i> Horizontal (3-in-1)
          </button>
        </div>
      </div>

      <!-- Right: Action Buttons -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Theme Selector Dropdown -->
        <div class="dropdown">
          <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center rounded-pill px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-palette me-1 text-info"></i> Theme
          </button>
          <ul class="dropdown-menu dropdown-menu-dark shadow-sm border-secondary py-1" style="min-width: 230px;">
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('brown')">
                <span class="theme-dot" style="background: #5c3a21;"></span> Classy Espresso Brown
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('leather')">
                <span class="theme-dot" style="background: #78350f;"></span> Warm Cognac Leather
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('blue')">
                <span class="theme-dot" style="background: #1e3a8a;"></span> Classic Vyapar Blue
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('emerald')">
                <span class="theme-dot" style="background: #065f46;"></span> Royal Emerald
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('slate')">
                <span class="theme-dot" style="background: #0f172a;"></span> Executive Slate
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('crimson')">
                <span class="theme-dot" style="background: #831843;"></span> Imperial Crimson
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="setInvoiceTheme('purple')">
                <span class="theme-dot" style="background: #4c1d95;"></span> Regal Purple
              </button>
            </li>
            <li><hr class="dropdown-divider border-secondary my-1"></li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center text-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#hsvCustomThemeModal">
                <i class="bi bi-sliders2 me-2"></i> Customise HSV Colour...
              </button>
            </li>
          </ul>
        </div>

        <!-- Customise HSV Theme Quick Button -->
        <button class="btn btn-outline-warning btn-sm d-flex align-items-center rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#hsvCustomThemeModal" title="Set exact HSV colour">
          <i class="bi bi-sliders2 me-1"></i> Customise
        </button>

        <!-- Bank Details & QR Code Toggle -->
        <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
          <button type="button" class="btn btn-outline-light btn-sm d-flex align-items-center rounded-pill px-3 shadow-sm" id="btnToggleBankQr" onclick="toggleBankQr()" title="Show or hide Bank Details and QR Code">
            <i class="bi bi-qr-code me-1 text-info" id="bankQrToggleIcon"></i>
            <span id="bankQrToggleText">Hide Bank &amp; QR</span>
          </button>
        <?php endif; ?>

        <!-- Download PDF Button -->
        <button id="btnDownloadPdf" class="btn btn-primary btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center">
          <i class="bi bi-download me-1"></i> Download PDF
        </button>

        <!-- Print Options Dropdown -->
        <div class="dropdown">
          <button class="btn btn-secondary btn-sm fw-semibold dropdown-toggle rounded-pill px-3 shadow-sm d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-printer me-1"></i> Print
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1">
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="triggerPrintMode('detailed')">
                <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i> Print Detailed <?= $isRateList ? 'Rate List' : 'Quotation' ?> (Portrait)
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="triggerPrintMode('simple')">
                <i class="bi bi-file-text text-success me-2"></i> Print Simple Format (Portrait)
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="triggerPrintMode('horizontal')">
                <i class="bi bi-layout-three-columns text-warning-emphasis me-2"></i> Print Simple Slips (Horizontal 3-in-1)
              </button>
            </li>
          </ul>
        </div>

        <!-- Send WhatsApp Modal Trigger -->
        <button type="button" class="btn btn-success btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#sendWhatsAppModal" title="Send details on WhatsApp">
          <i class="bi bi-whatsapp me-1"></i> WhatsApp
        </button>
      </div>
    </div>
  </header>


  <!-- =============================================================
       1. DETAILED PREVIEW (VYAPAR / HIGH-END STYLE)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="detailedInvoiceArea">
    <div class="invoice-paper" id="detailedPaperArea">
      <!-- Top Colored Accent Bar -->
      <div class="vyapar-top-strip"></div>

      <div class="invoice-inner">
        <!-- 1. Header: Firm Profile & Document Metadata -->
        <div class="row align-items-start mb-4 pb-3 border-bottom">
          <!-- Left: Business Info -->
          <div class="col-7">
            <div class="d-flex align-items-center mb-2">
              <?php if (!empty($firm["logo_path"])): ?>
                <img src="<?= $firm["logo_path"] ?>" alt="<?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?>" class="rounded border p-1 me-3 bg-white" style="max-height: 52px; max-width: 90px; object-fit: contain;">
              <?php endif; ?>
              <div>
                <h3 class="firm-name mb-0"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (!empty($firmGstin)): ?>
                  <div class="small fw-semibold text-muted font-monospace mt-1">
                    GSTIN: <span class="text-dark"><?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="small text-muted pe-3" style="font-size: 0.82rem; line-height: 1.45;">
              <?php if (!empty($firmAddress)): ?><div><?= htmlspecialchars(implode(', ', $firmAddress), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firmPhone)): ?><div><i class="bi bi-telephone text-secondary me-1"></i> <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firmEmail)): ?><div><i class="bi bi-envelope text-secondary me-1"></i> <?= htmlspecialchars($firmEmail, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
          </div>

          <!-- Right: Document Title & Metadata -->
          <div class="col-5 text-end">
            <div class="invoice-title-badge mb-2">
              <?= $docTitle ?>
            </div>
            <div class="small text-muted" style="font-size: 0.84rem;">
              <?php if (!$isRateList && !empty($estimateNumber)): ?>
                <div><strong>Estimate No:</strong> <span class="font-monospace fw-bold text-dark">#<?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php endif; ?>
              <div><strong>Date:</strong> <span class="text-dark"><?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php if (!empty($customerName)): ?>
                <div class="mt-1">
                  <strong>Customer:</strong> <span class="fw-bold text-dark"><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Optional Customer Line Bar (shown if customerName provided) -->
        <?php if (!empty($customerName)): ?>
          <div class="customer-single-line">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.5px;">Customer:</span>
            <strong class="text-dark ms-1" style="font-size: 13px;"><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
        <?php endif; ?>

        <!-- Line Items Table -->
        <div class="table-responsive mb-4">
          <table class="vyapar-table">
            <thead>
              <tr class="text-center">
                <th style="width: 35px;">#</th>
                <th class="text-start"><?= $isRateList ? 'Item / Product Name' : 'Item Description' ?></th>
                <th style="width: 75px;">HSN/SAC</th>
                <?php if (!$isRateList): ?>
                  <th style="width: 55px;">Qty</th>
                  <th style="width: 55px;">Unit</th>
                  <th style="width: 85px;" class="text-end">Rate (₹)</th>
                <?php else: ?>
                  <th style="width: 95px;" class="text-end">Pre-GST Price</th>
                <?php endif; ?>
                <!-- Conditional Discount Header (Removed if all are 0) -->
                <?php if ($hasAnyDiscount): ?>
                  <th style="width: 65px;" class="text-center">Disc %</th>
                <?php endif; ?>
                <!-- Conditional GST Header (Removed if all are 0) -->
                <?php if ($hasAnyGst): ?>
                  <th style="width: 65px;" class="<?= $isRateList ? 'text-center' : 'text-end' ?>"><?= $isRateList ? 'GST %' : 'GST' ?></th>
                <?php endif; ?>
                <?php if (!$isRateList): ?>
                  <th style="width: 95px;" class="text-end">Amount (₹)</th>
                <?php else: ?>
                  <th style="width: 95px;" class="text-end">Post-GST Price</th>
                  <th style="width: 135px;" class="text-start">Unit &amp; Packaging</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($normalizedItems)): ?>
                <?php foreach ($normalizedItems as $item): ?>
                  <tr>
                    <td class="text-center text-muted small"><?= $item['index'] ?></td>
                    <td>
                      <div class="fw-semibold text-dark"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php if (!empty($item['category'])): ?>
                        <span class="badge bg-light text-muted border" style="font-size: 8px;"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center font-monospace small text-muted"><?= htmlspecialchars($item['hsn_code'] ?: '--', ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if (!$isRateList): ?>
                      <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                      <td class="text-center text-muted small"><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="text-end font-monospace">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php else: ?>
                      <td class="text-end font-monospace fw-semibold">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php endif; ?>
                    <!-- Conditional Discount Column Value -->
                    <?php if ($hasAnyDiscount): ?>
                      <td class="text-center <?= $item['discount_percent'] > 0 ? 'text-danger fw-semibold' : 'text-muted' ?>">
                        <?= $item['discount_percent'] > 0 ? ($item['discount_percent'] . '%') : '--' ?>
                      </td>
                    <?php endif; ?>
                    <!-- Conditional GST Column Value -->
                    <?php if ($hasAnyGst): ?>
                      <?php if ($isRateList): ?>
                        <td class="text-center font-monospace"><?= $item['tax_rate'] > 0 ? ($item['tax_rate'] . '%') : '--' ?></td>
                      <?php else: ?>
                        <td class="text-end small">
                          <?= $item['tax_rate'] > 0 ? "₹ " . number_format($item['tax_amount'], 2) . "<br><span class='text-muted' style='font-size: 8px;'>(" . $item['tax_rate'] . "%)</span>" : '--' ?>
                        </td>
                      <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!$isRateList): ?>
                      <td class="text-end fw-bold text-dark font-monospace">₹ <?= number_format($item['total_amount'], 2) ?></td>
                    <?php else: ?>
                      <td class="text-end fw-bold text-dark font-monospace">
                        ₹ <?= number_format($item['post_gst'], 2) ?>
                        <?php if ($item['discount_percent'] > 0): ?>
                          <div class="text-muted text-decoration-line-through small" style="font-size: 8.5px;">₹ <?= number_format($item['orig_post_gst'], 2) ?></div>
                        <?php endif; ?>
                      </td>
                      <td class="text-start">
                        <strong><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if ($item['subUnitText'] !== '--'): ?>
                          <div class="text-muted" style="font-size: 9.5px;"><?= htmlspecialchars($item['subUnitText'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="10" class="text-center py-4 text-muted">No items in this list.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- 4. Summary & Bank / QR Area -->
        <div class="row g-4 mb-4">
          <!-- Left Column: Bank / QR & Notes -->
          <div class="col-md-6">
            <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
              <div class="bank-box bank-qr-block mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="small fw-bold text-uppercase text-dark" style="font-size: 0.75rem;">
                    <i class="bi bi-bank2 text-primary me-1"></i> Bank &amp; Payment Details
                  </div>
                  <?php if (!empty($firm["upi_id"])): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">UPI Accepted</span>
                  <?php endif; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                  <div class="small text-muted flex-grow-1" style="font-size: 0.82rem; line-height: 1.45;">
                    <?php if (!empty($firm["bank_name"])): ?><div>Bank Name: <strong class="text-dark"><?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["bank_account_no"])): ?><div>A/C Number: <strong class="text-dark font-monospace"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["bank_ifsc"])): ?><div>IFSC Code: <strong class="text-dark font-monospace"><?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                    <?php if (!empty($firm["upi_id"])): ?><div>UPI ID: <strong class="text-primary font-monospace"><?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
                  </div>
                  <?php if (!empty($firm["qr_code_path"])): ?>
                    <div class="text-center ps-3 border-start d-flex flex-column align-items-center">
                      <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="Scan & Pay QR" class="rounded border p-1 bg-white" style="width: 72px; height: 72px; object-fit: contain;">
                      <span class="text-uppercase fw-semibold text-muted mt-1" style="font-size: 0.62rem;">Scan to Pay</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($notes)): ?>
              <div class="p-2 border rounded bg-white mb-2">
                <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.7rem;">Terms / Notes:</div>
                <div class="small text-muted" style="font-size: 0.76rem; line-height: 1.35; white-space: pre-line;">
                  <?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?>
                </div>
              </div>
            <?php else: ?>
              <div class="text-muted small" style="font-size: 9.5px; line-height: 1.4;">
                * <?= $isRateList ? 'Rates mentioned above are current as of ' . htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') . ' and subject to standard terms & availability.' : 'This is a computerized commercial quotation/estimate and not an official tax invoice.' ?><br>
                * Subject to standard commercial terms and stock availability.
              </div>
            <?php endif; ?>

            <?php if (!$isRateList): ?>
              <div class="mt-2 text-muted small" style="font-size: 0.78rem;">
                <strong>Amount in Words:</strong> <?= amountToIndianWords($cleanGrandTotal) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Right Column: Calculation Summary (Only for Estimate Mode or Grand Total) -->
          <div class="col-md-6">
            <?php if (!$isRateList): ?>
              <div class="total-highlight-card">
                <table class="table table-borderless table-sm mb-0" style="font-size: 0.84rem;">
                  <tr>
                    <td class="text-muted p-1">Taxable Subtotal:</td>
                    <td class="text-end fw-semibold p-1">₹ <?= number_format($cleanSubtotal, 2) ?></td>
                  </tr>
                  <!-- Conditional Discount Summary Row (Removed if 0) -->
                  <?php if ($hasAnyDiscount && $cleanDiscTotal > 0): ?>
                    <tr>
                      <td class="text-danger p-1">Discount Total:</td>
                      <td class="text-end text-danger fw-semibold p-1">- ₹ <?= number_format($cleanDiscTotal, 2) ?></td>
                    </tr>
                  <?php endif; ?>
                  <!-- Conditional GST Summary Row (Removed if 0) -->
                  <?php if ($hasAnyGst && $cleanTaxTotal > 0): ?>
                    <tr>
                      <td class="text-muted p-1">Estimated GST:</td>
                      <td class="text-end p-1">₹ <?= number_format($cleanTaxTotal, 2) ?></td>
                    </tr>
                  <?php endif; ?>
                  <tr class="border-top border-secondary-subtle">
                    <td class="p-1 pt-2 fw-bold text-dark fs-6">Grand Total:</td>
                    <td class="text-end p-1 pt-2 font-monospace total-amount-display">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
                  </tr>
                </table>
              </div>
            <?php else: ?>
              <div class="p-3 border rounded bg-light text-center">
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">Product Rate List &amp; Price Catalog</div>
                <div class="text-muted small mt-1" style="font-size: 0.75rem;">Total <?= count($normalizedItems) ?> items configured. Rates reflect current inventory catalog.</div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- 5. Signatures Footer -->
        <div class="row pt-3 border-top mt-3">
          <div class="col-6 text-muted" style="font-size: 10px;">
            <?= $isRateList ? 'Verified &amp; Issued by' : 'Customer Acceptance (Signature)' ?>
          </div>
          <div class="col-6 text-end">
            <div class="d-inline-block text-center">
              <?php if (!empty($firm["signature_path"])): ?>
                <img src="<?= $firm["signature_path"] ?>" alt="Signature" style="max-height: 44px; max-width: 140px;" class="mb-1">
              <?php else: ?>
                <div style="height: 36px;"></div>
              <?php endif; ?>
              <div class="sign-box">
                <small class="fw-bold text-dark d-block" style="font-size: 0.78rem;">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></small>
                <small class="text-muted" style="font-size: 0.7rem;">Authorized Signatory</small>
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
      <!-- Top 3 Columns: Seller, Customer, Details -->
      <div class="row align-items-start mb-4">
        <!-- 1. Seller Column -->
        <div class="col-4">
          <div class="simple-bill-header-title"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($firmPhone)): ?><div>Phone no. : <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firmAddress)): ?><div>Address : <?= htmlspecialchars(implode(', ', $firmAddress), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firmEmail)): ?><div>Email : <?= htmlspecialchars($firmEmail, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($firmGstin)): ?><div>GSTIN : <?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>

        <!-- 2. Customer Column -->
        <div class="col-4">
          <div class="simple-bill-header-title"><?= !empty($customerName) ? htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') : 'Customer' ?></div>
          <?php if (!empty($customerName)): ?>
            <div>Customer Details : Verified</div>
          <?php endif; ?>
          <div>Place of supply: <?= !empty($firm['state']) ? htmlspecialchars($firm['state'], ENT_QUOTES, 'UTF-8') : 'Local' ?></div>
        </div>

        <!-- 3. Document Details Column -->
        <div class="col-4 text-end">
          <div class="simple-bill-header-title"><?= $docTitle ?></div>
          <?php if (!$isRateList && !empty($estimateNumber)): ?>
            <div>Estimate No. : #<?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div>Date : <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>

      <!-- Simple Items Table -->
      <table class="simple-bill-table">
        <thead>
          <tr>
            <th style="width: 35px;">#</th>
            <th>Item name</th>
            <?php if (!$isRateList): ?>
              <th style="width: 70px;" class="text-center">Qty</th>
              <th style="width: 60px;" class="text-center">Unit</th>
              <th style="width: 100px;" class="text-end">Price/Unit</th>
            <?php else: ?>
              <th style="width: 110px;" class="text-end">Pre-GST Price</th>
            <?php endif; ?>
            <!-- Conditional Discount Header -->
            <?php if ($hasAnyDiscount): ?>
              <th style="width: 65px;" class="text-center">Disc %</th>
            <?php endif; ?>
            <!-- Conditional GST Header -->
            <?php if ($hasAnyGst): ?>
              <th style="width: 75px;" class="<?= $isRateList ? 'text-center' : 'text-end' ?>"><?= $isRateList ? 'GST %' : 'GST' ?></th>
            <?php endif; ?>
            <?php if (!$isRateList): ?>
              <th style="width: 110px;" class="text-end">Amount</th>
            <?php else: ?>
              <th style="width: 110px;" class="text-end">Post-GST Price</th>
              <th style="width: 110px;" class="text-center">Unit</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($normalizedItems)): ?>
            <?php foreach ($normalizedItems as $item): ?>
              <tr>
                <td><?= $item['index'] ?></td>
                <td class="fw-bold"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <?php if (!$isRateList): ?>
                  <td class="text-center"><?= $item['quantity'] ?></td>
                  <td class="text-center"><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-end">₹ <?= number_format($item['rate'], 2) ?></td>
                <?php else: ?>
                  <td class="text-end fw-semibold">₹ <?= number_format($item['rate'], 2) ?></td>
                <?php endif; ?>
                <!-- Conditional Discount Cell -->
                <?php if ($hasAnyDiscount): ?>
                  <td class="text-center"><?= $item['discount_percent'] > 0 ? ($item['discount_percent'] . '%') : '--' ?></td>
                <?php endif; ?>
                <!-- Conditional GST Cell -->
                <?php if ($hasAnyGst): ?>
                  <?php if ($isRateList): ?>
                    <td class="text-center"><?= $item['tax_rate'] > 0 ? ($item['tax_rate'] . '%') : '--' ?></td>
                  <?php else: ?>
                    <td class="text-end">₹ <?= number_format($item['tax_amount'], 2) ?></td>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if (!$isRateList): ?>
                  <td class="text-end fw-bold">₹ <?= number_format($item['total_amount'], 2) ?></td>
                <?php else: ?>
                  <td class="text-end fw-bold">₹ <?= number_format($item['post_gst'], 2) ?></td>
                  <td class="text-center"><?= htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if (!$isRateList): ?>
            <!-- Total Row in Simple Table -->
            <tr class="total-row">
              <td></td>
              <td>Total</td>
              <td class="text-center"><?= $totalQtyCount ?></td>
              <td colspan="<?= 2 + ($hasAnyDiscount ? 1 : 0) + ($hasAnyGst ? 1 : 0) ?>"></td>
              <td class="text-end">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Simple Breakdown & Signatures -->
      <div class="row align-items-start mt-3">
        <div class="col-6">
          <?php if (!$isRateList): ?>
            <div class="mb-2">
              <strong>Invoice Amount In Words</strong>
              <div><?= amountToIndianWords($cleanGrandTotal) ?></div>
            </div>
          <?php endif; ?>

          <!-- Bank info for simple bill -->
          <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"])): ?>
            <div class="bank-qr-block mt-3 pt-2 border-top small text-muted">
              <strong>Bank Details:</strong><br>
              <?php if (!empty($firm["bank_name"])): ?>Bank: <?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?> | <?php endif; ?>
              <?php if (!empty($firm["bank_account_no"])): ?>A/C: <?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?> | <?php endif; ?>
              <?php if (!empty($firm["bank_ifsc"])): ?>IFSC: <?= htmlspecialchars($firm["bank_ifsc"], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-6 text-end">
          <?php if (!$isRateList): ?>
            <table class="table table-borderless table-sm mb-3 ms-auto" style="max-width: 260px;">
              <tr>
                <td class="text-muted p-1">Sub Total</td>
                <td class="text-end p-1">₹ <?= number_format($cleanSubtotal, 2) ?></td>
              </tr>
              <!-- Conditional Discount Line -->
              <?php if ($hasAnyDiscount && $cleanDiscTotal > 0): ?>
                <tr>
                  <td class="text-danger p-1">Discount</td>
                  <td class="text-end text-danger p-1">- ₹ <?= number_format($cleanDiscTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <!-- Conditional GST Line -->
              <?php if ($hasAnyGst && $cleanTaxTotal > 0): ?>
                <tr>
                  <td class="text-muted p-1">GST Total</td>
                  <td class="text-end p-1">₹ <?= number_format($cleanTaxTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="simple-total-bar">
                <td class="py-1 px-2">Total</td>
                <td class="text-end py-1 px-2">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
              </tr>
            </table>
          <?php endif; ?>

          <div class="mt-4 pt-4">
            <div class="fw-bold">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
            <div style="height: 30px;"></div>
            <div class="text-muted small">Authorized Signatory</div>
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
          <!-- Slip Badge -->
          <div class="d-flex justify-content-between align-items-center horizontal-slip-badge">
            <span><?= $slipLabels[$slipIdx] ?></span>
            <span><?= $docTitle ?></span>
          </div>

          <!-- Header: Firm & Doc Meta -->
          <div class="d-flex justify-content-between align-items-start mb-1">
            <div>
              <strong style="font-size: 0.82rem; color: #000;"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></strong>
              <?php if (!empty($firmPhone)): ?>
                <div style="font-size: 0.6rem; color: #475569;">Ph: <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <?php if (!empty($firmGstin)): ?>
                <div style="font-size: 0.6rem; color: #475569;">GSTIN: <?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            </div>
            <div class="text-end" style="font-size: 0.62rem; line-height: 1.25;">
              <?php if (!$isRateList && !empty($estimateNumber)): ?>
                <div><strong>Est #:</strong> <?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <div><strong>Date:</strong> <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>

          <?php if (!empty($customerName)): ?>
            <div class="mb-1 py-1 px-1 bg-light border rounded" style="font-size: 0.62rem;">
              <strong>Customer:</strong> <?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <!-- Slip Items Table -->
          <table class="horizontal-slip-table">
            <thead>
              <tr>
                <th style="width: 14px;">#</th>
                <th>Item</th>
                <?php if (!$isRateList): ?>
                  <th style="width: 25px;" class="text-center">Qty</th>
                  <th style="width: 45px;" class="text-end">Price</th>
                <?php else: ?>
                  <th style="width: 45px;" class="text-end">Pre-GST</th>
                <?php endif; ?>
                <!-- Conditional Disc -->
                <?php if ($hasAnyDiscount): ?>
                  <th style="width: 28px;" class="text-center">Disc</th>
                <?php endif; ?>
                <!-- Conditional GST -->
                <?php if ($hasAnyGst): ?>
                  <th style="width: 28px;" class="text-center">GST</th>
                <?php endif; ?>
                <th style="width: 48px;" class="text-end"><?= $isRateList ? 'Post-GST' : 'Amount' ?></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($normalizedItems)): ?>
                <?php foreach ($normalizedItems as $item): ?>
                  <tr>
                    <td><?= $item['index'] ?></td>
                    <td class="fw-bold" style="word-break: break-word; white-space: normal;"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if (!$isRateList): ?>
                      <td class="text-center"><?= $item['quantity'] ?></td>
                      <td class="text-end">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php else: ?>
                      <td class="text-end">₹ <?= number_format($item['rate'], 2) ?></td>
                    <?php endif; ?>
                    <?php if ($hasAnyDiscount): ?>
                      <td class="text-center"><?= $item['discount_percent'] > 0 ? ($item['discount_percent'] . '%') : '--' ?></td>
                    <?php endif; ?>
                    <?php if ($hasAnyGst): ?>
                      <td class="text-center"><?= $item['tax_rate'] > 0 ? ($item['tax_rate'] . '%') : '--' ?></td>
                    <?php endif; ?>
                    <td class="text-end fw-bold">₹ <?= number_format($isRateList ? $item['post_gst'] : $item['total_amount'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <!-- Slip Summary Breakdown -->
          <?php if (!$isRateList): ?>
            <table class="table table-borderless table-sm mb-0 ms-auto" style="font-size: 0.65rem;">
              <tr>
                <td class="p-0">Sub Total</td>
                <td class="text-end p-0">₹ <?= number_format($cleanSubtotal, 2) ?></td>
              </tr>
              <?php if ($hasAnyDiscount && $cleanDiscTotal > 0): ?>
                <tr>
                  <td class="p-0 text-danger">Discount</td>
                  <td class="text-end p-0 text-danger">- ₹ <?= number_format($cleanDiscTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($hasAnyGst && $cleanTaxTotal > 0): ?>
                <tr>
                  <td class="p-0">GST</td>
                  <td class="text-end p-0">₹ <?= number_format($cleanTaxTotal, 2) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="simple-total-bar" style="font-size: 0.67rem;">
                <td class="py-1 px-1">Total</td>
                <td class="text-end py-1 px-1">₹ <?= number_format($cleanGrandTotal, 2) ?></td>
              </tr>
            </table>
          <?php else: ?>
            <div class="text-muted text-center py-1 border-top" style="font-size: 0.58rem;">
              Prices valid as of <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <!-- Bank & QR for Horizontal Slip -->
          <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"]) || !empty($firm["qr_code_path"])): ?>
            <div class="bank-qr-block horizontal-bank-qr mt-2 pt-1 border-top" style="font-size: 0.62rem; line-height: 1.25;">
              <div class="d-flex align-items-center justify-content-between gap-1">
                <div>
                  <?php if (!empty($firm["bank_name"])): ?><div><strong>Bank:</strong> <?= htmlspecialchars($firm["bank_name"], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  <?php if (!empty($firm["bank_account_no"])): ?><div><strong>A/C:</strong> <span class="font-monospace"><?= htmlspecialchars($firm["bank_account_no"], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                  <?php if (!empty($firm["upi_id"])): ?><div><strong>UPI:</strong> <?= htmlspecialchars($firm["upi_id"], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <?php if (!empty($firm["qr_code_path"])): ?>
                  <div class="text-center">
                    <img src="<?= htmlspecialchars($firm["qr_code_path"], ENT_QUOTES, 'UTF-8') ?>" alt="QR" class="border p-1 bg-white rounded" style="height: 40px; width: 40px; object-fit: contain;">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Signatory Line -->
          <div class="d-flex justify-content-between align-items-end mt-2 pt-1 border-top" style="font-size: 0.58rem;">
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
  <div class="modal fade" id="hsvCustomThemeModal" tabindex="-1" aria-labelledby="hsvCustomThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
        <div class="modal-header border-0 bg-dark text-white py-3 px-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 38px; height: 38px;">
              <i class="bi bi-sliders2 fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0" id="hsvCustomThemeModalLabel">Customise Theme Palette</h5>
              <small class="text-white-50">Select Hue, Saturation &amp; Brightness for document styling</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4 bg-white">
          <div class="row g-4">
            <!-- Left Column: 2D Interactive HSV Pad & Sliders -->
            <div class="col-lg-7">
              <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">
                  <i class="bi bi-palette text-primary me-1"></i> Interactive Saturation &amp; Value Pad
                </label>
                <div id="hsvColorPad" class="hsv-color-pad">
                  <div id="hsvCursor" class="hsv-cursor"></div>
                </div>
              </div>

              <!-- Hue (H) Slider: 0 - 360° -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-bold text-dark small mb-0">
                    <i class="bi bi-circle-half text-danger me-1"></i> Hue (H) &mdash; Color Spectrum
                  </label>
                  <div class="input-group input-group-sm" style="width: 90px;">
                    <input type="number" id="hsvHNum" class="form-control form-control-sm text-end fw-bold font-monospace" min="0" max="360" value="28">
                    <span class="input-group-text py-0 px-2 small">°</span>
                  </div>
                </div>
                <input type="range" id="hsvHSlider" class="hsv-slider-hue" min="0" max="360" value="28">
              </div>

              <!-- Saturation (S) Slider: 0 - 100% -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-bold text-dark small mb-0">
                    <i class="bi bi-droplet-half text-primary me-1"></i> Saturation (S) &mdash; Chroma / Intensity
                  </label>
                  <div class="input-group input-group-sm" style="width: 90px;">
                    <input type="number" id="hsvSNum" class="form-control form-control-sm text-end fw-bold font-monospace" min="0" max="100" value="64">
                    <span class="input-group-text py-0 px-2 small">%</span>
                  </div>
                </div>
                <input type="range" id="hsvSSlider" class="hsv-slider-bar" min="0" max="100" value="64">
              </div>

              <!-- Value (V) Slider: 0 - 100% -->
              <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-bold text-dark small mb-0">
                    <i class="bi bi-brightness-high text-warning me-1"></i> Value / Brightness (V)
                  </label>
                  <div class="input-group input-group-sm" style="width: 90px;">
                    <input type="number" id="hsvVNum" class="form-control form-control-sm text-end fw-bold font-monospace" min="0" max="100" value="36">
                    <span class="input-group-text py-0 px-2 small">%</span>
                  </div>
                </div>
                <input type="range" id="hsvVSlider" class="hsv-slider-bar" min="0" max="100" value="36">
              </div>
            </div>

            <!-- Right Column: Exact Color Codes & Swatches -->
            <div class="col-lg-5">
              <div class="p-3 rounded-3 mb-3 text-white d-flex align-items-center justify-content-between shadow-sm" id="hsvCurrentColorBox" style="background-color: #5c3a21; min-height: 70px;">
                <div>
                  <div class="small text-uppercase text-white-50 fw-bold" style="font-size: 0.7rem;">Active Colour</div>
                  <div class="fs-4 fw-bold font-monospace" id="hsvHexDisplay">#5C3A21</div>
                </div>
                <div class="text-end font-monospace small">
                  <div id="hsvRgbDisplay">rgb(92, 58, 33)</div>
                  <div class="text-white-50" id="hsvValDisplay">H:28° S:64% V:36%</div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-dark mb-1">Enter Exact HEX Code:</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-white font-monospace">#</span>
                  <input type="text" class="form-control font-monospace fw-bold" id="hsvHexInput" value="5C3A21" maxlength="6">
                  <button class="btn btn-outline-secondary" type="button" id="btnApplyHexInput">Set</button>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-dark mb-1">Quick Tuning:</label>
                <div class="d-flex flex-wrap gap-1">
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('v', -5)">Darker (-5%)</button>
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('v', 5)">Brighter (+5%)</button>
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('s', -10)">Mute (-10%)</button>
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('s', 10)">Vibrant (+10%)</button>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label fw-bold small text-dark mb-1">Starting Swatches:</label>
                <div class="d-flex flex-wrap gap-2">
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

        <div class="modal-footer border-0 bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="setHsvValues(24, 68, 35)">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Default
          </button>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-sm" id="btnSaveHsvTheme">
              <i class="bi bi-check2-circle me-1"></i> Apply &amp; Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Send WhatsApp Modal -->
  <div class="modal fade" id="sendWhatsAppModal" tabindex="-1" aria-labelledby="sendWhatsAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
        <div class="modal-header border-0 bg-success text-white py-3 px-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
              <i class="bi bi-whatsapp fs-4"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0" id="sendWhatsAppModalLabel">Send on WhatsApp</h5>
              <small class="text-white-50">Share summary with customer or contacts</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Recipient Mobile Number:</label>
            <div class="input-group">
              <span class="input-group-text bg-light fw-bold">+91</span>
              <input type="tel" id="waCustomPhoneInput" class="form-control form-control-lg font-monospace fw-bold" placeholder="9876543210" maxlength="10">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Message Preview:</label>
            <textarea id="waMessageText" class="form-control font-monospace small" rows="5"></textarea>
          </div>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-success btn-lg fw-bold shadow-sm d-flex align-items-center justify-content-center" id="btnLaunchWhatsApp">
              <i class="bi bi-whatsapp fs-5 me-2"></i> Open WhatsApp Web / App
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 Bundle JS -->
  <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>if(typeof bootstrap==='undefined'){document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>');}</script>
  <script>
    window.RACE_INVOICE_DOWNLOAD = {
      invoiceNumber: <?= json_encode($estimateNumber ?: 'QUOTATION') ?>,
      whatsAppMessage: <?= json_encode($waDefaultMessage) ?>
    };
  </script>
  <script src="/js/invoice-download.js"></script>
</body>
</html>
