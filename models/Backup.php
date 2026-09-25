<?php
/**
 * Backup Model
 * Handles Full Data JSON Export, Restoration with ID re-mapping, and Encrypted Google Token Storage
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Firm.php';
require_once __DIR__ . '/Invoice.php';

class Backup extends Model {
    private static function getEncryptionKey(): string {
        $secret = env('SESSION_SECRET', 'biller_key');
        return hash('sha256', $secret, true);
    }

    public static function encryptToken(?string $plaintext): ?string {
        if (!$plaintext) return null;
        $key = self::getEncryptionKey();
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public static function decryptToken(?string $ciphertext): ?string {
        if (!$ciphertext) return null;
        try {
            $raw = base64_decode($ciphertext);
            if (strlen($raw) < 28) return $ciphertext;
            $iv = substr($raw, 0, 12);
            $tag = substr($raw, 12, 16);
            $cipher = substr($raw, 28);
            $key = self::getEncryptionKey();
            $decrypted = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            return ($decrypted !== false) ? $decrypted : $ciphertext;
        } catch (Throwable $e) {
            return $ciphertext;
        }
    }

    public static function saveGoogleToken(int $userId, array $data): void {
        $encAccess = self::encryptToken($data['access_token'] ?? null);
        $encRefresh = self::encryptToken($data['refresh_token'] ?? null);
        $scope = $data['scope'] ?? null;
        $tokenType = $data['token_type'] ?? null;
        $expiryDate = $data['expiry_date'] ?? null;
        $email = $data['email'] ?? null;

        if (DB::isMysql()) {
            $stmt = self::db()->prepare("
                INSERT INTO google_tokens (user_id, access_token, refresh_token, scope, token_type, expiry_date, email, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    access_token = VALUES(access_token),
                    refresh_token = COALESCE(VALUES(refresh_token), refresh_token),
                    scope = VALUES(scope),
                    token_type = VALUES(token_type),
                    expiry_date = VALUES(expiry_date),
                    email = COALESCE(VALUES(email), email),
                    updated_at = CURRENT_TIMESTAMP
            ");
        } else {
            $stmt = self::db()->prepare("
                INSERT INTO google_tokens (user_id, access_token, refresh_token, scope, token_type, expiry_date, email, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(user_id) DO UPDATE SET
                    access_token = excluded.access_token,
                    refresh_token = COALESCE(excluded.refresh_token, google_tokens.refresh_token),
                    scope = excluded.scope,
                    token_type = excluded.token_type,
                    expiry_date = excluded.expiry_date,
                    email = COALESCE(excluded.email, google_tokens.email),
                    updated_at = CURRENT_TIMESTAMP
            ");
        }

        $stmt->execute([$userId, $encAccess, $encRefresh, $scope, $tokenType, $expiryDate, $email]);
    }

    public static function getGoogleToken(int $userId): ?array {
        $stmt = self::db()->prepare("SELECT * FROM google_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row) return null;

        return [
            ...$row,
            'access_token' => self::decryptToken($row['access_token']),
            'refresh_token' => self::decryptToken($row['refresh_token'])
        ];
    }

    public static function exportFullBackup(int $userId): ?array {
        $user = User::findById($userId);
        if (!$user) return null;

        $firms = Firm::getByUserId($userId);
        $firmIds = array_map(fn($f) => (int)$f['id'], $firms);

        $parties = [];
        $items = [];
        $invoices = [];
        $invoiceItems = [];
        $payments = [];

        if (!empty($firmIds)) {
            $placeholders = implode(',', array_fill(0, count($firmIds), '?'));
            
            $pStmt = self::db()->prepare("SELECT * FROM parties WHERE firm_id IN ($placeholders)");
            $pStmt->execute($firmIds);
            $parties = $pStmt->fetchAll();

            $iStmt = self::db()->prepare("SELECT * FROM items WHERE firm_id IN ($placeholders)");
            $iStmt->execute($firmIds);
            $items = $iStmt->fetchAll();

            $invStmt = self::db()->prepare("SELECT * FROM invoices WHERE firm_id IN ($placeholders)");
            $invStmt->execute($firmIds);
            $invoices = $invStmt->fetchAll();

            $payStmt = self::db()->prepare("SELECT * FROM payments WHERE firm_id IN ($placeholders)");
            $payStmt->execute($firmIds);
            $payments = $payStmt->fetchAll();

            $invoiceIds = array_map(fn($inv) => (int)$inv['id'], $invoices);
            if (!empty($invoiceIds)) {
                $invPlaceholders = implode(',', array_fill(0, count($invoiceIds), '?'));
                $iiStmt = self::db()->prepare("SELECT * FROM invoice_items WHERE invoice_id IN ($invPlaceholders)");
                $iiStmt->execute($invoiceIds);
                $invoiceItems = $iiStmt->fetchAll();
            }
        }

        return [
            'version' => '1.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone']
            ],
            'firms' => $firms,
            'parties' => $parties,
            'items' => $items,
            'invoices' => $invoices,
            'invoice_items' => $invoiceItems,
            'payments' => $payments
        ];
    }

    public static function restoreFullBackup(int $userId, array $backupData): bool {
        return self::transaction(function($db) use ($userId, $backupData) {
            $firmIdMap = [];
            $partyIdMap = [];
            $itemIdMap = [];
            $invoiceIdMap = [];

            // 1. Firms
            if (!empty($backupData['firms'])) {
                $fStmt = $db->prepare("
                    INSERT INTO firms (
                        user_id, name, gstin, pan, phone, email, address, city, state, state_code,
                        pincode, bank_name, bank_account_no, bank_ifsc, bank_branch, upi_id, terms,
                        logo_path, signature_path, is_default
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['firms'] as $f) {
                    $fStmt->execute([
                        $userId, $f['name'], $f['gstin'] ?? null, $f['pan'] ?? null, $f['phone'] ?? null, $f['email'] ?? null,
                        $f['address'] ?? null, $f['city'] ?? null, $f['state'] ?? null, $f['state_code'] ?? null, $f['pincode'] ?? null,
                        $f['bank_name'] ?? null, $f['bank_account_no'] ?? null, $f['bank_ifsc'] ?? null, $f['bank_branch'] ?? null,
                        $f['upi_id'] ?? null, $f['terms'] ?? null, $f['logo_path'] ?? null, $f['signature_path'] ?? null, $f['is_default'] ?? 0
                    ]);
                    $firmIdMap[$f['id']] = (int)$db->lastInsertId();
                }
            }

            // 2. Parties
            if (!empty($backupData['parties'])) {
                $pStmt = $db->prepare("
                    INSERT INTO parties (
                        firm_id, type, name, phone, email, gstin, pan, billing_address,
                        shipping_address, city, state, state_code, pincode, opening_balance
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['parties'] as $p) {
                    $newFirmId = $firmIdMap[$p['firm_id']] ?? null;
                    if (!$newFirmId) continue;

                    $pStmt->execute([
                        $newFirmId, $p['type'] ?? 'customer', $p['name'], $p['phone'] ?? null, $p['email'] ?? null,
                        $p['gstin'] ?? null, $p['pan'] ?? null, $p['billing_address'] ?? null, $p['shipping_address'] ?? null,
                        $p['city'] ?? null, $p['state'] ?? null, $p['state_code'] ?? null, $p['pincode'] ?? null, (float)($p['opening_balance'] ?? 0)
                    ]);
                    $partyIdMap[$p['id']] = (int)$db->lastInsertId();
                }
            }

            // 3. Items
            if (!empty($backupData['items'])) {
                $itStmt = $db->prepare("
                    INSERT INTO items (
                        firm_id, name, item_code, hsn_code, unit, sale_price, purchase_price,
                        tax_rate, tax_inclusive, opening_stock, current_stock, low_stock_threshold, description
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['items'] as $it) {
                    $newFirmId = $firmIdMap[$it['firm_id']] ?? null;
                    if (!$newFirmId) continue;

                    $itStmt->execute([
                        $newFirmId, $it['name'], $it['item_code'] ?? null, $it['hsn_code'] ?? null, $it['unit'] ?? 'PCS',
                        (float)($it['sale_price'] ?? 0), (float)($it['purchase_price'] ?? 0), (float)($it['tax_rate'] ?? 0),
                        !empty($it['tax_inclusive']) ? 1 : 0, (float)($it['opening_stock'] ?? 0), (float)($it['current_stock'] ?? 0),
                        (float)($it['low_stock_threshold'] ?? 5), $it['description'] ?? null
                    ]);
                    $itemIdMap[$it['id']] = (int)$db->lastInsertId();
                }
            }

            // 4. Invoices
            if (!empty($backupData['invoices'])) {
                $invStmt = $db->prepare("
                    INSERT INTO invoices (
                        firm_id, type, invoice_number, invoice_date, due_date, party_id,
                        party_name, party_phone, party_gstin, party_address, party_state, party_state_code,
                        is_gst_bill, is_interstate, subtotal, discount_type, discount_value, discount_amount,
                        taxable_amount, cgst_amount, sgst_amount, igst_amount, tax_amount, round_off,
                        grand_total, paid_amount, balance_due, payment_status, payment_mode, notes, terms
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['invoices'] as $inv) {
                    $newFirmId = $firmIdMap[$inv['firm_id']] ?? null;
                    if (!$newFirmId) continue;

                    $newPartyId = (!empty($inv['party_id']) && isset($partyIdMap[$inv['party_id']])) ? $partyIdMap[$inv['party_id']] : null;

                    $invStmt->execute([
                        $newFirmId, $inv['type'] ?? 'sale', $inv['invoice_number'], $inv['invoice_date'], $inv['due_date'] ?? null,
                        $newPartyId, $inv['party_name'] ?? 'Walk-in Customer', $inv['party_phone'] ?? null, $inv['party_gstin'] ?? null,
                        $inv['party_address'] ?? null, $inv['party_state'] ?? null, $inv['party_state_code'] ?? null,
                        !empty($inv['is_gst_bill']) ? 1 : 0, !empty($inv['is_interstate']) ? 1 : 0, (float)($inv['subtotal'] ?? 0), $inv['discount_type'] ?? 'fixed',
                        (float)($inv['discount_value'] ?? 0), (float)($inv['discount_amount'] ?? 0), (float)($inv['taxable_amount'] ?? 0),
                        (float)($inv['cgst_amount'] ?? 0), (float)($inv['sgst_amount'] ?? 0), (float)($inv['igst_amount'] ?? 0), (float)($inv['tax_amount'] ?? 0),
                        (float)($inv['round_off'] ?? 0), (float)($inv['grand_total'] ?? 0), (float)($inv['paid_amount'] ?? 0), (float)($inv['balance_due'] ?? 0),
                        $inv['payment_status'] ?? 'unpaid', $inv['payment_mode'] ?? 'Cash', $inv['notes'] ?? null, $inv['terms'] ?? null
                    ]);
                    $invoiceIdMap[$inv['id']] = (int)$db->lastInsertId();
                }
            }

            // 5. Line Items
            if (!empty($backupData['invoice_items'])) {
                $iiStmt = $db->prepare("
                    INSERT INTO invoice_items (
                        invoice_id, item_id, item_name, hsn_code, unit, quantity, rate,
                        discount_percent, discount_amount, taxable_amount, tax_rate,
                        cgst_rate, cgst_amount, sgst_rate, sgst_amount, igst_rate, igst_amount, total_amount
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['invoice_items'] as $ii) {
                    $newInvoiceId = $invoiceIdMap[$ii['invoice_id']] ?? null;
                    if (!$newInvoiceId) continue;

                    $newItemId = (!empty($ii['item_id']) && isset($itemIdMap[$ii['item_id']])) ? $itemIdMap[$ii['item_id']] : null;

                    $iiStmt->execute([
                        $newInvoiceId, $newItemId, $ii['item_name'], $ii['hsn_code'] ?? null, $ii['unit'] ?? 'PCS',
                        (float)($ii['quantity'] ?? 1), (float)($ii['rate'] ?? 0), (float)($ii['discount_percent'] ?? 0), (float)($ii['discount_amount'] ?? 0),
                        (float)($ii['taxable_amount'] ?? 0), (float)($ii['tax_rate'] ?? 0), (float)($ii['cgst_rate'] ?? 0), (float)($ii['cgst_amount'] ?? 0),
                        (float)($ii['sgst_rate'] ?? 0), (float)($ii['sgst_amount'] ?? 0), (float)($ii['igst_rate'] ?? 0), (float)($ii['igst_amount'] ?? 0),
                        (float)($ii['total_amount'] ?? 0)
                    ]);
                }
            }

            // 6. Payments
            if (!empty($backupData['payments'])) {
                $payStmt = $db->prepare("
                    INSERT INTO payments (
                        firm_id, type, payment_number, payment_date, party_id,
                        invoice_id, amount, payment_mode, reference_no, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['payments'] as $p) {
                    $newFirmId = $firmIdMap[$p['firm_id']] ?? null;
                    $newPartyId = $partyIdMap[$p['party_id']] ?? null;
                    if (!$newFirmId || !$newPartyId) continue;

                    $newInvoiceId = (!empty($p['invoice_id']) && isset($invoiceIdMap[$p['invoice_id']])) ? $invoiceIdMap[$p['invoice_id']] : null;

                    $payStmt->execute([
                        $newFirmId, $p['type'] ?? 'payment_in', $p['payment_number'], $p['payment_date'], $newPartyId,
                        $newInvoiceId, (float)($p['amount'] ?? 0), $p['payment_mode'] ?? 'Cash', $p['reference_no'] ?? null, $p['notes'] ?? null
                    ]);
                }
            }

            // 7. Resynchronize document sequences for restored firms
            foreach ($firmIdMap as $oldFId => $newFId) {
                foreach (['sale', 'purchase', 'payment_in', 'payment_out'] as $dType) {
                    $db->prepare("DELETE FROM document_sequences WHERE firm_id = ? AND doc_type = ?")->execute([$newFId, $dType]);
                    Invoice::getOrInitLastSequence($newFId, $dType);
                }
            }

            return true;
        });
    }
}
