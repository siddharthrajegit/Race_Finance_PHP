<?php
/**
 * Report Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Party.php';

class ReportController {
    public function getDashboard(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $summary = Report::getDashboardSummary($firmId);

        view('dashboard', [
            'title' => 'Dashboard - RACE FINANCE',
            'summary' => $summary,
            'firm' => $firm,
            'activeMenu' => 'dashboard'
        ]);
    }

    public function getReportsIndex(): void {
        Auth::requireUserOnly();
        Auth::requireActiveFirm();

        view('reports/index', [
            'title' => 'Business & Tax Reports',
            'activeMenu' => 'reports'
        ]);
    }

    public function getPartyReport(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $parties = Party::getByFirmId($firmId);
        $partyData = [];
        $totalReceivables = 0.0;
        $totalPayables = 0.0;

        foreach ($parties as $p) {
            $ledger = Party::getLedger((int)$p['id'], $firmId);
            $balance = $ledger ? $ledger['closing_balance'] : (float)($p['opening_balance'] ?? 0);

            if ($balance > 0) $totalReceivables += $balance;
            if ($balance < 0) $totalPayables += abs($balance);

            $p['closing_balance'] = $balance;
            $partyData[] = $p;
        }

        view('reports/party_report', [
            'title' => 'Party & Customer Wise Report',
            'parties' => $partyData,
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'activeMenu' => 'reports'
        ]);
    }

    public function getTaxReport(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;

        $taxData = Report::getTaxReport((int)$firm['id'], $fromDate, $toDate);

        view('reports/tax_report', [
            'title' => 'GST & Tax Summary Report',
            'summary' => $taxData['summary'],
            'invoices' => $taxData['invoices'],
            'fromDate' => $fromDate ?: '',
            'toDate' => $toDate ?: '',
            'activeMenu' => 'reports'
        ]);
    }

    public function getItemReport(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $items = Report::getItemWiseReport((int)$firm['id']);

        view('reports/item_report', [
            'title' => 'Item Wise Sales Report',
            'items' => $items,
            'activeMenu' => 'reports'
        ]);
    }
}
