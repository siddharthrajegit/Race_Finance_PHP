<?php
$isPaymentIn = ($payment['type'] ?? '') === 'payment_in';
$cleanPhone = preg_replace('/[^0-9]/', '', (string)($payment['party_phone'] ?? ''));
$waPhone = (strlen($cleanPhone) === 10) ? '91' . $cleanPhone : $cleanPhone;
$firmName = $firm['name'] ?? 'RACE FINANCE';
$voucherNo = $payment['payment_number'] ?? '';
$payDate = formatDate($payment['payment_date'] ?? '');
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
  <!-- Compiled Tailwind CSS -->
  <link rel="stylesheet" href="/css/tailwind.css">
  <!-- Bootstrap Icons (Offline Local with CDN Fallback) -->
  <link rel="stylesheet" href="/vendor/bootstrap-icons/bootstrap-icons.min.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';">
  <!-- html2pdf.js for instant PDF generation (Offline Local with CDN Fallback) -->
  <script src="/vendor/html2pdf/html2pdf.bundle.min.js"></script>
  <script>if(typeof html2pdf==='undefined'){document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');}</script>

  <style>
    :root {
      --theme-primary: <?= $isPaymentIn ? '#2E5A36' : '#2B526E' ?>;
      --theme-accent: <?= $isPaymentIn ? '#3B7045' : '#3C6B8D' ?>;
      --theme-light: <?= $isPaymentIn ? '#EDF3EC' : '#EBF2F7' ?>;
      --theme-border: <?= $isPaymentIn ? '#CBDCC8' : '#C9DBE8' ?>;
    }

    body {
      background-color: #F7F6F3;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      color: #1C1B18;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .top-action-bar {
      position: relative;
      background: #1C1B18;
      color: #F7F6F3;
      padding: 0.75rem 1.25rem;
      border-bottom: 1px solid #302E29;
    }

    .slip-container {
      max-width: 190mm;
      margin: 2rem auto 3.5rem auto;
      padding: 0 1rem;
    }

    .slip-paper {
      background: #FFFFFF;
      border-radius: 12px;
      box-shadow: 0 8px 24px -8px rgba(28, 27, 24, 0.08);
      border: 1px solid #E6E4DF;
      overflow: hidden;
      position: relative;
    }

    .slip-top-strip {
      height: 6px;
      background: var(--theme-primary);
    }

    .slip-inner {
      padding: 2.25rem;
    }

    .firm-name {
      font-size: 1.35rem;
      font-weight: 700;
      color: #1C1B18;
      letter-spacing: -0.02em;
    }

    .slip-title-badge {
      display: inline-block;
      background: var(--theme-light);
      color: var(--theme-primary);
      border: 1px solid var(--theme-border);
      padding: 0.35rem 0.9rem;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.85rem;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .info-card {
      background: #FAF9F6;
      border: 1px solid #E6E4DF;
      border-radius: 10px;
      padding: 1rem 1.2rem;
    }

    .info-card-header {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--theme-primary);
      margin-bottom: 0.45rem;
    }

    .amount-hero-box {
      background: var(--theme-light);
      border: 1.5px dashed var(--theme-border);
      border-radius: 12px;
      padding: 1.4rem;
      text-align: center;
      margin: 1.5rem 0;
    }

    .amount-display {
      font-size: 2.1rem;
      font-weight: 700;
      font-family: 'JetBrains Mono', monospace;
      color: var(--theme-primary);
      letter-spacing: -0.02em;
    }

    .sign-box {
      border-top: 1px solid #D4D0C7;
      min-width: 170px;
      text-align: center;
      padding-top: 0.45rem;
    }

    @media print {
      body {
        background-color: #ffffff !important;
        padding: 0 !important;
      }
      .no-print, .top-action-bar, .rf-modal-backdrop, .rf-modal {
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

  <!-- Top Action Toolbar -->
  <header class="top-action-bar no-print">
    <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-3">
      <!-- Left: Back link and Slip Badge -->
      <div class="flex flex-wrap items-center gap-3">
        <?php if (!empty($payment['party_id'])): ?>
          <a href="/parties/ledger/<?= $payment['party_id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-white/20 text-xs font-medium text-white/90 hover:bg-white/10 transition-colors">
            <i class="bi bi-arrow-left"></i> Back to Ledger
          </a>
        <?php else: ?>
          <a href="/payments" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-white/20 text-xs font-medium text-white/90 hover:bg-white/10 transition-colors">
            <i class="bi bi-arrow-left"></i> Back to Payments
          </a>
        <?php endif; ?>
        <div>
          <div class="font-semibold text-sm flex items-center gap-2">
            <span><?= $isPaymentIn ? 'Payment Receipt' : 'Payment Voucher' ?>: <span class="font-mono text-amber-300"><?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?></span></span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= $isPaymentIn ? 'bg-emerald-900/60 text-emerald-200 border border-emerald-700/50' : 'bg-rose-900/60 text-rose-200 border border-rose-700/50' ?>">
              <?= $isPaymentIn ? 'RECEIVED' : 'PAID' ?>
            </span>
          </div>
          <div class="text-xs text-white/60"><?= htmlspecialchars($partyName, ENT_QUOTES, 'UTF-8') ?> | ₹ <?= $payAmount ?></div>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="flex flex-wrap items-center gap-2">
        <button id="btnDownloadSlipPdf" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white text-ink text-xs font-semibold hover:bg-white/90 transition-colors cursor-pointer">
          <i class="bi bi-download"></i> Download Slip
        </button>

        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white/15 text-white text-xs font-medium hover:bg-white/25 transition-colors cursor-pointer">
          <i class="bi bi-printer"></i> Print Slip
        </button>

        <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#2E5A36] text-white text-xs font-semibold hover:bg-[#254A2C] transition-colors">
          <i class="bi bi-whatsapp"></i> WhatsApp Slip
        </a>

        <form action="/payments/delete/<?= $payment['id'] ?>" method="POST" class="inline m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete this payment slip? Party balances will be adjusted automatically.">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-400/50 text-rose-200 text-xs font-medium hover:bg-rose-950/60 transition-colors cursor-pointer">
            <i class="bi bi-trash"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </header>

  <!-- Main Slip Paper Area -->
  <div class="slip-container">
    <div class="slip-paper" id="paymentSlipArea">
      <div class="slip-top-strip"></div>

      <div class="slip-inner">
        <!-- 1. Header: Firm Profile & Receipt Meta -->
        <div class="grid grid-cols-12 gap-4 items-start mb-6 pb-4 border-b border-border">
          <div class="col-span-7">
            <div class="flex items-center gap-3 mb-2">
              <?php if (!empty($firm['logo_path'])): ?>
                <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded border border-border p-1 bg-white max-h-12 max-w-[85px] object-contain">
              <?php endif; ?>
              <div>
                <h3 class="firm-name mb-0"><?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (!empty($firm['gstin'])): ?>
                  <div class="text-xs font-medium text-ink-muted font-mono mt-0.5">
                    GSTIN: <span class="text-ink"><?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-xs text-ink-muted pr-3 leading-relaxed space-y-0.5">
              <?php if (!empty($firm['address'])): ?><div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php 
                $fLoc = array_filter([$firm['city'] ?? '', $firm['state'] ?? '']);
                if (!empty($fLoc)):
              ?>
                <div><?= htmlspecialchars(implode(', ', $fLoc), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($firm['pincode'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <?php if (!empty($firm['phone'])): ?><div><i class="bi bi-telephone text-ink-subtle mr-1"></i> <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($firm['email'])): ?><div><i class="bi bi-envelope text-ink-subtle mr-1"></i> <?= htmlspecialchars($firm['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
          </div>

          <div class="col-span-5 text-right">
            <div class="slip-title-badge mb-2">
              <?= $isPaymentIn ? 'PAYMENT RECEIPT' : 'PAYMENT VOUCHER' ?>
            </div>
            <div class="text-xs text-ink-muted space-y-1">
              <div><strong class="text-ink">Voucher No:</strong> <span class="font-mono font-semibold text-ink"><?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?></span></div>
              <div><strong class="text-ink">Date:</strong> <span class="text-ink"><?= htmlspecialchars($payDate, ENT_QUOTES, 'UTF-8') ?></span></div>
              <div>
                <strong class="text-ink">Payment Mode:</strong>
                <span class="rf-badge rf-badge-neutral uppercase font-medium"><?= htmlspecialchars($payment['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <?php if (!empty($payment['reference_no'])): ?>
                <div><strong class="text-ink">Ref / Txn ID:</strong> <span class="font-mono text-ink"><?= htmlspecialchars($payment['reference_no'], ENT_QUOTES, 'UTF-8') ?></span></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- 2. Party Information Card -->
        <div class="info-card mb-4">
          <div class="info-card-header">
            <i class="bi bi-person-check mr-1"></i> <?= $isPaymentIn ? 'Received From (Customer)' : 'Paid To (Supplier / Vendor)' ?>
          </div>
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <div class="font-semibold text-ink text-lg"><?= htmlspecialchars($partyName, ENT_QUOTES, 'UTF-8') ?></div>
              <div class="text-xs text-ink-muted mt-1 leading-relaxed space-y-0.5">
                <?php if (!empty($payment['party_phone'])): ?><div><strong class="text-ink">Phone:</strong> <?= htmlspecialchars($payment['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if (!empty($payment['party_gstin'])): ?><div><strong class="text-ink">GSTIN:</strong> <span class="font-mono font-medium text-ink"><?= htmlspecialchars($payment['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                <?php if (!empty($payment['party_address'])): ?><div><strong class="text-ink">Address:</strong> <?= htmlspecialchars($payment['party_address'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if (!empty($payment['party_state'])): ?><div><strong class="text-ink">State:</strong> <?= htmlspecialchars($payment['party_state'], ENT_QUOTES, 'UTF-8') ?> <?php if (!empty($payment['party_state_code'])): ?>(Code: <?= htmlspecialchars($payment['party_state_code'], ENT_QUOTES, 'UTF-8') ?>)<?php endif; ?></div><?php endif; ?>
              </div>
            </div>
            <?php if (!empty($payment['party_id'])): ?>
              <a href="/parties/ledger/<?= $payment['party_id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm no-print">
                <i class="bi bi-journal-text"></i> View Full Ledger
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- 3. Amount Hero Display -->
        <div class="amount-hero-box">
          <div class="text-[11px] uppercase font-semibold tracking-wider text-ink-muted mb-1">
            <?= $isPaymentIn ? 'Total Amount Received' : 'Total Amount Paid' ?>
          </div>
          <div class="amount-display">
            ₹ <?= $payAmount ?>
          </div>
          <div class="text-xs text-ink-muted mt-1.5 font-mono">
            Payment Mode: <strong class="uppercase text-ink"><?= htmlspecialchars($payment['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8') ?></strong>
            <?php if (!empty($payment['reference_no'])): ?> | Ref: <strong class="text-ink"><?= htmlspecialchars($payment['reference_no'], ENT_QUOTES, 'UTF-8') ?></strong><?php endif; ?>
          </div>
        </div>

        <!-- 4. Notes & Linked Settlement Details -->
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-7">
            <div class="p-4 rounded-lg border border-border bg-canvas/70 h-full">
              <div class="text-[11px] uppercase font-semibold tracking-wider text-ink-muted mb-1.5">Transaction Notes / Description</div>
              <div class="text-xs text-ink leading-relaxed">
                <?= htmlspecialchars($payment['notes'] ?? 'Payment recorded and allocated against account ledger balance.', ENT_QUOTES, 'UTF-8') ?>
              </div>
              <?php if (!empty($payment['linked_invoice_number'])): ?>
                <div class="mt-3 pt-2 border-t border-border-subtle text-xs text-slate font-medium">
                  <i class="bi bi-link-45deg"></i> Linked to Bill: <strong><?= htmlspecialchars($payment['linked_invoice_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-span-5">
            <div class="p-4 rounded-lg border border-border bg-white h-full text-center flex flex-col justify-between">
              <div class="text-[11px] text-ink-muted leading-relaxed">
                Digitally generated transaction receipt.<br>
                Valid without physical signature.
              </div>
              <div class="mt-4">
                <?php if (!empty($firm['signature_path'])): ?>
                  <img src="<?= htmlspecialchars($firm['signature_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Signature" class="max-h-10 max-w-[120px] mx-auto mb-1 object-contain">
                <?php else: ?>
                  <div class="h-8"></div>
                <?php endif; ?>
                <div class="sign-box">
                  <div class="font-semibold text-ink text-xs">For <?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="text-ink-muted text-[11px]">Authorized Signatory</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="/js/ui.js"></script>
  <script>
    document.querySelectorAll('.form-delete-confirm').forEach(form => {
      form.addEventListener('submit', function (e) {
        const msg = this.getAttribute('data-confirm-message') || 'Are you sure?';
        if (!window.confirm(msg)) {
          e.preventDefault();
        }
      });
    });

    document.getElementById('btnDownloadSlipPdf').addEventListener('click', function () {
      const element = document.getElementById('paymentSlipArea');
      const voucherNo = '<?= htmlspecialchars($voucherNo, ENT_QUOTES, 'UTF-8') ?>';
      const btn = this;
      const originalText = btn.innerHTML;

      btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-1"></i> Generating PDF...';
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
