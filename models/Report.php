<?php
/**
 * Business & Tax Reports Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Item.php';

class Report extends Model {
    public static function getDashboardSummary(int $firmId): array {
        $salesStmt = self::db()->prepare("
            SELECT 
                COUNT(*) as total_sales_count,
                COALESCE(SUM(grand_total), 0) as total_sales_amount,
                COALESCE(SUM(paid_amount), 0) as total_sales_received,
                COALESCE(SUM(balance_due), 0) as total_receivables
            FROM invoices 
            WHERE firm_id = ? AND type = 'sale'
        ");
        $salesStmt->execute([$firmId]);
        $salesSummary = $salesStmt->fetch();

        $purchStmt = self::db()->prepare("
            SELECT 
                COUNT(*) as total_purchase_count,
                COALESCE(SUM(grand_total), 0) as total_purchase_amount,
                COALESCE(SUM(paid_amount), 0) as total_purchase_paid,
                COALESCE(SUM(balance_due), 0) as total_payables
            FROM invoices 
            WHERE firm_id = ? AND type = 'purchase'
        ");
        $purchStmt->execute([$firmId]);
        $purchaseSummary = $purchStmt->fetch();

        $todayDate = date('Y-m-d');
        $todayStmt = self::db()->prepare("
            SELECT COALESCE(SUM(grand_total), 0) as amount 
            FROM invoices 
            WHERE firm_id = ? AND type = 'sale' AND invoice_date = ?
        ");
        $todayStmt->execute([$firmId, $todayDate]);
        $todaySales = $todayStmt->fetch();

        $itemsCountStmt = self::db()->prepare("SELECT COUNT(*) as count FROM items WHERE firm_id = ?");
        $itemsCountStmt->execute([$firmId]);
        $totalItems = $itemsCountStmt->fetch();

        $lowStockItems = Item::getLowStock($firmId);

        $recentStmt = self::db()->prepare("
            SELECT i.*, p.name as party_name 
            FROM invoices i 
            LEFT JOIN parties p ON i.party_id = p.id 
            WHERE i.firm_id = ? 
            ORDER BY i.created_at DESC LIMIT 6
        ");
        $recentStmt->execute([$firmId]);
        $recentInvoices = $recentStmt->fetchAll();

        return [
            'sales' => $salesSummary,
            'purchases' => $purchaseSummary,
            'todaySales' => $todaySales ? (float)$todaySales['amount'] : 0,
            'totalItems' => $totalItems ? (int)$totalItems['count'] : 0,
            'lowStockCount' => count($lowStockItems),
            'lowStockItems' => $lowStockItems,
            'recentInvoices' => $recentInvoices
        ];
    }

    public static function getTaxReport(int $firmId, ?string $fromDate = null, ?string $toDate = null): array {
        $query = "
            SELECT 
                is_gst_bill,
                COUNT(*) as invoice_count,
                COALESCE(SUM(taxable_amount), 0) as total_taxable,
                COALESCE(SUM(cgst_amount), 0) as total_cgst,
                COALESCE(SUM(sgst_amount), 0) as total_sgst,
                COALESCE(SUM(igst_amount), 0) as total_igst,
                COALESCE(SUM(tax_amount), 0) as total_tax,
                COALESCE(SUM(grand_total), 0) as total_gross
            FROM invoices
            WHERE firm_id = ? AND type = 'sale'
        ";
        $params = [$firmId];

        if (!empty($fromDate) && !empty($toDate)) {
            $query .= " AND invoice_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        $query .= " GROUP BY is_gst_bill";

        $summaryStmt = self::db()->prepare($query);
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetchAll();

        $detailQuery = "SELECT * FROM invoices WHERE firm_id = ? AND type = 'sale'";
        $detailParams = [$firmId];
        if (!empty($fromDate) && !empty($toDate)) {
            $detailQuery .= " AND invoice_date BETWEEN ? AND ?";
            $detailParams[] = $fromDate;
            $detailParams[] = $toDate;
        }
        $detailQuery .= " ORDER BY invoice_date ASC";

        $detailStmt = self::db()->prepare($detailQuery);
        $detailStmt->execute($detailParams);
        $invoices = $detailStmt->fetchAll();

        return [
            'summary' => $summary,
            'invoices' => $invoices
        ];
    }

    public static function getItemWiseReport(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT 
                it.id, it.name, it.item_code, it.unit, it.current_stock,
                COALESCE(SUM(ii.quantity), 0) as total_sold_qty,
                COALESCE(SUM(ii.total_amount), 0) as total_sales_value
            FROM items it
            LEFT JOIN invoice_items ii ON it.id = ii.item_id
            LEFT JOIN invoices inv ON ii.invoice_id = inv.id AND inv.type = 'sale'
            WHERE it.firm_id = ?
            GROUP BY it.id, it.name, it.item_code, it.unit, it.current_stock
            ORDER BY total_sold_qty DESC
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }
}
