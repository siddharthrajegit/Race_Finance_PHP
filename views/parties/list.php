<?php
$fType = $filterType ?? 'all';
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Parties &amp; Customers</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Manage customer ledgers, vendor accounts, and outstanding balances</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
      <a href="/parties/create?type=customer" class="rf-btn rf-btn-primary">
        <i class="bi bi-person-plus"></i> Add Customer
      </a>
      <a href="/parties/create?type=supplier" class="rf-btn rf-btn-secondary">
        <i class="bi bi-truck"></i> Add Supplier
      </a>
    </div>
  </div>

  <!-- Tabs & Search Filter -->
  <div class="rf-card p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="inline-flex rounded-lg bg-[#F3F1EC] p-1 border border-[#E2DFD7]">
        <a href="/parties" class="px-3.5 py-1.5 rounded-md text-xs font-medium no-underline transition-colors <?= (empty($fType) || $fType === 'all') ? 'bg-white text-[#1C1B18] shadow-xs' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>">All Parties</a>
        <a href="/parties?type=customer" class="px-3.5 py-1.5 rounded-md text-xs font-medium no-underline transition-colors <?= ($fType === 'customer') ? 'bg-white text-[#1C1B18] shadow-xs' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>">Customers</a>
        <a href="/parties?type=supplier" class="px-3.5 py-1.5 rounded-md text-xs font-medium no-underline transition-colors <?= ($fType === 'supplier') ? 'bg-white text-[#1C1B18] shadow-xs' : 'text-[#5C5A54] hover:text-[#1C1B18]' ?>">Suppliers</a>
      </div>

      <div class="flex items-center gap-2.5 flex-1 max-w-md">
        <div class="relative flex-1">
          <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
          <input type="text" class="rf-input pl-9 py-2 text-xs" placeholder="Search parties by name, phone, or GSTIN..." data-table-search="partiesTable">
        </div>
        <span class="rf-badge rf-badge-neutral py-1.5 px-3 tabular-nums shrink-0">
          Total: <strong id="visibleRecordsCount_partiesTable" class="ml-1"><?= count($parties ?? []) ?></strong>
        </span>
      </div>
    </div>
  </div>

  <!-- Parties Table -->
  <div class="rf-card">
    <div class="rf-table-wrap">
      <table class="rf-table" id="partiesTable">
        <thead>
          <tr>
            <th>Party Name</th>
            <th class="text-center">Type</th>
            <th>Contact Details</th>
            <th>GSTIN</th>
            <th>State</th>
            <th class="text-right">Sales / Due</th>
            <th class="w-12 text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($parties)): ?>
            <?php foreach ($parties as $p): ?>
              <?php $sum = $summaryMap[$p['id']] ?? []; ?>
              <tr>
                <td>
                  <a href="/parties/ledger/<?= $p['id'] ?>" class="font-semibold text-[#1C1B18] hover:underline no-underline">
                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                  <?php if (!empty($p['city'])): ?>
                    <div class="text-xs text-[#78746C] mt-0.5"><?= htmlspecialchars($p['city'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($p['type'] === 'customer'): ?>
                    <span class="rf-badge rf-badge-sage">Customer</span>
                  <?php elseif ($p['type'] === 'supplier'): ?>
                    <span class="rf-badge rf-badge-slate">Supplier</span>
                  <?php else: ?>
                    <span class="rf-badge rf-badge-neutral">Both</span>
                  <?php endif; ?>
                </td>
                <td class="text-xs text-[#5C5A54]">
                  <?php if (!empty($p['phone'])): ?><div><i class="bi bi-telephone mr-1 text-[#87847C]"></i><?= htmlspecialchars($p['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  <?php if (!empty($p['email'])): ?><div class="text-[#78746C] mt-0.5"><i class="bi bi-envelope mr-1"></i><?= htmlspecialchars($p['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </td>
                <td class="font-mono text-xs text-[#5C5A54]"><?= htmlspecialchars($p['gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($p['state'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-right tabular-nums whitespace-nowrap">
                  <div class="font-semibold text-[#1C1B18]">₹ <?= number_format((float)($sum['total_sales'] ?? 0), 2) ?></div>
                  <?php if ((float)($sum['sales_due'] ?? 0) > 0.001): ?>
                    <div class="text-[11px] font-medium text-[#8F2D2B]">Due: ₹ <?= number_format((float)$sum['sales_due'], 2) ?></div>
                  <?php else: ?>
                    <div class="text-[11px] text-[#2E5A36]">No Dues</div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="rf-dropdown" data-ui-dropdown>
                    <button class="w-8 h-8 rounded-full hover:bg-[#EFECE6] inline-flex items-center justify-center text-[#5C5A54] bg-transparent border-0 cursor-pointer transition-colors" type="button" data-ui-dropdown-toggle aria-expanded="false" title="Actions">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="rf-dropdown-menu" data-ui-dropdown-menu>
                      <a class="rf-dropdown-item" href="/parties/ledger/<?= $p['id'] ?>">
                        <i class="bi bi-journal-text text-[#2B526E]"></i> View Statement / Ledger
                      </a>
                      <a class="rf-dropdown-item" href="/payments/create?party_id=<?= $p['id'] ?>&type=<?= ($p['type'] === 'supplier') ? 'payment_out' : 'payment_in' ?>">
                        <i class="bi bi-cash-stack text-[#2E5A36]"></i> <?= ($p['type'] === 'supplier') ? 'Pay Supplier' : 'Record Payment' ?>
                      </a>
                      <a class="rf-dropdown-item" href="/parties/edit/<?= $p['id'] ?>">
                        <i class="bi bi-pencil text-[#5C5A54]"></i> Edit Party Details
                      </a>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-14 text-[#78746C]">
                <i class="bi bi-people text-3xl block mb-2 text-[#B5B0A6]"></i>
                No parties found. Click <strong class="text-[#1C1B18]">Add Customer</strong> or <strong class="text-[#1C1B18]">Add Supplier</strong> to get started.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
