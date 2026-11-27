<?php $curr = $firmSettings['general']['currency_symbol'] ?? '₹'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-file-earmark-spreadsheet text-[#2E5A36]"></i>
        <span>GST Summary Exports (GSTR-1 / 2B / 3B)</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Local software-compiled tax summaries for CA reconciliation</p>
    </div>
    <a href="/reports" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Reports</a>
  </div>

  <div class="rf-alert rf-alert-warning mb-0">
    <i class="bi bi-exclamation-triangle mt-0.5 shrink-0"></i>
    <div class="text-xs leading-relaxed">
      <strong>Important Disclaimer:</strong> These CSV downloads are <strong>software-generated working summaries</strong> from your RACE FINANCE bills for accountant review. They are <strong>not</strong> official GSTN portal filings, e-Invoices (IRN), or e-Way Bills.
    </div>
  </div>

  <form action="/reports/gst-exports" method="GET" class="rf-card p-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <div>
        <label class="rf-label-plain text-xs">From Date</label>
        <input type="date" name="from_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div>
        <label class="rf-label-plain text-xs">To Date</label>
        <input type="date" name="to_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Update Period Summary</button>
      </div>
    </div>
  </form>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="rf-card p-5 flex flex-col justify-between gap-4">
      <div>
        <div class="text-sm font-semibold text-[#2B526E]">GSTR-1 Working Summary</div>
        <div class="text-xs text-[#5C5A54] mt-1">
          <?= count($data['sales']) ?> outward sales record(s) &bull; Output Tax:
          <strong class="font-mono text-[#1C1B18] tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)($data['outward']['cgst'] + $data['outward']['sgst'] + $data['outward']['igst']), 2) ?></strong>
        </div>
      </div>
      <a href="/reports/gst-exports/download?report=gstr1&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="rf-btn rf-btn-secondary rf-btn-sm w-full">
        <i class="bi bi-download"></i> Download GSTR-1 CSV
      </a>
    </div>

    <div class="rf-card p-5 flex flex-col justify-between gap-4">
      <div>
        <div class="text-sm font-semibold text-[#2E5A36]">GSTR-2B Purchase Register</div>
        <div class="text-xs text-[#5C5A54] mt-1">
          <?= count($data['purchases']) ?> inward purchase bill(s) &bull; Input ITC:
          <strong class="font-mono text-[#1C1B18] tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)($data['inward']['cgst'] + $data['inward']['sgst'] + $data['inward']['igst']), 2) ?></strong>
        </div>
      </div>
      <a href="/reports/gst-exports/download?report=gstr2b&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="rf-btn rf-btn-sage rf-btn-sm w-full">
        <i class="bi bi-download"></i> Download GSTR-2B CSV
      </a>
    </div>

    <div class="rf-card p-5 flex flex-col justify-between gap-4">
      <div>
        <div class="text-sm font-semibold text-[#1C1B18]">GSTR-3B Liability Summary</div>
        <div class="text-xs text-[#5C5A54] mt-1">
          Estimated Net Tax Liability:
          <strong class="font-mono text-[#1C1B18] tabular-nums"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$data['net_payable']['total_tax'], 2) ?></strong>
        </div>
      </div>
      <a href="/reports/gst-exports/download?report=gstr3b&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="rf-btn rf-btn-primary rf-btn-sm w-full">
        <i class="bi bi-download"></i> Download GSTR-3B CSV
      </a>
    </div>
  </div>

  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Period Tax Position Preview</h2>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Section</th>
            <th class="text-right">Taxable</th>
            <th class="text-right">CGST</th>
            <th class="text-right">SGST</th>
            <th class="text-right">IGST</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="font-medium text-[#1C1B18]">Outward Sales (3.1a)</td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['outward']['taxable'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['outward']['cgst'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['outward']['sgst'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['outward']['igst'], 2) ?></td>
          </tr>
          <tr>
            <td class="font-medium text-[#1C1B18]">Inward Purchases ITC (4A)</td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['inward']['taxable'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['inward']['cgst'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['inward']['sgst'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['inward']['igst'], 2) ?></td>
          </tr>
          <tr class="bg-[#FAF9F6] font-semibold">
            <td class="text-[#1C1B18]">Estimated Net Payable</td>
            <td class="text-right text-[#78746C]">--</td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['net_payable']['cgst'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['net_payable']['sgst'], 2) ?></td>
            <td class="text-right font-mono tabular-nums"><?= number_format($data['net_payable']['igst'], 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
