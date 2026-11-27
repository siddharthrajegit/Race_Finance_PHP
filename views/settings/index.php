<?php
$tab = $activeTab ?? 'sales';
$salesSettings = $settings['sales'] ?? [];
$purSettings = $settings['purchases'] ?? [];
$printSettings = $settings['print'] ?? [];
$genSettings = $settings['general'] ?? [];
?>
<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Settings</h1>
    <p class="text-sm text-[#5C5A54] mt-1">Configure bill defaults, tax calculation engines, and printing preferences for <strong class="text-[#1C1B18]"><?= htmlspecialchars($activeFirm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- Category Navigation -->
    <div class="lg:col-span-3 space-y-4">
      <div class="rf-card p-2 space-y-1" id="settingsTabs" role="tablist">
        <a class="flex items-center gap-3 px-3.5 py-3 rounded-lg no-underline transition-colors <?= ($tab === 'sales') ? 'bg-[#1C1B18] text-white' : 'text-[#1C1B18] hover:bg-[#F3F1EC]' ?>" href="/settings?tab=sales">
          <i class="bi bi-cart-check text-base <?= ($tab === 'sales') ? 'text-white' : 'text-[#2B526E]' ?>"></i>
          <div>
            <div class="text-sm font-medium">Sales Bill Settings</div>
            <div class="text-[11px] <?= ($tab === 'sales') ? 'text-white/70' : 'text-[#78746C]' ?>">GST, Calculation &amp; Discounts</div>
          </div>
        </a>

        <a class="flex items-center gap-3 px-3.5 py-3 rounded-lg no-underline transition-colors <?= ($tab === 'purchases') ? 'bg-[#1C1B18] text-white' : 'text-[#1C1B18] hover:bg-[#F3F1EC]' ?>" href="/settings?tab=purchases">
          <i class="bi bi-bag-plus text-base <?= ($tab === 'purchases') ? 'text-white' : 'text-[#2E5A36]' ?>"></i>
          <div>
            <div class="text-sm font-medium">Purchase Settings</div>
            <div class="text-[11px] <?= ($tab === 'purchases') ? 'text-white/70' : 'text-[#78746C]' ?>">Supplier Bill Defaults</div>
          </div>
        </a>

        <a class="flex items-center gap-3 px-3.5 py-3 rounded-lg no-underline transition-colors <?= ($tab === 'print') ? 'bg-[#1C1B18] text-white' : 'text-[#1C1B18] hover:bg-[#F3F1EC]' ?>" href="/settings?tab=print">
          <i class="bi bi-printer text-base <?= ($tab === 'print') ? 'text-white' : 'text-[#5C5A54]' ?>"></i>
          <div>
            <div class="text-sm font-medium">Print &amp; A4 Invoice</div>
            <div class="text-[11px] <?= ($tab === 'print') ? 'text-white/70' : 'text-[#78746C]' ?>">Bank, QR &amp; Signatures</div>
          </div>
        </a>

        <a class="flex items-center gap-3 px-3.5 py-3 rounded-lg no-underline transition-colors <?= ($tab === 'general') ? 'bg-[#1C1B18] text-white' : 'text-[#1C1B18] hover:bg-[#F3F1EC]' ?>" href="/settings?tab=general">
          <i class="bi bi-sliders text-base <?= ($tab === 'general') ? 'text-white' : 'text-[#8A5A00]' ?>"></i>
          <div>
            <div class="text-sm font-medium">General Defaults</div>
            <div class="text-[11px] <?= ($tab === 'general') ? 'text-white/70' : 'text-[#78746C]' ?>">Currency &amp; Regional</div>
          </div>
        </a>
      </div>

      <div class="rf-card-muted p-4 space-y-1.5">
        <div class="text-[10px] font-semibold uppercase tracking-wider text-[#78746C]">Settings Configured For</div>
        <div class="text-sm font-semibold text-[#1C1B18] truncate"><?= htmlspecialchars($activeFirm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="text-xs text-[#5C5A54] font-mono"><?= !empty($activeFirm['gstin']) ? 'GSTIN: ' . htmlspecialchars($activeFirm['gstin'], ENT_QUOTES, 'UTF-8') : 'Non-GST Business' ?></div>
        <div class="pt-2 mt-2 border-t border-[#E2DFD7]">
          <a href="/firms" class="text-xs font-medium text-[#2B526E] hover:underline no-underline"><i class="bi bi-buildings mr-1"></i> Switch / Manage Firms &rarr;</a>
        </div>
      </div>
    </div>

    <!-- Settings Content Panels -->
    <div class="lg:col-span-9">
      <?php if ($tab === 'sales'): ?>
        <form action="/settings" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="section" value="sales">

          <div class="rf-card">
            <div class="rf-card-header">
              <div>
                <h2 class="text-base font-semibold text-[#1C1B18] flex items-center gap-2"><i class="bi bi-cart-check text-[#2B526E]"></i> Sales Record Configuration</h2>
                <p class="text-xs text-[#78746C] mt-0.5">Manage how sales records are constructed, calculated, and presented</p>
              </div>
            </div>

            <div class="rf-card-body space-y-7">
              <!-- Default Tax Mode -->
              <div class="pb-6 border-b border-[#ECEAE4]">
                <div class="text-sm font-semibold text-[#1C1B18] mb-1">Default Record Tax Mode (GST vs Non-GST)</div>
                <p class="text-xs text-[#78746C] mb-4">Choose whether new sales records start with GST tax calculations enabled or in Non-GST format.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="setting-radio-card <?= (($salesSettings['default_gst_type'] ?? '') === 'gst') ? 'active' : '' ?>">
                    <label class="flex items-start gap-3 cursor-pointer">
                      <input class="rf-radio mt-0.5" type="radio" name="default_gst_type" id="gstTypeGst" value="gst" <?= (($salesSettings['default_gst_type'] ?? '') === 'gst') ? 'checked' : '' ?>>
                      <div>
                        <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
                          <span class="rf-badge rf-badge-slate">GST</span> Sales Record (Default)
                        </div>
                        <p class="text-xs text-[#5C5A54] mt-1.5 m-0">Automatic CGST, SGST, IGST calculations and HSN code tracking.</p>
                      </div>
                    </label>
                  </div>

                  <div class="setting-radio-card <?= (($salesSettings['default_gst_type'] ?? '') === 'non_gst') ? 'active' : '' ?>">
                    <label class="flex items-start gap-3 cursor-pointer">
                      <input class="rf-radio mt-0.5" type="radio" name="default_gst_type" id="gstTypeNonGst" value="non_gst" <?= (($salesSettings['default_gst_type'] ?? '') === 'non_gst') ? 'checked' : '' ?>>
                      <div>
                        <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
                          <span class="rf-badge rf-badge-neutral">Non-GST</span> Retail Record
                        </div>
                        <p class="text-xs text-[#5C5A54] mt-1.5 m-0">Simple record with total amounts without requiring GST breakdown.</p>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- GST Calculation Method -->
              <div class="pb-6 border-b border-[#ECEAE4]">
                <div class="text-sm font-semibold text-[#1C1B18] mb-1">GST Calculation Method</div>
                <p class="text-xs text-[#78746C] mb-4">Select how GST is calculated when creating a bill.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="setting-radio-card <?= (($salesSettings['gst_calc_mode'] ?? '') === 'separate') ? 'active' : '' ?>">
                    <label class="flex items-start gap-3 cursor-pointer">
                      <input class="rf-radio mt-0.5" type="radio" name="gst_calc_mode" id="calcSeparate" value="separate" <?= (($salesSettings['gst_calc_mode'] ?? '') === 'separate') ? 'checked' : '' ?>>
                      <div>
                        <div class="text-sm font-semibold text-[#1C1B18]">Separate GST (Item-Wise Rates)</div>
                        <p class="text-xs text-[#5C5A54] mt-1.5 m-0">Each line item carries its own GST rate (0%, 5%, 12%, 18%, 28%).</p>
                      </div>
                    </label>
                  </div>

                  <div class="setting-radio-card <?= (($salesSettings['gst_calc_mode'] ?? '') === 'final_amount') ? 'active' : '' ?>">
                    <label class="flex items-start gap-3 cursor-pointer">
                      <input class="rf-radio mt-0.5" type="radio" name="gst_calc_mode" id="calcFinal" value="final_amount" <?= (($salesSettings['gst_calc_mode'] ?? '') === 'final_amount') ? 'checked' : '' ?>>
                      <div>
                        <div class="text-sm font-semibold text-[#1C1B18]">GST on Final Amount (Composite)</div>
                        <p class="text-xs text-[#5C5A54] mt-1.5 m-0">Tax computed uniformly on net taxable subtotal after discounts.</p>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Discount Column -->
              <div class="pb-6 border-b border-[#ECEAE4]">
                <div class="setting-toggle-box <?= !empty($salesSettings['enable_discount_column']) ? 'active' : '' ?>" id="salesDiscountToggleBox">
                  <div class="flex items-center justify-between gap-4">
                    <div>
                      <label class="text-sm font-semibold text-[#1C1B18] block cursor-pointer" for="enableDiscountToggle">
                        Line Item Discount Column
                      </label>
                      <p class="text-xs text-[#5C5A54] mt-1 m-0">Show or hide the <strong>Discount % (Disc %)</strong> column in the bill items table.</p>
                    </div>
                    <label class="rf-switch" aria-label="Toggle Line Item Discount Column">
                      <input type="checkbox" name="enable_discount_column" value="1" id="enableDiscountToggle" <?= !empty($salesSettings['enable_discount_column']) ? 'checked' : '' ?>>
                      <span class="rf-switch-thumb"></span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Due Days -->
              <div>
                <label class="rf-label-plain" for="default_due_days">Default Payment Due Period</label>
                <div class="flex flex-wrap items-center gap-3">
                  <div class="flex items-center w-36">
                    <input type="number" id="default_due_days" name="default_due_days" class="rf-input rf-input-sm rounded-r-none tabular-nums" min="0" max="365" value="<?= htmlspecialchars((string)($salesSettings['default_due_days'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="px-3 py-1.5 text-xs bg-[#F3F1EC] border border-l-0 border-[#D9D6CE] rounded-r-md text-[#5C5A54]">Days</span>
                  </div>
                  <span class="text-xs text-[#78746C]">Set to 0 for immediate payment due on bill date.</span>
                </div>
              </div>
            </div>

            <div class="rf-card-footer">
              <span class="text-xs text-[#78746C]"><i class="bi bi-info-circle mr-1"></i> Changes apply immediately to new sales bills.</span>
              <button type="submit" class="rf-btn rf-btn-primary">
                <i class="bi bi-check2-circle"></i> Save Sales Settings
              </button>
            </div>
          </div>
        </form>

      <?php elseif ($tab === 'purchases'): ?>
        <form action="/settings" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="section" value="purchases">

          <div class="rf-card">
            <div class="rf-card-header">
              <div>
                <h2 class="text-base font-semibold text-[#1C1B18] flex items-center gap-2"><i class="bi bi-bag-plus text-[#2E5A36]"></i> Purchase Bill Configuration</h2>
                <p class="text-xs text-[#78746C] mt-0.5">Manage defaults for inward supplier bills and stock entries</p>
              </div>
            </div>

            <div class="rf-card-body space-y-7">
              <div class="pb-6 border-b border-[#ECEAE4]">
                <div class="text-sm font-semibold text-[#1C1B18] mb-3">Default Purchase Bill Tax Mode</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="setting-radio-card <?= (($purSettings['default_gst_type'] ?? '') === 'gst') ? 'active' : '' ?>">
                    <label class="flex items-center gap-3 cursor-pointer">
                      <input class="rf-radio" type="radio" name="default_gst_type" id="purGst" value="gst" <?= (($purSettings['default_gst_type'] ?? '') === 'gst') ? 'checked' : '' ?>>
                      <span class="text-sm font-semibold text-[#1C1B18]">GST Purchase Bill</span>
                    </label>
                  </div>
                  <div class="setting-radio-card <?= (($purSettings['default_gst_type'] ?? '') === 'non_gst') ? 'active' : '' ?>">
                    <label class="flex items-center gap-3 cursor-pointer">
                      <input class="rf-radio" type="radio" name="default_gst_type" id="purNonGst" value="non_gst" <?= (($purSettings['default_gst_type'] ?? '') === 'non_gst') ? 'checked' : '' ?>>
                      <span class="text-sm font-semibold text-[#1C1B18]">Non-GST / Cash Purchase</span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="setting-toggle-box <?= !empty($purSettings['enable_discount_column']) ? 'active' : '' ?>" id="purDiscountToggleBox">
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <label class="text-sm font-semibold text-[#1C1B18] block cursor-pointer" for="purDiscountToggle">
                      Line Item Discount Column in Purchases
                    </label>
                    <p class="text-xs text-[#5C5A54] mt-1 m-0">Show or hide supplier discount percentage column.</p>
                  </div>
                  <label class="rf-switch" aria-label="Toggle Purchase Discount Column">
                    <input type="checkbox" name="enable_discount_column" value="1" id="purDiscountToggle" <?= !empty($purSettings['enable_discount_column']) ? 'checked' : '' ?>>
                    <span class="rf-switch-thumb"></span>
                  </label>
                </div>
              </div>
            </div>

            <div class="rf-card-footer justify-end">
              <button type="submit" class="rf-btn rf-btn-primary">
                <i class="bi bi-check2-circle"></i> Save Purchase Settings
              </button>
            </div>
          </div>
        </form>

      <?php elseif ($tab === 'print'): ?>
        <form action="/settings" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="section" value="print">

          <div class="rf-card">
            <div class="rf-card-header">
              <div>
                <h2 class="text-base font-semibold text-[#1C1B18] flex items-center gap-2"><i class="bi bi-printer text-[#5C5A54]"></i> Print &amp; A4 Invoice Customization</h2>
                <p class="text-xs text-[#78746C] mt-0.5">Control what appears on printed invoices and PDF exports</p>
              </div>
            </div>

            <div class="rf-card-body space-y-5">
              <div class="flex items-center justify-between gap-4 pb-5 border-b border-[#ECEAE4]">
                <div>
                  <div class="text-sm font-semibold text-[#1C1B18]">Display Bank Account Details</div>
                  <div class="text-xs text-[#78746C] mt-0.5">Show bank name, A/C number, and IFSC on invoice footer.</div>
                </div>
                <label class="rf-switch">
                  <input type="checkbox" name="show_bank_details" value="1" <?= !empty($printSettings['show_bank_details']) ? 'checked' : '' ?>>
                  <span class="rf-switch-thumb"></span>
                </label>
              </div>

              <div class="flex items-center justify-between gap-4 pb-5 border-b border-[#ECEAE4]">
                <div>
                  <div class="text-sm font-semibold text-[#1C1B18]">Display Dynamic UPI QR Code</div>
                  <div class="text-xs text-[#78746C] mt-0.5">Include UPI QR code on bill for instant customer scanning.</div>
                </div>
                <label class="rf-switch">
                  <input type="checkbox" name="show_upi_qr" value="1" <?= !empty($printSettings['show_upi_qr']) ? 'checked' : '' ?>>
                  <span class="rf-switch-thumb"></span>
                </label>
              </div>

              <div class="flex items-center justify-between gap-4 pb-5 border-b border-[#ECEAE4]">
                <div>
                  <div class="text-sm font-semibold text-[#1C1B18]">Display Authorized Signatory Box</div>
                  <div class="text-xs text-[#78746C] mt-0.5">Include signature and stamp placeholder at bottom right.</div>
                </div>
                <label class="rf-switch">
                  <input type="checkbox" name="show_signature" value="1" <?= !empty($printSettings['show_signature']) ? 'checked' : '' ?>>
                  <span class="rf-switch-thumb"></span>
                </label>
              </div>

              <div>
                <label class="rf-label-plain">Default Invoice Footer Notes</label>
                <textarea name="footer_notes" class="rf-textarea" rows="2" placeholder="e.g. Thank you for your business!"><?= htmlspecialchars($printSettings['footer_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
              </div>
            </div>

            <div class="rf-card-footer justify-end">
              <button type="submit" class="rf-btn rf-btn-primary">
                <i class="bi bi-check2-circle"></i> Save Print Settings
              </button>
            </div>
          </div>
        </form>

      <?php elseif ($tab === 'general'): ?>
        <form action="/settings" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="section" value="general">

          <div class="rf-card">
            <div class="rf-card-header">
              <div>
                <h2 class="text-base font-semibold text-[#1C1B18] flex items-center gap-2"><i class="bi bi-sliders text-[#8A5A00]"></i> General System Preferences</h2>
                <p class="text-xs text-[#78746C] mt-0.5">Regional formatting and currency symbols</p>
              </div>
            </div>

            <div class="rf-card-body">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <label class="rf-label-plain">Currency Symbol</label>
                  <input type="text" name="currency_symbol" class="rf-input" value="<?= htmlspecialchars($genSettings['currency_symbol'] ?? '₹', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                  <label class="rf-label-plain">Date Format</label>
                  <select name="date_format" class="rf-select">
                    <option value="DD/MM/YYYY" <?= (($genSettings['date_format'] ?? 'DD/MM/YYYY') === 'DD/MM/YYYY') ? 'selected' : '' ?>>DD/MM/YYYY (e.g. <?= date('d/m/Y') ?>)</option>
                    <option value="DD-MM-YYYY" <?= (($genSettings['date_format'] ?? '') === 'DD-MM-YYYY') ? 'selected' : '' ?>>DD-MM-YYYY (e.g. <?= date('d-m-Y') ?>)</option>
                    <option value="YYYY-MM-DD" <?= (($genSettings['date_format'] ?? '') === 'YYYY-MM-DD') ? 'selected' : '' ?>>YYYY-MM-DD (e.g. <?= date('Y-m-d') ?>)</option>
                  </select>
                  <p class="rf-help">Applied to invoice creation, bill viewing, printouts, and reports.</p>
                </div>

                <div>
                  <label class="rf-label-plain">Inventory Stock Valuation Method</label>
                  <select name="stock_valuation_method" class="rf-select">
                    <option value="fifo" <?= (($genSettings['stock_valuation_method'] ?? 'fifo') === 'fifo') ? 'selected' : '' ?>>FIFO (First-In, First-Out)</option>
                    <option value="weighted_avg" <?= (($genSettings['stock_valuation_method'] ?? '') === 'weighted_avg') ? 'selected' : '' ?>>Weighted Average Cost</option>
                  </select>
                  <p class="rf-help">Controls cost layer calculation on Stock Valuation reports.</p>
                </div>
              </div>
            </div>

            <div class="rf-card-footer justify-end">
              <button type="submit" class="rf-btn rf-btn-primary">
                <i class="bi bi-check2-circle"></i> Save General Settings
              </button>
            </div>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
