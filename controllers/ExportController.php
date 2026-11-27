<?php
/**
 * Compliance & Accounting Exports Controller (Zero-Cost Local CSV & XML Generation)
 * Generates software-compiled GSTR-1 / GSTR-2B / GSTR-3B summaries (CSV) and Tally-compatible XML.
 * NOTE: These are local software summaries for CA review/reconciliation — NOT official GSTN filings or IRN/E-Way Bills.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Party.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ExportController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_COMPLIANCE_EXPORTS') || !ENABLE_COMPLIANCE_EXPORTS) {
            Flash::set('info_msg', 'Compliance exports module is currently disabled.');
            header('Location: /reports');
            exit;
        }
    }

    /**
     * Prevent CSV formula injection (=, +, -, @, \t, \r) while preserving numeric signs cleanly.
     */
    public static function sanitizeCsvCell($val): string {
        $s = (string)$val;
        if ($s !== '' && !is_numeric($s) && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $s;
        }
        return $s;
    }

    public static function computeGstrSummaries(int $firmId, ?string $fromDate = null, ?string $toDate = null): array {
        $db = DB::getConnection();
        $where = "firm_id = ?";
        $params = [$firmId];
        if ($fromDate) {
            $where .= " AND invoice_date >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $where .= " AND invoice_date <= ?";
            $params[] = $toDate;
        }

        $stmt = $db->prepare("SELECT * FROM invoices WHERE {$where} ORDER BY invoice_date ASC, id ASC");
        $stmt->execute($params);
        $all = $stmt->fetchAll();

        $sales = [];
        $purchases = [];
        $outSummary = ['taxable' => 0.0, 'cgst' => 0.0, 'sgst' => 0.0, 'igst' => 0.0, 'total' => 0.0];
        $inSummary = ['taxable' => 0.0, 'cgst' => 0.0, 'sgst' => 0.0, 'igst' => 0.0, 'total' => 0.0];

        foreach ($all as $inv) {
            $taxable = (float)($inv['taxable_amount'] ?? 0);
            $cgst = (float)($inv['cgst_amount'] ?? 0);
            $sgst = (float)($inv['sgst_amount'] ?? 0);
            $igst = (float)($inv['igst_amount'] ?? 0);
            $grand = (float)($inv['grand_total'] ?? 0);

            if (($inv['type'] ?? 'sale') === 'sale') {
                $sales[] = $inv;
                if (!empty($inv['is_gst_bill'])) {
                    $outSummary['taxable'] += $taxable;
                    $outSummary['cgst'] += $cgst;
                    $outSummary['sgst'] += $sgst;
                    $outSummary['igst'] += $igst;
                    $outSummary['total'] += $grand;
                }
            } else {
                $purchases[] = $inv;
                if (!empty($inv['is_gst_bill'])) {
                    $inSummary['taxable'] += $taxable;
                    $inSummary['cgst'] += $cgst;
                    $inSummary['sgst'] += $sgst;
                    $inSummary['igst'] += $igst;
                    $inSummary['total'] += $grand;
                }
            }
        }

        $netPayable = [
            'cgst' => round($outSummary['cgst'] - $inSummary['cgst'], 2),
            'sgst' => round($outSummary['sgst'] - $inSummary['sgst'], 2),
            'igst' => round($outSummary['igst'] - $inSummary['igst'], 2),
            'total_tax' => round(($outSummary['cgst'] + $outSummary['sgst'] + $outSummary['igst']) - ($inSummary['cgst'] + $inSummary['sgst'] + $inSummary['igst']), 2)
        ];

        return [
            'sales' => $sales,
            'purchases' => $purchases,
            'outward' => $outSummary,
            'inward' => $inSummary,
            'net_payable' => $netPayable
        ];
    }

    public function gstrIndex(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_reports');
        $firm = Auth::requireActiveFirm();

        $fromDate = !empty($_GET['from_date']) ? trim($_GET['from_date']) : date('Y-m-01');
        $toDate = !empty($_GET['to_date']) ? trim($_GET['to_date']) : date('Y-m-d');
        $data = self::computeGstrSummaries((int)$firm['id'], $fromDate, $toDate);

        view('reports/gstr_exports', [
            'title' => 'GST Summary Exports (GSTR-1 / 2B / 3B)',
            'data' => $data,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'activeMenu' => 'reports'
        ]);
    }

    public function downloadGstrCsv(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_reports');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $reportType = in_array($_GET['report'] ?? '', ['gstr1', 'gstr2b', 'gstr3b'], true) ? $_GET['report'] : 'gstr1';
        $fromDate = !empty($_GET['from_date']) ? trim($_GET['from_date']) : null;
        $toDate = !empty($_GET['to_date']) ? trim($_GET['to_date']) : null;
        $data = self::computeGstrSummaries($firmId, $fromDate, $toDate);

        AuditLog::log($firmId, 'EXPORT', 'gst_csv', null, "Exported {$reportType} CSV");

        $filename = strtoupper($reportType) . '_Summary_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $firm['name']) . '_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        if ($reportType === 'gstr1' || $reportType === 'gstr2b') {
            $rows = ($reportType === 'gstr1') ? $data['sales'] : $data['purchases'];
            $partyLabel = ($reportType === 'gstr1') ? 'Customer Name' : 'Supplier Name';
            fputcsv($out, [
                'Software-Generated Working Summary Only (Not Official GST Portal Return)',
                'Firm: ' . $firm['name'],
                'GSTIN: ' . ($firm['gstin'] ?? 'N/A')
            ]);
            fputcsv($out, ['Invoice Number', 'Invoice Date', $partyLabel, 'Party GSTIN', 'State/POS', 'Supply Type', 'GST Bill', 'Taxable Value', 'CGST', 'SGST', 'IGST', 'Total Tax', 'Invoice Total']);

            foreach ($rows as $r) {
                $supplyType = !empty($r['party_gstin']) ? 'B2B' : 'B2C';
                fputcsv($out, [
                    self::sanitizeCsvCell($r['invoice_number']),
                    $r['invoice_date'],
                    self::sanitizeCsvCell($r['party_name']),
                    self::sanitizeCsvCell($r['party_gstin'] ?? ''),
                    self::sanitizeCsvCell($r['party_state'] ?? ''),
                    $supplyType,
                    !empty($r['is_gst_bill']) ? 'Yes' : 'No',
                    number_format((float)$r['taxable_amount'], 2, '.', ''),
                    number_format((float)$r['cgst_amount'], 2, '.', ''),
                    number_format((float)$r['sgst_amount'], 2, '.', ''),
                    number_format((float)$r['igst_amount'], 2, '.', ''),
                    number_format((float)$r['tax_amount'], 2, '.', ''),
                    number_format((float)$r['grand_total'], 2, '.', '')
                ]);
            }
        } else {
            fputcsv($out, ['Software-Generated GSTR-3B Working Summary (Verify with CA before portal filing)', 'Firm: ' . $firm['name'], 'GSTIN: ' . ($firm['gstin'] ?? 'N/A')]);
            fputcsv($out, ['Section / Particulars', 'Taxable Value', 'IGST', 'CGST', 'SGST', 'Total Value / Tax']);
            fputcsv($out, [
                '3.1(a) Outward Taxable Supplies (Sales)',
                number_format($data['outward']['taxable'], 2, '.', ''),
                number_format($data['outward']['igst'], 2, '.', ''),
                number_format($data['outward']['cgst'], 2, '.', ''),
                number_format($data['outward']['sgst'], 2, '.', ''),
                number_format($data['outward']['total'], 2, '.', '')
            ]);
            fputcsv($out, [
                '4(A) Eligible Input Tax Credit (Purchases)',
                number_format($data['inward']['taxable'], 2, '.', ''),
                number_format($data['inward']['igst'], 2, '.', ''),
                number_format($data['inward']['cgst'], 2, '.', ''),
                number_format($data['inward']['sgst'], 2, '.', ''),
                number_format($data['inward']['total'], 2, '.', '')
            ]);
            fputcsv($out, [
                '6.1 Estimated Net GST Liability (Outward Tax - Input ITC)',
                '0.00',
                number_format($data['net_payable']['igst'], 2, '.', ''),
                number_format($data['net_payable']['cgst'], 2, '.', ''),
                number_format($data['net_payable']['sgst'], 2, '.', ''),
                number_format($data['net_payable']['total_tax'], 2, '.', '')
            ]);
        }

        fclose($out);
        exit;
    }

    public static function buildTallyXml(array $firm, ?string $fromDate = null, ?string $toDate = null): string {
        $firmId = (int)$firm['id'];
        $db = DB::getConnection();

        $where = "firm_id = ?";
        $params = [$firmId];
        if ($fromDate) {
            $where .= " AND invoice_date >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $where .= " AND invoice_date <= ?";
            $params[] = $toDate;
        }
        $invStmt = $db->prepare("SELECT * FROM invoices WHERE {$where} ORDER BY invoice_date ASC, id ASC");
        $invStmt->execute($params);
        $invoices = $invStmt->fetchAll();

        $parties = Party::getByFirmId($firmId);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $envelope = $dom->createElement('ENVELOPE');
        $dom->appendChild($envelope);

        $header = $dom->createElement('HEADER');
        $header->appendChild($dom->createElement('TALLYREQUEST', 'Import Data'));
        $envelope->appendChild($header);

        $body = $dom->createElement('BODY');
        $envelope->appendChild($body);
        $importData = $dom->createElement('IMPORTDATA');
        $body->appendChild($importData);

        $reqDesc = $dom->createElement('REQUESTDESC');
        $reqDesc->appendChild($dom->createElement('REPORTNAME', 'Vouchers'));
        $staticVars = $dom->createElement('STATICVARIABLES');
        $staticVars->appendChild($dom->createElement('SVCURRENTCOMPANY', (string)($firm['name'] ?? 'Company')));
        $reqDesc->appendChild($staticVars);
        $importData->appendChild($reqDesc);

        $reqData = $dom->createElement('REQUESTDATA');
        $importData->appendChild($reqData);

        // Party Ledger Masters
        foreach ($parties as $p) {
            $msg = $dom->createElement('TALLYMESSAGE');
            $ledger = $dom->createElement('LEDGER');
            $ledger->setAttribute('NAME', (string)$p['name']);
            $ledger->setAttribute('ACTION', 'Create');
            $ledger->appendChild($dom->createElement('NAME', (string)$p['name']));
            $ledger->appendChild($dom->createElement('PARENT', ($p['type'] ?? 'customer') === 'supplier' ? 'Sundry Creditors' : 'Sundry Debtors'));
            if (!empty($p['gstin'])) {
                $ledger->appendChild($dom->createElement('PARTYGSTIN', (string)$p['gstin']));
            }
            $msg->appendChild($ledger);
            $reqData->appendChild($msg);
        }

        // Invoice Vouchers
        foreach ($invoices as $inv) {
            $vchType = (($inv['type'] ?? 'sale') === 'purchase') ? 'Purchase' : 'Sales';
            $tallyDate = str_replace('-', '', substr((string)$inv['invoice_date'], 0, 10));
            $grand = number_format((float)$inv['grand_total'], 2, '.', '');
            $isSale = ($vchType === 'Sales');

            $msg = $dom->createElement('TALLYMESSAGE');
            $vch = $dom->createElement('VOUCHER');
            $vch->setAttribute('VCHTYPE', $vchType);
            $vch->setAttribute('ACTION', 'Create');
            $vch->appendChild($dom->createElement('DATE', $tallyDate));
            $vch->appendChild($dom->createElement('VOUCHERTYPENAME', $vchType));
            $vch->appendChild($dom->createElement('VOUCHERNUMBER', (string)$inv['invoice_number']));
            $vch->appendChild($dom->createElement('PARTYLEDGERNAME', (string)$inv['party_name']));
            $vch->appendChild($dom->createElement('NARRATION', 'RACE FINANCE ' . $vchType . ' #' . $inv['invoice_number']));

            // Party Entry
            $partyEntry = $dom->createElement('ALLLEDGERENTRIES.LIST');
            $partyEntry->appendChild($dom->createElement('LEDGERNAME', (string)$inv['party_name']));
            $partyEntry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', $isSale ? 'Yes' : 'No'));
            $partyEntry->appendChild($dom->createElement('AMOUNT', $isSale ? ('-' . $grand) : $grand));
            $vch->appendChild($partyEntry);

            // Taxable Revenue/Purchase Entry
            $taxable = number_format((float)$inv['taxable_amount'], 2, '.', '');
            $mainEntry = $dom->createElement('ALLLEDGERENTRIES.LIST');
            $mainEntry->appendChild($dom->createElement('LEDGERNAME', $isSale ? 'Sales Account' : 'Purchase Account'));
            $mainEntry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', $isSale ? 'No' : 'Yes'));
            $mainEntry->appendChild($dom->createElement('AMOUNT', $isSale ? $taxable : ('-' . $taxable)));
            $vch->appendChild($mainEntry);

            // Tax Entries if GST
            foreach (['CGST' => (float)$inv['cgst_amount'], 'SGST' => (float)$inv['sgst_amount'], 'IGST' => (float)$inv['igst_amount']] as $taxName => $taxVal) {
                if ($taxVal <= 0.005) continue;
                $fmtTax = number_format($taxVal, 2, '.', '');
                $taxEntry = $dom->createElement('ALLLEDGERENTRIES.LIST');
                $taxEntry->appendChild($dom->createElement('LEDGERNAME', $taxName));
                $taxEntry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', $isSale ? 'No' : 'Yes'));
                $taxEntry->appendChild($dom->createElement('AMOUNT', $isSale ? $fmtTax : ('-' . $fmtTax)));
                $vch->appendChild($taxEntry);
            }

            $msg->appendChild($vch);
            $reqData->appendChild($msg);
        }

        return (string)$dom->saveXML();
    }

    public function tallyIndex(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_reports');
        $firm = Auth::requireActiveFirm();

        $fromDate = !empty($_GET['from_date']) ? trim($_GET['from_date']) : date('Y-m-01');
        $toDate = !empty($_GET['to_date']) ? trim($_GET['to_date']) : date('Y-m-d');
        $data = self::computeGstrSummaries((int)$firm['id'], $fromDate, $toDate);

        view('reports/tally_export', [
            'title' => 'Tally-Compatible XML Export',
            'salesCount' => count($data['sales']),
            'purchasesCount' => count($data['purchases']),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'activeMenu' => 'reports'
        ]);
    }

    public function downloadTallyXml(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('view_reports');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $fromDate = !empty($_GET['from_date']) ? trim($_GET['from_date']) : null;
        $toDate = !empty($_GET['to_date']) ? trim($_GET['to_date']) : null;
        $xml = self::buildTallyXml($firm, $fromDate, $toDate);

        AuditLog::log($firmId, 'EXPORT', 'tally_xml', null, 'Exported Tally-compatible XML');

        $filename = 'Tally_Vouchers_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $firm['name']) . '_' . date('Ymd') . '.xml';
        header('Content-Type: application/xml; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        echo $xml;
        exit;
    }
}
