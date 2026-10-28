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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      color: #111;
      margin: 0;
      padding: 20px 0;
    }
    .bulk-invoice-page {
      max-width: 210mm;
      margin: 0 auto 30px auto;
      background: #fff;
      padding: 12mm 15mm;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      border: 1px solid #cbd5e1;
    }
    .tax-invoice-box {
      border: 1px solid #000;
    }
    .bordered-table th, .bordered-table td {
      border: 1px solid #000 !important;
      padding: 4px 6px;
      font-size: 11px;
    }
    .bordered-table th {
      background-color: #f8fafc;
      font-weight: 600;
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

  <!-- Sticky Top Action Bar for Bulk Printing -->
  <div class="container mb-3 no-print" style="max-width: 210mm;">
    <div class="d-flex flex-wrap justify-content-between align-items-center bg-dark text-white p-2 px-3 rounded shadow-sm gap-2">
      <div class="d-flex align-items-center">
        <i class="bi bi-printer fs-5 me-2 text-warning"></i>
        <span class="fw-semibold">Bulk Print Queue: <strong><?= $invCount ?> Invoices / Records</strong></span>
      </div>
      <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
          <i class="bi bi-printer me-1"></i> Print All Now
        </button>
        <button onclick="window.close()" class="btn btn-outline-light btn-sm px-3">
          Close Window
        </button>
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
        <div class="text-center py-1 border-bottom border-dark bg-light d-flex justify-content-between align-items-center px-2">
          <div class="small text-muted font-monospace" style="font-size: 10px;">Doc <?= $idx + 1 ?> of <?= $invCount ?></div>
          <h5 class="fw-bold mb-0 text-uppercase letter-spacing-1" style="font-size: 13px;">
            <?= $isGst ? ($isPurchase ? 'TAX INVOICE / PURCHASE RECORD' : 'TAX INVOICE') : ($isPurchase ? 'PURCHASE BILL' : 'RETAIL INVOICE') ?>
          </h5>
          <div class="small text-muted" style="font-size: 10px;">Original for Recipient</div>
        </div>

        <!-- Header: Firm Profile & Invoice Metadata -->
        <div class="row g-0 border-bottom border-dark">
          <!-- Left: Firm Details -->
          <div class="col-7 p-2 border-end border-dark">
            <div class="d-flex align-items-center mb-1">
              <?php if (!empty($firm['logo_path'])): ?>
                <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="me-2" style="max-height: 38px; max-width: 75px; object-fit: contain;">
              <?php endif; ?>
              <div>
                <h6 class="fw-bold mb-0 text-uppercase" style="font-size: 13px;"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h6>
              </div>
            </div>
            <div style="font-size: 10.5px; line-height: 1.35;">
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
          <div class="col-5 p-2" style="font-size: 10.5px; line-height: 1.4;">
            <table class="table table-borderless table-sm mb-0" style="font-size: 10.5px;">
              <tr>
                <td class="p-0 fw-bold" style="width: 45%;">Invoice No:</td>
                <td class="p-0 font-monospace fw-bold"><?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
              <tr>
                <td class="p-0 fw-bold">Invoice Date:</td>
                <td class="p-0"><?= htmlspecialchars(formatDate($invoice['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
              <?php if (!empty($invoice['due_date'])): ?>
                <tr>
                  <td class="p-0 fw-bold">Due Date:</td>
                  <td class="p-0"><?= htmlspecialchars(formatDate($invoice['due_date']), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endif; ?>
              <tr>
                <td class="p-0 fw-bold">Payment Mode:</td>
                <td class="p-0 text-uppercase"><?= strtoupper(htmlspecialchars($invoice['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></td>
              </tr>
              <tr>
                <td class="p-0 fw-bold">Place of Supply:</td>
                <td class="p-0"><?= htmlspecialchars($invoice['party_state'] ?? ($firm['state'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Billed To Party Details -->
        <div class="p-2 border-bottom border-dark bg-light" style="font-size: 11px;">
          <div class="fw-bold text-uppercase" style="font-size: 9.5px; color: #475569;"><?= $isPurchase ? 'Billed From (Supplier):' : 'Billed To (Customer):' ?></div>
          <div class="fw-bold text-dark fs-6 mb-1"><?= htmlspecialchars($invoice['party_name'] ?? 'Counter Sale', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="row g-1">
            <div class="col-6">
              <?php if (!empty($invoice['party_address'])): ?><div>Address: <?= htmlspecialchars($invoice['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($invoice['party_phone'])): ?><div>Phone: <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-6">
              <?php if (!empty($invoice['party_gstin'])): ?><div>GSTIN: <span class="font-monospace fw-bold"><?= htmlspecialchars($invoice['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
              <div>State: <?= htmlspecialchars($invoice['party_state'] ?? '--', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <table class="table bordered-table mb-0 w-100">
          <thead>
            <tr class="text-center align-middle">
              <th style="width: 28px;">#</th>
              <th class="text-start">Item Description</th>
              <th style="width: 70px;">HSN/SAC</th>
              <th style="width: 50px;">Qty</th>
              <th style="width: 45px;">Unit</th>
              <th style="width: 75px;" class="text-end">Rate (₹)</th>
              <?php if ($isGst): ?>
                <th style="width: 55px;">GST %</th>
              <?php endif; ?>
              <th style="width: 85px;" class="text-end">Amount (₹)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $iIdx => $it): ?>
                <tr>
                  <td class="text-center"><?= $iIdx + 1 ?></td>
                  <td class="fw-semibold"><?= htmlspecialchars($it['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center font-monospace"><?= htmlspecialchars($it['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center fw-bold"><?= htmlspecialchars((string)($it['quantity'] ?? 1), ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-center"><?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-end">₹ <?= number_format((float)($it['rate'] ?? 0), 2) ?></td>
                  <?php if ($isGst): ?>
                    <td class="text-center"><?= (float)($it['tax_rate'] ?? 0) ?>%</td>
                  <?php endif; ?>
                  <td class="text-end fw-bold">₹ <?= number_format((float)($it['total_amount'] ?? 0), 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Summary & Totals -->
        <div class="row g-0">
          <div class="col-7 p-2 border-end border-dark" style="font-size: 10px;">
            <?php if (!empty($firm['bank_name']) || !empty($firm['bank_account_no']) || !empty($firm['qr_code_path'])): ?>
              <div class="p-1 border rounded bg-white mb-2 d-flex justify-content-between align-items-center">
                <div>
                  <strong>Bank:</strong> <?= htmlspecialchars($firm['bank_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> | 
                  <strong>A/C:</strong> <?= htmlspecialchars($firm['bank_account_no'] ?? '', ENT_QUOTES, 'UTF-8') ?> | 
                  <strong>IFSC:</strong> <?= htmlspecialchars($firm['bank_ifsc'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  <?php if (!empty($firm['upi_id'])): ?> | <strong>UPI:</strong> <?= htmlspecialchars($firm['upi_id'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                </div>
                <?php if (!empty($firm['qr_code_path'])): ?>
                  <img src="<?= htmlspecialchars($firm['qr_code_path'], ENT_QUOTES, 'UTF-8') ?>" alt="QR" style="height: 36px; width: 36px; object-fit: contain;" class="ms-1 border p-0 bg-white">
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <div>Terms: <?= htmlspecialchars(!empty($invoice['terms']) ? $invoice['terms'] : 'Thank you for your business!', ENT_QUOTES, 'UTF-8') ?></div>
          </div>

          <div class="col-5 p-2">
            <table class="table table-borderless table-sm mb-0" style="font-size: 11px;">
              <tr>
                <td class="p-0 text-muted">Sub Total:</td>
                <td class="p-0 text-end fw-semibold">₹ <?= number_format($cleanTaxable, 2) ?></td>
              </tr>
              <?php if ($isGst && (float)($invoice['tax_amount'] ?? 0) > 0): ?>
                <?php if (!empty($invoice['is_interstate'])): ?>
                  <tr>
                    <td class="p-0 text-muted">IGST:</td>
                    <td class="p-0 text-end">₹ <?= number_format((float)($invoice['igst_amount'] ?? 0), 2) ?></td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td class="p-0 text-muted">CGST + SGST:</td>
                    <td class="p-0 text-end">₹ <?= number_format((float)($invoice['cgst_amount'] ?? 0) + (float)($invoice['sgst_amount'] ?? 0), 2) ?></td>
                  </tr>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ((float)($invoice['round_off'] ?? 0) != 0): ?>
                <tr>
                  <td class="p-0 text-muted">Round Off:</td>
                  <td class="p-0 text-end">₹ <?= number_format((float)$invoice['round_off'], 2) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="border-top border-dark">
                <td class="p-0 fw-bold fs-6">Grand Total:</td>
                <td class="p-0 text-end fw-bold fs-6">₹ <?= number_format($cleanGrand, 2) ?></td>
              </tr>
              <tr>
                <td class="p-0 text-success fw-medium">Paid:</td>
                <td class="p-0 text-end text-success fw-bold">₹ <?= number_format($cleanPaid, 2) ?></td>
              </tr>
              <tr>
                <td class="p-0 text-danger fw-bold">Balance Due:</td>
                <td class="p-0 text-end text-danger fw-bold">₹ <?= number_format($cleanDue, 2) ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Footer Signatory -->
        <div class="row g-0 border-top border-dark p-2 align-items-end" style="font-size: 10px;">
          <div class="col-8">
            Digitally generated record &bull; <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?>
          </div>
          <div class="col-4 text-end">
            <div style="height: 25px;"></div>
            <div class="fw-bold">Authorized Signatory</div>
          </div>
        </div>

      </div>
    </div>
  <?php endforeach; ?>

  <script>
    // Auto-open print dialog on load
    window.addEventListener('load', () => {
      setTimeout(() => {
        window.print();
      }, 400);
    });
  </script>
</body>
</html>
