<?php
/**
 * Rate List & Estimate Builder View
 * RACE FINANCE - Billing & Inventory System
 */

$firmName = $firm['name'] ?? 'RACE FINANCE';
$categories = $categories ?? [];
$allItems = $allItems ?? [];
$nextEstimateNumber = $nextEstimateNumber ?? '001';
$todayDate = date('d/m/Y');
$preloadedItemsJson = '[]';
$preloadedCustomer = '';
$preloadedNumber = $nextEstimateNumber;

if (!empty($preloadedEstimate)) {
    $preloadedCustomer = $preloadedEstimate['customer_name'] ?? '';
    $preloadedNumber = $preloadedEstimate['estimate_number'] ?? $nextEstimateNumber;
    $itemsList = [];
    foreach (($preloadedEstimate['items'] ?? []) as $pi) {
        $itemsList[] = [
            'id' => $pi['item_id'] ?? null,
            'name' => $pi['item_name'],
            'hsn_code' => $pi['hsn_code'] ?? '',
            'unit' => $pi['unit'] ?? 'PCS',
            'sub_unit' => $pi['sub_unit'] ?? '',
            'sub_unit_qty' => !empty($pi['sub_unit_qty']) ? (float)$pi['sub_unit_qty'] : null,
            'sale_price' => (float)$pi['rate'],
            'tax_rate' => (float)$pi['tax_rate'],
            'quantity' => (float)$pi['quantity'],
            'discount_percent' => (float)($pi['discount_percent'] ?? 0)
        ];
    }
    $preloadedItemsJson = json_encode($itemsList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold mb-1">
      <i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Rate List &amp; Estimate Builder
    </h3>
    <p class="text-muted small mb-0">Select items category-wise, generate instant customer rate lists or switch to full estimates.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="/estimates" class="btn btn-outline-secondary shadow-sm">
      <i class="bi bi-journal-text me-1"></i> Saved Estimates
    </a>
  </div>
</div>

<!-- Mode Switcher Banner -->
<div class="card shadow-sm border-0 mb-3 bg-white">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <!-- Toggle Segmented Control -->
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold text-dark small me-1">Current Mode:</span>
        <div class="btn-group shadow-sm" role="group" aria-label="Builder Mode">
          <input type="radio" class="btn-check" name="builder_mode" id="modeRateList" value="ratelist" checked autocomplete="off">
          <label class="btn btn-outline-primary px-3 py-2 fw-bold d-flex align-items-center gap-2" for="modeRateList">
            <i class="bi bi-list-check fs-6"></i>
            <span>Rate List Mode</span>
          </label>

          <input type="radio" class="btn-check" name="builder_mode" id="modeEstimate" value="estimate" autocomplete="off">
          <label class="btn btn-outline-success px-3 py-2 fw-bold d-flex align-items-center gap-2" for="modeEstimate">
            <i class="bi bi-calculator fs-6"></i>
            <span>Estimate Mode</span>
          </label>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-dark shadow-sm" id="btnPrintCurrent">
          <i class="bi bi-printer me-1"></i> <span id="printBtnLabel">Print Rate List</span>
        </button>
        <button type="button" class="btn btn-success shadow-sm d-none" id="btnSaveEstimateModal">
          <i class="bi bi-check2-circle me-1"></i> Save Estimate
        </button>
      </div>
    </div>

    <!-- Estimate Mode Metadata Row (Revealed in Estimate Mode) -->
    <div id="estimateMetaBox" class="mt-3 pt-3 border-top d-none">
      <div class="row g-3 align-items-center">
        <div class="col-md-3">
          <label class="form-label small fw-bold mb-1" for="metaEstimateNumber">Estimate Number</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light text-muted font-monospace">#</span>
            <input type="text" class="form-control font-monospace fw-bold" id="metaEstimateNumber" value="<?= htmlspecialchars($preloadedNumber, ENT_QUOTES, 'UTF-8') ?>" placeholder="001">
          </div>
          <div class="form-text small text-muted" style="font-size: 0.72rem;">Separate sequence starting from 001</div>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold mb-1" for="metaEstimateDate">Estimate Date</label>
          <input type="text" class="form-control form-control-sm" id="metaEstimateDate" value="<?= htmlspecialchars($todayDate, ENT_QUOTES, 'UTF-8') ?>" placeholder="DD/MM/YYYY">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold mb-1" for="metaCustomerName">
            Customer Firm Name <span class="badge bg-light text-muted border fw-normal">Optional - 1 Line Only</span>
          </label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white"><i class="bi bi-building text-muted"></i></span>
            <input type="text" class="form-control" id="metaCustomerName" value="<?= htmlspecialchars($preloadedCustomer, ENT_QUOTES, 'UTF-8') ?>" placeholder="Leave blank to omit, or enter customer firm name...">
          </div>
          <div class="form-text small text-muted" style="font-size: 0.72rem;">If omitted, completely excluded from the printed document.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Left Side: Category & Item Picker (Step 1) -->
  <div class="col-lg-5">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="fw-bold text-dark d-flex align-items-center gap-2">
          <span class="badge bg-primary text-white rounded-circle p-1" style="width: 22px; height: 22px; line-height: 14px; text-align: center;">1</span>
          <span>Select Items from Category</span>
        </div>
        <span class="badge bg-light text-muted border small" id="itemsAvailableBadge">
          <?= count($allItems) ?> Available
        </span>
      </div>

      <div class="card-body p-3">
        <!-- Category Dropdown Selector -->
        <div class="mb-3">
          <label for="categorySelector" class="form-label small fw-bold text-muted mb-1">
            <i class="bi bi-tags me-1"></i>Select Category
          </label>
          <select class="form-select form-select-sm" id="categorySelector">
            <option value="all">All Categories</option>
            <option value="__none__">-- Uncategorized (No Category) --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                📁 <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Live Search Box & Select All -->
        <div class="row g-2 mb-2 align-items-center">
          <div class="col-8">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" class="form-control border-start-0" id="itemPickerSearch" placeholder="Filter category items...">
            </div>
          </div>
          <div class="col-4 text-end">
            <button type="button" class="btn btn-outline-primary btn-sm w-100 py-1" id="btnSelectAllVisible" style="font-size: 0.75rem;">
              Select All
            </button>
          </div>
        </div>

        <!-- Scrollable Items Checklist Container -->
        <div class="border rounded p-1 bg-light" style="max-height: 480px; overflow-y: auto;" id="itemsChecklistContainer">
          <?php if (!empty($allItems)): ?>
            <?php foreach ($allItems as $it): 
              $taxRate = (float)($it['tax_rate'] ?? 0);
              $salePrice = (float)($it['sale_price'] ?? 0);
              $salePriceWithTax = round($salePrice * (1 + ($taxRate / 100)), 2);
              $catVal = trim($it['category'] ?? '');
              $subUnitStr = '';
              if (!empty($it['sub_unit']) && !empty($it['sub_unit_qty'])) {
                  $subUnitStr = "1 " . $it['unit'] . " = " . (float)$it['sub_unit_qty'] . " " . $it['sub_unit'];
              }
            ?>
              <div class="item-checkbox-row p-2 mb-1 bg-white rounded border d-flex align-items-center justify-content-between"
                   data-item-id="<?= $it['id'] ?>"
                   data-category="<?= htmlspecialchars($catVal, ENT_QUOTES, 'UTF-8') ?>"
                   data-name="<?= htmlspecialchars(strtolower($it['name']), ENT_QUOTES, 'UTF-8') ?>"
                   data-hsn="<?= htmlspecialchars($it['hsn_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   data-unit="<?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?>"
                   data-sub-unit="<?= htmlspecialchars($it['sub_unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   data-sub-unit-qty="<?= !empty($it['sub_unit_qty']) ? (float)$it['sub_unit_qty'] : '' ?>"
                   data-pre-gst="<?= $salePrice ?>"
                   data-tax-rate="<?= $taxRate ?>"
                   data-post-gst="<?= $salePriceWithTax ?>">
                
                <div class="d-flex align-items-center me-2 text-truncate">
                  <div class="form-check me-2 mb-0">
                    <input class="form-check-input item-check-input" type="checkbox" id="chkItem<?= $it['id'] ?>" value="<?= $it['id'] ?>">
                  </div>
                  <label class="form-check-label text-truncate mb-0" for="chkItem<?= $it['id'] ?>" style="cursor: pointer;">
                    <div class="fw-bold text-dark text-truncate small"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-muted" style="font-size: 0.72rem;">
                      <?php if (!empty($catVal)): ?>
                        <span class="badge bg-secondary-subtle text-secondary me-1"><?= htmlspecialchars($catVal, ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                      <span><?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
                      <?php if (!empty($subUnitStr)): ?>
                        <span class="text-primary ms-1">(<?= htmlspecialchars($subUnitStr, ENT_QUOTES, 'UTF-8') ?>)</span>
                      <?php endif; ?>
                    </div>
                  </label>
                </div>

                <div class="text-end text-nowrap">
                  <div class="fw-bold text-dark" style="font-size: 0.85rem;">₹ <?= number_format($salePriceWithTax, 2) ?></div>
                  <div class="text-muted" style="font-size: 0.7rem;">Pre: ₹ <?= number_format($salePrice, 2) ?> (+<?= $taxRate ?>%)</div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="p-4 text-center text-muted small">
              <i class="bi bi-box-seam fs-3 d-block mb-1 text-secondary"></i>
              No products found in inventory.
              <div class="mt-2"><a href="/items/create" class="btn btn-sm btn-primary">Add Products First</a></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Side: Live Rate List & Estimate Preview Table (Step 2) -->
  <div class="col-lg-7">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="fw-bold text-dark d-flex align-items-center gap-2">
          <span class="badge bg-success text-white rounded-circle p-1" style="width: 22px; height: 22px; line-height: 14px; text-align: center;">2</span>
          <span id="previewSectionTitle">Rate List Items</span>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" id="selectedCountBadge">0 selected</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" id="btnClearAllSelected" style="font-size: 0.75rem;" title="Clear all selected items">
            <i class="bi bi-trash me-1"></i>Clear
          </button>
        </div>
      </div>

      <div class="card-body p-0 d-flex flex-column justify-content-between">
        <!-- Selected Items Table -->
        <div class="table-responsive flex-grow-1" style="min-height: 320px; max-height: 520px;">
          <table class="table table-hover align-middle mb-0" id="selectedItemsTable" style="font-size: 0.82rem;">
            <thead class="table-light sticky-top">
              <tr>
                <th style="width: 25px;" class="text-center">#</th>
                <th>Item Details</th>
                <th style="width: 80px;" class="text-end">Pre-GST</th>
                <th style="width: 60px;" class="text-center">GST</th>
                <th style="width: 85px;" class="text-end">Post-GST</th>
                <!-- Estimate Only Columns -->
                <th style="width: 80px;" class="text-center col-estimate-only d-none">Quantity</th>
                <th style="width: 65px;" class="text-end col-estimate-only d-none">Disc %</th>
                <th style="width: 95px;" class="text-end col-estimate-only d-none">Total (₹)</th>
                <th style="width: 30px;" class="text-center"></th>
              </tr>
            </thead>
            <tbody id="selectedItemsTbody">
              <tr id="emptyTablePlaceholder">
                <td colspan="9" class="text-center py-5 text-muted">
                  <i class="bi bi-hand-index-thumb fs-2 d-block mb-2 text-secondary opacity-50"></i>
                  <span>No items selected yet. Select items from the category list on the left.</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Estimate Mode Live Calculation Summary Box -->
        <div id="estimateSummaryBox" class="p-3 bg-light border-top d-none">
          <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
              <div class="small text-muted mb-1">
                <i class="bi bi-info-circle me-1"></i> Estimates can be converted to official Sales records anytime.
              </div>
              <input type="text" class="form-control form-control-sm" id="metaNotes" placeholder="Optional notes (e.g. Valid for 7 days, payment terms...)">
            </div>
            <div class="col-md-6 text-md-end">
              <div class="d-flex justify-content-between text-muted small mb-1">
                <span>Taxable Amount (Pre-GST):</span>
                <span class="fw-semibold font-monospace" id="calcSubtotal">₹ 0.00</span>
              </div>
              <div class="d-flex justify-content-between text-muted small mb-1">
                <span>GST Tax Total:</span>
                <span class="fw-semibold font-monospace text-primary" id="calcTaxTotal">₹ 0.00</span>
              </div>
              <div class="d-flex justify-content-between fs-6 fw-bold border-top pt-1 mt-1 text-dark">
                <span>Grand Total:</span>
                <span class="text-success font-monospace fs-5" id="calcGrandTotal">₹ 0.00</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="p-3 bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="small text-muted" id="footerHint">
            Select items to preview rate list.
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark" id="btnBottomPrint">
              <i class="bi bi-printer me-1"></i> Print / Download
            </button>
            <button type="button" class="btn btn-success d-none" id="btnBottomSave">
              <i class="bi bi-check2-circle me-1"></i> Save Estimate
            </button>
            <button type="button" class="btn btn-primary d-none" id="btnConvertToInvoice">
              <i class="bi bi-receipt me-1"></i> Convert to Invoice
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden Form for Direct Printing -->
<form id="directPrintForm" action="/estimates/print-direct" method="POST" target="_blank" class="d-none">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="mode" id="directPrintMode" value="ratelist">
  <input type="hidden" name="estimate_number" id="directPrintNumber" value="">
  <input type="hidden" name="estimate_date" id="directPrintDate" value="">
  <input type="hidden" name="customer_name" id="directPrintCustomer" value="">
  <input type="hidden" name="notes" id="directPrintNotes" value="">
  <input type="hidden" name="items_json" id="directPrintItemsJson" value="">
</form>

<!-- Hidden Form for Saving Estimate -->
<form id="saveEstimateForm" action="/estimates/save" method="POST" class="d-none">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="estimate_number" id="saveEstimateNumber" value="">
  <input type="hidden" name="estimate_date" id="saveEstimateDate" value="">
  <input type="hidden" name="customer_name" id="saveEstimateCustomer" value="">
  <input type="hidden" name="notes" id="saveEstimateNotes" value="">
  <input type="hidden" name="items_json" id="saveEstimateItemsJson" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modeRateListRadio = document.getElementById('modeRateList');
  const modeEstimateRadio = document.getElementById('modeEstimate');
  const estimateMetaBox = document.getElementById('estimateMetaBox');
  const estimateSummaryBox = document.getElementById('estimateSummaryBox');
  const previewSectionTitle = document.getElementById('previewSectionTitle');
  const printBtnLabel = document.getElementById('printBtnLabel');
  const footerHint = document.getElementById('footerHint');
  const btnSaveEstimateModal = document.getElementById('btnSaveEstimateModal');
  const btnBottomSave = document.getElementById('btnBottomSave');
  const btnConvertToInvoice = document.getElementById('btnConvertToInvoice');
  const categorySelector = document.getElementById('categorySelector');
  const itemPickerSearch = document.getElementById('itemPickerSearch');
  const btnSelectAllVisible = document.getElementById('btnSelectAllVisible');
  const btnClearAllSelected = document.getElementById('btnClearAllSelected');
  const selectedItemsTbody = document.getElementById('selectedItemsTbody');
  const emptyTablePlaceholder = document.getElementById('emptyTablePlaceholder');
  const selectedCountBadge = document.getElementById('selectedCountBadge');
  const itemsChecklistContainer = document.getElementById('itemsChecklistContainer');
  const allCheckboxRows = Array.from(itemsChecklistContainer.querySelectorAll('.item-checkbox-row'));

  let currentMode = 'ratelist'; // 'ratelist' | 'estimate'
  let selectedItemsMap = new Map(); // id -> itemObject

  // Preloaded data if any
  const preloadedItems = <?= $preloadedItemsJson ?>;
  if (Array.isArray(preloadedItems) && preloadedItems.length > 0) {
    preloadedItems.forEach(it => {
      selectedItemsMap.set(String(it.id || it.name), it);
    });
    // Switch to estimate mode if preloaded
    modeEstimateRadio.checked = true;
    currentMode = 'estimate';
  }

  function syncModeUI() {
    const isEstimate = (currentMode === 'estimate');
    if (isEstimate) {
      estimateMetaBox.classList.remove('d-none');
      estimateSummaryBox.classList.remove('d-none');
      btnSaveEstimateModal.classList.remove('d-none');
      btnBottomSave.classList.remove('d-none');
      btnConvertToInvoice.classList.remove('d-none');
      previewSectionTitle.textContent = 'Estimate Items & Quantities';
      printBtnLabel.textContent = 'Print Estimate';
      footerHint.textContent = 'Adjust quantities and discount, then save or print estimate.';
      document.querySelectorAll('.col-estimate-only').forEach(el => el.classList.remove('d-none'));
    } else {
      estimateMetaBox.classList.add('d-none');
      estimateSummaryBox.classList.add('d-none');
      btnSaveEstimateModal.classList.add('d-none');
      btnBottomSave.classList.add('d-none');
      btnConvertToInvoice.classList.add('d-none');
      previewSectionTitle.textContent = 'Rate List Items';
      printBtnLabel.textContent = 'Print Rate List';
      footerHint.textContent = 'Select items to preview and print a clean product rate list.';
      document.querySelectorAll('.col-estimate-only').forEach(el => el.classList.add('d-none'));
    }
    renderSelectedItemsTable();
  }

  modeRateListRadio.addEventListener('change', () => {
    currentMode = 'ratelist';
    syncModeUI();
  });

  modeEstimateRadio.addEventListener('change', () => {
    currentMode = 'estimate';
    syncModeUI();
  });

  // Filter items in the left panel based on Category and Search
  function filterItemsList() {
    const selectedCat = categorySelector.value;
    const query = (itemPickerSearch.value || '').trim().toLowerCase();

    allCheckboxRows.forEach(row => {
      const cat = (row.dataset.category || '').trim();
      const name = (row.dataset.name || '').trim();
      const hsn = (row.dataset.hsn || '').trim();

      let matchCat = false;
      if (selectedCat === 'all') {
        matchCat = true;
      } else if (selectedCat === '__none__') {
        matchCat = (cat === '');
      } else {
        matchCat = (cat.toLowerCase() === selectedCat.toLowerCase());
      }

      let matchQuery = true;
      if (query !== '') {
        matchQuery = name.includes(query) || hsn.includes(query);
      }

      if (matchCat && matchQuery) {
        row.classList.remove('d-none');
      } else {
        row.classList.add('d-none');
      }
    });

    updateVisibleCheckboxStates();
  }

  categorySelector.addEventListener('change', filterItemsList);
  itemPickerSearch.addEventListener('input', filterItemsList);

  // Sync checkboxes on left panel with selectedItemsMap
  function updateVisibleCheckboxStates() {
    allCheckboxRows.forEach(row => {
      const id = row.dataset.itemId;
      const chk = row.querySelector('.item-check-input');
      if (chk) {
        chk.checked = selectedItemsMap.has(String(id));
      }
    });
  }

  // Handle individual checkbox changes
  itemsChecklistContainer.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('item-check-input')) {
      const row = e.target.closest('.item-checkbox-row');
      const id = row.dataset.itemId;
      if (e.target.checked) {
        selectedItemsMap.set(String(id), {
          id: parseInt(id),
          name: row.querySelector('.fw-bold').textContent.trim(),
          hsn_code: row.dataset.hsn || '',
          unit: row.dataset.unit || 'PCS',
          sub_unit: row.dataset.subUnit || '',
          sub_unit_qty: row.dataset.subUnitQty ? parseFloat(row.dataset.subUnitQty) : null,
          sale_price: parseFloat(row.dataset.preGst) || 0,
          tax_rate: parseFloat(row.dataset.taxRate) || 0,
          quantity: 1,
          discount_percent: 0
        });
      } else {
        selectedItemsMap.delete(String(id));
      }
      renderSelectedItemsTable();
    }
  });

  // Select all currently visible items
  btnSelectAllVisible.addEventListener('click', () => {
    const visibleRows = allCheckboxRows.filter(r => !r.classList.contains('d-none'));
    const allChecked = visibleRows.every(r => {
      const chk = r.querySelector('.item-check-input');
      return chk && chk.checked;
    });

    visibleRows.forEach(row => {
      const id = row.dataset.itemId;
      const chk = row.querySelector('.item-check-input');
      if (allChecked) {
        // Deselect all visible
        selectedItemsMap.delete(String(id));
        if (chk) chk.checked = false;
      } else {
        // Select all visible
        selectedItemsMap.set(String(id), {
          id: parseInt(id),
          name: row.querySelector('.fw-bold').textContent.trim(),
          hsn_code: row.dataset.hsn || '',
          unit: row.dataset.unit || 'PCS',
          sub_unit: row.dataset.subUnit || '',
          sub_unit_qty: row.dataset.subUnitQty ? parseFloat(row.dataset.subUnitQty) : null,
          sale_price: parseFloat(row.dataset.preGst) || 0,
          tax_rate: parseFloat(row.dataset.taxRate) || 0,
          quantity: 1,
          discount_percent: 0
        });
        if (chk) chk.checked = true;
      }
    });

    renderSelectedItemsTable();
  });

  // Clear all selected items
  btnClearAllSelected.addEventListener('click', () => {
    if (selectedItemsMap.size === 0) return;
    if (confirm('Are you sure you want to clear all selected items?')) {
      selectedItemsMap.clear();
      updateVisibleCheckboxStates();
      renderSelectedItemsTable();
    }
  });

  // Render the Selected Items Table & recalculate totals
  function renderSelectedItemsTable() {
    const isEstimate = (currentMode === 'estimate');
    const items = Array.from(selectedItemsMap.values());
    selectedCountBadge.textContent = `${items.length} selected`;

    if (items.length === 0) {
      selectedItemsTbody.innerHTML = `
        <tr id="emptyTablePlaceholder">
          <td colspan="9" class="text-center py-5 text-muted">
            <i class="bi bi-hand-index-thumb fs-2 d-block mb-2 text-secondary opacity-50"></i>
            <span>No items selected yet. Select items from the category list on the left.</span>
          </td>
        </tr>
      `;
      calcTotals();
      return;
    }

    let html = '';
    items.forEach((item, index) => {
      const preGst = item.sale_price;
      const taxRate = item.tax_rate;
      const postGst = preGst * (1 + (taxRate / 100));
      const qty = item.quantity || 1;
      const disc = item.discount_percent || 0;

      const lineBase = qty * preGst;
      const lineDisc = lineBase * (disc / 100);
      const lineTaxable = lineBase - lineDisc;
      const lineTax = lineTaxable * (taxRate / 100);
      const lineTotal = lineTaxable + lineTax;

      // Sub-unit representation
      let subUnitDisplay = '';
      if (item.sub_unit && item.sub_unit_qty) {
        const totalSubUnits = (qty * item.sub_unit_qty).toFixed(1).replace(/\.0$/, '');
        subUnitDisplay = `<div class="text-primary small" style="font-size: 0.72rem;">= ${totalSubUnits} ${item.sub_unit} (${item.sub_unit_qty} / ${item.unit})</div>`;
      }

      html += `
        <tr data-item-id="${item.id}">
          <td class="text-center text-muted font-monospace">${index + 1}</td>
          <td>
            <div class="fw-bold text-dark">${escapeHtml(item.name)}</div>
            <div class="small text-muted font-monospace" style="font-size: 0.72rem;">
              Unit: ${escapeHtml(item.unit)} ${item.hsn_code ? '| HSN: ' + escapeHtml(item.hsn_code) : ''}
            </div>
            ${subUnitDisplay}
          </td>
          <td class="text-end fw-semibold">₹ ${preGst.toFixed(2)}</td>
          <td class="text-center">
            <span class="badge ${taxRate > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border'}" style="font-size: 0.7rem;">
              ${taxRate}%
            </span>
          </td>
          <td class="text-end fw-bold text-dark">₹ ${postGst.toFixed(2)}</td>
          
          <!-- Estimate Mode Inputs -->
          <td class="text-center col-estimate-only ${isEstimate ? '' : 'd-none'}">
            <div class="input-group input-group-sm mx-auto" style="max-width: 90px;">
              <input type="number" class="form-control form-control-sm text-center fw-bold row-qty-input" 
                     min="0.01" step="any" value="${qty}" data-item-id="${item.id}">
            </div>
          </td>
          <td class="text-end col-estimate-only ${isEstimate ? '' : 'd-none'}">
            <div class="input-group input-group-sm ms-auto" style="max-width: 70px;">
              <input type="number" class="form-control form-control-sm text-end row-disc-input" 
                     min="0" max="100" step="any" value="${disc}" data-item-id="${item.id}">
            </div>
          </td>
          <td class="text-end fw-bold text-success col-estimate-only ${isEstimate ? '' : 'd-none'}">
            ₹ ${lineTotal.toFixed(2)}
          </td>

          <!-- Remove Action -->
          <td class="text-center">
            <button type="button" class="btn btn-link text-danger p-0 border-0 btn-remove-item" data-item-id="${item.id}" title="Remove item">
              <i class="bi bi-x-circle-fill"></i>
            </button>
          </td>
        </tr>
      `;
    });

    selectedItemsTbody.innerHTML = html;
    calcTotals();
  }

  // Handle table row input changes (Qty and Disc)
  selectedItemsTbody.addEventListener('input', (e) => {
    if (e.target.classList.contains('row-qty-input')) {
      const id = String(e.target.dataset.itemId);
      const val = parseFloat(e.target.value) || 1;
      if (selectedItemsMap.has(id)) {
        selectedItemsMap.get(id).quantity = val;
      }
      calcTotals();
    } else if (e.target.classList.contains('row-disc-input')) {
      const id = String(e.target.dataset.itemId);
      const val = Math.min(100, Math.max(0, parseFloat(e.target.value) || 0));
      if (selectedItemsMap.has(id)) {
        selectedItemsMap.get(id).discount_percent = val;
      }
      calcTotals();
    }
  });

  // Handle table row remove
  selectedItemsTbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-remove-item');
    if (btn) {
      const id = String(btn.dataset.itemId);
      selectedItemsMap.delete(id);
      updateVisibleCheckboxStates();
      renderSelectedItemsTable();
    }
  });

  // Calculate Subtotals and Grand Total
  function calcTotals() {
    let subtotal = 0;
    let taxTotal = 0;
    let grandTotal = 0;

    selectedItemsMap.forEach(item => {
      const preGst = item.sale_price;
      const taxRate = item.tax_rate;
      const qty = item.quantity || 1;
      const disc = item.discount_percent || 0;

      const lineBase = qty * preGst;
      const lineDisc = lineBase * (disc / 100);
      const lineTaxable = lineBase - lineDisc;
      const lineTax = lineTaxable * (taxRate / 100);
      const lineTotal = lineTaxable + lineTax;

      subtotal += lineTaxable;
      taxTotal += lineTax;
      grandTotal += lineTotal;
    });

    document.getElementById('calcSubtotal').textContent = `₹ ${subtotal.toFixed(2)}`;
    document.getElementById('calcTaxTotal').textContent = `₹ ${taxTotal.toFixed(2)}`;
    document.getElementById('calcGrandTotal').textContent = `₹ ${grandTotal.toFixed(2)}`;
  }

  // Print Handling (Rate list or Estimate)
  function handlePrint() {
    if (selectedItemsMap.size === 0) {
      alert('Please select at least one item to print.');
      return;
    }

    const form = document.getElementById('directPrintForm');
    document.getElementById('directPrintMode').value = currentMode;
    document.getElementById('directPrintNumber').value = document.getElementById('metaEstimateNumber').value || '';
    document.getElementById('directPrintDate').value = document.getElementById('metaEstimateDate').value || '';
    document.getElementById('directPrintCustomer').value = document.getElementById('metaCustomerName').value || '';
    document.getElementById('directPrintNotes').value = document.getElementById('metaNotes').value || '';

    const itemsArr = Array.from(selectedItemsMap.values()).map(it => ({
      item_id: it.id,
      item_name: it.name,
      hsn_code: it.hsn_code,
      unit: it.unit,
      sub_unit: it.sub_unit,
      sub_unit_qty: it.sub_unit_qty,
      quantity: it.quantity || 1,
      rate: it.sale_price,
      tax_rate: it.tax_rate,
      discount_percent: it.discount_percent || 0
    }));

    document.getElementById('directPrintItemsJson').value = JSON.stringify(itemsArr);
    form.submit();
  }

  document.getElementById('btnPrintCurrent').addEventListener('click', handlePrint);
  document.getElementById('btnBottomPrint').addEventListener('click', handlePrint);

  // Save Estimate Handling
  async function handleSaveEstimate() {
    if (selectedItemsMap.size === 0) {
      alert('Please select at least one item before saving.');
      return;
    }

    const itemsArr = Array.from(selectedItemsMap.values()).map(it => ({
      item_id: it.id,
      item_name: it.name,
      hsn_code: it.hsn_code,
      unit: it.unit,
      sub_unit: it.sub_unit,
      sub_unit_qty: it.sub_unit_qty,
      quantity: it.quantity || 1,
      rate: it.sale_price,
      tax_rate: it.tax_rate,
      discount_percent: it.discount_percent || 0
    }));

    const form = document.getElementById('saveEstimateForm');
    document.getElementById('saveEstimateNumber').value = document.getElementById('metaEstimateNumber').value.trim();
    document.getElementById('saveEstimateDate').value = document.getElementById('metaEstimateDate').value.trim();
    document.getElementById('saveEstimateCustomer').value = document.getElementById('metaCustomerName').value.trim();
    document.getElementById('saveEstimateNotes').value = document.getElementById('metaNotes').value.trim();
    document.getElementById('saveEstimateItemsJson').value = JSON.stringify(itemsArr);

    form.submit();
  }

  btnSaveEstimateModal.addEventListener('click', handleSaveEstimate);
  btnBottomSave.addEventListener('click', handleSaveEstimate);

  // Convert to Sales Invoice handling
  btnConvertToInvoice.addEventListener('click', async () => {
    if (selectedItemsMap.size === 0) {
      alert('Please select at least one item.');
      return;
    }

    if (confirm('Would you like to save this Estimate first and then convert to a Sales Record?')) {
      // First save via AJAX, then redirect to /sales/create?estimate_id=...
      const itemsArr = Array.from(selectedItemsMap.values()).map(it => ({
        item_id: it.id,
        item_name: it.name,
        hsn_code: it.hsn_code,
        unit: it.unit,
        sub_unit: it.sub_unit,
        sub_unit_qty: it.sub_unit_qty,
        quantity: it.quantity || 1,
        rate: it.sale_price,
        tax_rate: it.tax_rate,
        discount_percent: it.discount_percent || 0
      }));

      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('/estimates/save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({
            estimate_number: document.getElementById('metaEstimateNumber').value.trim(),
            estimate_date: document.getElementById('metaEstimateDate').value.trim(),
            customer_name: document.getElementById('metaCustomerName').value.trim(),
            notes: document.getElementById('metaNotes').value.trim(),
            items_json: JSON.stringify(itemsArr),
            _csrf: csrfToken
          })
        });

        const data = await res.json();
        if (data.success && data.estimate_id) {
          window.location.href = `/sales/create?estimate_id=${data.estimate_id}`;
        } else {
          alert(data.error || 'Failed to save estimate before converting.');
        }
      } catch (err) {
        alert('Error: ' + err.message);
      }
    }
  });

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Initial trigger
  syncModeUI();
  filterItemsList();
});
</script>
