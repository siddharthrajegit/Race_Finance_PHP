<?php
$pType = $type ?? 'payment_in';
$selInvId = $selectedInvoiceId ?? null;
$selPartyId = $selectedPartyId ?? null;
$nextNum = $nextPaymentNumber ?? '';
$dateToday = $today ?? date('Y-m-d');
$partyList = !empty($parties) ? $parties : [];
?>
<div class="max-w-3xl mx-auto">
  <div class="rf-card">
    <div class="rf-card-header">
      <h1 class="text-lg font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi <?= ($pType === 'payment_in') ? 'bi-arrow-down-left-circle text-[#2E5A36]' : 'bi-arrow-up-right-circle text-[#8F2D2B]' ?>"></i>
        <?= ($pType === 'payment_in') ? 'Record Payment Receipt (Received In)' : 'Record Payment Voucher (Paid Out)' ?>
      </h1>
      <a href="/payments" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
    </div>

    <div class="rf-card-body">
      <form action="/payments/create" method="POST" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="type" value="<?= htmlspecialchars($pType, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($selInvId)): ?>
          <input type="hidden" name="invoice_id" value="<?= htmlspecialchars((string)$selInvId, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="payment_number" class="rf-label-plain">Voucher / Receipt No <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" class="rf-input font-mono font-semibold" id="payment_number" name="payment_number" value="<?= htmlspecialchars($nextNum, ENT_QUOTES, 'UTF-8') ?>" pattern="[0-9]+" inputmode="numeric" required title="Payment number must contain only numbers" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="e.g. 001">
          </div>
          <div>
            <label for="payment_date" class="rf-label-plain">Payment Date <span class="text-[#8F2D2B]">*</span></label>
            <?php $activeDateFormat = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY'; ?>
            <div class="relative">
              <input type="text" class="rf-input pr-10 date-picker" id="payment_date" name="payment_date" value="<?= htmlspecialchars(formatDate($dateToday, $activeDateFormat), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" data-date-format="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="off">
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#78746C] bg-transparent border-0 cursor-pointer p-0" onclick="const el = document.getElementById('payment_date'); if (el._flatpickr) { el._flatpickr.open(); } else { el.focus(); }">
                <i class="bi bi-calendar3"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="party_id" class="rf-label-plain">Select <?= ($pType === 'payment_in') ? 'Customer' : 'Supplier' ?> <span class="text-[#8F2D2B]">*</span></label>
            <select class="rf-select" id="party_id" name="party_id" required>
              <option value="">-- Choose Party --</option>
              <?php foreach ($partyList as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (!empty($selPartyId) && $selPartyId == $p['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?> <?= !empty($p['phone']) ? '(' . htmlspecialchars($p['phone'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="amount" class="rf-label-plain">Amount Paid / Received (₹) <span class="text-[#8F2D2B]">*</span></label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-[#5C5A54]">₹</span>
              <input type="number" class="rf-input pl-8 text-right font-mono font-semibold tabular-nums" id="amount" name="amount" min="0.01" step="any" placeholder="0.00" required autofocus>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="payment_mode" class="rf-label-plain">Payment Mode</label>
            <select class="rf-select" id="payment_mode" name="payment_mode">
              <option value="cash">Cash</option>
              <option value="bank_transfer">Bank Transfer / NEFT / RTGS</option>
              <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
              <option value="cheque">Cheque</option>
            </select>
          </div>
          <div>
            <label for="reference_no" class="rf-label-plain">Reference No / Transaction ID</label>
            <input type="text" class="rf-input font-mono" id="reference_no" name="reference_no" placeholder="e.g. UTR12345678 or Cheque #001234">
          </div>
        </div>

        <div>
          <label for="notes" class="rf-label-plain">Notes / Remarks</label>
          <textarea class="rf-textarea" id="notes" name="notes" rows="2" placeholder="Optional notes..."></textarea>
        </div>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-[#ECEAE4]">
          <a href="/payments" class="rf-btn rf-btn-secondary">Cancel</a>
          <button type="submit" class="rf-btn <?= ($pType === 'payment_in') ? 'rf-btn-sage-solid' : 'rf-btn-danger-solid' ?>">
            <i class="bi bi-check2-circle"></i> Record Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
