      </div>
    </main>

    <?php if (!empty($user)): ?>
      <footer class="footer mt-auto py-3 bg-white border-top text-muted small">
        <div class="container-fluid px-lg-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <span>RACE FINANCE &copy; <?= date('Y') ?> &bull; </span>
              <span class="d-inline-flex gap-2 text-muted">
                <a href="/free-trial" class="text-secondary text-decoration-none hover-primary">Free Trial</a> &bull;
                <a href="/about" class="text-secondary text-decoration-none hover-primary">About</a> &bull;
                <a href="/pricing" class="text-secondary text-decoration-none hover-primary">Pricing</a> &bull;
                <a href="/contact" class="text-secondary text-decoration-none hover-primary">Contact</a> &bull;
                <a href="/terms" class="text-secondary text-decoration-none hover-primary">Terms</a> &bull;
                <a href="/privacy" class="text-secondary text-decoration-none hover-primary">Privacy</a> &bull;
                <a href="/refund-policy" class="text-secondary text-decoration-none hover-primary">Refund Policy</a> &bull;
                <a href="/disclaimer" class="text-secondary text-decoration-none hover-primary">Disclaimer</a> &bull;
                <a href="/security" class="text-secondary text-decoration-none hover-primary">Security</a>
              </span>
            </div>
            <?php if (!empty($isAdmin)): ?>
              <span class="badge bg-warning text-dark"><i class="bi bi-shield-lock-fill me-1"></i> Admin Command Center</span>
            <?php else: ?>
              <span>Active Firm: <strong class="text-dark"><?= !empty($activeFirm) ? htmlspecialchars($activeFirm['name'], ENT_QUOTES, 'UTF-8') : 'None' ?></strong></span>
            <?php endif; ?>
          </div>
        </div>
      </footer>
    </div> <!-- /app-main-wrapper -->
  </div> <!-- /app-layout -->
  <?php else: ?>
    <footer class="footer mt-auto py-3 text-center text-muted small">
      <div class="container">
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-1">
          <a href="/free-trial" class="text-secondary text-decoration-none fw-semibold text-primary">1-Month Free Trial</a> &bull;
          <a href="/about" class="text-secondary text-decoration-none">About & Features</a> &bull;
          <a href="/pricing" class="text-secondary text-decoration-none">Pricing</a> &bull;
          <a href="/contact" class="text-secondary text-decoration-none">Contact</a> &bull;
          <a href="/terms" class="text-secondary text-decoration-none">Terms & Conditions</a> &bull;
          <a href="/privacy" class="text-secondary text-decoration-none">Privacy Policy</a> &bull;
          <a href="/refund-policy" class="text-secondary text-decoration-none">Refund Policy</a> &bull;
          <a href="/disclaimer" class="text-secondary text-decoration-none">Disclaimer</a> &bull;
          <a href="/security" class="text-secondary text-decoration-none">Password & Data Security</a>
        </div>
        <div>RACE FINANCE &copy; <?= date('Y') ?> — Smart Small Business Billing & Inventory System</div>
      </div>
    </footer>
  </div> <!-- /container -->
</main>
<?php endif; ?>

<!-- Bootstrap 5 Bundle JS (Offline Local with CDN Fallback) -->
<script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>if(typeof bootstrap==='undefined'){document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>');}</script>
<!-- Main App JS -->
<script src="/js/app.js"></script>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/sw.js').catch(function() {});
  });
}
</script>
</body>
</html>
