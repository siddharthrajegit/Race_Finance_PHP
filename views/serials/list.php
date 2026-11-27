<?php $canManage = in_array('*', $userPermissions ?? [], true) || in_array('manage_items', $userPermissions ?? [], true); ?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight flex items-center gap-2">
        <i class="bi bi-hash text-[#2B526E]"></i>
        <span>Serial Numbers &amp; IMEI Register</span>
      </h1>
      <p class="text-sm text-[#5C5A54] mt-1">Unique per-unit serial/IMEI tracking across purchases, sales &amp; warranty status</p>
    </div>
    <a href="/items" class="rf-btn rf-btn-secondary rf-btn-sm">&larr; Back to Items</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
    <div class="<?= $canManage ? 'lg:col-span-8' : 'lg:col-span-12' ?> rf-card">
      <div class="rf-card-header py-3 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Serial Register (<?= count($serials) ?>)</h2>
        <div class="inline-flex flex-wrap items-center gap-1.5">
          <a href="/serials" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= empty($selectedStatus) ? 'rf-btn-primary' : 'rf-btn-secondary' ?>">All</a>
          <a href="/serials?status=in_stock" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= ($selectedStatus === 'in_stock') ? 'rf-btn-sage-solid' : 'rf-btn-sage' ?>">In Stock</a>
          <a href="/serials?status=sold" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= ($selectedStatus === 'sold') ? 'rf-btn-primary' : 'rf-btn-secondary' ?>">Sold</a>
          <a href="/serials?status=damaged" class="rf-btn rf-btn-sm py-1 px-2.5 text-xs <?= ($selectedStatus === 'damaged') ? 'rf-btn-danger' : 'rf-btn-secondary' ?>">Damaged</a>
        </div>
      </div>
      <div class="rf-table-wrap">
        <table class="rf-table">
          <thead>
            <tr>
              <th>Serial / IMEI #</th>
              <th>Product</th>
              <th>Warehouse</th>
              <th>Status</th>
              <?php if ($canManage): ?><th class="text-right">Change Status</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($serials)): ?>
              <tr>
                <td colspan="<?= $canManage ? 5 : 4 ?>" class="text-center py-10 text-[#78746C]">No serial numbers registered yet.</td>
              </tr>
            <?php else: foreach ($serials as $s): ?>
              <tr>
                <td class="font-mono font-semibold text-[#1C1B18]"><?= htmlspecialchars($s['serial_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($s['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-xs text-[#78746C]"><?= htmlspecialchars($s['warehouse_name'] ?? 'Default', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php
                    $badge = match ($s['status']) {
                        'in_stock' => 'rf-badge-sage',
                        'sold'     => 'rf-badge-slate',
                        'returned' => 'rf-badge-neutral',
                        default    => 'rf-badge-rose',
                    };
                  ?>
                  <span class="rf-badge <?= $badge ?> uppercase"><?= htmlspecialchars($s['status'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <?php if ($canManage): ?>
                  <td class="text-right">
                    <form action="/serials/status" method="POST" class="inline-flex items-center gap-1.5 m-0">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="serial_id" value="<?= (int)$s['id'] ?>">
                      <select name="status" class="rf-select rf-select-sm w-auto py-1 text-xs">
                        <?php foreach (['in_stock', 'sold', 'returned', 'damaged'] as $stOpt): ?>
                          <option value="<?= $stOpt ?>" <?= ($s['status'] === $stOpt) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $stOpt)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="rf-btn rf-btn-secondary rf-btn-sm py-1 px-2.5 text-xs">Save</button>
                    </form>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($canManage): ?>
      <form action="/serials/register" method="POST" class="lg:col-span-4 rf-card">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="rf-card-header py-3">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-[#5C5A54]">Register Serials / IMEIs</h2>
        </div>
        <div class="rf-card-body space-y-3.5">
          <div>
            <label class="rf-label-plain text-xs">Product <span class="text-[#8F2D2B]">*</span></label>
            <select name="item_id" class="rf-select rf-select-sm" required>
              <option value="">-- Select Product --</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (count($warehouses) > 1): ?>
            <div>
              <label class="rf-label-plain text-xs">Warehouse</label>
              <select name="warehouse_id" class="rf-select rf-select-sm">
                <?php foreach ($warehouses as $w): ?>
                  <option value="<?= (int)$w['id'] ?>"><?= htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <div>
            <label class="rf-label-plain text-xs">Serial Numbers (one per line or comma separated) <span class="text-[#8F2D2B]">*</span></label>
            <textarea name="serial_numbers" class="rf-textarea font-mono text-xs" rows="4" required placeholder="IMEI-10001&#10;IMEI-10002"></textarea>
          </div>
          <div>
            <label class="rf-label-plain text-xs">Notes</label>
            <input type="text" name="notes" class="rf-input rf-input-sm" placeholder="Optional warranty / supplier lot note">
          </div>
        </div>
        <div class="rf-card-footer justify-end py-3">
          <button type="submit" class="rf-btn rf-btn-primary rf-btn-sm">Register Serials</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
