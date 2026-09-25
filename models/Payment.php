<?php
/**
 * Payment (Vouchers & Receipts) Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Invoice.php';
require_once __DIR__ . '/Party.php';

class Payment extends Model {
    public static function getByFirmId(int $firmId, ?string $type = null): array {
        if ($type) {
            $stmt = self::db()->prepare("
                SELECT p.*, pt.name as party_name, pt.type as party_type
                FROM payments p
                JOIN parties pt ON p.party_id = pt.id
                WHERE p.firm_id = ? AND p.type = ?
                ORDER BY p.payment_date DESC, p.id DESC
            ");
            $stmt->execute([$firmId, $type]);
        } else {
            $stmt = self::db()->prepare("
                SELECT p.*, pt.name as party_name, pt.type as party_type
                FROM payments p
                JOIN parties pt ON p.party_id = pt.id
                WHERE p.firm_id = ?
                ORDER BY p.payment_date DESC, p.id DESC
            ");
            $stmt->execute([$firmId]);
        }
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("
            SELECT p.*, pt.name as party_name, pt.type as party_type, pt.phone as party_phone,
                   pt.billing_address as party_address, pt.gstin as party_gstin,
                   pt.state as party_state, pt.state_code as party_state_code,
                   inv.invoice_number as linked_invoice_number
            FROM payments p
            JOIN parties pt ON p.party_id = pt.id
            LEFT JOIN invoices inv ON p.invoice_id = inv.id
            WHERE p.id = ? AND p.firm_id = ?
        ");
        $stmt->execute([$id, $firmId]);
        return $stmt->fetch() ?: null;
    }

    public static function getNextPaymentNumber(int $firmId, string $type = 'payment_in'): string {
        $last = Invoice::getOrInitLastSequence($firmId, $type);
        $next = $last + 1;
        $stmt = self::db()->prepare("SELECT id FROM payments WHERE firm_id = ? AND type = ? AND payment_number = ?");
        while (true) {
            $cand = self::formatDocumentNumber($next);
            $stmt->execute([$firmId, $type, $cand]);
            if (!$stmt->fetch()) {
                return $cand;
            }
            $next++;
        }
    }

    public static function create(array $data): array {
        return self::transaction(function($db) use ($data) {
            $firmId = (int)$data['firm_id'];
            $payType = $data['type'] ?? 'payment_in';
            $finalNumber = Invoice::allocateDocumentNumber($firmId, $payType, $data['payment_number'] ?? null, 'payments', 'payment_number');
            $partyId = (int)$data['party_id'];

            $stmt = $db->prepare("
                INSERT INTO payments (
                    firm_id, type, payment_number, payment_date, party_id,
                    invoice_id, amount, payment_mode, reference_no, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $firmId,
                $payType,
                $finalNumber,
                $data['payment_date'],
                $partyId,
                !empty($data['invoice_id']) ? (int)$data['invoice_id'] : null,
                (float)($data['amount'] ?? 0),
                $data['payment_mode'] ?? 'cash',
                !empty($data['reference_no']) ? trim($data['reference_no']) : null,
                !empty($data['notes']) ? trim($data['notes']) : null
            ]);

            $id = (int)$db->lastInsertId();

            Party::syncFIFOSettlement($partyId, $firmId);

            return self::getById($id, $firmId);
        });
    }

    public static function delete(int $id, int $firmId): bool {
        return self::transaction(function($db) use ($id, $firmId) {
            $payment = self::getById($id, $firmId);
            if (!$payment) return false;

            $partyId = (int)$payment['party_id'];
            $db->prepare("DELETE FROM payments WHERE id = ? AND firm_id = ?")->execute([$id, $firmId]);

            if ($partyId) {
                Party::syncFIFOSettlement($partyId, $firmId);
            }
            return true;
        });
    }
}
