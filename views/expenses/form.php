<?php $isEdit = !empty($expense); ?>
<div class="max-w-2xl mx-auto">
  <div class="rf-card">
    <div class="rf-card-header">
      <h1 class="text-lg font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-wallet2 text-[#8F2D2B]"></i>
        <span><?= $isEdit ? 'Edit Expense Voucher' : 'Record Business Expense' ?></span>
      </h1>
      <a href="/expenses" class="rf-btn rf-btn-secondary rf-btn-sm">Back to Expenses</a>
    </div>

    <form action="<?= $isEdit ? '/expenses/edit/' . (int)$expense['id'] : '/expenses/create' ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

      <div class="rf-card-body space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">Expense # <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" name="expense_number" class="rf-input rf-input-sm font-mono" required value="<?= htmlspecialchars($expense['expense_number'] ?? ($nextExpenseNumber ?? 'EXP-0001'), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">Expense Date <span class="text-[#8F2D2B]">*</span></label>
            <input type="date" name="expense_date" class="rf-input rf-input-sm" required value="<?= htmlspecialchars($expense['expense_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">Amount (₹) <span class="text-[#8F2D2B]">*</span></label>
            <input type="number" name="amount" class="rf-input rf-input-sm text-right font-mono tabular-nums" step="0.01" min="0.01" required value="<?= htmlspecialchars((string)($expense['amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="rf-label-plain text-xs">Category <span class="text-[#8F2D2B]">*</span></label>
            <select name="category" class="rf-select rf-select-sm">
              <?php
                $currCat = $expense['category'] ?? '';
                $catOptions = array_values(array_filter(array_map(
                    fn($c) => is_array($c) ? (string)($c['name'] ?? '') : (string)$c,
                    $categories ?? []
                ), fn($v) => $v !== ''));
                if (empty($catOptions)) {
                    $catOptions = ['Other Expense'];
                }
                if ($currCat !== '' && !in_array($currCat, $catOptions, true)) {
                    array_unshift($catOptions, $currCat);
                }
              ?>
              <?php foreach ($catOptions as $catName): ?>
                <option value="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>" <?= ($currCat === $catName) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="rf-label-plain text-xs">Or Enter New Category</label>
            <input type="text" name="new_category" class="rf-input rf-input-sm" placeholder="Custom category overrides dropdown">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-5">
            <label class="rf-label-plain text-xs">Paid To (Payee / Vendor)</label>
            <input type="text" name="payee" class="rf-input rf-input-sm" placeholder="e.g. Landlord / Electricity Board" value="<?= htmlspecialchars($expense['payee'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">Payment Mode</label>
            <select name="payment_mode" class="rf-select rf-select-sm">
              <?php $pm = $expense['payment_mode'] ?? 'cash'; ?>
              <option value="cash" <?= $pm === 'cash' ? 'selected' : '' ?>>Cash</option>
              <option value="upi" <?= $pm === 'upi' ? 'selected' : '' ?>>UPI / Wallet</option>
              <option value="bank_transfer" <?= $pm === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer / NEFT</option>
              <option value="card" <?= $pm === 'card' ? 'selected' : '' ?>>Card</option>
              <option value="cheque" <?= $pm === 'cheque' ? 'selected' : '' ?>>Cheque</option>
            </select>
          </div>
          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">Reference / UTR</label>
            <input type="text" name="reference_no" class="rf-input rf-input-sm font-mono" value="<?= htmlspecialchars($expense['reference_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <div>
          <label class="rf-label-plain text-xs">Attach Bill / Receipt Photo (JPG, PNG, WEBP, PDF &bull; Max 5MB)</label>
          <input type="file" name="attachment" class="block w-full text-xs text-[#5C5A54] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-[#D4D1C9] file:text-xs file:font-medium file:bg-[#F7F6F3] file:text-[#1C1B18] hover:file:bg-[#EFECE6]" accept=".jpg,.jpeg,.png,.webp,.pdf">
          <?php if (!empty($expense['attachment_path'])): ?>
            <div class="mt-2.5 flex flex-wrap items-center gap-4 text-xs">
              <a href="/uploads/expenses/<?= htmlspecialchars(rawurlencode(basename($expense['attachment_path'])), ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="text-[#2B526E] hover:underline font-medium">
                <i class="bi bi-paperclip"></i> View Current Attachment
              </a>
              <label class="inline-flex items-center gap-1.5 text-[#8F2D2B] cursor-pointer">
                <input type="checkbox" id="remAtt" name="remove_attachment" value="1" class="rounded border-[#D4D1C9] accent-[#8F2D2B]">
                <span>Remove Attachment</span>
              </label>
            </div>
          <?php endif; ?>
        </div>

        <div>
          <label class="rf-label-plain text-xs">Description / Notes</label>
          <textarea name="notes" class="rf-textarea text-xs" rows="2"><?= htmlspecialchars($expense['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
      </div>

      <div class="rf-card-footer justify-end gap-2">
        <a href="/expenses" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-check2-circle"></i> <?= $isEdit ? 'Save Expense' : 'Record Expense' ?>
        </button>
      </div>
    </form>
  </div>
</div>
