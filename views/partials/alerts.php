<?php if (!empty($success_msg)): ?>
  <?php foreach ((array)$success_msg as $msg): ?>
    <div class="rf-alert rf-alert-success" role="alert" data-ui-alert>
      <i class="bi bi-check-circle text-base mt-0.5 shrink-0"></i>
      <div class="flex-1 leading-relaxed"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="bg-transparent border-0 text-current opacity-60 hover:opacity-100 cursor-pointer p-0.5" data-ui-dismiss="alert" aria-label="Close">
        <i class="bi bi-x-lg text-xs"></i>
      </button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
  <?php foreach ((array)$error_msg as $msg): ?>
    <div class="rf-alert rf-alert-danger" role="alert" data-ui-alert>
      <i class="bi bi-exclamation-triangle text-base mt-0.5 shrink-0"></i>
      <div class="flex-1 leading-relaxed"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="bg-transparent border-0 text-current opacity-60 hover:opacity-100 cursor-pointer p-0.5" data-ui-dismiss="alert" aria-label="Close">
        <i class="bi bi-x-lg text-xs"></i>
      </button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($info_msg)): ?>
  <?php foreach ((array)$info_msg as $msg): ?>
    <div class="rf-alert rf-alert-info" role="alert" data-ui-alert>
      <i class="bi bi-info-circle text-base mt-0.5 shrink-0"></i>
      <div class="flex-1 leading-relaxed"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
      <button type="button" class="bg-transparent border-0 text-current opacity-60 hover:opacity-100 cursor-pointer p-0.5" data-ui-dismiss="alert" aria-label="Close">
        <i class="bi bi-x-lg text-xs"></i>
      </button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
