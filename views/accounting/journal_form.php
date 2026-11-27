<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-pencil-square text-[#2B526E]"></i>
        <span>Post Manual Journal Entry</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Total debits must equal total credits before posting</p>
    </div>
    <a href="/accounting/journals" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Journals</a>
  </div>

  <form action="/accounting/journals/create" method="POST" id="manualJournalForm" class="space-y-5">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div class="rf-card p-5">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-2">
          <label class="rf-label-plain text-xs">Entry #</label>
          <input type="text" name="entry_number" class="rf-input rf-input-sm font-mono" required value="<?= htmlspecialchars($nextEntryNumber, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="md:col-span-3">
          <label class="rf-label-plain text-xs">Entry Date <span class="text-[#8F2D2B]">*</span></label>
          <input type="date" name="entry_date" class="rf-input rf-input-sm" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="md:col-span-3">
          <label class="rf-label-plain text-xs">Reference #</label>
          <input type="text" name="reference_no" class="rf-input rf-input-sm font-mono" placeholder="e.g. JV-CONTRA-01">
        </div>
        <div class="md:col-span-4">
          <label class="rf-label-plain text-xs">Narration <span class="text-[#8F2D2B]">*</span></label>
          <input type="text" name="narration" class="rf-input rf-input-sm" required placeholder="Reason for manual adjustment or contra entry">
        </div>
      </div>
    </div>

    <div class="rf-card">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Debit / Credit Lines</h2>
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" id="addJeLineBtn">
          <i class="bi bi-plus-circle"></i> Add Line
        </button>
      </div>
      <div class="rf-table-wrap">
        <table class="rf-table" id="jeLinesTable">
          <thead>
            <tr>
              <th>Ledger Account</th>
              <th>Line Description</th>
              <th class="w-40 text-right">Debit</th>
              <th class="w-40 text-right">Credit</th>
              <th class="w-12 text-center"></th>
            </tr>
          </thead>
          <tbody>
            <?php for ($r = 0; $r < 2; $r++): ?>
              <tr class="je-row">
                <td>
                  <select name="account_id[]" class="rf-select rf-select-sm" required>
                    <option value="">-- Select Account --</option>
                    <?php foreach ($accounts as $acc): ?>
                      <option value="<?= (int)$acc['id'] ?>"><?= htmlspecialchars($acc['code'] . ' — ' . $acc['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="text" name="line_description[]" class="rf-input rf-input-sm" placeholder="Optional line note"></td>
                <td><input type="number" name="debit[]" class="rf-input rf-input-sm text-right font-mono tabular-nums je-debit" min="0" step="0.01" value="0.00"></td>
                <td><input type="number" name="credit[]" class="rf-input rf-input-sm text-right font-mono tabular-nums je-credit" min="0" step="0.01" value="0.00"></td>
                <td class="text-center">
                  <button type="button" class="w-7 h-7 rounded-lg border border-[#E2DFD7] text-[#8F2D2B] hover:bg-[#FDF2F2] inline-flex items-center justify-center bg-white cursor-pointer btn-remove-je" title="Remove line">
                    <i class="bi bi-x"></i>
                  </button>
                </td>
              </tr>
            <?php endfor; ?>
          </tbody>
          <tfoot class="bg-[#FAF9F6] font-semibold border-t border-[#E2DFD7]">
            <tr>
              <td colspan="2" class="text-right text-xs uppercase tracking-wider text-[#5C5A54]">Totals:</td>
              <td class="text-right font-mono tabular-nums text-[#1C1B18]" id="jeTotalDebit">0.00</td>
              <td class="text-right font-mono tabular-nums text-[#1C1B18]" id="jeTotalCredit">0.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="rf-card-footer justify-between py-3">
        <span id="jeBalanceMsg" class="text-xs font-semibold text-[#5C5A54]">Enter equal debit &amp; credit amounts</span>
        <button type="submit" class="rf-btn rf-btn-sage-solid rf-btn-sm" id="btnPostJe" disabled>
          <i class="bi bi-check2-circle"></i> Post Balanced Entry
        </button>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#jeLinesTable tbody');
  const totDrEl = document.getElementById('jeTotalDebit');
  const totCrEl = document.getElementById('jeTotalCredit');
  const msgEl = document.getElementById('jeBalanceMsg');
  const submitBtn = document.getElementById('btnPostJe');

  function recalc() {
    let dr = 0, cr = 0;
    tbody.querySelectorAll('.je-debit').forEach(i => dr += parseFloat(i.value || 0));
    tbody.querySelectorAll('.je-credit').forEach(i => cr += parseFloat(i.value || 0));
    totDrEl.textContent = dr.toFixed(2);
    totCrEl.textContent = cr.toFixed(2);
    const diff = Math.abs(dr - cr);
    const ok = (dr > 0 && diff < 0.01);
    submitBtn.disabled = !ok;
    msgEl.textContent = ok ? 'Entry balanced and ready to post.' : `Difference: ${diff.toFixed(2)} (must be 0.00)`;
    msgEl.className = 'text-xs font-semibold ' + (ok ? 'text-[#2E5A36]' : 'text-[#8F2D2B]');
  }

  document.getElementById('addJeLineBtn').addEventListener('click', () => {
    const clone = tbody.querySelector('.je-row').cloneNode(true);
    clone.querySelector('select').value = '';
    clone.querySelectorAll('input').forEach(i => { i.value = i.type === 'number' ? '0.00' : ''; });
    tbody.appendChild(clone);
    recalc();
  });

  tbody.addEventListener('input', recalc);
  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-remove-je');
    if (btn && tbody.querySelectorAll('.je-row').length > 2) {
      btn.closest('.je-row').remove();
      recalc();
    }
  });
});
</script>
