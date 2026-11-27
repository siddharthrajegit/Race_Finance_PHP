<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-filetype-xml text-[#2B526E]"></i>
        <span>Tally-Compatible XML Export</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Export Party Ledgers and Sales/Purchase Vouchers in Tally XML import schema</p>
    </div>
    <a href="/reports" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Reports</a>
  </div>

  <div class="rf-alert rf-alert-info mb-0">
    <i class="bi bi-info-circle mt-0.5 shrink-0"></i>
    <div class="text-xs leading-relaxed">
      <strong>Import Guidance:</strong> Generates Tally-compatible <code class="font-mono bg-white/70 px-1 rounded">&lt;ENVELOPE&gt;</code> XML containing Sundry Debtor/Creditor masters and Sales/Purchase vouchers for the selected period.
    </div>
  </div>

  <div class="rf-card max-w-2xl">
    <form action="/reports/tally-export/download" method="GET">
      <div class="rf-card-body space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="rf-label-plain text-xs">From Date</label>
            <input type="date" name="from_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label class="rf-label-plain text-xs">To Date</label>
            <input type="date" name="to_date" class="rf-input rf-input-sm" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>
        <div class="text-xs text-[#5C5A54]">
          Selected Period Ready: <strong class="text-[#1C1B18] tabular-nums"><?= (int)$salesCount ?></strong> Sales &amp; <strong class="text-[#1C1B18] tabular-nums"><?= (int)$purchasesCount ?></strong> Purchase voucher(s).
        </div>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">
          <i class="bi bi-download"></i> Download Tally XML File
        </button>
      </div>
    </form>
  </div>
</div>
