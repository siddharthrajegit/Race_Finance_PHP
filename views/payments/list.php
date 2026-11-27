<?php
$tFilter = $typeFilter ?? '';
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Payment Transactions</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Record receipts from customers and payments to vendors</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
      <a href="/payments/create?type=payment_in" class="rf-btn rf-btn-sage-solid">
        <i class="bi bi-arrow-down-left-circle"></i> Record Receipt (In)
      </a>
      <a href="/payments/create?type=payment_out" class="rf-btn rf-btn-danger-solid">
        <i class="bi bi-arrow-up-right-circle"></i> Record Payment (Out)
      </a>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="rf-card p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="inline-flex rounded-lg bg-[#F3F1EC] p-1 border border-[#E2DFD7]">
        <a href="/payments" class="px-3.5 py-1.5 rounded-md text-xs font-medium no-underline transition-colors <?= empty($tFilter) ? 'bg-white text-[#1C1B18] shadow-xs' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>">All Payments</a>
        <a href="/payments?type=payment_in" class="px-3.5 py-1.5 rounded-md text-xs font-medium no-underline transition-colors <?= ($tFilter === 'payment_in') ? 'bg-white text-[#2E5A36] shadow-xs' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>">Receipts (In)</a>
        <a href="/payments?type=payment_out" class="px-3.5 py-1.5 rounded-md text-xs font-medium no-underline transition-colors <?= ($tFilter === 'payment_out') ? 'bg-white text-[#8F2D2B] shadow-xs' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>">Payments (Out)</a>
      </div>

      <div class="relative flex-1 max-w-md">
        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="text" class="rf-input pl-9 py-2 text-xs" placeholder="Search payments by voucher no, party, reference..." data-table-search="paymentsTable">
      </div>
    </div>
  </div>

  <!-- Payments Table -->
  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table" id="paymentsTable">
        <thead>
          <tr>
            <th>Voucher No</th>
            <th>Date</th>
            <th>Party Name</th>
            <th class="text-center">Type</th>
            <th class="text-center">Mode</th>
            <th>Reference / Note</th>
            <th class="text-right">Amount (₹)</th>
            <th class="w-12 text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $p): ?>
              <tr>
                <td>
                  <a href="/payments/view/<?= $p['id'] ?>" class="font-mono font-semibold text-[#1C1B18] hover:underline no-underline">
                    <i class="bi bi-file-earmark-text mr-1 text-[#78746C]"></i><?= htmlspecialchars($p['payment_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td class="text-xs text-[#5C5A54] whitespace-nowrap"><?= htmlspecialchars(formatDate($p['payment_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a href="/parties/ledger/<?= $p['party_id'] ?>" class="font-medium text-[#1C1B18] hover:underline no-underline">
                    <?= htmlspecialchars($p['party_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td class="text-center">
                  <?php if ($p['type'] === 'payment_in'): ?>
                    <span class="rf-badge rf-badge-sage">Received (In)</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-rose">Paid (Out)</span>
                  <?php endif; ?>
                </td>
                <td class="text-center uppercase text-xs text-[#5C5A54]"><?= htmlspecialchars($p['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#5C5A54] truncate max-w-[190px]">
                  <?php
                    $ref = array_filter([$p['reference_no'] ?? null, $p['notes'] ?? null]);
                    echo !empty($ref) ? htmlspecialchars(implode(' - ', $ref), ENT_QUOTES, 'UTF-8') : '--';
                  ?>
                </td>
                <td class="text-right font-semibold tabular-nums whitespace-nowrap <?= ($p['type'] === 'payment_in') ? 'text-[#2E5A36]' : 'text-[#8F2D2B]' ?>">
                  ₹ <?= number_format((float)($p['amount'] ?? 0), 2) ?>
                </td>
                <td class="text-center">
                  <div class="rf-dropdown" data-ui-dropdown>
                    <button class="w-8 h-8 rounded-full hover:bg-[#EFECE6] inline-flex items-center justify-center text-[#5C5A54] bg-transparent border-0 cursor-pointer transition-colors" type="button" data-ui-dropdown-toggle aria-expanded="false" title="Actions">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="rf-dropdown-menu" data-ui-dropdown-menu>
                      <a class="rf-dropdown-item font-semibold text-[#2E5A36]" href="/payments/view/<?= $p['id'] ?>">
                        <i class="bi bi-file-earmark-text"></i> View Payment Slip
                      </a>
                      <a class="rf-dropdown-item" href="/parties/ledger/<?= $p['party_id'] ?>">
                        <i class="bi bi-journal-text text-[#2B526E]"></i> View Party Ledger
                      </a>
                      <div class="rf-dropdown-divider"></div>
                      <form action="/payments/delete/<?= $p['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete this payment record? FIFO bill balances will be recalculated automatically.">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
                          <i class="bi bi-trash"></i> Delete Payment
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-14 text-[#78746C]">
                <i class="bi bi-cash-stack text-3xl block mb-2 text-[#B5B0A6]"></i>
                No payment transactions recorded yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
