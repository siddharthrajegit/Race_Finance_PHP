<div class="max-w-4xl mx-auto space-y-8 pb-8">
  <!-- Portal Header -->
  <div class="text-center max-w-2xl mx-auto pt-2">
    <span class="rf-badge rf-badge-neutral mb-3">
      <i class="bi <?= $activeTab === 'free-trial' ? 'bi-gift' : ($activeTab === 'about' ? 'bi-info-circle' : ($activeTab === 'pricing' ? 'bi-tags' : ($activeTab === 'contact' ? 'bi-headset' : 'bi-shield-check'))) ?>"></i>
      <?= $activeTab === 'free-trial' ? 'Complimentary 30-Day Evaluation' : ($activeTab === 'about' ? 'Platform Overview & Capabilities' : ($activeTab === 'pricing' ? 'Transparent Subscription Plans' : ($activeTab === 'contact' ? 'Official Support & Inquiries' : 'Legal & Governance Center'))) ?>
    </span>
    <h1 class="text-2xl sm:text-3xl font-semibold text-[#1C1B18] tracking-tight">
      <?= $activeTab === 'free-trial' ? 'RACE FINANCE 1-Month Free Trial' : ($activeTab === 'about' ? 'About RACE FINANCE' : ($activeTab === 'pricing' ? 'RACE FINANCE Pricing & Plans' : ($activeTab === 'contact' ? 'Contact RACE FINANCE Desk' : 'Legal, Privacy & Terms'))) ?>
    </h1>
    <p class="text-sm text-[#5C5A54] mt-2 leading-relaxed">
      <?= $activeTab === 'free-trial'
        ? 'Enjoy 30 days of full, unrestricted access to RACE FINANCE. Experience dual-firm billing, inventory management, and FIFO party ledgers with zero setup cost.'
        : ($activeTab === 'about'
            ? 'An integrated, lightweight business management and digital bookkeeping utility engineered specifically for small business owners, traders, and retail merchants.'
            : ($activeTab === 'pricing'
                ? 'Straightforward, transparent pricing designed to be accessible for every small business. Start with 1-Month Free.'
                : ($activeTab === 'contact'
                    ? 'Connect with our team for account provisioning, technical support, 1-month free trial onboarding, or commercial inquiries.'
                    : 'Transparent policies, zero-knowledge security commitments, and clear commercial terms governing your use of the RACE FINANCE platform.'))) ?>
    </p>
  </div>

  <!-- Main Content Card -->
  <div class="rf-card p-6 sm:p-10">
    <?php if ($activeTab === 'free-trial'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">1-Month Complimentary Evaluation</h2>
          <div class="text-xs text-[#78746C] mt-1">Full-featured 30-day access &bull; Zero financial commitment &bull; WhatsApp onboarding</div>
        </div>
        <a href="https://wa.me/<?= $supportPhoneRaw ?? '919672847747' ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="rf-btn rf-btn-sage rf-btn-sm">
          <i class="bi bi-whatsapp"></i> Start 1-Month Free Trial
        </a>
      </div>

      <div class="space-y-6 text-sm text-[#5C5A54] leading-relaxed">
        <p class="text-[#1C1B18]">
          Welcome to the <strong>RACE FINANCE</strong> complimentary evaluation program. We believe every Indian small business owner, merchant, and trader deserves to experience calm billing and ledger management before investing in software.
        </p>

        <div class="rounded-xl bg-[#FAF9F6] border border-[#E2DFD7] p-6 flex flex-wrap items-center justify-between gap-4">
          <div class="max-w-lg">
            <h3 class="text-base font-semibold text-[#1C1B18] mb-1">30 Days of Complete Feature Access</h3>
            <p class="text-xs text-[#5C5A54] m-0">Your trial is <strong>100% unrestricted</strong>. Access dual-firm accounting, FIFO ledgers, inventory tracking, and full offline JSON backups.</p>
          </div>
          <div class="text-left sm:text-right">
            <div class="text-3xl font-semibold text-[#1C1B18] tabular-nums">₹0</div>
            <div class="text-xs text-[#78746C]">No Credit Card Needed</div>
          </div>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-[#1C1B18] mb-3 flex items-center gap-2"><i class="bi bi-check-circle text-[#2E5A36]"></i> Everything Unlocked in Your Trial</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            <div class="p-4 rounded-xl bg-[#FAF9F6] border border-[#ECEAE4]">
              <div class="font-semibold text-[#1C1B18] mb-1 flex items-center gap-2"><i class="bi bi-buildings text-[#2B526E]"></i> Dual-Firm Operations</div>
              <p class="text-xs text-[#5C5A54] m-0">Operate 2 independent business profiles with separate trade names, GSTINs, and billing formats.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#FAF9F6] border border-[#ECEAE4]">
              <div class="font-semibold text-[#1C1B18] mb-1 flex items-center gap-2"><i class="bi bi-receipt text-[#2E5A36]"></i> Unlimited GST &amp; Non-GST Bills</div>
              <p class="text-xs text-[#5C5A54] m-0">Create, edit, and print tax invoices, quotations, and bills with instant A4 and PDF output.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#FAF9F6] border border-[#ECEAE4]">
              <div class="font-semibold text-[#1C1B18] mb-1 flex items-center gap-2"><i class="bi bi-journal-text text-[#8A5A00]"></i> FIFO Party Ledgers</div>
              <p class="text-xs text-[#5C5A54] m-0">Track customer and vendor balances with chronological FIFO payment allocation and receipts.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#FAF9F6] border border-[#ECEAE4]">
              <div class="font-semibold text-[#1C1B18] mb-1 flex items-center gap-2"><i class="bi bi-box-seam text-[#2B526E]"></i> Live Inventory Tracking</div>
              <p class="text-xs text-[#5C5A54] m-0">Real-time stock deduction, multi-unit measurements (PCS, KG, BOX), and low-stock threshold alerts.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#FAF9F6] border border-[#ECEAE4]">
              <div class="font-semibold text-[#1C1B18] mb-1 flex items-center gap-2"><i class="bi bi-file-earmark-bar-graph text-[#8F2D2B]"></i> Accountant GST Exports</div>
              <p class="text-xs text-[#5C5A54] m-0">Export structured sales and purchase registers to help your accountant prepare GSTR returns.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#FAF9F6] border border-[#ECEAE4]">
              <div class="font-semibold text-[#1C1B18] mb-1 flex items-center gap-2"><i class="bi bi-cloud-download text-[#1C1B18]"></i> 100% Data Portability</div>
              <p class="text-xs text-[#5C5A54] m-0">Download complete structured JSON backups of your items, parties, and invoices anytime.</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-[#F3F1EC] border border-[#E2DFD7] p-6">
          <h3 class="text-sm font-semibold text-[#1C1B18] mb-4">How to Activate Your Free Trial</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
            <div>
              <div class="font-semibold text-[#1C1B18] mb-1">1. Contact Onboarding</div>
              <p class="text-[#5C5A54] m-0">Click WhatsApp below or call <strong><?= htmlspecialchars($supportPhone ?? '+91 9672847747', ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            </div>
            <div>
              <div class="font-semibold text-[#1C1B18] mb-1">2. Share Business Details</div>
              <p class="text-[#5C5A54] m-0">Provide your firm/shop name and 10-digit mobile number to use as your login ID.</p>
            </div>
            <div>
              <div class="font-semibold text-[#1C1B18] mb-1">3. Immediate Access</div>
              <p class="text-[#5C5A54] m-0">Receive your temporary password and sign in at <a href="/auth/login" class="text-[#2B526E] font-medium hover:underline">racefinance.site/auth/login</a>.</p>
            </div>
          </div>
        </div>

        <div class="text-center pt-2">
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="rf-btn rf-btn-sage-solid rf-btn-lg">
            <i class="bi bi-whatsapp"></i> Activate 1-Month Free Trial on WhatsApp
          </a>
        </div>
      </div>

    <?php elseif ($activeTab === 'about'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Empowering Small Business Management</h2>
          <div class="text-xs text-[#78746C] mt-1">Streamlined operations, ledger clarity, and digital reference records</div>
        </div>
        <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="rf-btn rf-btn-sage rf-btn-sm">
          <i class="bi bi-whatsapp"></i> Start 1-Month Free Trial
        </a>
      </div>

      <div class="space-y-6 text-sm text-[#5C5A54] leading-relaxed">
        <p class="text-[#1C1B18]">
          <strong>RACE FINANCE</strong> is a modern business utility engineered to simplify daily commercial management. It unifies firm administration, sales and purchase documentation, customer ledgers, and inventory tracking into one fast, distraction-free web platform.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-5 rounded-xl bg-[#FAF9F6] border border-[#E6E4DF]">
            <div class="flex items-center gap-2.5 mb-2">
              <span class="w-8 h-8 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center"><i class="bi bi-buildings"></i></span>
              <h3 class="text-sm font-semibold text-[#1C1B18]">Dual-Firm Management</h3>
            </div>
            <p class="text-xs text-[#5C5A54] m-0">Operate up to two independent business entities under one subscription with separate trade profiles, logos, banking setups, and isolated party registers.</p>
          </div>
          <div class="p-5 rounded-xl bg-[#FAF9F6] border border-[#E6E4DF]">
            <div class="flex items-center gap-2.5 mb-2">
              <span class="w-8 h-8 rounded-lg bg-[#EDF3EC] border border-[#CFE0CE] text-[#2E5A36] flex items-center justify-center"><i class="bi bi-receipt"></i></span>
              <h3 class="text-sm font-semibold text-[#1C1B18]">Fast Record Keeping</h3>
            </div>
            <p class="text-xs text-[#5C5A54] m-0">Create structured digital sales and purchase records in seconds with itemized discounts, GST/Non-GST breakdowns, and quick party/item modals.</p>
          </div>
          <div class="p-5 rounded-xl bg-[#FAF9F6] border border-[#E6E4DF]">
            <div class="flex items-center gap-2.5 mb-2">
              <span class="w-8 h-8 rounded-lg bg-[#FBF3DB] border border-[#EAE0B8] text-[#8A5A00] flex items-center justify-center"><i class="bi bi-journal-text"></i></span>
              <h3 class="text-sm font-semibold text-[#1C1B18]">Smart Party Ledgers &amp; FIFO</h3>
            </div>
            <p class="text-xs text-[#5C5A54] m-0">Maintain real-time customer and vendor statements with chronological FIFO payment allocation and printable receipt slips.</p>
          </div>
          <div class="p-5 rounded-xl bg-[#FAF9F6] border border-[#E6E4DF]">
            <div class="flex items-center gap-2.5 mb-2">
              <span class="w-8 h-8 rounded-lg bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] flex items-center justify-center"><i class="bi bi-box-seam"></i></span>
              <h3 class="text-sm font-semibold text-[#1C1B18]">Live Inventory &amp; Alerts</h3>
            </div>
            <p class="text-xs text-[#5C5A54] m-0">Track stock counts across customizable measurement units with automatic adjustments and early low-stock warnings.</p>
          </div>
          <div class="p-5 rounded-xl bg-[#FAF9F6] border border-[#E6E4DF]">
            <div class="flex items-center gap-2.5 mb-2">
              <span class="w-8 h-8 rounded-lg bg-[#FBEAEA] border border-[#ECD0D0] text-[#8F2D2B] flex items-center justify-center"><i class="bi bi-file-earmark-bar-graph"></i></span>
              <h3 class="text-sm font-semibold text-[#1C1B18]">Accountant GST Registers</h3>
            </div>
            <p class="text-xs text-[#5C5A54] m-0">Generate clean periodic tax summaries and record-wise GST registers ready for your accountant or tax consultant.</p>
          </div>
          <div class="p-5 rounded-xl bg-[#FAF9F6] border border-[#E6E4DF]">
            <div class="flex items-center gap-2.5 mb-2">
              <span class="w-8 h-8 rounded-lg bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] flex items-center justify-center"><i class="bi bi-shield-lock"></i></span>
              <h3 class="text-sm font-semibold text-[#1C1B18]">Zero-Knowledge Portability</h3>
            </div>
            <p class="text-xs text-[#5C5A54] m-0">Export offline <code class="font-mono bg-white px-1 rounded">.json</code> backups anytime, secured by bcrypt password hashing and zero admin inspection.</p>
          </div>
        </div>

        <div class="rounded-xl bg-[#1C1B18] text-white p-6 flex flex-wrap items-center justify-between gap-4">
          <div>
            <h3 class="text-base font-semibold mb-1">Start Your 1-Month Complimentary Trial</h3>
            <p class="text-xs text-[#B8B4AA] m-0 max-w-lg">No credit card required. Experience complete billing, inventory, and ledger control today.</p>
          </div>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business.%20Please%20provide%20my%20login%20credentials." target="_blank" class="rf-btn bg-white text-[#1C1B18] hover:bg-[#F2F0EB]">
            <i class="bi bi-whatsapp text-[#2E5A36]"></i> WhatsApp Onboarding
          </a>
        </div>

        <div class="text-center text-xs text-[#78746C]">
          Have questions? Direct line: <a href="tel:+<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>" class="text-[#1C1B18] font-medium no-underline"><?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?></a> &bull; Email: <a href="mailto:support@<?= htmlspecialchars($appDomain ?? 'racefinance.site', ENT_QUOTES, 'UTF-8') ?>" class="text-[#1C1B18] font-medium no-underline">support@<?= htmlspecialchars($appDomain ?? 'racefinance.site', ENT_QUOTES, 'UTF-8') ?></a>
        </div>
      </div>

    <?php elseif ($activeTab === 'pricing'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Transparent &amp; Simple Pricing</h2>
          <div class="text-xs text-[#78746C] mt-1">Everything included &bull; 1-Month Free Evaluation &bull; Zero Hidden Costs</div>
        </div>
        <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="rf-btn rf-btn-sage rf-btn-sm">
          <i class="bi bi-whatsapp"></i> Start 1-Month Free Trial
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="border border-[#E6E4DF] rounded-xl p-6 flex flex-col bg-[#FAF9F6]">
          <span class="rf-badge rf-badge-sage self-start mb-4">Complimentary Evaluation</span>
          <h3 class="text-lg font-semibold text-[#1C1B18]">1-Month Free Trial</h3>
          <div class="text-3xl font-semibold text-[#1C1B18] my-2 tabular-nums">₹0</div>
          <p class="text-xs text-[#5C5A54] mb-5">Full access to all features for 30 days. No credit card required.</p>
          <ul class="space-y-2 text-xs text-[#5C5A54] mb-6 list-none p-0 flex-1">
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Up to 2 Business Firms</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Full Digital Sales &amp; Purchase Records</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> FIFO Customer &amp; Supplier Ledgers</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Live Stock &amp; Inventory Tracking</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Accountant GST-Ready Exports</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#2E5A36]"></i> Offline Backup Downloads (.json)</li>
          </ul>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20start%20my%20complimentary%201-Month%20Free%20Trial." target="_blank" class="rf-btn rf-btn-secondary w-full mt-auto">
            <i class="bi bi-whatsapp"></i> Request Free Trial
          </a>
        </div>

        <div class="border border-[#1C1B18] rounded-xl p-6 flex flex-col bg-white">
          <span class="rf-badge rf-badge-neutral self-start mb-4">Complete Business Suite</span>
          <h3 class="text-lg font-semibold text-[#1C1B18]">Annual Subscription</h3>
          <div class="text-lg font-semibold text-[#2B526E] my-2">Affordable Small Business Plan</div>
          <p class="text-xs text-[#5C5A54] mb-5">Provisioned directly for your business with dedicated support.</p>
          <ul class="space-y-2 text-xs text-[#5C5A54] mb-6 list-none p-0 flex-1">
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Everything in Free Trial included</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Dedicated WhatsApp &amp; Phone Support</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Zero Account Inspection &amp; High Privacy</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Automatic Platform Feature Updates</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> Up to 200 MB Storage Allocation</li>
            <li class="flex items-center gap-2"><i class="bi bi-check2 text-[#1C1B18]"></i> 1-Year Account Data Protection</li>
          </ul>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hi%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20inquire%20about%20the%20annual%20subscription%20pricing." target="_blank" class="rf-btn rf-btn-primary w-full mt-auto">
            <i class="bi bi-whatsapp"></i> Contact for Plan Pricing
          </a>
        </div>
      </div>

    <?php elseif ($activeTab === 'contact'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Get in Touch with Our Team</h2>
          <div class="text-xs text-[#78746C] mt-1">Instant WhatsApp onboarding, phone assistance &amp; security support</div>
        </div>
        <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20have%20an%20inquiry%20regarding%20the%20platform." target="_blank" class="rf-btn rf-btn-sage rf-btn-sm">
          <i class="bi bi-whatsapp"></i> Chat on WhatsApp
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="border border-[#E6E4DF] rounded-xl p-6 text-center flex flex-col bg-[#FAF9F6]">
          <div class="w-12 h-12 rounded-full bg-[#EDF3EC] border border-[#CFE0CE] text-[#2E5A36] mx-auto mb-4 flex items-center justify-center text-xl">
            <i class="bi bi-whatsapp"></i>
          </div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-1">WhatsApp Desk</h3>
          <p class="text-xs text-[#5C5A54] mb-3">Fastest response for onboarding, free trial requests, and active account help.</p>
          <div class="text-sm font-semibold text-[#1C1B18] font-mono mb-4"><?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?></div>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20need%20assistance%20with%20my%20business%20account." target="_blank" class="rf-btn rf-btn-sage rf-btn-sm mt-auto">Message Support</a>
        </div>

        <div class="border border-[#E6E4DF] rounded-xl p-6 text-center flex flex-col bg-[#FAF9F6]">
          <div class="w-12 h-12 rounded-full bg-[#EBF2F7] border border-[#CFDFEB] text-[#2B526E] mx-auto mb-4 flex items-center justify-center text-xl">
            <i class="bi bi-telephone"></i>
          </div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-1">Phone Line</h3>
          <p class="text-xs text-[#5C5A54] mb-3">Direct executive line for urgent account queries and billing verification.</p>
          <div class="text-sm font-semibold text-[#1C1B18] font-mono mb-4"><?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?></div>
          <a href="tel:+<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm mt-auto">Call Directly</a>
        </div>

        <div class="border border-[#E6E4DF] rounded-xl p-6 text-center flex flex-col bg-[#FAF9F6]">
          <div class="w-12 h-12 rounded-full bg-[#F3F1EC] border border-[#E2DFD7] text-[#1C1B18] mx-auto mb-4 flex items-center justify-center text-xl">
            <i class="bi bi-envelope"></i>
          </div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-1">Official Email</h3>
          <p class="text-xs text-[#5C5A54] mb-3">Formal business inquiries, partnership requests, and documentation feedback.</p>
          <div class="text-xs font-semibold text-[#1C1B18] font-mono mb-4 break-all">support@<?= htmlspecialchars($appDomain ?? 'racefinance.site', ENT_QUOTES, 'UTF-8') ?></div>
          <a href="mailto:support@<?= htmlspecialchars($appDomain ?? 'racefinance.site', ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm mt-auto">Send Email</a>
        </div>
      </div>

      <div class="mt-6 rounded-xl bg-[#F3F1EC] border border-[#E2DFD7] p-5 flex flex-wrap items-center justify-between gap-4">
        <div>
          <div class="text-sm font-semibold text-[#1C1B18]"><i class="bi bi-clock-history mr-1.5"></i> Operational Hours</div>
          <p class="text-xs text-[#5C5A54] mt-1 m-0">Monday through Saturday: 9:00 AM &ndash; 8:00 PM IST. After-hours inquiries are answered the next morning.</p>
        </div>
        <span class="rf-badge rf-badge-sage"><i class="bi bi-shield-check"></i> Official Verified Desk</span>
      </div>

    <?php elseif ($activeTab === 'terms'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Terms and Conditions of Service</h2>
          <div class="text-xs text-[#78746C] mt-1">Effective Date: January 1, 2026 &bull; Last Updated: August 2026</div>
        </div>
        <span class="rf-badge rf-badge-sage">Standard Commercial License</span>
      </div>
      <div class="space-y-5 text-sm text-[#5C5A54] leading-relaxed">
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">1. Acceptance of Terms</h3>
          <p>By logging into, using, or subscribing to the <strong>RACE FINANCE</strong> platform ("Service"), you acknowledge that you have read, understood, and agreed to be legally bound by these Terms and Conditions.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">2. Account Provisioning &amp; User Custody</h3>
          <p>Account access is granted via direct administrative provisioning. Public self-registration is disabled. You are solely responsible for maintaining the confidentiality of your credentials.</p>
        </div>
        <div class="rf-alert rf-alert-warning my-2">
          <i class="bi bi-shield-exclamation text-base mt-0.5 shrink-0"></i>
          <div>
            <strong class="text-[#1C1B18]">Zero-Knowledge Password Security:</strong>
            <p class="text-xs mt-1 m-0">All passwords undergo one-way cryptographic hashing (<code class="font-mono bg-white/70 px-1 rounded">bcrypt</code>). Administrators do <strong>not</strong> possess access to your plain-text password. You bear sole responsibility for safeguarding your credentials.</p>
          </div>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">3. Data Ownership &amp; Local Portability</h3>
          <p>You retain exclusive ownership over all business data, inventory records, customer ledgers, and transactions entered into the system, and may download structured <code class="font-mono bg-[#F3F1EC] px-1 rounded">.json</code> backups anytime.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">4. Account Lifecycle &amp; Inactive Account Purge Policy</h3>
          <p>Subscribers receive a <strong>1-Month Complimentary Free Trial</strong> upon onboarding. Following subscription expiry, if an account remains un-renewed for <strong>one continuous year (12 consecutive months)</strong>, the account and associated database records will be permanently deleted.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">5. Nature of Utility</h3>
          <p>RACE FINANCE operates strictly as an internal record-keeping, inventory tracking, and billing utility. Subscribers remain responsible for verifying tax classifications and submitting formal returns via a Chartered Accountant or the official GST portal.</p>
        </div>
      </div>

    <?php elseif ($activeTab === 'privacy'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Privacy Policy</h2>
          <div class="text-xs text-[#78746C] mt-1">Strict Zero-Knowledge &amp; Data Confidentiality Protocol</div>
        </div>
        <span class="rf-badge rf-badge-slate">Confidential &amp; Protected</span>
      </div>
      <div class="space-y-5 text-sm text-[#5C5A54] leading-relaxed">
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">1. Zero Account Inspection Policy</h3>
          <p><strong>Platform administrators and staff do not browse, inspect, or log into your subscriber account</strong> for commercial curiosity. Diagnostic access is strictly restricted to system-level uptime maintenance.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">2. Password &amp; Credential Hashing</h3>
          <p>Your account password is encrypted using salted one-way cryptographic algorithms before storage. Our team cannot retrieve or view your password under any circumstance.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">3. Information We Store</h3>
          <p>The platform stores only business data entered by you, including registered firm names, GSTIN, inventory items, prices, party details, and billing records.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">4. Local Offline Portability</h3>
          <p>Export your full database snapshot to your local device anytime via the Backup portal without proprietary lock-in.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">5. Reporting Suspicious Activity</h3>
          <p>If you detect irregular behavior or suspect compromised credentials, notify our security team immediately at <strong><?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?></strong> or via WhatsApp.</p>
        </div>
      </div>

    <?php elseif ($activeTab === 'refund'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Refund and Cancellation Policy</h2>
          <div class="text-xs text-[#78746C] mt-1">Transparent Subscription &amp; Payment Terms</div>
        </div>
        <span class="rf-badge rf-badge-amber">Non-Refundable Policy</span>
      </div>
      <div class="space-y-5 text-sm text-[#5C5A54] leading-relaxed">
        <div class="rf-alert rf-alert-info">
          <i class="bi bi-gift text-base mt-0.5 shrink-0"></i>
          <div>
            <strong class="text-[#1C1B18]">1-Month Complimentary Evaluation Period:</strong>
            <p class="text-xs mt-1 m-0">RACE FINANCE provides every new subscriber with a <strong>1-Month Full-Featured Free Trial</strong> to evaluate all billing, inventory, reporting, and ledger capabilities with zero commitment before purchasing.</p>
          </div>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">1. Non-Refundable Subscription Fees</h3>
          <p>Because every business owner receives full evaluation access during the 1-month trial prior to payment, <strong>all subscription fees paid for renewals, annual plans, or upgrades are strictly non-refundable</strong> once processed.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">2. Subscription Cancellation</h3>
          <p>You may choose not to renew at the conclusion of your active term without penalty fees. Data remains preserved for up to one year before scheduled removal.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">3. Billing Queries &amp; Assistance</h3>
          <p>If a duplicate payment or billing discrepancy occurred during renewal, contact support within 7 days at <strong><?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?></strong>.</p>
        </div>
      </div>

    <?php elseif ($activeTab === 'disclaimer'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">Legal &amp; Tax Compliance Disclaimer</h2>
          <div class="text-xs text-[#78746C] mt-1">Record-Management Utility Notice</div>
        </div>
        <span class="rf-badge rf-badge-neutral">Statutory Advisory</span>
      </div>
      <div class="space-y-5 text-sm text-[#5C5A54] leading-relaxed">
        <div class="rounded-xl bg-[#FAF9F6] border border-[#E6E4DF] p-5">
          <div class="font-semibold text-[#1C1B18] mb-1.5"><i class="bi bi-info-circle mr-1.5"></i>General Notice</div>
          <p class="text-xs text-[#5C5A54] m-0">This software is intended for basic business record-keeping, inventory tracking, and internal billing management. It is not a substitute for a Chartered Accountant, tax professional, or official government GST filing system. Users are responsible for verifying HSN/SAC codes and tax treatment.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">1. Digital Reference Utility</h3>
          <p>The sales and purchase records, balance sheets, and summaries produced serve as internal digital reference documents and do not certify statutory government tax returns.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">2. User Responsibility for Data Accuracy</h3>
          <p>Calculation of CGST, SGST, IGST, discounts, and totals relies on input supplied by the subscriber. Verify rates, party GSTINs, and state tax codes before finalizing filings.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">3. Limitation of Liability</h3>
          <p>RACE FINANCE and its administrators shall not be held liable for indirect, incidental, or consequential damages, tax penalties, or losses arising from data entry discrepancies or network interruptions.</p>
        </div>
      </div>

    <?php elseif ($activeTab === 'security'): ?>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-7 border-b border-[#ECEAE4]">
        <div>
          <h2 class="text-lg font-semibold text-[#1C1B18]">User Data Security &amp; Password Notice</h2>
          <div class="text-xs text-[#78746C] mt-1">Safeguarding Your Business Credentials</div>
        </div>
        <span class="rf-badge rf-badge-sage">Cryptographic Safeguards</span>
      </div>
      <div class="space-y-5 text-sm text-[#5C5A54] leading-relaxed">
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">1. Password Custody &amp; One-Way Hashing</h3>
          <p>All passwords are encrypted using one-way hashing functions. Because our system never stores plain-text passwords, <strong>we cannot recover or read your password</strong>.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">2. Strict Non-Intervention Policy</h3>
          <p>Administrators do not access your user session, browse ledger entries, or inspect day-to-day business receipts.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">3. Routine Local Backups</h3>
          <p>Every subscriber is encouraged to download routine offline backup files (<code class="font-mono bg-[#F3F1EC] px-1 rounded">.json</code>) via the Backup tab.</p>
        </div>
        <div>
          <h3 class="text-base font-semibold text-[#1C1B18] mb-2">4. Emergency Security Hotline</h3>
          <p class="mb-4">Report any suspicious activity immediately through our official channels:</p>
          <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=SECURITY%20ALERT:%20I%20noticed%20suspicious%20activity%20on%20my%20RACE%20FINANCE%20account." class="rf-btn rf-btn-sage rf-btn-sm">
              <i class="bi bi-whatsapp"></i> Report on WhatsApp (<?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?>)
            </a>
            <a href="tel:+<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>" class="rf-btn rf-btn-secondary rf-btn-sm">
              <i class="bi bi-telephone"></i> Call Security Desk (<?= htmlspecialchars($supportPhone ?? '+91 96728 47747', ENT_QUOTES, 'UTF-8') ?>)
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Links Bar -->
  <div class="rf-card-muted px-5 py-4 text-center">
    <div class="flex flex-wrap justify-center gap-x-4 gap-y-1.5 text-xs text-[#78746C]">
      <a href="/free-trial" class="no-underline <?= $activeTab === 'free-trial' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">1-Month Free Trial</a>
      <a href="/about" class="no-underline <?= $activeTab === 'about' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">About &amp; Features</a>
      <a href="/pricing" class="no-underline <?= $activeTab === 'pricing' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Pricing</a>
      <a href="/contact" class="no-underline <?= $activeTab === 'contact' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Contact</a>
      <a href="/terms" class="no-underline <?= $activeTab === 'terms' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Terms &amp; Conditions</a>
      <a href="/privacy" class="no-underline <?= $activeTab === 'privacy' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Privacy Policy</a>
      <a href="/refund-policy" class="no-underline <?= $activeTab === 'refund' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Refund Policy</a>
      <a href="/disclaimer" class="no-underline <?= $activeTab === 'disclaimer' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Legal Disclaimer</a>
      <a href="/security" class="no-underline <?= $activeTab === 'security' ? 'font-semibold text-[#1C1B18]' : 'hover:text-[#1C1B18]' ?>">Security &amp; Passwords</a>
    </div>
  </div>
</div>
