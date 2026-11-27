<!-- Floating Bulk Action Dock -->
<div class="bulk-action-bar hidden items-center justify-between flex-wrap gap-4" id="bulkActionBar_<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>">
  <div class="flex items-center gap-2.5">
    <span class="bulk-selected-counter">0</span>
    <span class="text-xs font-medium text-white/90">selected</span>
    <span class="text-white/30">&bull;</span>
    <button type="button" class="text-xs font-medium text-[#CFE0CE] hover:text-white bg-transparent border-0 p-0 cursor-pointer bulk-select-all-filtered-btn">Select All Visible</button>
    <span class="text-white/30">&bull;</span>
    <button type="button" class="text-xs text-white/60 hover:text-white bg-transparent border-0 p-0 cursor-pointer bulk-clear-selection-btn">Clear</button>
  </div>
  <div class="flex items-center gap-2">
    <button type="button" class="rf-btn rf-btn-sage-solid rf-btn-sm rounded-full px-4 bulk-print-btn" data-target-table="<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>" data-doc-type="<?= ($paymentType ?? '') === 'payment_out' ? 'purchase' : 'sale' ?>">
      <i class="bi bi-printer"></i> Print Selected
    </button>
  </div>
</div>

<div class="rf-card">
  <div class="rf-table-wrap">
    <table class="rf-table" id="<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>">
      <thead>
        <tr>
          <th class="w-10 text-center px-3">
            <input type="checkbox" class="rf-checkbox bulk-select-all" data-target-table="<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>" title="Select / Deselect All Visible">
          </th>
          <th class="sortable-th" data-sort-key="invoice-number" role="button" title="Click to sort by Invoice Number">
            <div class="inline-flex items-center gap-1.5">
              <span>Invoice #</span>
              <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
            </div>
          </th>
          <th class="sortable-th" data-sort-key="invoice-date" role="button" title="Click to sort by Date">
            <div class="inline-flex items-center gap-1.5">
              <span>Date</span>
              <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
            </div>
          </th>
          <th class="sortable-th" data-sort-key="party-name" role="button" title="Click to sort by <?= htmlspecialchars($partyLabel ?? 'Party', ENT_QUOTES, 'UTF-8') ?>">
            <div class="inline-flex items-center gap-1.5">
              <span><?= htmlspecialchars($partyLabel ?? 'Party', ENT_QUOTES, 'UTF-8') ?></span>
              <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
            </div>
          </th>
          <th class="text-center">Type</th>
          <th class="text-right sortable-th" data-sort-key="grand-total" role="button" title="Click to sort by Amount">
            <div class="inline-flex items-center justify-end gap-1.5 w-full">
              <span>Total (₹)</span>
              <span class="sort-icon"><i class="bi bi-arrow-down-up"></i></span>
            </div>
          </th>
          <th class="text-right">Paid / Balance</th>
          <th class="text-center">Status</th>
          <th class="w-12 text-center"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($invoices)): ?>
          <?php foreach ($invoices as $inv): ?>
            <tr data-invoice-number="<?= htmlspecialchars((string)($inv['invoice_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-invoice-date="<?= htmlspecialchars((string)($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-party-name="<?= htmlspecialchars(mb_strtolower($inv['party_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                data-grand-total="<?= (float)($inv['grand_total'] ?? 0) ?>">
              <td class="text-center px-3">
                <input type="checkbox" class="rf-checkbox invoice-row-checkbox" value="<?= (int)$inv['id'] ?>" data-invoice-id="<?= (int)$inv['id'] ?>">
              </td>
              <td>
                <a href="/invoices/view/<?= $inv['id'] ?>" class="font-mono font-semibold text-[#1C1B18] hover:underline no-underline">
                  <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>
              <td class="text-xs text-[#5C5A54] whitespace-nowrap">
                <?= htmlspecialchars(formatDate($inv['invoice_date']), ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td>
                <div class="font-medium text-[#1C1B18] truncate max-w-[190px]"><?= htmlspecialchars($inv['party_name'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($inv['party_phone'])): ?>
                  <div class="text-xs text-[#87847C]"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (!empty($inv['is_gst_bill'])): ?>
                  <span class="rf-badge rf-badge-slate">GST</span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-neutral">Non-GST</span>
                <?php endif; ?>
              </td>
              <td class="text-right font-semibold tabular-nums whitespace-nowrap">₹ <?= number_format((float)$inv['grand_total'], 2) ?></td>
              <td class="text-right tabular-nums whitespace-nowrap">
                <div class="text-xs font-medium text-[#2E5A36]">Paid: ₹ <?= number_format((float)($inv['paid_amount'] ?? 0), 2) ?></div>
                <?php if ((float)($inv['balance_due'] ?? 0) > 0.001): ?>
                  <div class="text-[11px] font-medium text-[#8F2D2B]">Due: ₹ <?= number_format((float)$inv['balance_due'], 2) ?></div>
                <?php else: ?>
                  <div class="text-[11px] text-[#87847C]">Cleared</div>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if ($inv['payment_status'] === 'paid'): ?>
                  <span class="rf-badge rf-badge-sage" title="Paid">Paid</span>
                <?php elseif ($inv['payment_status'] === 'partial'): ?>
                  <span class="rf-badge rf-badge-amber" title="Partially Paid">Partial</span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-rose" title="Unpaid">Unpaid</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <div class="rf-dropdown" data-ui-dropdown>
                  <button class="w-8 h-8 rounded-full hover:bg-[#EFECE6] inline-flex items-center justify-center text-[#5C5A54] bg-transparent border-0 cursor-pointer transition-colors" type="button" data-ui-dropdown-toggle aria-expanded="false" title="Actions">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <div class="rf-dropdown-menu" data-ui-dropdown-menu>
                    <a class="rf-dropdown-item font-semibold text-[#1C1B18]" href="/invoices/download/<?= $inv['id'] ?>">
                      <i class="bi bi-download text-[#2B526E]"></i> Download Record
                    </a>
                    <a class="rf-dropdown-item" href="/invoices/view/<?= $inv['id'] ?>">
                      <i class="bi bi-eye text-[#5C5A54]"></i> View Record
                    </a>
                    <a class="rf-dropdown-item" href="/invoices/edit/<?= $inv['id'] ?>">
                      <i class="bi bi-pencil-square text-[#8A5A00]"></i> Edit Record
                    </a>
                    <a class="rf-dropdown-item" href="/invoices/print/<?= $inv['id'] ?>" target="_blank">
                      <i class="bi bi-printer text-[#2E5A36]"></i> Print Digital Record
                    </a>
                    <?php if (!empty($inv['party_id'])): ?>
                      <a class="rf-dropdown-item" href="/payments/create?party_id=<?= $inv['party_id'] ?>&type=<?= $paymentType ?? 'payment_in' ?>">
                        <i class="bi bi-cash-stack text-[#8A5A00]"></i> <?= htmlspecialchars($paymentLabel ?? 'Record Payment', ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    <?php endif; ?>
                    <div class="rf-dropdown-divider"></div>
                    <form action="/invoices/delete/<?= $inv['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="<?= htmlspecialchars(($deleteMessagePrefix ?? 'Delete') . ' ' . $inv['invoice_number'] . '? ' . ($deleteMessageSuffix ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
                        <i class="bi bi-trash"></i> <?= htmlspecialchars($deleteLabel ?? 'Delete Record', ENT_QUOTES, 'UTF-8') ?>
                      </button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center py-14 text-[#78746C]">
              <i class="bi <?= htmlspecialchars($emptyIcon ?? 'bi-inbox', ENT_QUOTES, 'UTF-8') ?> text-3xl block mb-2 text-[#B5B0A6]"></i>
              <?= $emptyMessage ?? 'No records found' ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
