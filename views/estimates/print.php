<?php
/**
 * Rate List & Estimate Printable Layout (A4 format)
 * RACE FINANCE - Billing & Inventory System
 */

$firmName = $firm['name'] ?? 'RACE FINANCE';
$firmAddress = array_filter([
    $firm['address'] ?? '',
    $firm['city'] ?? '',
    $firm['state'] ?? ''
]);
$firmPhone = $firm['phone'] ?? '';
$firmGstin = $firm['gstin'] ?? '';
$mode = $mode ?? 'estimate'; // 'estimate' or 'ratelist'
$isRateList = ($mode === 'ratelist');
$items = $estimate['items'] ?? [];
$customerName = trim($estimate['customer_name'] ?? '');
$estimateNumber = $estimate['estimate_number'] ?? '';
$estimateDate = $estimate['estimate_date'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isRateList ? 'Rate List - ' . htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') : 'Estimate ' . htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      color: #0f172a;
      margin: 0;
      padding: 20px 0;
      font-size: 12px;
      line-height: 1.4;
    }
    .print-sheet {
      max-width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      background: #ffffff;
      padding: 12mm 15mm;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      border: 1px solid #cbd5e1;
      position: relative;
    }
    /* Firm Header Styling: Bigger, bold, basic clean styling */
    .firm-header-box {
      border-bottom: 2px solid #0f172a;
      padding-bottom: 12px;
      margin-bottom: 14px;
    }
    .owner-firm-name {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.5px;
      color: #0f172a;
      text-transform: uppercase;
      line-height: 1.15;
    }
    .firm-sub-info {
      font-size: 11px;
      color: #475569;
      margin-top: 3px;
    }
    .doc-badge-title {
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      padding: 4px 10px;
      background-color: #0f172a;
      color: #ffffff;
      display: inline-block;
      border-radius: 3px;
    }
    /* Single-line Customer Bar */
    .customer-single-line {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-left: 4px solid #0f172a;
      padding: 6px 12px;
      margin-bottom: 14px;
      font-size: 12px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    /* Tables */
    .print-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }
    .print-table th, .print-table td {
      border: 1px solid #94a3b8 !important;
      padding: 5px 7px;
      font-size: 11px;
    }
    .print-table th {
      background-color: #f1f5f9;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 10px;
      letter-spacing: 0.5px;
      color: #1e293b;
    }
    .summary-box {
      border: 1px solid #94a3b8;
      background-color: #f8fafc;
      padding: 8px 12px;
      font-size: 11.5px;
    }
    @media print {
      body {
        background-color: #ffffff !important;
        padding: 0 !important;
      }
      .print-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 8mm 10mm !important;
        max-width: 100% !important;
        min-height: auto !important;
      }
      .no-print {
        display: none !important;
      }
      /* Ensure owner header displays on top of first page only */
      .firm-header-box {
        page-break-after: avoid;
      }
      tr {
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Control Bar (Hidden on actual print) -->
  <div class="container mb-3 no-print" style="max-width: 210mm;">
    <div class="d-flex justify-content-between align-items-center bg-dark text-white p-2 px-3 rounded shadow-sm">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-printer text-warning fs-5"></i>
        <span class="fw-bold">
          <?= $isRateList ? 'Print Product Rate List' : 'Print Estimate #' . htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?>
        </span>
      </div>
      <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
          <i class="bi bi-printer me-1"></i> Print / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-light btn-sm px-3">
          Close Window
        </button>
      </div>
    </div>
  </div>

  <div class="print-sheet">
    <!-- Header: Owner Firm Name (Bigger, Bold, Clean Styling, On Top of First Page Only) -->
    <div class="firm-header-box">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="owner-firm-name"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="firm-sub-info">
            <?php if (!empty($firmAddress)): ?>
              <span><?= htmlspecialchars(implode(', ', $firmAddress), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if (!empty($firmPhone)): ?>
              <span class="ms-2">| <strong>Ph:</strong> <?= htmlspecialchars($firmPhone, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if (!empty($firmGstin)): ?>
              <span class="ms-2">| <strong>GSTIN:</strong> <?= htmlspecialchars($firmGstin, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="text-end">
          <div class="doc-badge-title mb-1">
            <?= $isRateList ? 'PRODUCT RATE LIST' : 'ESTIMATE / QUOTATION' ?>
          </div>
          <?php if (!$isRateList && !empty($estimateNumber)): ?>
            <div class="fw-bold font-monospace text-dark" style="font-size: 13px;">#<?= htmlspecialchars($estimateNumber, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div class="text-muted small" style="font-size: 10.5px;">
            Date: <strong><?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Optional Customer Line (Shown ONLY if customerName is provided, in EXACTLY 1 line, no other details) -->
    <?php if (!empty($customerName)): ?>
      <div class="customer-single-line">
        <span class="text-muted text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.5px;">Customer:</span>
        <strong class="text-dark ms-1" style="font-size: 13px;"><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
    <?php endif; ?>

    <!-- Items Table -->
    <?php if ($isRateList): ?>
      <!-- ==================== RATE LIST TABLE LAYOUT ==================== -->
      <table class="print-table">
        <thead>
          <tr class="text-center">
            <th style="width: 35px;">#</th>
            <th class="text-start">Item / Product Name</th>
            <th style="width: 75px;">HSN/SAC</th>
            <th style="width: 90px;" class="text-end">Pre-GST Price</th>
            <th style="width: 55px;" class="text-center">GST %</th>
            <th style="width: 95px;" class="text-end">Post-GST Price</th>
            <th style="width: 140px;" class="text-start">Unit &amp; Sub-Quantity</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $idx => $it): 
              $rateVal = (float)($it['rate'] ?? ($it['sale_price'] ?? 0));
              $taxRate = (float)($it['tax_rate'] ?? 0);
              $postGst = round($rateVal * (1 + ($taxRate / 100)), 2);
              $unitStr = strtoupper($it['unit'] ?? 'PCS');
              $subUnitText = '--';
              if (!empty($it['sub_unit']) && !empty($it['sub_unit_qty'])) {
                  $subUnitText = "1 " . $unitStr . " = " . (float)$it['sub_unit_qty'] . " " . strtoupper($it['sub_unit']);
              }
            ?>
              <tr>
                <td class="text-center text-muted font-monospace"><?= $idx + 1 ?></td>
                <td>
                  <strong class="text-dark"><?= htmlspecialchars($it['item_name'] ?? ($it['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                  <?php if (!empty($it['category'])): ?>
                    <span class="badge bg-light text-muted border ms-1" style="font-size: 8px;"><?= htmlspecialchars($it['category'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-center font-monospace small"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end fw-semibold">₹ <?= number_format($rateVal, 2) ?></td>
                <td class="text-center font-monospace"><?= $taxRate ?>%</td>
                <td class="text-end fw-bold text-dark">₹ <?= number_format($postGst, 2) ?></td>
                <td class="text-start">
                  <strong><?= htmlspecialchars($unitStr, ENT_QUOTES, 'UTF-8') ?></strong>
                  <?php if ($subUnitText !== '--'): ?>
                    <div class="text-muted" style="font-size: 9.5px;"><?= htmlspecialchars($subUnitText, ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No items in this rate list.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Rate List Policy & Remarks Note -->
      <div class="p-2 border rounded bg-light text-muted small mt-2" style="font-size: 10px;">
        <i class="bi bi-info-circle me-1"></i> Note: Rates mentioned above are current as of <?= htmlspecialchars(formatDate($estimateDate), ENT_QUOTES, 'UTF-8') ?> and subject to standard commercial terms, stock availability, and applicable taxes.
      </div>

    <?php else: ?>
      <!-- ==================== ESTIMATE / QUOTATION TABLE LAYOUT ==================== -->
      <table class="print-table">
        <thead>
          <tr class="text-center">
            <th style="width: 30px;">#</th>
            <th class="text-start">Item Description</th>
            <th style="width: 65px;">HSN/SAC</th>
            <th style="width: 50px;">Qty</th>
            <th style="width: 70px;">Unit</th>
            <th style="width: 75px;" class="text-end">Rate (₹)</th>
            <th style="width: 45px;" class="text-end">Disc%</th>
            <th style="width: 70px;" class="text-end">Taxable</th>
            <th style="width: 60px;" class="text-end">GST</th>
            <th style="width: 85px;" class="text-end">Amount (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $idx => $it): 
              $rateVal = (float)($it['rate'] ?? ($it['sale_price'] ?? 0));
              $qty = (float)($it['quantity'] ?? 1);
              $taxRate = (float)($it['tax_rate'] ?? 0);
              $discPercent = (float)($it['discount_percent'] ?? 0);

              $lineBase = $qty * $rateVal;
              $lineDisc = round($lineBase * ($discPercent / 100), 2);
              $lineTaxable = (float)($it['taxable_amount'] ?? ($lineBase - $lineDisc));
              $lineTax = (float)($it['tax_amount'] ?? round($lineTaxable * ($taxRate / 100), 2));
              $lineTotal = (float)($it['total_amount'] ?? round($lineTaxable + $lineTax, 2));

              // Sub quantity display e.g. "2 BOX = 24 PCS"
              $subQtyStr = '';
              if (!empty($it['sub_unit']) && !empty($it['sub_unit_qty'])) {
                  $totSub = (float)$it['sub_unit_qty'] * $qty;
                  $subQtyStr = " (" . $totSub . " " . $it['sub_unit'] . ")";
              }
            ?>
              <tr>
                <td class="text-center text-muted font-monospace"><?= $idx + 1 ?></td>
                <td>
                  <strong class="text-dark"><?= htmlspecialchars($it['item_name'] ?? ($it['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </td>
                <td class="text-center font-monospace small"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center fw-bold"><?= $qty ?></td>
                <td class="text-center small">
                  <?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?><?= htmlspecialchars($subQtyStr, ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="text-end font-monospace">₹ <?= number_format($rateVal, 2) ?></td>
                <td class="text-end small"><?= $discPercent > 0 ? $discPercent . '%' : '--' ?></td>
                <td class="text-end font-monospace">₹ <?= number_format($lineTaxable, 2) ?></td>
                <td class="text-end small"><?= $taxRate > 0 ? "₹ " . number_format($lineTax, 2) . "<br><span class='text-muted' style='font-size: 8px;'>(" . $taxRate . "%)</span>" : '0%' ?></td>
                <td class="text-end fw-bold text-dark font-monospace">₹ <?= number_format($lineTotal, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Estimate Summary & Calculations -->
      <div class="row g-2 justify-content-between">
        <div class="col-6">
          <?php if (!empty($estimate['notes'])): ?>
            <div class="p-2 border rounded bg-light" style="font-size: 10px;">
              <strong>Terms / Notes:</strong>
              <div class="text-muted mt-1" style="white-space: pre-line;"><?= htmlspecialchars($estimate['notes'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          <?php else: ?>
            <div class="text-muted small" style="font-size: 9.5px; line-height: 1.4;">
              * This is a computerized Estimate / Commercial Quotation and not an official tax invoice.<br>
              * Prices and estimates are valid subject to company terms.
            </div>
          <?php endif; ?>
        </div>

        <div class="col-5">
          <div class="summary-box rounded">
            <div class="d-flex justify-content-between mb-1">
              <span>Subtotal (Taxable):</span>
              <span class="fw-semibold font-monospace">₹ <?= number_format((float)($estimate['subtotal'] ?? 0), 2) ?></span>
            </div>
            <?php if ((float)($estimate['discount_amount'] ?? 0) > 0): ?>
              <div class="d-flex justify-content-between text-danger mb-1">
                <span>Discount:</span>
                <span class="font-monospace">- ₹ <?= number_format((float)$estimate['discount_amount'], 2) ?></span>
              </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between mb-1">
              <span>Estimated GST:</span>
              <span class="font-monospace">₹ <?= number_format((float)($estimate['tax_amount'] ?? 0), 2) ?></span>
            </div>
            <div class="d-flex justify-content-between border-top border-dark pt-1 mt-1 fs-6 fw-bold text-dark">
              <span>Grand Total:</span>
              <span class="font-monospace">₹ <?= number_format((float)($estimate['grand_total'] ?? 0), 2) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Signatures -->
      <div class="row mt-4 pt-3 border-top">
        <div class="col-6 text-muted" style="font-size: 10px;">
          Customer Acceptance (Signature)
        </div>
        <div class="col-6 text-end">
          <div class="fw-bold" style="font-size: 11px;">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <div style="height: 30px;"></div>
          <div class="text-muted small" style="font-size: 10px;">Authorized Signatory</div>
        </div>
      </div>
    <?php endif; ?>

  </div>

</body>
</html>
