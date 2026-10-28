<!-- Floating / Sticky Bulk Action Bar -->
<div class="bulk-action-bar card bg-dark text-white shadow-lg p-2 px-3 mb-2 rounded-3 d-none align-items-center justify-content-between flex-wrap gap-2" id="bulkActionBar_<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>">
  <div class="d-flex align-items-center gap-2">
    <span class="badge bg-warning text-dark fw-bold fs-6 bulk-selected-counter">0</span>
    <span class="small fw-semibold">records selected</span>
    <button type="button" class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none ms-2 bulk-select-all-filtered-btn" style="font-size: 0.8rem;">Select All Visible</button>
    <span class="text-white-50">&bull;</span>
    <button type="button" class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none bulk-clear-selection-btn" style="font-size: 0.8rem;">Clear Selection</button>
  </div>
  <div class="d-flex align-items-center gap-2">
    <button type="button" class="btn btn-success btn-sm fw-semibold rounded-pill px-3 shadow-sm bulk-print-btn" data-target-table="<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>" data-doc-type="<?= ($paymentType ?? '') === 'payment_out' ? 'purchase' : 'sale' ?>">
      <i class="bi bi-printer me-1"></i> Print Selected (Bulk)
    </button>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100" id="<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 0.85rem;">
        <thead class="table-light">
          <tr>
            <th style="width: 32px; padding: 0.5rem 0.45rem;" class="text-center">
              <input type="checkbox" class="form-check-input bulk-select-all" data-target-table="<?= htmlspecialchars($tableId ?? 'invoiceTable', ENT_QUOTES, 'UTF-8') ?>" title="Select / Deselect All Visible">
            </th>
            <th style="padding: 0.5rem 0.65rem;" class="sortable-th user-select-none text-nowrap" data-sort-key="invoice-number" role="button" title="Click to sort by Invoice Number">
              <span>Invoice #</span>
              <span class="sort-icon ms-1 text-muted small"><i class="bi bi-arrow-down-up"></i></span>
            </th>
            <th style="padding: 0.5rem 0.65rem;" class="sortable-th user-select-none text-nowrap" data-sort-key="invoice-date" role="button" title="Click to sort by Date">
              <span>Date</span>
              <span class="sort-icon ms-1 text-muted small"><i class="bi bi-arrow-down-up"></i></span>
            </th>
            <th style="padding: 0.5rem 0.65rem;" class="sortable-th user-select-none text-nowrap" data-sort-key="party-name" role="button" title="Click to sort by <?= htmlspecialchars($partyLabel ?? 'Party', ENT_QUOTES, 'UTF-8') ?>">
              <span><?= htmlspecialchars($partyLabel ?? 'Party', ENT_QUOTES, 'UTF-8') ?></span>
              <span class="sort-icon ms-1 text-muted small"><i class="bi bi-arrow-down-up"></i></span>
            </th>
            <th style="padding: 0.5rem 0.4rem;" class="text-center text-nowrap">Type</th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end sortable-th user-select-none text-nowrap" data-sort-key="grand-total" role="button" title="Click to sort by Amount">
              <span>Total (₹)</span>
              <span class="sort-icon ms-1 text-muted small"><i class="bi bi-arrow-down-up"></i></span>
            </th>
            <th style="padding: 0.5rem 0.65rem;" class="text-end text-nowrap">Paid / Balance</th>
            <th style="width: 45px; padding: 0.5rem 0.25rem;" class="text-center text-nowrap">Status</th>
            <th style="width: 36px; padding: 0.5rem 0.35rem;" class="text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($invoices)): ?>
            <?php foreach ($invoices as $inv): ?>
              <tr data-invoice-number="<?= htmlspecialchars((string)($inv['invoice_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                  data-invoice-date="<?= htmlspecialchars((string)($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                  data-party-name="<?= htmlspecialchars(mb_strtolower($inv['party_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                  data-grand-total="<?= (float)($inv['grand_total'] ?? 0) ?>">
                <td style="width: 32px; padding: 0.45rem 0.45rem;" class="text-center">
                  <input type="checkbox" class="form-check-input invoice-row-checkbox" value="<?= (int)$inv['id'] ?>" data-invoice-id="<?= (int)$inv['id'] ?>">
                </td>
                <td style="padding: 0.45rem 0.65rem;">
                  <a href="/invoices/view/<?= $inv['id'] ?>" class="fw-bold font-monospace text-decoration-none text-primary">
                    <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td style="padding: 0.45rem 0.65rem;">
                  <div class="text-dark small text-nowrap"><?= htmlspecialchars(formatDate($inv['invoice_date']), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td style="padding: 0.45rem 0.65rem;">
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;"><?= htmlspecialchars($inv['party_name'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($inv['party_phone'])): ?>
                    <div class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.4rem;" class="text-center">
                  <?php if (!empty($inv['is_gst_bill'])): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem; padding: 0.2em 0.45em;">GST</span>
                  <?php else: ?>
                    <span class="badge bg-light text-secondary border" style="font-size: 0.68rem; padding: 0.2em 0.45em;">Non-GST</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end fw-bold text-nowrap">₹ <?= number_format((float)$inv['grand_total'], 2) ?></td>
                <td style="padding: 0.45rem 0.65rem;" class="text-end text-nowrap">
                  <div class="text-success fw-medium" style="font-size: 0.8rem;">Paid: ₹ <?= number_format((float)($inv['paid_amount'] ?? 0), 2) ?></div>
                  <?php if ((float)($inv['balance_due'] ?? 0) > 0.001): ?>
                    <div class="text-danger fw-semibold" style="font-size: 0.72rem;">Due: ₹ <?= number_format((float)$inv['balance_due'], 2) ?></div>
                  <?php else: ?>
                    <div class="text-muted" style="font-size: 0.72rem;">Cleared</div>
                  <?php endif; ?>
                </td>
                <td style="width: 45px; padding: 0.45rem 0.25rem;" class="text-center">
                  <?php if ($inv['payment_status'] === 'paid'): ?>
                    <span class="badge badge-status badge-status-paid" style="font-size: 0.72rem; padding: 0.2em 0.45em; min-width: 24px; display: inline-block;" title="Paid">P</span>
                  <?php elseif ($inv['payment_status'] === 'partial'): ?>
                    <span class="badge badge-status badge-status-partial" style="font-size: 0.72rem; padding: 0.2em 0.45em; min-width: 24px; display: inline-block;" title="Partially Paid">PP</span>
                  <?php else: ?>
                    <span class="badge badge-status badge-status-unpaid" style="font-size: 0.72rem; padding: 0.2em 0.45em; min-width: 24px; display: inline-block;" title="Unpaid">UP</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 0.45rem 0.35rem;" class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-light btn-sm p-1 border-0 rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions" style="width: 28px; height: 28px; line-height: 1;">
                      <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1">
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center text-primary fw-semibold" href="/invoices/download/<?= $inv['id'] ?>">
                          <i class="bi bi-download text-primary me-2"></i> Download Record
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/invoices/view/<?= $inv['id'] ?>">
                          <i class="bi bi-eye text-secondary me-2"></i> View Record
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/invoices/edit/<?= $inv['id'] ?>">
                          <i class="bi bi-pencil-square text-warning me-2"></i> Edit Record
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/invoices/print/<?= $inv['id'] ?>" target="_blank">
                          <i class="bi bi-printer text-success me-2"></i> Print Digital Record
                        </a>
                      </li>
                      <?php if (!empty($inv['party_id'])): ?>
                        <li>
                          <a class="dropdown-item py-1 px-3 small d-flex align-items-center" href="/payments/create?party_id=<?= $inv['party_id'] ?>&type=<?= $paymentType ?? 'payment_in' ?>">
                            <i class="bi bi-cash-stack text-warning me-2"></i> <?= htmlspecialchars($paymentLabel ?? 'Record Payment', ENT_QUOTES, 'UTF-8') ?>
                          </a>
                        </li>
                      <?php endif; ?>
                      <li><hr class="dropdown-divider my-1"></li>
                      <li>
                        <form action="/invoices/delete/<?= $inv['id'] ?>" method="POST" class="m-0 form-delete-confirm" data-confirm-message="<?= htmlspecialchars(($deleteMessagePrefix ?? 'Delete') . ' ' . $inv['invoice_number'] . '? ' . ($deleteMessageSuffix ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="dropdown-item py-1 px-3 small d-flex align-items-center text-danger border-0 bg-transparent w-100">
                            <i class="bi bi-trash me-2"></i> <?= htmlspecialchars($deleteLabel ?? 'Delete Record', ENT_QUOTES, 'UTF-8') ?>
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
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="bi <?= htmlspecialchars($emptyIcon ?? 'bi-inbox', ENT_QUOTES, 'UTF-8') ?> fs-2 d-block mb-2 text-secondary"></i>
                <?= $emptyMessage ?? 'No records found' ?>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
