<?php if (!empty($success_msg)): ?>
  <?php foreach ((array)$success_msg as $msg): ?>
    <div class="alert alert-success alert-rf alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
      <span class="alert-rf-icon bg-success-subtle text-success me-3"><i class="bi bi-check-circle-fill"></i></span>
      <div class="fw-medium text-dark small"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <?php foreach ((array)$error_msg as $msg): ?>
    <div class="alert alert-danger alert-rf alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
      <span class="alert-rf-icon bg-danger-subtle text-danger me-3"><i class="bi bi-exclamation-triangle-fill"></i></span>
      <div class="fw-medium text-dark small"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($info_msg)): ?>
  <?php foreach ((array)$info_msg as $msg): ?>
    <div class="alert alert-info alert-rf alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
      <span class="alert-rf-icon bg-primary-subtle text-primary me-3"><i class="bi bi-info-circle-fill"></i></span>
      <div class="fw-medium text-dark small"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
