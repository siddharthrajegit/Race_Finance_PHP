<div class="row justify-content-center">
  <div class="col-md-7 col-lg-6 col-xl-5">
    <!-- Login Card -->
    <div class="card auth-card shadow border-0 rounded-4">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <img src="/uploads/logo-1787158589640-807857696.png" alt="RACE FINANCE Logo" class="rounded-3 mx-auto mb-3 shadow-sm d-block" width="58" height="58" style="object-fit: contain; background: #ffffff; padding: 4px; border: 1px solid #e2e8f0;">
          <h3 class="fw-bold text-dark mb-1">Sign In to RACE FINANCE</h3>
          <p class="text-muted small">Smart Small Business Billing, Inventory & Accounting System</p>
        </div>

        <?php if (!empty($hasGoogleAuth)): ?>
          <div class="d-grid mb-3">
            <a href="/auth/google" class="btn btn-google shadow-sm">
              <svg class="me-2" width="18" height="18" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
              Sign In with Google
            </a>
          </div>

          <div class="d-flex align-items-center my-3">
            <hr class="flex-grow-1">
            <span class="px-2 text-muted small text-uppercase">Or with phone / email</span>
            <hr class="flex-grow-1">
          </div>
        <?php endif; ?>

        <form action="/auth/login" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="mb-3">
            <label for="identifier" class="form-label">Phone Number or Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
              <input type="text" class="form-control" id="identifier" name="identifier" placeholder="e.g. 9876543210 or user@example.com" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
          </div>

          <?php if (!empty($showCaptcha) && !empty($captchaQuestion)): ?>
            <div class="mb-4 p-3 rounded-3 bg-light border border-warning shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <label for="captcha" class="form-label fw-semibold text-dark mb-0 d-flex align-items-center">
                  <i class="bi bi-shield-check text-warning fs-5 me-1"></i> Security Verification
                </label>
                <span class="badge bg-warning text-dark fw-normal">Required</span>
              </div>
              <div class="input-group">
                <span class="input-group-text bg-white fw-bold text-primary font-monospace fs-6 px-3 border-end-0">
                  <?= htmlspecialchars($captchaQuestion, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <input type="number" class="form-control text-center fw-bold fs-6 font-monospace" id="captcha" name="captcha" placeholder="Your Answer" required autocomplete="off">
                <a href="/auth/login" class="btn btn-outline-secondary" title="Get a new question">
                  <i class="bi bi-arrow-clockwise"></i>
                </a>
              </div>
              <div class="form-text small text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i> Please solve the math question to verify you are a human.
              </div>
            </div>
          <?php endif; ?>

          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-semibold">
              <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
          </div>
        </form>

        <!-- WhatsApp Free Trial Onboarding Request Card -->
        <div class="text-center mt-3 pt-3 border-top">
          <div class="text-muted small mb-2 fw-medium">Don't have an account yet?</div>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business.%20Please%20provide%20my%20login%20credentials." class="btn btn-outline-success btn-sm w-100 py-2 fw-semibold rounded-pill d-inline-flex align-items-center justify-content-center shadow-sm" target="_blank">
            <i class="bi bi-whatsapp fs-5 me-2"></i> Request 1-Month Free Trial on WhatsApp
          </a>
          <div class="text-muted text-center mt-2" style="font-size: 0.75rem;">
            <i class="bi bi-shield-check text-success me-1"></i> Direct registration is closed. Accounts are manually provisioned by the admin.
          </div>
        </div>
      </div>
    </div>

    <!-- Security & Password Notice Banner -->
    <div class="card shadow-sm border-0 rounded-4 mt-3 bg-light">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-center mb-2">
          <i class="bi bi-shield-lock-fill text-primary fs-5 me-2"></i>
          <h6 class="fw-bold text-dark mb-0">Security & Password Advisory</h6>
        </div>
        
        <ul class="list-unstyled text-muted small mb-0 lh-base d-flex flex-column gap-2">
          <li class="d-flex align-items-start">
            <i class="bi bi-key-fill text-warning me-2 mt-1 flex-shrink-0"></i>
            <span><strong>Zero-Knowledge Passwords:</strong> We do not have access to your plain-text password. Once changed or set, you are solely responsible for remembering and safeguarding your credentials.</span>
          </li>
          <li class="d-flex align-items-start">
            <i class="bi bi-eye-slash-fill text-success me-2 mt-1 flex-shrink-0"></i>
            <span><strong>Zero Account Inspection:</strong> Our administrators never access or log into your personal account. If you observe any suspicious or unauthorized activity, please report it to us immediately (<?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?>).</span>
          </li>
          <li class="d-flex align-items-start">
            <i class="bi bi-download text-info me-2 mt-1 flex-shrink-0"></i>
            <span><strong>Local Data Portability:</strong> Your data belongs to you. Complete financial records can be downloaded directly into your local offline storage anytime.</span>
          </li>
          <li class="d-flex align-items-start">
            <i class="bi bi-clock-history text-danger me-2 mt-1 flex-shrink-0"></i>
            <span><strong>Account Lifecycle Policy:</strong> Accounts that remain expired and un-renewed for over 1 year (12 continuous months) are permanently purged from the database.</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Quick Legal Links & Support -->
    <div class="text-center mt-4">
      <div class="d-flex flex-wrap justify-content-center gap-2 small text-muted mb-2">
        <a href="/free-trial" class="text-success text-decoration-none hover-primary fw-semibold"><i class="bi bi-gift-fill me-1"></i>Free Trial</a> &bull;
        <a href="/about" class="text-secondary text-decoration-none hover-primary fw-semibold"><i class="bi bi-info-circle me-1"></i>About & Features</a> &bull;
        <a href="/pricing" class="text-secondary text-decoration-none hover-primary fw-semibold">Pricing</a> &bull;
        <a href="/contact" class="text-secondary text-decoration-none hover-primary fw-semibold">Contact</a> &bull;
        <a href="/terms" class="text-secondary text-decoration-none hover-primary">Terms & Conditions</a> &bull;
        <a href="/privacy" class="text-secondary text-decoration-none hover-primary">Privacy Policy</a> &bull;
        <a href="/refund-policy" class="text-secondary text-decoration-none hover-primary">Refund Policy</a> &bull;
        <a href="/disclaimer" class="text-secondary text-decoration-none hover-primary">Disclaimer</a> &bull;
        <a href="/security" class="text-secondary text-decoration-none hover-primary">Security</a>
      </div>
      <div class="small text-muted">
        Need assistance? <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hi%20RACE%20FINANCE%20Support,%20I%20need%20assistance." target="_blank" class="text-success text-decoration-none fw-semibold"><i class="bi bi-whatsapp"></i> WhatsApp Support (<?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?>)</a>
      </div>
    </div>
  </div>
</div>
