<?php
/**
 * Rate List & Estimate Builder View
 * RACE FINANCE - Billing & Inventory System
 */

$firmName = $firm['name'] ?? 'RACE FINANCE';
$firmSettings = !empty($firm['settings']) ? (is_array($firm['settings']) ? $firm['settings'] : json_decode($firm['settings'], true)) : [];
$activeDateFormat = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$categories = $categories ?? [];
$allItems = $allItems ?? [];
$parties = $parties ?? [];
$nextEstimateNumber = $nextEstimateNumber ?? '001';
$rawEstimateDate = !empty($preloadedEstimate['estimate_date']) ? $preloadedEstimate['estimate_date'] : date('Y-m-d');
$formattedEstimateDate = formatDate($rawEstimateDate, $activeDateFormat);
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

<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Rate List &amp; Estimate Builder</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Select items category-wise, generate customer rate lists or switch to full estimates.</p>
    </div>
    <a href="/estimates" class="rf-btn rf-btn-secondary">
      <i class="bi bi-journal-text"></i> Saved Estimates
    </a>
  </div>

  <!-- Mode Switcher Card -->
  <div class="rf-card p-5 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-[#78746C]">Mode</span>
        <div class="builder-mode-toggle" role="group" aria-label="Builder Mode">
          <input type="radio" name="builder_mode" id="modeRateList" value="ratelist" checked autocomplete="off">
          <label class="btn-ratelist inline-flex items-center gap-2" for="modeRateList">
            <i class="bi bi-list-check"></i><span>Rate List</span>
          </label>
          <input type="radio" name="builder_mode" id="modeEstimate" value="estimate" autocomplete="off">
          <label class="btn-estimate inline-flex items-center gap-2" for="modeEstimate">
            <i class="bi bi-calculator"></i><span>Estimate (Quotation)</span>
          </label>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" id="btnPrintCurrent">
          <i class="bi bi-printer"></i> <span id="printBtnLabel">Print Rate List</span>
        </button>
        <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-sm hidden" id="btnSaveEstimateModal">
          <i class="bi bi-check2-circle"></i> Save Estimate
        </button>
      </div>
    </div>

    <!-- Estimate Metadata Row -->
    <div id="estimateMetaBox" class="pt-4 border-t border-[#ECEAE4] hidden">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
        <div class="md:col-span-3">
          <label class="rf-label-plain text-xs" for="metaEstimateNumber">Estimate Number</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-mono text-[#78746C]">#</span>
            <input type="text" class="rf-input rf-input-sm pl-7 font-mono font-semibold" id="metaEstimateNumber" value="<?= htmlspecialchars($preloadedNumber, ENT_QUOTES, 'UTF-8') ?>" placeholder="001">
          </div>
          <p class="rf-help">Separate sequence starting from 001</p>
        </div>

        <div class="md:col-span-3">
          <label class="rf-label-plain text-xs" for="metaEstimateDate">Estimate Date</label>
          <div class="relative">
            <input type="text" class="rf-input rf-input-sm pr-9 date-picker" id="metaEstimateDate" value="<?= htmlspecialchars($formattedEstimateDate, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" data-date-format="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" maxlength="10" autocomplete="off">
            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#78746C] bg-transparent border-0 cursor-pointer p-0" onclick="const el = document.getElementById('metaEstimateDate'); if (el._flatpickr) { el._flatpickr.open(); } else { el.focus(); }" title="Open calendar">
              <i class="bi bi-calendar3 text-xs"></i>
            </button>
          </div>
          <p class="rf-help">Click icon to open calendar</p>
        </div>

        <div class="md:col-span-6">
          <div class="flex justify-between items-center mb-1.5">
            <label class="text-xs font-medium text-[#2C2B27]" for="metaCustomerName">Customer / Party Name</label>
            <span class="rf-badge rf-badge-neutral text-[10px]">Search or Type</span>
          </div>
          <div class="relative">
            <div class="relative flex items-center">
              <i class="bi bi-search absolute left-3 text-[#87847C] text-xs pointer-events-none"></i>
              <input type="text" class="rf-input rf-input-sm pl-8 pr-8" id="metaCustomerName" value="<?= htmlspecialchars($preloadedCustomer, ENT_QUOTES, 'UTF-8') ?>" placeholder="Type name, phone or GSTIN to search party..." autocomplete="off">
              <button class="absolute right-2 text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" type="button" id="btnClearCustomerName" title="Clear customer name" style="<?= !empty($preloadedCustomer) ? 'display: block;' : 'display: none;' ?>">
                <i class="bi bi-x-lg text-xs"></i>
              </button>
            </div>
            <div id="customerSearchResults" class="hidden absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto z-50 bg-white border border-[#E2DFD7] rounded-xl shadow-lg"></div>
          </div>
          <p class="rf-help">Select an existing party or type custom customer name (optional).</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Split Builder Workspace -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- Left: Category & Item Picker -->
    <div class="lg:col-span-5 rf-card">
      <div class="rf-card-header py-3">
        <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
          <span class="w-5 h-5 rounded-full bg-[#1C1B18] text-white text-[11px] font-mono flex items-center justify-center">1</span>
          <span>Select Items from Category</span>
        </div>
        <span class="rf-badge rf-badge-neutral tabular-nums" id="itemsAvailableBadge"><?= count($allItems) ?> Available</span>
      </div>

      <div class="p-4 space-y-3">
        <div>
          <label for="categorySelector" class="rf-label-plain text-xs"><i class="bi bi-tags mr-1 text-[#78746C]"></i>Select Category</label>
          <select class="rf-select rf-select-sm" id="categorySelector">
            <option value="all">All Categories</option>
            <option value="__none__">-- Uncategorized --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative flex-1">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
            <input type="text" class="rf-input rf-input-sm pl-8" id="itemPickerSearch" placeholder="Filter category items...">
          </div>
          <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm shrink-0" id="btnSelectAllVisible">Select All</button>
        </div>

        <div class="border border-[#E6E4DF] rounded-xl p-2 bg-[#FAF9F6] max-h-[480px] overflow-y-auto space-y-1.5" id="itemsChecklistContainer">
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
              <div class="item-checkbox-row p-2.5 bg-white rounded-lg flex items-center justify-between gap-2"
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
                <div class="flex items-center gap-2.5 min-w-0">
                  <input class="rf-checkbox item-check-input shrink-0" type="checkbox" id="chkItem<?= $it['id'] ?>" value="<?= $it['id'] ?>">
                  <label class="min-w-0 cursor-pointer" for="chkItem<?= $it['id'] ?>">
                    <div class="item-name-label text-xs font-semibold text-[#1C1B18] truncate"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-[11px] text-[#78746C] flex flex-wrap items-center gap-1.5 mt-0.5">
                      <?php if (!empty($catVal)): ?>
                        <span class="rf-badge rf-badge-neutral text-[10px] py-0"><?= htmlspecialchars($catVal, ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                      <span><?= htmlspecialchars($it['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
                      <?php if (!empty($subUnitStr)): ?>
                        <span class="text-[#2B526E]">(<?= htmlspecialchars($subUnitStr, ENT_QUOTES, 'UTF-8') ?>)</span>
                      <?php endif; ?>
                    </div>
                  </label>
                </div>

                <div class="text-right whitespace-nowrap shrink-0 tabular-nums">
                  <div class="text-xs font-semibold text-[#1C1B18]">₹ <?= number_format($salePriceWithTax, 2) ?></div>
                  <div class="text-[10px] text-[#78746C]">Pre: ₹ <?= number_format($salePrice, 2) ?> (+<?= $taxRate ?>%)</div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="p-8 text-center text-xs text-[#78746C]">
              <i class="bi bi-box-seam text-2xl block mb-2 text-[#B5B0A6]"></i>
              No products found in inventory.
              <div class="mt-3"><a href="/items/create" class="rf-btn rf-btn-primary rf-btn-sm">Add Products First</a></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: Live Selected Table & Totals -->
    <div class="lg:col-span-7 rf-card flex flex-col">
      <div class="rf-card-header py-3">
        <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
          <span class="w-5 h-5 rounded-full bg-[#2E5A36] text-white text-[11px] font-mono flex items-center justify-center">2</span>
          <span id="previewSectionTitle">Rate List Items</span>
          <span class="rf-badge rf-badge-slate tabular-nums" id="selectedCountBadge">0 selected</span>
        </div>
        <button type="button" class="rf-btn rf-btn-danger rf-btn-sm py-1 px-2.5" id="btnClearAllSelected" title="Clear all selected items">
          <i class="bi bi-trash"></i> Clear
        </button>
      </div>

      <div class="rf-table-wrap flex-1 min-h-[320px] max-h-[520px]">
        <table class="rf-table text-xs" id="selectedItemsTable">
          <thead class="sticky top-0 z-10">
            <tr>
              <th class="w-8 text-center">#</th>
              <th class="min-w-[160px]">Item Details</th>
              <th class="w-28 text-right">Rate (₹)</th>
              <th class="w-16 text-center col-estimate-only hidden">Qty</th>
              <th class="w-20 text-right">Disc %</th>
              <th class="w-24 text-right col-estimate-only hidden">Total (₹)</th>
              <th class="w-8 text-center"></th>
            </tr>
          </thead>
          <tbody id="selectedItemsTbody">
            <tr id="emptyTablePlaceholder">
              <td colspan="7" class="text-center py-16 text-[#78746C]">
                <i class="bi bi-hand-index-thumb text-3xl block mb-2 text-[#C8C4BA]"></i>
                <span>No items selected yet. Select items from the category list on the left.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estimate Summary -->
      <div id="estimateSummaryBox" class="p-4 bg-[#FAF9F6] border-t border-[#ECEAE4] hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
          <div class="space-y-1.5">
            <div class="text-[11px] text-[#78746C]"><i class="bi bi-info-circle mr-1"></i> Estimates can be converted to Sales records anytime.</div>
            <input type="text" class="rf-input rf-input-sm" id="metaNotes" placeholder="Optional notes (e.g. Valid for 7 days...)">
          </div>
          <div class="space-y-1 text-xs">
            <div class="flex justify-between text-[#5C5A54]">
              <span>Taxable Amount (Pre-GST)</span>
              <span class="font-mono font-medium tabular-nums" id="calcSubtotal">₹ 0.00</span>
            </div>
            <div class="flex justify-between text-[#5C5A54]">
              <span>GST Tax Total</span>
              <span class="font-mono font-medium text-[#2B526E] tabular-nums" id="calcTaxTotal">₹ 0.00</span>
            </div>
            <div class="flex justify-between items-center pt-1.5 border-t border-[#E2DFD7] text-sm font-semibold text-[#1C1B18]">
              <span>Grand Total</span>
              <span class="font-mono text-[#2E5A36] text-base tabular-nums" id="calcGrandTotal">₹ 0.00</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="rf-card-footer">
        <div class="text-xs text-[#78746C]" id="footerHint">Select items to preview rate list.</div>
        <div class="flex flex-wrap items-center gap-2">
          <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" id="btnBottomPrint">
            <i class="bi bi-printer"></i> Print / Download
          </button>
          <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-sm hidden" id="btnBottomSave">
            <i class="bi bi-check2-circle"></i> Save Estimate
          </button>
          <button type="button" class="rf-btn rf-btn-primary rf-btn-sm hidden" id="btnConvertToInvoice">
            <i class="bi bi-receipt"></i> Convert to Invoice
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden Form for Direct Printing -->
<form id="directPrintForm" action="/estimates/print-direct" method="POST" target="_blank" class="hidden">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="mode" id="directPrintMode" value="ratelist">
  <input type="hidden" name="estimate_number" id="directPrintNumber" value="">
  <input type="hidden" name="estimate_date" id="directPrintDate" value="">
  <input type="hidden" name="customer_name" id="directPrintCustomer" value="">
  <input type="hidden" name="notes" id="directPrintNotes" value="">
  <input type="hidden" name="items_json" id="directPrintItemsJson" value="">
</form>

<!-- Hidden Form for Saving Estimate -->
<form id="saveEstimateForm" action="/estimates/save" method="POST" class="hidden">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="estimate_number" id="saveEstimateNumber" value="">
  <input type="hidden" name="estimate_date" id="saveEstimateDate" value="">
  <input type="hidden" name="customer_name" id="saveEstimateCustomer" value="">
  <input type="hidden" name="notes" id="saveEstimateNotes" value="">
  <input type="hidden" name="items_json" id="saveEstimateItemsJson" value="">
</form>

<script id="partiesData" type="application/json">
  <?= json_encode(array_map(function($p) {
      return [
          'id' => (int)$p['id'],
          'name' => $p['name'],
          'phone' => $p['phone'] ?? '',
          'gstin' => $p['gstin'] ?? '',
          'state' => $p['state'] ?? '',
          'address' => $p['billing_address'] ?? ''
      ];
  }, $parties), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>

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
  const selectedCountBadge = document.getElementById('selectedCountBadge');
  const itemsChecklistContainer = document.getElementById('itemsChecklistContainer');
  const allCheckboxRows = Array.from(itemsChecklistContainer.querySelectorAll('.item-checkbox-row'));

  let currentMode = 'ratelist';
  let selectedItemsMap = new Map();

  const preloadedItems = <?= $preloadedItemsJson ?>;
  if (Array.isArray(preloadedItems) && preloadedItems.length > 0) {
    preloadedItems.forEach(it => {
      selectedItemsMap.set(String(it.id || it.name), it);
    });
    modeEstimateRadio.checked = true;
    currentMode = 'estimate';
  }

  function syncModeUI() {
    const isEstimate = (currentMode === 'estimate');
    if (isEstimate) {
      estimateMetaBox.classList.remove('hidden');
      estimateSummaryBox.classList.remove('hidden');
      btnSaveEstimateModal.classList.remove('hidden');
      btnBottomSave.classList.remove('hidden');
      btnConvertToInvoice.classList.remove('hidden');
      previewSectionTitle.textContent = 'Estimate Items & Quantities';
      printBtnLabel.textContent = 'Print Estimate';
      footerHint.textContent = 'Adjust quantities and discount, then save or print estimate.';
      document.querySelectorAll('.col-estimate-only').forEach(el => el.classList.remove('hidden'));
      initDatePickerIfNeeded();
    } else {
      estimateMetaBox.classList.add('hidden');
      estimateSummaryBox.classList.add('hidden');
      btnSaveEstimateModal.classList.add('hidden');
      btnBottomSave.classList.add('hidden');
      btnConvertToInvoice.classList.add('hidden');
      previewSectionTitle.textContent = 'Rate List Items';
      printBtnLabel.textContent = 'Print Rate List';
      footerHint.textContent = 'Select items, set discounts, preview and print a clean product rate list.';
      document.querySelectorAll('.col-estimate-only').forEach(el => el.classList.add('hidden'));
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

  function filterItemsList() {
    const selectedCat = categorySelector.value;
    const query = (itemPickerSearch.value || '').trim().toLowerCase();

    allCheckboxRows.forEach(row => {
      const cat = (row.dataset.category || '').trim();
      const name = (row.dataset.name || '').trim();
      const hsn = (row.dataset.hsn || '').trim();

      let matchCat = false;
      if (selectedCat === 'all') matchCat = true;
      else if (selectedCat === '__none__') matchCat = (cat === '');
      else matchCat = (cat.toLowerCase() === selectedCat.toLowerCase());

      let matchQuery = true;
      if (query !== '') matchQuery = name.includes(query) || hsn.includes(query);

      if (matchCat && matchQuery) row.classList.remove('hidden');
      else row.classList.add('hidden');
    });

    updateVisibleCheckboxStates();
  }

  categorySelector.addEventListener('change', filterItemsList);
  itemPickerSearch.addEventListener('input', filterItemsList);

  function updateVisibleCheckboxStates() {
    allCheckboxRows.forEach(row => {
      const id = row.dataset.itemId;
      const chk = row.querySelector('.item-check-input');
      const isChecked = selectedItemsMap.has(String(id));
      if (chk) chk.checked = isChecked;
      row.classList.toggle('is-selected', isChecked);
    });
  }

  function extractItemFromRow(row) {
    return {
      id: parseInt(row.dataset.itemId),
      name: row.querySelector('.item-name-label').textContent.trim(),
      hsn_code: row.dataset.hsn || '',
      unit: row.dataset.unit || 'PCS',
      sub_unit: row.dataset.subUnit || '',
      sub_unit_qty: row.dataset.subUnitQty ? parseFloat(row.dataset.subUnitQty) : null,
      sale_price: parseFloat(row.dataset.preGst) || 0,
      tax_rate: parseFloat(row.dataset.taxRate) || 0,
      quantity: 1,
      discount_percent: 0
    };
  }

  itemsChecklistContainer.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('item-check-input')) {
      const row = e.target.closest('.item-checkbox-row');
      const id = row.dataset.itemId;
      if (e.target.checked) {
        row.classList.add('is-selected');
        selectedItemsMap.set(String(id), extractItemFromRow(row));
      } else {
        row.classList.remove('is-selected');
        selectedItemsMap.delete(String(id));
      }
      renderSelectedItemsTable();
    }
  });

  itemsChecklistContainer.addEventListener('click', (e) => {
    if (e.target.closest('input[type="checkbox"]') || e.target.closest('label')) return;
    const row = e.target.closest('.item-checkbox-row');
    if (row) {
      const chk = row.querySelector('.item-check-input');
      if (chk) {
        chk.checked = !chk.checked;
        chk.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
  });

  btnSelectAllVisible.addEventListener('click', () => {
    const visibleRows = allCheckboxRows.filter(r => !r.classList.contains('hidden'));
    const allChecked = visibleRows.every(r => {
      const chk = r.querySelector('.item-check-input');
      return chk && chk.checked;
    });

    visibleRows.forEach(row => {
      const id = row.dataset.itemId;
      const chk = row.querySelector('.item-check-input');
      if (allChecked) {
        selectedItemsMap.delete(String(id));
        if (chk) chk.checked = false;
        row.classList.remove('is-selected');
      } else {
        selectedItemsMap.set(String(id), extractItemFromRow(row));
        if (chk) chk.checked = true;
        row.classList.add('is-selected');
      }
    });

    renderSelectedItemsTable();
  });

  btnClearAllSelected.addEventListener('click', () => {
    if (selectedItemsMap.size === 0) return;
    if (confirm('Are you sure you want to clear all selected items?')) {
      selectedItemsMap.clear();
      updateVisibleCheckboxStates();
      renderSelectedItemsTable();
    }
  });

  function renderSelectedItemsTable() {
    const isEstimate = (currentMode === 'estimate');
    const items = Array.from(selectedItemsMap.values());
    selectedCountBadge.textContent = `${items.length} selected`;

    if (items.length === 0) {
      selectedItemsTbody.innerHTML = `
        <tr id="emptyTablePlaceholder">
          <td colspan="7" class="text-center py-16 text-[#78746C]">
            <i class="bi bi-hand-index-thumb text-3xl block mb-2 text-[#C8C4BA]"></i>
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
      const qty = item.quantity || 1;
      const disc = item.discount_percent || 0;

      const lineBase = qty * preGst;
      const lineDisc = lineBase * (disc / 100);
      const lineTaxable = lineBase - lineDisc;
      const lineTax = lineTaxable * (taxRate / 100);
      const lineTotal = lineTaxable + lineTax;

      let subUnitDisplay = '';
      if (item.sub_unit && item.sub_unit_qty) {
        const totalSubUnits = (qty * item.sub_unit_qty).toFixed(1).replace(/\.0$/, '');
        subUnitDisplay = `<div class="text-[11px] text-[#2B526E]">= ${totalSubUnits} ${item.sub_unit} (${item.sub_unit_qty} / ${item.unit})</div>`;
      }

      html += `
        <tr data-item-id="${item.id}">
          <td class="text-center text-[#78746C] font-mono tabular-nums">${index + 1}</td>
          <td>
            <div class="font-semibold text-[#1C1B18]">${escapeHtml(item.name)}</div>
            <div class="text-[11px] text-[#78746C] font-mono">
              Unit: ${escapeHtml(item.unit)} ${item.hsn_code ? '| HSN: ' + escapeHtml(item.hsn_code) : ''}
            </div>
            ${subUnitDisplay}
          </td>
          <td class="text-right row-rate-cell tabular-nums">
            ${getRateCellHtml(item)}
          </td>
          <td class="text-center col-estimate-only ${isEstimate ? '' : 'hidden'} px-1">
            <input type="number" class="rf-input rf-input-sm text-center font-semibold row-qty-input selected-table-input w-14 mx-auto px-1 tabular-nums"
                   min="0.01" step="any" value="${qty}" data-item-id="${item.id}">
          </td>
          <td class="text-right px-1">
            <div class="inline-flex items-center w-16">
              <input type="number" class="rf-input rf-input-sm text-right rounded-r-none row-disc-input selected-table-input px-1 tabular-nums"
                     min="0" max="100" step="any" value="${disc}" data-item-id="${item.id}">
              <span class="px-1.5 py-1.5 text-[11px] bg-[#F3F1EC] border border-l-0 border-[#D9D6CE] rounded-r-md text-[#78746C]">%</span>
            </div>
          </td>
          <td class="text-right font-semibold text-[#2E5A36] col-estimate-only ${isEstimate ? '' : 'hidden'} row-total-display tabular-nums">
            ₹ ${lineTotal.toFixed(2)}
          </td>
          <td class="text-center">
            <button type="button" class="text-[#87847C] hover:text-[#8F2D2B] bg-transparent border-0 p-1 cursor-pointer btn-remove-item" data-item-id="${item.id}" title="Remove item">
              <i class="bi bi-x-circle"></i>
            </button>
          </td>
        </tr>
      `;
    });

    selectedItemsTbody.innerHTML = html;
    calcTotals();
  }

  function getRateCellHtml(item) {
    const preGst = item.sale_price;
    const taxRate = item.tax_rate;
    const disc = item.discount_percent || 0;
    const netPreGst = disc > 0 ? (preGst * (1 - (disc / 100))) : preGst;
    const postGst = netPreGst * (1 + (taxRate / 100));
    const origPostGst = preGst * (1 + (taxRate / 100));

    if (disc > 0) {
      return `
        <div class="font-semibold text-[#1C1B18]">₹ ${postGst.toFixed(2)}</div>
        <div class="text-[11px] text-[#78746C]"><s>₹ ${origPostGst.toFixed(2)}</s> Pre: ₹ ${netPreGst.toFixed(2)} (${taxRate}%)</div>
      `;
    }
    return `
      <div class="font-semibold text-[#1C1B18]">₹ ${postGst.toFixed(2)}</div>
      <div class="text-[11px] text-[#78746C]">Pre: ₹ ${preGst.toFixed(2)} (${taxRate}%)</div>
    `;
  }

  function updateRowRate(tr, item) {
    if (!tr || !item) return;
    const cell = tr.querySelector('.row-rate-cell');
    if (cell) cell.innerHTML = getRateCellHtml(item);
  }

  function updateRowTotal(tr, item) {
    if (!tr || !item) return;
    const preGst = item.sale_price;
    const taxRate = item.tax_rate;
    const qty = item.quantity || 1;
    const disc = item.discount_percent || 0;
    const lineBase = qty * preGst;
    const lineDisc = lineBase * (disc / 100);
    const lineTaxable = lineBase - lineDisc;
    const lineTax = lineTaxable * (taxRate / 100);
    const lineTotal = lineTaxable + lineTax;
    const cell = tr.querySelector('.row-total-display');
    if (cell) cell.textContent = `₹ ${lineTotal.toFixed(2)}`;
  }

  selectedItemsTbody.addEventListener('input', (e) => {
    if (e.target.classList.contains('row-qty-input')) {
      const id = String(e.target.dataset.itemId);
      const val = parseFloat(e.target.value) || 1;
      if (selectedItemsMap.has(id)) {
        selectedItemsMap.get(id).quantity = val;
        updateRowTotal(e.target.closest('tr'), selectedItemsMap.get(id));
      }
      calcTotals();
    } else if (e.target.classList.contains('row-disc-input')) {
      const id = String(e.target.dataset.itemId);
      const val = Math.min(100, Math.max(0, parseFloat(e.target.value) || 0));
      if (selectedItemsMap.has(id)) {
        selectedItemsMap.get(id).discount_percent = val;
        const tr = e.target.closest('tr');
        updateRowTotal(tr, selectedItemsMap.get(id));
        updateRowRate(tr, selectedItemsMap.get(id));
      }
      calcTotals();
    }
  });

  selectedItemsTbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-remove-item');
    if (btn) {
      const id = String(btn.dataset.itemId);
      selectedItemsMap.delete(id);
      updateVisibleCheckboxStates();
      renderSelectedItemsTable();
    }
  });

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

  btnConvertToInvoice.addEventListener('click', async () => {
    if (selectedItemsMap.size === 0) {
      alert('Please select at least one item.');
      return;
    }

    if (confirm('Would you like to save this Estimate first and then convert to a Sales Record?')) {
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

  function initDatePickerIfNeeded() {
    const dateInput = document.getElementById('metaEstimateDate');
    if (dateInput && typeof flatpickr !== 'undefined' && !dateInput._flatpickr) {
      const formatSetting = (dateInput.dataset.dateFormat || 'DD/MM/YYYY').toUpperCase();
      let fpFormat = 'd/m/Y';
      if (formatSetting === 'YYYY-MM-DD') fpFormat = 'Y-m-d';
      else if (formatSetting === 'DD-MM-YYYY') fpFormat = 'd-m-Y';
      else if (formatSetting === 'DD/MM/YYYY') fpFormat = 'd/m/Y';

      dateInput.setAttribute('maxlength', '10');
      dateInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9\/\-\.]/g, '').slice(0, 10);
      });

      flatpickr(dateInput, {
        dateFormat: fpFormat,
        allowInput: true,
        monthSelectorType: 'static',
        disableMobile: true,
        onClose: function(selectedDates, dateStr, instance) {
          instance.element.dispatchEvent(new Event('change', { bubbles: true }));
          instance.element.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    }
  }

  const partiesDataEl = document.getElementById('partiesData');
  let partiesList = [];
  try {
    partiesList = JSON.parse(partiesDataEl?.textContent || '[]');
  } catch (err) {
    partiesList = [];
  }

  const metaCustomerNameInput = document.getElementById('metaCustomerName');
  const customerSearchResults = document.getElementById('customerSearchResults');
  const btnClearCustomerName = document.getElementById('btnClearCustomerName');

  function getPartyMatches(rawQuery) {
    const q = (rawQuery || '').trim().toLowerCase();
    if (!q) return partiesList;
    return partiesList.filter(p => {
      const name = (p.name || '').toLowerCase();
      const phone = (p.phone || '').toLowerCase();
      const gstin = (p.gstin || '').toLowerCase();
      return name.includes(q) || phone.includes(q) || gstin.includes(q);
    });
  }

  function renderCustomerResults(matches) {
    if (!customerSearchResults) return;
    customerSearchResults.innerHTML = '';
    const query = metaCustomerNameInput ? metaCustomerNameInput.value.trim() : '';

    if (matches.length === 0) {
      const emptyItem = document.createElement('div');
      emptyItem.className = 'px-3 py-2.5 text-xs text-[#78746C] text-center';
      emptyItem.textContent = query ? `No existing party found matching "${query}".` : 'No registered parties found.';
      customerSearchResults.appendChild(emptyItem);

      if (query) {
        const useBtn = document.createElement('button');
        useBtn.type = 'button';
        useBtn.className = 'w-full px-3 py-2.5 text-left text-xs font-semibold text-[#2B526E] bg-[#FAF9F6] hover:bg-[#F3F1EC] border-t border-[#E6E4DF] cursor-pointer';
        useBtn.innerHTML = `<i class="bi bi-check-circle mr-1"></i> Use "<strong>${escapeHtml(query)}</strong>" as customer name`;
        useBtn.addEventListener('click', () => {
          customerSearchResults.classList.add('hidden');
          if (btnClearCustomerName) btnClearCustomerName.style.display = 'block';
        });
        customerSearchResults.appendChild(useBtn);
      }
      customerSearchResults.classList.remove('hidden');
      return;
    }

    matches.slice(0, 15).forEach(p => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'w-full px-3.5 py-2.5 text-left hover:bg-[#FAF9F6] border-b border-[#F0EEE9] last:border-b-0 transition-colors cursor-pointer bg-white';
      btn.innerHTML = `
        <div class="flex justify-between items-center gap-2">
          <strong class="text-xs font-semibold text-[#1C1B18]">${escapeHtml(p.name)}</strong>
          ${p.phone ? `<span class="rf-badge rf-badge-neutral font-mono text-[10px]"><i class="bi bi-telephone"></i>${escapeHtml(p.phone)}</span>` : ''}
        </div>
        ${(p.gstin || p.state || p.address) ? `
          <div class="text-[11px] text-[#78746C] flex justify-between mt-1 gap-2">
            <span>${p.gstin ? `<span class="text-[#2B526E] font-mono">GSTIN: ${escapeHtml(p.gstin)}</span>` : escapeHtml(p.address || '')}</span>
            ${p.state ? `<span class="text-[#5C5A54]">${escapeHtml(p.state)}</span>` : ''}
          </div>
        ` : ''}
      `;

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        metaCustomerNameInput.value = p.name;
        customerSearchResults.classList.add('hidden');
        if (btnClearCustomerName) btnClearCustomerName.style.display = 'block';
      });

      customerSearchResults.appendChild(btn);
    });

    customerSearchResults.classList.remove('hidden');
  }

  if (metaCustomerNameInput) {
    metaCustomerNameInput.addEventListener('focus', () => {
      renderCustomerResults(getPartyMatches(metaCustomerNameInput.value));
    });

    metaCustomerNameInput.addEventListener('input', () => {
      const val = metaCustomerNameInput.value;
      if (btnClearCustomerName) btnClearCustomerName.style.display = val.trim() ? 'block' : 'none';
      renderCustomerResults(getPartyMatches(val));
    });

    metaCustomerNameInput.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && customerSearchResults) {
        customerSearchResults.classList.add('hidden');
      }
    });
  }

  if (btnClearCustomerName) {
    btnClearCustomerName.addEventListener('click', () => {
      if (metaCustomerNameInput) {
        metaCustomerNameInput.value = '';
        metaCustomerNameInput.focus();
      }
      btnClearCustomerName.style.display = 'none';
      if (customerSearchResults) customerSearchResults.classList.add('hidden');
    });
  }

  document.addEventListener('click', (e) => {
    if (customerSearchResults && !customerSearchResults.contains(e.target) &&
        metaCustomerNameInput && !metaCustomerNameInput.contains(e.target) &&
        btnClearCustomerName && !btnClearCustomerName.contains(e.target)) {
      customerSearchResults.classList.add('hidden');
    }
  });

  initDatePickerIfNeeded();
  syncModeUI();
  filterItemsList();
});
</script>
