<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4 no-print">
    <div>
      <a href="/reports" class="text-xs text-[#78746C] hover:text-[#1C1B18] no-underline">&larr; Back to Reports</a>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight mt-1">GSTR-1 &amp; GST Tax Summary Report</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Taxable turnover, CGST, SGST, and IGST breakdowns for tax filing</p>
    </div>
    <button type="button" class="rf-btn rf-btn-secondary btn-trigger-print">
      <i class="bi bi-printer"></i> Print Tax Statement
    </button>
  </div>

  <!-- Date Filter Form -->
  <div class="rf-card p-5 no-print">
    <form action="/reports/tax" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <?php $activeDateFormat = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY'; ?>
      <div>
        <label for="from_date" class="rf-label-plain text-xs">From Date</label>
        <div class="relative">
          <input type="text" class="rf-input rf-input-sm pr-9 date-picker" id="from_date" name="from_date" value="<?= htmlspecialchars(!empty($fromDate) ? formatDate($fromDate, $activeDateFormat) : '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" data-date-format="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
          <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#78746C] bg-transparent border-0 cursor-pointer p-0" onclick="const el = document.getElementById('from_date'); if (el._flatpickr) { el._flatpickr.open(); } else { el.focus(); }"><i class="bi bi-calendar3 text-xs"></i></button>
        </div>
      </div>
      <div>
        <label for="to_date" class="rf-label-plain text-xs">To Date</label>
        <div class="relative">
          <input type="text" class="rf-input rf-input-sm pr-9 date-picker" id="to_date" name="to_date" value="<?= htmlspecialchars(!empty($toDate) ? formatDate($toDate, $activeDateFormat) : '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" data-date-format="<?= htmlspecialchars($activeDateFormat, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
          <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#78746C] bg-transparent border-0 cursor-pointer p-0" onclick="const el = document.getElementById('to_date'); if (el._flatpickr) { el._flatpickr.open(); } else { el.focus(); }"><i class="bi bi-calendar3 text-xs"></i></button>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-filter"></i> Apply Filter
        </button>
        <a href="/reports/tax" class="rf-btn rf-btn-secondary rf-btn-sm">Reset</a>
      </div>
    </form>
  </div>

  <!-- Tax Breakdown Summary -->
  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-sm font-semibold text-[#1C1B18]">Turnover &amp; Tax Breakdown Summary</h2>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Type</th>
            <th class="text-center">Bills Count</th>
            <th class="text-right">Taxable Amount (₹)</th>
            <th class="text-right">CGST (₹)</th>
            <th class="text-right">SGST (₹)</th>
            <th class="text-right">IGST (₹)</th>
            <th class="text-right">Total Tax (₹)</th>
            <th class="text-right">Gross Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($summary)): ?>
            <?php foreach ($summary as $s): ?>
              <tr>
                <td class="font-medium text-[#1C1B18]"><?= !empty($s['is_gst_bill']) ? 'GST Sales Records' : 'Non-GST / Exempt Records' ?></td>
                <td class="text-center tabular-nums"><?= (int)($s['invoice_count'] ?? 0) ?></td>
                <td class="text-right font-medium tabular-nums">₹ <?= number_format((float)($s['total_taxable'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($s['total_cgst'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($s['total_sgst'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($s['total_igst'] ?? 0), 2) ?></td>
                <td class="text-right font-semibold text-[#2B526E] tabular-nums">₹ <?= number_format((float)($s['total_tax'] ?? 0), 2) ?></td>
                <td class="text-right font-semibold tabular-nums">₹ <?= number_format((float)($s['total_gross'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-10 text-[#78746C]">No sales records found for the selected period.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Record Wise GST Register -->
  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-sm font-semibold text-[#1C1B18]">Record Wise GST Register</h2>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Record No</th>
            <th>Customer Name</th>
            <th>Customer GSTIN</th>
            <th class="text-right">Taxable Value (₹)</th>
            <th class="text-right">CGST (₹)</th>
            <th class="text-right">SGST (₹)</th>
            <th class="text-right">IGST (₹)</th>
            <th class="text-right">Gross Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($invoices)): ?>
            <?php foreach ($invoices as $inv): ?>
              <tr>
                <td class="text-xs text-[#5C5A54] whitespace-nowrap"><?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a href="/invoices/view/<?= $inv['id'] ?>" class="font-mono font-semibold text-[#1C1B18] hover:underline no-underline">
                    <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($inv['party_gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right tabular-nums">₹ <?= number_format((float)($inv['taxable_amount'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($inv['cgst_amount'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($inv['sgst_amount'] ?? 0), 2) ?></td>
                <td class="text-right text-[#5C5A54] tabular-nums">₹ <?= number_format((float)($inv['igst_amount'] ?? 0), 2) ?></td>
                <td class="text-right font-semibold tabular-nums">₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center py-10 text-[#78746C]">No invoices found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Statutory Disclaimer -->
  <div class="rf-card-muted p-5 flex items-start gap-3.5">
    <i class="bi bi-exclamation-triangle text-lg text-[#8A5A00] mt-0.5 shrink-0"></i>
    <div>
      <h3 class="text-sm font-semibold text-[#1C1B18] mb-1">Statutory Record-Management &amp; Billing Utility Notice</h3>
      <p class="text-xs text-[#5C5A54] leading-relaxed m-0">
        <strong>Disclaimer:</strong> This software is intended for basic business record-keeping and billing purposes. It is not a substitute for a Chartered Accountant, tax professional, or official government tax/GST filing system.
      </p>
    </div>
  </div>
</div>
