<?php
$firmCount = !empty($firms) ? count($firms) : 0;
?>
<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight">My Business Firms</h1>
        <span class="rf-badge <?= $firmCount >= 2 ? 'rf-badge-amber' : 'rf-badge-slate' ?>">
          <i class="bi <?= $firmCount >= 2 ? 'bi-lock' : 'bi-building-check' ?>"></i>
          <?= $firmCount ?>/2 Firms Registered
        </span>
      </div>
      <p class="text-sm text-[#5C5A54] mt-1">Manage up to 2 business entities, companies, or branch firms</p>
    </div>

    <div>
      <?php if ($firmCount < 2): ?>
        <a href="/firms/create" class="rf-btn rf-btn-primary">
          <i class="bi bi-plus-circle"></i> Register New Firm (<?= $firmCount ?>/2)
        </a>
      <?php else: ?>
        <button class="rf-btn rf-btn-secondary" disabled title="Maximum limit of 2 business firms reached">
          <i class="bi bi-slash-circle"></i> Max 2 Firms Reached
        </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($firmCount >= 2): ?>
    <div class="rf-alert rf-alert-info">
      <i class="bi bi-info-circle text-base mt-0.5 shrink-0"></i>
      <div class="text-xs leading-relaxed">
        You have registered the maximum limit of <strong>2 business firms</strong>. Edit your existing firms or delete an inactive one to add a new entity.
      </div>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php if (!empty($firms)): ?>
      <?php foreach ($firms as $f): ?>
        <?php $isCurrent = (!empty($activeFirm) && $activeFirm['id'] == $f['id']); ?>
        <div class="rf-card p-6 flex flex-col justify-between <?= $isCurrent ? 'border-[#1C1B18]' : '' ?>">
          <div>
            <div class="flex items-start justify-between gap-3 mb-4">
              <?php if (!empty($f['logo_path'])): ?>
                <img src="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>" class="w-12 h-12 rounded-xl border border-[#E6E4DF] p-1 bg-white object-contain">
              <?php else: ?>
                <div class="w-12 h-12 rounded-xl bg-[#F3F1EC] border border-[#E2DFD7] text-[#5C5A54] flex items-center justify-center text-xl">
                  <i class="bi bi-buildings"></i>
                </div>
              <?php endif; ?>

              <div class="flex flex-wrap gap-1.5 justify-end">
                <?php if ($isCurrent): ?>
                  <span class="rf-badge rf-badge-sage">Active Now</span>
                <?php endif; ?>
                <?php if (!empty($f['is_default'])): ?>
                  <span class="rf-badge rf-badge-neutral">Default</span>
                <?php endif; ?>
              </div>
            </div>

            <h2 class="text-lg font-semibold text-[#1C1B18]"><?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if (!empty($f['gstin'])): ?>
              <div class="text-xs font-mono text-[#2B526E] mt-1">GSTIN: <?= htmlspecialchars($f['gstin'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
              <div class="text-xs text-[#78746C] mt-1">Non-GST Firm</div>
            <?php endif; ?>

            <div class="text-xs text-[#5C5A54] space-y-1 mt-4">
              <?php if (!empty($f['phone'])): ?><div><i class="bi bi-telephone mr-1.5 text-[#87847C]"></i><?= htmlspecialchars($f['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($f['email'])): ?><div><i class="bi bi-envelope mr-1.5 text-[#87847C]"></i><?= htmlspecialchars($f['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php
                $loc = array_filter([$f['city'] ?? '', $f['state'] ?? '']);
                if (!empty($loc)):
              ?>
                <div><i class="bi bi-geo-alt mr-1.5 text-[#87847C]"></i><?= htmlspecialchars(implode(', ', $loc), ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="pt-4 mt-5 border-t border-[#ECEAE4] flex flex-wrap items-center justify-between gap-3">
            <div>
              <?php if (!$isCurrent): ?>
                <form action="/firms/switch/<?= $f['id'] ?>" method="POST" class="inline">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="rf-btn rf-btn-secondary rf-btn-sm">
                    <i class="bi bi-arrow-repeat"></i> Switch to This
                  </button>
                </form>
              <?php else: ?>
                <span class="text-xs font-medium text-[#2E5A36]"><i class="bi bi-check-circle mr-1"></i> Currently Active</span>
              <?php endif; ?>
            </div>

            <div class="flex items-center gap-1.5">
              <a href="/firms/edit/<?= $f['id'] ?>" class="rf-btn rf-btn-secondary rf-btn-sm" title="Edit Details">
                <i class="bi bi-pencil"></i> Edit
              </a>

              <?php if (empty($f['is_default'])): ?>
                <form action="/firms/default/<?= $f['id'] ?>" method="POST" class="inline">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="rf-btn rf-btn-ghost rf-btn-sm px-2.5" title="Make Default">
                    <i class="bi bi-star"></i>
                  </button>
                </form>
              <?php endif; ?>

              <?php if ($firmCount > 1): ?>
                <form action="/firms/delete/<?= $f['id'] ?>" method="POST" class="inline form-delete-confirm" data-confirm-message="Are you sure you want to delete this firm and all associated bills?">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="rf-btn rf-btn-danger rf-btn-sm px-2.5" title="Delete Firm">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
