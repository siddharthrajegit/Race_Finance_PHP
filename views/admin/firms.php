<?php include __DIR__ . '/_nav.php'; ?>

<!-- Filter & Search Toolbar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <form action="/admin/firms" method="GET" class="d-flex align-items-center gap-2 flex-grow-1">
        <div class="input-group input-group-sm" style="max-width: 380px;">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="search" class="form-control border-start-0" placeholder="Search by firm name, GSTIN, owner..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Search</button>
        <?php if (!empty($search)): ?>
          <a href="/admin/firms" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Clear</a>
        <?php endif; ?>
      </form>
      <span class="badge bg-light text-dark border py-2 px-3">
        Total Businesses: <strong><?= !empty($firms) ? count($firms) : 0 ?></strong>
      </span>
    </div>
  </div>
</div>

<!-- Firms Table -->
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>Business Firm</th>
          <th>Subscriber / Owner</th>
          <th>GSTIN &amp; State</th>
          <th class="text-center">Bank / UPI</th>
          <th class="text-center">Parties</th>
          <th class="text-center">Items</th>
          <th class="text-center">Invoices</th>
          <th class="text-end px-3">Gross Turnover (₹)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($firms)): ?>
          <?php foreach ($firms as $f): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($f['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="rounded border p-1 bg-white" width="38" height="38" style="object-fit: contain;">
                  <?php else: ?>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                      <i class="bi bi-buildings"></i>
                    </div>
                  <?php endif; ?>
                  <div>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <small class="text-muted"><?= htmlspecialchars($f['city'] ?? ($f['state'] ?? 'Local'), ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                </div>
              </td>
              <td>
                <div class="small fw-semibold text-dark">
                  <a href="/admin/users/<?= htmlspecialchars((string)($f['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($f['owner_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="small text-muted font-monospace"><?= htmlspecialchars($f['owner_phone'] ?? ($f['owner_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
              </td>
              <td>
                <?php if (!empty($f['gstin'])): ?>
                  <div class="font-monospace small text-primary fw-bold"><?= htmlspecialchars($f['gstin'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php else: ?>
                  <span class="badge bg-secondary-subtle text-secondary small">Non-GST</span>
                <?php endif; ?>
                <div class="small text-muted"><?= !empty($f['state']) ? htmlspecialchars($f['state'] . ' (' . ($f['state_code'] ?? '--') . ')', ENT_QUOTES, 'UTF-8') : '--' ?></div>
              </td>
              <td class="text-center">
                <?php if (!empty($f['bank_account_no'])): ?>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle small" title="Bank Configured">Bank</span>
                <?php endif; ?>
                <?php if (!empty($f['upi_id'])): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle small" title="UPI Configured">UPI</span>
                <?php endif; ?>
                <?php if (empty($f['bank_account_no']) && empty($f['upi_id'])): ?>
                  <span class="text-muted small">--</span>
                <?php endif; ?>
              </td>
              <td class="text-center font-monospace small"><?= htmlspecialchars((string)($f['parties_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-center font-monospace small"><?= htmlspecialchars((string)($f['items_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-center font-monospace small"><?= htmlspecialchars((string)($f['invoices_count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="text-end px-3 fw-bold font-monospace text-dark">
                ₹ <?= number_format((float)($f['total_turnover'] ?? 0), 2) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="bi bi-buildings fs-1 d-block mb-2 text-secondary opacity-50"></i>
              No business firms found matching your search.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
