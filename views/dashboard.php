<!-- Active Firm Header Banner -->
<div class="card bg-white mb-4">
  <div class="card-body p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center">
        <?php if (!empty($firm['logo_path'])): ?>
          <img src="<?= htmlspecialchars($firm['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded-3 border p-1 me-3 bg-white" style="width: 54px; height: 54px; object-fit: contain;">
        <?php else: ?>
          <div class="bg-primary-subtle text-primary rounded-3 p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 1.5rem;">
            <i class="bi bi-buildings"></i>
          </div>
        <?php endif; ?>
        <div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($firm['name'], ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!empty($firm['gstin'])): ?>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">GSTIN: <?= htmlspecialchars($firm['gstin'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
              <span class="badge bg-light text-secondary border">Non-GST Firm</span>
            <?php endif; ?>
          </div>
          <p class="text-muted small mb-0 mt-1">
            <?php if (!empty($firm['phone'])): ?><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($firm['phone'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
            <?php if (!empty($firm['phone']) && !empty($firm['state'])): ?><span class="mx-2 text-secondary opacity-50">&bull;</span><?php endif; ?>
            <?php if (!empty($firm['state'])): ?><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($firm['state'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($firm['state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)<?php endif; ?>
          </p>
        </div>
      </div>

      <!-- Focused Quick Action Buttons -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="/sales/create" class="btn btn-success">
          <i class="bi bi-plus-circle me-1"></i> New Sale Bill
        </a>
        <a href="/purchases/create" class="btn btn-outline-primary">
          <i class="bi bi-bag-plus me-1"></i> New Purchase
        </a>
        <a href="/items/create" class="btn btn-outline-secondary">
          <i class="bi bi-box-seam me-1"></i> Add Item
        </a>
        <a href="/backup" class="btn btn-outline-secondary" title="Backup & Cloud">
          <i class="bi bi-cloud-arrow-up me-1"></i> Backup
        </a>
      </div>
    </div>
  </div>
</div>

<!-- KPI Cards Row -->
<div class="row g-3 mb-4">
  <!-- Today's Sales -->
  <div class="col-sm-6 col-xl-3">
    <a href="/sales" class="kpi-card-link">
      <div class="card kpi-card kpi-sales h-100 p-3">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="kpi-label">Today's Sales</div>
            <h3 class="kpi-value text-success tabular-nums">₹ <?= number_format((float)($summary['todaySales'] ?? 0), 2) ?></h3>
            <div class="text-muted small">Daily revenue <i class="bi bi-arrow-right-short"></i></div>
          </div>
          <div class="kpi-icon-wrap bg-success-subtle text-success">
            <i class="bi bi-currency-rupee"></i>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Total Sales -->
  <div class="col-sm-6 col-xl-3">
    <a href="/sales" class="kpi-card-link" title="Click to view all sales records">
      <div class="card kpi-card kpi-count h-100 p-3">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="kpi-label">Total Sales (<?= (int)($summary['sales']['total_sales_count'] ?? 0) ?>)</div>
            <h3 class="kpi-value text-dark tabular-nums">₹ <?= number_format((float)($summary['sales']['total_sales_amount'] ?? 0), 2) ?></h3>
            <div class="text-muted small">
              Received: <span class="text-success fw-semibold tabular-nums">₹ <?= number_format((float)($summary['sales']['total_sales_received'] ?? 0), 2) ?></span>
            </div>
          </div>
          <div class="kpi-icon-wrap bg-primary-subtle text-primary">
            <i class="bi bi-cart-check"></i>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Total Receivables (Customers Owe Us) -->
  <div class="col-sm-6 col-xl-3">
    <a href="/parties?type=customer" class="kpi-card-link" title="Click to view customer balances">
      <div class="card kpi-card kpi-receivable h-100 p-3">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="kpi-label">To Collect (Receivables)</div>
            <h3 class="kpi-value text-warning tabular-nums">₹ <?= number_format((float)($summary['sales']['total_receivables'] ?? 0), 2) ?></h3>
            <div class="text-muted small text-nowrap">Customer dues <i class="bi bi-arrow-right-short"></i></div>
          </div>
          <div class="kpi-icon-wrap bg-warning-subtle text-warning">
            <i class="bi bi-arrow-down-left-circle"></i>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Total Payables (We Owe Suppliers) -->
  <div class="col-sm-6 col-xl-3">
    <a href="/purchases" class="kpi-card-link" title="Click to view purchase records">
      <div class="card kpi-card kpi-payable h-100 p-3">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="kpi-label">To Pay (Payables)</div>
            <h3 class="kpi-value text-danger tabular-nums">₹ <?= number_format((float)($summary['purchases']['total_payables'] ?? 0), 2) ?></h3>
            <div class="text-muted small text-nowrap">Vendor dues <i class="bi bi-arrow-right-short"></i></div>
          </div>
          <div class="kpi-icon-wrap bg-danger-subtle text-danger">
            <i class="bi bi-arrow-up-right-circle"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<!-- Full-Width Recent Records Section -->
<div class="card mb-4">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
      <i class="bi bi-clock-history text-primary"></i> Recent Records
    </span>
    <div class="d-flex gap-2">
      <a href="/sales" class="btn btn-outline-secondary btn-sm">View All Sales</a>
      <a href="/sales/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Sales Record</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100">
        <thead>
          <tr>
            <th>Record &amp; Date</th>
            <th>Customer</th>
            <th class="text-center">Type</th>
            <th class="text-end">Total (₹)</th>
            <th class="text-end">Paid / Balance</th>
            <th class="text-center">Status</th>
            <th class="text-center" style="width: 44px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($summary['recentInvoices']) && count($summary['recentInvoices']) > 0): ?>
            <?php foreach ($summary['recentInvoices'] as $inv): ?>
              <tr>
                <td>
                  <a href="/invoices/view/<?= $inv['id'] ?>" class="fw-bold font-monospace text-decoration-none text-primary">
                    <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                  <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 190px;"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($inv['party_phone'])): ?>
                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if (!empty($inv['is_gst_bill'])): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">GST</span>
                  <?php else: ?>
                    <span class="badge bg-light text-secondary border">Non-GST</span>
                  <?php endif; ?>
                </td>
                <td class="text-end fw-bold text-nowrap">₹ <?= number_format((float)$inv['grand_total'], 2) ?></td>
                <td class="text-end text-nowrap">
                  <div class="text-success fw-medium" style="font-size: 0.82rem;">Paid: ₹ <?= number_format((float)($inv['paid_amount'] ?? 0), 2) ?></div>
                  <?php if ((float)($inv['balance_due'] ?? 0) > 0.001): ?>
                    <div class="text-danger fw-semibold" style="font-size: 0.75rem;">Due: ₹ <?= number_format((float)$inv['balance_due'], 2) ?></div>
                  <?php else: ?>
                    <div class="text-muted" style="font-size: 0.75rem;">Cleared</div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if (($inv['payment_status'] ?? '') === 'paid'): ?>
                    <span class="badge badge-status badge-status-paid" title="Paid">Paid</span>
                  <?php elseif (($inv['payment_status'] ?? '') === 'partial'): ?>
                    <span class="badge badge-status badge-status-partial" title="Partially Paid">Partial</span>
                  <?php else: ?>
                    <span class="badge badge-status badge-status-unpaid" title="Unpaid">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-light btn-sm p-1 border-0 rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" style="width: 30px; height: 30px; line-height: 1;">
                      <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item small d-flex align-items-center text-primary fw-semibold" href="/invoices/download/<?= $inv['id'] ?>">
                          <i class="bi bi-download text-primary me-2"></i> Download Record
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item small d-flex align-items-center" href="/invoices/view/<?= $inv['id'] ?>">
                          <i class="bi bi-eye text-secondary me-2"></i> View Record
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item small d-flex align-items-center" href="/invoices/edit/<?= $inv['id'] ?>">
                          <i class="bi bi-pencil-square text-warning me-2"></i> Edit Record
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item small d-flex align-items-center" href="/invoices/print/<?= $inv['id'] ?>" target="_blank">
                          <i class="bi bi-printer text-success me-2"></i> Print Digital Record
                        </a>
                      </li>
                      <?php if (!empty($inv['party_id'])): ?>
                        <li>
                          <a class="dropdown-item small d-flex align-items-center" href="/payments/create?party_id=<?= $inv['party_id'] ?>&type=payment_in">
                            <i class="bi bi-cash-stack text-warning me-2"></i> Record Payment
                          </a>
                        </li>
                      <?php endif; ?>
                      <li><hr class="dropdown-divider my-1"></li>
                      <li>
                        <form action="/invoices/delete/<?= $inv['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Are you sure you want to delete sales record <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>? Item inventory stock and party balances will be restored automatically.">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item small d-flex align-items-center text-danger border-0 bg-transparent w-100">
                            <i class="bi bi-trash me-2"></i> Delete Record
                          </button>
                        </form>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                No sales records generated yet. Click <strong>"New Sales Record"</strong> to log your first entry.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Bottom Section: Low Stock Alert & Quick Reports Columns -->
<div class="row g-4 mb-4">
  <!-- Low Stock Alert Card (Bottom Left) -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold fs-6 d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill text-warning"></i> Low Stock Alerts
        </span>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1"><?= (int)($summary['lowStockCount'] ?? 0) ?> items</span>
      </div>
      <div class="card-body p-0 d-flex flex-column">
        <?php if (!empty($summary['lowStockItems']) && count($summary['lowStockItems']) > 0): ?>
          <ul class="list-group list-group-flush flex-grow-1">
            <?php foreach (array_slice($summary['lowStockItems'], 0, 5) as $item): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 border-light">
                <div>
                  <div class="fw-semibold text-dark"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="small text-muted">Threshold: <?= htmlspecialchars((string)($item['low_stock_threshold'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?> &bull; Sale: ₹ <?= number_format((float)($item['sale_price'] ?? 0), 2) ?></div>
                </div>
                <div class="text-end">
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold px-2.5 py-2">
                    <?= htmlspecialchars((string)($item['current_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?> Left
                  </span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="p-3 text-center border-top bg-light rounded-bottom">
            <a href="/items" class="text-decoration-none small fw-semibold text-primary">View Full Inventory &amp; Adjust Stock &rarr;</a>
          </div>
        <?php else: ?>
          <div class="text-center py-5 px-3 text-muted my-auto">
            <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
            <div class="fw-semibold text-dark">All items are well stocked</div>
            <div class="small text-muted">No items are below their minimum threshold.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Quick Reports & Shortcuts (Bottom Right) -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header fw-bold fs-6 d-flex align-items-center gap-2">
        <i class="bi bi-lightning-charge-fill text-primary"></i> Quick Reports &amp; Utilities
      </div>
      <div class="card-body p-3">
        <div class="d-grid gap-2">
          <a href="/reports/parties" class="rf-quick-tile">
            <div class="d-flex align-items-center">
              <div class="rf-quick-tile-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
              <div>
                <div class="fw-semibold text-dark">Customer &amp; Party Statement</div>
                <div class="small text-muted">Ledgers, outstanding dues &amp; payment histories</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="/reports/tax" class="rf-quick-tile">
            <div class="d-flex align-items-center">
              <div class="rf-quick-tile-icon bg-success-subtle text-success"><i class="bi bi-file-earmark-text"></i></div>
              <div>
                <div class="fw-semibold text-dark">GSTR-1 Tax Summary</div>
                <div class="small text-muted">B2B, B2C taxable amounts &amp; GST breakdown</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="/reports/items" class="rf-quick-tile">
            <div class="d-flex align-items-center">
              <div class="rf-quick-tile-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam"></i></div>
              <div>
                <div class="fw-semibold text-dark">Item-wise Sales Report</div>
                <div class="small text-muted">Product sales volumes, margins &amp; revenues</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="/backup" class="rf-quick-tile">
            <div class="d-flex align-items-center">
              <div class="rf-quick-tile-icon bg-warning-subtle text-warning"><i class="bi bi-cloud-arrow-up"></i></div>
              <div>
                <div class="fw-semibold text-dark">1-Click Google Drive Backup</div>
                <div class="small text-muted">Export data to JSON &amp; secure cloud sync</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
