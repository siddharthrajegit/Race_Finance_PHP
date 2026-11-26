<?php
require_once __DIR__ . '/../../core/SvgQrCode.php';
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$width = (($_GET['width'] ?? '80') === '58') ? '58mm' : '80mm';
$upiWidget = SvgQrCode::renderUpiWidget($firm, (float)($invoice['balance_due'] > 0 ? $invoice['balance_due'] : $invoice['grand_total']), (string)$invoice['invoice_number'], 96);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Thermal Receipt', ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/receipt.css">
</head>
<body class="receipt-page">
  <div class="receipt-controls no-print">
    <div>
      <a href="/invoices/receipt/<?= (int)$invoice['id'] ?>?width=80" style="margin-right:8px;">80mm Roll</a>
      <a href="/invoices/receipt/<?= (int)$invoice['id'] ?>?width=58">58mm Roll</a>
    </div>
    <div>
      <button type="button" onclick="window.print()" style="padding:4px 10px;cursor:pointer;">Print Receipt</button>
      <a href="/invoices/view/<?= (int)$invoice['id'] ?>" style="margin-left:8px;">Back</a>
    </div>
  </div>

  <div class="receipt-paper width-<?= htmlspecialchars($width, ENT_QUOTES, 'UTF-8') ?>">
    <div class="text-center">
      <div class="fw-bold" style="font-size:14px;"><?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
      <?php if (!empty($firm['address'])): ?>
        <div><?= htmlspecialchars($firm['address'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <?php if (!empty($firm['phone'])): ?>
        <div>Ph: <?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <?php if (!empty($firm['gstin'])): ?>
        <div>GSTIN: <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
    </div>

    <div class="receipt-divider"></div>
    <div>
      <div><strong>Bill #:</strong> <?= htmlspecialchars($invoice['invoice_number'], ENT_QUOTES, 'UTF-8') ?> &nbsp; <strong>Date:</strong> <?= htmlspecialchars(formatDate($invoice['invoice_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></div>
      <div><strong>Party:</strong> <?= htmlspecialchars($invoice['party_name'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php if (!empty($invoice['party_phone'])): ?>
        <div><strong>Ph:</strong> <?= htmlspecialchars($invoice['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
    </div>
    <div class="receipt-divider"></div>

    <table class="receipt-table">
      <thead>
        <tr>
          <th style="text-align:left;">Item</th>
          <th class="text-end">Qty</th>
          <th class="text-end">Rate</th>
          <th class="text-end">Amt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($invoice['items'] ?? []) as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end"><?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></td>
            <td class="text-end"><?= number_format((float)$it['rate'], 2) ?></td>
            <td class="text-end"><?= number_format((float)$it['total_amount'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="receipt-divider"></div>
    <table class="receipt-table">
      <tr><td>Subtotal</td><td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$invoice['subtotal'], 2) ?></td></tr>
      <?php if ((float)$invoice['discount_amount'] > 0): ?>
        <tr><td>Discount</td><td class="text-end">- <?= number_format((float)$invoice['discount_amount'], 2) ?></td></tr>
      <?php endif; ?>
      <?php if (!empty($invoice['is_gst_bill']) && (float)$invoice['tax_amount'] > 0): ?>
        <tr><td>GST Tax</td><td class="text-end"><?= number_format((float)$invoice['tax_amount'], 2) ?></td></tr>
      <?php endif; ?>
      <?php if (abs((float)$invoice['round_off']) > 0.001): ?>
        <tr><td>Round Off</td><td class="text-end"><?= number_format((float)$invoice['round_off'], 2) ?></td></tr>
      <?php endif; ?>
      <tr class="fw-bold" style="font-size:13px;">
        <td>GRAND TOTAL</td>
        <td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$invoice['grand_total'], 2) ?></td>
      </tr>
      <tr><td>Paid (<?= htmlspecialchars(strtoupper((string)$invoice['payment_mode']), ENT_QUOTES, 'UTF-8') ?>)</td><td class="text-end"><?= number_format((float)$invoice['paid_amount'], 2) ?></td></tr>
      <?php if ((float)$invoice['balance_due'] > 0): ?>
        <tr class="fw-bold"><td>Balance Due</td><td class="text-end"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$invoice['balance_due'], 2) ?></td></tr>
      <?php endif; ?>
    </table>

    <?php if ($upiWidget !== ''): ?>
      <div class="receipt-divider"></div>
      <div class="text-center">
        <div style="font-size:10px;font-weight:bold;margin-bottom:4px;">SCAN TO PAY VIA UPI</div>
        <?= $upiWidget ?>
        <div style="font-size:9px;color:#555;margin-top:3px;">Customer scan only; confirm receipt in bank/UPI app</div>
      </div>
    <?php endif; ?>

    <div class="receipt-divider"></div>
    <div class="text-center" style="font-size:10px;">
      Thank you for your business!
    </div>
  </div>

  <script src="/js/vendor/qrcode.min.js"></script>
  <script src="/js/barcode.js"></script>
</body>
</html>
