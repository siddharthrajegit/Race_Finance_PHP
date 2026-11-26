<?php $isEdit = !empty($expense); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold text-dark mb-0">
    <i class="bi bi-wallet2 text-danger me-2"></i><?= $isEdit ? 'Edit Expense #' . htmlspecialchars($expense['expense_number'], ENT_QUOTES, 'UTF-8') : 'Record Business Expense' ?>
  </h3>
  <a href="/expenses" class="btn btn-outline-secondary btn-sm">Back to Expenses</a>
</div>

<div class="card shadow-sm border-0" style="max-width: 760px;">
  <form action="<?= $isEdit ? '/expenses/edit/' . (int)$expense['id'] : '/expenses/create' ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="card-body p-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Expense # <span class="text-danger">*</span></label>
          <input type="text" name="expense_number" class="form-control form-control-sm font-monospace" required value="<?= htmlspecialchars($expense['expense_number'] ?? $nextExpenseNumber, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Expense Date <span class="text-danger">*</span></label>
          <input type="date" name="expense_date" class="form-control form-control-sm" required value="<?= htmlspecialchars($expense['expense_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
          <input type="number" name="amount" class="form-control form-control-sm fw-bold" min="0.01" step="0.01" required value="<?= $isEdit ? htmlspecialchars((string)$expense['amount'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="0.00">
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Expense Category <span class="text-danger">*</span></label>
          <select name="category" class="form-select form-select-sm">
            <?php foreach ($categories as $c): ?>
              <option value="<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>" <?= (($expense['category'] ?? '') === $c['name']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Or Add New Category</label>
          <input type="text" name="new_category" class="form-control form-control-sm" placeholder="Leave blank to use dropdown above">
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Paid To / Vendor</label>
          <input type="text" name="payee" class="form-control form-control-sm" value="<?= htmlspecialchars($expense['payee'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Landlord / Utility Board">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Payment Mode</label>
          <select name="payment_mode" class="form-select form-select-sm">
            <?php foreach (['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'cheque' => 'Cheque'] as $k => $lbl): ?>
              <option value="<?= $k ?>" <?= (($expense['payment_mode'] ?? 'cash') === $k) ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Reference / Txn #</label>
          <input type="text" name="reference_no" class="form-control form-control-sm" value="<?= htmlspecialchars($expense['reference_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="col-12">
          <label class="form-label small fw-semibold">Receipt Attachment (JPG, PNG, WebP, or PDF &le; 2MB)</label>
          <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf">
          <?php if (!empty($expense['attachment_path'])): ?>
            <div class="mt-2 d-flex align-items-center gap-3 small">
              <a href="<?= htmlspecialchars($expense['attachment_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><i class="bi bi-paperclip"></i> Current Attachment</a>
              <label class="form-check-label text-danger">
                <input type="checkbox" class="form-check-input" name="remove_attachment" value="1"> Remove existing attachment
              </label>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-12">
          <label class="form-label small fw-semibold">Notes</label>
          <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional narration"><?= htmlspecialchars($expense['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
      </div>
    </div>
    <div class="card-footer bg-light p-3 text-end">
      <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Update Expense' : 'Save Expense' ?>
      </button>
    </div>
  </form>
</div>
