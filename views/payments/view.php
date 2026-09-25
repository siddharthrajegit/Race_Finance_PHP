<?php
$isPaymentIn = ($payment['type'] ?? '') === 'payment_in';
$cleanPhone = preg_replace('/[^0-9]/', '', (string)($payment['party_phone'] ?? ''));
$waPhone = (strlen($cleanPhone) === 10) ? '91' . $cleanPhone : $cleanPhone;
$firmName = $firm['name'] ?? 'RACE FINANCE';
$voucherNo = $payment['payment_number'] ?? '';
$payDate = $payment['payment_date'] ?? '';
$payAmount = number_format((float)($payment['amount'] ?? 0), 2);
$payMode = strtoupper($payment['payment_mode'] ?? 'Cash');
$refNo = $payment['reference_no'] ?? '';
$partyName = $payment['party_name'] ?? '';

$waText = urlencode(
    "*" . ($isPaymentIn ? 'Payment Receipt' : 'Payment Voucher') . " from {$firmName}*\n" .
    "Voucher No: *{$voucherNo}*\n" .
    "Date: {$payDate}\n" .
    "Amount: *₹ {$payAmount}*\n" .
    "Payment Mode: *{$payMode}*\n" .
    (!empty($refNo) ? "Ref / Txn No: {$refNo}\n" : '') .
    "\nDear {$partyName}, thank you for your transaction!"
);
$waUrl = "whatsapp://send?phone={$waPhone}&text={$waText}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isPaymentIn ? 'Payment Receipt' : 'Payment Voucher' ?> - <?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?> | RACE FINANCE</title>
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

  <style>
    :root {
      --theme-primary: <?= $isPaymentIn ? '#065f46' : '#1e3a8a' ?>;
      --theme-accent: <?= $isPaymentIn ? '#059669' : '#2563eb' ?>;
      --theme-light: <?= $isPaymentIn ? '#ecfdf5' : '#eff6ff' ?>;
      --theme-border: <?= $isPaymentIn ? '#a7f3d0' : '#bfdbfe' ?>;
    }

    body {
      background-color: #f1f5f9;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      color: #1e293b;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    /* Top Static Action Toolbar */
    .top-action-bar {
      position: relative;
      background: #0f172a;
      color: #fff;
      padding: 0.75rem 1.25rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .slip-container {
      max-width: 190mm;
      margin: 1.5rem auto 3rem auto;
      padding: 0 1rem;
    }

    .slip-paper {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
      border: 1px solid #e2e8f0;
      overflow: hidden;
      position: relative;
    }

    .slip-top-strip {
      height: 8px;
      background: linear-gradient(90deg, var(--theme-primary), var(--theme-accent));
    }

    .slip-inner {
      padding: 2.2rem;
    }

    .firm-name {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--theme-primary);
      letter-spacing: -0.02em;
    }

    .slip-title-badge {
      display: inline-block;
      background: var(--theme-light);
      color: var(--theme-primary);
      border: 1.5px solid var(--theme-border);
      padding: 0.35rem 1rem;
      border-radius: 8px;
      font-weight: 800;
      font-size: 0.95rem;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .info-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 1rem 1.15rem;
      height: 100%;
    }

    .info-card-header {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--theme-accent);
      margin-bottom: 0.4rem;
    }

    .amount-hero-box {
      background: var(--theme-light);
      border: 2px dashed var(--theme-border);
      border-radius: 12px;
      padding: 1.25rem;
      text-align: center;
      margin: 1.5rem 0;
    }

    .amount-display {
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--theme-primary);
      letter-spacing: -0.02em;
    }

    .sign-box {
      border-top: 1.5px solid #cbd5e1;
      min-width: 170px;
      text-align: center;
      padding-top: 0.4rem;
    }

    @media print {
      body {
        background-color: #ffffff !important;
        padding: 0 !important;
      }
      .no-print, .top-action-bar, .modal-backdrop, .modal {
        display: none !important;
      }
      .slip-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      .slip-paper {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
      }
      .slip-inner {
        padding: 8mm !important;
      }
    }
  </style>
