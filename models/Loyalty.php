<?php
/**
 * Customer Loyalty Points Engine
 * Handles firm-level rewards config, earn on sales invoice, redemption discount, and reversal on edit/delete.
 */

require_once __DIR__ . '/Model.php';

class Loyalty extends Model {
    public static function getProgram(int $firmId): array {
        $stmt = self::db()->prepare("SELECT * FROM loyalty_programs WHERE firm_id = ? LIMIT 1");
        $stmt->execute([$firmId]);
        $prog = $stmt->fetch();
        if ($prog) {
            return $prog;
        }

        self::db()->prepare("
            INSERT INTO loyalty_programs (firm_id, is_enabled, program_name, points_per_currency, currency_unit, point_value, min_redeem_points, max_redeem_pct)
            VALUES (?, 0, 'Rewards Club', 1.00, 100.00, 1.00, 10.00, 50.00)
        ")->execute([$firmId]);

        $stmt->execute([$firmId]);
        return $stmt->fetch();
    }

    public static function updateProgram(int $firmId, array $data): array {
        self::getProgram($firmId);

        $isEnabled = !empty($data['is_enabled']) ? 1 : 0;
        $name = trim((string)($data['program_name'] ?? 'Rewards Club')) ?: 'Rewards Club';
        $ptsPer = max(0.0, (float)($data['points_per_currency'] ?? 1.0));
        $unit = max(1.0, (float)($data['currency_unit'] ?? 100.0));
        $val = max(0.01, (float)($data['point_value'] ?? 1.0));
        $minRedeem = max(0.0, (float)($data['min_redeem_points'] ?? 10.0));
        $maxPct = min(100.0, max(1.0, (float)($data['max_redeem_pct'] ?? 50.0)));

        self::db()->prepare("
            UPDATE loyalty_programs
            SET is_enabled = ?, program_name = ?, points_per_currency = ?, currency_unit = ?,
                point_value = ?, min_redeem_points = ?, max_redeem_pct = ?, updated_at = CURRENT_TIMESTAMP
            WHERE firm_id = ?
        ")->execute([$isEnabled, $name, $ptsPer, $unit, $val, $minRedeem, $maxPct, $firmId]);

        return self::getProgram($firmId);
    }

    public static function getPartyBalance(int $partyId, int $firmId): float {
        $stmt = self::db()->prepare("SELECT loyalty_points FROM parties WHERE id = ? AND firm_id = ? LIMIT 1");
        $stmt->execute([$partyId, $firmId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? round((float)$val, 2) : 0.0;
    }

    public static function getTransactions(int $firmId, ?int $partyId = null, int $limit = 100): array {
        $sql = "
            SELECT lt.*, p.name AS party_name, p.phone AS party_phone
            FROM loyalty_transactions lt
            JOIN parties p ON p.id = lt.party_id
            WHERE lt.firm_id = ?
        ";
        $params = [$firmId];
        if ($partyId) {
            $sql .= " AND lt.party_id = ?";
            $params[] = $partyId;
        }
        $limit = max(1, min(500, $limit));
        $sql .= " ORDER BY lt.id DESC LIMIT {$limit}";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function adjustPoints(
        int $firmId,
        int $partyId,
        float $delta,
        string $txnType = 'adjust',
        ?int $invoiceId = null,
        ?string $notes = null,
        ?int $createdBy = null
    ): float {
        if (abs($delta) < 0.005) {
            return self::getPartyBalance($partyId, $firmId);
        }

        $db = self::db();
        $curr = self::getPartyBalance($partyId, $firmId);
        $newBalance = round(max(0.0, $curr + $delta), 2);
        $actualDelta = round($newBalance - $curr, 2);

        $db->prepare("UPDATE parties SET loyalty_points = ? WHERE id = ? AND firm_id = ?")
           ->execute([$newBalance, $partyId, $firmId]);

        $db->prepare("
            INSERT INTO loyalty_transactions (firm_id, party_id, invoice_id, txn_type, points_delta, balance_after, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([$firmId, $partyId, $invoiceId, $txnType, $actualDelta, $newBalance, $notes, $createdBy]);

        return $newBalance;
    }

    /**
     * Automatically award loyalty points on sale invoice (and redeem if requested in $extraData).
     */
    public static function processInvoicePoints(array $invoice, array $extraData = []): void {
        if (($invoice['type'] ?? 'sale') !== 'sale' || empty($invoice['party_id'])) {
            return;
        }
        $firmId = (int)$invoice['firm_id'];
        $partyId = (int)$invoice['party_id'];
        $invId = (int)$invoice['id'];

        $prog = self::getProgram($firmId);
        if (empty($prog['is_enabled'])) {
            return;
        }

        // Prevent duplicate awards on same invoice
        $chk = self::db()->prepare("SELECT COUNT(*) FROM loyalty_transactions WHERE firm_id = ? AND invoice_id = ? AND txn_type = 'earn'");
        $chk->execute([$firmId, $invId]);
        if ((int)$chk->fetchColumn() > 0) {
            return;
        }

        $redeemPts = round(max(0.0, (float)($extraData['redeem_points'] ?? 0)), 2);
        if ($redeemPts > 0) {
            $avail = self::getPartyBalance($partyId, $firmId);
            if ($redeemPts > $avail) {
                throw new Exception("Customer only has {$avail} loyalty points available to redeem.");
            }
            if ($redeemPts < (float)$prog['min_redeem_points']) {
                throw new Exception("Minimum redemption is {$prog['min_redeem_points']} points.");
            }
            $maxAmt = ((float)$invoice['subtotal'] * ((float)$prog['max_redeem_pct'] / 100.0));
            $redeemAmt = $redeemPts * (float)$prog['point_value'];
            if ($redeemAmt > $maxAmt + 0.01) {
                throw new Exception("Loyalty redemption cannot exceed {$prog['max_redeem_pct']}% of bill subtotal.");
            }
            self::adjustPoints($firmId, $partyId, -$redeemPts, 'redeem', $invId, "Redeemed on Invoice #{$invoice['invoice_number']}");
        }

        $grandTotal = max(0.0, (float)($invoice['grand_total'] ?? 0));
        $unit = max(1.0, (float)$prog['currency_unit']);
        $rate = max(0.0, (float)$prog['points_per_currency']);
        $earned = round(($grandTotal / $unit) * $rate, 2);

        if ($earned > 0) {
            self::adjustPoints($firmId, $partyId, $earned, 'earn', $invId, "Earned on Invoice #{$invoice['invoice_number']}");
        }
    }

    /**
     * Reverse any earn/redeem transactions when a sale invoice is deleted.
     */
    public static function reverseInvoicePoints(int $firmId, int $invoiceId): void {
        $stmt = self::db()->prepare("
            SELECT * FROM loyalty_transactions
            WHERE firm_id = ? AND invoice_id = ? AND txn_type IN ('earn', 'redeem')
            ORDER BY id ASC
        ");
        $stmt->execute([$firmId, $invoiceId]);
        foreach ($stmt->fetchAll() as $txn) {
            $reverseDelta = -1 * (float)$txn['points_delta'];
            self::adjustPoints(
                $firmId,
                (int)$txn['party_id'],
                $reverseDelta,
                'reverse',
                $invoiceId,
                "Reversed on deleted Invoice #{$invoiceId}"
            );
        }
    }
}
