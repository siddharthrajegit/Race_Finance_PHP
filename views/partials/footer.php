      </div>
    </main>

    <?php if (!empty($user)): ?>
      <footer class="mt-auto border-t border-[#E6E4DF] bg-white/75 py-4 px-4 sm:px-7 lg:px-10 text-xs text-[#78746C]">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-3">
          <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <span class="font-medium text-[#5C5A54]">RACE FINANCE &copy; <?= date('Y') ?></span>
            <span class="text-[#D4D1C9]">&bull;</span>
            <a href="/free-trial" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Free Trial</a>
            <a href="/about" class="text-[#78746C] hover:text-[#1C1B18] no-underline">About</a>
            <a href="/pricing" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Pricing</a>
            <a href="/contact" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Contact</a>
            <a href="/terms" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Terms</a>
            <a href="/privacy" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Privacy</a>
            <a href="/refund-policy" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Refund Policy</a>
            <a href="/disclaimer" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Disclaimer</a>
            <a href="/security" class="text-[#78746C] hover:text-[#1C1B18] no-underline">Security</a>
          </div>
          <?php if (!empty($isAdmin)): ?>
            <span class="rf-badge rf-badge-amber"><i class="bi bi-shield-lock"></i> Admin Command Center</span>
          <?php else: ?>
            <span>Active Firm: <strong class="text-[#1C1B18] font-medium"><?= !empty($activeFirm) ? htmlspecialchars($activeFirm['name'], ENT_QUOTES, 'UTF-8') : 'None' ?></strong></span>
          <?php endif; ?>
        </div>
      </footer>
    </div>
  </div>
<?php else: ?>
    </div>
  </main>

  <footer class="mt-auto border-t border-[#E6E4DF] bg-white/60 py-6 px-4 text-center text-xs text-[#78746C]">
    <div class="max-w-6xl mx-auto space-y-2">
      <div class="flex flex-wrap justify-center items-center gap-x-4 gap-y-1.5">
        <a href="/free-trial" class="text-[#2E5A36] hover:underline font-medium no-underline">1-Month Free Trial</a>
        <a href="/about" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">About & Features</a>
        <a href="/pricing" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Pricing</a>
        <a href="/contact" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Contact</a>
        <a href="/terms" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Terms & Conditions</a>
        <a href="/privacy" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Privacy Policy</a>
        <a href="/refund-policy" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Refund Policy</a>
        <a href="/disclaimer" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Disclaimer</a>
        <a href="/security" class="text-[#5C5A54] hover:text-[#1C1B18] no-underline">Password & Data Security</a>
      </div>
      <div>RACE FINANCE &copy; <?= date('Y') ?> — Smart Small Business Billing & Inventory System</div>
    </div>
  </footer>
<?php endif; ?>

<!-- Flatpickr JS (Offline Local with CDN Fallback) -->
<script src="/vendor/flatpickr/flatpickr.min.js"></script>
<script>if(typeof flatpickr==='undefined'){document.write('<script src="https://cdn.jsdelivr.net/npm/flatpickr"><\/script>');}</script>
<!-- Barcode / QR Helpers -->
<script src="/js/vendor/qrcode.min.js"></script>
<script src="/js/barcode.js"></script>
<?php if (!empty($user) && defined('ENABLE_OFFLINE') && ENABLE_OFFLINE): ?>
<!-- Offline Queue Modal -->
<div class="rf-modal hidden" id="offlineQueueModal" tabindex="-1" aria-hidden="true">
  <div class="rf-modal-dialog rf-modal-dialog-lg">
    <div class="rf-modal-header">
      <h3 class="rf-modal-title text-sm"><i class="bi bi-cloud-upload text-[#2B526E]"></i> Offline Billing Queue &amp; Sync</h3>
      <button type="button" class="text-[#78746C] hover:text-[#1C1B18] bg-transparent border-0 cursor-pointer p-1" data-ui-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="rf-modal-body" id="offlineQueueList">
      <div class="text-[#78746C] text-center py-6 text-xs">Checking local offline queue...</div>
    </div>
    <div class="rf-modal-footer">
      <button type="button" id="btnSyncOfflineNow" class="rf-btn rf-btn-primary rf-btn-sm">Sync Pending Now</button>
      <button type="button" class="rf-btn rf-btn-secondary rf-btn-sm" data-ui-dismiss="modal">Close</button>
    </div>
  </div>
</div>
<script src="/js/offline.js"></script>
<?php endif; ?>
<!-- Vanilla UI Controller & App Scripts -->
<script src="/js/ui.js"></script>
<script src="/js/app.js"></script>
<?php if (!defined('ENABLE_PWA') || ENABLE_PWA): ?>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/sw.js').catch(function() {});
  });
}
</script>
<?php endif; ?>
</body>
</html>
