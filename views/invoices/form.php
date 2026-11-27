<?php
$isEditMode = !empty($isEdit) && !empty($invoice);
$currentSettings = !empty($settings) ? $settings : ["sales" => ["default_gst_type" => "gst", "gst_calc_mode" => "separate", "enable_discount_column" => true, "default_due_days" => 0]];
$billSettings = (($invoiceType ?? "sale") === "purchase" && !empty($currentSettings["purchases"])) ? $currentSettings["purchases"] : ($currentSettings["sales"] ?? []);
$isGstDefault = $isEditMode ? (((int)($invoice["is_gst_bill"] ?? 0)) === 1) : (($billSettings["default_gst_type"] ?? "") !== "non_gst");
$isSeparateGst = ($billSettings["gst_calc_mode"] ?? "") !== "final_amount";
$showDiscountCol = ($billSettings["enable_discount_column"] ?? true) !== false;

$defaultDueDate = $isEditMode ? ($invoice["due_date"] ?? "") : "";
if (!$isEditMode && !empty($billSettings["default_due_days"]) && (int)$billSettings["default_due_days"] > 0) {
    $defaultDueDate = date("Y-m-d", strtotime("+" . (int)$billSettings["default_due_days"] . " days"));
}
$activeDateFormat = $currentSettings['general']['date_format'] ?? ($firmSettings['general']['date_format'] ?? 'DD/MM/YYYY');
$formattedInvoiceDate = formatDate($isEditMode ? $invoice['invoice_date'] : $today, $activeDateFormat);
$formattedDueDate = !empty($defaultDueDate) ? formatDate($defaultDueDate, $activeDateFormat) : '';
$existingFinalTaxRate = ($isEditMode && !empty($invoice['taxable_amount']) && (float)$invoice['taxable_amount'] > 0)
    ? (int)round(((float)($invoice['tax_amount'] ?? 0) / (float)$invoice['taxable_amount']) * 100)
    : 18;
?>

