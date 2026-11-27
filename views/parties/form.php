<?php
$defType = $defaultType ?? 'customer';
?>
<div class="max-w-3xl mx-auto">
  <div class="rf-card">
    <div class="rf-card-header">
      <h1 class="text-lg font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-person-lines-fill text-[#5C5A54]"></i>
        <?= !empty($party) ? 'Edit Party Details' : 'Add New Customer / Supplier' ?>
      </h1>
      <a href="/parties" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
    </div>

    <div class="rf-card-body">
      <form action="<?= !empty($party) ? '/parties/edit/' . $party['id'] : '/parties/create' ?>" method="POST" class="space-y-6">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-4">
            <label for="type" class="rf-label-plain">Party Type <span class="text-[#8F2D2B]">*</span></label>
            <select class="rf-select" id="type" name="type" required>
              <option value="customer" <?= (!empty($party) ? ($party['type'] === 'customer') : ($defType === 'customer')) ? 'selected' : '' ?>>Customer (Buyer)</option>
              <option value="supplier" <?= (!empty($party) ? ($party['type'] === 'supplier') : ($defType === 'supplier')) ? 'selected' : '' ?>>Supplier (Vendor)</option>
              <option value="both" <?= (!empty($party) && $party['type'] === 'both') ? 'selected' : '' ?>>Both (Customer &amp; Supplier)</option>
            </select>
          </div>
          <div class="md:col-span-8">
            <label for="name" class="rf-label-plain">Party / Customer Name <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" class="rf-input" id="name" name="name" placeholder="e.g. Rajesh Gupta Traders" value="<?= htmlspecialchars($party['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="phone" class="rf-label-plain">Mobile / Phone (10 Digits)</label>
            <input type="tel" class="rf-input font-mono" id="phone" name="phone" placeholder="10-digit mobile number" maxlength="10" minlength="10" pattern="[0-9]{10}" inputmode="numeric" value="<?= htmlspecialchars($party['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <p class="rf-help">Exact 10 numeric digits</p>
          </div>
          <div>
            <label for="email" class="rf-label-plain">Email Address</label>
            <input type="email" class="rf-input" id="email" name="email" placeholder="e.g. party@example.com" value="<?= htmlspecialchars($party['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="gstin" class="rf-label-plain">GSTIN (15 Characters)</label>
            <input type="text" class="rf-input font-mono uppercase" id="gstin" name="gstin" placeholder="15-character GSTIN" maxlength="15" minlength="15" pattern="[0-9A-Z]{15}" value="<?= htmlspecialchars($party['gstin'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '')">
            <p class="rf-help">Exact 15 letters &amp; numbers</p>
          </div>
          <div>
            <label for="pan" class="rf-label-plain">PAN Number (Optional)</label>
            <input type="text" class="rf-input font-mono uppercase" id="pan" name="pan" placeholder="e.g. ABCDE1234F" maxlength="10" value="<?= htmlspecialchars($party['pan'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '')">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-12">
            <label for="billing_address" class="rf-label-plain">Billing Address</label>
            <textarea class="rf-textarea" id="billing_address" name="billing_address" rows="2" placeholder="Street address, building, locality..."><?= htmlspecialchars($party['billing_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <div class="md:col-span-4">
            <label for="city" class="rf-label-plain">City</label>
            <input type="text" class="rf-input" id="city" name="city" placeholder="e.g. Pune" value="<?= htmlspecialchars($party['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="md:col-span-4">
            <label for="partyStateSelect" class="rf-label-plain">State / UT</label>
            <select class="rf-select" id="partyStateSelect" name="state">
              <option value="">-- Select State --</option>
              <?php if (!empty($gstStates)): ?>
                <?php foreach ($gstStates as $st): ?>
                  <option value="<?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($st['code'], ENT_QUOTES, 'UTF-8') ?>" <?= (!empty($party) && (($party['state'] ?? '') === $st['name'] || ($party['state_code'] ?? '') === $st['code'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="md:col-span-2">
            <label for="partyStateCodeInput" class="rf-label-plain">State Code</label>
            <input type="text" class="rf-input font-mono bg-[#F3F1EC] font-semibold text-center" id="partyStateCodeInput" name="state_code" placeholder="00" value="<?= htmlspecialchars($party['state_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly title="Auto-filled from state">
          </div>
          <div class="md:col-span-2">
            <label for="pincode" class="rf-label-plain">Pincode</label>
            <input type="text" class="rf-input font-mono" id="pincode" name="pincode" placeholder="411001" maxlength="6" value="<?= htmlspecialchars($party['pincode'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
          </div>
        </div>

        <div class="max-w-xs">
          <label for="opening_balance" class="rf-label-plain">Opening Balance (₹)</label>
          <input type="number" class="rf-input tabular-nums" id="opening_balance" name="opening_balance" step="any" placeholder="0.00" value="<?= htmlspecialchars((string)($party['opening_balance'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
          <p class="rf-help">Positive to receive (Debit), negative to pay (Credit).</p>
        </div>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-[#ECEAE4]">
          <a href="/parties" class="rf-btn rf-btn-secondary">Cancel</a>
          <button type="submit" class="rf-btn rf-btn-primary">
            <i class="bi bi-check2-circle"></i> <?= !empty($party) ? 'Update Party' : 'Save Party' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const pStateSelect = document.getElementById('partyStateSelect');
  const pStateCodeInput = document.getElementById('partyStateCodeInput');
  const gstinInput = document.getElementById('gstin');

  if (pStateSelect && pStateCodeInput) {
    pStateSelect.addEventListener('change', function() {
      const selected = pStateSelect.options[pStateSelect.selectedIndex];
      pStateCodeInput.value = selected ? (selected.dataset.code || '') : '';
    });
  }

  if (gstinInput && pStateSelect) {
    gstinInput.addEventListener('input', function() {
      if (this.value.length >= 2) {
        const prefix = this.value.slice(0, 2);
        const opt = Array.from(pStateSelect.options).find(o => o.dataset.code === prefix);
        if (opt) {
          pStateSelect.value = opt.value;
          if (pStateCodeInput) pStateCodeInput.value = prefix;
        }
      }
    });
  }
</script>
