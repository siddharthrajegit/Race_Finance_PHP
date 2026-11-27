<?php
$invList = !empty($invoices) ? $invoices : [];
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">Purchase Records</h1>
      <p class="text-sm text-[#5C5A54] mt-1">Record inward purchases from suppliers and automatically track inventory additions</p>
    </div>
    <a href="/purchases/create" class="rf-btn rf-btn-primary">
      <i class="bi bi-plus-circle"></i> Record New Purchase
    </a>
  </div>

  <!-- Filter Toolbar -->
  <div class="rf-card p-4">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
      <div class="lg:col-span-4 relative">
        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-xs"></i>
        <input type="text" class="rf-input pl-9 py-2 text-xs" placeholder="Search purchases by record no, supplier, phone..." data-table-search="purchasesTable">
      </div>

      <div class="lg:col-span-4 flex items-center gap-2">
        <span class="text-xs font-medium text-[#78746C] shrink-0"><i class="bi bi-calendar-range mr-1"></i>Date</span>
        <input type="date" class="rf-input rf-input-sm" data-date-filter-from="purchasesTable" title="Filter From Date">
        <span class="text-xs text-[#87847C]">&rarr;</span>
        <input type="date" class="rf-input rf-input-sm" data-date-filter-to="purchasesTable" title="Filter To Date">
        <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm px-2.5" data-date-filter-clear="purchasesTable" title="Clear Date Filter">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="lg:col-span-4 flex items-center gap-2.5 justify-end">
        <div class="relative flex-1">
          <select class="rf-select rf-select-sm" data-table-sort="purchasesTable">
            <option value="">Sort: Default (Newest)</option>
            <option value="invoice-date-desc">Date (Newest First)</option>
            <option value="invoice-date-asc">Date (Oldest First)</option>
            <option value="invoice-number-asc">Invoice # (0 &rarr; 9)</option>
            <option value="invoice-number-desc">Invoice # (9 &rarr; 0)</option>
            <option value="party-name-asc">Supplier (A &rarr; Z)</option>
            <option value="party-name-desc">Supplier (Z &rarr; A)</option>
            <option value="grand-total-desc">Amount (High &rarr; Low)</option>
            <option value="grand-total-asc">Amount (Low &rarr; High)</option>
          </select>
        </div>
        <span class="rf-badge rf-badge-neutral py-1.5 px-3 tabular-nums shrink-0">
          <i class="bi bi-layers"></i>
          <strong id="visibleRecordsCount_purchasesTable"><?= count($invList) ?></strong>
        </span>
      </div>
    </div>
  </div>

  <?php
  $tableId = 'purchasesTable';
  $partyLabel = 'Supplier';
  $paymentType = 'payment_out';
  $paymentLabel = 'Pay Supplier';
  $deleteLabel = 'Delete Record';
  $deleteMessagePrefix = 'Are you sure you want to delete purchase record';
  $deleteMessageSuffix = 'Item inventory stock and party balances will be adjusted automatically.';
  $emptyIcon = 'bi-bag-plus';
  $emptyMessage = 'No purchase records recorded yet. Click <strong class="text-[#1C1B18]">Record New Purchase</strong> to add your first supplier entry.';
  include ROOT_DIR . '/views/partials/invoice_list_table.php';
  ?>
</div>
