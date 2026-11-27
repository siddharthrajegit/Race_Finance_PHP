<div class="max-w-3xl mx-auto">
  <div class="rf-card">
    <div class="rf-card-header">
      <h1 class="text-lg font-semibold text-[#1C1B18] flex items-center gap-2">
        <i class="bi bi-box-seam text-[#5C5A54]"></i>
        <?= !empty($item) ? 'Edit Item / Product' : 'Add New Item / Product' ?>
      </h1>
      <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
    </div>

    <div class="rf-card-body">
      <form action="<?= !empty($item) ? '/items/edit/' . $item['id'] : '/items/create' ?>" method="POST" class="space-y-6">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <!-- Basic Identity -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-5">
            <label for="name" class="rf-label-plain">Item / Product Name <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" class="rf-input" id="name" name="name" placeholder="e.g. Cotton T-Shirt / Steel Rod 10mm" value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
          </div>
          <div class="md:col-span-4">
            <label for="category" class="rf-label-plain">Category <span class="text-xs text-[#78746C] font-normal">(Optional)</span></label>
            <div class="relative">
              <i class="bi bi-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
              <input type="text" class="rf-input pl-9" id="category" name="category" list="categoryList" placeholder="e.g. Electronics..." value="<?= htmlspecialchars($item['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <datalist id="categoryList">
              <?php foreach (($categories ?? []) as $cat): ?>
                <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"></option>
              <?php endforeach; ?>
            </datalist>
            <p class="rf-help">Type to select or add new category</p>
          </div>
          <div class="md:col-span-3">
            <label for="item_code" class="rf-label-plain">Item Code / SKU</label>
            <input type="text" class="rf-input font-mono" id="item_code" name="item_code" placeholder="SKU-101" value="<?= htmlspecialchars($item['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <!-- HSN & Units -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-4">
            <label for="hsn_code" class="rf-label-plain">HSN / SAC Code</label>
            <input type="text" class="rf-input font-mono" id="hsn_code" name="hsn_code" placeholder="e.g. 61091000" value="<?= htmlspecialchars($item['hsn_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="20" oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-\.\/ ]/g, '').slice(0, 20);">
            <p class="rf-help">2 to 8 digits HSN or SAC code</p>
          </div>

          <div class="md:col-span-4">
            <div class="flex items-center justify-between mb-1.5">
              <label for="unit" class="text-sm font-medium text-[#2C2B27]">Primary Unit</label>
              <button type="button" class="text-xs font-medium text-[#2B526E] hover:underline bg-transparent border-0 p-0 cursor-pointer" data-ui-modal="#modalAddCustomUnit">
                <i class="bi bi-plus-circle mr-0.5"></i> Add Unit
              </button>
            </div>
            <select class="rf-select" id="unit" name="unit">
              <?php
                $currentUnitUpper = strtoupper($item['unit'] ?? 'PCS');
                $unitFound = false;
              ?>
              <?php foreach (($allUnits ?? []) as $u): ?>
                <?php
                  $uCode = strtoupper($u['code']);
                  $isSel = ($uCode === $currentUnitUpper);
                  if ($isSel) $unitFound = true;
                ?>
                <option value="<?= htmlspecialchars($uCode, ENT_QUOTES, 'UTF-8') ?>" <?= $isSel ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['label'] ?? $uCode, ENT_QUOTES, 'UTF-8') ?><?= !empty($u['is_custom']) ? ' (Custom)' : '' ?>
                </option>
              <?php endforeach; ?>
              <?php if (!$unitFound && !empty($item['unit'])): ?>
                <option value="<?= htmlspecialchars($currentUnitUpper, ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars($currentUnitUpper, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endif; ?>
            </select>
          </div>

          <div class="md:col-span-4">
            <label for="sub_unit" class="rf-label-plain">Sub-Unit <span class="text-xs text-[#78746C] font-normal">(Optional Pack)</span></label>
            <div class="flex items-stretch gap-1.5">
              <input type="text" class="rf-input uppercase" id="sub_unit" name="sub_unit" placeholder="PCS" value="<?= htmlspecialchars($item['sub_unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="15">
              <input type="number" step="any" min="0.01" class="rf-input w-24 text-right tabular-nums" id="sub_unit_qty" name="sub_unit_qty" placeholder="Qty" value="<?= !empty($item['sub_unit_qty']) ? (float)$item['sub_unit_qty'] : '' ?>" title="Items per primary unit (e.g. 10)">
            </div>
            <p class="rf-help">e.g. 1 BOX = 10 PCS</p>
          </div>
        </div>

        <!-- Pricing & GST Studio -->
        <?php
          $currentTaxRate = !empty($item) ? (float)($item['tax_rate'] ?? 0) : 0.0;
          $isStandardTax = in_array($currentTaxRate, [0.0, 5.0, 12.0, 18.0, 28.0], true);
          $isCustomTax = !empty($item) && !$isStandardTax;
          $initialSalePrice = !empty($item) && isset($item['sale_price']) && (float)$item['sale_price'] > 0 ? (float)$item['sale_price'] : null;
          $initialSaleWithTax = $initialSalePrice !== null ? round($initialSalePrice * (1 + ($currentTaxRate / 100)), 2) : null;
        ?>
        <div class="rf-card-muted p-5 space-y-5">
          <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#E2DFD7]">
            <div class="flex items-center gap-2.5">
              <span class="w-8 h-8 rounded-lg bg-white border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center"><i class="bi bi-calculator"></i></span>
              <div>
                <h2 class="text-sm font-semibold text-[#1C1B18]">Item Pricing &amp; GST Configuration</h2>
                <p class="text-xs text-[#78746C]">Enter either price &mdash; the other calculates automatically from GST rate</p>
              </div>
            </div>
            <span class="rf-badge rf-badge-slate"><i class="bi bi-arrow-left-right"></i> Auto-Calculation</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border border-[#E2DFD7] rounded-xl p-4 space-y-2">
              <div class="flex items-center justify-between">
                <label for="sale_price" class="text-xs font-semibold text-[#1C1B18]">Sale Price (Without GST) <span class="text-[#8F2D2B]">*</span></label>
                <span class="rf-badge rf-badge-neutral text-[10px]">Excl. GST</span>
              </div>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-[#5C5A54]">₹</span>
                <input type="number" class="rf-input pl-8 text-right text-base font-semibold font-mono tabular-nums" id="sale_price" name="sale_price" step="any" min="0" placeholder="0.00" value="<?= $initialSalePrice !== null ? htmlspecialchars((string)$initialSalePrice, ENT_QUOTES, 'UTF-8') : '' ?>">
              </div>
              <p class="text-[11px] text-[#78746C] m-0">Base selling price excluding GST</p>
            </div>

            <div class="bg-[#F4F8FB] border border-[#CFDFEB] rounded-xl p-4 space-y-2">
              <div class="flex items-center justify-between">
                <label for="sale_price_with_tax" class="text-xs font-semibold text-[#1C1B18]">Sale Price (With GST)</label>
                <span class="rf-badge rf-badge-slate text-[10px]">Incl. GST</span>
              </div>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-[#5C5A54]">₹</span>
                <input type="number" class="rf-input pl-8 text-right text-base font-semibold font-mono tabular-nums" id="sale_price_with_tax" name="sale_price_with_tax" step="any" min="0" placeholder="0.00" value="<?= $initialSaleWithTax !== null ? htmlspecialchars((string)$initialSaleWithTax, ENT_QUOTES, 'UTF-8') : '' ?>">
              </div>
              <p class="text-[11px] text-[#78746C] m-0">Final customer bill amount including GST</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="tax_rate_select" class="rf-label-plain text-xs">Applicable GST Tax Rate (%)</label>
              <select class="rf-select" id="tax_rate_select">
                <option value="0" <?= (empty($item) || ($isStandardTax && $currentTaxRate === 0.0)) ? 'selected' : '' ?>>0% (Exempt / Nil)</option>
                <option value="5" <?= ($isStandardTax && $currentTaxRate === 5.0) ? 'selected' : '' ?>>5% (GST 5%)</option>
                <option value="12" <?= ($isStandardTax && $currentTaxRate === 12.0) ? 'selected' : '' ?>>12% (GST 12%)</option>
                <option value="18" <?= ($isStandardTax && $currentTaxRate === 18.0) ? 'selected' : '' ?>>18% (GST 18%)</option>
                <option value="28" <?= ($isStandardTax && $currentTaxRate === 28.0) ? 'selected' : '' ?>>28% (GST 28%)</option>
                <option value="custom" <?= $isCustomTax ? 'selected' : '' ?>>Custom Tax %</option>
              </select>
              <div id="customTaxRateContainer" class="mt-2 <?= $isCustomTax ? '' : 'hidden' ?>">
                <div class="flex items-center gap-2">
                  <input type="number" class="rf-input rf-input-sm text-right font-mono tabular-nums" id="custom_tax_rate_input" placeholder="1 - 100" min="1" max="100" step="any" value="<?= $isCustomTax ? htmlspecialchars((string)$currentTaxRate, ENT_QUOTES, 'UTF-8') : '' ?>" onkeydown="if(['-','+','e','E'].includes(event.key)) event.preventDefault();">
                  <span class="text-xs text-[#5C5A54]">%</span>
                </div>
                <p class="rf-help">Enter custom rate (1 - 100%)</p>
              </div>
              <input type="hidden" id="tax_rate" name="tax_rate" value="<?= htmlspecialchars((string)$currentTaxRate, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div>
              <label for="purchase_price" class="rf-label-plain text-xs">Purchase Price / Cost (₹)</label>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-[#78746C]">₹</span>
                <input type="number" class="rf-input pl-8 text-right font-mono tabular-nums" id="purchase_price" name="purchase_price" step="any" min="0" placeholder="0.00" value="<?= htmlspecialchars((string)($item['purchase_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <p class="rf-help">Acquisition / supplier cost per unit</p>
            </div>
          </div>
        </div>

        <!-- Stock Levels -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <?php if (empty($item)): ?>
            <div>
              <label for="opening_stock" class="rf-label-plain">Opening Stock Quantity</label>
              <input type="number" class="rf-input tabular-nums" id="opening_stock" name="opening_stock" step="any" placeholder="0" value="0">
            </div>
          <?php endif; ?>
          <div class="<?= !empty($item) ? 'md:col-span-2' : '' ?>">
            <label for="low_stock_threshold" class="rf-label-plain">Low Stock Alert Threshold</label>
            <input type="number" class="rf-input tabular-nums" id="low_stock_threshold" name="low_stock_threshold" step="any" placeholder="e.g. 5" value="<?= htmlspecialchars((string)($item['low_stock_threshold'] ?? '5'), ENT_QUOTES, 'UTF-8') ?>">
            <p class="rf-help">Receive an alert when stock drops below this number.</p>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="rf-label-plain">Item Description / Specifications</label>
          <textarea class="rf-textarea" id="description" name="description" rows="2" placeholder="Optional notes or product details..."><?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-[#ECEAE4]">
          <a href="/items" class="rf-btn rf-btn-secondary">Cancel</a>
          <button type="submit" class="rf-btn rf-btn-primary">
            <i class="bi bi-check2-circle"></i> <?= !empty($item) ? 'Update Item' : 'Save Item' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const taxSelect = document.getElementById('tax_rate_select');
  const customContainer = document.getElementById('customTaxRateContainer');
  const customInput = document.getElementById('custom_tax_rate_input');
  const hiddenInput = document.getElementById('tax_rate');
  const salePriceWithoutTax = document.getElementById('sale_price');
  const salePriceWithTax = document.getElementById('sale_price_with_tax');
  const itemForm = taxSelect ? taxSelect.closest('form') : null;

  let lastEditedSalePrice = 'without_gst';

  function getTaxRate() {
    if (!hiddenInput) return 0;
    const rate = parseFloat(hiddenInput.value);
    return (isNaN(rate) || rate < 0) ? 0 : rate;
  }

  function triggerPulse(element) {
    if (!element) return;
    element.classList.remove('calc-highlight');
    void element.offsetWidth;
    element.classList.add('calc-highlight');
  }

  function updatePriceFromWithoutGst() {
    if (!salePriceWithoutTax || !salePriceWithTax) return;
    const val = salePriceWithoutTax.value.trim();
    if (val === '') { salePriceWithTax.value = ''; return; }
    const base = parseFloat(val);
    if (isNaN(base) || base < 0) { salePriceWithTax.value = ''; return; }
    const taxRate = getTaxRate();
    const withTax = base * (1 + (taxRate / 100));
    salePriceWithTax.value = parseFloat(withTax.toFixed(2));
    triggerPulse(salePriceWithTax);
  }

  function updatePriceFromWithGst() {
    if (!salePriceWithoutTax || !salePriceWithTax) return;
    const val = salePriceWithTax.value.trim();
    if (val === '') { salePriceWithoutTax.value = ''; return; }
    const total = parseFloat(val);
    if (isNaN(total) || total < 0) { salePriceWithoutTax.value = ''; return; }
    const taxRate = getTaxRate();
    const withoutTax = total / (1 + (taxRate / 100));
    salePriceWithoutTax.value = parseFloat(withoutTax.toFixed(2));
    triggerPulse(salePriceWithoutTax);
  }

  if (salePriceWithoutTax) {
    salePriceWithoutTax.addEventListener('input', function() {
      lastEditedSalePrice = 'without_gst';
      updatePriceFromWithoutGst();
    });
  }

  if (salePriceWithTax) {
    salePriceWithTax.addEventListener('input', function() {
      lastEditedSalePrice = 'with_gst';
      updatePriceFromWithGst();
    });
  }

  function syncTax() {
    if (!taxSelect || !hiddenInput) return;
    if (taxSelect.value === 'custom') {
      if (customContainer) customContainer.classList.remove('hidden');
      let val = customInput ? parseFloat(customInput.value) : 0;
      hiddenInput.value = (isNaN(val) || val <= 0) ? '' : val;
    } else {
      if (customContainer) customContainer.classList.add('hidden');
      hiddenInput.value = taxSelect.value;
    }

    if (lastEditedSalePrice === 'with_gst') {
      updatePriceFromWithGst();
    } else {
      updatePriceFromWithoutGst();
    }
  }

  if (taxSelect) {
    taxSelect.addEventListener('change', function() {
      syncTax();
      if (taxSelect.value === 'custom' && customInput) customInput.focus();
    });
  }

  if (customInput) {
    customInput.addEventListener('input', function() {
      let val = customInput.value;
      if (val !== '') {
        let num = parseFloat(val);
        if (!isNaN(num)) {
          if (num > 100) { customInput.value = 100; num = 100; }
          else if (num < 0) { customInput.value = 1; num = 1; }
          if (hiddenInput) hiddenInput.value = num;
        }
      } else {
        if (hiddenInput) hiddenInput.value = '';
      }
      if (lastEditedSalePrice === 'with_gst') updatePriceFromWithGst();
      else updatePriceFromWithoutGst();
    });

    customInput.addEventListener('blur', function() {
      if (customInput.value !== '') {
        let num = parseFloat(customInput.value);
        if (isNaN(num) || num < 1) { customInput.value = 1; if (hiddenInput) hiddenInput.value = 1; }
        else if (num > 100) { customInput.value = 100; if (hiddenInput) hiddenInput.value = 100; }
      }
      if (lastEditedSalePrice === 'with_gst') updatePriceFromWithGst();
      else updatePriceFromWithoutGst();
    });
  }

  if (itemForm) {
    itemForm.addEventListener('submit', function(e) {
      if (taxSelect && taxSelect.value === 'custom') {
        let num = parseFloat(customInput ? customInput.value : '');
        if (isNaN(num) || num < 1 || num > 100) {
          e.preventDefault();
          alert('Please enter a custom tax percentage strictly between 1 and 100.');
          if (customInput) customInput.focus();
          return false;
        }
        if (hiddenInput) hiddenInput.value = num;
      }

      if (salePriceWithoutTax && salePriceWithTax) {
        if (salePriceWithoutTax.value.trim() === '' && salePriceWithTax.value.trim() !== '') {
          updatePriceFromWithGst();
        }
      }
    });
  }

  // Custom Unit Modal AJAX Creation
  const customUnitForm = document.getElementById('customUnitForm');
  const customUnitAlert = document.getElementById('customUnitAlert');
  const btnSaveCustomUnit = document.getElementById('btnSaveCustomUnit');
  const unitSelect = document.getElementById('unit');

  if (customUnitForm) {
    customUnitForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (customUnitAlert) customUnitAlert.classList.add('hidden');
      if (btnSaveCustomUnit) btnSaveCustomUnit.disabled = true;

      const codeInput = document.getElementById('newUnitCode');
      const labelInput = document.getElementById('newUnitLabel');
      const codeVal = codeInput ? codeInput.value.trim().toUpperCase() : '';
      const labelVal = labelInput ? labelInput.value.trim() : '';

      if (!codeVal) {
        if (customUnitAlert) {
          customUnitAlert.textContent = 'Please enter a unit code (e.g. PAIR, SET).';
          customUnitAlert.classList.remove('hidden');
        }
        if (btnSaveCustomUnit) btnSaveCustomUnit.disabled = false;
        return;
      }

      try {
        const csrfToken = document.querySelector('input[name="_csrf"]')?.value || '';
        const res = await fetch('/api/units/create', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({
            unit_code: codeVal,
            unit_label: labelVal || codeVal,
            _csrf: csrfToken
          })
        });

        const data = await res.json();
        if (data.success && data.unit) {
          const newCode = data.unit.code.toUpperCase();
          const newLabel = (data.unit.label || newCode) + ' (Custom)';

          let opt = Array.from(unitSelect.options).find(o => o.value === newCode);
          if (!opt) {
            opt = document.createElement('option');
            opt.value = newCode;
            opt.textContent = newLabel;
            unitSelect.appendChild(opt);
          }
          unitSelect.value = newCode;

          if (window.RFUI) window.RFUI.closeModal('modalAddCustomUnit');
          customUnitForm.reset();
        } else {
          if (customUnitAlert) {
            customUnitAlert.textContent = data.error || 'Failed to create unit.';
            customUnitAlert.classList.remove('hidden');
          }
        }
      } catch (err) {
        if (customUnitAlert) {
          customUnitAlert.textContent = 'Network error: ' + err.message;
          customUnitAlert.classList.remove('hidden');
        }
      } finally {
        if (btnSaveCustomUnit) btnSaveCustomUnit.disabled = false;
      }
    });
  }
});
</script>

