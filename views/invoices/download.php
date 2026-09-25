<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Download Bill - <?= $invoice["invoice_number"] ?> | RACE FINANCE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <!-- Bootstrap Icons (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <!-- html2pdf.js for instant PDF generation (Offline Local with CDN Fallback) -->
  <script src="/vendor/html2pdf/html2pdf.bundle.min.js"></script>
  <script>if(typeof html2pdf==='undefined'){document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');}</script>
  <link rel="stylesheet" href="/css/invoice-download.css">
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
$$totalQtyCount = 0;
$hasDiscount = false;
foreach ($items as $it) {
    $$totalQtyCount += (float)($it["quantity"] ?? 0);
    if (!empty($it["discount_percent"]) && (float)$it["discount_percent"] > 0) {
        $hasDiscount = true;
    }
}
$firstTaxRate = !empty($items[0]["tax_rate"]) ? (float)$items[0]["tax_rate"] : 18;

$$cleanTaxable = (float)($invoice["taxable_amount"] ?? ($invoice["subtotal"] ?? 0));
$$cleanCgst = (float)($invoice["cgst_amount"] ?? 0);
$$cleanSgst = (float)($invoice["sgst_amount"] ?? 0);
$$cleanIgst = (float)($invoice["igst_amount"] ?? ($invoice["tax_amount"] ?? 0));
$$cleanRoundOff = (float)($invoice["round_off"] ?? 0);
$$cleanGrandTotal = (float)($invoice["grand_total"] ?? 0);
$$cleanPaid = (float)($invoice["paid_amount"] ?? 0);
$$cleanDue = (float)($invoice["balance_due"] ?? 0);
$$wordsTotal = amountToIndianWords($$cleanGrandTotal);

$firmName = $firm["name"] ?? "RACE FINANCE";
$invNum = $invoice["invoice_number"] ?? "";
$invDate = $invoice["invoice_date"] ?? "";
$partyName = $invoice["party_name"] ?? "Customer";
$payStatus = !empty($invoice["payment_status"]) ? strtoupper($invoice["payment_status"]) : "PENDING";

$$waDefaultMessage = "*Tax Invoice from {$firmName}*\n" .
  "Invoice No: *{$invNum}*\n" .
  "Invoice Date: {$invDate}\n" .
  "Total Bill Amount: *₹ " . number_format($$cleanGrandTotal, 2) . "*\n" .
  "Payment Received: *₹ " . number_format($$cleanPaid, 2) . "*\n" .
  "Balance Due: *₹ " . number_format($$cleanDue, 2) . "*\n" .
  "Status: *{$payStatus}*\n\n" .
  "Dear {$partyName}, please find your invoice details above. Thank you for your business!";
?>

  <!-- Top Static Action Toolbar -->
  <header class="top-action-bar no-print">
    <div class="container-fluid px-lg-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <!-- Left: Back link and Template Selector -->
      <div class="d-flex align-items-center gap-3">
        <a href="<?= $invoice["type"] === 'purchase' ? '/purchases' : '/sales' ?>" class="btn btn-outline-light btn-sm d-flex align-items-center rounded-pill px-3">
          <i class="bi bi-arrow-left me-1"></i> Back
        </a>

        <!-- Template Layout Selector Buttons -->
        <div class="template-switcher-bar">
          <button type="button" class="template-btn active" id="tabDetailed" onclick="switchInvoiceTemplate('detailed')">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Detailed Tax Bill
          </button>
          <button type="button" class="template-btn" id="tabSimple" onclick="switchInvoiceTemplate('simple')">
            <i class="bi bi-file-text me-1"></i> Simple Bill (Portrait)
          </button>
          <button type="button" class="template-btn" id="tabHorizontal" onclick="switchInvoiceTemplate('horizontal')">
            <i class="bi bi-layout-three-columns me-1"></i> Simple Horizontal (3-in-1)
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

        <!-- 1. Download PDF Button -->
        <button id="btnDownloadPdf" class="btn btn-primary btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center">
          <i class="bi bi-download me-1"></i> Download PDF
        </button>

        <!-- 2. Print Options Dropdown -->
        <div class="dropdown">
          <button class="btn btn-secondary btn-sm fw-semibold dropdown-toggle rounded-pill px-3 shadow-sm d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-printer me-1"></i> Print Bill
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1">
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="triggerPrintMode('detailed')">
                <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i> Print Detailed Tax Bill (Portrait)
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="triggerPrintMode('simple')">
                <i class="bi bi-file-text text-success me-2"></i> Print Simple Bill (Portrait)
              </button>
            </li>
            <li>
              <button class="dropdown-item py-2 d-flex align-items-center" onclick="triggerPrintMode('horizontal')">
                <i class="bi bi-layout-three-columns text-warning-emphasis me-2"></i> Print Simple Bill (Horizontal 3-in-1)
              </button>
            </li>
          </ul>
        </div>

        <!-- 3. Send to WhatsApp to Anyone Button -->
        <button type="button" class="btn btn-success btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#sendWhatsAppModal" title="Send bill on WhatsApp to anyone">
          <i class="bi bi-whatsapp me-1"></i> Send to WhatsApp
        </button>

        <!-- Edit Bill -->
        <a href="/invoices/edit/<?= $invoice["id"] ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
          <i class="bi bi-pencil-square me-1"></i> Edit
        </a>
      </div>
    </div>
  </header>

  <!-- =============================================================
       1. PROPER DETAILED BILL PREVIEW (VYAPAR STYLE)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="detailedInvoiceArea">
    <div class="invoice-paper" id="detailedPaperArea">
      <!-- Top Colored Accent Bar -->
      <div class="vyapar-top-strip"></div>

      <div class="invoice-inner">
        <!-- 1. Header: Firm Profile & Invoice Metadata -->
        <div class="row align-items-start mb-4 pb-3 border-bottom">
          <!-- Left: Business Info -->
          <div class="col-7">
            <div class="d-flex align-items-center mb-2">
              <?php if (!empty($firm["logo_path"])): ?>
                <img src="<?= $firm["logo_path"] ?>" alt="<?= $firm["name"] ?>" class="rounded border p-1 me-3 bg-white" style="max-height: 52px; max-width: 90px; object-fit: contain;">
              <?php endif; ?>
              <div>
                <h3 class="firm-name mb-0"><?= $firm["name"] ?></h3>
                <?php if (!empty($firm["gstin"])): ?>
                  <div class="small fw-semibold text-muted font-monospace mt-1">
                    GSTIN: <span class="text-dark"><?= $firm["gstin"] ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="small text-muted pe-3" style="font-size: 0.82rem; line-height: 1.45;">
              <?php if (!empty($firm["address"])): ?><div><?= $firm["address"] ?></div><?php endif; ?>
              <?php if (!empty($firm["city"]) || !empty($firm["state"])): ?><div><?= implode(', ', array_filter([$firm['city'] ?? '', $firm['state'] ?? ''])) ?> - <?= ($firm["pincode"] ?: '') ?></div><?php endif; ?>
              <?php if (!empty($firm["phone"])): ?><div><i class="bi bi-telephone text-secondary me-1"></i> <?= $firm["phone"] ?></div><?php endif; ?>
              <?php if (!empty($firm["email"])): ?><div><i class="bi bi-envelope text-secondary me-1"></i> <?= $firm["email"] ?></div><?php endif; ?>
            </div>
          </div>

          <!-- Right: Invoice Title & Number Details -->
          <div class="col-5 text-end">
            <div class="invoice-title-badge mb-2">
              <?= $invoice["is_gst_bill"] ? 'TAX INVOICE' : 'RETAIL INVOICE' ?>
            </div>
            <div class="small text-muted" style="font-size: 0.84rem;">
              <div><strong>Invoice No:</strong> <span class="font-monospace fw-bold text-dark"><?= $invoice["invoice_number"] ?></span></div>
              <div><strong>Invoice Date:</strong> <span class="text-dark"><?= $invoice["invoice_date"] ?></span></div>
              <?php if (!empty($invoice["due_date"])): ?>
                <div><strong>Due Date:</strong> <span class="text-dark"><?= $invoice["due_date"] ?></span></div>
              <?php endif; ?>
              <div><strong>Payment Mode:</strong> <span class="text-uppercase fw-semibold text-dark"><?= ($invoice["payment_mode"] ?: 'Cash') ?></span></div>
              <div class="mt-2">
                <strong>Status:</strong>
                <?php if (!empty($invoice["payment_status"]) === 'paid'): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">PAID IN FULL</span>
                <?php elseif (!empty($invoice["payment_status"]) === 'partial'): ?>
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">PARTIALLY PAID</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">PAYMENT UNPAID</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Dual Party Cards: Billed To / Shipped To -->
        <div class="row g-3 mb-4">
          <div class="col-sm-7">
            <div class="info-card">
              <div class="info-card-header">
                <i class="bi bi-person-check me-1"></i> <?= $invoice["type"] === 'purchase' ? 'Billed From (Supplier)' : 'Billed To (Customer)' ?>
              </div>
              <div class="fw-bold text-dark fs-6"><?= $invoice["party_name"] ?></div>
              <div class="small text-muted mt-1" style="font-size: 0.82rem; line-height: 1.45;">
                <?php if (!empty($invoice["party_phone"])): ?><div><strong>Phone:</strong> <?= $invoice["party_phone"] ?></div><?php endif; ?>
                <?php if (!empty($invoice["party_gstin"])): ?><div><strong>GSTIN:</strong> <span class="font-monospace fw-medium text-dark"><?= $invoice["party_gstin"] ?></span></div><?php endif; ?>
                <?php if (!empty($invoice["party_address"])): ?><div><strong>Address:</strong> <?= $invoice["party_address"] ?></div><?php endif; ?>
                <div><strong>State:</strong> <?= ($invoice["party_state"] ?: '--') ?> <?php if (!empty($invoice["party_state_code"])): ?>(Code: <?= $invoice["party_state_code"] ?>)<?php endif; ?></div>
              </div>
            </div>
          </div>

          <div class="col-sm-5">
            <div class="info-card">
              <div class="info-card-header">
                <i class="bi bi-shield-check me-1"></i> Tax & Place of Supply
              </div>
              <div class="small" style="font-size: 0.82rem; line-height: 1.5;">
                <div><strong>Tax Regime:</strong> <?= $invoice["is_gst_bill"] ? 'GST Registered' : 'Composition / Non-GST' ?></div>
                <div><strong>Supply Type:</strong> <?= $invoice["is_interstate"] ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' ?></div>
                <div><strong>Place of Supply:</strong> <?= (!empty($invoice['party_state']) ? $invoice['party_state'] : (!empty($firm['state']) ? $firm['state'] : 'Local')) ?></div>
                <div class="mt-2 pt-2 border-top text-muted" style="font-size: 0.76rem;">
                  Original for Recipient &bull; Computer Generated
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Line Items Table -->
        <div class="table-responsive mb-4">
          <table class="vyapar-table">
            <thead>
              <tr class="text-center">
                <th style="width: 35px;">#</th>
                <th class="text-start">Item Description</th>
                <th style="width: 80px;">HSN/SAC</th>
                <th style="width: 60px;">Qty</th>
                <th style="width: 55px;">Unit</th>
                <th style="width: 85px;" class="text-end">Rate (₹)</th>
                <?php if ($hasDiscount): ?>
                  <th style="width: 65px;" class="text-end">Disc %</th>
                <?php endif; ?>
                <?php if (!empty($invoice["is_gst_bill"])): ?>
                  <th style="width: 65px;" class="text-center">GST %</th>
                <?php endif; ?>
                <th style="width: 100px;" class="text-end">Amount (₹)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($items)): ?>
                <?php foreach ($items as $idx => $item): ?>
                  <tr>
                    <td class="text-center text-muted small"><?= idx + 1 ?></td>
                    <td>
                      <div class="fw-semibold text-dark"><?= $item["item_name"] ?></div>
                    </td>
                    <td class="text-center font-monospace small text-muted"><?= ($item["hsn_code"] ?: '--') ?></td>
                    <td class="text-center fw-bold"><?= $item["quantity"] ?></td>
                    <td class="text-center text-muted small"><?= ($item["unit"] ?: 'PCS') ?></td>
                    <td class="text-end">₹ <?= number_format((float)($item["rate"]), 2) ?></td>
                    <?php if ($hasDiscount): ?>
                      <td class="text-end text-danger"><?= $item["discount_percent"] > 0 ? $item["discount_percent"] + '%' : '--' ?></td>
                    <?php endif; ?>
                    <?php if (!empty($invoice["is_gst_bill"])): ?>
                      <td class="text-center"><?= $item["tax_rate"] ?>%</td>
                    <?php endif; ?>
                    <td class="text-end fw-bold text-dark">₹ <?= number_format((float)($item["total_amount"]), 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- 4. Calculation Summary & Bank/QR Area -->
        <div class="row g-4 mb-4">
          <!-- Left: Bank Details & Terms -->
          <div class="col-md-6">
            <?php if (!empty($firm["bank_name"]) || !empty($firm["bank_account_no"]) || !empty($firm["upi_id"])): ?>
              <div class="bank-box mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="small fw-bold text-uppercase text-dark" style="font-size: 0.75rem;">
                    <i class="bi bi-bank2 text-primary me-1"></i> Bank & Payment Details
                  </div>
                  <?php if (!empty($firm["upi_id"])): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">UPI Accepted</span>
                  <?php endif; ?>
                </div>
                <div class="small text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                  <?php if (!empty($firm["bank_name"])): ?><div>Bank Name: <strong class="text-dark"><?= $firm["bank_name"] ?></strong> (<?= ($firm["bank_branch"] ?: '') ?>)</div><?php endif; ?>
                  <?php if (!empty($firm["bank_account_no"])): ?><div>A/C Number: <strong class="text-dark font-monospace"><?= $firm["bank_account_no"] ?></strong></div><?php endif; ?>
                  <?php if (!empty($firm["bank_ifsc"])): ?><div>IFSC Code: <strong class="text-dark font-monospace"><?= $firm["bank_ifsc"] ?></strong></div><?php endif; ?>
                  <?php if (!empty($firm["upi_id"])): ?><div>UPI ID: <strong class="text-primary font-monospace"><?= $firm["upi_id"] ?></strong></div><?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if ((!empty($invoice['terms']) ? $invoice['terms'] : (!empty($firm['terms']) ? $firm['terms'] : ''))): ?>
              <div class="p-2 border rounded bg-white">
                <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.7rem;">Terms & Conditions:</div>
                <div class="small text-muted" style="font-size: 0.76rem; line-height: 1.35;">
                  <?= (!empty($invoice['terms']) ? $invoice['terms'] : (!empty($firm['terms']) ? $firm['terms'] : '')) ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Right: Summary Table & Grand Total Highlight -->
          <div class="col-md-6">
            <div class="total-highlight-card">
              <table class="table table-borderless table-sm mb-0" style="font-size: 0.84rem;">
                <tr>
                  <td class="text-muted p-1">Taxable Subtotal:</td>
                  <td class="text-end fw-semibold p-1">₹ <?= $number_format($cleanTaxable, 2) ?></td>
                </tr>
                <?php if ((float)(!empty($invoice["discount_amount"])) > 0): ?>
                  <tr>
                    <td class="text-danger p-1">Overall Discount:</td>
                    <td class="text-end text-danger fw-semibold p-1">- ₹ <?= number_format((float)($invoice["discount_amount"]), 2) ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($invoice["is_gst_bill"]) && (float)(!empty($invoice["tax_amount"])) > 0): ?>
                  <?php if (!empty($invoice["is_interstate"])): ?>
                    <tr>
                      <td class="text-muted p-1">IGST (Integrated Tax):</td>
                      <td class="text-end p-1">₹ <?= $number_format($cleanIgst, 2) ?></td>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <td class="text-muted p-1">CGST (Central Tax):</td>
                      <td class="text-end p-1">₹ <?= $number_format($cleanCgst, 2) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted p-1">SGST (State Tax):</td>
                      <td class="text-end p-1">₹ <?= $number_format($cleanSgst, 2) ?></td>
                    </tr>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($cleanRoundOff !== 0): ?>
                  <tr>
                    <td class="text-muted p-1">Round Off:</td>
                    <td class="text-end p-1">₹ <?= $number_format($cleanRoundOff, 2) ?></td>
                  </tr>
                <?php endif; ?>
                <tr class="border-top border-2 pt-2">
                  <td class="fw-bold fs-6 p-1 text-dark">Grand Total:</td>
                  <td class="text-end p-1 total-amount-display">₹ <?= number_format($$cleanGrandTotal, 2) ?></td>
                </tr>
                <tr>
                  <td class="text-success fw-medium p-1">Received / Paid:</td>
                  <td class="text-end text-success fw-bold p-1">₹ <?= $number_format($cleanPaid, 2) ?></td>
                </tr>
                <tr class="border-top">
                  <td class="text-danger fw-bold p-1">Balance Due:</td>
                  <td class="text-end text-danger fw-bold fs-6 p-1">₹ <?= $number_format($cleanDue, 2) ?></td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- 5. Signatory & Bottom Acknowledgement -->
        <div class="row align-items-end mt-4 pt-3 border-top">
          <div class="col-7">
            <div class="small text-muted" style="font-size: 0.76rem;">
              Thank you for doing business with <strong><?= $firm["name"] ?></strong>.<br>
              This is a digitally generated invoice, authorized signature verified.
            </div>
          </div>
          <div class="col-5 text-end">
            <div class="d-inline-block text-center">
              <?php if (!empty($firm["signature_path"])): ?>
                <img src="<?= $firm["signature_path"] ?>" alt="Signature" style="max-height: 44px; max-width: 140px;" class="mb-1">
              <?php else: ?>
                <div style="height: 38px;"></div>
              <?php endif; ?>
              <div class="sign-box">
                <small class="fw-bold text-dark d-block" style="font-size: 0.78rem;">For <?= $firm["name"] ?></small>
                <small class="text-muted" style="font-size: 0.7rem;">Authorized Signatory</small>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- =============================================================
       2. SIMPLE BILL PREVIEW (PORTRAIT - MATCHES SAMPLE SIMPLE BILL)
  ============================================================= -->
  <div class="invoice-preview-container print-template-target" id="simpleInvoiceArea" style="display: none;">
    <div class="invoice-paper simple-bill-paper" id="simplePaperArea">
      <!-- Top 3 Columns: Seller, Buyer, Invoice Details -->
      <div class="row align-items-start mb-4">
        <!-- 1. Seller Column -->
        <div class="col-4">
          <div class="simple-bill-header-title"><?= $firm["name"] ?></div>
          <?php if (!empty($firm["phone"])): ?><div>Phone no. : <?= $firm["phone"] ?></div><?php endif; ?>
          <?php if (!empty($firm["address"])): ?><div>Address : <?= $firm["address"] ?></div><?php endif; ?>
          <?php if (!empty($firm["email"])): ?><div>Email : <?= $firm["email"] ?></div><?php endif; ?>
          <?php if (!empty($firm["gstin"])): ?><div>GSTIN : <?= $firm["gstin"] ?></div><?php endif; ?>
        </div>

        <!-- 2. Buyer Column -->
        <div class="col-4">
          <div class="simple-bill-header-title"><?= $invoice["party_name"] ?></div>
          <?php if (!empty($invoice["party_address"])): ?><div>Address : <?= $invoice["party_address"] ?></div><?php endif; ?>
          <?php if (!empty($invoice["party_phone"])): ?><div>Phone no. : <?= $invoice["party_phone"] ?></div><?php endif; ?>
          <?php if (!empty($invoice["party_gstin"])): ?><div>GSTIN : <?= $invoice["party_gstin"] ?></div><?php endif; ?>
          <?php if (!empty($invoice["party_state"])): ?><div>State: <?= $invoice["party_state"] ?></div><?php endif; ?>
        </div>

        <!-- 3. Invoice Details Column -->
        <div class="col-4 text-end">
          <div class="simple-bill-header-title">Invoice Details</div>
          <div>Invoice No. : <?= $invoice["invoice_number"] ?></div>
          <div>Date : <?= $invoice["invoice_date"] ?></div>
          <div>Place of supply: <?= (!empty($invoice['party_state']) ? $invoice['party_state'] : (!empty($firm['state']) ? $firm['state'] : 'Local')) ?></div>
        </div>
      </div>

      <!-- Items Table -->
      <table class="simple-bill-table">
        <thead>
          <tr>
            <th style="width: 35px;">#</th>
            <th>Item name</th>
            <th style="width: 80px;" class="text-center">Quantity</th>
            <th style="width: 60px;" class="text-center">Unit</th>
            <th style="width: 120px;" class="text-end">Price/ Unit</th>
            <th style="width: 130px;" class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $idx => $item): ?>
              <tr>
                <td><?= idx + 1 ?></td>
                <td class="fw-bold"><?= $item["item_name"] ?></td>
                <td class="text-center"><?= $item["quantity"] ?></td>
                <td class="text-center"><?= ($item["unit"] ?: 'cls') ?></td>
                <td class="text-end">₹ <?= number_format((float)($item["rate"]), 4) ?></td>
                <td class="text-end">₹ <?= number_format((float)($item["total_amount"]), 4) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          <!-- Total Row -->
          <tr class="total-row">
            <td></td>
            <td>Total</td>
            <td class="text-center"><?= $totalQtyCount ?></td>
            <td></td>
            <td></td>
            <td class="text-end">₹ <?= $number_format($cleanTaxable, 4) ?></td>
          </tr>
        </tbody>
      </table>

      <!-- Bottom 2-Column Section -->
      <div class="row align-items-start mt-3">
        <!-- Left: Words & Terms -->
        <div class="col-6">
          <div class="fw-bold mb-1">Invoice Amount In Words</div>
          <div class="mb-4"><?= $wordsTotal ?></div>

          <div class="fw-bold mb-1">Terms and Conditions</div>
          <div><?= (!empty($invoice['terms']) ? $invoice['terms'] : (!empty($firm['terms']) ? $firm['terms'] : 'Thanks for doing business with us!')) ?></div>
        </div>

        <!-- Right: Summary Totals -->
        <div class="col-6">
          <table class="table table-borderless table-sm mb-0 ms-auto" style="max-width: 320px; font-size: 0.84rem;">
            <tr>
              <td>Sub Total</td>
              <td class="text-end">₹ <?= $number_format($cleanTaxable, 4) ?></td>
            </tr>
            <?php if (!empty($invoice["is_gst_bill"]) && ($cleanSgst > 0 || $cleanCgst > 0 || $cleanIgst > 0)): ?>
              <?php if (!empty($invoice["is_interstate"])): ?>
                <tr>
                  <td>IGST@<?= $firstTaxRate ?>%</td>
                  <td class="text-end">₹ <?= $number_format($cleanIgst, 4) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td>SGST@<?= ($firstTaxRate / 2) ?>%</td>
                  <td class="text-end">₹ <?= $number_format($cleanSgst, 4) ?></td>
                </tr>
                <tr>
                  <td>CGST@<?= ($firstTaxRate / 2) ?>%</td>
                  <td class="text-end">₹ <?= $number_format($cleanCgst, 4) ?></td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
            <tr>
              <td>Round off</td>
              <td class="text-end">₹ <?= $number_format($cleanRoundOff, 4) ?></td>
            </tr>
            <tr class="simple-total-bar">
              <td>Total</td>
              <td class="text-end">₹ <?= $number_format($cleanGrandTotal, 4) ?></td>
            </tr>
            <tr>
              <td>Received</td>
              <td class="text-end">₹ <?= $number_format($cleanPaid, 4) ?></td>
            </tr>
            <tr>
              <td>Balance</td>
              <td class="text-end">₹ <?= $number_format($cleanDue, 4) ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>


  <!-- =============================================================
       3. SIMPLE BILL HORIZONTAL (3-IN-1 SLIPS LANDSCAPE - MATCHES SAMPLE)
  ============================================================= -->
  <div class="invoice-preview-container-horizontal print-template-target" id="simpleHorizontalInvoiceArea" style="display: none;">
    <div class="horizontal-tri-grid" id="horizontalPaperArea">
      <?php $slipTitles = ["Original for Buyer", "Duplicate for Transporter", "Triplicate for Supplier"]; foreach ($slipTitles as $slipIndex => $slipLabel): ?>
        <!-- Slip <?= slipIndex + 1 ?> -->
        <div class="horizontal-slip-card">
          <!-- Slip Label & Voucher Meta -->
          <div class="d-flex justify-content-between align-items-center horizontal-slip-badge">
            <span><?= slipLabel ?></span>
            <span><?= $invoice["invoice_date"] ?></span>
          </div>

          <div class="d-flex justify-content-between align-items-start mb-2" style="font-size: 0.72rem;">
            <div><strong>Invoice No. :</strong> <?= $invoice["invoice_number"] ?></div>
            <div><strong>Date :</strong> <?= $invoice["invoice_date"] ?></div>
          </div>

          <!-- Parties Side-by-Side in slip -->
          <div class="row g-1 mb-2" style="font-size: 0.7rem;">
            <div class="col-6">
              <div class="fw-bold text-dark"><?= $firm["name"] ?></div>
              <?php if (!empty($firm["phone"])): ?><div>Phone : <?= $firm["phone"] ?></div><?php endif; ?>
              <?php if (!empty($firm["address"])): ?><div>Address : <?= $firm["address"] ?></div><?php endif; ?>
              <?php if (!empty($firm["email"])): ?><div>Email : <?= $firm["email"] ?></div><?php endif; ?>
              <?php if (!empty($firm["gstin"])): ?><div>GSTIN : <?= $firm["gstin"] ?></div><?php endif; ?>
            </div>
            <div class="col-6">
              <div class="fw-bold text-dark"><?= $invoice["party_name"] ?></div>
              <?php if (!empty($invoice["party_address"])): ?><div>Address : <?= $invoice["party_address"] ?></div><?php endif; ?>
              <?php if (!empty($invoice["party_phone"])): ?><div>Phone : <?= $invoice["party_phone"] ?></div><?php endif; ?>
              <?php if (!empty($invoice["party_gstin"])): ?><div>GSTIN : <?= $invoice["party_gstin"] ?></div><?php endif; ?>
              <?php if (!empty($invoice["party_state"])): ?><div>State: <?= $invoice["party_state"] ?></div><?php endif; ?>
            </div>
          </div>

          <!-- Slip Item Table -->
          <table class="horizontal-slip-table">
            <thead>
              <tr>
                <th style="width: 15px;">#</th>
                <th>Item</th>
                <th style="width: 35px;" class="text-center">Qty</th>
                <th style="width: 25px;" class="text-center">Unit</th>
                <th style="width: 65px;" class="text-end">Price/Unit</th>
                <th style="width: 65px;" class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($items)): ?>
                <?php foreach ($items as $idx => $item): ?>
                  <tr>
                    <td><?= idx + 1 ?></td>
                    <td class="fw-bold text-truncate" style="max-width: 80px;"><?= $item["item_name"] ?></td>
                    <td class="text-center"><?= $item["quantity"] ?></td>
                    <td class="text-center"><?= ($item["unit"] ?: 'cls') ?></td>
                    <td class="text-end">₹ <?= number_format((float)($item["rate"]), 2) ?></td>
                    <td class="text-end">₹ <?= number_format((float)($item["total_amount"]), 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr class="total-row">
                <td></td>
                <td>Total</td>
                <td class="text-center"><?= $totalQtyCount ?></td>
                <td></td>
                <td></td>
                <td class="text-end">₹ <?= $number_format($cleanTaxable, 2) ?></td>
              </tr>
            </tbody>
          </table>

          <!-- Slip Summary Breakdown -->
          <table class="table table-borderless table-sm mb-0 ms-auto" style="font-size: 0.7rem;">
            <tr>
              <td class="p-0">Sub Total</td>
              <td class="text-end p-0">₹ <?= $number_format($cleanTaxable, 2) ?></td>
            </tr>
            <?php if (!empty($invoice["is_gst_bill"]) && ($cleanSgst > 0 || $cleanCgst > 0 || $cleanIgst > 0)): ?>
              <?php if (!empty($invoice["is_interstate"])): ?>
                <tr>
                  <td class="p-0">IGST</td>
                  <td class="text-end p-0">₹ <?= $number_format($cleanIgst, 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td class="p-0">SGST</td>
                  <td class="text-end p-0">₹ <?= $number_format($cleanSgst, 2) ?></td>
                </tr>
                <tr>
                  <td class="p-0">CGST</td>
                  <td class="text-end p-0">₹ <?= $number_format($cleanCgst, 2) ?></td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
            <tr>
              <td class="p-0">Round off</td>
              <td class="text-end p-0">₹ <?= $number_format($cleanRoundOff, 2) ?></td>
            </tr>
            <tr class="simple-total-bar" style="font-size: 0.72rem;">
              <td class="py-1 px-1">Total</td>
              <td class="text-end py-1 px-1">₹ <?= $number_format($cleanGrandTotal, 2) ?></td>
            </tr>
            <tr>
              <td class="p-0">Received</td>
              <td class="text-end p-0">₹ <?= $number_format($cleanPaid, 2) ?></td>
            </tr>
            <tr>
              <td class="p-0">Balance</td>
              <td class="text-end p-0">₹ <?= $number_format($cleanDue, 2) ?></td>
            </tr>
          </table>
        </div>
      <?php endforeach; ?>
    </div>
  </div>


  <!-- =============================================================
       MODALS: HSV COLOR STUDIO, WHATSAPP TO ANYONE & POST-DOWNLOAD
  ============================================================= -->

  <!-- Comprehensive HSV Custom Theme Studio Modal -->
  <div class="modal fade" id="hsvCustomThemeModal" tabindex="-1" aria-labelledby="hsvCustomThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
        <div class="modal-header border-0 bg-dark text-white py-3 px-4">
          <div class="d-flex align-items-center">
            <div class="rounded-3 bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
              <i class="bi bi-sliders2 fs-4"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0" id="hsvCustomThemeModalLabel">HSV Custom Colour Studio</h5>
              <small class="text-white-50">Set your exact brand theme by tuning Hue (H), Saturation (S), and Value (V)</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="row g-4">
            <!-- Left Column: Interactive 2D HSV Pad & Sliders -->
            <div class="col-lg-7">
              <!-- 2D Saturation-Value Color Pad -->
              <div class="mb-3">
                <label class="form-label fw-bold text-dark small d-flex justify-content-between mb-1">
                  <span><i class="bi bi-grid-3x3 me-1 text-primary"></i> 2D Saturation & Brightness Surface</span>
                  <span class="text-muted" style="font-size: 0.75rem;">Click or drag cursor anywhere</span>
                </label>
                <div class="hsv-color-pad" id="hsvColorPad">
                  <div class="hsv-cursor" id="hsvCursor"></div>
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

            <!-- Right Column: Exact Color Codes, Preset Shortcuts & Live Preview -->
            <div class="col-lg-5">
              <!-- Live Color Header Pill -->
              <div class="p-3 rounded-3 mb-3 text-white d-flex align-items-center justify-content-between shadow-sm" id="hsvCurrentColorBox" style="background-color: #5c3a21; min-height: 70px;">
                <div>
                  <div class="small text-uppercase text-white-50 fw-bold" style="font-size: 0.7rem;">Active Brand Colour</div>
                  <div class="fs-4 fw-bold font-monospace" id="hsvHexDisplay">#5C3A21</div>
                </div>
                <div class="text-end font-monospace small">
                  <div id="hsvRgbDisplay">rgb(92, 58, 33)</div>
                  <div class="text-white-50" id="hsvValDisplay">H:28° S:64% V:36%</div>
                </div>
              </div>

              <!-- Exact Manual HEX Input -->
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark mb-1">Enter Exact HEX Code:</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-white font-monospace">#</span>
                  <input type="text" class="form-control font-monospace fw-bold" id="hsvHexInput" value="5C3A21" maxlength="6">
                  <button class="btn btn-outline-secondary" type="button" id="btnApplyHexInput">Set</button>
                </div>
              </div>

              <!-- Quick Fine-Tuning Step Buttons -->
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark mb-1">Quick Tuning Steps:</label>
                <div class="d-flex flex-wrap gap-1">
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('v', -5)">Darker (-5% V)</button>
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('v', 5)">Brighter (+5% V)</button>
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('s', -10)">Mute (-10% S)</button>
                  <button type="button" class="btn btn-light btn-sm py-1 px-2 border small" onclick="stepHsv('s', 10)">Vibrant (+10% S)</button>
                </div>
              </div>

              <!-- Quick Classical Base Swatches -->
              <div class="mb-2">
                <label class="form-label fw-bold small text-dark mb-1">Classical Starting Points:</label>
                <div class="d-flex flex-wrap gap-2">
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

        <div class="modal-footer border-0 bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="setHsvValues(24, 68, 35)">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Classy Brown
          </button>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-sm" id="btnSaveHsvTheme">
              <i class="bi bi-check2-circle me-1"></i> Apply & Save Theme
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Send WhatsApp to Anyone Modal -->
  <div class="modal fade" id="sendWhatsAppModal" tabindex="-1" aria-labelledby="sendWhatsAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
        <div class="modal-header border-0 bg-success text-white py-3 px-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
              <i class="bi bi-whatsapp fs-4"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0" id="sendWhatsAppModalLabel">Send Bill on WhatsApp</h5>
              <small class="text-white-50">Send this bill summary to any customer, accountant, or contact</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- 1. Quick Recipient Selection Chips -->
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark mb-1">Quick Recipient Selection:</label>
            <div class="d-flex flex-wrap gap-2">
              <?php if (!empty($invoice["party_phone"])): ?>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 active" id="chipPartyPhone" onclick="setRecipientPhone('<?= $invoice["party_phone"] ?>')">
                  <i class="bi bi-person-check me-1"></i> <?= $invoice["party_name"] ?> (<?= $invoice["party_phone"] ?>)
                </button>
              <?php endif; ?>
              <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="chipCustomPhone" onclick="focusCustomPhone()">
                <i class="bi bi-telephone-plus me-1"></i> Enter Other Number
              </button>
            </div>
          </div>

          <!-- 2. Phone Number Input (Send to Anyone) -->
          <div class="mb-3">
            <label class="form-label fw-bold small text-dark mb-1">Recipient Mobile Number (Send to Anyone):</label>
            <div class="input-group">
              <span class="input-group-text bg-white fw-bold font-monospace border-end-0">
                <i class="bi bi-phone text-success me-1"></i> +91
              </span>
              <input type="tel" class="form-control font-monospace fs-6" id="waCustomPhoneInput" placeholder="Enter 10-digit mobile number" value="<?= preg_replace('/[^0-9]/', '', $invoice['party_phone'] ?? '') ?>" maxlength="10">
            </div>
            <div class="form-text small" id="waPhoneHelpText">You can enter any 10-digit number here to send the bill.</div>
          </div>

          <!-- 3. Message Preview & Customization -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label fw-bold small text-dark mb-0">Message Preview (Editable):</label>
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" id="btnCopyWaText">
                <i class="bi bi-clipboard me-1"></i> <span id="copyBtnText">Copy Text</span>
              </button>
            </div>
            <textarea class="form-control font-monospace small bg-light" id="waMessageText" rows="6" style="resize: vertical; font-size: 0.82rem;"></textarea>
          </div>

          <div class="d-grid gap-2">
            <button type="button" class="btn btn-success btn-lg fw-bold shadow-sm d-flex align-items-center justify-content-center" id="btnLaunchWhatsApp">
              <i class="bi bi-whatsapp fs-5 me-2"></i> Open & Send in WhatsApp
            </button>
          </div>
        </div>

        <div class="modal-footer border-0 bg-light py-2 px-4 d-flex justify-content-between">
          <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> Opens directly in WhatsApp App or Web</small>
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Post-Download WhatsApp Modal Prompt -->
  <div class="modal fade" id="postDownloadModal" tabindex="-1" aria-labelledby="postDownloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
        <div class="modal-header border-0 bg-success-subtle pb-0">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px;">
              <i class="bi bi-check-lg fs-5"></i>
            </div>
            <h5 class="modal-title fw-bold text-dark" id="postDownloadModalLabel">Bill PDF Downloaded!</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 text-center">
          <p class="text-muted mb-3">
            Invoice <strong><?= $invoice["invoice_number"] ?></strong> has been saved to your computer.<br>
            Send this bill details directly to anyone on WhatsApp:
          </p>

          <div class="mb-3 text-start">
            <label class="form-label fw-bold small text-dark mb-1">Recipient Mobile Number:</label>
            <div class="input-group">
              <span class="input-group-text bg-white fw-bold font-monospace border-end-0">
                <i class="bi bi-phone text-success me-1"></i> +91
              </span>
              <input type="tel" class="form-control font-monospace fs-6" id="postWaPhoneInput" placeholder="Enter 10-digit mobile number" value="<?= preg_replace('/[^0-9]/', '', $invoice['party_phone'] ?? '') ?>" maxlength="10">
            </div>
          </div>

          <div class="p-3 bg-light rounded-3 mb-3 text-start small font-monospace">
            <div><strong>Party:</strong> <?= $invoice["party_name"] ?></div>
            <div><strong>Total Bill:</strong> ₹ <?= $number_format($cleanGrandTotal, 2) ?></div>
            <div><strong>Balance Due:</strong> ₹ <?= $number_format($cleanDue, 2) ?></div>
          </div>

          <div class="d-grid gap-2">
            <button type="button" class="btn btn-success btn-lg fw-bold shadow-sm d-flex align-items-center justify-content-center" id="btnSendPostDownloadWa">
              <i class="bi bi-whatsapp fs-5 me-2"></i> Send to WhatsApp Now
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 Bundle JS (Offline Local with CDN Fallback) -->
  <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>if(typeof bootstrap==='undefined'){document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>');}</script>
  <script>
    window.RACE_INVOICE_DOWNLOAD = {
      invoiceNumber: <?= json_encode($invoice['invoice_number'] ?? '') ?>,
      whatsAppMessage: <?= json_encode($$waDefaultMessage) ?>
    };
  </script>
  <script src="/js/invoice-download.js"></script>
</body>
</html>

