<?php
$curr = $firmSettings['general']['currency_symbol'] ?? '₹';
$dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY';
$canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_accounting', $userPermissions ?? [], true);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <div>
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-book text-primary me-2"></i>Double-Entry Journal Ledger</h3>
    <small class="text-muted">Immutable balanced postings (historical adjustments use offsetting reversal entries)</small>
  </div>
  <div class="d-flex gap-2">
    <a href="/accounting" class="btn btn-outline-secondary btn-sm">Chart of Accounts</a>
    <?php if ($canManage): ?>
      <a href="/accounting/journals/create" class="btn btn-primary btn-sm fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> Post Manual Journal
      </a>
    <?php endif; ?>
  </div>
</div>

<form action="/accounting/journals" method="GET" class="card shadow-sm border-0 mb-3">
  <div class="card-body p-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-1">Source</label>
        <select name="source_type" class="form-select form-select-sm">
          <option value="">All Sources</option>
          <?php foreach (['invoice', 'payment', 'expense', 'manual', 'reversal'] as $st): ?>
            <option value="<?= $st ?>" <?= (($filters['source_type'] ?? '') === $st) ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
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
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
        <a href="/accounting/journals" class="btn btn-outline-secondary btn-sm">Reset</a>
      </div>
    </div>
  </div>
</form>

<?php if (empty($entries)): ?>
  <div class="card shadow-sm border-0 p-4 text-center text-muted">
    No journal entries found for this filter. Use <strong>Sync Missing Historical Journals</strong> under Chart of Accounts or create a new invoice/payment/manual journal.
  </div>
<?php else: ?>
  <?php foreach ($entries as $je): ?>
    <div class="card shadow-sm border-0 mb-2">
      <div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <span class="font-monospace fw-bold text-primary me-2">JE #<?= htmlspecialchars($je['entry_number'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="small text-muted me-2"><?= htmlspecialchars(formatDate($je['entry_date'], $dateFmt), ENT_QUOTES, 'UTF-8') ?></span>
          <span class="badge bg-light text-dark border text-uppercase"><?= htmlspecialchars($je['source_type'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php if (!empty($je['is_reversed'])): ?>
            <span class="badge bg-danger-subtle text-danger border ms-1">Reversed</span>
          <?php endif; ?>
          <span class="small fw-semibold ms-2"><?= htmlspecialchars($je['narration'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">Total: <strong><?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?> <?= number_format((float)$je['total_debit'], 2) ?></strong></span>
          <?php if ($canManage && empty($je['is_reversed']) && $je['source_type'] !== 'reversal'): ?>
            <form action="/accounting/journals/reverse/<?= (int)$je['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="Post an offsetting reversal entry for JE #<?= htmlspecialchars($je['entry_number'], ENT_QUOTES, 'UTF-8') ?>?">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="btn btn-outline-warning btn-sm py-0 px-2 text-dark">Reverse</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size: 0.84rem;">
          <tbody>
            <?php foreach ($je['lines'] as $ln): ?>
              <tr>
                <td class="ps-3 font-monospace text-muted" style="width: 80px;"><?= htmlspecialchars($ln['account_code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="<?= (float)$ln['credit'] > 0 ? 'ps-4' : 'fw-semibold' ?>">
                  <?= htmlspecialchars($ln['account_name'], ENT_QUOTES, 'UTF-8') ?>
                  <?php if (!empty($ln['description'])): ?>
                    <span class="small text-muted ms-2">&mdash; <?= htmlspecialchars($ln['description'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end" style="width: 140px;"><?= (float)$ln['debit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['debit'], 2) : '' ?></td>
                <td class="text-end pe-3" style="width: 140px;"><?= (float)$ln['credit'] > 0 ? htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$ln['credit'], 2) : '' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
