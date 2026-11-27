<?php
$fType = $filterType ?? 'all';
?>
<div class="rf-page-header">
  <div>
    <h1 class="rf-page-title">Parties &amp; Customers</h1>
    <p class="rf-page-subtitle">Manage customer ledgers, vendor accounts, and outstanding balances</p>
  </div>
  <div class="d-flex gap-2">
    <a href="/parties/create?type=customer" class="btn btn-primary">
      <i class="bi bi-person-plus me-1"></i> Add Customer
    </a>
    <a href="/parties/create?type=supplier" class="btn btn-outline-primary">
      <i class="bi bi-truck me-1"></i> Add Supplier
    </a>
  </div>
</div>

<!-- Tabs & Search Filter -->
<div class="card mb-3">
  <div class="card-body p-2 p-md-3">
    <div class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="btn-group" role="group">
          <a href="/parties" class="btn btn-sm <?= (empty($fType) || $fType === 'all') ? 'btn-primary' : 'btn-outline-secondary' ?>">All Parties</a>
          <a href="/parties?type=customer" class="btn btn-sm <?= ($fType === 'customer') ? 'btn-primary' : 'btn-outline-secondary' ?>">Customers</a>
          <a href="/parties?type=supplier" class="btn btn-sm <?= ($fType === 'supplier') ? 'btn-primary' : 'btn-outline-secondary' ?>">Suppliers</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="d-flex gap-2">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0" placeholder="Search parties by name, phone, or GSTIN..." data-table-search="partiesTable">
          </div>
          <span class="badge bg-light text-dark border p-2 px-3 text-nowrap d-inline-flex align-items-center">
            Total: <strong class="ms-1" id="visibleRecordsCount_partiesTable"><?= count($parties ?? []) ?></strong>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Parties Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100" id="partiesTable">
        <thead>
          <tr>
            <th>Party Name</th>
            <th class="text-center">Type</th>
            <th>Contact Details</th>
            <th>GSTIN</th>
            <th>State</th>
            <th class="text-end">Sales / Due</th>
            <th style="width: 44px;" class="text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($parties)): ?>
            <?php foreach ($parties as $p): ?>
              <?php $sum = $summaryMap[$p['id']] ?? []; ?>
              <tr>
                <td>
                  <a href="/parties/ledger/<?= $p['id'] ?>" class="fw-bold text-decoration-none text-dark">
                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                  <?php if (!empty($p['city'])): ?>
                    <div class="small text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($p['city'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($p['type'] === 'customer'): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Customer</span>
                  <?php elseif ($p['type'] === 'supplier'): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Supplier</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border">Both</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($p['phone'])): ?><div class="small text-dark"><i class="bi bi-telephone text-muted me-1"></i> <?= htmlspecialchars($p['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  <?php if (!empty($p['email'])): ?><div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($p['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </td>
                <td class="font-monospace text-muted small"><?= htmlspecialchars($p['gstin'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="small text-muted"><?= htmlspecialchars($p['state'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end text-nowrap">
                  <div class="fw-semibold text-dark">₹ <?= number_format((float)($sum['total_sales'] ?? 0), 2) ?></div>
                  <?php if ((float)($sum['sales_due'] ?? 0) > 0.001): ?>
                    <div class="text-danger fw-semibold" style="font-size: 0.75rem;">Due: ₹ <?= number_format((float)$sum['sales_due'], 2) ?></div>
                  <?php else: ?>
                    <div class="text-success" style="font-size: 0.75rem;">No Dues</div>
                  <?php endif; ?>
                </td>
                <!-- 3-Dot Action Menu -->
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-light btn-sm p-1 border-0 rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" style="width: 30px; height: 30px; line-height: 1;">
                      <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item small d-flex align-items-center" href="/parties/ledger/<?= $p['id'] ?>">
                          <i class="bi bi-journal-text text-primary me-2"></i> View Statement / Ledger
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item small d-flex align-items-center" href="/payments/create?party_id=<?= $p['id'] ?>&type=<?= ($p['type'] === 'supplier') ? 'payment_out' : 'payment_in' ?>">
                          <i class="bi bi-cash-stack text-success me-2"></i> <?= ($p['type'] === 'supplier') ? 'Pay Supplier' : 'Record Payment' ?>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item small d-flex align-items-center" href="/parties/edit/<?= $p['id'] ?>">
                          <i class="bi bi-pencil text-secondary me-2"></i> Edit Party Details
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                No parties found. Click <strong>"Add Customer"</strong> or <strong>"Add Supplier"</strong> to get started.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
