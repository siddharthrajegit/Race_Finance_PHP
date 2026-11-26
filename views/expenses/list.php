<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManageExp = in_array('*', $userPermissions ?? [], true) || in_array('manage_expenses', $userPermissions ?? [], true);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-wallet2 text-danger me-2"></i>Business Expenses</h3>
    <small class="text-muted">Record operating expenses, categories, and receipt attachments</small>
  </div>
  <?php if ($canManageExp): ?>
    <a href="/expenses/create" class="btn btn-primary btn-sm fw-semibold">
      <i class="bi bi-plus-lg me-1"></i> Record Expense
    </a>
  <?php endif; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm"><div class="card-body py-3">
      <div class="small text-muted">Filtered Expense Entries</div>
      <div class="fs-4 fw-bold text-dark"><?= (int)($summary['total_count'] ?? 0) ?></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm"><div class="card-body py-3">
      <div class="small text-muted">Total Expense Outflow</div>
      <div class="fs-4 fw-bold text-danger"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)($summary['total_amount'] ?? 0), 2) ?></div>
    </div></div>
  </div>
</div>

<!-- Filters -->
<form action="/expenses" method="GET" class="card shadow-sm border-0 mb-3">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small mb-1">Category</label>
        <select name="category" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>" <?= (($filters['category'] ?? '') === $c['name']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">From Date</label>
        <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">To Date</label>
        <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
        <a href="/expenses" class="btn btn-outline-secondary btn-sm">Reset</a>
      </div>
    </div>
  </div>
</form>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.875rem;">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Category</th>
          <th>Payee / Vendor</th>
          <th>Mode &amp; Ref</th>
          <th class="text-end">Amount</th>
          <th>Receipt</th>
          <?php if ($canManageExp): ?><th class="text-end">Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($expenses)): ?>
          <tr><td colspan="<?= $canManageExp ? 8 : 7 ?>" class="text-center py-4 text-muted">No expenses found.</td></tr>
        <?php else: ?>
          <?php foreach ($expenses as $exp): ?>
            <tr>
              <td class="font-monospace fw-bold">#<?= htmlspecialchars($exp['expense_number'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(formatDate($exp['expense_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($exp['category'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><?= htmlspecialchars($exp['payee'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small text-muted text-uppercase">
                <?= htmlspecialchars($exp['payment_mode'] ?? 'cash', ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($exp['reference_no'])): ?>
                  <span class="text-dark ms-1">(#<?= htmlspecialchars($exp['reference_no'], ENT_QUOTES, 'UTF-8') ?>)</span>
                <?php endif; ?>
              </td>
              <td class="text-end fw-bold text-danger"><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$exp['amount'], 2) ?></td>
              <td>
                <?php if (!empty($exp['attachment_path'])): ?>
                  <a href="<?= htmlspecialchars($exp['attachment_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2">
                    <i class="bi bi-paperclip"></i> View
                  </a>
                <?php else: ?>
                  <span class="text-muted small">--</span>
                <?php endif; ?>
              </td>
              <?php if ($canManageExp): ?>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="/expenses/edit/<?= (int)$exp['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2"><i class="bi bi-pencil"></i></a>
                    <form action="/expenses/delete/<?= (int)$exp['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Delete expense #<?= htmlspecialchars($exp['expense_number'], ENT_QUOTES, 'UTF-8') ?>?">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"><i class="bi bi-trash"></i></button>
                    </form>
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
