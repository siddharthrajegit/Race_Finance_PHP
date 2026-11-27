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

    private static function fetchOptionalFirmRows(string $table, array $firmIds): array {
        if (empty($firmIds)) return [];
        try {
            $ph = implode(',', array_fill(0, count($firmIds), '?'));
            $stmt = self::db()->prepare("SELECT * FROM {$table} WHERE firm_id IN ($ph)");
            $stmt->execute($firmIds);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function exportFullBackup(int $userId): ?array {
        $user = User::findById($userId);
        if (!$user) return null;

        $firms = Firm::getByUserId($userId);
        $firmIds = array_map(fn($f) => (int)$f['id'], $firms);

        $parties = self::fetchOptionalFirmRows('parties', $firmIds);
        $items = self::fetchOptionalFirmRows('items', $firmIds);
        $invoices = self::fetchOptionalFirmRows('invoices', $firmIds);
        $payments = self::fetchOptionalFirmRows('payments', $firmIds);
        $invoiceItems = [];
        $stockTransferItems = [];
        $bomComponents = [];

        if (!empty($invoices)) {
            $invoiceIds = array_map(fn($inv) => (int)$inv['id'], $invoices);
            $invPh = implode(',', array_fill(0, count($invoiceIds), '?'));
            $iiStmt = self::db()->prepare("SELECT * FROM invoice_items WHERE invoice_id IN ($invPh)");
            $iiStmt->execute($invoiceIds);
            $invoiceItems = $iiStmt->fetchAll();
        }

        $stockTransfers = self::fetchOptionalFirmRows('stock_transfers', $firmIds);
        if (!empty($stockTransfers)) {
            try {
                $tIds = array_map(fn($st) => (int)$st['id'], $stockTransfers);
                $tPh = implode(',', array_fill(0, count($tIds), '?'));
                $stStmt = self::db()->prepare("SELECT * FROM stock_transfer_items WHERE transfer_id IN ($tPh)");
                $stStmt->execute($tIds);
                $stockTransferItems = $stStmt->fetchAll();
            } catch (Throwable $e) {}
        }

        $boms = self::fetchOptionalFirmRows('boms', $firmIds);
        if (!empty($boms)) {
            try {
                $bIds = array_map(fn($b) => (int)$b['id'], $boms);
                $bPh = implode(',', array_fill(0, count($bIds), '?'));
                $bcStmt = self::db()->prepare("SELECT * FROM bom_components WHERE bom_id IN ($bPh)");
                $bcStmt->execute($bIds);
                $bomComponents = $bcStmt->fetchAll();
            } catch (Throwable $e) {}
        }

        return [
            'version' => '2.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone']
            ],
            'firms' => $firms,
            'firm_settings' => self::fetchOptionalFirmRows('firm_settings', $firmIds),
            'parties' => $parties,
            'items' => $items,
            'invoices' => $invoices,
            'invoice_items' => $invoiceItems,
            'payments' => $payments,
            'warehouses' => self::fetchOptionalFirmRows('warehouses', $firmIds),
            'warehouse_stock' => self::fetchOptionalFirmRows('warehouse_stock', $firmIds),
            'stock_transfers' => $stockTransfers,
            'stock_transfer_items' => $stockTransferItems,
            'stock_movements' => self::fetchOptionalFirmRows('stock_movements', $firmIds),
            'expense_categories' => self::fetchOptionalFirmRows('expense_categories', $firmIds),
            'expenses' => self::fetchOptionalFirmRows('expenses', $firmIds),
            'chart_of_accounts' => self::fetchOptionalFirmRows('chart_of_accounts', $firmIds),
            'journal_entries' => self::fetchOptionalFirmRows('journal_entries', $firmIds),
            'journal_lines' => self::fetchOptionalFirmRows('journal_lines', $firmIds),
            'bank_statement_lines' => self::fetchOptionalFirmRows('bank_statement_lines', $firmIds),
            'item_batches' => self::fetchOptionalFirmRows('item_batches', $firmIds),
            'item_serials' => self::fetchOptionalFirmRows('item_serials', $firmIds),
            'boms' => $boms,
            'bom_components' => $bomComponents,
            'production_orders' => self::fetchOptionalFirmRows('production_orders', $firmIds),
            'loyalty_programs' => self::fetchOptionalFirmRows('loyalty_programs', $firmIds),
            'loyalty_transactions' => self::fetchOptionalFirmRows('loyalty_transactions', $firmIds),
            'catalogue_settings' => self::fetchOptionalFirmRows('catalogue_settings', $firmIds)
        ];
    }

    public static function restoreFullBackup(int $userId, array $backupData): bool {
        return self::transaction(function($db) use ($userId, $backupData) {
            $firmIdMap = [];
            $partyIdMap = [];
            $itemIdMap = [];
            $invoiceIdMap = [];
            $warehouseIdMap = [];
            $coaIdMap = [];
            $jeIdMap = [];
            $bomIdMap = [];

            // 1. Firms
            if (!empty($backupData['firms'])) {
                $fStmt = $db->prepare("
                    INSERT INTO firms (
                        user_id, name, gstin, pan, phone, email, address, city, state, state_code,
                        pincode, bank_name, bank_account_no, bank_ifsc, bank_branch, upi_id, terms,
                        logo_path, signature_path, qr_code_path, is_default
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['firms'] as $f) {
                    $fStmt->execute([
                        $userId, $f['name'], $f['gstin'] ?? null, $f['pan'] ?? null, $f['phone'] ?? null, $f['email'] ?? null,
                        $f['address'] ?? null, $f['city'] ?? null, $f['state'] ?? null, $f['state_code'] ?? null, $f['pincode'] ?? null,
                        $f['bank_name'] ?? null, $f['bank_account_no'] ?? null, $f['bank_ifsc'] ?? null, $f['bank_branch'] ?? null,
                        $f['upi_id'] ?? null, $f['terms'] ?? null, $f['logo_path'] ?? null, $f['signature_path'] ?? null, $f['qr_code_path'] ?? null, $f['is_default'] ?? 0
                    ]);
                    $firmIdMap[$f['id']] = (int)$db->lastInsertId();
                }
            }

            // 1b. Firm Settings
            if (!empty($backupData['firm_settings'])) {
                $fsStmt = $db->prepare("INSERT INTO firm_settings (firm_id, section, settings_json) VALUES (?, ?, ?)");
                foreach ($backupData['firm_settings'] as $fs) {
                    $newFirmId = $firmIdMap[$fs['firm_id']] ?? null;
                    if (!$newFirmId) continue;
                    $fsStmt->execute([$newFirmId, $fs['section'] ?? 'general', $fs['settings_json'] ?? '{}']);
                }
            }

            // 2. Parties (with optional loyalty_points from v2.0)
            if (!empty($backupData['parties'])) {
                $pStmt = $db->prepare("
                    INSERT INTO parties (
                        firm_id, type, name, phone, email, gstin, pan, billing_address,
                        shipping_address, city, state, state_code, pincode, opening_balance, loyalty_points
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['parties'] as $p) {
                    $newFirmId = $firmIdMap[$p['firm_id']] ?? null;
                    if (!$newFirmId) continue;

                    $pStmt->execute([
                        $newFirmId, $p['type'] ?? 'customer', $p['name'], $p['phone'] ?? null, $p['email'] ?? null,
                        $p['gstin'] ?? null, $p['pan'] ?? null, $p['billing_address'] ?? null, $p['shipping_address'] ?? null,
                        $p['city'] ?? null, $p['state'] ?? null, $p['state_code'] ?? null, $p['pincode'] ?? null,
                        (float)($p['opening_balance'] ?? 0), (float)($p['loyalty_points'] ?? 0)
                    ]);
                    $partyIdMap[$p['id']] = (int)$db->lastInsertId();
                }
            }

            // 3. Items (with category, sub_unit, tracking_type)
            if (!empty($backupData['items'])) {
                $itStmt = $db->prepare("
                    INSERT INTO items (
                        firm_id, name, category, item_code, hsn_code, unit, sub_unit, sub_unit_qty,
                        sale_price, purchase_price, tax_rate, tax_inclusive, opening_stock, current_stock,
                        low_stock_threshold, description, tracking_type
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['items'] as $it) {
                    $newFirmId = $firmIdMap[$it['firm_id']] ?? null;
                    if (!$newFirmId) continue;

                    $itStmt->execute([
                        $newFirmId, $it['name'], $it['category'] ?? null, $it['item_code'] ?? null, $it['hsn_code'] ?? null,
                        $it['unit'] ?? 'PCS', $it['sub_unit'] ?? null, $it['sub_unit_qty'] ?? null,
                        (float)($it['sale_price'] ?? 0), (float)($it['purchase_price'] ?? 0), (float)($it['tax_rate'] ?? 0),
                        !empty($it['tax_inclusive']) ? 1 : 0, (float)($it['opening_stock'] ?? 0), (float)($it['current_stock'] ?? 0),
                        (float)($it['low_stock_threshold'] ?? 5), $it['description'] ?? null, $it['tracking_type'] ?? 'none'
                    ]);
                    $itemIdMap[$it['id']] = (int)$db->lastInsertId();
                }
            }

            // 3b. Warehouses (before invoices so invoices.warehouse_id remaps cleanly)
            if (!empty($backupData['warehouses'])) {
                $wStmt = $db->prepare("
                    INSERT INTO warehouses (firm_id, name, code, address, is_default, is_active)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                foreach ($backupData['warehouses'] as $w) {
                    $newFirmId = $firmIdMap[$w['firm_id']] ?? null;
                    if (!$newFirmId) continue;
                    $wStmt->execute([
                        $newFirmId, $w['name'], $w['code'] ?? null, $w['address'] ?? null,
                        !empty($w['is_default']) ? 1 : 0, array_key_exists('is_active', $w) ? (int)$w['is_active'] : 1
                    ]);
                    $warehouseIdMap[$w['id']] = (int)$db->lastInsertId();
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
                        grand_total, paid_amount, balance_due, payment_status, payment_mode, notes, terms, warehouse_id, client_uuid
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($backupData['invoices'] as $inv) {
                    $newFirmId = $firmIdMap[$inv['firm_id']] ?? null;
                    if (!$newFirmId) continue;

                    $newPartyId = (!empty($inv['party_id']) && isset($partyIdMap[$inv['party_id']])) ? $partyIdMap[$inv['party_id']] : null;
                    $newWhId = (!empty($inv['warehouse_id']) && isset($warehouseIdMap[$inv['warehouse_id']])) ? $warehouseIdMap[$inv['warehouse_id']] : null;

                    $invStmt->execute([
                        $newFirmId, $inv['type'] ?? 'sale', $inv['invoice_number'], $inv['invoice_date'], $inv['due_date'] ?? null,
                        $newPartyId, $inv['party_name'] ?? 'Walk-in Customer', $inv['party_phone'] ?? null, $inv['party_gstin'] ?? null,
                        $inv['party_address'] ?? null, $inv['party_state'] ?? null, $inv['party_state_code'] ?? null,
                        !empty($inv['is_gst_bill']) ? 1 : 0, !empty($inv['is_interstate']) ? 1 : 0, (float)($inv['subtotal'] ?? 0), $inv['discount_type'] ?? 'fixed',
                        (float)($inv['discount_value'] ?? 0), (float)($inv['discount_amount'] ?? 0), (float)($inv['taxable_amount'] ?? 0),
                        (float)($inv['cgst_amount'] ?? 0), (float)($inv['sgst_amount'] ?? 0), (float)($inv['igst_amount'] ?? 0), (float)($inv['tax_amount'] ?? 0),
                        (float)($inv['round_off'] ?? 0), (float)($inv['grand_total'] ?? 0), (float)($inv['paid_amount'] ?? 0), (float)($inv['balance_due'] ?? 0),
                        $inv['payment_status'] ?? 'unpaid', $inv['payment_mode'] ?? 'Cash', $inv['notes'] ?? null, $inv['terms'] ?? null,
                        $newWhId, $inv['client_uuid'] ?? null
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

            // 6b. V2.0 Extended Modules (Warehouse Stock, Expenses, Accounting, Batches, Serials, BOM, Loyalty, Catalogue)
            if (!empty($backupData['warehouse_stock'])) {
                $wsStmt = $db->prepare("INSERT INTO warehouse_stock (firm_id, warehouse_id, item_id, quantity) VALUES (?, ?, ?, ?)");
                foreach ($backupData['warehouse_stock'] as $ws) {
                    $f = $firmIdMap[$ws['firm_id']] ?? null;
                    $w = $warehouseIdMap[$ws['warehouse_id']] ?? null;
                    $it = $itemIdMap[$ws['item_id']] ?? null;
                    if ($f && $w && $it) $wsStmt->execute([$f, $w, $it, (float)$ws['quantity']]);
                }
            }
            if (!empty($backupData['expense_categories'])) {
                $ecStmt = $db->prepare("INSERT INTO expense_categories (firm_id, name, account_code, is_default) VALUES (?, ?, ?, ?)");
                foreach ($backupData['expense_categories'] as $ec) {
                    $f = $firmIdMap[$ec['firm_id']] ?? null;
                    if ($f) $ecStmt->execute([$f, $ec['name'], $ec['account_code'] ?? '5900', !empty($ec['is_default']) ? 1 : 0]);
                }
            }
            if (!empty($backupData['expenses'])) {
                $exStmt = $db->prepare("INSERT INTO expenses (firm_id, expense_number, expense_date, category, payee, amount, payment_mode, reference_no, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($backupData['expenses'] as $ex) {
                    $f = $firmIdMap[$ex['firm_id']] ?? null;
                    if ($f) $exStmt->execute([$f, $ex['expense_number'], $ex['expense_date'], $ex['category'], $ex['payee'] ?? null, (float)$ex['amount'], $ex['payment_mode'] ?? 'cash', $ex['reference_no'] ?? null, $ex['notes'] ?? null]);
                }
            }
            if (!empty($backupData['chart_of_accounts'])) {
                $coaStmt = $db->prepare("INSERT INTO chart_of_accounts (firm_id, code, name, account_type, parent_code, opening_balance, opening_side, is_system, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($backupData['chart_of_accounts'] as $ca) {
                    $f = $firmIdMap[$ca['firm_id']] ?? null;
                    if (!$f) continue;
                    $coaStmt->execute([$f, $ca['code'], $ca['name'], $ca['account_type'], $ca['parent_code'] ?? null, (float)($ca['opening_balance'] ?? 0), $ca['opening_side'] ?? 'debit', !empty($ca['is_system']) ? 1 : 0, !empty($ca['is_active']) ? 1 : 0]);
                    $coaIdMap[$ca['id']] = (int)$db->lastInsertId();
                }
            }
            if (!empty($backupData['journal_entries'])) {
                $jeStmt = $db->prepare("INSERT INTO journal_entries (firm_id, entry_number, entry_date, source_type, source_id, reference_no, narration, total_debit, total_credit, is_reversed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($backupData['journal_entries'] as $je) {
                    $f = $firmIdMap[$je['firm_id']] ?? null;
                    if (!$f) continue;
                    $jeStmt->execute([$f, $je['entry_number'], $je['entry_date'], $je['source_type'], $je['source_id'] ?? null, $je['reference_no'] ?? null, $je['narration'] ?? null, (float)$je['total_debit'], (float)$je['total_credit'], !empty($je['is_reversed']) ? 1 : 0]);
                    $jeIdMap[$je['id']] = (int)$db->lastInsertId();
                }
            }
            if (!empty($backupData['journal_lines'])) {
                $jlStmt = $db->prepare("INSERT INTO journal_lines (journal_entry_id, firm_id, account_id, description, debit, credit, party_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($backupData['journal_lines'] as $jl) {
                    $f = $firmIdMap[$jl['firm_id']] ?? null;
                    $j = $jeIdMap[$jl['journal_entry_id']] ?? null;
                    $a = $coaIdMap[$jl['account_id']] ?? null;
                    if (!$f || !$j || !$a) continue;
                    $p = (!empty($jl['party_id']) && isset($partyIdMap[$jl['party_id']])) ? $partyIdMap[$jl['party_id']] : null;
                    $jlStmt->execute([$j, $f, $a, $jl['description'] ?? null, (float)$jl['debit'], (float)$jl['credit'], $p]);
                }
            }
            if (!empty($backupData['item_batches'])) {
                $ibStmt = $db->prepare("INSERT INTO item_batches (firm_id, item_id, warehouse_id, batch_number, mfg_date, expiry_date, purchase_rate, initial_qty, remaining_qty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($backupData['item_batches'] as $ib) {
                    $f = $firmIdMap[$ib['firm_id']] ?? null;
                    $it = $itemIdMap[$ib['item_id']] ?? null;
                    if (!$f || !$it) continue;
                    $w = (!empty($ib['warehouse_id']) && isset($warehouseIdMap[$ib['warehouse_id']])) ? $warehouseIdMap[$ib['warehouse_id']] : null;
                    $ibStmt->execute([$f, $it, $w, $ib['batch_number'], $ib['mfg_date'] ?? null, $ib['expiry_date'] ?? null, (float)$ib['purchase_rate'], (float)$ib['initial_qty'], (float)$ib['remaining_qty']]);
                }
            }
            if (!empty($backupData['item_serials'])) {
                $isStmt = $db->prepare("INSERT INTO item_serials (firm_id, item_id, warehouse_id, serial_number, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($backupData['item_serials'] as $sn) {
                    $f = $firmIdMap[$sn['firm_id']] ?? null;
                    $it = $itemIdMap[$sn['item_id']] ?? null;
                    if (!$f || !$it) continue;
                    $w = (!empty($sn['warehouse_id']) && isset($warehouseIdMap[$sn['warehouse_id']])) ? $warehouseIdMap[$sn['warehouse_id']] : null;
                    $isStmt->execute([$f, $it, $w, $sn['serial_number'], $sn['status'] ?? 'in_stock', $sn['notes'] ?? null]);
                }
            }
            if (!empty($backupData['boms'])) {
                $bStmt = $db->prepare("INSERT INTO boms (firm_id, finished_item_id, bom_name, output_qty, notes, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($backupData['boms'] as $bm) {
                    $f = $firmIdMap[$bm['firm_id']] ?? null;
                    $it = $itemIdMap[$bm['finished_item_id']] ?? null;
                    if (!$f || !$it) continue;
                    $bStmt->execute([$f, $it, $bm['bom_name'], (float)$bm['output_qty'], $bm['notes'] ?? null, !empty($bm['is_active']) ? 1 : 0]);
                    $bomIdMap[$bm['id']] = (int)$db->lastInsertId();
                }
            }
            if (!empty($backupData['bom_components'])) {
                $bcStmt = $db->prepare("INSERT INTO bom_components (bom_id, component_item_id, quantity_per_unit, unit) VALUES (?, ?, ?, ?)");
                foreach ($backupData['bom_components'] as $bc) {
                    $b = $bomIdMap[$bc['bom_id']] ?? null;
                    $it = $itemIdMap[$bc['component_item_id']] ?? null;
                    if ($b && $it) $bcStmt->execute([$b, $it, (float)$bc['quantity_per_unit'], $bc['unit'] ?? 'PCS']);
                }
            }
            if (!empty($backupData['loyalty_programs'])) {
                $lpStmt = $db->prepare("INSERT INTO loyalty_programs (firm_id, is_enabled, program_name, points_per_currency, currency_unit, point_value, min_redeem_points, max_redeem_pct) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($backupData['loyalty_programs'] as $lp) {
                    $f = $firmIdMap[$lp['firm_id']] ?? null;
                    if ($f) $lpStmt->execute([$f, !empty($lp['is_enabled']) ? 1 : 0, $lp['program_name'], (float)$lp['points_per_currency'], (float)$lp['currency_unit'], (float)$lp['point_value'], (float)$lp['min_redeem_points'], (float)$lp['max_redeem_pct']]);
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
