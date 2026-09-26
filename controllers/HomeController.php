<?php
/**
 * Home Controller
 * Public landing page & homepage marketing portal
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Auth.php';

class HomeController {
    /**
     * Display public landing page for guests or redirect authenticated users
     */
    public function index(): void {
        if (Auth::isAuthenticated()) {
            header('Location: ' . (Auth::isAdmin() ? '/admin' : '/dashboard'));
            exit;
        }

        view('home/index', [
            'title' => 'RACE FINANCE — Smart Small Business Billing, GST Invoicing & Inventory System',
            'metaDescription' => 'All-in-one billing, inventory management, and FIFO party ledger utility for Indian small businesses. Manage 2 firms with a complimentary 1-month free trial.',
            'activeMenu' => 'home',
            'activeTab' => 'home'
        ]);
    }
}
