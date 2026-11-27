<div class="max-w-xl mx-auto text-center py-20 px-4">
  <div class="w-16 h-16 rounded-2xl bg-[#FBEAEA] text-[#8F2D2B] border border-[#ECD0D0] mx-auto mb-5 flex items-center justify-center text-2xl">
    <i class="bi bi-exclamation-triangle"></i>
  </div>
  <h1 class="text-2xl font-semibold text-[#1C1B18] tracking-tight mb-2">Something went wrong</h1>
  <p class="text-sm text-[#5C5A54] leading-relaxed mb-6">An unexpected error occurred while processing your request. Please try again.</p>
  <?php if (!empty($error) && !empty($error['message'])): ?>
    <div class="rf-alert rf-alert-danger text-left font-mono text-xs mb-6">
      <?= htmlspecialchars($error['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php elseif (!empty($error) && is_string($error)): ?>
    <div class="rf-alert rf-alert-danger text-left font-mono text-xs mb-6">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
  <a href="/dashboard" class="rf-btn rf-btn-primary">
    <i class="bi bi-house"></i> Return to Dashboard
  </a>
</div>
