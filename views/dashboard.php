<div class="space-y-8">
  <!-- Active Firm Header -->
  <div class="rf-card p-6 sm:p-7">
    <div class="flex flex-wrap items-center justify-between gap-5">
      <div class="flex items-center gap-4 min-w-0">
        <?php if (!empty($firm['logo_path'])): ?>
          <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?>" class="w-14 h-14 rounded-xl border border-[#E6E4DF] p-1 bg-white object-contain shrink-0">
        <?php else: ?>
          <div class="w-14 h-14 rounded-xl bg-[#F3F1EC] border border-[#E2DFD7] text-[#5C5A54] flex items-center justify-center text-2xl shrink-0">
            <i class="bi bi-buildings"></i>
          </div>
        <?php endif; ?>
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="text-xl sm:text-2xl font-semibold text-[#1C1B18] tracking-tight truncate"><?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if (!empty($firm['gstin'])): ?>
              <span class="rf-badge rf-badge-slate font-mono">GSTIN: <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
              <span class="rf-badge rf-badge-neutral">Non-GST Firm</span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-[#78746C] mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1">
            <?php if (!empty($firm['phone'])): ?><span><i class="bi bi-telephone mr-1"></i><?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($firm['state'])): ?><span><i class="bi bi-geo-alt mr-1"></i><?= htmlspecialchars($firm['state'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($firm['state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</span><?php endif; ?>
          </p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="flex flex-wrap items-center gap-2.5">
        <a href="/sales/create" class="rf-btn rf-btn-primary rf-btn-sm py-2 px-3.5">
          <i class="bi bi-plus-circle"></i> New Sale Bill
        </a>
        <a href="/purchases/create" class="rf-btn rf-btn-secondary rf-btn-sm py-2 px-3.5">
          <i class="bi bi-bag-plus"></i> New Purchase
        </a>
        <a href="/items/create" class="rf-btn rf-btn-secondary rf-btn-sm py-2 px-3.5">
          <i class="bi bi-box-seam"></i> Add Item
        </a>
        <a href="/backup" class="rf-btn rf-btn-ghost rf-btn-sm py-2 px-3">
          <i class="bi bi-cloud-arrow-up"></i> Backup
        </a>
      </div>
    </div>
  </div>

  <!-- KPI Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    <a href="/sales" class="rf-card p-6 block no-underline hover:border-[#C8C4BA] transition-colors">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Today's Sales</div>
          <div class="text-2xl font-semibold text-[#2E5A36] mt-2 tabular-nums">₹ <?= number_format((float)($summary['todaySales'] ?? 0), 2) ?></div>
          <div class="text-xs text-[#78746C] mt-1.5 inline-flex items-center gap-1">Daily revenue <i class="bi bi-arrow-right-short"></i></div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-[#EDF3EC] border border-[#CFE0CE] text-[#2E5A36] flex items-center justify-center text-lg shrink-0">
          <i class="bi bi-currency-rupee"></i>
        </div>
      </div>
    </a>

    <a href="/sales" class="rf-card p-6 block no-underline hover:border-[#C8C4BA] transition-colors" title="Click to view all sales records">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">Total Sales (<?= (int)($summary['sales']['total_sales_count'] ?? 0) ?>)</div>
          <div class="text-2xl font-semibold text-[#1C1B18] mt-2 tabular-nums">₹ <?= number_format((float)($summary['sales']['total_sales_amount'] ?? 0), 2) ?></div>
          <div class="text-xs text-[#78746C] mt-1.5">
            Received: <span class="text-[#2E5A36] font-medium tabular-nums">₹ <?= number_format((float)($summary['sales']['total_sales_received'] ?? 0), 2) ?></span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] flex items-center justify-center text-lg shrink-0">
          <i class="bi bi-cart-check"></i>
        </div>
      </div>
    </a>

    <a href="/parties?type=customer" class="rf-card p-6 block no-underline hover:border-[#C8C4BA] transition-colors" title="Click to view customer balances">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">To Collect</div>
          <div class="text-2xl font-semibold text-[#8A5A00] mt-2 tabular-nums">₹ <?= number_format((float)($summary['sales']['total_receivables'] ?? 0), 2) ?></div>
          <div class="text-xs text-[#78746C] mt-1.5 inline-flex items-center gap-1">Receivables <i class="bi bi-arrow-right-short"></i></div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-[#FBF3DB] border border-[#EAE0B8] text-[#8A5A00] flex items-center justify-center text-lg shrink-0">
          <i class="bi bi-arrow-down-left-circle"></i>
        </div>
      </div>
    </a>

    <a href="/purchases" class="rf-card p-6 block no-underline hover:border-[#C8C4BA] transition-colors" title="Click to view purchase records">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78746C]">To Pay</div>
          <div class="text-2xl font-semibold text-[#8F2D2B] mt-2 tabular-nums">₹ <?= number_format((float)($summary['purchases']['total_payables'] ?? 0), 2) ?></div>
          <div class="text-xs text-[#78746C] mt-1.5 inline-flex items-center gap-1">Payables <i class="bi bi-arrow-right-short"></i></div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-[#FBEAEA] border border-[#ECD0D0] text-[#8F2D2B] flex items-center justify-center text-lg shrink-0">
          <i class="bi bi-arrow-up-right-circle"></i>
        </div>
      </div>
    </a>
  </div>

  <!-- Recent Records -->
  <div class="rf-card">
    <div class="rf-card-header">
      <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
        <i class="bi bi-clock-history text-[#5C5A54]"></i>
        <span>Recent Records</span>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <a href="/sales" class="rf-btn rf-btn-secondary rf-btn-sm">View All Sales</a>
        <a href="/sales/create" class="rf-btn rf-btn-primary rf-btn-sm"><i class="bi bi-plus-lg"></i> New Record</a>
      </div>
    </div>

    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Record &amp; Date</th>
            <th>Customer</th>
            <th class="text-center">Type</th>
            <th class="text-right">Total (₹)</th>
            <th class="text-right">Paid / Balance</th>
            <th class="text-center">Status</th>
            <th class="w-12 text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($summary['recentInvoices']) && count($summary['recentInvoices']) > 0): ?>
            <?php foreach ($summary['recentInvoices'] as $inv): ?>
              <tr>
                <td>
                  <a href="/invoices/view/<?= $inv['id'] ?>" class="font-mono font-semibold text-[#1C1B18] hover:underline no-underline">
                    <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                  <div class="text-xs text-[#87847C] mt-0.5"><?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td>
                  <div class="font-medium text-[#1C1B18] truncate max-w-[190px]"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
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
                  <?php if (($inv['payment_status'] ?? '') === 'paid'): ?>
                    <span class="rf-badge rf-badge-sage" title="Paid">Paid</span>
                  <?php elseif (($inv['payment_status'] ?? '') === 'partial'): ?>
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
                        <a class="rf-dropdown-item" href="/payments/create?party_id=<?= $inv['party_id'] ?>&type=payment_in">
                          <i class="bi bi-cash-stack text-[#8A5A00]"></i> Record Payment
                        </a>
                      <?php endif; ?>
                      <div class="rf-dropdown-divider"></div>
                      <form action="/invoices/delete/<?= $inv['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete sales record <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>? Item inventory stock and party balances will be restored automatically.">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="rf-dropdown-item text-[#8F2D2B]">
                          <i class="bi bi-trash"></i> Delete Record
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-14 text-[#78746C]">
                <i class="bi bi-inbox text-3xl block mb-2 text-[#B5B0A6]"></i>
                No sales records generated yet. Click <strong class="text-[#1C1B18]">New Record</strong> to log your first entry.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Low Stock & Quick Utilities -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Low Stock Alerts -->
    <div class="rf-card flex flex-col">
      <div class="rf-card-header">
        <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
          <i class="bi bi-exclamation-triangle text-[#8A5A00]"></i>
          <span>Low Stock Alerts</span>
        </div>
        <span class="rf-badge rf-badge-rose tabular-nums"><?= (int)($summary['lowStockCount'] ?? 0) ?> items</span>
      </div>
      <div class="flex-1">
        <?php if (!empty($summary['lowStockItems']) && count($summary['lowStockItems']) > 0): ?>
          <ul class="divide-y divide-[#F0EEE9] list-none p-0 m-0">
            <?php foreach (array_slice($summary['lowStockItems'], 0, 5) as $item): ?>
              <li class="flex items-center justify-between gap-4 px-6 py-4">
                <div class="min-w-0">
                  <div class="text-sm font-medium text-[#1C1B18] truncate"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="text-xs text-[#78746C] mt-0.5 tabular-nums">
                    Threshold: <?= htmlspecialchars((string)($item['low_stock_threshold'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    &bull; Sale Price: ₹ <?= number_format((float)($item['sale_price'] ?? 0), 2) ?>
                  </div>
                </div>
                <span class="rf-badge rf-badge-rose tabular-nums shrink-0">
                  <?= htmlspecialchars((string)($item['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?> left
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-center py-14 px-6 text-[#78746C]">
            <i class="bi bi-check-circle text-3xl text-[#2E5A36] block mb-2"></i>
            <div class="text-sm font-medium text-[#1C1B18]">All items are well stocked</div>
            <div class="text-xs mt-1">No items are below their minimum threshold.</div>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!empty($summary['lowStockItems']) && count($summary['lowStockItems']) > 0): ?>
        <div class="rf-card-footer justify-center">
          <a href="/items" class="text-xs font-medium text-[#2B526E] hover:underline no-underline">View Full Inventory &amp; Adjust Stock &rarr;</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Reports & Utilities -->
    <div class="rf-card flex flex-col">
      <div class="rf-card-header">
        <div class="flex items-center gap-2 text-sm font-semibold text-[#1C1B18]">
          <i class="bi bi-compass text-[#5C5A54]"></i>
          <span>Quick Reports &amp; Utilities</span>
        </div>
      </div>
      <div class="p-5 space-y-3 flex-1">
        <a href="/reports/parties" class="flex items-center justify-between gap-4 p-4 rounded-xl border border-[#ECEAE4] bg-[#FAF9F6] hover:bg-[#F3F1EC] hover:border-[#D9D6CE] transition-colors no-underline">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] flex items-center justify-center shrink-0">
              <i class="bi bi-people"></i>
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold text-[#1C1B18]">Customer &amp; Party Statement</div>
              <div class="text-xs text-[#78746C] truncate">Ledgers, outstanding dues &amp; payment histories</div>
            </div>
          </div>
          <i class="bi bi-chevron-right text-xs text-[#87847C]"></i>
        </a>

        <a href="/reports/tax" class="flex items-center justify-between gap-4 p-4 rounded-xl border border-[#ECEAE4] bg-[#FAF9F6] hover:bg-[#F3F1EC] hover:border-[#D9D6CE] transition-colors no-underline">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-[#EDF3EC] border border-[#CFE0CE] text-[#2E5A36] flex items-center justify-center shrink-0">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold text-[#1C1B18]">GSTR-1 Tax Summary</div>
              <div class="text-xs text-[#78746C] truncate">B2B, B2C taxable amounts &amp; GST breakdown</div>
            </div>
          </div>
          <i class="bi bi-chevron-right text-xs text-[#87847C]"></i>
        </a>

        <a href="/reports/items" class="flex items-center justify-between gap-4 p-4 rounded-xl border border-[#ECEAE4] bg-[#FAF9F6] hover:bg-[#F3F1EC] hover:border-[#D9D6CE] transition-colors no-underline">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center shrink-0">
              <i class="bi bi-box-seam"></i>
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold text-[#1C1B18]">Item-wise Sales Report</div>
              <div class="text-xs text-[#78746C] truncate">Product sales volumes, margins &amp; revenues</div>
            </div>
          </div>
          <i class="bi bi-chevron-right text-xs text-[#87847C]"></i>
        </a>

        <a href="/backup" class="flex items-center justify-between gap-4 p-4 rounded-xl border border-[#ECEAE4] bg-[#FAF9F6] hover:bg-[#F3F1EC] hover:border-[#D9D6CE] transition-colors no-underline">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-[#FBF3DB] border border-[#EAE0B8] text-[#8A5A00] flex items-center justify-center shrink-0">
              <i class="bi bi-cloud-arrow-up"></i>
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold text-[#1C1B18]">1-Click Google Drive Backup</div>
              <div class="text-xs text-[#78746C] truncate">Export data to JSON &amp; secure cloud sync</div>
            </div>
          </div>
          <i class="bi bi-chevron-right text-xs text-[#87847C]"></i>
        </a>
      </div>
    </div>
  </div>
</div>
