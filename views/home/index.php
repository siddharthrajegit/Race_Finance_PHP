<div class="landing-page">
  <!-- Hero Section -->
  <section class="py-5 text-center position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-9">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold mb-3 shadow-xs">
          <i class="bi bi-patch-check-fill me-1"></i> Smart Small Business Billing, Inventory & Accounting
        </span>
        
        <h1 class="display-5 fw-extrabold text-dark tracking-tight mb-3">
          Simpler Invoicing. Clearer Ledgers.<br class="d-none d-md-inline"> 
          <span class="text-primary">Engineered for Indian Small Businesses.</span>
        </h1>
        
        <p class="lead text-secondary mx-auto mb-4" style="max-width: 720px; font-size: 1.15rem; line-height: 1.6;">
          <strong>RACE FINANCE</strong> is an all-in-one business management utility designed for traders, retailers, and merchants. Run up to 2 firms, track FIFO customer statements, manage live inventory, and prepare GST-ready registers without accounting complexity.
        </p>

        <!-- CTA Action Buttons -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
          <a href="/free-trial" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow-sm d-inline-flex align-items-center">
            <i class="bi bi-gift-fill me-2 fs-5"></i> Start 1-Month Free Trial
          </a>
          <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="btn btn-success btn-lg rounded-pill px-4 py-3 fw-bold shadow-sm d-inline-flex align-items-center">
            <i class="bi bi-whatsapp me-2 fs-5"></i> WhatsApp Onboarding
          </a>
          <a href="/auth/login" class="btn btn-outline-dark btn-lg rounded-pill px-4 py-3 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
          </a>
        </div>

        <!-- Key Trust Badges -->
        <div class="row g-3 justify-content-center text-start">
          <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 border shadow-xs text-center h-100">
              <div class="text-primary fw-bold fs-4 mb-0">30 Days</div>
              <div class="small text-muted fw-medium">100% Free Trial</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 border shadow-xs text-center h-100">
              <div class="text-success fw-bold fs-4 mb-0">2 Firms</div>
              <div class="small text-muted fw-medium">Under 1 Account</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 border shadow-xs text-center h-100">
              <div class="text-warning fw-bold fs-4 mb-0">FIFO</div>
              <div class="small text-muted fw-medium">Auto-Balancing Ledgers</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 border shadow-xs text-center h-100">
              <div class="text-info fw-bold fs-4 mb-0">Offline</div>
              <div class="small text-muted fw-medium">1-Click JSON Backups</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Core Features Grid -->
  <section class="py-5" id="features">
    <div class="text-center mb-5">
      <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill small fw-semibold text-uppercase">Platform Capabilities</span>
      <h2 class="fw-bold text-dark mt-2">Everything You Need to Run Your Business</h2>
      <p class="text-muted small mx-auto" style="max-width: 600px;">
        Designed to be clean, distraction-free, and lightning fast. No bloated software manuals required.
      </p>
    </div>

    <div class="row g-4">
      <!-- Feature 1 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white transition-all hover-shadow">
          <div class="rounded-3 bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
            <i class="bi bi-buildings fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Dual-Firm Management</h5>
          <p class="text-muted small mb-0 lh-base">
            Operate up to 2 distinct commercial firms under a single login. Switch seamlessly between them with isolated bill registers, independent logos, and distinct GSTIN numbers.
          </p>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white transition-all hover-shadow">
          <div class="rounded-3 bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
            <i class="bi bi-receipt fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">GST & Non-GST Invoicing</h5>
          <p class="text-muted small mb-0 lh-base">
            Create professional tax invoices, quotations, and bills with itemized discounts, HSN/SAC codes, and automatic tax breakdowns. Instant A4 printing and PDF generation.
          </p>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white transition-all hover-shadow">
          <div class="rounded-3 bg-warning-subtle text-warning p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
            <i class="bi bi-journal-text fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">FIFO Customer Ledgers</h5>
          <p class="text-muted small mb-0 lh-base">
            Full party accounts ledger tracking debits, credits, and live balances. Chronological FIFO settlement ensures payments automatically offset oldest outstanding invoices.
          </p>
        </div>
      </div>

      <!-- Feature 4 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white transition-all hover-shadow">
          <div class="rounded-3 bg-info-subtle text-info p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
            <i class="bi bi-box-seam fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Live Inventory & Stock Alerts</h5>
          <p class="text-muted small mb-0 lh-base">
            Automated stock deduction upon sales and addition on purchases. Receive instant low-stock alerts before products run out, with multi-unit measurement support.
          </p>
        </div>
      </div>

      <!-- Feature 5 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white transition-all hover-shadow">
          <div class="rounded-3 bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
            <i class="bi bi-file-earmark-bar-graph fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Accountant-Ready GST Reports</h5>
          <p class="text-muted small mb-0 lh-base">
            Export structured periodic sales, purchases, and tax summaries that your accountant or tax consultant can immediately use for preparing official GST portal returns.
          </p>
        </div>
      </div>

      <!-- Feature 6 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white transition-all hover-shadow">
          <div class="rounded-3 bg-dark-subtle text-dark p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
            <i class="bi bi-shield-lock fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">Zero-Knowledge & Full Portability</h5>
          <p class="text-muted small mb-0 lh-base">
            Your data belongs exclusively to you. One-click offline backup downloads (<code class="bg-light px-1">.json</code>) to your hard drive, bcrypt password encryption, and zero admin inspection.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 1-Month Free Trial Spotlight Banner -->
  <section class="py-4">
    <div class="card border-0 rounded-4 shadow-sm bg-gradient text-white p-4 p-md-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
      <div class="row align-items-center g-4">
        <div class="col-lg-8">
          <span class="badge bg-white text-primary px-3 py-1 rounded-pill fw-bold small mb-3">Complimentary 30 Days</span>
          <h3 class="fw-bold mb-2">Experience RACE FINANCE Risk-Free for 1 Month</h3>
          <p class="text-white-50 mb-4" style="max-width: 620px;">
            No credit card, no bank details, and no automated recurring charges. Test all features with full functionality, create your firms, and see how much time you save.
          </p>
          <div class="d-flex flex-wrap gap-3">
            <a href="/free-trial" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2 shadow-sm">
              <i class="bi bi-info-circle me-1"></i> Learn About Free Trial
            </a>
            <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20request%20access%20to%20start%20my%20complimentary%201-Month%20Free%20Trial%20for%20my%20business." target="_blank" class="btn btn-success fw-bold rounded-pill px-4 py-2 shadow-sm">
              <i class="bi bi-whatsapp me-1"></i> Activate on WhatsApp
            </a>
          </div>
        </div>
        <div class="col-lg-4 text-center text-lg-end">
          <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 d-inline-block text-start">
            <div class="fw-bold fs-5 text-white mb-2"><i class="bi bi-check-all text-warning me-1"></i> What's Included:</div>
            <ul class="list-unstyled text-white-50 small mb-0 lh-lg">
              <li><i class="bi bi-check-circle-fill text-warning me-2"></i>2 Business Firms Unlocked</li>
              <li><i class="bi bi-check-circle-fill text-warning me-2"></i>Unlimited Invoices & Quotes</li>
              <li><i class="bi bi-check-circle-fill text-warning me-2"></i>FIFO Party Ledgers</li>
              <li><i class="bi bi-check-circle-fill text-warning me-2"></i>Live Inventory Tracking</li>
              <li><i class="bi bi-check-circle-fill text-warning me-2"></i>GST Accountant Exports</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="py-5">
    <div class="text-center mb-5">
      <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill small fw-semibold text-uppercase">Simple Onboarding</span>
      <h2 class="fw-bold text-dark mt-2">Get Started in 3 Simple Steps</h2>
      <p class="text-muted small mx-auto" style="max-width: 500px;">
        Start managing your business records in less than 5 minutes.
      </p>
    </div>

    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-xs rounded-4 p-4 h-100">
          <div class="rounded-circle bg-primary text-white fw-bold fs-4 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">1</div>
          <h5 class="fw-bold text-dark mb-2">Claim Free Trial</h5>
          <p class="small text-muted mb-0">
            Reach out via WhatsApp or phone call. Our onboarding desk instantly sets up your business login credentials.
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-xs rounded-4 p-4 h-100">
          <div class="rounded-circle bg-primary text-white fw-bold fs-4 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">2</div>
          <h5 class="fw-bold text-dark mb-2">Add Your Firm & Items</h5>
          <p class="small text-muted mb-0">
            Enter your business trade name, GSTIN (optional), bank details, and add items with opening stock in seconds.
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-xs rounded-4 p-4 h-100">
          <div class="rounded-circle bg-primary text-white fw-bold fs-4 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">3</div>
          <h5 class="fw-bold text-dark mb-2">Record & Print Bills</h5>
          <p class="small text-muted mb-0">
            Create sales invoices, print A4 receipts, record payments, and monitor party ledgers automatically.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing Preview -->
  <section class="py-5 bg-white rounded-4 border p-4 p-md-5 my-4" id="pricing">
    <div class="text-center mb-5">
      <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill small fw-semibold text-uppercase">Transparent Pricing</span>
      <h2 class="fw-bold text-dark mt-2">Affordable Plans. Zero Hidden Costs.</h2>
      <p class="text-muted small mx-auto" style="max-width: 550px;">
        Designed specifically to keep overhead low for Indian small business owners.
      </p>
    </div>

    <div class="row g-4 justify-content-center">
      <!-- Free Trial Card -->
      <div class="col-md-6 col-lg-5">
        <div class="card border border-2 shadow-none rounded-4 p-4 h-100 text-center">
          <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mx-auto mb-3 small fw-semibold">
            Complimentary Evaluation
          </span>
          <h4 class="fw-bold text-dark mb-1">1-Month Free Trial</h4>
          <div class="display-6 fw-bold text-dark my-2">₹0</div>
          <p class="small text-muted mb-3">Full access to all features for 30 days. No credit card required.</p>
          <ul class="list-unstyled text-start small text-muted lh-lg mb-4 ps-2">
            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Up to 2 Business Firms</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Full Digital Sales & Purchase Records</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i>FIFO Customer & Supplier Ledgers</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Live Stock & Inventory Tracking</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Accountant GST-Ready Exports</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Offline Backup Downloads (.json)</li>
          </ul>
          <a href="/free-trial" class="btn btn-outline-success rounded-pill fw-semibold w-100 py-2 mt-auto">
            <i class="bi bi-gift-fill me-1"></i> View Free Trial Details
          </a>
        </div>
      </div>

      <!-- Annual Plan Card -->
      <div class="col-md-6 col-lg-5">
        <div class="card border border-primary border-2 shadow-sm rounded-4 p-4 h-100 text-center position-relative">
          <span class="badge bg-primary text-white px-3 py-1 rounded-pill mx-auto mb-3 small fw-semibold">
            Complete Business Suite
          </span>
          <h4 class="fw-bold text-dark mb-1">Annual Subscription</h4>
          <div class="my-2">
            <span class="fs-4 fw-bold text-primary">Affordable Small Business Plan</span>
          </div>
          <p class="small text-muted mb-3">Provisioned directly for your business with dedicated onboarding support.</p>
          <ul class="list-unstyled text-start small text-muted lh-lg mb-4 ps-2">
            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Everything in Free Trial included</li>
            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Dedicated WhatsApp & Phone Support</li>
            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Zero Account Inspection & High Privacy</li>
            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Automatic Platform Feature Updates</li>
            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Up to 200 MB Storage Allocation</li>
            <li><i class="bi bi-check-circle-fill text-primary me-2"></i>1-Year Account Data Protection</li>
          </ul>
          <a href="/pricing" class="btn btn-primary rounded-pill fw-semibold w-100 py-2 mt-auto">
            <i class="bi bi-tags me-1"></i> View All Plan Details
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- SEO Frequently Asked Questions (FAQ) Section -->
  <section class="py-5" id="faq">
    <div class="text-center mb-5">
      <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill small fw-semibold text-uppercase">Got Questions?</span>
      <h2 class="fw-bold text-dark mt-2">Frequently Asked Questions</h2>
      <p class="text-muted small mx-auto" style="max-width: 600px;">
        Everything you need to know about RACE FINANCE, account provisioning, and data safety.
      </p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="accordion shadow-xs rounded-4 overflow-hidden border-0" id="landingFaqAccordion">
          <!-- FAQ 1 -->
          <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-xs">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                What is RACE FINANCE?
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#landingFaqAccordion">
              <div class="accordion-body text-secondary small lh-lg">
                <strong>RACE FINANCE</strong> is an integrated small business billing, inventory accounting, and party ledger utility built for Indian merchants, traders, and small enterprise owners. It replaces messy paper khatabooks and complex enterprise ERPs with a clean, fast web platform for invoicing, FIFO settlement, and stock tracking.
              </div>
            </div>
          </div>

          <!-- FAQ 2 -->
          <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-xs">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                How does the 1-Month Free Trial work?
              </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#landingFaqAccordion">
              <div class="accordion-body text-secondary small lh-lg">
                Every new user is provided a <strong>1-Month (30 days) Complimentary Free Trial</strong> with 100% of features completely unlocked. No payment card or financial details are required. You can manage 2 firms, create unlimited invoices, track ledgers, and download offline backups. You only decide whether to subscribe after fully evaluating the software.
              </div>
            </div>
          </div>

          <!-- FAQ 3 -->
          <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-xs">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                Can I manage multiple business firms under one account?
              </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#landingFaqAccordion">
              <div class="accordion-body text-secondary small lh-lg">
                Yes! RACE FINANCE supports <strong>Dual-Firm Management</strong>. You can operate up to two distinct commercial business entities under a single subscriber login, each with its own business name, logo, GSTIN, address, bank details, and independent ledger registers.
              </div>
            </div>
          </div>

          <!-- FAQ 4 -->
          <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-xs">
            <h2 class="accordion-header" id="headingFour">
              <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                Is my business and financial data private and secure?
              </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#landingFaqAccordion">
              <div class="accordion-body text-secondary small lh-lg">
                Absolutely. We operate under a strict <strong>Zero-Knowledge Security Architecture</strong>. Passwords undergo one-way cryptographic bcrypt hashing. Our administrators do not inspect your daily receipts or party ledgers, and you can export complete structured JSON backups directly to your local computer at any time.
              </div>
            </div>
          </div>

          <!-- FAQ 5 -->
          <div class="accordion-item border-0 rounded-4 overflow-hidden shadow-xs">
            <h2 class="accordion-header" id="headingFive">
              <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                How do I get my login account created?
              </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#landingFaqAccordion">
              <div class="accordion-body text-secondary small lh-lg">
                To prevent spam and ensure dedicated onboarding for every business, accounts are provisioned directly by our team. Simply click <a href="https://wa.me/<?= htmlspecialchars($supportPhoneRaw ?? '919672847747', ENT_QUOTES, 'UTF-8') ?>?text=Hello%20RACE%20FINANCE%20Support%2C%20I%20would%20like%20to%20start%20my%201-Month%20Free%20Trial." target="_blank" class="text-success fw-bold">WhatsApp Onboarding</a> or call us at <strong><?= htmlspecialchars($supportPhone ?? '+91 9672847747', ENT_QUOTES, 'UTF-8') ?></strong>. Your login credentials will be activated immediately!
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Final Call to Action -->
  <section class="py-5 text-center">
    <div class="card border-0 rounded-4 shadow-sm bg-light p-4 p-md-5">
      <h3 class="fw-bold text-dark mb-2">Ready to Transform Your Business Accounting?</h3>
      <p class="text-muted small mx-auto mb-4" style="max-width: 550px;">
        Join businesses using RACE FINANCE for faster billing, crystal-clear customer ledgers, and accurate stock management.
      </p>
      <div class="d-flex flex-wrap justify-content-center gap-3">
        <a href="/free-trial" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
          <i class="bi bi-gift-fill me-1"></i> Start 1-Month Free Trial
        </a>
        <a href="/about" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold">
          Learn More About Us
        </a>
        <a href="/contact" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
          Contact Support Desk
        </a>
      </div>
    </div>
  </section>
</div>
