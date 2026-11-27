<div class="space-y-16 sm:space-y-24 pb-10">
  <!-- Hero Section -->
  <section class="pt-6 sm:pt-12 pb-4 text-center max-w-4xl mx-auto">
    <div class="inline-flex items-center gap-2 rounded-full border border-[#E2DFD7] bg-white px-3.5 py-1.5 text-xs font-medium text-[#5C5A54] mb-6">
      <i class="bi bi-patch-check text-[#2E5A36]"></i>
      <span>Smart Small Business Billing, Inventory &amp; Accounting</span>
    </div>

    <h1 class="text-3xl sm:text-5xl font-semibold text-[#1C1B18] tracking-tight leading-[1.12] mb-5">
      Simpler invoicing. Clearer ledgers.<br class="hidden sm:inline">
      <span class="font-serif italic font-normal text-[#4A4740]">Built for Indian small businesses.</span>
    </h1>

    <p class="text-base sm:text-lg text-[#5C5A54] leading-relaxed max-w-2xl mx-auto mb-9">
      <strong class="text-[#1C1B18] font-semibold">RACE FINANCE</strong> is a calm, focused business utility for traders, retailers, and merchants. Run up to 2 firms, track FIFO customer statements, manage live stock, and export GST-ready registers without accounting clutter.
    </p>

    <div class="flex flex-wrap justify-center items-center gap-3 mb-12">
      <a href="/free-trial" class="rf-btn rf-btn-primary rf-btn-lg">
        <i class="bi bi-gift"></i> Start 1-Month Free Trial
      </a>
      <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="rf-btn rf-btn-sage rf-btn-lg">
        <i class="bi bi-whatsapp"></i> WhatsApp Onboarding
      </a>
      <a href="/auth/login" class="rf-btn rf-btn-secondary rf-btn-lg">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
      </a>
    </div>

    <!-- Calm Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 max-w-3xl mx-auto text-left">
      <div class="rf-card p-5">
        <div class="text-xl font-semibold text-[#1C1B18] tabular-nums">30 Days</div>
        <div class="text-xs text-[#78746C] mt-1">100% Free Trial</div>
      </div>
      <div class="rf-card p-5">
        <div class="text-xl font-semibold text-[#2E5A36] tabular-nums">2 Firms</div>
        <div class="text-xs text-[#78746C] mt-1">Under 1 Account</div>
      </div>
      <div class="rf-card p-5">
        <div class="text-xl font-semibold text-[#2B526E] tabular-nums">FIFO</div>
        <div class="text-xs text-[#78746C] mt-1">Auto-Balancing Ledgers</div>
      </div>
      <div class="rf-card p-5">
        <div class="text-xl font-semibold text-[#8A5A00] tabular-nums">Offline</div>
        <div class="text-xs text-[#78746C] mt-1">1-Click JSON Backups</div>
      </div>
    </div>
  </section>

  <!-- Core Features Grid -->
  <section id="features" class="space-y-10">
    <div class="max-w-2xl">
      <span class="rf-badge rf-badge-neutral uppercase tracking-wider mb-3">Platform Capabilities</span>
      <h2 class="text-2xl sm:text-3xl font-semibold text-[#1C1B18] tracking-tight">Everything required to run your business</h2>
      <p class="text-sm text-[#5C5A54] mt-2 leading-relaxed">
        Designed to stay quiet, fast, and predictable. No bloated ERP menus or manual spreadsheets.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div class="rf-card p-7">
        <div class="w-10 h-10 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center mb-5 text-lg">
          <i class="bi bi-buildings"></i>
        </div>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Dual-Firm Management</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">
          Operate up to 2 distinct commercial firms under one login. Switch between them with isolated bill registers, logos, and GSTIN profiles.
        </p>
      </div>

      <div class="rf-card p-7">
        <div class="w-10 h-10 rounded-lg bg-[#EDF3EC] border border-[#CFE0CE] text-[#2E5A36] flex items-center justify-center mb-5 text-lg">
          <i class="bi bi-receipt"></i>
        </div>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">GST &amp; Non-GST Invoicing</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">
          Create tax invoices, quotations, and bills with itemized discounts, HSN/SAC codes, and tax breakdowns. Instant A4 print and PDF export.
        </p>
      </div>

      <div class="rf-card p-7">
        <div class="w-10 h-10 rounded-lg bg-[#FBF3DB] border border-[#EAE0B8] text-[#8A5A00] flex items-center justify-center mb-5 text-lg">
          <i class="bi bi-journal-text"></i>
        </div>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">FIFO Customer Ledgers</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">
          Full party accounts tracking debits, credits, and running balances. Chronological FIFO settlement automatically offsets oldest invoices.
        </p>
      </div>

      <div class="rf-card p-7">
        <div class="w-10 h-10 rounded-lg bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] flex items-center justify-center mb-5 text-lg">
          <i class="bi bi-box-seam"></i>
        </div>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Live Inventory &amp; Stock Alerts</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">
          Automatic stock deduction on sales and addition on purchases. Low-stock alerts notify you before items run out, with custom units.
        </p>
      </div>

      <div class="rf-card p-7">
        <div class="w-10 h-10 rounded-lg bg-[#FBEAEA] border border-[#ECD0D0] text-[#8F2D2B] flex items-center justify-center mb-5 text-lg">
          <i class="bi bi-file-earmark-bar-graph"></i>
        </div>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Accountant-Ready GST Reports</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">
          Export structured periodic sales, purchase, and tax summaries ready for your accountant or tax consultant to file GST portal returns.
        </p>
      </div>

      <div class="rf-card p-7">
        <div class="w-10 h-10 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center mb-5 text-lg">
          <i class="bi bi-shield-lock"></i>
        </div>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Zero-Knowledge &amp; Portability</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">
          Your records stay yours. Download structured offline <code class="font-mono text-xs bg-[#F3F1EC] px-1.5 py-0.5 rounded">.json</code> backups anytime, backed by bcrypt password hashing.
        </p>
      </div>
    </div>
  </section>

  <!-- 1-Month Free Trial Editorial Spotlight -->
  <section class="rounded-2xl bg-[#1C1B18] text-white p-8 sm:p-12 border border-[#2E2C27]">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      <div class="lg:col-span-7 space-y-4">
        <span class="inline-flex items-center rounded-full bg-white/10 border border-white/15 px-3 py-1 text-xs font-medium text-[#E6E4DF]">
          Complimentary 30 Days
        </span>
        <h2 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">
          Evaluate RACE FINANCE risk-free for one full month
        </h2>
        <p class="text-sm text-[#B8B4AA] leading-relaxed max-w-xl">
          No credit card, no bank mandate, and no automatic charges. Test every module with real bills and firm profiles before committing.
        </p>
        <div class="flex flex-wrap gap-3 pt-2">
          <a href="/free-trial" class="rf-btn bg-white text-[#1C1B18] hover:bg-[#F2F0EB]">
            <i class="bi bi-info-circle"></i> Learn About Free Trial
          </a>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="rf-btn bg-[#2E5A36] text-white hover:bg-[#386B41]">
            <i class="bi bi-whatsapp"></i> Activate on WhatsApp
          </a>
        </div>
      </div>

      <div class="lg:col-span-5">
        <div class="rounded-xl bg-white/[0.06] border border-white/10 p-6">
          <div class="text-xs font-semibold uppercase tracking-wider text-[#D4D1C9] mb-4">Included in Evaluation</div>
          <ul class="space-y-2.5 text-sm text-[#E6E4DF] list-none p-0 m-0">
            <li class="flex items-center gap-2.5"><i class="bi bi-check2 text-[#9BC4A2]"></i> 2 Business Firms Unlocked</li>
            <li class="flex items-center gap-2.5"><i class="bi bi-check2 text-[#9BC4A2]"></i> Unlimited Invoices &amp; Estimates</li>
            <li class="flex items-center gap-2.5"><i class="bi bi-check2 text-[#9BC4A2]"></i> FIFO Party Ledgers</li>
            <li class="flex items-center gap-2.5"><i class="bi bi-check2 text-[#9BC4A2]"></i> Live Inventory Tracking</li>
            <li class="flex items-center gap-2.5"><i class="bi bi-check2 text-[#9BC4A2]"></i> GST Accountant Exports</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="space-y-10">
    <div class="max-w-xl">
      <span class="rf-badge rf-badge-neutral uppercase tracking-wider mb-3">Simple Onboarding</span>
      <h2 class="text-2xl sm:text-3xl font-semibold text-[#1C1B18] tracking-tight">Get started in 3 steps</h2>
      <p class="text-sm text-[#5C5A54] mt-2">Begin recording your business transactions in under 5 minutes.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div class="rf-card p-7">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] font-mono text-xs font-semibold text-[#1C1B18] mb-4">01</span>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Claim Free Trial</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">Reach out via WhatsApp or phone call. Our onboarding desk sets up your business login credentials right away.</p>
      </div>
      <div class="rf-card p-7">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] font-mono text-xs font-semibold text-[#1C1B18] mb-4">02</span>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Add Your Firm &amp; Items</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">Enter your business trade name, optional GSTIN, bank details, and opening item inventory in seconds.</p>
      </div>
      <div class="rf-card p-7">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] font-mono text-xs font-semibold text-[#1C1B18] mb-4">03</span>
        <h3 class="text-base font-semibold text-[#1C1B18] mb-2">Record &amp; Print Bills</h3>
        <p class="text-sm text-[#5C5A54] leading-relaxed">Generate sales bills, print A4 receipts, record payments, and monitor party ledgers automatically.</p>
      </div>
    </div>
  </section>

  <!-- Pricing Preview -->
  <section id="pricing" class="rf-card p-7 sm:p-12 space-y-10">
    <div class="text-center max-w-xl mx-auto">
      <span class="rf-badge rf-badge-sage uppercase tracking-wider mb-3">Transparent Pricing</span>
      <h2 class="text-2xl sm:text-3xl font-semibold text-[#1C1B18] tracking-tight">Affordable plans with zero hidden fees</h2>
      <p class="text-sm text-[#5C5A54] mt-2">Built to keep software overhead predictable for small business owners.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
      <div class="border border-[#E6E4DF] rounded-xl p-7 flex flex-col bg-[#FAF9F6]">
        <span class="rf-badge rf-badge-sage self-start mb-4">Complimentary Evaluation</span>
        <h3 class="text-lg font-semibold text-[#1C1B18]">1-Month Free Trial</h3>
        <div class="text-3xl font-semibold text-[#1C1B18] my-3 tabular-nums">₹0</div>
        <p class="text-xs text-[#5C5A54] mb-6">Full access to all modules for 30 days. No card details needed.</p>
        <ul class="space-y-2.5 text-sm text-[#5C5A54] mb-8 list-none p-0 flex-1">
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Up to 2 Business Firms</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Full Digital Sales &amp; Purchase Records</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> FIFO Customer &amp; Supplier Ledgers</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Live Stock &amp; Inventory Tracking</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Accountant GST-Ready Exports</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Offline Backup Downloads (.json)</li>
        </ul>
        <a href="/free-trial" class="rf-btn rf-btn-secondary w-full mt-auto">
          <i class="bi bi-gift"></i> View Free Trial Details
        </a>
      </div>

      <div class="border border-[#1C1B18] rounded-xl p-7 flex flex-col bg-white">
        <span class="rf-badge rf-badge-neutral self-start mb-4">Complete Business Suite</span>
        <h3 class="text-lg font-semibold text-[#1C1B18]">Annual Subscription</h3>
        <div class="text-lg font-semibold text-[#2B526E] my-3">Affordable Small Business Plan</div>
        <p class="text-xs text-[#5C5A54] mb-6">Provisioned directly for your business with dedicated onboarding support.</p>
        <ul class="space-y-2.5 text-sm text-[#5C5A54] mb-8 list-none p-0 flex-1">
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Everything in Free Trial included</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Dedicated WhatsApp &amp; Phone Support</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Zero Account Inspection &amp; High Privacy</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Automatic Platform Feature Updates</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Up to 200 MB Storage Allocation</li>
          <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> 1-Year Account Data Protection</li>
        </ul>
        <a href="/pricing" class="rf-btn rf-btn-primary w-full mt-auto">
          <i class="bi bi-tags"></i> View All Plan Details
        </a>
      </div>
    </div>
  </section>

  <!-- Minimalist FAQ -->
  <section id="faq" class="max-w-3xl mx-auto space-y-8">
    <div class="text-center">
      <span class="rf-badge rf-badge-neutral uppercase tracking-wider mb-3">Questions</span>
      <h2 class="text-2xl sm:text-3xl font-semibold text-[#1C1B18] tracking-tight">Frequently Asked Questions</h2>
    </div>

    <div class="divide-y divide-[#E6E4DF] border-t border-b border-[#E6E4DF]" id="landingFaqAccordion">
      <div class="py-5">
        <button class="w-full flex items-center justify-between gap-4 text-left font-medium text-[#1C1B18] text-sm sm:text-base bg-transparent border-0 cursor-pointer" type="button" data-ui-collapse="#collapseOne" aria-expanded="true">
          <span>What is RACE FINANCE?</span>
          <i class="bi bi-plus-lg text-xs text-[#78746C] shrink-0"></i>
        </button>
        <div id="collapseOne" data-ui-collapse-panel data-ui-parent="#landingFaqAccordion" class="pt-3 text-sm text-[#5C5A54] leading-relaxed">
          <strong>RACE FINANCE</strong> is an integrated small business billing, inventory accounting, and party ledger utility built for Indian merchants, traders, and small enterprise owners. It replaces messy paper khatabooks and complex ERPs with a clean web platform for invoicing, FIFO settlement, and stock tracking.
        </div>
      </div>

      <div class="py-5">
        <button class="w-full flex items-center justify-between gap-4 text-left font-medium text-[#1C1B18] text-sm sm:text-base bg-transparent border-0 cursor-pointer" type="button" data-ui-collapse="#collapseTwo" aria-expanded="false">
          <span>How does the 1-Month Free Trial work?</span>
          <i class="bi bi-plus-lg text-xs text-[#78746C] shrink-0"></i>
        </button>
        <div id="collapseTwo" data-ui-collapse-panel data-ui-parent="#landingFaqAccordion" class="hidden pt-3 text-sm text-[#5C5A54] leading-relaxed">
          Every new user receives a <strong>1-Month (30 days) Complimentary Free Trial</strong> with all features unlocked. No payment card or bank details are required. You can manage 2 firms, create unlimited invoices, track ledgers, and download offline backups before deciding whether to subscribe.
        </div>
      </div>

      <div class="py-5">
        <button class="w-full flex items-center justify-between gap-4 text-left font-medium text-[#1C1B18] text-sm sm:text-base bg-transparent border-0 cursor-pointer" type="button" data-ui-collapse="#collapseThree" aria-expanded="false">
          <span>Can I manage multiple business firms under one account?</span>
          <i class="bi bi-plus-lg text-xs text-[#78746C] shrink-0"></i>
        </button>
        <div id="collapseThree" data-ui-collapse-panel data-ui-parent="#landingFaqAccordion" class="hidden pt-3 text-sm text-[#5C5A54] leading-relaxed">
          Yes. RACE FINANCE supports <strong>Dual-Firm Management</strong> — operate up to two distinct commercial entities under a single login, each with its own business name, logo, GSTIN, bank details, and independent ledger registers.
        </div>
      </div>

      <div class="py-5">
        <button class="w-full flex items-center justify-between gap-4 text-left font-medium text-[#1C1B18] text-sm sm:text-base bg-transparent border-0 cursor-pointer" type="button" data-ui-collapse="#collapseFour" aria-expanded="false">
          <span>Is my business and financial data private and secure?</span>
          <i class="bi bi-plus-lg text-xs text-[#78746C] shrink-0"></i>
        </button>
        <div id="collapseFour" data-ui-collapse-panel data-ui-parent="#landingFaqAccordion" class="hidden pt-3 text-sm text-[#5C5A54] leading-relaxed">
          Yes. We operate under a <strong>Zero-Knowledge Security Architecture</strong>. Passwords undergo one-way cryptographic bcrypt hashing, administrators do not inspect your daily receipts or party ledgers, and you can export complete JSON backups to your computer anytime.
        </div>
      </div>

      <div class="py-5">
        <button class="w-full flex items-center justify-between gap-4 text-left font-medium text-[#1C1B18] text-sm sm:text-base bg-transparent border-0 cursor-pointer" type="button" data-ui-collapse="#collapseFive" aria-expanded="false">
          <span>How do I get my login account created?</span>
          <i class="bi bi-plus-lg text-xs text-[#78746C] shrink-0"></i>
        </button>
        <div id="collapseFive" data-ui-collapse-panel data-ui-parent="#landingFaqAccordion" class="hidden pt-3 text-sm text-[#5C5A54] leading-relaxed">
          Accounts are provisioned directly by our onboarding team to prevent spam. Click <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20start%20my%201-Month%20Free%20Trial." target="_blank" class="text-[#2E5A36] font-medium hover:underline">WhatsApp Onboarding</a> or call <strong><?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?></strong> to get activated.
        </div>
      </div>
    </div>
  </section>

  <!-- Final CTA -->
  <section class="rf-card-muted p-8 sm:p-14 text-center max-w-4xl mx-auto">
    <h2 class="text-xl sm:text-2xl font-semibold text-[#1C1B18] tracking-tight mb-2">Ready to simplify your billing &amp; ledgers?</h2>
    <p class="text-sm text-[#5C5A54] max-w-lg mx-auto mb-7">
      Join businesses using RACE FINANCE for clear customer statements, fast invoicing, and accurate stock records.
    </p>
    <div class="flex flex-wrap justify-center gap-3">
      <a href="/free-trial" class="rf-btn rf-btn-primary">
        <i class="bi bi-gift"></i> Start 1-Month Free Trial
      </a>
      <a href="/about" class="rf-btn rf-btn-secondary">Learn More</a>
      <a href="/contact" class="rf-btn rf-btn-ghost">Contact Support Desk</a>
    </div>
  </section>
</div>
