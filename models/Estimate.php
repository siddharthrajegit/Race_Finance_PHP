<?php
/**
 * Estimate / Quotation Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/../core/Security.php';

class Estimate extends Model {
    public static function getByFirmId(int $firmId): array {
        $stmt = self::db()->prepare("
            SELECT e.*, COUNT(ei.id) as item_count
            FROM estimates e
            LEFT JOIN estimate_items ei ON e.id = ei.estimate_id
            WHERE e.firm_id = ?
            GROUP BY e.id
            ORDER BY e.estimate_date DESC, e.id DESC
        ");
        $stmt->execute([$firmId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM estimates WHERE id = ? AND firm_id = ?");
        $stmt->execute([$id, $firmId]);
        $estimate = $stmt->fetch();
        if (!$estimate) return null;

        $itemStmt = self::db()->prepare("SELECT * FROM estimate_items WHERE estimate_id = ? ORDER BY id ASC");
        $itemStmt->execute([$id]);
        $estimate['items'] = $itemStmt->fetchAll();

        return $estimate;
    }

    public static function getOrInitLastSequence(int $firmId): int {
        $stmt = self::db()->prepare("SELECT last_number FROM document_sequences WHERE firm_id = ? AND doc_type = 'estimate'");
        $stmt->execute([$firmId]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['last_number'];
        }

        $maxNum = 0;
        $checkStmt = self::db()->prepare("SELECT estimate_number FROM estimates WHERE firm_id = ?");
        $checkStmt->execute([$firmId]);
        while ($r = $checkStmt->fetch()) {
            $raw = preg_replace('/[^0-9]/', '', (string)$r['estimate_number']);
            if ($raw !== '') {
                $val = (int)$raw;
                if ($val > $maxNum && $val < 10000000) {
                    $maxNum = $val;
                }
            }
        }

        if (DB::isMysql()) {
            self::db()->prepare("
                INSERT INTO document_sequences (firm_id, doc_type, last_number, updated_at)
                VALUES (?, 'estimate', ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE last_number = last_number
            ")->execute([$firmId, $maxNum]);
        } else {
            self::db()->prepare("
                INSERT INTO document_sequences (firm_id, doc_type, last_number, updated_at)
                VALUES (?, 'estimate', ?, CURRENT_TIMESTAMP)
                ON CONFLICT(firm_id, doc_type) DO NOTHING
            ")->execute([$firmId, $maxNum]);
        }

        return $maxNum;
    }

    public static function getNextEstimateNumber(int $firmId): string {
        $last = self::getOrInitLastSequence($firmId);
        $next = $last + 1;
        return str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }

    public static function allocateEstimateNumber(int $firmId, ?string $requestedNumber): string {
        self::getOrInitLastSequence($firmId);

        if ($requestedNumber !== null && trim((string)$requestedNumber) !== '') {
            $raw = trim((string)$requestedNumber);
            $candidate = $raw;
            $isNumeric = false;
            $parsedNum = null;

            if (ctype_digit($raw)) {
                $isNumeric = true;
                $parsedNum = (int)$raw;
                $candidate = str_pad((string)$parsedNum, 3, '0', STR_PAD_LEFT);
            }

            $stmt = self::db()->prepare("SELECT id FROM estimates WHERE firm_id = ? AND estimate_number = ?");
            $stmt->execute([$firmId, $candidate]);
            if ($stmt->fetch()) {
                throw new Exception("Estimate number \"{$candidate}\" already exists. Please choose a different number.");
            }

            if ($isNumeric && $parsedNum !== null) {
                if (DB::isMysql()) {
                    self::db()->prepare("
                        UPDATE document_sequences
                        SET last_number = GREATEST(last_number, ?), updated_at = CURRENT_TIMESTAMP
                        WHERE firm_id = ? AND doc_type = 'estimate'
                    ")->execute([$parsedNum, $firmId]);
                } else {
                    self::db()->prepare("
                        UPDATE document_sequences
                        SET last_number = MAX(last_number, ?), updated_at = CURRENT_TIMESTAMP
                        WHERE firm_id = ? AND doc_type = 'estimate'
                    ")->execute([$parsedNum, $firmId]);
                }
            }

            return $candidate;
        }

        // Automatic generation
        $pdo = self::db();
        $isTxOwner = false;
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
            $isTxOwner = true;
        }

        try {
            $seqStmt = $pdo->prepare("SELECT last_number FROM document_sequences WHERE firm_id = ? AND doc_type = 'estimate'");
            $seqStmt->execute([$firmId]);
            $currentNum = (int)($seqStmt->fetchColumn() ?: 0);

            $attempts = 0;
            $finalCandidate = null;

            while ($attempts < 500) {
                $currentNum++;
                $candidate = str_pad((string)$currentNum, 3, '0', STR_PAD_LEFT);

                $check = $pdo->prepare("SELECT id FROM estimates WHERE firm_id = ? AND estimate_number = ?");
                $check->execute([$firmId, $candidate]);
                if (!$check->fetch()) {
                    $finalCandidate = $candidate;
                    break;
                }
                $attempts++;
            }

            if ($finalCandidate === null) {
                throw new Exception("Unable to allocate unique estimate number after multiple attempts.");
            }

            $upd = $pdo->prepare("UPDATE document_sequences SET last_number = ?, updated_at = CURRENT_TIMESTAMP WHERE firm_id = ? AND doc_type = 'estimate'");
            $upd->execute([$currentNum, $firmId]);

            if ($isTxOwner) {
                $pdo->commit();
            }

            return $finalCandidate;
        } catch (Throwable $e) {
            if ($isTxOwner && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function create(array $data, array $items): ?array {
        $firmId = (int)$data['firm_id'];
        $estimateDate = !empty($data['estimate_date']) ? $data['estimate_date'] : date('Y-m-d');
        $customerName = !empty($data['customer_name']) ? trim($data['customer_name']) : null;
        $notes = !empty($data['notes']) ? trim($data['notes']) : null;
        $status = !empty($data['status']) ? trim($data['status']) : 'draft';

        $pdo = self::db();
        $isTxOwner = false;
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
            $isTxOwner = true;
        }

        try {
            $requestedNumber = !empty($data['estimate_number']) ? trim($data['estimate_number']) : null;
            $finalNumber = self::allocateEstimateNumber($firmId, $requestedNumber);

            // Compute totals
            $subtotal = 0.0;
            $discountTotal = 0.0;
            $taxTotal = 0.0;
            $grandTotal = 0.0;

            $preparedItems = [];
            foreach ($items as $it) {
                $qty = max(0.001, Security::parseCleanFloat($it['quantity'] ?? 1));
                $rate = max(0.0, Security::parseCleanFloat($it['rate'] ?? 0));
                $taxRate = min(100.0, max(0.0, Security::parseCleanFloat($it['tax_rate'] ?? 0)));
                $discPercent = min(100.0, max(0.0, Security::parseCleanFloat($it['discount_percent'] ?? 0)));

                $lineBase = $qty * $rate;
                $lineDisc = round($lineBase * ($discPercent / 100), 2);
                $lineTaxable = max(0.0, $lineBase - $lineDisc);
                $lineTax = round($lineTaxable * ($taxRate / 100), 2);
                $lineTotal = round($lineTaxable + $lineTax, 2);

                $subtotal += $lineTaxable;
                $discountTotal += $lineDisc;
                $taxTotal += $lineTax;
                $grandTotal += $lineTotal;

                $preparedItems[] = [
                    'item_id' => !empty($it['item_id']) ? (int)$it['item_id'] : null,
                    'item_name' => trim($it['item_name'] ?? 'Product'),
                    'hsn_code' => !empty($it['hsn_code']) ? trim($it['hsn_code']) : null,
                    'unit' => !empty($it['unit']) ? strtoupper(trim($it['unit'])) : 'PCS',
                    'sub_unit' => !empty($it['sub_unit']) ? strtoupper(trim($it['sub_unit'])) : null,
                    'sub_unit_qty' => isset($it['sub_unit_qty']) && (float)$it['sub_unit_qty'] > 0 ? (float)$it['sub_unit_qty'] : null,
                    'quantity' => $qty,
                    'rate' => $rate,
                    'tax_rate' => $taxRate,
                    'discount_percent' => $discPercent,
                    'discount_amount' => $lineDisc,
                    'taxable_amount' => $lineTaxable,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal
                ];
            }

            $stmt = $pdo->prepare("
                INSERT INTO estimates (
                    firm_id, estimate_number, estimate_date, customer_name,
                    subtotal, discount_amount, tax_amount, grand_total, notes, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $firmId,
                $finalNumber,
                $estimateDate,
                $customerName,
                round($subtotal, 2),
                round($discountTotal, 2),
                round($taxTotal, 2),
                round($grandTotal, 2),
                $notes,
                $status
            ]);

            $estimateId = (int)$pdo->lastInsertId();

            $itemInsertStmt = $pdo->prepare("
                INSERT INTO estimate_items (
                    estimate_id, item_id, item_name, hsn_code, unit,
                    sub_unit, sub_unit_qty, quantity, rate, tax_rate,
                    discount_percent, discount_amount, taxable_amount, tax_amount, total_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($preparedItems as $pItem) {
                $itemInsertStmt->execute([
                    $estimateId,
                    $pItem['item_id'],
                    $pItem['item_name'],
                    $pItem['hsn_code'],
                    $pItem['unit'],
                    $pItem['sub_unit'],
                    $pItem['sub_unit_qty'],
                    $pItem['quantity'],
                    $pItem['rate'],
                    $pItem['tax_rate'],
                    $pItem['discount_percent'],
                    $pItem['discount_amount'],
                    $pItem['taxable_amount'],
                    $pItem['tax_amount'],
                    $pItem['total_amount']
                ]);
            }

            if ($isTxOwner) {
                $pdo->commit();
            }

            return self::getById($estimateId, $firmId);
        } catch (Throwable $e) {
            if ($isTxOwner && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function delete(int $id, int $firmId): bool {
        $stmt = self::db()->prepare("DELETE FROM estimates WHERE id = ? AND firm_id = ?");
        return $stmt->execute([$id, $firmId]);
    }

    public static function updateStatus(int $id, int $firmId, string $status): bool {
        $stmt = self::db()->prepare("UPDATE estimates SET status = ? WHERE id = ? AND firm_id = ?");
        return $stmt->execute([$status, $id, $firmId]);
    }
}
