<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Business &amp; Tax Reports</h1>
    <p class="text-sm text-[#5C5A54] mt-1">Generate financial summaries, customer balances, and GST tax statements</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="rf-card p-6 flex flex-col justify-between hover:border-[#C8C4BA] transition-colors">
      <div>
        <div class="w-11 h-11 rounded-xl bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] flex items-center justify-center text-xl mb-5">
          <i class="bi bi-people"></i>
        </div>
        <h2 class="text-base font-semibold text-[#1C1B18] mb-2">Party &amp; Customer Report</h2>
        <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
          View outstanding receivables, payables, and detailed ledger balances for all customers and suppliers.
        </p>
      </div>
      <a href="/reports/parties" class="rf-btn rf-btn-secondary w-full">
        View Party Report &rarr;
      </a>
    </div>

    <div class="rf-card p-6 flex flex-col justify-between hover:border-[#C8C4BA] transition-colors">
      <div>
        <div class="w-11 h-11 rounded-xl bg-[#EDF3EC] border border-[#CFE0CE] text-[#2E5A36] flex items-center justify-center text-xl mb-5">
          <i class="bi bi-file-earmark-text"></i>
        </div>
        <h2 class="text-base font-semibold text-[#1C1B18] mb-2">GSTR-1 &amp; Tax Summary</h2>
        <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
          Export taxable values, CGST, SGST, and IGST breakdowns for quick GST return filing and audits.
        </p>
      </div>
      <a href="/reports/tax" class="rf-btn rf-btn-secondary w-full">
        View Tax Report &rarr;
      </a>
    </div>

    <div class="rf-card p-6 flex flex-col justify-between hover:border-[#C8C4BA] transition-colors">
      <div>
        <div class="w-11 h-11 rounded-xl bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center text-xl mb-5">
          <i class="bi bi-box-seam"></i>
        </div>
        <h2 class="text-base font-semibold text-[#1C1B18] mb-2">Item-Wise Sales Report</h2>
        <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
          Identify top-selling products, sales quantities, total revenue, and remaining warehouse inventory.
        </p>
      </div>
      <a href="/reports/items" class="rf-btn rf-btn-secondary w-full">
        View Item Report &rarr;
      </a>
    </div>

    <?php if (defined('ENABLE_COMPLIANCE_EXPORTS') && ENABLE_COMPLIANCE_EXPORTS): ?>
      <div class="rf-card p-6 flex flex-col justify-between hover:border-[#C8C4BA] transition-colors">
        <div>
          <div class="w-11 h-11 rounded-xl bg-[#FBF3DB] border border-[#EAE0B8] text-[#8A5A00] flex items-center justify-center text-xl mb-5">
            <i class="bi bi-file-earmark-spreadsheet"></i>
          </div>
          <h2 class="text-base font-semibold text-[#1C1B18] mb-2">GSTR-1 / 2B / 3B CSV Exports</h2>
          <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
            Download software-compiled outward sales, purchase ITC, and liability working CSV sheets for CA review.
          </p>
        </div>
        <a href="/reports/gst-exports" class="rf-btn rf-btn-amber w-full">
          Open GST Exports &rarr;
        </a>
      </div>

      <div class="rf-card p-6 flex flex-col justify-between hover:border-[#C8C4BA] transition-colors">
        <div>
          <div class="w-11 h-11 rounded-xl bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center text-xl mb-5">
            <i class="bi bi-filetype-xml"></i>
          </div>
          <h2 class="text-base font-semibold text-[#1C1B18] mb-2">Tally-Compatible XML Export</h2>
          <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">
            Export Sundry Debtor/Creditor masters and Sales/Purchase vouchers in standard Tally XML schema.
          </p>
        </div>
        <a href="/reports/tally-export" class="rf-btn rf-btn-secondary w-full">
          Export Tally XML &rarr;
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>
