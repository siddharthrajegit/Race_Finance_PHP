<?php include __DIR__ . '/_nav.php'; ?>

<!-- Search & Filter Toolbar -->
<div class="rf-card mb-6">
  <div class="rf-card-body">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
      <form action="/admin/firms" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
        <div class="relative w-full sm:w-80">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none">
            <i class="bi bi-search text-xs"></i>
          </span>
          <input type="text" name="search" class="rf-input rf-input-sm pl-8" placeholder="Search firm, GSTIN, city, owner..." value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Search Firms</button>
        <?php if (!empty($search)): ?>
          <a href="/admin/firms" class="rf-btn rf-btn-ghost rf-btn-sm">
            <i class="bi bi-x-circle"></i> Clear
          </a>
        <?php endif; ?>
      </form>
      <div class="text-xs text-ink-muted font-mono shrink-0">
        Showing <strong class="text-ink"><?= count($firms ?? []) ?></strong> registered business firms
      </div>
    </div>
  </div>
</div>

<!-- Firms Directory Table -->
<div class="rf-card overflow-hidden">
  <div class="rf-table-wrap border-0 rounded-none">
    <table class="rf-table">
      <thead>
        <tr>
          <th class="w-16 text-center">ID</th>
          <th>Business Firm</th>
          <th>Subscriber / Owner</th>
          <th>GSTIN / Tax Info</th>
          <th>Bank / UPI</th>
          <th class="text-center">Parties / Items</th>
          <th class="text-center">Invoices</th>
          <th class="text-right">Turnover (₹)</th>
          <th class="text-right">Inspect</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($firms)): ?>
          <?php foreach ($firms as $f): ?>
            <tr>
              <td class="text-center font-mono text-xs text-ink-muted">#<?= htmlspecialchars((string)($f['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div class="flex items-center gap-2.5">
                  <?php if (!empty($f['logo_path'])): ?>
                    <img src="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-9 h-9 rounded-lg border border-border bg-white object-contain p-0.5 shrink-0">
                  <?php else: ?>
                    <div class="w-9 h-9 rounded-lg bg-canvas-alt border border-border text-ink-muted font-semibold text-xs flex items-center justify-center shrink-0">
                      <?= strtoupper(substr($f['name'] ?? 'F', 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                  <div class="min-w-0">
                    <div class="font-medium text-ink truncate"><?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-xs text-ink-muted truncate">
                      <?= htmlspecialchars(trim(($f['city'] ?? '') . (!empty($f['city']) && !empty($f['state']) ? ', ' : '') . ($f['state'] ?? '')), ENT_QUOTES, 'UTF-8') ?: '--' ?>
                      <?= !empty($f['state_code']) ? '<span class="font-mono text-[11px]">(Code ' . htmlspecialchars((string)$f['state_code'], ENT_QUOTES, 'UTF-8') . ')</span>' : '' ?>
                    </div>
                  </div>
                </div>
              </td>
              <td class="text-xs">
                <a href="/admin/users/<?= htmlspecialchars((string)($f['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="font-medium text-ink hover:underline block">
                  <?= htmlspecialchars($f['owner_name'] ?? '--', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <div class="text-ink-muted font-mono text-[11px] mt-0.5">
                  <?= htmlspecialchars($f['owner_phone'] ?? ($f['owner_email'] ?? '--'), ENT_QUOTES, 'UTF-8') ?>
                </div>
              </td>
              <td class="font-mono text-xs">
                <?php if (!empty($f['gstin'])): ?>
                  <span class="rf-badge rf-badge-sage font-mono"><?= htmlspecialchars($f['gstin'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                  <span class="rf-badge rf-badge-neutral">Unregistered</span>
                <?php endif; ?>
              </td>
              <td class="text-xs font-mono text-ink-muted">
                <?php if (!empty($f['bank_account_no'])): ?>
                  <div>A/C: <strong class="text-ink"><?= htmlspecialchars($f['bank_account_no'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($f['upi_id'])): ?>
                  <div class="text-[11px]">UPI: <span class="text-ink"><?= htmlspecialchars($f['upi_id'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <?php endif; ?>
                <?php if (empty($f['bank_account_no']) && empty($f['upi_id'])): ?>
                  <span class="text-ink-subtle font-sans">Not Set</span>
                <?php endif; ?>
              </td>
              <td class="text-center font-mono text-xs">
                <span class="rf-badge rf-badge-neutral">
                  <?= (int)($f['parties_count'] ?? 0) ?>P &bull; <?= (int)($f['items_count'] ?? 0) ?>I
                </span>
              </td>
              <td class="text-center font-mono text-xs">
                <span class="rf-badge rf-badge-slate"><?= (int)($f['invoices_count'] ?? 0) ?></span>
              </td>
              <td class="text-right font-semibold font-mono text-ink whitespace-nowrap">
                ₹ <?= number_format((float)($f['total_turnover'] ?? 0), 0) ?>
              </td>
              <td class="text-right">
                <a href="/admin/users/<?= htmlspecialchars((string)($f['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm">
                  <i class="bi bi-person-badge"></i> Owner
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center py-12 text-ink-muted">
              <i class="bi bi-buildings text-3xl block mb-2 text-ink-subtle"></i>
              No business firms found matching your search.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