<!-- Modal: Add Custom Unit -->
<div class="rf-modal hidden" id="modalAddCustomUnit" tabindex="-1" aria-labelledby="modalAddCustomUnitLabel" aria-hidden="true">
  <div class="rf-modal-dialog max-w-sm">
    <div class="rf-modal-header">
      <h3 class="rf-modal-title text-sm" id="modalAddCustomUnitLabel">
        <i class="bi bi-plus-circle text-[#5C5A54]"></i> Add Custom Unit
      </h3>
      <button type="button" class="text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" data-ui-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="customUnitForm">
      <div class="rf-modal-body space-y-4">
        <div id="customUnitAlert" class="rf-alert rf-alert-danger hidden text-xs mb-0"></div>
        <p class="text-xs text-[#5C5A54] m-0">Add a custom measurement unit visible only in your account.</p>
        <div>
          <label class="rf-label-plain text-xs" for="newUnitCode">Unit Code <span class="text-[#8F2D2B]">*</span></label>
          <input type="text" class="rf-input rf-input-sm uppercase font-mono" id="newUnitCode" name="new_unit_code" placeholder="e.g. PAIR, SET, CAN" maxlength="15" required oninput="this.value = this.value.replace(/[^a-zA-Z0-9_\-]/g, '').slice(0, 15).toUpperCase();">
          <p class="rf-help">Short symbol (1-15 chars, alphanumeric)</p>
        </div>
        <div>
          <label class="rf-label-plain text-xs" for="newUnitLabel">Display Label (Optional)</label>
          <input type="text" class="rf-input rf-input-sm" id="newUnitLabel" name="new_unit_label" placeholder="e.g. PAIR (Pairs)" maxlength="50">
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm" id="btnSaveCustomUnit">
          <i class="bi bi-check-circle"></i> Save Unit
        </button>
      </div>
    </form>
  </div>
</div>