<form action="<?= $isEditMode ? ('/invoices/edit/' . $invoice['id']) : '/invoices/create' ?>" method="POST" id="invoiceForm"
  class="space-y-6"
  data-firm-id="<?= htmlspecialchars((string)($activeFirm['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
  data-is-edit="<?= $isEditMode ? '1' : '0' ?>"
  data-firm-state-code="<?= htmlspecialchars((string)($activeFirm['state_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
  data-default-gst="<?= $isEditMode ? (!empty($invoice['is_gst_bill']) ? 'gst' : 'non_gst') : htmlspecialchars($billSettings['default_gst_type'] ?? 'gst', ENT_QUOTES, 'UTF-8') ?>"
  data-gst-calc-mode="<?= htmlspecialchars($billSettings['gst_calc_mode'] ?? 'separate', ENT_QUOTES, 'UTF-8') ?>"
  data-enable-discount="<?= $showDiscountCol ? '1' : '0' ?>">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="type" value="<?= htmlspecialchars($invoiceType ?? 'sale', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="gst_calc_mode" id="gstCalcModeInput" value="<?= htmlspecialchars($billSettings['gst_calc_mode'] ?? 'separate', ENT_QUOTES, 'UTF-8') ?>">

  <!-- Draft Recovery Banner -->
  <div id="draftRecoveryBanner" class="rf-alert rf-alert-warning hidden justify-between flex-wrap items-center gap-3 mb-0" role="alert">
    <div class="flex items-center gap-3">
      <i class="bi bi-clock-history text-xl text-[#8A5A00] shrink-0"></i>
      <div>
        <div class="font-semibold text-[#1C1B18] text-sm">Unsaved Bill Draft Found</div>
        <div class="text-xs text-[#5C5A54]" id="draftDetailsText">You have an unsaved draft from a previous session.</div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button type="button" class="rf-btn rf-btn-amber rf-btn-sm" id="btnRestoreDraft">
        <i class="bi bi-arrow-counterclockwise"></i> Restore Draft
      </button>
      <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" id="btnDiscardDraft">Discard</button>
    </div>
  </div>

  <!-- Top Action Header -->
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="flex flex-wrap items-center gap-2.5">
        <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
          <i class="bi <?= $isEditMode ? 'bi-pencil-square text-[#8A5A00]' : ($invoiceType === 'sale' ? 'bi-cart-check text-[#2E5A36]' : 'bi-bag-plus text-[#2B526E]') ?>"></i>
          <span><?= $isEditMode ? ($invoiceType === 'sale' ? 'Edit Sales Record' : 'Edit Purchase Record') : ($invoiceType === 'sale' ? 'Create Sales Record' : 'Create Purchase Record') ?></span>
        </h1>
        <span id="draftSaveIndicator" class="rf-badge rf-badge-neutral hidden">
          <i class="bi bi-cloud-check text-[#2E5A36]"></i> <span id="draftSaveTime">Draft saved</span>
        </span>
      </div>
      <div class="mt-1.5">
        <span class="rf-badge <?= $isGstDefault ? 'rf-badge-slate' : 'rf-badge-neutral' ?>" id="gstModeBadge">
          <?= $isGstDefault ? ($isSeparateGst ? 'GST Sales Record (Separate Item GST)' : 'GST Sales Record (Final Amount GST)') : 'Non-GST Business Record' ?>
        </span>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="/settings?tab=<?= $invoiceType === 'sale' ? 'sales' : 'purchases' ?>" class="rf-btn rf-btn-ghost rf-btn-sm" title="Record Settings">
        <i class="bi bi-gear"></i> Settings
      </a>
      <a href="<?= $isEditMode ? ('/invoices/view/' . $invoice['id']) : ($invoiceType === 'sale' ? '/sales' : '/purchases') ?>" class="rf-btn rf-btn-secondary rf-btn-sm">Cancel</a>
      <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
        <i class="bi bi-check2-circle"></i> <?= $isEditMode ? 'Update Record' : 'Save Record' ?>
      </button>
    </div>
  </div>

  <!-- Row 1: Record Metadata & Tax Toggles -->
  <div class="rf-card p-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
      <div class="lg:col-span-3">
        <label class="rf-label-plain text-xs" for="invoiceNumberInput">Record Number <span class="text-[#8F2D2B]">*</span></label>
        <input type="text" class="rf-input rf-input-sm font-mono font-semibold" id="invoiceNumberInput" name="invoice_number" value="<?= $isEditMode ? $invoice['invoice_number'] : $nextInvoiceNumber ?>" pattern="[0-9]+" inputmode="numeric" required title="Record number must contain only numbers" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="e.g. 101">
      </div>

      <div class="lg:col-span-3">
        <label class="rf-label-plain text-xs" for="invoiceDateInput">Record Date <span class="text-[#8F2D2B]">*</span></label>
        <div class="relative">
          <input type="text" class="rf-input rf-input-sm pr-9 date-picker" id="invoiceDateInput" name="invoice_date" value="<?= htmlspecialchars($formattedInvoiceDate, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" data-date-format="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" maxlength="10" oninput="this.value = this.value.replace(/[^0-9\/\-\.]/g, '').slice(0, 10);" required autocomplete="off">
          <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#78746C] bg-transparent border-0 cursor-pointer p-0" onclick="const el = document.getElementById('invoiceDateInput'); if (el._flatpickr) { el._flatpickr.open(); } else { el.focus(); }"><i class="bi bi-calendar3 text-xs"></i></button>
        </div>
      </div>

      <div class="lg:col-span-2">
        <label class="rf-label-plain text-xs" for="dueDateInput">Due Date</label>
        <div class="relative">
          <input type="text" class="rf-input rf-input-sm pr-9 date-picker" id="dueDateInput" name="due_date" value="<?= htmlspecialchars($formattedDueDate, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" data-date-format="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" maxlength="10" oninput="this.value = this.value.replace(/[^0-9\/\-\.]/g, '').slice(0, 10);" autocomplete="off">
          <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#78746C] bg-transparent border-0 cursor-pointer p-0" onclick="const el = document.getElementById('dueDateInput'); if (el._flatpickr) { el._flatpickr.open(); } else { el.focus(); }"><i class="bi bi-calendar3 text-xs"></i></button>
        </div>
      </div>

      <div class="lg:col-span-4 space-y-1.5">
        <div class="text-xs font-medium text-[#78746C]">Tax &amp; GST Breakdown</div>
        <div class="flex flex-wrap items-center gap-5 pt-1">
          <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-[#1C1B18]">
            <span class="rf-switch">
              <input type="checkbox" id="isGstToggle" name="is_gst_bill" value="1" <?= $isGstDefault ? 'checked' : '' ?>>
              <span class="rf-switch-thumb"></span>
            </span>
            <span>Calculate GST</span>
          </label>
          <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-[#5C5A54]">
            <span class="rf-switch">
              <input type="checkbox" id="isInterstateToggle" name="is_interstate" value="1" <?= $isEditMode && $invoice['is_interstate'] === 1 ? 'checked' : '' ?>>
              <span class="rf-switch-thumb"></span>
            </span>
            <span>Inter-State (IGST)</span>
          </label>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: Party Details -->
  <div class="rf-card">
    <div class="rf-card-header py-3">
      <span class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54] flex items-center gap-1.5">
        <i class="bi bi-person"></i> <?= $invoiceType === 'sale' ? 'Bill To (Customer Details)' : 'Bill From (Supplier Details)' ?>
      </span>
      <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5" data-ui-modal="#modalQuickAddParty" id="btnOpenAddPartyModal">
        <i class="bi bi-person-plus"></i> Add New Party
      </button>
    </div>

    <div class="p-5 grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-4">
        <label class="rf-label-plain text-xs">Select Existing Party</label>
        <div class="relative">
          <div class="relative flex items-center">
            <i class="bi bi-search absolute left-3 text-[#87847C] text-xs pointer-events-none"></i>
            <input type="text" class="rf-input rf-input-sm pl-8 pr-8" id="partySearchInput" placeholder="Type name, phone or GSTIN..." autocomplete="off" value="<?= $isEditMode ? htmlspecialchars(($invoice['party_name'] ?? '') . (!empty($invoice['party_phone']) ? ' (' . $invoice['party_phone'] . ')' : ''), ENT_QUOTES, 'UTF-8') : '' ?>">
            <button class="absolute right-2 text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" type="button" id="btnClearPartySearch" title="Clear selection" style="<?= ($isEditMode && !empty($invoice['party_id'])) ? 'display: block;' : 'display: none;' ?>">
              <i class="bi bi-x-lg text-xs"></i>
            </button>
          </div>
          <div id="partySearchResults" class="hidden absolute left-0 right-0 mt-1 max-h-64 overflow-y-auto z-50 bg-white border border-[#E2DFD7] rounded-xl shadow-lg"></div>
        </div>

        <select class="hidden" id="partySelect" name="party_id">
          <option value="">-- Choose Party or Enter Manually --</option>
          <?php foreach (($parties ?? []) as $p): ?>
            <option value="<?= $p['id'] ?>"
              data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
              data-phone="<?= htmlspecialchars($p['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              data-gstin="<?= htmlspecialchars($p['gstin'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              data-address="<?= htmlspecialchars($p['billing_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              data-state="<?= htmlspecialchars($p['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              data-state-code="<?= htmlspecialchars($p['state_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              <?= ($isEditMode && ($invoice['party_id'] ?? null) == $p['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?> <?= !empty($p['phone']) ? '(' . htmlspecialchars($p['phone'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="md:col-span-4">
        <label class="rf-label-plain text-xs" for="partyNameInput">Party / Customer Name <span class="text-[#8F2D2B]">*</span></label>
        <input type="text" class="rf-input rf-input-sm" id="partyNameInput" name="party_name" placeholder="Enter party name" value="<?= $isEditMode ? htmlspecialchars($invoice['party_name'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" required>
      </div>

      <div class="md:col-span-4">
        <label class="rf-label-plain text-xs" for="partyPhoneInput">Phone / Mobile (10 Digits)</label>
        <input type="tel" class="rf-input rf-input-sm font-mono" id="partyPhoneInput" name="party_phone" placeholder="10-digit mobile number" maxlength="10" minlength="10" pattern="[0-9]{10}" inputmode="numeric" value="<?= $isEditMode ? htmlspecialchars($invoice['party_phone'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        <div id="phoneValidationStatus" class="text-[11px] text-[#78746C] mt-1">Exact 10 digits required</div>
      </div>

      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs" for="partyGstinInput">Party GSTIN (15 Chars)</label>
        <input type="text" class="rf-input rf-input-sm font-mono uppercase" id="partyGstinInput" name="party_gstin" placeholder="15-character GSTIN" maxlength="15" minlength="15" pattern="[0-9A-Z]{15}" value="<?= $isEditMode ? htmlspecialchars($invoice['party_gstin'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" oninput="this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '')">
        <div id="gstinValidationStatus" class="text-[11px] text-[#78746C] mt-1">Exact 15 letters &amp; numbers</div>
      </div>

      <div class="md:col-span-5">
        <label class="rf-label-plain text-xs" for="partyAddressInput">Billing Address</label>
        <input type="text" class="rf-input rf-input-sm" id="partyAddressInput" name="party_address" placeholder="Building, Street, Area..." value="<?= $isEditMode ? htmlspecialchars($invoice['party_address'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>">
      </div>

      <div class="md:col-span-3">
        <label class="rf-label-plain text-xs" for="partyStateSelect">State / UT <span class="text-[#8F2D2B]">*</span></label>
        <select class="rf-select rf-select-sm" id="partyStateSelect" name="party_state" required>
          <option value="">-- Select State --</option>
          <?php foreach (($gstStates ?? []) as $st): ?>
            <?php
              $isSelected = ($isEditMode && (($invoice['party_state'] ?? '') === $st['name'] || ($invoice['party_state_code'] ?? '') === $st['code'])) ||
                            (!$isEditMode && !empty($activeFirm) && (($activeFirm['state'] ?? '') === $st['name'] || ($activeFirm['state_code'] ?? '') === $st['code']));
            ?>
            <option value="<?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($st['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $isSelected ? 'selected' : '' ?>>
              <?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="md:col-span-1">
        <label class="rf-label-plain text-xs" for="partyStateCodeInput">Code</label>
        <input type="text" class="rf-input rf-input-sm font-mono bg-[#F3F1EC] text-center font-semibold" id="partyStateCodeInput" name="party_state_code" placeholder="00" value="<?= $isEditMode ? htmlspecialchars($invoice['party_state_code'] ?? '', ENT_QUOTES, 'UTF-8') : htmlspecialchars($activeFirm['state_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly title="State code is auto-populated">
      </div>
    </div>
  </div>

  <!-- Row 3: Line Items Table -->
  <div class="rf-card">
    <div class="rf-card-header py-3">
      <span class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54] flex items-center gap-1.5">
        <i class="bi bi-box-seam"></i> Bill Line Items
      </span>
      <div class="flex items-center gap-2">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5" data-ui-modal="#modalQuickAddItem" id="btnOpenAddItemModal">
          <i class="bi bi-plus-circle"></i> New Item
        </button>
        <button type="button" class="rf-btn rf-btn-primary rf-btn-sm py-1 px-2.5" id="btnAddItem">
          <i class="bi bi-plus-lg"></i> Add Row
        </button>
      </div>
    </div>

    <div class="p-3">
      <div class="invoice-table-wrap">
        <table class="invoice-items-table" id="invoiceItemsTable">
          <thead>
            <tr>
              <th class="text-center" style="width: 28px;">#</th>
              <th>Item Name / Description</th>
              <th style="width: 85px;">HSN/SAC</th>
              <th style="width: 82px;" class="text-right">Qty</th>
              <th style="width: 78px;">Unit</th>
              <th style="width: 95px;" class="text-right">Rate (₹)</th>
              <th style="width: 72px;" class="text-right discount-col <?= !$showDiscountCol ? 'hidden' : '' ?>">Disc %</th>
              <th style="width: 96px;" class="gst-col <?= (!$isGstDefault || !$isSeparateGst) ? 'hidden' : '' ?>">Tax %</th>
              <th style="width: 105px;" class="text-right">Amount (₹)</th>
              <th style="width: 32px;" class="text-center"></th>
            </tr>
          </thead>
          <tbody id="invoiceItemsBody">
            <!-- Dynamic rows inserted via invoice-calc.js -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="preloadedInvoiceItems" data-items="<?= htmlspecialchars(json_encode(!empty($invoice['items']) ? $invoice['items'] : []), ENT_QUOTES, 'UTF-8') ?>" class="hidden"></div>
  <div id="unitOptionsData" data-units="<?= htmlspecialchars(json_encode($allUnits ?? []), ENT_QUOTES, 'UTF-8') ?>" class="hidden"></div>

  <!-- Row 4: Payment & Totals -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <div class="lg:col-span-6 rf-card p-5 space-y-5">
      <div>
        <div class="rf-label-plain text-xs">Payment Mode</div>
        <div class="flex flex-wrap gap-4 pt-1 text-xs text-[#1C1B18]">
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input class="rf-radio" type="radio" name="payment_mode" id="modeCash" value="cash" <?= !$isEditMode || $invoice['payment_mode'] === 'cash' ? 'checked' : '' ?>>
            <span>Cash</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input class="rf-radio" type="radio" name="payment_mode" id="modeBank" value="bank" <?= $isEditMode && $invoice['payment_mode'] === 'bank' ? 'checked' : '' ?>>
            <span>Bank Transfer</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input class="rf-radio" type="radio" name="payment_mode" id="modeUpi" value="upi" <?= $isEditMode && $invoice['payment_mode'] === 'upi' ? 'checked' : '' ?>>
            <span>UPI</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input class="rf-radio" type="radio" name="payment_mode" id="modeCheque" value="cheque" <?= $isEditMode && $invoice['payment_mode'] === 'cheque' ? 'checked' : '' ?>>
            <span>Cheque</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input class="rf-radio" type="radio" name="payment_mode" id="modeCredit" value="credit" <?= $isEditMode && $invoice['payment_mode'] === 'credit' ? 'checked' : '' ?>>
            <span>Credit (Unpaid)</span>
          </label>
        </div>
      </div>

      <div>
        <label for="notes" class="rf-label-plain text-xs">Private Notes / Remarks</label>
        <textarea class="rf-textarea text-xs" id="notes" name="notes" rows="2" placeholder="Optional notes for your reference..."><?= $isEditMode ? htmlspecialchars($invoice['notes'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?></textarea>
      </div>

      <div>
        <label for="terms" class="rf-label-plain text-xs">Invoice Terms &amp; Conditions</label>
        <textarea class="rf-textarea text-xs" id="terms" name="terms" rows="2"><?= $isEditMode ? htmlspecialchars($invoice['terms'] ?? ($activeFirm['terms'] ?? ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars(!empty($activeFirm['terms']) ? $activeFirm['terms'] : '1. Goods once sold will not be taken back. 2. Subject to local jurisdiction.', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
    </div>

    <div class="lg:col-span-6 rf-card p-5 space-y-4">
      <div class="flex justify-between items-center text-sm">
        <span class="text-xs text-[#5C5A54]">Taxable Subtotal</span>
        <span class="font-semibold tabular-nums" id="displaySubtotal">₹ 0.00</span>
        <input type="hidden" name="subtotal" id="subtotalInput" value="<?= $isEditMode ? $invoice['subtotal'] : '0' ?>">
        <input type="hidden" name="taxable_amount" id="taxableAmountInput" value="<?= $isEditMode ? $invoice['taxable_amount'] : '0' ?>">
      </div>

      <div class="flex justify-between items-center gap-3">
        <span class="text-xs text-[#5C5A54] shrink-0">Discount</span>
        <div class="flex items-center gap-2 max-w-[270px] w-full">
          <select name="discount_type" id="discountTypeSelect" class="rf-select rf-select-sm w-36">
            <option value="percentage" <?= $isEditMode && $invoice['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
            <option value="flat" <?= $isEditMode && $invoice['discount_type'] === 'flat' ? 'selected' : '' ?>>Flat Amount (₹)</option>
          </select>
          <input type="number" name="discount_value" id="discountValueInput" class="rf-input rf-input-sm text-right font-semibold tabular-nums flex-1" min="0" max="<?= ($isEditMode && $invoice['discount_type'] === 'flat') ? '' : '100' ?>" step="any" placeholder="0.00" value="<?= $isEditMode ? $invoice['discount_value'] : '0' ?>">
          <input type="hidden" name="discount_amount" id="discountAmountInput" value="<?= $isEditMode ? $invoice['discount_amount'] : '0' ?>">
        </div>
      </div>

      <div id="gstSummarySection" class="space-y-2">
        <div class="grid grid-cols-2 gap-3 items-center <?= $isSeparateGst ? 'hidden' : '' ?>" id="finalGstRateRow">
          <span class="text-xs font-medium text-[#5C5A54]">GST Rate on Final Amount</span>
          <div>
            <?php $isStandardFinalTax = in_array((int)$existingFinalTaxRate, [0, 5, 12, 18, 28], true); ?>
            <select id="finalTaxRateSelect" class="rf-select rf-select-sm">
              <option value="0" <?= ($isStandardFinalTax && $existingFinalTaxRate === 0) ? 'selected' : '' ?>>0% (Tax Exempt)</option>
              <option value="5" <?= ($isStandardFinalTax && $existingFinalTaxRate === 5) ? 'selected' : '' ?>>5% GST</option>
              <option value="12" <?= ($isStandardFinalTax && $existingFinalTaxRate === 12) ? 'selected' : '' ?>>12% GST</option>
              <option value="18" <?= ($isStandardFinalTax && $existingFinalTaxRate === 18) ? 'selected' : '' ?>>18% GST</option>
              <option value="28" <?= ($isStandardFinalTax && $existingFinalTaxRate === 28) ? 'selected' : '' ?>>28% GST</option>
              <option value="custom" <?= !$isStandardFinalTax ? 'selected' : '' ?>>Custom Tax %</option>
            </select>
            <div id="finalCustomTaxContainer" class="mt-1.5 <?= !$isStandardFinalTax ? '' : 'hidden' ?>">
              <div class="flex items-center gap-1.5">
                <input type="number" id="finalTaxRateCustom" class="rf-input rf-input-sm text-right tabular-nums" min="1" max="100" step="any" placeholder="1-100" value="<?= !$isStandardFinalTax ? $existingFinalTaxRate : '' ?>" onkeydown="if(['-','+','e','E'].includes(event.key)) event.preventDefault();">
                <span class="text-xs text-[#5C5A54]">%</span>
              </div>
            </div>
            <input type="hidden" name="final_tax_rate" id="finalTaxRateInput" value="<?= $existingFinalTaxRate ?>">
          </div>
        </div>

        <div class="flex justify-between items-center text-xs text-[#5C5A54]" id="cgstSgstSummaryRow">
          <span>CGST + SGST</span>
          <span class="font-medium tabular-nums" id="displayTax">₹ 0.00</span>
          <input type="hidden" name="cgst_amount" id="cgstAmountInput" value="<?= $isEditMode ? $invoice['cgst_amount'] : '0' ?>">
          <input type="hidden" name="sgst_amount" id="sgstAmountInput" value="<?= $isEditMode ? $invoice['sgst_amount'] : '0' ?>">
        </div>
        <div class="flex justify-between items-center text-xs text-[#5C5A54] hidden" id="igstSummaryRow">
          <span>IGST (Integrated Tax)</span>
          <span class="font-medium tabular-nums">₹ 0.00</span>
          <input type="hidden" name="igst_amount" id="igstAmountInput" value="<?= $isEditMode ? $invoice['igst_amount'] : '0' ?>">
        </div>
        <input type="hidden" name="tax_amount" id="taxAmountInput" value="<?= $isEditMode ? $invoice['tax_amount'] : '0' ?>">
      </div>

      <div class="flex justify-between items-center text-xs text-[#78746C]">
        <span>Round Off</span>
        <input type="hidden" name="round_off" id="roundOffInput" value="<?= $isEditMode ? $invoice['round_off'] : '0' ?>">
        <span class="tabular-nums">₹ 0.00</span>
      </div>

      <div class="pt-3 border-t border-[#E6E4DF] flex justify-between items-center">
        <span class="text-base font-semibold text-[#1C1B18]">Grand Total</span>
        <span class="text-2xl font-semibold text-[#1C1B18] tabular-nums" id="displayGrandTotal">₹ 0.00</span>
        <input type="hidden" name="grand_total" id="grandTotalInput" value="<?= $isEditMode ? $invoice['grand_total'] : '0' ?>">
      </div>

      <div class="rf-card-muted p-4 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
          <label for="paidAmountInput" class="sm:col-span-5 text-xs font-semibold text-[#2E5A36]">Amount Received / Paid</label>
          <div class="sm:col-span-4">
            <input type="number" name="paid_amount" id="paidAmountInput" class="rf-input rf-input-sm text-right font-semibold tabular-nums" step="any" min="0" placeholder="0.00" value="<?= $isEditMode ? $invoice['paid_amount'] : '0' ?>">
          </div>
          <div class="sm:col-span-3 flex gap-1.5">
            <button type="button" class="rf-btn rf-btn-sage rf-btn-sm flex-1 py-1 px-2" id="btnPayFull" title="Full Payment">Full</button>
            <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm flex-1 py-1 px-2" id="btnPayZero" title="Unpaid / Credit">Zero</button>
          </div>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-xs font-semibold text-[#8F2D2B]">Balance Due</span>
          <span class="text-sm font-semibold text-[#8F2D2B] tabular-nums" id="displayBalanceDue">₹ 0.00</span>
          <input type="hidden" name="balance_due" id="balanceDueInput" value="<?= $isEditMode ? $invoice['balance_due'] : '0' ?>">
        </div>
      </div>

      <button type="submit" class="rf-btn rf-btn-primary w-full py-3">
        <i class="bi bi-check2-circle"></i> <?= $isEditMode ? ('Update ' . ($invoiceType === 'sale' ? 'Sales Record' : 'Purchase Record')) : ('Save ' . ($invoiceType === 'sale' ? 'Sales Record' : 'Purchase Record')) ?>
      </button>
    </div>
  </div>
</form>

<!-- Datalist for Items Autocomplete -->
<datalist id="itemsDataList">
  <?php foreach (($items ?? []) as $it): ?>
    <option value="<?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>"
      data-id="<?= $it['id'] ?>"
      data-hsn="<?= htmlspecialchars($it['hsn_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
      data-unit="<?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
      data-price="<?= ($invoiceType === 'purchase') ? (!empty($it['purchase_price']) ? $it['purchase_price'] : $it['sale_price']) : $it['sale_price'] ?>"
      data-tax="<?= htmlspecialchars((string)($it['tax_rate'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?> (Stock: <?= htmlspecialchars((string)($it['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($it['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?> | ₹<?= htmlspecialchars((string)($it['sale_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>)
    </option>
  <?php endforeach; ?>
</datalist>

<!-- Modal: Quick Add Party -->
<div class="rf-modal hidden" id="modalQuickAddParty" tabindex="-1" aria-labelledby="modalQuickAddPartyLabel" aria-hidden="true">
  <div class="rf-modal-dialog rf-modal-dialog-lg">
    <div class="rf-modal-header">
      <h3 class="rf-modal-title" id="modalQuickAddPartyLabel">
        <i class="bi bi-person-plus text-[#2B526E]"></i> Quick Create <?= $invoiceType === 'sale' ? 'Customer' : 'Supplier' ?> Party
      </h3>
      <button type="button" class="text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" data-ui-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="quickPartyForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="type" value="<?= $invoiceType === 'sale' ? 'customer' : 'supplier' ?>">
      <div class="rf-modal-body space-y-4">
        <div id="quickPartyAlert" class="rf-alert rf-alert-danger hidden text-xs mb-0"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="rf-label-plain text-xs">Party / Business Name <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" class="rf-input rf-input-sm" name="name" id="quickPartyName" placeholder="e.g. Ramesh Trading Co." required>
          </div>
          <div>
            <label class="rf-label-plain text-xs">Phone Number (10 Digits)</label>
            <input type="tel" class="rf-input rf-input-sm font-mono" name="phone" id="quickPartyPhone" placeholder="10-digit mobile number" maxlength="10" minlength="10" pattern="[0-9]{10}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <p class="rf-help">Exact 10 digits without country code</p>
          </div>
          <div>
            <label class="rf-label-plain text-xs">GSTIN (15 Chars)</label>
            <input type="text" class="rf-input rf-input-sm font-mono uppercase" name="gstin" id="quickPartyGstin" placeholder="15-character GSTIN" maxlength="15" minlength="15" oninput="this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '')">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Email Address</label>
            <input type="email" class="rf-input rf-input-sm" name="email" id="quickPartyEmail" placeholder="party@example.com">
          </div>
          <div class="md:col-span-2">
            <label class="rf-label-plain text-xs">Billing Address</label>
            <input type="text" class="rf-input rf-input-sm" name="billing_address" id="quickPartyAddress" placeholder="Building, Street, Area...">
          </div>
          <div class="grid grid-cols-12 gap-3 md:col-span-2">
            <div class="col-span-5">
              <label class="rf-label-plain text-xs">State / UT <span class="text-[#8F2D2B]">*</span></label>
              <select class="rf-select rf-select-sm" name="state" id="quickPartyState" required>
                <option value="">-- Select State --</option>
                <?php foreach (($gstStates ?? []) as $st): ?>
                  <?php
                    $isSelected = ($isEditMode && (($invoice['party_state'] ?? '') === $st['name'] || ($invoice['party_state_code'] ?? '') === $st['code'])) ||
                                  (!$isEditMode && !empty($activeFirm) && (($activeFirm['state'] ?? '') === $st['name'] || ($activeFirm['state_code'] ?? '') === $st['code']));
                  ?>
                  <option value="<?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($st['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $isSelected ? 'selected' : '' ?>>
                    <?= htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-span-3">
              <label class="rf-label-plain text-xs">State Code</label>
              <input type="text" class="rf-input rf-input-sm bg-[#F3F1EC] text-center font-mono font-semibold" name="state_code" id="quickPartyStateCode" value="<?= htmlspecialchars($activeFirm['state_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="col-span-4">
              <label class="rf-label-plain text-xs">Opening Balance (₹)</label>
              <input type="number" class="rf-input rf-input-sm tabular-nums" name="opening_balance" id="quickPartyOpeningBalance" step="any" placeholder="0.00" value="0">
            </div>
          </div>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm" id="btnSubmitQuickParty">
          <i class="bi bi-arrow-repeat animate-spin hidden" id="quickPartySpinner"></i>
          <i class="bi bi-check-circle"></i> Save &amp; Select Party
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Quick Add Item -->
<div class="rf-modal hidden" id="modalQuickAddItem" tabindex="-1" aria-labelledby="modalQuickAddItemLabel" aria-hidden="true">
  <div class="rf-modal-dialog rf-modal-dialog-lg">
    <div class="rf-modal-header">
      <h3 class="rf-modal-title" id="modalQuickAddItemLabel">
        <i class="bi bi-box-seam text-[#2E5A36]"></i> Quick Create Inventory Item
      </h3>
      <button type="button" class="text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" data-ui-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="quickItemForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-modal-body space-y-4">
        <div id="quickItemAlert" class="rf-alert rf-alert-danger hidden text-xs mb-0"></div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-7">
            <label class="rf-label-plain text-xs">Item / Product Name <span class="text-[#8F2D2B]">*</span></label>
            <input type="text" class="rf-input rf-input-sm" name="name" id="quickItemName" placeholder="e.g. Cotton Shirt Blue - XL" required>
          </div>
          <div class="md:col-span-5">
            <label class="rf-label-plain text-xs">Item Code / Barcode</label>
            <input type="text" class="rf-input rf-input-sm font-mono" name="item_code" id="quickItemCode" placeholder="e.g. SKU-101">
          </div>

          <div class="md:col-span-6">
            <div class="flex justify-between items-center mb-1.5">
              <label class="text-xs font-medium text-[#2C2B27]">Category <span class="text-[#78746C] font-normal">(Optional)</span></label>
              <button type="button" class="text-[11px] font-medium text-[#2B526E] hover:underline bg-transparent border-0 p-0 cursor-pointer" id="btnToggleNewCategory">
                <i class="bi bi-plus-circle"></i> Add Category
              </button>
            </div>
            <div id="quickItemCategorySelectWrap">
              <select class="rf-select rf-select-sm" id="quickItemCategorySelect">
                <option value="">-- No Category --</option>
                <?php foreach (($categories ?? []) as $cat): ?>
                  <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
                <option value="__create_new__">+ Create New Category...</option>
              </select>
            </div>
            <div id="quickItemNewCategoryWrap" class="hidden">
              <div class="flex items-center gap-1.5">
                <input type="text" class="rf-input rf-input-sm" id="quickItemNewCategoryInput" placeholder="Enter new category name...">
                <button class="rf-btn rf-btn-secondary rf-btn-sm px-2.5" type="button" id="btnCancelNewCategory" title="Select from existing categories">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>
            <input type="hidden" name="category" id="quickItemCategory" value="">
          </div>

          <div class="md:col-span-6">
            <label class="rf-label-plain text-xs">HSN / SAC Code</label>
            <input type="text" class="rf-input rf-input-sm font-mono" name="hsn_code" id="quickItemHsn" placeholder="e.g. 6205" maxlength="20" oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-\.\/ ]/g, '').slice(0, 20);">
          </div>

          <div class="md:col-span-4">
            <div class="flex justify-between items-center mb-1.5">
              <label class="text-xs font-medium text-[#2C2B27]">Unit <span class="text-[#8F2D2B]">*</span></label>
              <button type="button" class="text-[11px] font-medium text-[#2B526E] hover:underline bg-transparent border-0 p-0 cursor-pointer" data-ui-modal="#modalAddCustomUnit">
                <i class="bi bi-plus-circle"></i> Add Unit
              </button>
            </div>
            <select class="rf-select rf-select-sm" name="unit" id="quickItemUnit" required>
              <?php foreach (($allUnits ?? []) as $u): ?>
                <option value="<?= htmlspecialchars(strtoupper($u['code']), ENT_QUOTES, 'UTF-8') ?>" <?= strtoupper($u['code']) === 'PCS' ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['label'] ?? $u['code'], ENT_QUOTES, 'UTF-8') ?><?= !empty($u['is_custom']) ? ' (Custom)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">Sub-Unit <span class="text-[#78746C] font-normal">(Pack)</span></label>
            <div class="flex gap-1.5">
              <input type="text" class="rf-input rf-input-sm uppercase" name="sub_unit" id="quickItemSubUnit" placeholder="PCS" maxlength="15">
              <input type="number" step="any" min="0.01" class="rf-input rf-input-sm w-20 text-right tabular-nums" name="sub_unit_qty" id="quickItemSubUnitQty" placeholder="Qty">
            </div>
          </div>

          <div class="md:col-span-4">
            <label class="rf-label-plain text-xs">Opening Stock Qty</label>
            <input type="number" class="rf-input rf-input-sm tabular-nums" name="opening_stock" id="quickItemOpeningStock" min="0" step="any" placeholder="0" value="0">
          </div>

          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs"><?= $invoiceType === 'purchase' ? 'Price (Excl. GST)' : 'Sale Price (Ex-GST)' ?> <span class="text-[#8F2D2B]">*</span></label>
            <input type="number" class="rf-input rf-input-sm text-right font-mono tabular-nums" name="sale_price" id="quickItemPrice" min="0" step="any" placeholder="0.00" required>
            <p class="text-[10px] text-[#78746C] mt-1">Without GST</p>
          </div>

          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs"><?= $invoiceType === 'purchase' ? 'Price (Incl. GST)' : 'Sale Price (Inc-GST)' ?></label>
            <input type="number" class="rf-input rf-input-sm text-right font-mono tabular-nums" name="sale_price_with_tax" id="quickItemPriceWithTax" min="0" step="any" placeholder="0.00">
            <p class="text-[10px] text-[#78746C] mt-1">With GST (MRP)</p>
          </div>

          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">GST Tax Rate (%)</label>
            <select class="rf-select rf-select-sm" name="tax_rate_select" id="quickItemTaxRate">
              <option value="0">0% (Nil / Exempt)</option>
              <option value="5">5% GST</option>
              <option value="12">12% GST</option>
              <option value="18" selected>18% GST</option>
              <option value="28">28% GST</option>
              <option value="custom">Custom Tax %</option>
            </select>
            <div id="quickItemCustomTaxContainer" class="mt-1.5 hidden">
              <div class="flex items-center gap-1">
                <input type="number" class="rf-input rf-input-sm text-right tabular-nums" id="quickItemCustomTax" placeholder="1-100" min="1" max="100" step="any" onkeydown="if(['-','+','e','E'].includes(event.key)) event.preventDefault();">
                <span class="text-xs text-[#5C5A54]">%</span>
              </div>
            </div>
            <input type="hidden" name="tax_rate" id="quickItemTaxRateHidden" value="18">
          </div>

          <div class="md:col-span-3">
            <label class="rf-label-plain text-xs">Description</label>
            <input type="text" class="rf-input rf-input-sm" name="description" id="quickItemDesc" placeholder="Optional details...">
          </div>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-sage-solid rf-btn-sm" id="btnSubmitQuickItem">
          <i class="bi bi-arrow-repeat animate-spin hidden" id="quickItemSpinner"></i>
          <i class="bi bi-check-circle"></i> Save &amp; Add to Record
        </button>
      </div>
    </form>
  </div>
</div>

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

<script src="/js/invoice-calc.js?v=6"></script>
