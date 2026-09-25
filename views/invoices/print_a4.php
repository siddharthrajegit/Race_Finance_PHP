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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
  <!-- Bootstrap Icons (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <link rel="stylesheet" href="/css/style.css">
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      color: #111;
      margin: 0;
      padding: 20px 0;
    }
    .print-wrapper {
      max-width: 210mm;
      margin: 0 auto;
      background: #fff;
      padding: 15mm;
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
      body {
        background-color: #fff !important;
        padding: 0 !important;
      }
      .print-wrapper {
        border: none !important;
        box-shadow: none !important;
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
  <div class="container mb-3 no-print" style="max-width: 210mm;">
    <div class="d-flex justify-content-between align-items-center bg-dark text-white p-2 rounded shadow-sm">
      <div class="d-flex align-items-center">
        <i class="bi bi-printer fs-5 me-2 text-warning"></i>
        <span class="fw-semibold">Print Digital Record (<?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?>)</span>
      </div>
      <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
          <i class="bi bi-printer me-1"></i> Print / Save PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-light btn-sm px-3">
          Close
        </button>
      </div>
    </div>
  </div>

  <div class="print-wrapper">
    <div class="tax-invoice-box">
      <!-- Record Title Bar -->
      <div class="text-center py-1 border-bottom border-dark bg-light">
        <h5 class="fw-bold mb-0 text-uppercase letter-spacing-1" style="font-size: 14px;">
          <?= $isGst ? ($isPurchase ? 'DIGITAL PURCHASE RECORD' : 'DIGITAL SALES RECORD') : ($isPurchase ? 'PURCHASE RECORD' : 'SALES RECORD') ?>
        </h5>
        <div class="small text-muted" style="font-size: 10px;">(Digital Reference Copy)</div>
      </div>

      <!-- Seller & Invoice Header -->
      <div class="row g-0 border-bottom border-dark">
        <!-- Left: Seller Info -->
        <div class="col-7 p-2 border-end border-dark">
          <div class="d-flex align-items-center mb-1">
            <?php if (!empty($firm['logo_path'])): ?>
              <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="me-2" style="max-height: 40px; max-width: 80px; object-fit: contain;">
            <?php endif; ?>
            <div>
              <h6 class="fw-bold mb-0 text-uppercase" style="font-size: 13px;"><?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></h6>
            </div>
          </div>
          <div style="font-size: 11px; line-height: 1.3;">
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
        <div class="col-5 p-2" style="font-size: 11px; line-height: 1.4;">
          <table class="table table-borderless table-sm mb-0" style="font-size: 11px;">
            <tr>
              <td class="p-0 fw-bold" style="width: 45%;">Record No:</td>
              <td class="p-0 font-monospace fw-bold"><?= htmlspecialchars($invNum, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
              <td class="p-0 fw-bold">Record Date:</td>
              <td class="p-0"><?= htmlspecialchars($invoice['invoice_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php if (!empty($invoice['due_date'])): ?>
              <tr>
                <td class="p-0 fw-bold">Due Date:</td>
                <td class="p-0"><?= htmlspecialchars($invoice['due_date'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endif; ?>
            <tr>
              <td class="p-0 fw-bold">Payment Mode:</td>
              <td class="p-0 text-uppercase"><?= strtoupper(htmlspecialchars($invoice['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></td>
            </tr>
            <tr>
              <td class="p-0 fw-bold">Place of Supply:</td>
              <td class="p-0"><?= htmlspecialchars($invoice['party_state'] ?? ($firm['state'] ?? '--'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($invoice['party_state_code'] ?? ($firm['state_code'] ?? '--'), ENT_QUOTES, 'UTF-8') ?>)</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Buyer Details (Billed To) -->
      <div class="p-2 border-bottom border-dark" style="font-size: 11px; line-height: 1.3;">
        <div class="fw-bold text-uppercase mb-1" style="font-size: 11px;">
          <?= $isPurchase ? 'Billed From (Supplier Details):' : 'Billed To (Customer Details):' ?>
        </div>
        <div class="row">
          <div class="col-7">
            <div class="fw-bold fs-6"><?= htmlspecialchars($invoice['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($invoice['party_address'])): ?><div><?= htmlspecialchars($invoice['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if (!empty($invoice['party_phone'])): ?><div><strong>Phone:</strong> <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          </div>
          <div class="col-5">
            <?php if (!empty($invoice['party_gstin'])): ?><div><strong>GSTIN / UIN:</strong> <?= htmlspecialchars($invoice['party_gstin'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <div><strong>State:</strong> <?= htmlspecialchars($invoice['party_state'] ?? '--', ENT_QUOTES, 'UTF-8') ?> (Code: <?= htmlspecialchars($invoice['party_state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div>
          </div>
        </div>
      </div>

      <!-- Line Items Table -->
      <table class="table bordered-table mb-0">
        <thead>
          <tr class="text-center">
            <th style="width: 30px;">#</th>
            <th class="text-start">Description of Goods / Services</th>
            <th style="width: 70px;">HSN/SAC</th>
            <th style="width: 45px;">Qty</th>
            <th style="width: 45px;">Unit</th>
            <th style="width: 65px;" class="text-end">Rate (₹)</th>
            <th style="width: 50px;" class="text-end">Disc %</th>
            <?php if ($isGst): ?>
              <th style="width: 70px;" class="text-end">Taxable (₹)</th>
              <?php if (!empty($invoice['is_interstate'])): ?>
                <th style="width: 70px;" class="text-end">IGST (₹)</th>
              <?php else: ?>
                <th style="width: 55px;" class="text-end">CGST (₹)</th>
                <th style="width: 55px;" class="text-end">SGST (₹)</th>
              <?php endif; ?>
            <?php endif; ?>
            <th style="width: 75px;" class="text-end">Amount (₹)</th>
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
                <td class="text-center font-monospace"><?= htmlspecialchars($item['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars((string)($item['quantity'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($item['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end"><?= number_format($rateVal, 2) ?></td>
                <td class="text-end"><?= $disc > 0 ? $disc . '%' : '--' ?></td>
                <?php if ($isGst): ?>
                  <td class="text-end"><?= number_format((float)($item['taxable_amount'] ?? 0), 2) ?></td>
                  <?php if (!empty($invoice['is_interstate'])): ?>
                    <td class="text-end"><?= number_format((float)($item['igst_amount'] ?? 0), 2) ?></td>
                  <?php else: ?>
                    <td class="text-end"><?= number_format((float)($item['cgst_amount'] ?? 0), 2) ?></td>
                    <td class="text-end"><?= number_format((float)($item['sgst_amount'] ?? 0), 2) ?></td>
                  <?php endif; ?>
                <?php endif; ?>
                <td class="text-end fw-bold"><?= number_format((float)($item['total_amount'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Total & Calculation Summary -->
      <div class="row g-0 border-top border-dark">
        <!-- Left: Bank Details & Amount in Words -->
        <div class="col-7 p-2 border-end border-dark" style="font-size: 11px;">
          <?php if (!empty($firm['bank_name']) || !empty($firm['bank_account_no']) || !empty($firm['upi_id'])): ?>
            <div class="mb-2">
              <strong class="text-uppercase" style="font-size: 10px;">Bank Account Details:</strong>
              <div>Bank: <strong><?= htmlspecialchars($firm['bank_name'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($firm['bank_branch'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</div>
              <div>A/C No: <strong><?= htmlspecialchars($firm['bank_account_no'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong></div>
              <div>IFSC Code: <strong><?= htmlspecialchars($firm['bank_ifsc'] ?? '--', ENT_QUOTES, 'UTF-8') ?></strong></div>
              <?php if (!empty($firm['upi_id'])): ?><div>UPI ID: <strong><?= htmlspecialchars($firm['upi_id'], ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($invoice['terms'])): ?>
            <div class="mt-2 text-muted" style="font-size: 10px;">
              <strong>Terms & Conditions:</strong>
              <div><?= htmlspecialchars($invoice['terms'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right: Summary Totals -->
        <div class="col-5 p-2" style="font-size: 11px;">
          <table class="table table-borderless table-sm mb-0">
            <tr>
              <td class="p-1">Taxable Amount:</td>
              <td class="p-1 text-end fw-semibold">₹ <?= number_format((float)($invoice['taxable_amount'] ?? ($invoice['subtotal'] ?? 0)), 2) ?></td>
            </tr>
            <?php if ((float)($invoice['discount_amount'] ?? 0) > 0): ?>
              <tr>
                <td class="p-1 text-danger">Discount:</td>
                <td class="p-1 text-end text-danger">- ₹ <?= number_format((float)$invoice['discount_amount'], 2) ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($isGst && (float)($invoice['tax_amount'] ?? 0) > 0): ?>
              <?php if (!empty($invoice['is_interstate'])): ?>
                <tr>
                  <td class="p-1">IGST (Integrated Tax):</td>
                  <td class="p-1 text-end">₹ <?= number_format((float)($invoice['igst_amount'] ?? $invoice['tax_amount']), 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td class="p-1">CGST:</td>
                  <td class="p-1 text-end">₹ <?= number_format((float)($invoice['cgst_amount'] ?? 0), 2) ?></td>
                </tr>
                <tr>
                  <td class="p-1">SGST:</td>
                  <td class="p-1 text-end">₹ <?= number_format((float)($invoice['sgst_amount'] ?? 0), 2) ?></td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ((float)($invoice['round_off'] ?? 0) != 0.0): ?>
              <tr>
                <td class="p-1">Round Off:</td>
                <td class="p-1 text-end">₹ <?= number_format((float)$invoice['round_off'], 2) ?></td>
              </tr>
            <?php endif; ?>
            <tr class="border-top border-dark">
              <td class="p-1 fw-bold fs-6">Grand Total:</td>
              <td class="p-1 text-end fw-bold fs-6">₹ <?= number_format((float)($invoice['grand_total'] ?? 0), 2) ?></td>
            </tr>
            <tr>
              <td class="p-1 text-success">Received / Paid:</td>
              <td class="p-1 text-end text-success fw-semibold">₹ <?= number_format((float)($invoice['paid_amount'] ?? 0), 2) ?></td>
            </tr>
            <tr>
              <td class="p-1 text-danger fw-bold">Balance Due:</td>
              <td class="p-1 text-end text-danger fw-bold">₹ <?= number_format((float)($invoice['balance_due'] ?? 0), 2) ?></td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Signatures Footer -->
      <div class="row g-0 border-top border-dark">
        <div class="col-7 p-2 border-end border-dark text-muted" style="font-size: 10px;">
          <div>Customer / Recipient Signature</div>
          <div style="height: 35px;"></div>
        </div>
        <div class="col-5 p-2 text-end" style="font-size: 11px;">
          <div class="fw-bold">For <?= htmlspecialchars($firmName, ENT_QUOTES, 'UTF-8') ?></div>
          <div style="height: 25px;"></div>
          <div class="small fw-semibold">Authorized Signatory</div>
        </div>
      </div>

      <!-- Digital Reference Utility Note -->
      <div class="p-1 text-center border-top border-dark bg-light text-muted" style="font-size: 8.5px;">
        * Note: This document is a digital business record generated for internal management and accounting data entry. It is not an official tax invoice.
      </div>
    </div>
  </div>

</body>
</html>
