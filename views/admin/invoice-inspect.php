<?php
/**
 * Admin Invoice Inspect View
 * Read-only detailed inspection of any subscriber's invoice.
 * RACE FINANCE - Billing & Inventory System
 */

include __DIR__ . '/_nav.php';

$inv     = $invoice;
$isGst   = !empty($inv['is_gst_bill']);
$isPurch = ($inv['type'] ?? 'sale') === 'purchase';
$items   = $inv['items'] ?? [];
$due     = (float)($inv['balance_due'] ?? 0);
$status  = $inv['payment_status'] ?? 'unpaid';

$statusClass = match($status) {
    'paid'    => 'rf-badge-sage',
    'partial' => 'rf-badge-amber',
    default   => 'rf-badge-rose',
};
$statusLabel = strtoupper($status);
?>

<!-- Top Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <div>
    <a href="/admin/invoices" class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-muted hover:text-ink transition-colors">
      <i class="bi bi-arrow-left"></i> Back to Platform Records
    </a>
    <h2 class="text-xl font-semibold text-ink mt-1 mb-0 flex items-center gap-2">
      <i class="bi bi-file-earmark-text text-ink-muted"></i>
      <span>Invoice Inspector</span>
      <span class="font-mono text-ink-muted font-medium"><?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </h2>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <span class="rf-badge <?= $statusClass ?> px-3 py-1"><?= $statusLabel ?></span>
    <span class="rf-badge <?= $isPurch ? 'rf-badge-amber' : 'rf-badge-sage' ?> px-3 py-1">
      <?= $isPurch ? 'PURCHASE' : 'SALE' ?>
    </span>
    <?php if ($isGst): ?>
      <span class="rf-badge rf-badge-slate px-3 py-1">GST Bill</span>
    <?php endif; ?>
  </div>
</div>

