<?php
/**
 * Party (Customer & Supplier) Model
 * Implements full FIFO ledger allocation and transaction tracking
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class Party extends Model {
    public static function getByFirmId(int $firmId, ?string $type = null): array {
        if ($type) {
            $stmt = self::db()->prepare("SELECT * FROM parties WHERE firm_id = ? AND (type = ? OR type = 'both') ORDER BY name ASC");
            $stmt->execute([$firmId, $type]);
        } else {
            $stmt = self::db()->prepare("SELECT * FROM parties WHERE firm_id = ? ORDER BY name ASC");
            $stmt->execute([$firmId]);
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM parties WHERE id = ? AND firm_id = ?");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function getByName(string $name, int $firmId): ?array {
        if (trim($name) === '') return null;
        $stmt = self::db()->prepare("SELECT * FROM parties WHERE LOWER(name) = LOWER(?) AND firm_id = ?");
        $stmt->execute([trim($name), $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): ?array {
        $firmId = (int)$data['firm_id'];
        $stmt = self::db()->prepare("
            INSERT INTO parties (
                firm_id, type, name, phone, email, gstin, pan, billing_address,
                shipping_address, city, state, state_code, pincode, opening_balance
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $firmId,
            $data['type'] ?? 'customer',
            trim($data['name']),
            !empty($data['phone']) ? trim($data['phone']) : null,
            !empty($data['email']) ? trim($data['email']) : null,
            !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null,
            !empty($data['pan']) ? strtoupper(trim($data['pan'])) : null,
            !empty($data['billing_address']) ? trim($data['billing_address']) : null,
            !empty($data['shipping_address']) ? trim($data['shipping_address']) : null,
            !empty($data['city']) ? trim($data['city']) : null,
            !empty($data['state']) ? trim($data['state']) : null,
            !empty($data['state_code']) ? trim($data['state_code']) : null,
            !empty($data['pincode']) ? trim($data['pincode']) : null,
            Security::parseCleanFloat($data['opening_balance'] ?? 0)
        ]);

        $id = (int)self::db()->lastInsertId();
        return self::getById($id, $firmId);
    }

    public static function update(int $id, int $firmId, array $data): ?array {
        $stmt = self::db()->prepare("
            UPDATE parties SET
                type = ?, name = ?, phone = ?, email = ?, gstin = ?, pan = ?,
                billing_address = ?, shipping_address = ?, city = ?, state = ?,
                state_code = ?, pincode = ?, opening_balance = ?
            WHERE id = ? AND firm_id = ?
        ");

        $stmt->execute([
            $data['type'] ?? 'customer',
            trim($data['name']),
            !empty($data['phone']) ? trim($data['phone']) : null,
            !empty($data['email']) ? trim($data['email']) : null,
            !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null,
            !empty($data['pan']) ? strtoupper(trim($data['pan'])) : null,
            !empty($data['billing_address']) ? trim($data['billing_address']) : null,
            !empty($data['shipping_address']) ? trim($data['shipping_address']) : null,
            !empty($data['city']) ? trim($data['city']) : null,
            !empty($data['state']) ? trim($data['state']) : null,
            !empty($data['state_code']) ? trim($data['state_code']) : null,
            !empty($data['pincode']) ? trim($data['pincode']) : null,
            Security::parseCleanFloat($data['opening_balance'] ?? 0),
            $id,
            $firmId
        ]);

        return self::getById($id, $firmId);
    }

    public static function delete(int $id, int $firmId): bool {
        $stmt = self::db()->prepare("DELETE FROM parties WHERE id = ? AND firm_id = ?");
        return $stmt->execute([$id, $firmId]);
    }

    /**
     * FIFO Settlement Synchronizer: Allocates payments to oldest bills first
     */
    public static function syncFIFOSettlement(?int $partyId, ?int $firmId): void {
        if (!$partyId || !$firmId) return;

        self::transaction(function($db) use ($partyId, $firmId) {
            $updateInvStmt = $db->prepare("
                UPDATE invoices SET paid_amount = ?, balance_due = ?, payment_status = ?
                WHERE id = ? AND firm_id = ?
            ");

            // 1. Sales Invoices vs Payment-In
            $salesStmt = $db->prepare("
                SELECT * FROM invoices 
                WHERE party_id = ? AND firm_id = ? AND type = 'sale' 
                ORDER BY invoice_date ASC, id ASC
            ");
            $salesStmt->execute([$partyId, $firmId]);
            $salesInvoices = $salesStmt->fetchAll();

            $payInStmt = $db->prepare("
                SELECT * FROM payments 
                WHERE party_id = ? AND firm_id = ? AND type = 'payment_in' 
                ORDER BY payment_date ASC, id ASC
            ");
            $payInStmt->execute([$partyId, $firmId]);
            $paymentsIn = $payInStmt->fetchAll();

            $totalCashReceived = 0.0;
            foreach ($paymentsIn as $p) {
                $totalCashReceived += (float)$p['amount'];
            }

            foreach ($salesInvoices as $inv) {
                $grandTotal = (float)$inv['grand_total'];
                $allocated = min($totalCashReceived, $grandTotal);
                $due = max(0.0, $grandTotal - $allocated);
                $status = ($due <= 0.001) ? 'paid' : (($allocated > 0) ? 'partial' : 'unpaid');

                $updateInvStmt->execute([$allocated, $due, $status, $inv['id'], $firmId]);
                $totalCashReceived = max(0.0, $totalCashReceived - $allocated);
            }

            // 2. Purchase Invoices vs Payment-Out
            $purchStmt = $db->prepare("
                SELECT * FROM invoices 
                WHERE party_id = ? AND firm_id = ? AND type = 'purchase' 
                ORDER BY invoice_date ASC, id ASC
            ");
            $purchStmt->execute([$partyId, $firmId]);
            $purchaseInvoices = $purchStmt->fetchAll();

            $payOutStmt = $db->prepare("
                SELECT * FROM payments 
                WHERE party_id = ? AND firm_id = ? AND type = 'payment_out' 
                ORDER BY payment_date ASC, id ASC
            ");
            $payOutStmt->execute([$partyId, $firmId]);
            $paymentsOut = $payOutStmt->fetchAll();

            $totalCashPaid = 0.0;
            foreach ($paymentsOut as $p) {
                $totalCashPaid += (float)$p['amount'];
            }

            foreach ($purchaseInvoices as $inv) {
                $grandTotal = (float)$inv['grand_total'];
                $allocated = min($totalCashPaid, $grandTotal);
                $due = max(0.0, $grandTotal - $allocated);
                $status = ($due <= 0.001) ? 'paid' : (($allocated > 0) ? 'partial' : 'unpaid');

                $updateInvStmt->execute([$allocated, $due, $status, $inv['id'], $firmId]);
                $totalCashPaid = max(0.0, $totalCashPaid - $allocated);
            }
        });
    }

    /**
     * Comprehensive Party Ledger with FIFO Step-by-Step Breakdown
     */
    public static function getLedger(int $partyId, int $firmId): ?array {
        self::syncFIFOSettlement($partyId, firmId: $firmId);

        $party = self::getById($partyId, $firmId);
        if (!$party) return null;

        $invStmt = self::db()->prepare("
            SELECT 
                id, invoice_number as voucher_no, invoice_date as date, type,
                grand_total, paid_amount, balance_due, payment_status, notes, 'invoice' as entry_type
            FROM invoices
            WHERE party_id = ? AND firm_id = ?
            ORDER BY invoice_date ASC, id ASC
        ");
        $invStmt->execute([$partyId, $firmId]);
        $invoices = $invStmt->fetchAll();

        $payStmt = self::db()->prepare("
            SELECT 
                id, payment_number as voucher_no, payment_date as date, type,
                amount, payment_mode, reference_no, notes, 'payment' as entry_type
            FROM payments
            WHERE party_id = ? AND firm_id = ?
            ORDER BY payment_date ASC, id ASC
        ");
        $payStmt->execute([$partyId, $firmId]);
        $payments = $payStmt->fetchAll();

        $invStateMap = [];
        foreach ($invoices as $inv) {
            $invStateMap[$inv['id']] = [
                'number' => $inv['voucher_no'],
                'total' => (float)$inv['grand_total'],
                'paid' => 0.0,
                'due' => (float)$inv['grand_total']
            ];
        }

        $runningBalance = (float)($party['opening_balance'] ?? 0);
        $totalBilled = 0.0;
        $totalPaid = 0.0;

        $allEntries = array_merge($invoices, $payments);
        usort($allEntries, function($a, $b) {
            $timeA = strtotime($a['date']);
            $timeB = strtotime($b['date']);
            if ($timeA === $timeB) {
                return ((int)$a['id']) - ((int)$b['id']);
            }
            return $timeA - $timeB;
        });

        $transactions = [];

        foreach ($allEntries as $entry) {
            $debit = 0.0;
            $credit = 0.0;
            $fifoDetails = '';

            if ($entry['entry_type'] === 'invoice') {
                if ($entry['type'] === 'sale') {
                    $debit = (float)$entry['grand_total'];
                    $totalBilled += $debit;
                    $runningBalance += $debit;
                    $fifoDetails = "Sale Bill " . $entry['voucher_no'] . " generated (₹" . number_format($debit, 2) . ")";
                } elseif ($entry['type'] === 'purchase') {
                    $credit = (float)$entry['grand_total'];
                    $totalBilled += $credit;
                    $runningBalance -= $credit;
                    $fifoDetails = "Purchase Bill " . $entry['voucher_no'] . " generated (₹" . number_format($credit, 2) . ")";
                }
            } elseif ($entry['entry_type'] === 'payment') {
                $payAmt = (float)$entry['amount'];
                $totalPaid += $payAmt;

                if ($entry['type'] === 'payment_in') {
                    $credit = $payAmt;
                    $runningBalance -= $payAmt;

                    $rem = $payAmt;
                    $logs = [];

                    foreach ($invoices as $inv) {
                        if ($inv['type'] !== 'sale') continue;
                        $st = &$invStateMap[$inv['id']];
                        if ($st && $st['due'] > 0.001) {
                            $apply = min($rem, $st['due']);
                            $st['due'] -= $apply;
                            $st['paid'] += $apply;
                            $rem -= $apply;

                            if ($st['due'] <= 0.001) {
                                $logs[] = "Bill {$st['number']} cleared (₹" . number_format($apply, 2) . ")";
                            } else {
                                $logs[] = "Applied ₹" . number_format($apply, 2) . " to oldest bill {$st['number']} (Balance left in bill: ₹" . number_format($st['due'], 2) . ")";
                            }

                            if ($rem <= 0) break;
                        }
                    }
                    unset($st);

                    if (!empty($logs)) {
                        $fifoDetails = implode('. ', $logs) . ". Total balance left: ₹" . number_format($runningBalance, 2);
                    } else {
                        $fifoDetails = "Advance payment of ₹" . number_format($payAmt, 2) . " received. Total balance left: ₹" . number_format($runningBalance, 2);
                    }
                } elseif ($entry['type'] === 'payment_out') {
                    $debit = $payAmt;
                    $runningBalance += $payAmt;

                    $rem = $payAmt;
                    $logs = [];

                    foreach ($invoices as $inv) {
                        if ($inv['type'] !== 'purchase') continue;
                        $st = &$invStateMap[$inv['id']];
                        if ($st && $st['due'] > 0.001) {
                            $apply = min($rem, $st['due']);
                            $st['due'] -= $apply;
                            $st['paid'] += $apply;
                            $rem -= $apply;

                            if ($st['due'] <= 0.001) {
                                $logs[] = "Purchase Bill {$st['number']} cleared (₹" . number_format($apply, 2) . ")";
                            } else {
                                $logs[] = "Applied ₹" . number_format($apply, 2) . " to oldest bill {$st['number']} (Balance left in bill: ₹" . number_format($st['due'], 2) . ")";
                            }

                            if ($rem <= 0) break;
                        }
                    }
                    unset($st);

                    if (!empty($logs)) {
                        $fifoDetails = implode('. ', $logs) . ". Total balance left: ₹" . number_format(abs($runningBalance), 2);
                    } else {
                        $fifoDetails = "Payment of ₹" . number_format($payAmt, 2) . " made. Total balance left: ₹" . number_format(abs($runningBalance), 2);
                    }
                }
            }

            $transactions[] = array_merge($entry, [
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningBalance,
                'fifo_details' => $fifoDetails
            ]);
        }

        $pendingBills = array_values(array_filter($invoices, fn($i) => (float)$i['balance_due'] > 0.001));

        return [
            'party' => $party,
            'opening_balance' => (float)($party['opening_balance'] ?? 0),
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'closing_balance' => $runningBalance,
            'pending_bills' => $pendingBills,
            'transactions' => $transactions
        ];
    }

    public static function getPartySummary(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT 
                p.*,
                COALESCE((SELECT SUM(grand_total) FROM invoices WHERE party_id = p.id AND type = 'sale'), 0) as total_sales,
                COALESCE((SELECT SUM(grand_total) FROM invoices WHERE party_id = p.id AND type = 'purchase'), 0) as total_purchases,
                COALESCE((SELECT SUM(balance_due) FROM invoices WHERE party_id = p.id AND type = 'sale'), 0) as sales_due,
                COALESCE((SELECT SUM(balance_due) FROM invoices WHERE party_id = p.id AND type = 'purchase'), 0) as purchase_due
            FROM parties p
            WHERE p.firm_id = ?
            ORDER BY p.name ASC
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }
}
