<?php
$invList = !empty($invoices) ? $invoices : [];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold mb-1">Sales Records</h3>
    <p class="text-muted small mb-0">Record GST and Non-GST digital sales references, track customer payments & ledger data</p>
  </div>
  <a href="/sales/create" class="btn btn-success shadow-sm">
    <i class="bi bi-plus-circle me-1"></i> Create New Sales Record
  </a>
</div>

<div class="card shadow-sm border-0 mb-3">
  <div class="card-body p-2 p-md-3">
    <div class="row g-2 align-items-center">
      <!-- Search Input -->
      <div class="col-lg-4 col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" placeholder="Search sales by record no, customer, phone..." data-table-search="salesTable">
        </div>
      </div>
      <!-- Custom Date Filter -->
      <div class="col-lg-4 col-md-7">
        <div class="input-group">
          <span class="input-group-text bg-white text-muted"><i class="bi bi-calendar3 me-1"></i> Date</span>
          <input type="date" class="form-control" placeholder="From" data-date-filter-from="salesTable" title="Filter From Date">
          <span class="input-group-text bg-light text-muted">to</span>
          <input type="date" class="form-control" placeholder="To" data-date-filter-to="salesTable" title="Filter To Date">
          <button type="button" class="btn btn-outline-secondary" data-date-filter-clear="salesTable" title="Clear Date Filter">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>
      <!-- Sort Dropdown & Record Count -->
      <div class="col-lg-4 col-md-12">
        <div class="d-flex gap-2 align-items-center justify-content-lg-end">
          <div class="input-group" style="max-width: 240px;">
            <span class="input-group-text bg-white text-muted"><i class="bi bi-arrow-down-up me-1"></i> Sort</span>
            <select class="form-select border-start-0" data-table-sort="salesTable">
              <option value="">Default (Date: Newest First)</option>
              <option value="invoice-date-desc">Date (Newest First)</option>
              <option value="invoice-date-asc">Date (Oldest First)</option>
              <option value="invoice-number-asc">Invoice # (Ascending 0 &rarr; 9)</option>
              <option value="invoice-number-desc">Invoice # (Descending 9 &rarr; 0)</option>
              <option value="party-name-asc">Customer Name (A &rarr; Z)</option>
              <option value="party-name-desc">Customer Name (Z &rarr; A)</option>
              <option value="grand-total-desc">Amount (Highest First)</option>
              <option value="grand-total-asc">Amount (Lowest First)</option>
            </select>
          </div>
          <span class="badge bg-light text-dark border p-2 text-nowrap">
            Records: <strong id="visibleRecordsCount_salesTable"><?= count($invList) ?></strong>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$tableId = 'salesTable';
$partyLabel = 'Customer';
$paymentType = 'payment_in';
$paymentLabel = 'Record Payment';
$deleteLabel = 'Delete Record';
$deleteMessagePrefix = 'Are you sure you want to delete sales record';
$deleteMessageSuffix = 'Item inventory stock and party balances will be restored automatically.';
$emptyIcon = 'bi-receipt-cutoff';
$emptyMessage = 'No sales records logged yet. Click <strong>"Create New Sales Record"</strong> to record your first entry.';
include ROOT_DIR . '/views/partials/invoice_list_table.php';
?>
