<?php
$firmCount = !empty($firms) ? count($firms) : 0;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <div class="d-flex align-items-center gap-2">
      <h3 class="fw-bold mb-0">My Business Firms</h3>
      <span class="badge <?= $firmCount >= 2 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill px-3 py-2">
        <i class="bi <?= $firmCount >= 2 ? 'bi-lock-fill' : 'bi-building-check' ?> me-1"></i>
        <?= $firmCount ?>/2 Firms Registered
      </span>
    </div>
    <p class="text-muted small mb-0 mt-1">Manage up to 2 business entities, companies, or branch firms</p>
  </div>

  <div>
    <?php if ($firmCount < 2): ?>
      <a href="/firms/create" class="btn btn-primary shadow-sm rounded-pill px-3">
        <i class="bi bi-plus-circle me-1"></i> Register New Firm (<?= $firmCount ?>/2)
      </a>
    <?php else: ?>
      <button class="btn btn-secondary shadow-sm rounded-pill px-3" disabled title="Maximum limit of 2 business firms reached">
        <i class="bi bi-slash-circle me-1"></i> Max 2 Firms Reached
      </button>
    <?php endif; ?>
  </div>
</div>

<?php if ($firmCount >= 2): ?>
  <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 py-2 px-3 rounded-3" style="background-color: #eff6ff;">
    <i class="bi bi-info-circle-fill text-primary fs-5 me-2"></i>
    <div class="small text-dark">
      You have registered the maximum permitted limit of <strong>2 business firms</strong>. To register a new firm, you can edit your existing firms or delete an inactive one.
    </div>
  </div>
<?php endif; ?>

<div class="row g-4">
  <?php if (!empty($firms)): ?>
    <?php foreach ($firms as $f): ?>
      <div class="col-md-6 col-lg-6">
        <div class="card h-100 shadow-sm border-0 position-relative <?= (!empty($activeFirm) && $activeFirm['id'] == $f['id']) ? 'border border-2 border-primary' : '' ?>" style="border-radius: 12px;">
          <div class="card-body p-4">
            <!-- Badges -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <?php if (!empty($f['logo_path'])): ?>
                <img src="<?= htmlspecialchars($f['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>" class="rounded border p-1 bg-white" style="width: 48px; height: 48px; object-fit: contain;">
              <?php else: ?>
                <div class="bg-light text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                  <i class="bi bi-buildings"></i>
                </div>
              <?php endif; ?>

              <div class="d-flex flex-column align-items-end gap-1">
                <?php if (!empty($activeFirm) && $activeFirm['id'] == $f['id']): ?>
                  <span class="badge bg-primary">Active Now</span>
                <?php endif; ?>
                <?php if (!empty($f['is_default'])): ?>
                  <span class="badge bg-secondary">Default</span>
                <?php endif; ?>
              </div>
            </div>

            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?></h5>
            
            <?php if (!empty($f['gstin'])): ?>
              <div class="small font-monospace text-primary mb-2">GSTIN: <?= htmlspecialchars($f['gstin'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
              <div class="small text-muted mb-2">Non-GST Firm</div>
            <?php endif; ?>

            <div class="text-muted small mb-3">
              <?php if (!empty($f['phone'])): ?><div><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($f['phone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($f['email'])): ?><div><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($f['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php 
                $loc = array_filter([$f['city'] ?? '', $f['state'] ?? '']);
                if (!empty($loc)): 
              ?>
                <div><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars(implode(', ', $loc), ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            </div>

            <hr class="my-3">

            <!-- Actions -->
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
              <div>
                <?php if (empty($activeFirm) || $activeFirm['id'] != $f['id']): ?>
                  <form action="/firms/switch/<?= $f['id'] ?>" method="POST" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                      <i class="bi bi-arrow-repeat me-1"></i> Switch to This
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Currently Active</span>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-1">
                <a href="/firms/edit/<?= $f['id'] ?>" class="btn btn-sm btn-light rounded-pill px-3" title="Edit Details">
                  <i class="bi bi-pencil me-1"></i> Edit
                </a>

                <?php if (empty($f['is_default'])): ?>
                  <form action="/firms/default/<?= $f['id'] ?>" method="POST" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-sm btn-light rounded-pill px-2" title="Make Default">
                      <i class="bi bi-star"></i>
                    </button>
                  </form>
                <?php endif; ?>

                <?php if ($firmCount > 1): ?>
                  <form action="/firms/delete/<?= $f['id'] ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-message="Are you sure you want to delete this firm and all associated bills?">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete Firm">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
