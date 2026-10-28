<?php
$invList = !empty($invoices) ? $invoices : [];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold mb-1">Purchase Records</h3>
    <p class="text-muted small mb-0">Record inward purchases from suppliers and automatically track inventory additions</p>
  </div>
  <a href="/purchases/create" class="btn btn-primary shadow-sm">
    <i class="bi bi-plus-circle me-1"></i> Record New Purchase
  </a>
</div>

<div class="card invoice-filter-card mb-3">
  <div class="card-body p-2 p-md-3">
    <div class="row g-2 align-items-center">
      <!-- Search Input -->
      <div class="col-lg-4 col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search text-primary"></i></span>
          <input type="text" class="form-control border-start-0" placeholder="Search purchases by record no, supplier, phone..." data-table-search="purchasesTable">
        </div>
      </div>
      <!-- Custom Date Filter -->
      <div class="col-lg-5 col-md-7">
        <div class="input-group filter-date-group">
          <span class="input-group-text bg-white text-dark"><i class="bi bi-calendar-range text-primary me-1"></i> Date</span>
          <input type="date" class="form-control px-2" placeholder="From" data-date-filter-from="purchasesTable" title="Filter From Date">
          <span class="input-group-text bg-light text-muted px-2">&rarr;</span>
          <input type="date" class="form-control px-2" placeholder="To" data-date-filter-to="purchasesTable" title="Filter To Date">
          <button type="button" class="btn btn-light btn-clear-date border" data-date-filter-clear="purchasesTable" title="Clear Date Filter">
            <i class="bi bi-x-lg small"></i>
          </button>
        </div>
      </div>
      <!-- Sort Dropdown & Record Count -->
      <div class="col-lg-3 col-md-12">
        <div class="d-flex gap-2 align-items-center justify-content-lg-end">
          <div class="input-group flex-grow-1" style="min-width: 170px;">
            <span class="input-group-text bg-white text-dark"><i class="bi bi-arrow-down-up text-primary me-1"></i></span>
            <select class="form-select border-start-0" data-table-sort="purchasesTable">
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
          <span class="badge rounded-pill bg-light text-dark border p-2 px-3 text-nowrap d-inline-flex align-items-center shadow-xs">
            <i class="bi bi-layers text-primary me-1"></i>
            <strong id="visibleRecordsCount_purchasesTable"><?= count($invList) ?></strong>
          </span>
        </div>
      </div>
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
$emptyMessage = 'No purchase records recorded yet. Click <strong>"Record New Purchase"</strong> to add your first supplier entry.';
include ROOT_DIR . '/views/partials/invoice_list_table.php';
?>
