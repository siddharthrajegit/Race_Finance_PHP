<?php
/**
 * Invoice (Sales & Purchase Records) Model
 * Handles atomic numbering sequences, inventory synchronization, and auto-settlement
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/Party.php';
require_once __DIR__ . '/../core/Security.php';

class Invoice extends Model {
    public static function getByFirmId(int $firmId, string $type = 'sale'): array {
        $stmt = self::db()->prepare("
            SELECT i.*, p.name as party_display_name
            FROM invoices i
            LEFT JOIN parties p ON i.party_id = p.id
            WHERE i.firm_id = ? AND i.type = ?
            ORDER BY i.invoice_date DESC, i.id DESC
        ");
        $stmt->execute([$firmId, $type]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id, int $firmId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM invoices WHERE id = ? AND firm_id = ?");
        $stmt->execute([$id, $firmId]);
        $invoice = $stmt->fetch();
        if (!$invoice) return null;

        $itemStmt = self::db()->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $itemStmt->execute([$id]);
        $invoice['items'] = $itemStmt->fetchAll();

        return $invoice;
    }

    public static function getOrInitLastSequence(int $firmId, string $docType): int {
        $stmt = self::db()->prepare("SELECT last_number FROM document_sequences WHERE firm_id = ? AND doc_type = ?");
        $stmt->execute([$firmId, $docType]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['last_number'];
        }

        $maxNum = 0;
        if ($docType === 'sale' || $docType === 'purchase') {
            $checkStmt = self::db()->prepare("SELECT invoice_number FROM invoices WHERE firm_id = ? AND type = ?");
            $checkStmt->execute([$firmId, $docType]);
            while ($r = $checkStmt->fetch()) {
                if (ctype_digit((string)$r['invoice_number'])) {
                    $val = (int)$r['invoice_number'];
                    if ($val > $maxNum && $val < 10000000) {
                        $maxNum = $val;
                    }
                }
            }
        } elseif ($docType === 'payment_in' || $docType === 'payment_out') {
            $checkStmt = self::db()->prepare("SELECT payment_number FROM payments WHERE firm_id = ? AND type = ?");
            $checkStmt->execute([$firmId, $docType]);
            while ($r = $checkStmt->fetch()) {
                if (ctype_digit((string)$r['payment_number'])) {
                    $val = (int)$r['payment_number'];
                    if ($val > $maxNum && $val < 10000000) {
                        $maxNum = $val;
                    }
                }
            }
        }

        if (DB::isMysql()) {
            self::db()->prepare("
                INSERT INTO document_sequences (firm_id, doc_type, last_number, updated_at)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE last_number = last_number
            ")->execute([$firmId, $docType, $maxNum]);
        } else {
            self::db()->prepare("
                INSERT INTO document_sequences (firm_id, doc_type, last_number, updated_at)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(firm_id, doc_type) DO NOTHING
            ")->execute([$firmId, $docType, $maxNum]);
        }

        return $maxNum;
    }

    public static function allocateDocumentNumber(int $firmId, string $docType, ?string $requestedNumber, string $table, string $column): string {
        self::getOrInitLastSequence($firmId, $docType);

        if ($requestedNumber !== null && trim((string)$requestedNumber) !== '') {
            $raw = trim((string)$requestedNumber);
            $candidate = $raw;
            $isNumeric = false;
            $parsedNum = null;

            if (ctype_digit($raw)) {
                $isNumeric = true;
                $parsedNum = (int)$raw;
                $candidate = self::formatDocumentNumber($parsedNum);
            }

            $stmt = self::db()->prepare("SELECT id FROM {$table} WHERE firm_id = ? AND type = ? AND {$column} = ?");
            $stmt->execute([$firmId, $docType, $candidate]);
            if ($stmt->fetch()) {
                $label = ($docType === 'purchase') ? 'Purchase Bill' : (($docType === 'sale') ? 'Sales Invoice' : (($docType === 'payment_in') ? 'Payment Receipt' : 'Payment Voucher'));
                throw new Exception("Record number \"{$candidate}\" already exists for this {$label}. Please use a different number.");
            }

            if ($isNumeric && $parsedNum !== null) {
                if (DB::isMysql()) {
                    self::db()->prepare("
                        UPDATE document_sequences
                        SET last_number = GREATEST(last_number, ?), updated_at = CURRENT_TIMESTAMP
                        WHERE firm_id = ? AND doc_type = ?
                    ")->execute([$parsedNum, $firmId, $docType]);
                } else {
                    self::db()->prepare("
                        UPDATE document_sequences
                        SET last_number = MAX(last_number, ?), updated_at = CURRENT_TIMESTAMP
                        WHERE firm_id = ? AND doc_type = ?
                    ")->execute([$parsedNum, $firmId, $docType]);
                }
            }

            return $candidate;
        }

        $attempts = 0;
        $candidateNumber = null;
        while ($attempts < 1000) {
            $attempts++;
            self::db()->prepare("
                UPDATE document_sequences
                SET last_number = last_number + 1, updated_at = CURRENT_TIMESTAMP
                WHERE firm_id = ? AND doc_type = ?
            ")->execute([$firmId, $docType]);

            $seqStmt = self::db()->prepare("SELECT last_number FROM document_sequences WHERE firm_id = ? AND doc_type = ?");
            $seqStmt->execute([$firmId, $docType]);
            $seqRow = $seqStmt->fetch();
            $candidate = self::formatDocumentNumber((int)($seqRow['last_number'] ?? 1));

            $chk = self::db()->prepare("SELECT id FROM {$table} WHERE firm_id = ? AND type = ? AND {$column} = ?");
            $chk->execute([$firmId, $docType, $candidate]);
            if (!$chk->fetch()) {
                $candidateNumber = $candidate;
                break;
            }
        }

        if (!$candidateNumber) {
            throw new Exception("Failed to allocate a unique document number for {$docType} in firm {$firmId}.");
        }

        return $candidateNumber;
    }

    public static function getNextInvoiceNumber(int $firmId, string $type = 'sale'): string {
        $last = self::getOrInitLastSequence($firmId, $type);
        $next = $last + 1;
        $stmt = self::db()->prepare("SELECT id FROM invoices WHERE firm_id = ? AND type = ? AND invoice_number = ?");
        while (true) {
            $cand = self::formatDocumentNumber($next);
            $stmt->execute([$firmId, $type, $cand]);
            if (!$stmt->fetch()) {
                return $cand;
            }
            $next++;
        }
    }

    public static function create(array $invoiceData, array $itemsData): array {
        return self::transaction(function($db) use ($invoiceData, $itemsData) {
            $firmId = (int)$invoiceData['firm_id'];
            $docType = $invoiceData['type'] ?? 'sale';
            $finalInvoiceNumber = self::allocateDocumentNumber($firmId, $docType, $invoiceData['invoice_number'] ?? null, 'invoices', 'invoice_number');

            $cleanSubtotal = max(0.0, (float)($invoiceData['subtotal'] ?? 0));
            $cleanDiscType = in_array($invoiceData['discount_type'] ?? '', ['flat', 'fixed']) ? $invoiceData['discount_type'] : 'percentage';
            $rawDiscVal = max(0.0, (float)($invoiceData['discount_value'] ?? 0));
            $cleanDiscVal = ($cleanDiscType === 'percentage') ? min(100.0, $rawDiscVal) : min($cleanSubtotal, $rawDiscVal);
            $cleanDiscAmt = min($cleanSubtotal, max(0.0, (float)($invoiceData['discount_amount'] ?? 0)));

            $invStmt = $db->prepare("
                INSERT INTO invoices (
                    firm_id, type, invoice_number, invoice_date, due_date, party_id,
                    party_name, party_phone, party_gstin, party_address, party_state, party_state_code,
                    is_gst_bill, is_interstate, subtotal, discount_type, discount_value, discount_amount,
                    taxable_amount, cgst_amount, sgst_amount, igst_amount, tax_amount, round_off,
                    grand_total, paid_amount, balance_due, payment_status, payment_mode, notes, terms
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $invStmt->execute([
                $firmId, $docType, $finalInvoiceNumber, $invoiceData['invoice_date'], $invoiceData['due_date'] ?? null,
                $invoiceData['party_id'] ?? null, trim($invoiceData['party_name']), $invoiceData['party_phone'] ?? null, $invoiceData['party_gstin'] ?? null,
                $invoiceData['party_address'] ?? null, $invoiceData['party_state'] ?? null, $invoiceData['party_state_code'] ?? null,
                !empty($invoiceData['is_gst_bill']) ? 1 : 0, !empty($invoiceData['is_interstate']) ? 1 : 0, $cleanSubtotal,
                $cleanDiscType, $cleanDiscVal, $cleanDiscAmt,
                max(0.0, (float)($invoiceData['taxable_amount'] ?? 0)), max(0.0, (float)($invoiceData['cgst_amount'] ?? 0)), max(0.0, (float)($invoiceData['sgst_amount'] ?? 0)),
                max(0.0, (float)($invoiceData['igst_amount'] ?? 0)), max(0.0, (float)($invoiceData['tax_amount'] ?? 0)), (float)($invoiceData['round_off'] ?? 0),
                max(0.0, (float)($invoiceData['grand_total'] ?? 0)), max(0.0, (float)($invoiceData['paid_amount'] ?? 0)), max(0.0, (float)($invoiceData['balance_due'] ?? 0)),
                $invoiceData['payment_status'] ?? 'unpaid', $invoiceData['payment_mode'] ?? 'cash', $invoiceData['notes'] ?? null, $invoiceData['terms'] ?? null
            ]);

            $invoiceId = (int)$db->lastInsertId();

            $itemStmt = $db->prepare("
                INSERT INTO invoice_items (
                    invoice_id, item_id, item_name, hsn_code, unit, quantity, rate,
                    discount_percent, discount_amount, taxable_amount, tax_rate,
                    cgst_rate, cgst_amount, sgst_rate, sgst_amount, igst_rate, igst_amount, total_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($itemsData as $item) {
                $rawQty = (float)($item['quantity'] ?? 1);
                $rawRate = (float)($item['rate'] ?? 0);
                if ($rawQty <= 0) {
                    throw new Exception("Quantity for item \"" . ($item['item_name'] ?? 'Line Item') . "\" must be greater than 0.");
                }
                if ($rawRate < 0) {
                    throw new Exception("Rate for item \"" . ($item['item_name'] ?? 'Line Item') . "\" cannot be negative.");
                }

                $gross = $rawQty * $rawRate;
                $discPct = min(100.0, max(0.0, (float)($item['discount_percent'] ?? 0)));
                $discAmt = min($gross, max(0.0, (float)($item['discount_amount'] ?? 0)));
                $taxable = max(0.0, (float)($item['taxable_amount'] ?? ($gross - $discAmt)));
                $taxRate = min(100.0, max(0.0, (float)($item['tax_rate'] ?? 0)));
                $cgstAmt = max(0.0, (float)($item['cgst_amount'] ?? 0));
                $sgstAmt = max(0.0, (float)($item['sgst_amount'] ?? 0));
                $igstAmt = max(0.0, (float)($item['igst_amount'] ?? 0));
                $totAmt = max(0.0, (float)($item['total_amount'] ?? ($taxable + $cgstAmt + $sgstAmt + $igstAmt)));

                $itemStmt->execute([
                    $invoiceId, $item['item_id'] ?? null, $item['item_name'], $item['hsn_code'] ?? null,
                    $item['unit'] ?? 'PCS', $rawQty, $rawRate,
                    $discPct, $discAmt, $taxable, $taxRate,
                    (float)($item['cgst_rate'] ?? 0), $cgstAmt,
                    (float)($item['sgst_rate'] ?? 0), $sgstAmt,
                    (float)($item['igst_rate'] ?? 0), $igstAmt,
                    $totAmt
                ]);

                Item::adjustInvoiceItemStock($item['item_id'] ?? null, $firmId, $docType, $rawQty);
            }

            // Auto payment voucher / receipt
            $paidAmt = (float)($invoiceData['paid_amount'] ?? 0);
            $partyId = !empty($invoiceData['party_id']) ? (int)$invoiceData['party_id'] : null;
            if ($paidAmt > 0 && $partyId) {
                $payType = ($docType === 'purchase') ? 'payment_out' : 'payment_in';
                $payNum = self::allocateDocumentNumber($firmId, $payType, null, 'payments', 'payment_number');
                $db->prepare("
                    INSERT INTO payments (
                        firm_id, type, payment_number, payment_date, party_id,
                        invoice_id, amount, payment_mode, reference_no, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $firmId, $payType, $payNum, $invoiceData['invoice_date'], $partyId,
                    $invoiceId, $paidAmt, $invoiceData['payment_mode'] ?? 'cash',
                    $finalInvoiceNumber, "Paid on bill {$finalInvoiceNumber}"
                ]);
            }

            if ($partyId) {
                Party::syncFIFOSettlement($partyId, $firmId);
            }

            return self::getById($invoiceId, $firmId);
        });
    }

    public static function update(int $id, int $firmId, array $invoiceData, array $itemsData): ?array {
        return self::transaction(function($db) use ($id, $firmId, $invoiceData, $itemsData) {
            $existing = self::getById($id, $firmId);
            if (!$existing) return null;

            // 1. Revert previous inventory
            foreach ($existing['items'] as $oldItem) {
                Item::adjustInvoiceItemStock($oldItem['item_id'] ?? null, $firmId, $existing['type'], (float)$oldItem['quantity'], -1);
            }

            // 2. Delete existing items
            $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);

            // 3. Update header
            $docType = $invoiceData['type'] ?? $existing['type'] ?? 'sale';
            $finalInvoiceNumber = $existing['invoice_number'];

            if (!empty($invoiceData['invoice_number']) && trim((string)$invoiceData['invoice_number']) !== '') {
                $raw = trim((string)$invoiceData['invoice_number']);
                if (ctype_digit($raw)) {
                    $parsed = (int)$raw;
                    $finalInvoiceNumber = self::formatDocumentNumber($parsed);
                    if (DB::isMysql()) {
                        $db->prepare("
                            UPDATE document_sequences
                            SET last_number = GREATEST(last_number, ?), updated_at = CURRENT_TIMESTAMP
                            WHERE firm_id = ? AND doc_type = ?
                        ")->execute([$parsed, $firmId, $docType]);
                    } else {
                        $db->prepare("
                            UPDATE document_sequences
                            SET last_number = MAX(last_number, ?), updated_at = CURRENT_TIMESTAMP
                            WHERE firm_id = ? AND doc_type = ?
                        ")->execute([$parsed, $firmId, $docType]);
                    }
                } else {
                    $finalInvoiceNumber = $raw;
                }

                $chk = $db->prepare("SELECT id FROM invoices WHERE firm_id = ? AND type = ? AND invoice_number = ? AND id != ?");
                $chk->execute([$firmId, $docType, $finalInvoiceNumber, $id]);
                if ($chk->fetch()) {
                    $label = ($docType === 'purchase') ? 'Purchase Bill' : 'Sales Invoice';
                    throw new Exception("Record number \"{$finalInvoiceNumber}\" already exists for this {$label}.");
                }
            }

            $cleanSubtotal = max(0.0, (float)($invoiceData['subtotal'] ?? 0));
            $cleanDiscType = in_array($invoiceData['discount_type'] ?? '', ['flat', 'fixed']) ? $invoiceData['discount_type'] : 'percentage';
            $rawDiscVal = max(0.0, (float)($invoiceData['discount_value'] ?? 0));
            $cleanDiscVal = ($cleanDiscType === 'percentage') ? min(100.0, $rawDiscVal) : min($cleanSubtotal, $rawDiscVal);
            $cleanDiscAmt = min($cleanSubtotal, max(0.0, (float)($invoiceData['discount_amount'] ?? 0)));

            $db->prepare("
                UPDATE invoices SET
                    type = ?, invoice_number = ?, invoice_date = ?, due_date = ?, party_id = ?,
                    party_name = ?, party_phone = ?, party_gstin = ?, party_address = ?, party_state = ?, party_state_code = ?,
                    is_gst_bill = ?, is_interstate = ?, subtotal = ?, discount_type = ?, discount_value = ?, discount_amount = ?,
                    taxable_amount = ?, cgst_amount = ?, sgst_amount = ?, igst_amount = ?, tax_amount = ?, round_off = ?,
                    grand_total = ?, paid_amount = ?, balance_due = ?, payment_status = ?, payment_mode = ?, notes = ?, terms = ?
                WHERE id = ? AND firm_id = ?
            ")->execute([
                $docType, $finalInvoiceNumber, $invoiceData['invoice_date'], $invoiceData['due_date'] ?? null,
                $invoiceData['party_id'] ?? null, trim($invoiceData['party_name']), $invoiceData['party_phone'] ?? null, $invoiceData['party_gstin'] ?? null,
                $invoiceData['party_address'] ?? null, $invoiceData['party_state'] ?? null, $invoiceData['party_state_code'] ?? null,
                !empty($invoiceData['is_gst_bill']) ? 1 : 0, !empty($invoiceData['is_interstate']) ? 1 : 0, $cleanSubtotal,
                $cleanDiscType, $cleanDiscVal, $cleanDiscAmt,
                max(0.0, (float)($invoiceData['taxable_amount'] ?? 0)), max(0.0, (float)($invoiceData['cgst_amount'] ?? 0)), max(0.0, (float)($invoiceData['sgst_amount'] ?? 0)),
                max(0.0, (float)($invoiceData['igst_amount'] ?? 0)), max(0.0, (float)($invoiceData['tax_amount'] ?? 0)), (float)($invoiceData['round_off'] ?? 0),
                max(0.0, (float)($invoiceData['grand_total'] ?? 0)), max(0.0, (float)($invoiceData['paid_amount'] ?? 0)), max(0.0, (float)($invoiceData['balance_due'] ?? 0)),
                $invoiceData['payment_status'] ?? 'unpaid', $invoiceData['payment_mode'] ?? 'cash', $invoiceData['notes'] ?? null, $invoiceData['terms'] ?? null,
                $id, $firmId
            ]);

            // 4. Insert updated items
            $itemStmt = $db->prepare("
                INSERT INTO invoice_items (
                    invoice_id, item_id, item_name, hsn_code, unit, quantity, rate,
                    discount_percent, discount_amount, taxable_amount, tax_rate,
                    cgst_rate, cgst_amount, sgst_rate, sgst_amount, igst_rate, igst_amount, total_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($itemsData as $item) {
                $rawQty = (float)($item['quantity'] ?? 1);
                $rawRate = (float)($item['rate'] ?? 0);
                if ($rawQty <= 0) {
                    throw new Exception("Quantity for item \"" . ($item['item_name'] ?? 'Line Item') . "\" must be greater than 0.");
                }
                if ($rawRate < 0) {
                    throw new Exception("Rate for item \"" . ($item['item_name'] ?? 'Line Item') . "\" cannot be negative.");
                }

                $gross = $rawQty * $rawRate;
                $discPct = min(100.0, max(0.0, (float)($item['discount_percent'] ?? 0)));
                $discAmt = min($gross, max(0.0, (float)($item['discount_amount'] ?? 0)));
                $taxable = max(0.0, (float)($item['taxable_amount'] ?? ($gross - $discAmt)));
                $taxRate = min(100.0, max(0.0, (float)($item['tax_rate'] ?? 0)));
                $cgstAmt = max(0.0, (float)($item['cgst_amount'] ?? 0));
                $sgstAmt = max(0.0, (float)($item['sgst_amount'] ?? 0));
                $igstAmt = max(0.0, (float)($item['igst_amount'] ?? 0));
                $totAmt = max(0.0, (float)($item['total_amount'] ?? ($taxable + $cgstAmt + $sgstAmt + $igstAmt)));

                $itemStmt->execute([
                    $id, $item['item_id'] ?? null, $item['item_name'], $item['hsn_code'] ?? null,
                    $item['unit'] ?? 'PCS', $rawQty, $rawRate,
                    $discPct, $discAmt, $taxable, $taxRate,
                    (float)($item['cgst_rate'] ?? 0), $cgstAmt,
                    (float)($item['sgst_rate'] ?? 0), $sgstAmt,
                    (float)($item['igst_rate'] ?? 0), $igstAmt,
                    $totAmt
                ]);

                Item::adjustInvoiceItemStock($item['item_id'] ?? null, $firmId, $docType, $rawQty);
            }

            // 5. Update or recreate payment
            $paidAmt = (float)($invoiceData['paid_amount'] ?? 0);
            $partyId = !empty($invoiceData['party_id']) ? (int)$invoiceData['party_id'] : null;

            $payStmt = $db->prepare("SELECT id FROM payments WHERE invoice_id = ? AND firm_id = ?");
            $payStmt->execute([$id, $firmId]);
            $existingPayment = $payStmt->fetch();

            if ($paidAmt > 0 && $partyId) {
                $payType = ($docType === 'purchase') ? 'payment_out' : 'payment_in';
                if ($existingPayment) {
                    $db->prepare("
                        UPDATE payments SET
                            payment_date = ?, party_id = ?, amount = ?, payment_mode = ?,
                            reference_no = ?, notes = ?
                        WHERE id = ? AND firm_id = ?
                    ")->execute([
                        $invoiceData['invoice_date'], $partyId, $paidAmt, $invoiceData['payment_mode'] ?? 'cash',
                        $finalInvoiceNumber, "Paid on bill {$finalInvoiceNumber}",
                        $existingPayment['id'], $firmId
                    ]);
                } else {
                    $payNum = self::allocateDocumentNumber($firmId, $payType, null, 'payments', 'payment_number');
                    $db->prepare("
                        INSERT INTO payments (
                            firm_id, type, payment_number, payment_date, party_id,
                            invoice_id, amount, payment_mode, reference_no, notes
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ")->execute([
                        $firmId, $payType, $payNum, $invoiceData['invoice_date'], $partyId,
                        $id, $paidAmt, $invoiceData['payment_mode'] ?? 'cash',
                        $finalInvoiceNumber, "Paid on bill {$finalInvoiceNumber}"
                    ]);
                }
            } elseif ($existingPayment && $paidAmt == 0) {
                $db->prepare("DELETE FROM payments WHERE id = ? AND firm_id = ?")->execute([$existingPayment['id'], $firmId]);
            }

            if ($partyId) {
                Party::syncFIFOSettlement($partyId, $firmId);
            }
            if (!empty($existing['party_id']) && (int)$existing['party_id'] !== $partyId) {
                Party::syncFIFOSettlement((int)$existing['party_id'], $firmId);
            }

            return self::getById($id, $firmId);
        });
    }

    public static function delete(int $id, int $firmId): bool {
        return self::transaction(function($db) use ($id, $firmId) {
            $invoice = self::getById($id, $firmId);
            if (!$invoice) return false;

            $partyId = $invoice['party_id'];

            foreach ($invoice['items'] as $item) {
                Item::adjustInvoiceItemStock($item['item_id'] ?? null, $firmId, $invoice['type'], (float)$item['quantity'], -1);
            }

            $db->prepare("DELETE FROM payments WHERE invoice_id = ? AND firm_id = ?")->execute([$id, $firmId]);
            $db->prepare("DELETE FROM invoices WHERE id = ? AND firm_id = ?")->execute([$id, $firmId]);

            if ($partyId) {
                Party::syncFIFOSettlement((int)$partyId, $firmId);
            }

            return true;
        });
    }
}
