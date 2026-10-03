<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0 text-dark">
          <i class="bi bi-box-seam me-2 text-primary"></i>
          <?= !empty($item) ? 'Edit Item / Product' : 'Add New Item / Product' ?>
        </h4>
        <a href="/items" class="btn btn-outline-secondary btn-sm">Cancel</a>
      </div>

      <div class="card-body p-4">
        <form action="<?= !empty($item) ? '/items/edit/' . $item['id'] : '/items/create' ?>" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <!-- Item Name & Code -->
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label for="name" class="form-label">Item / Product Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Cotton T-Shirt / Steel Rod 10mm" value="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            </div>
            <div class="col-md-4">
              <label for="item_code" class="form-label">Item Code / Barcode</label>
              <input type="text" class="form-control font-monospace" id="item_code" name="item_code" placeholder="e.g. SKU-101" value="<?= htmlspecialchars($item['item_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>

          <!-- HSN & Unit -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="hsn_code" class="form-label">HSN / SAC Code</label>
              <input type="text" class="form-control font-monospace" id="hsn_code" name="hsn_code" placeholder="e.g. 61091000" value="<?= htmlspecialchars($item['hsn_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
              <label for="unit" class="form-label">Unit of Measurement</label>
              <select class="form-select" id="unit" name="unit">
                <option value="PCS" <?= (empty($item) || ($item['unit'] ?? '') === 'PCS') ? 'selected' : '' ?>>PCS (Pieces)</option>
                <option value="KG" <?= (!empty($item) && ($item['unit'] ?? '') === 'KG') ? 'selected' : '' ?>>KG (Kilograms)</option>
                <option value="BOX" <?= (!empty($item) && ($item['unit'] ?? '') === 'BOX') ? 'selected' : '' ?>>BOX (Boxes)</option>
                <option value="MTR" <?= (!empty($item) && ($item['unit'] ?? '') === 'MTR') ? 'selected' : '' ?>>MTR (Meters)</option>
                <option value="LTR" <?= (!empty($item) && ($item['unit'] ?? '') === 'LTR') ? 'selected' : '' ?>>LTR (Liters)</option>
                <option value="NOS" <?= (!empty($item) && ($item['unit'] ?? '') === 'NOS') ? 'selected' : '' ?>>NOS (Numbers)</option>
                <option value="BAG" <?= (!empty($item) && ($item['unit'] ?? '') === 'BAG') ? 'selected' : '' ?>>BAG (Bags)</option>
                <option value="PKT" <?= (!empty($item) && ($item['unit'] ?? '') === 'PKT') ? 'selected' : '' ?>>PKT (Packets)</option>
                <option value="BDL" <?= (!empty($item) && ($item['unit'] ?? '') === 'BDL') ? 'selected' : '' ?>>BDL (Bundle)</option>
                <option value="DZ" <?= (!empty($item) && ($item['unit'] ?? '') === 'DZ') ? 'selected' : '' ?>>DZ (Dozen)</option>
              </select>
            </div>
          </div>

          <!-- Pricing & GST Tax Rate -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="sale_price" class="form-label">Sale Price (₹)</label>
              <div class="input-group">
                <span class="input-group-text bg-light">₹</span>
                <input type="number" class="form-control text-end" id="sale_price" name="sale_price" step="any" min="0" placeholder="0.00" value="<?= htmlspecialchars((string)($item['sale_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label for="purchase_price" class="form-label">Purchase Price (₹)</label>
              <div class="input-group">
                <span class="input-group-text bg-light">₹</span>
                <input type="number" class="form-control text-end" id="purchase_price" name="purchase_price" step="any" min="0" placeholder="0.00" value="<?= htmlspecialchars((string)($item['purchase_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <?php
                $currentTaxRate = !empty($item) ? (float)($item['tax_rate'] ?? 0) : 0.0;
                $isStandardTax = in_array($currentTaxRate, [0.0, 5.0, 12.0, 18.0, 28.0], true);
                $isCustomTax = !empty($item) && !$isStandardTax;
              ?>
              <label for="tax_rate_select" class="form-label">GST Tax Rate (%)</label>
              <select class="form-select" id="tax_rate_select">
                <option value="0" <?= (empty($item) || ($isStandardTax && $currentTaxRate === 0.0)) ? 'selected' : '' ?>>0% (Exempt / Nil)</option>
                <option value="5" <?= ($isStandardTax && $currentTaxRate === 5.0) ? 'selected' : '' ?>>5% (GST 5%)</option>
                <option value="12" <?= ($isStandardTax && $currentTaxRate === 12.0) ? 'selected' : '' ?>>12% (GST 12%)</option>
                <option value="18" <?= ($isStandardTax && $currentTaxRate === 18.0) ? 'selected' : '' ?>>18% (GST 18%)</option>
                <option value="28" <?= ($isStandardTax && $currentTaxRate === 28.0) ? 'selected' : '' ?>>28% (GST 28%)</option>
                <option value="custom" <?= $isCustomTax ? 'selected' : '' ?>>Custom Tax %</option>
              </select>
              <div id="customTaxRateContainer" class="mt-2 <?= $isCustomTax ? '' : 'd-none' ?>">
                <div class="input-group">
                  <span class="input-group-text bg-light">Custom Rate</span>
                  <input type="number" class="form-control text-end" id="custom_tax_rate_input" placeholder="1 - 100" min="1" max="100" step="any" value="<?= $isCustomTax ? htmlspecialchars((string)$currentTaxRate, ENT_QUOTES, 'UTF-8') : '' ?>" onkeydown="if(['-','+','e','E'].includes(event.key)) event.preventDefault();">
                  <span class="input-group-text bg-light">%</span>
                </div>
                <div class="form-text small text-muted">Enter a custom rate strictly between 1 and 100%</div>
              </div>
              <input type="hidden" id="tax_rate" name="tax_rate" value="<?= htmlspecialchars((string)$currentTaxRate, ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>

          <!-- Stock Levels -->
          <div class="row g-3 mb-3">
            <?php if (empty($item)): ?>
              <div class="col-md-6">
                <label for="opening_stock" class="form-label">Opening Stock Quantity</label>
                <input type="number" class="form-control" id="opening_stock" name="opening_stock" step="any" placeholder="0" value="0">
              </div>
            <?php endif; ?>
            <div class="<?= !empty($item) ? 'col-md-12' : 'col-md-6' ?>">
              <label for="low_stock_threshold" class="form-label">Low Stock Alert Threshold</label>
              <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" step="any" placeholder="e.g. 5" value="<?= htmlspecialchars((string)($item['low_stock_threshold'] ?? '5'), ENT_QUOTES, 'UTF-8') ?>">
              <div class="form-text">Get an alert when stock drops below this number.</div>
            </div>
          </div>

          <!-- Description -->
          <div class="mb-4">
            <label for="description" class="form-label">Item Description / Specifications</label>
            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional notes or product details..."><?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="/items" class="btn btn-light px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
              <i class="bi bi-check2-circle me-1"></i> <?= !empty($item) ? 'Update Item' : 'Save Item' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const taxSelect = document.getElementById('tax_rate_select');
  const customContainer = document.getElementById('customTaxRateContainer');
  const customInput = document.getElementById('custom_tax_rate_input');
  const hiddenInput = document.getElementById('tax_rate');
  const itemForm = taxSelect ? taxSelect.closest('form') : null;

  function syncTax() {
    if (!taxSelect || !hiddenInput) return;
    if (taxSelect.value === 'custom') {
      if (customContainer) customContainer.classList.remove('d-none');
      let val = customInput ? parseFloat(customInput.value) : 0;
      hiddenInput.value = (isNaN(val) || val <= 0) ? '' : val;
    } else {
      if (customContainer) customContainer.classList.add('d-none');
      hiddenInput.value = taxSelect.value;
    }
  }

  if (taxSelect) {
    taxSelect.addEventListener('change', function() {
      syncTax();
      if (taxSelect.value === 'custom' && customInput) {
        customInput.focus();
      }
    });
  }

  if (customInput) {
    customInput.addEventListener('input', function() {
      let val = customInput.value;
      if (val !== '') {
        let num = parseFloat(val);
        if (!isNaN(num)) {
          if (num > 100) {
            customInput.value = 100;
            num = 100;
          } else if (num < 0) {
            customInput.value = 1;
            num = 1;
          }
          if (hiddenInput) hiddenInput.value = num;
        }
      } else {
        if (hiddenInput) hiddenInput.value = '';
      }
    });

    customInput.addEventListener('blur', function() {
      if (customInput.value !== '') {
        let num = parseFloat(customInput.value);
        if (isNaN(num) || num < 1) {
          customInput.value = 1;
          if (hiddenInput) hiddenInput.value = 1;
        } else if (num > 100) {
          customInput.value = 100;
          if (hiddenInput) hiddenInput.value = 100;
        }
      }
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
    });
  }
});
</script>
