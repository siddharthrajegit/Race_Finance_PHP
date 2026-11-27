<?php $dateFmt = $firmSettings['general']['date_format'] ?? 'DD/MM/YYYY'; ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-stars text-[#8A5A00]"></i>
        <span>Customer Loyalty Rewards</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Configure points earned on sales invoices and track customer reward balances</p>
    </div>
    <a href="/parties" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Parties</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
    <form action="/loyalty/program" method="POST" class="rf-card flex flex-col">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Program Settings</h2>
      </div>
      <div class="rf-card-body space-y-4 flex-1">
        <div class="flex items-center justify-between gap-4 pb-3 border-b border-[#ECEAE4]">
          <label class="text-xs font-semibold text-[#1C1B18] cursor-pointer" for="loyaltyEnable">Enable Automatic Points on Sales Invoices</label>
          <label class="rf-switch">
            <input type="checkbox" id="loyaltyEnable" name="is_enabled" value="1" <?= !empty($program['is_enabled']) ? 'checked' : '' ?>>
            <span class="rf-switch-thumb"></span>
          </label>
        </div>

        <div>
          <label class="rf-label-plain text-xs">Program Name</label>
          <input type="text" name="program_name" class="rf-input rf-input-sm" value="<?= htmlspecialchars($program['program_name'] ?? 'Rewards Club', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="rf-label-plain text-xs">Points Earned</label>
            <input type="number" name="points_per_currency" class="rf-input rf-input-sm tabular-nums" step="0.1" min="0" value="<?= htmlspecialchars((string)$program['points_per_currency'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Per Spend Unit (₹)</label>
            <input type="number" name="currency_unit" class="rf-input rf-input-sm tabular-nums" step="1" min="1" value="<?= htmlspecialchars((string)$program['currency_unit'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="rf-label-plain text-xs">Point Value (₹)</label>
            <input type="number" name="point_value" class="rf-input rf-input-sm tabular-nums" step="0.01" min="0.01" value="<?= htmlspecialchars((string)$program['point_value'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Min Redeem Pts</label>
            <input type="number" name="min_redeem_points" class="rf-input rf-input-sm tabular-nums" step="1" min="0" value="<?= htmlspecialchars((string)$program['min_redeem_points'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label class="rf-label-plain text-xs">Max Bill %</label>
            <input type="number" name="max_redeem_pct" class="rf-input rf-input-sm tabular-nums" step="1" min="1" max="100" value="<?= htmlspecialchars((string)$program['max_redeem_pct'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>
      </div>
      <div class="rf-card-footer justify-end py-3">
        <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Save Program</button>
      </div>
    </form>

    <form action="/loyalty/adjust" method="POST" class="rf-card flex flex-col">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="rf-card-header py-3">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Manual Point Adjustment / Redemption</h2>
      </div>
      <div class="rf-card-body space-y-4 flex-1">
        <div>
          <label class="rf-label-plain text-xs">Customer <span class="text-[#8F2D2B]">*</span></label>
          <select name="party_id" class="rf-select rf-select-sm" required>
            <option value="">-- Select Customer --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= (int)$c['id'] ?>">
                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?> (Balance: <?= number_format((float)($c['loyalty_points'] ?? 0), 2) ?> pts)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="rf-label-plain text-xs">Action</label>
            <select name="action" class="rf-select rf-select-sm">
              <option value="add">Award (+ Bonus Points)</option>
              <option value="deduct">Redeem / Deduct (- Points)</option>
            </select>
          </div>
          <div>
            <label class="rf-label-plain text-xs">Points</label>
            <input type="number" name="points" class="rf-input rf-input-sm tabular-nums" min="0.01" step="0.01" required>
          </div>
        </div>

        <div>
          <label class="rf-label-plain text-xs">Notes</label>
          <input type="text" name="notes" class="rf-input rf-input-sm" placeholder="Reason for manual bonus or redemption">
        </div>
      </div>
      <div class="rf-card-footer justify-end py-3">
        <button type="submit" class="rf-btn rf-btn-sage-solid rf-btn-sm">Update Points</button>
      </div>
    </form>
  </div>

  <div class="rf-card">
    <div class="rf-card-header">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Recent Loyalty Point Ledger</h2>
    </div>
    <div class="rf-table-wrap">
      <table class="rf-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Type</th>
            <th class="text-right">Points Delta</th>
            <th class="text-right">Balance After</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($transactions)): ?>
            <tr><td colspan="6" class="text-center py-10 text-[#78746C]">No loyalty transactions recorded yet.</td></tr>
          <?php else: foreach ($transactions as $lt): ?>
            <tr>
              <td class="text-xs text-[#5C5A54] whitespace-nowrap"><?= htmlspecialchars($lt['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="font-medium text-[#1C1B18]"><?= htmlspecialchars($lt['party_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="rf-badge rf-badge-neutral uppercase"><?= htmlspecialchars($lt['txn_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td class="text-right font-semibold font-mono tabular-nums <?= (float)$lt['points_delta'] >= 0 ? 'text-[#2E5A36]' : 'text-[#8F2D2B]' ?>">
                <?= (float)$lt['points_delta'] >= 0 ? '+' : '' ?><?= number_format((float)$lt['points_delta'], 2) ?>
              </td>
              <td class="text-right font-semibold font-mono tabular-nums"><?= number_format((float)$lt['balance_after'], 2) ?></td>
              <td class="text-xs text-[#5C5A54]"><?= htmlspecialchars($lt['notes'] ?? '--', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