<!-- Subscriber & Firm Info Banner -->
<div class="rf-card border-l-4 border-l-ink mb-6">
  <div class="rf-card-body">
    <div class="flex flex-col md:flex-row md:items-center gap-4">
      <div class="shrink-0">
        <span class="rf-badge rf-badge-amber">
          <i class="bi bi-shield-lock"></i> ADMIN VIEW
        </span>
      </div>
      <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-0.5">Subscriber</div>
          <div class="font-semibold text-ink text-sm"><?= htmlspecialchars($inv['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="text-xs text-ink-muted mt-0.5 flex flex-wrap items-center gap-1.5">
            <span><?= htmlspecialchars($inv['user_phone'] ?? ($inv['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <span>&bull;</span>
            <span class="rf-badge <?= ($inv['user_status'] ?? '') === 'active' ? 'rf-badge-sage' : 'rf-badge-rose' ?>">
              <?= strtoupper($inv['user_status'] ?? 'unknown') ?>
            </span>
          </div>
        </div>
        <div>
          <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-0.5">Business Firm</div>
          <div class="font-semibold text-ink text-sm"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($inv['firm_gstin'])): ?>
            <div class="text-xs text-ink-muted font-mono mt-0.5">GSTIN: <?= htmlspecialchars($inv['firm_gstin'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
        </div>
        <div class="md:text-right flex flex-col md:items-end justify-center">
          <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-1">Subscriber Profile</div>
          <a href="/admin/users/<?= (int)($inv['user_id'] ?? 0) ?>" class="rf-btn rf-btn-secondary rf-btn-sm">
            <i class="bi bi-person-badge"></i> View Subscriber
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Invoice Card -->
<div class="rf-card mb-6">
  <div class="rf-card-body p-6 md:p-8 space-y-6">

    <!-- Header: Firm & Invoice Meta -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pb-6 border-b border-border">
      <div class="md:col-span-7 space-y-1">
        <h3 class="text-lg font-semibold text-ink mb-1"><?= htmlspecialchars($inv['firm_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
        <?php if (!empty($inv['firm_gstin'])): ?>
          <div class="text-xs text-ink-muted font-mono">GSTIN: <strong class="text-ink"><?= htmlspecialchars($inv['firm_gstin'], ENT_QUOTES, 'UTF-8') ?></strong></div>
        <?php endif; ?>
        <div class="text-xs text-ink-muted space-y-0.5 leading-relaxed">
          <?php if (!empty($inv['firm_address'])): ?>
            <div><?= htmlspecialchars($inv['firm_address'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <?php
            $loc = array_filter([$inv['firm_city'] ?? '', $inv['firm_state'] ?? '']);
            if (!empty($loc)):
          ?>
            <div>
              <?= htmlspecialchars(implode(', ', $loc), ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($inv['firm_pincode'])): ?>
                - <?= htmlspecialchars($inv['firm_pincode'], ENT_QUOTES, 'UTF-8') ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($inv['firm_phone'])): ?><div>Phone: <?= htmlspecialchars($inv['firm_phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($inv['firm_email'])): ?><div>Email: <?= htmlspecialchars($inv['firm_email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>
      </div>

      <div class="md:col-span-5 md:text-right space-y-1.5">
        <div class="text-sm font-semibold uppercase tracking-wider text-ink">
          <?= $isGst ? ($isPurch ? 'GST Purchase Record' : 'GST Sales Record') : ($isPurch ? 'Purchase Record' : 'Sales Record') ?>
        </div>
        <div class="text-xs text-ink-muted space-y-1">
          <div><strong class="text-ink">Record No:</strong> <span class="font-mono text-ink"><?= htmlspecialchars($inv['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
          <div><strong class="text-ink">Date:</strong> <?= htmlspecialchars(formatDate($inv['invoice_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($inv['due_date'])): ?>
            <div><strong class="text-ink">Due Date:</strong> <?= htmlspecialchars(formatDate($inv['due_date']), ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div><strong class="text-ink">Payment Mode:</strong> <?= strtoupper(htmlspecialchars($inv['payment_mode'] ?? 'Cash', ENT_QUOTES, 'UTF-8')) ?></div>
          <div class="pt-1">
            <strong class="text-ink mr-1">Status:</strong>
            <span class="rf-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Party Section -->
    <div class="pb-6 border-b border-border">
      <div class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-1.5">
        <?= $isPurch ? 'Party (Supplier / Vendor)' : 'Party (Customer / Client)' ?>
      </div>
      <div class="text-base font-semibold text-ink"><?= htmlspecialchars($inv['party_name'] ?? 'Cash Sale', ENT_QUOTES, 'UTF-8') ?></div>
      <div class="text-xs text-ink-muted mt-1 space-y-0.5">
        <?php if (!empty($inv['party_phone'])): ?>
          <div>Phone: <?= htmlspecialchars($inv['party_phone'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($inv['party_gstin'])): ?>
          <div>GSTIN: <span class="font-mono font-medium text-ink"><?= htmlspecialchars($inv['party_gstin'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <?php endif; ?>
        <?php if (!empty($inv['party_address'])): ?>
          <div>Address: <?= htmlspecialchars($inv['party_address'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($inv['party_state'])): ?>
          <div>State: <?= htmlspecialchars($inv['party_state'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($inv['party_state_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?>)</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Line Items Table -->
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th class="text-center w-10">#</th>
            <th>Item Description</th>
            <th class="text-center">HSN/SAC</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Rate (₹)</th>
            <?php if ($isGst): ?>
              <th class="text-center">Disc %</th>
              <th class="text-center">GST %</th>
              <th class="text-right">Tax (₹)</th>
            <?php else: ?>
              <th class="text-center">Disc %</th>
            <?php endif; ?>
            <th class="text-right">Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): ?>
              <tr>
                <td class="text-center text-ink-muted text-xs"><?= $index + 1 ?></td>
                <td>
                  <div class="font-medium text-ink"><?= htmlspecialchars($item['item_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($item['description'])): ?>
                    <div class="text-ink-muted text-xs mt-0.5"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center font-mono text-xs text-ink-muted"><?= htmlspecialchars($item['hsn_code'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center whitespace-nowrap">
                  <strong class="font-mono"><?= number_format((float)($item['quantity'] ?? 0), 2) ?></strong>
                  <span class="text-ink-muted text-xs"><?= htmlspecialchars($item['unit'] ?? 'PCS', ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="text-right font-mono whitespace-nowrap">₹ <?= number_format((float)($item['rate'] ?? 0), 2) ?></td>
                <?php if ($isGst): ?>
                  <td class="text-center text-xs text-ink-muted font-mono">
                    <?= (float)($item['discount_percent'] ?? 0) > 0 ? number_format((float)$item['discount_percent'], 1) . '%' : '—' ?>
                  </td>
                  <td class="text-center text-xs font-mono">
                    <?= ((float)($item['tax_rate'] ?? 0) == (int)($item['tax_rate'] ?? 0)) ? number_format((float)($item['tax_rate'] ?? 0), 0) : (float)($item['tax_rate'] ?? 0) ?>%
                  </td>
                  <td class="text-right text-ink-muted text-xs font-mono whitespace-nowrap">
                    ₹ <?= number_format((float)($item['cgst_amount'] ?? 0) + (float)($item['sgst_amount'] ?? 0) + (float)($item['igst_amount'] ?? 0), 2) ?>
                  </td>
                <?php else: ?>
                  <td class="text-center text-xs text-ink-muted font-mono">
                    <?= (float)($item['discount_percent'] ?? 0) > 0 ? number_format((float)$item['discount_percent'], 1) . '%' : '—' ?>
                  </td>
                <?php endif; ?>
                <td class="text-right font-semibold text-ink font-mono whitespace-nowrap">₹ <?= number_format((float)($item['total_amount'] ?? 0), 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= $isGst ? 9 : 7 ?>" class="text-center text-ink-muted py-8">No line items found for this invoice.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Totals & Notes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
      <div class="space-y-4">
        <?php if (!empty($inv['notes'])): ?>
          <div>
            <h6 class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-1">Notes / Remarks</h6>
            <p class="text-xs text-ink-muted mb-0 leading-relaxed"><?= htmlspecialchars($inv['notes'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php endif; ?>
        <?php if (!empty($inv['terms'])): ?>
          <div>
            <h6 class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-1">Terms &amp; Conditions</h6>
            <div class="text-xs text-ink-muted leading-relaxed whitespace-pre-line"><?= htmlspecialchars($inv['terms'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <?php if ($isGst && (float)($inv['tax_amount'] ?? 0) > 0): ?>
          <div>
            <h6 class="text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-2">GST Breakdown</h6>
            <div class="rf-table-wrap max-w-xs">
              <table class="rf-table text-center">
                <thead>
                  <tr>
                    <th>Tax Head</th>
                    <th>Rate</th>
                    <th class="text-right">Amount (₹)</th>
                  </tr>
                </thead>
                <tbody class="text-xs">
                  <?php if (!empty($inv['is_interstate'])): ?>
                    <tr>
                      <td>IGST</td>
                      <td>—</td>
                      <td class="text-right font-mono">₹ <?= number_format((float)($inv['igst_amount'] ?? 0), 2) ?></td>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <td>CGST</td>
                      <td>—</td>
                      <td class="text-right font-mono">₹ <?= number_format((float)($inv['cgst_amount'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                      <td>SGST</td>
                      <td>—</td>
                      <td class="text-right font-mono">₹ <?= number_format((float)($inv['sgst_amount'] ?? 0), 2) ?></td>
                    </tr>
                  <?php endif; ?>
                  <tr class="font-semibold bg-canvas-alt/60">
                    <td colspan="2">Total GST</td>
                    <td class="text-right font-mono">₹ <?= number_format((float)($inv['tax_amount'] ?? 0), 2) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <div class="rf-alert rf-alert-warning mb-0">
          <i class="bi bi-shield-exclamation mt-0.5 shrink-0"></i>
          <div class="text-xs leading-relaxed">
            <strong>Admin Read-Only Access:</strong> This invoice belongs to a subscriber. No edits or deletions can be made from the admin panel. This access is logged.
          </div>
        </div>
      </div>

      <div class="rf-card-muted p-4 space-y-2 text-sm">
        <div class="flex items-center justify-between py-1.5 border-b border-border-subtle">
          <span class="text-ink-muted text-xs">Subtotal</span>
          <span class="font-medium font-mono text-ink">₹ <?= number_format((float)($inv['subtotal'] ?? 0), 2) ?></span>
        </div>

        <?php if ((float)($inv['discount_amount'] ?? 0) > 0): ?>
          <div class="flex items-center justify-between py-1.5 border-b border-border-subtle text-rose">
            <span class="text-xs">
              Discount
              <?= ($inv['discount_type'] ?? '') === 'percentage' ? '(' . number_format((float)($inv['discount_value'] ?? 0), 1) . '%)' : '' ?>
            </span>
            <span class="font-mono">- ₹ <?= number_format((float)$inv['discount_amount'], 2) ?></span>
          </div>
        <?php endif; ?>

        <div class="flex items-center justify-between py-1.5 border-b border-border-subtle">
          <span class="text-ink-muted text-xs">Taxable Amount</span>
          <span class="font-medium font-mono text-ink">₹ <?= number_format((float)($inv['taxable_amount'] ?? 0), 2) ?></span>
        </div>

        <?php if ($isGst && (float)($inv['tax_amount'] ?? 0) > 0): ?>
          <?php if (!empty($inv['is_interstate'])): ?>
            <div class="flex items-center justify-between py-1.5 border-b border-border-subtle text-ink-muted text-xs">
              <span>IGST</span>
              <span class="font-mono">₹ <?= number_format((float)($inv['igst_amount'] ?? 0), 2) ?></span>
            </div>
          <?php else: ?>
            <div class="flex items-center justify-between py-1.5 border-b border-border-subtle text-ink-muted text-xs">
              <span>CGST</span>
              <span class="font-mono">₹ <?= number_format((float)($inv['cgst_amount'] ?? 0), 2) ?></span>
            </div>
            <div class="flex items-center justify-between py-1.5 border-b border-border-subtle text-ink-muted text-xs">
              <span>SGST</span>
              <span class="font-mono">₹ <?= number_format((float)($inv['sgst_amount'] ?? 0), 2) ?></span>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (abs((float)($inv['round_off'] ?? 0)) > 0): ?>
          <div class="flex items-center justify-between py-1.5 border-b border-border-subtle text-ink-muted text-xs">
            <span>Round Off</span>
            <span class="font-mono"><?= (float)($inv['round_off'] ?? 0) >= 0 ? '+' : '' ?>₹ <?= number_format((float)($inv['round_off'] ?? 0), 2) ?></span>
          </div>
        <?php endif; ?>

        <div class="flex items-center justify-between py-2.5 border-b border-border">
          <span class="font-semibold text-ink">Grand Total</span>
          <span class="font-semibold text-lg font-mono text-ink">₹ <?= number_format((float)($inv['grand_total'] ?? 0), 2) ?></span>
        </div>

        <div class="flex items-center justify-between pt-1 text-xs">
          <span class="text-sage font-medium">Amount Paid</span>
          <span class="font-semibold font-mono text-sage">₹ <?= number_format((float)($inv['paid_amount'] ?? 0), 2) ?></span>
        </div>

        <div class="flex items-center justify-between text-xs">
          <span class="text-rose font-medium">Balance Due</span>
          <span class="font-semibold font-mono text-rose">₹ <?= number_format($due, 2) ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Footer Actions -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-8">
  <a href="/admin/invoices" class="rf-btn rf-btn-secondary">
    <i class="bi bi-arrow-left"></i> Back to Platform Records
  </a>
  <a href="/admin/users/<?= (int)($inv['user_id'] ?? 0) ?>" class="rf-btn rf-btn-primary">
    <i class="bi bi-person-badge"></i> View Subscriber Profile
  </a>
</div>
