<div class="max-w-[440px] mx-auto py-6 sm:py-10">
  <!-- Sign In Card -->
  <div class="rf-card p-7 sm:p-9">
    <div class="text-center mb-7">
      <img src="/uploads/logo-1787158589640-807857696.png" alt="RACE FINANCE Logo" class="w-14 h-14 rounded-xl mx-auto mb-4 object-contain bg-white p-1 border border-[#E6E4DF]">
      <h1 class="text-xl font-semibold text-[#1C1B18] tracking-tight">Sign in to RACE FINANCE</h1>
      <p class="text-xs text-[#78746C] mt-1">Small Business Billing, Inventory & Accounting</p>
    </div>

    <?php if (!empty($hasGoogleAuth)): ?>
      <div class="mb-5">
        <a href="/auth/google" class="rf-btn rf-btn-secondary w-full py-2.5">
          <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
          </svg>
          Sign in with Google
        </a>
      </div>

      <div class="flex items-center gap-3 my-5">
        <div class="h-px flex-1 bg-[#E6E4DF]"></div>
        <span class="text-[11px] uppercase tracking-wider text-[#87847C]">Or with phone / email</span>
        <div class="h-px flex-1 bg-[#E6E4DF]"></div>
      </div>
    <?php endif; ?>

    <form action="/auth/login" method="POST" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

      <div>
        <label for="identifier" class="rf-label-plain">Phone Number or Email</label>
        <div class="relative">
          <i class="bi bi-person absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-sm"></i>
          <input type="text" class="rf-input pl-10" id="identifier" name="identifier" placeholder="e.g. 9876543210 or user@example.com" required autofocus>
        </div>
      </div>

      <div>
        <label for="password" class="rf-label-plain">Password</label>
        <div class="relative">
          <i class="bi bi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-[#87847C] text-sm"></i>
          <input type="password" class="rf-input pl-10" id="password" name="password" placeholder="Enter your password" required>
        </div>
      </div>

      <?php if (!empty($showCaptcha) && !empty($captchaQuestion)): ?>
        <div class="p-4 rounded-xl bg-[#FBF3DB]/60 border border-[#EAE0B8] space-y-2.5">
          <div class="flex items-center justify-between">
            <label for="captcha" class="text-xs font-semibold text-[#1C1B18] flex items-center gap-1.5">
              <i class="bi bi-shield-check text-[#8A5A00]"></i> Security Verification
            </label>
            <span class="rf-badge rf-badge-amber">Required</span>
          </div>
          <div class="flex items-stretch gap-2">
            <span class="inline-flex items-center px-3.5 rounded-lg bg-white border border-[#D9D6CE] font-mono font-semibold text-sm text-[#1C1B18] shrink-0">
              <?= htmlspecialchars($captchaQuestion, ENT_QUOTES, 'UTF-8') ?>
            </span>
            <input type="number" class="rf-input text-center font-mono font-semibold" id="captcha" name="captcha" placeholder="Answer" required autocomplete="off">
            <a href="/auth/login" class="rf-btn rf-btn-secondary px-3" title="Get a new question">
              <i class="bi bi-arrow-clockwise"></i>
            </a>
          </div>
          <p class="text-[11px] text-[#784F00]">Solve the math question to verify you are human.</p>
        </div>
      <?php endif; ?>

      <div class="pt-1">
        <button type="submit" class="rf-btn rf-btn-primary w-full py-3">
          <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
      </div>
    </form>

    <!-- Onboarding Request -->
    <div class="mt-6 pt-5 border-t border-[#ECEAE4] text-center space-y-2.5">
      <div class="text-xs text-[#78746C]">Don't have an account yet?</div>
      <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business.%20Please%20provide%20my%20login%20credentials." class="rf-btn rf-btn-sage w-full text-xs" target="_blank">
        <i class="bi bi-whatsapp"></i> Request 1-Month Free Trial on WhatsApp
      </a>
      <p class="text-[11px] text-[#87847C]">
        <i class="bi bi-shield-check text-[#2E5A36] mr-1"></i> Direct registration is closed. Accounts are provisioned by admin.
      </p>
    </div>
  </div>

  <!-- Security & Password Notice -->
  <div class="rf-card-muted p-5 sm:p-6 mt-5">
    <div class="flex items-center gap-2 mb-3">
      <i class="bi bi-shield-lock text-[#5C5A54]"></i>
      <h2 class="text-xs font-semibold uppercase tracking-wider text-[#1C1B18]">Security & Password Advisory</h2>
    </div>
    <ul class="space-y-2.5 text-xs text-[#5C5A54] leading-relaxed list-none p-0 m-0">
      <li class="flex items-start gap-2.5">
        <i class="bi bi-key text-[#8A5A00] mt-0.5 shrink-0"></i>
        <span><strong class="text-[#1C1B18]">Zero-Knowledge Passwords:</strong> We do not have access to your plain-text password. Safeguard your credentials once changed.</span>
      </li>
      <li class="flex items-start gap-2.5">
        <i class="bi bi-eye-slash text-[#2E5A36] mt-0.5 shrink-0"></i>
        <span><strong class="text-[#1C1B18]">Zero Account Inspection:</strong> Administrators never log into your account. Report suspicious activity to <?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?>.</span>
      </li>
      <li class="flex items-start gap-2.5">
        <i class="bi bi-download text-[#2B526E] mt-0.5 shrink-0"></i>
        <span><strong class="text-[#1C1B18]">Local Data Portability:</strong> Export complete financial records to local JSON storage anytime.</span>
      </li>
      <li class="flex items-start gap-2.5">
        <i class="bi bi-clock-history text-[#8F2D2B] mt-0.5 shrink-0"></i>
        <span><strong class="text-[#1C1B18]">Account Lifecycle Policy:</strong> Accounts expired and un-renewed for 12 continuous months are permanently purged.</span>
      </li>
    </ul>
  </div>

  <div class="text-center mt-6 space-y-2">
    <div class="flex flex-wrap justify-center gap-x-3 gap-y-1 text-xs text-[#78746C]">
      <a href="/free-trial" class="text-[#2E5A36] font-medium hover:underline no-underline">Free Trial</a>
      <a href="/about" class="hover:text-[#1C1B18] no-underline">About & Features</a>
      <a href="/pricing" class="hover:text-[#1C1B18] no-underline">Pricing</a>
      <a href="/contact" class="hover:text-[#1C1B18] no-underline">Contact</a>
      <a href="/terms" class="hover:text-[#1C1B18] no-underline">Terms</a>
      <a href="/privacy" class="hover:text-[#1C1B18] no-underline">Privacy</a>
      <a href="/refund-policy" class="hover:text-[#1C1B18] no-underline">Refunds</a>
      <a href="/disclaimer" class="hover:text-[#1C1B18] no-underline">Disclaimer</a>
      <a href="/security" class="hover:text-[#1C1B18] no-underline">Security</a>
    </div>
    <div class="text-xs text-[#78746C]">
      Need assistance? <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hi%20RACE%20FINANCE%20Support,%20I%20need%20assistance." target="_blank" class="text-[#2E5A36] font-medium hover:underline no-underline"><i class="bi bi-whatsapp"></i> WhatsApp Support (<?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?>)</a>
    </div>
  </div>
</div>
