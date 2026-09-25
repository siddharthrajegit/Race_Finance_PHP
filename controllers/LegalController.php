<?php
/**
 * Legal Controller
 * Public compliance, policy, pricing, and contact pages
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/View.php';

class LegalController {
    public function getLegalPage(array $params = []): void {
        $tab = $_GET['tab'] ?? ($params['section'] ?? 'terms');
        $validTabs = ['about', 'pricing', 'contact', 'terms', 'privacy', 'refund', 'disclaimer', 'security'];
        $activeTab = in_array($tab, $validTabs, true) ? $tab : 'terms';

        $titles = [
            'about' => 'About RACE FINANCE - Smart Small Business Management & Digital Bookkeeping',
            'pricing' => 'Pricing Plans & 1-Month Free Trial - RACE FINANCE',
            'contact' => 'Contact Support & Onboarding Desk - RACE FINANCE',
            'terms' => 'Terms & Conditions (T&C) - RACE FINANCE',
            'privacy' => 'Privacy Policy & Zero-Knowledge Architecture - RACE FINANCE',
            'refund' => 'Refund & Cancellation Policy - RACE FINANCE',
            'disclaimer' => 'Legal Disclaimer & Tax Responsibility - RACE FINANCE',
            'security' => 'User Data Security & Password Notice - RACE FINANCE'
        ];

        $descriptions = [
            'about' => 'Discover RACE FINANCE: An integrated small business management and digital bookkeeping utility. Manage up to 2 firms, FIFO ledgers, inventory, and GST registers.',
            'pricing' => 'Transparent and affordable pricing plans for Indian small businesses. Includes a 1-month complimentary full-featured free trial with zero setup fees.',
            'contact' => 'Get in touch with RACE FINANCE official support, customer onboarding, and technical assistance desk via WhatsApp (' . SUPPORT_PHONE . ') or direct call.',
            'terms' => 'Read the Terms and Conditions of service governing account provisioning, zero-knowledge security custody, and subscription lifecycle on RACE FINANCE.',
            'privacy' => 'Our strict zero-knowledge privacy policy: Zero administrator account inspection, salted one-way password encryption, and 100% offline data portability.',
            'refund' => 'RACE FINANCE Refund & Cancellation Policy: Non-refundable subscriptions backed by a full 1-month complimentary trial period prior to renewal.',
            'disclaimer' => 'Legal and tax compliance disclaimer: RACE FINANCE is an internal record management and billing utility for small business bookkeeping.',
            'security' => 'User data security advisory: One-way cryptographic bcrypt hashing, complete customer data ownership, and 24/7 security reporting desk.'
        ];

        view('legal/index', [
            'title' => $titles[$activeTab] ?? 'RACE FINANCE - Small Business Billing & Inventory',
            'metaDescription' => $descriptions[$activeTab] ?? 'Smart Small Business Billing & Inventory Management Utility',
            'activeTab' => $activeTab,
            'activeMenu' => in_array($activeTab, ['about', 'pricing', 'contact'], true) ? $activeTab : 'legal'
        ]);
    }

    public function getAbout(): void {
        header('Location: /legal?tab=about');
        exit;
    }

    public function getPricing(): void {
        header('Location: /legal?tab=pricing');
        exit;
    }

    public function getContact(): void {
        header('Location: /legal?tab=contact');
        exit;
    }

    public function getTerms(): void {
        header('Location: /legal?tab=terms');
        exit;
    }

    public function getPrivacy(): void {
        header('Location: /legal?tab=privacy');
        exit;
    }

    public function getRefund(): void {
        header('Location: /legal?tab=refund');
        exit;
    }

    public function getDisclaimer(): void {
        header('Location: /legal?tab=disclaimer');
        exit;
    }

    public function getSecurity(): void {
        header('Location: /legal?tab=security');
        exit;
    }
}
