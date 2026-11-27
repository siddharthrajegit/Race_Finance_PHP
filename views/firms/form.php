<div class="max-w-4xl mx-auto">
  <div class="rf-card">
    <div class="rf-card-header">
      <h1 class="text-lg font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-building-gear text-[#5C5A54]"></i>
        <?= !empty($firm) ? 'Edit Business Firm' : 'Register New Business Firm' ?>
      </h1>
      <a href="/firms" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
    </div>

    <div class="rf-card-body">
      <form action="<?= !empty($firm) ? '/firms/edit/' . $firm['id'] : '/firms/create' ?>?_csrf=<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <!-- Section 1: Business Profile -->
        <section class="space-y-4">
          <h2 class="text-sm font-semibold text-[#1C1B18] pb-2 border-b border-[#ECEAE4]">1. Business Profile</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="name" class="rf-label-plain">Business / Firm Name <span class="text-[#8F2D2B]">*</span></label>
              <input type="text" class="rf-input" id="name" name="name" placeholder="e.g. Sharma Enterprises" value="<?= htmlspecialchars($firm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div>
              <label for="phone" class="rf-label-plain">Business Phone / Mobile</label>
              <input type="tel" class="rf-input" id="phone" name="phone" placeholder="e.g. 9876543210" value="<?= htmlspecialchars($firm['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
              <label for="email" class="rf-label-plain">Business Email</label>
              <input type="email" class="rf-input" id="email" name="email" placeholder="e.g. info@sharmaenterprises.com" value="<?= htmlspecialchars($firm['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
              <label for="gstin" class="rf-label-plain">GSTIN (Optional)</label>
              <input type="text" class="rf-input font-mono uppercase" id="gstin" name="gstin" placeholder="e.g. 27AAAAA0000A1Z5" maxlength="15" value="<?= htmlspecialchars($firm['gstin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <p class="rf-help">Leave blank if non-GST / unregistered.</p>
            </div>
            <div>
              <label for="pan" class="rf-label-plain">PAN Number (Optional)</label>
              <input type="text" class="rf-input font-mono uppercase" id="pan" name="pan" placeholder="e.g. ABCDE1234F" maxlength="10" value="<?= htmlspecialchars($firm['pan'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
              <label class="rf-label-plain">Business Logo <span class="text-xs text-[#78746C] font-normal">(JPG/PNG/WebP, Max 2MB)</span></label>
              <input type="file" class="rf-input py-2 text-xs" name="logo" accept=".jpg,.jpeg,.png,.webp">
              <?php if (!empty($firm['logo_path'])): ?>
                <div class="mt-2 flex items-center gap-2.5">
                  <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Current Logo" class="h-9 rounded border border-[#E6E4DF] p-0.5 bg-white">
                  <span class="text-xs text-[#78746C]">Current logo uploaded</span>
                </div>
              <?php endif; ?>
            </div>
            <div>
              <label class="rf-label-plain">Authorized Signature / Stamp <span class="text-xs text-[#78746C] font-normal">(JPG/PNG, Max 2MB)</span></label>
              <input type="file" class="rf-input py-2 text-xs" name="signature" accept=".jpg,.jpeg,.png,.webp">
              <?php if (!empty($firm['signature_path'])): ?>
                <div class="mt-2 flex items-center gap-2.5">
                  <img src="<?= htmlspecialchars($firm['signature_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Current Signature" class="h-9 rounded border border-[#E6E4DF] p-0.5 bg-white">
                  <span class="text-xs text-[#78746C]">Current signature uploaded</span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <!-- Section 2: Address -->
        <section class="space-y-4">
          <h2 class="text-sm font-semibold text-[#1C1B18] pb-2 border-b border-[#ECEAE4]">2. Address &amp; Place of Supply</h2>
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-12">
              <label for="address" class="rf-label-plain">Street Address</label>
              <textarea class="rf-textarea" id="address" name="address" rows="2" placeholder="e.g. Shop No. 4, MG Road, Market Area"><?= htmlspecialchars($firm['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="md:col-span-4">
              <label for="city" class="rf-label-plain">City / Town</label>
              <input type="text" class="rf-input" id="city" name="city" placeholder="e.g. Mumbai" value="<?= htmlspecialchars($firm['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="md:col-span-4">
              <label for="stateSelect" class="rf-label-plain">State</label>
              <select class="rf-select" id="stateSelect" name="state">
                <option value="">-- Select State --</option>
                <?php if (!empty($gstStates)): ?>
                  <?php foreach ($gstStates as $st): ?>
                    <option value="<?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($st['code'], ENT_QUOTES, 'UTF-8') ?>" <?= (!empty($firm['state']) && $firm['state'] === $st['name']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($st['code'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div class="md:col-span-2">
              <label for="stateCodeInput" class="rf-label-plain">State Code</label>
              <input type="text" class="rf-input font-mono" id="stateCodeInput" name="state_code" placeholder="27" value="<?= htmlspecialchars($firm['state_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="md:col-span-2">
              <label for="pincode" class="rf-label-plain">Pincode</label>
              <input type="text" class="rf-input" id="pincode" name="pincode" placeholder="400001" value="<?= htmlspecialchars($firm['pincode'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
        </section>

        <!-- Section 3: Bank & UPI -->
        <section class="space-y-4">
          <h2 class="text-sm font-semibold text-[#1C1B18] pb-2 border-b border-[#ECEAE4]">3. Bank Account &amp; UPI Details (Printed on Invoices)</h2>
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
              <label for="bank_name" class="rf-label-plain">Bank Name</label>
              <input type="text" class="rf-input" id="bank_name" name="bank_name" placeholder="e.g. State Bank of India" value="<?= htmlspecialchars($firm['bank_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="md:col-span-4">
              <label for="bank_account_no" class="rf-label-plain">Bank Account Number</label>
              <input type="text" class="rf-input font-mono" id="bank_account_no" name="bank_account_no" placeholder="e.g. 987654321098" value="<?= htmlspecialchars($firm['bank_account_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="md:col-span-4">
              <label for="bank_ifsc" class="rf-label-plain">IFSC Code</label>
              <input type="text" class="rf-input font-mono uppercase" id="bank_ifsc" name="bank_ifsc" placeholder="e.g. SBIN0001234" value="<?= htmlspecialchars($firm['bank_ifsc'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="md:col-span-6">
              <label for="bank_branch" class="rf-label-plain">Branch Name</label>
              <input type="text" class="rf-input" id="bank_branch" name="bank_branch" placeholder="e.g. Main Branch, Mumbai" value="<?= htmlspecialchars($firm['bank_branch'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="md:col-span-6">
              <label for="upi_id" class="rf-label-plain">UPI ID / VPA</label>
              <input type="text" class="rf-input font-mono" id="upi_id" name="upi_id" placeholder="e.g. business@okaxis" value="<?= htmlspecialchars($firm['upi_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="md:col-span-12">
              <label class="rf-label-plain">Payment QR Code (UPI / Bank QR) <span class="text-xs text-[#78746C] font-normal">(JPG/PNG/WebP, Max 2MB)</span></label>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <div class="<?= !empty($firm['qr_code_path']) ? 'md:col-span-7' : 'md:col-span-12' ?>">
                  <input type="file" class="rf-input py-2 text-xs" name="qr_code" accept=".jpg,.jpeg,.png,.webp">
                  <p class="rf-help">Upload your Google Pay, PhonePe, Paytm, or Bank UPI QR code to show on invoices.</p>
                </div>
                <?php if (!empty($firm['qr_code_path'])): ?>
                  <div class="md:col-span-5">
                    <div class="p-3 border border-[#E6E4DF] rounded-xl bg-[#FAF9F6] flex items-center justify-between gap-2">
                      <div class="flex items-center gap-2.5">
                        <img src="<?= htmlspecialchars($firm['qr_code_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Current QR Code" class="h-10 max-w-[60px] rounded border border-[#E6E4DF] p-0.5 bg-white object-contain">
                        <span class="text-xs text-[#78746C]">Current QR</span>
                      </div>
                      <label class="inline-flex items-center gap-1.5 text-xs text-[#8F2D2B] font-medium cursor-pointer">
                        <input class="rf-checkbox" type="checkbox" id="remove_qr_code" name="remove_qr_code" value="1">
                        <span>Remove</span>
                      </label>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>

        <!-- Section 4: Terms -->
        <section class="space-y-4">
          <h2 class="text-sm font-semibold text-[#1C1B18] pb-2 border-b border-[#ECEAE4]">4. Default Invoice Terms &amp; Conditions</h2>
          <textarea class="rf-textarea" id="terms" name="terms" rows="2" placeholder="e.g. 1. Goods once sold will not be taken back."><?= htmlspecialchars($firm['terms'] ?? '1. Goods once sold will not be taken back. 2. Subject to local jurisdiction.', ENT_QUOTES, 'UTF-8') ?></textarea>
          <label class="inline-flex items-center gap-2.5 text-sm font-medium text-[#1C1B18] cursor-pointer pt-1">
            <input class="rf-checkbox" type="checkbox" id="is_default" name="is_default" value="1" <?= (!empty($firm['is_default'])) ? 'checked' : '' ?>>
            <span>Set as primary / default business firm</span>
          </label>
        </section>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-[#ECEAE4]">
          <a href="/firms" class="rf-btn rf-btn-secondary">Cancel</a>
          <button type="submit" class="rf-btn rf-btn-primary">
            <i class="bi bi-check2-circle"></i> <?= !empty($firm) ? 'Save Changes' : 'Create Firm' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const stateSelect = document.getElementById('stateSelect');
  const stateCodeInput = document.getElementById('stateCodeInput');
  if (stateSelect && stateCodeInput) {
    stateSelect.addEventListener('change', function() {
      const selected = stateSelect.options[stateSelect.selectedIndex];
      stateCodeInput.value = selected ? (selected.dataset.code || '') : '';
    });
  }
</script>
