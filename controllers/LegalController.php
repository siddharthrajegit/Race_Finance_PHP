<?php
/**
 * Legal & Public Pages Controller
 * Public compliance, policy, pricing, free trial, and contact pages
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/View.php';

class LegalController {
    /**
     * Map of valid tabs, titles, and descriptions
     */
    private static function getMetadata(): array {
        return [
            'free-trial' => [
                'title' => '1-Month Free Trial — RACE FINANCE Billing & Inventory System',
                'description' => 'Get 30 days of full, unrestricted access to RACE FINANCE. Manage 2 firms, GST invoices, FIFO party ledgers, and inventory with zero setup fee.',
                'menu' => 'free-trial'
            ],
            'about' => [
                'title' => 'About RACE FINANCE — Smart Small Business Management & Digital Bookkeeping',
                'description' => 'Discover RACE FINANCE: An integrated small business management and digital bookkeeping utility. Manage up to 2 firms, FIFO ledgers, inventory, and GST registers.',
                'menu' => 'about'
            ],
            'pricing' => [
                'title' => 'Pricing Plans & 1-Month Free Trial — RACE FINANCE',
                'description' => 'Transparent and affordable pricing plans for Indian small businesses. Includes a 1-month complimentary full-featured free trial with zero setup fees.',
                'menu' => 'pricing'
            ],
            'contact' => [
                'title' => 'Contact Support & Onboarding Desk — RACE FINANCE',
                'description' => 'Get in touch with RACE FINANCE official support, customer onboarding, and technical assistance desk via WhatsApp (' . SUPPORT_PHONE . ') or direct call.',
                'menu' => 'contact'
            ],
            'terms' => [
                'title' => 'Terms & Conditions (T&C) — RACE FINANCE',
                'description' => 'Read the Terms and Conditions of service governing account provisioning, zero-knowledge security custody, and subscription lifecycle on RACE FINANCE.',
                'menu' => 'legal'
            ],
            'privacy' => [
                'title' => 'Privacy Policy & Zero-Knowledge Architecture — RACE FINANCE',
                'description' => 'Our strict zero-knowledge privacy policy: Zero administrator account inspection, salted one-way password encryption, and 100% offline data portability.',
                'menu' => 'legal'
            ],
            'refund' => [
                'title' => 'Refund & Cancellation Policy — RACE FINANCE',
                'description' => 'RACE FINANCE Refund & Cancellation Policy: Non-refundable subscriptions backed by a full 1-month complimentary trial period prior to renewal.',
                'menu' => 'legal'
            ],
            'disclaimer' => [
                'title' => 'Legal & Tax Disclaimer — RACE FINANCE',
                'description' => 'Legal and tax compliance disclaimer: RACE FINANCE is an internal record management and billing utility for small business bookkeeping.',
                'menu' => 'legal'
            ],
            'security' => [
                'title' => 'User Data Security & Password Notice — RACE FINANCE',
                'description' => 'User data security advisory: One-way cryptographic bcrypt hashing, complete customer data ownership, and 24/7 security reporting desk.',
                'menu' => 'legal'
            ]
        ];
    }

    /**
     * Render a standalone legal or public page directly (200 OK without redirect)
     */
    public function renderPage(string $tab): void {
        $meta = self::getMetadata();
        if (!isset($meta[$tab])) {
            $tab = 'terms';
        }

        $info = $meta[$tab];

        view('legal/index', [
            'title' => $info['title'],
            'metaDescription' => $info['description'],
            'activeTab' => $tab,
            'activeMenu' => $info['menu']
        ]);
    }

    /**
     * Handle generic /legal or redirect legacy /legal?tab=... queries permanently
     */
    public function getLegalPage(array $params = []): void {
        $tab = $_GET['tab'] ?? ($params['section'] ?? null);

        // 301 Permanent Redirect clean canonical URLs for search engines
        if ($tab && in_array($tab, ['about', 'pricing', 'contact', 'free-trial', 'terms', 'privacy', 'security'], true)) {
            $target = ($tab === 'terms') ? '/terms' : (($tab === 'privacy') ? '/privacy' : (($tab === 'security') ? '/security' : '/' . $tab));
            header('Location: ' . $target, true, 301);
            exit;
        }

        if ($tab === 'refund') {
            header('Location: /refund-policy', true, 301);
            exit;
        }

        if ($tab === 'disclaimer') {
            header('Location: /disclaimer', true, 301);
            exit;
        }

        $this->renderPage('terms');
    }

    public function getFreeTrial(): void {
        $this->renderPage('free-trial');
    }

    public function getAbout(): void {
        $this->renderPage('about');
    }

    public function getPricing(): void {
        $this->renderPage('pricing');
    }

    public function getContact(): void {
        $this->renderPage('contact');
    }

    public function getTerms(): void {
        $this->renderPage('terms');
    }

    public function getPrivacy(): void {
        $this->renderPage('privacy');
    }

    public function getRefund(): void {
        $this->renderPage('refund');
    }

    public function getDisclaimer(): void {
        $this->renderPage('disclaimer');
    }

    public function getSecurity(): void {
        $this->renderPage('security');
    }
}
