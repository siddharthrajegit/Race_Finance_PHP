<?php include __DIR__ . '/_nav.php'; ?>

<!-- Filter & Search Toolbar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
  <div class="card-body p-3">
    <form action="/admin/invoices" method="GET" class="d-flex flex-wrap align-items-center gap-2">
      <div class="input-group input-group-sm" style="max-width: 340px;">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="search" class="form-control border-start-0" placeholder="Search record #, party, firm, phone..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <select name="type" class="form-select form-select-sm" style="max-width: 140px;" onchange="this.form.submit()">
        <option value="">All Types</option>
        <option value="sale" <?= ($type ?? '') === 'sale' ? 'selected' : '' ?>>Sales Only</option>
        <option value="purchase" <?= ($type ?? '') === 'purchase' ? 'selected' : '' ?>>Purchases Only</option>
      </select>

      <select name="payment_status" class="form-select form-select-sm" style="max-width: 140px;" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="paid" <?= ($payment_status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
        <option value="partial" <?= ($payment_status ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
        <option value="unpaid" <?= ($payment_status ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      </select>

      <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Filter</button>
      <?php if (!empty($search) || !empty($type) || !empty($payment_status)): ?>
        <a href="/admin/invoices" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Clear</a>
      <?php endif; ?>
      <span class="ms-auto badge bg-light text-dark border py-2 px-3">
        Showing <?= !empty($invoices) ? count($invoices) : 0 ?> latest records
      </span>
    </form>
  </div>
</div>

<!-- Records Table -->
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>Record #</th>
          <th>Firm &amp; Subscriber</th>
          <th>Party Details</th>
          <th>Date</th>
          <th class="text-center">Type</th>
          <th class="text-end">Total Amount (₹)</th>
          <th class="text-end">Balance Due (₹)</th>
          <th class="text-center">Status</th>
          <th class="text-end px-3">Inspect</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($invoices)): ?>
          <?php foreach ($invoices as $inv): ?>
            <tr>
              <td class="font-monospace fw-bold text-primary">
                <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="text-decoration-none"><?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
              </td>
              <td>
                <div class="fw-bold text-dark"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-muted"><?= htmlspecialchars($inv['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($inv['user_phone'] ?? ($inv['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</div>
              </td>
              <td>
                <div class="fw-semibold text-dark"><?= htmlspecialchars($inv['party_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($inv['party_phone'])): ?><div class="small text-muted"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              </td>
              <td class="small text-muted font-monospace"><?= htmlspecialchars($inv['invoice_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-center">
                <?php if (($inv['type'] ?? '') === 'purchase'): ?>
                  <span class="badge bg-warning text-dark">PURCHASE</span>
                <?php else: ?>
                  <span class="badge bg-success">SALE</span>
                <?php endif; ?>
              </td>
              <td class="text-end fw-bold">₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?></td>
              <td class="text-end">
                <?php if ((float)($inv['balance_due'] ?? 0) > 0): ?>
                  <span class="text-danger fw-bold">₹ <?= number_format((float)($inv['balance_due'] ?? 0), 2) ?></span>
                <?php else: ?>
                  <span class="text-success small fw-semibold"><i class="bi bi-check-all"></i> Cleared</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (($inv['payment_status'] ?? '') === 'paid'): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">PAID</span>
                <?php elseif (($inv['payment_status'] ?? '') === 'partial'): ?>
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">PARTIAL</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">UNPAID</span>
                <?php endif; ?>
              </td>
              <td class="text-end px-3">
                <a href="/invoices/download/<?= htmlspecialchars((string)($inv['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3" target="_blank" title="Inspect &amp; View Record">
                  <i class="bi bi-eye-fill me-1"></i> Inspect
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
              No records found matching your filter criteria.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
