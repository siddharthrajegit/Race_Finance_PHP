<?php if (!empty($success_msg)): ?>
  <?php foreach ((array)$success_msg as $msg): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
      <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <?php foreach ((array)$error_msg as $msg): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
      <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($info_msg)): ?>
  <?php foreach ((array)$info_msg as $msg): ?>
    <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-info-circle-fill fs-5 me-2 text-info"></i>
      <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