</head>
<body>

  <!-- Top Sticky Action Toolbar -->
  <header class="top-action-bar no-print">
    <div class="container-fluid px-lg-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <!-- Left: Back link and Slip Badge -->
      <div class="d-flex align-items-center gap-3">
        <?php if (!empty($payment['party_id'])): ?>
          <a href="/parties/ledger/<?= $payment['party_id'] ?>" class="btn btn-outline-light btn-sm d-flex align-items-center rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Ledger
          </a>
        <?php else: ?>
          <a href="/payments" class="btn btn-outline-light btn-sm d-flex align-items-center rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Payments
          </a>
        <?php endif; ?>
        <div>
          <div class="fw-bold fs-6 d-flex align-items-center gap-2">
            <span><?= $isPaymentIn ? 'Payment Receipt' : 'Payment Voucher' ?>: <span class="font-monospace text-warning"><?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?></span></span>
            <span class="badge <?= $isPaymentIn ? 'bg-success' : 'bg-danger' ?> rounded-pill small">
              <?= $isPaymentIn ? 'RECEIVED' : 'PAID' ?>
            </span>
          </div>
          <small class="text-white-50"><?= htmlspecialchars($partyName, ENT_QUOTES, 'UTF-8') ?> | ₹ <?= $payAmount ?></small>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- 1. Download PDF Button -->
        <button id="btnDownloadSlipPdf" class="btn btn-primary btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center">
          <i class="bi bi-download me-1"></i> Download Slip
        </button>

        <!-- 2. Print Button -->
        <button onclick="window.print()" class="btn btn-secondary btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center">
          <i class="bi bi-printer me-1"></i> Print Slip
        </button>

        <!-- 3. Direct Send to WhatsApp Button -->
        <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center">
          <i class="bi bi-whatsapp me-1"></i> WhatsApp Slip
        </a>

        <!-- Delete Payment -->
        <form action="/payments/delete/<?= $payment['id'] ?>" method="POST" class="d-inline m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete this payment slip? Party balances will be adjusted automatically.">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
            <i class="bi bi-trash me-1"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </header>

  <!-- Main Slip Paper Area -->
  <div class="slip-container">
    <div class="slip-paper" id="paymentSlipArea">
      <!-- Top Colored Accent Bar -->
      <div class="slip-top-strip"></div>

      <div class="slip-inner">
        <!-- 1. Header: Firm Profile & Receipt Meta -->
        <div class="row align-items-start mb-4 pb-3 border-bottom">
          <div class="col-7">
            <div class="d-flex align-items-center mb-2">
              <?php if (!empty($firm['logo_path'])): ?>
                <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded border p-1 me-3 bg-white" style="max-height: 48px; max-width: 85px; object-fit: contain;">
              <?php endif; ?>
              <div>
                <h3 class="firm-name mb-0"><?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (!empty($firm['gstin'])): ?>
                  <div class="small fw-semibold text-muted font-monospace mt-1">
                    GSTIN: <span class="text-dark"><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="small text-muted pe-3" style="font-size: 0.82rem; line-height: 1.45;">
              <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php 
                $fLoc = array_filter([$firm['city'] ?? '', $firm['state'] ?? '']);
                if (!empty($fLoc)):
              ?>
                <div><?= htmlspecialchars(implode(', ', $fLoc), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($firm['pincode'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <?php if (!empty($firm['phone'])): ?><div><i class="bi bi-telephone text-secondary me-1"></i> <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firm['email'])): ?><div><i class="bi bi-envelope text-secondary me-1"></i> <?= htmlspecialchars($firm['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
          </div>

          <div class="col-5 text-end">
            <div class="slip-title-badge mb-2">
              <?= $isPaymentIn ? 'PAYMENT RECEIPT' : 'PAYMENT VOUCHER' ?>
            </div>
            <div class="small text-muted" style="font-size: 0.84rem;">
              <div><strong>Voucher No:</strong> <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?></span></div>
              <div><strong>Date:</strong> <span class="text-dark"><?= htmlspecialchars($payDate, ENT_QUOTES, 'UTF-8') ?></span></div>
              <div><strong>Payment Mode:</strong> <span class="badge bg-light text-dark border text-uppercase fw-semibold"><?= htmlspecialchars($payment['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php if (!empty($payment['reference_no'])): ?>
                <div><strong>Ref / Txn ID:</strong> <span class="font-monospace text-dark"><?= htmlspecialchars($payment['reference_no'], ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- 2. Party Information Card -->
        <div class="info-card mb-3">
          <div class="info-card-header">
            <i class="bi bi-person-check me-1"></i> <?= $isPaymentIn ? 'Received From (Customer)' : 'Paid To (Supplier / Vendor)' ?>
          </div>
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($partyName, ENT_QUOTES, 'UTF-8') ?></div>
              <div class="small text-muted mt-1" style="font-size: 0.82rem; line-height: 1.45;">
                <?php if (!empty($payment['party_phone'])): ?><div><strong>Phone:</strong> <?= htmlspecialchars($payment['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if (!empty($payment['party_gstin'])): ?><div><strong>GSTIN:</strong> <span class="font-monospace fw-medium text-dark"><?= htmlspecialchars($payment['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                <?php if (!empty($payment['party_address'])): ?><div><strong>Address:</strong> <?= htmlspecialchars($payment['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if (!empty($payment['party_state'])): ?><div><strong>State:</strong> <?= htmlspecialchars($payment['party_state'], ENT_QUOTES, 'UTF-8') ?> <?php if (!empty($payment['party_state_code'])): ?>(Code: <?= htmlspecialchars($payment['party_state_code'], ENT_QUOTES, 'UTF-8') ?>)<?php endif; ?></div><?php endif; ?>
              </div>
            </div>
            <?php if (!empty($payment['party_id'])): ?>
              <a href="/parties/ledger/<?= $payment['party_id'] ?>" class="btn btn-outline-primary btn-sm no-print">
                <i class="bi bi-journal-text me-1"></i> View Full Ledger
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- 3. Amount Hero Display -->
        <div class="amount-hero-box">
          <div class="small text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            <?= $isPaymentIn ? 'Total Amount Received' : 'Total Amount Paid' ?>
          </div>
          <div class="amount-display">
            ₹ <?= $payAmount ?>
          </div>
          <div class="small text-muted mt-1 font-monospace">
            Payment Mode: <strong class="text-uppercase text-dark"><?= htmlspecialchars($payment['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8') ?></strong>
            <?php if (!empty($payment['reference_no'])): ?> | Ref: <strong class="text-dark"><?= htmlspecialchars($payment['reference_no'], ENT_QUOTES, 'UTF-8') ?></strong><?php endif; ?>
          </div>
        </div>

        <!-- 4. Notes & Linked Settlement Details -->
        <div class="row g-3 mb-4">
          <div class="col-md-7">
            <div class="p-3 border rounded bg-light h-100">
              <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.72rem;">Transaction Notes / Description:</div>
              <div class="small text-dark" style="font-size: 0.82rem;">
                <?= htmlspecialchars($payment['notes'] ?? 'Payment recorded and allocated against account ledger balance.', ENT_QUOTES, 'UTF-8') ?>
              </div>
              <?php if (!empty($payment['linked_invoice_number'])): ?>
                <div class="mt-2 pt-2 border-top small text-primary">
                  <i class="bi bi-link-45deg"></i> Linked to Bill: <strong><?= htmlspecialchars($payment['linked_invoice_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-md-5">
            <div class="p-3 border rounded bg-white h-100 text-center d-flex flex-column justify-content-between">
              <div class="small text-muted" style="font-size: 0.75rem;">
                Digitally generated transaction receipt.<br>
                Valid without physical signature.
              </div>
              <div class="mt-3">
                <?php if (!empty($firm['signature_path'])): ?>
                  <img src="<?= htmlspecialchars($firm['signature_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Signature" style="max-height: 38px; max-width: 120px;" class="mb-1">
                <?php else: ?>
                  <div style="height: 32px;"></div>
                <?php endif; ?>
                <div class="sign-box">
                  <small class="fw-bold text-dark d-block" style="font-size: 0.78rem;">For <?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></small>
                  <small class="text-muted" style="font-size: 0.7rem;">Authorized Signatory</small>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap 5 Bundle JS (Offline Local with CDN Fallback) -->
  <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>if(typeof bootstrap==='undefined'){document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>');}</script>
  <script>
    document.getElementById('btnDownloadSlipPdf').addEventListener('click', function () {
      const element = document.getElementById('paymentSlipArea');
      const voucherNo = '<?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?>';
      const btn = this;
      const originalText = btn.innerHTML;

      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating PDF...';
      btn.disabled = true;

      const opt = {
        margin: [8, 8, 8, 8],
        filename: `PaymentSlip_${voucherNo}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      html2pdf().set(opt).from(element).save().then(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }).catch(err => {
        console.error('PDF error:', err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        window.print();
      });
    });
  </script>
</body>
</html>
