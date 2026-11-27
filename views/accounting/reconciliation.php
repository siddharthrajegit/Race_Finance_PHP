<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_accounting', $userPermissions ?? [], true);
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-bank text-[#8A5A00]"></i>
        <span>Bank Reconciliation (CSV Import)</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Upload bank statement CSVs and manually match entries against recorded payments &amp; expenses (no live bank APIs required)</p>
    </div>
    <a href="/accounting" class="rf-btn rf-btn-secondary rf-btn-sm">Accounting Hub</a>
  </div>

  <!-- Summary KPI Strip -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="rf-card p-4">
      <div class="text-xs font-medium text-[#78746C]">Total Lines</div>
      <div class="text-2xl font-semibold text-[#1C1B18] tabular-nums mt-1"><?= (int)$summary['total_lines'] ?></div>
    </div>
    <div class="rf-card p-4">
      <div class="text-xs font-medium text-[#78746C]">Matched</div>
      <div class="text-2xl font-semibold text-[#2E5A36] tabular-nums mt-1"><?= (int)$summary['matched_count'] ?></div>
    </div>
    <div class="rf-card p-4">
      <div class="text-xs font-medium text-[#78746C]">Unmatched</div>
      <div class="text-2xl font-semibold text-[#8F2D2B] tabular-nums mt-1"><?= (int)$summary['unmatched_count'] ?></div>
    </div>
    <div class="rf-card p-4">
      <div class="text-xs font-medium text-[#78746C]">Ignored</div>
      <div class="text-2xl font-semibold text-[#5C5A54] tabular-nums mt-1"><?= (int)$summary['ignored_count'] ?></div>
    </div>
  </div>

  <?php if ($canManage): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">
      <form action="/accounting/reconciliation/import" method="POST" enctype="multipart/form-data" class="rf-card flex flex-col">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="rf-card-header py-3">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Import Bank Statement CSV</h2>
        </div>
        <div class="rf-card-body space-y-2.5 flex-1">
          <input type="file" name="statement_csv" class="block w-full text-xs text-[#5C5A54] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-[#D4D1C9] file:text-xs file:font-medium file:bg-[#F7F6F3] file:text-[#1C1B18] hover:file:bg-[#EFECE6]" accept=".csv,.txt" required>
          <p class="text-xs text-[#78746C] m-0">CSV headers detected automatically: Date, Description/Narration, Reference, Debit/Withdrawal, Credit/Deposit, Balance.</p>
        </div>
        <div class="rf-card-footer justify-end py-3">
          <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Upload &amp; Import CSV</button>
        </div>
      </form>

      <form action="/accounting/reconciliation/manual-line" method="POST" class="rf-card flex flex-col">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="rf-card-header py-3">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Quick Add Bank Entry</h2>
        </div>
        <div class="rf-card-body flex-1">
          <div class="grid grid-cols-12 gap-2.5">
            <div class="col-span-4"><input type="date" name="txn_date" class="rf-input rf-input-sm" required value="<?= date('Y-m-d') ?>"></div>
            <div class="col-span-8"><input type="text" name="description" class="rf-input rf-input-sm" required placeholder="Bank narration / UTR note"></div>
            <div class="col-span-4"><input type="text" name="reference_no" class="rf-input rf-input-sm font-mono" placeholder="Ref / UTR"></div>
            <div class="col-span-4"><input type="number" name="debit_amount" class="rf-input rf-input-sm text-right font-mono tabular-nums" step="0.01" min="0" placeholder="Debit (Out)"></div>
            <div class="col-span-4"><input type="number" name="credit_amount" class="rf-input rf-input-sm text-right font-mono tabular-nums" step="0.01" min="0" placeholder="Credit (In)"></div>
          </div>
        </div>
        <div class="rf-card-footer justify-end py-3">
          <button type="submit" class="rf-btn rf-btn-secondary rf-btn-sm">Add Line</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="rf-card">
    <div class="rf-card-header py-3 flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Statement Entries</h2>
      <div class="inline-flex flex-wrap items-center gap-1.5">
        <a href="/accounting/reconciliation" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= empty($filters['match_status']) ? 'rf-btn-primary' : 'rf-btn-secondary' ?>">All</a>
        <a href="/accounting/reconciliation?match_status=unmatched" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= ($filters['match_status'] ?? '') === 'unmatched' ? 'rf-btn-danger' : 'rf-btn-secondary' ?>">Unmatched</a>
        <a href="/accounting/reconciliation?match_status=matched" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= ($filters['match_status'] ?? '') === 'matched' ? 'rf-btn-sage-solid' : 'rf-btn-sage' ?>">Matched</a>
      </div>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Description &amp; Reference</th>
            <th class="text-right">Debit (Out)</th>
            <th class="text-right">Credit (In)</th>
            <th>Status</th>
            <?php if ($canManage): ?><th class="text-right">Reconcile</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($lines)): ?>
            <tr>
              <td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center py-10 text-[#78746C]">No bank statement lines imported yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($lines as $ln): ?>
              <tr>
                <td class="whitespace-nowrap text-xs text-[#5C5A54]"><?= htmlspecialchars(formatDate($ln['txn_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <div class="font-medium text-[#1C1B18]"><?= htmlspecialchars($ln['description'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($ln['reference_no'])): ?>
                    <div class="text-xs text-[#78746C] font-mono">Ref: <?= htmlspecialchars($ln['reference_no'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-right font-mono tabular-nums text-[#8F2D2B]"><?= (float)$ln['debit_amount'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['debit_amount'], 2) : '--' ?></td>
                <td class="text-right font-mono tabular-nums text-[#2E5A36]"><?= (float)$ln['credit_amount'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['credit_amount'], 2) : '--' ?></td>
                <td>
                  <?php if ($ln['match_status'] === 'matched'): ?>
                    <span class="rf-badge rf-badge-sage">Matched (<?= htmlspecialchars((string)$ln['matched_source_type'] . ' #' . $ln['matched_source_id'], ENT_QUOTES, 'UTF-8') ?>)</span>
                  <?php elseif ($ln['match_status'] === 'ignored'): ?>
                    <span class="rf-badge rf-badge-neutral">Ignored</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-rose">Unmatched</span>
                  <?php endif; ?>
                </td>
                <?php if ($canManage): ?>
                  <td class="text-right">
                    <div class="inline-flex items-center gap-1.5">
                      <?php if ($ln['match_status'] === 'unmatched'): ?>
                        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5 text-xs btn-match-modal" data-ui-modal="#reconcileMatchModal" data-line-id="<?= (int)$ln['id'] ?>" data-desc="<?= htmlspecialchars($ln['description'], ENT_QUOTES, 'UTF-8') ?>">
                          Match
                        </button>
                        <form action="/accounting/reconciliation/status" method="POST" class="m-0">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="line_id" value="<?= (int)$ln['id'] ?>">
                          <input type="hidden" name="status" value="ignored">
                          <button type="submit" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5 text-xs">Ignore</button>
                        </form>
                      <?php else: ?>
                        <form action="/accounting/reconciliation/status" method="POST" class="m-0">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="line_id" value="<?= (int)$ln['id'] ?>">
                          <input type="hidden" name="status" value="unmatched">
                          <button type="submit" class="rf-btn rf-btn-amber rf-btn-sm py-1 px-2.5 text-xs">Reset</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Match Modal -->
<div class="rf-modal hidden" id="reconcileMatchModal" tabindex="-1" aria-hidden="true">
  <div class="rf-modal-dialog max-w-md">
    <form action="/accounting/reconciliation/match" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="line_id" id="modalMatchLineId">
      <div class="rf-modal-header">
        <h3 class="rf-modal-title text-sm">Match Bank Line to Book Transaction</h3>
        <button type="button" class="text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" data-ui-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="rf-modal-body space-y-3">
        <div class="text-xs text-[#5C5A54]" id="modalMatchDesc"></div>
        <div>
          <label class="rf-label-plain text-xs">Matching Book Payments / Expenses</label>
          <select name="match_target" id="modalMatchSelect" class="rf-select rf-select-sm" required></select>
        </div>
      </div>
      <div class="rf-modal-footer">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Cancel</button>
        <button type="submit" class="rf-btn rf-btn-sage-solid rf-btn-sm">Confirm Match</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-match-modal').forEach(btn => {
    btn.addEventListener('click', async () => {
      const lineId = btn.dataset.lineId;
      document.getElementById('modalMatchLineId').value = lineId;
      document.getElementById('modalMatchDesc').textContent = btn.dataset.desc;
      const sel = document.getElementById('modalMatchSelect');
      sel.innerHTML = '<option value="">Loading candidates...</option>';
      try {
        const res = await fetch('/api/accounting/reconcile-candidates/' + lineId);
        const data = await res.json();
        sel.innerHTML = '';
        if (!data.candidates || !data.candidates.length) {
          sel.innerHTML = '<option value="">No close amount matches found in Payments/Expenses</option>';
        } else {
          data.candidates.forEach(c => {
            const opt = document.createElement('option');
            opt.value = `${c.source_type}:${c.source_id}`;
            opt.textContent = `${c.label} — ${c.txn_date} (${Number(c.amount).toFixed(2)})`;
            sel.appendChild(opt);
          });
        }
      } catch (err) {
        sel.innerHTML = '<option value="">Failed to load candidates</option>';
      }
    });
  });
});
</script>
