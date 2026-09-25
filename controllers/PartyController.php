<?php
/**
 * Party Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Party.php';
require_once __DIR__ . '/../models/Model.php';

class PartyController {
    public function listParties(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $filterType = $_GET['type'] ?? 'all';

        if ($filterType === 'customer' || $filterType === 'supplier') {
            $parties = Party.getByFirmId($firmId, $filterType);
        } else {
            $parties = Party::getByFirmId($firmId);
        }

        $partySummaries = Party::getPartySummary($firmId);
        $summaryMap = [];
        foreach ($partySummaries as $s) {
            $summaryMap[$s['id']] = $s;
        }

        view('parties/list', [
            'title' => 'Parties & Customers',
            'parties' => $parties,
            'summaryMap' => $summaryMap,
            'filterType' => $filterType,
            'activeMenu' => 'parties'
        ]);
    }

    public function getCreate(): void {
        Auth::requireUserOnly();
        Auth::requireActiveFirm();
        $defaultType = $_GET['type'] ?? 'customer';

        view('parties/form', [
            'title' => 'Add New Party (Customer / Supplier)',
            'party' => null,
            'defaultType' => $defaultType,
            'gstStates' => Model::GST_STATES,
            'activeMenu' => 'parties'
        ]);
    }

    public function postCreate(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Party / Customer name is required.');
                header('Location: /parties/create');
                exit;
            }

            $cleanPhone = null;
            $rawPhone = trim($_POST['phone'] ?? '');
            if ($rawPhone !== '') {
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (strlen($cleanPhone) !== 10) {
                    Flash::set('error_msg', "Invalid Phone Number: Mobile number must contain exactly 10 digits (received " . strlen($cleanPhone) . " digits).");
                    header('Location: /parties/create');
                    exit;
                }
            }

            $cleanGstin = null;
            $rawGstin = trim($_POST['gstin'] ?? '');
            if ($rawGstin !== '') {
                $cleanGstin = strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rawGstin));
                if (strlen($cleanGstin) !== 15) {
                    Flash::set('error_msg', "Invalid GSTIN Number: GST number must contain exactly 15 characters (received " . strlen($cleanGstin) . " characters).");
                    header('Location: /parties/create');
                    exit;
                }
            }

            $email = trim($_POST['email'] ?? '');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flash::set('error_msg', 'Invalid Email: Please provide a valid email address.');
                header('Location: /parties/create');
                exit;
            }

            $state = trim($_POST['state'] ?? '');
            $stateCode = trim($_POST['state_code'] ?? '');
            if ($stateCode === '' && $state !== '') {
                foreach (Model::GST_STATES as $s) {
                    if (strcasecmp($s['name'], $state) === 0) {
                        $stateCode = $s['code'];
                        break;
                    }
                }
            }

            Party::create([
                'firm_id' => $firmId,
                'type' => $_POST['type'] ?? 'customer',
                'name' => $name,
                'phone' => $cleanPhone,
                'email' => $email ?: null,
                'gstin' => $cleanGstin,
                'pan' => !empty($_POST['pan']) ? strtoupper(trim($_POST['pan'])) : null,
                'billing_address' => $_POST['billing_address'] ?? null,
                'shipping_address' => $_POST['shipping_address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $state ?: null,
                'state_code' => $stateCode ?: null,
                'pincode' => $_POST['pincode'] ?? null,
                'opening_balance' => Security::parseCleanFloat($_POST['opening_balance'] ?? 0)
            ]);

            Flash::set('success_msg', "Party \"{$name}\" created successfully!");
            header('Location: /parties');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to create party.'));
            header('Location: /parties/create');
            exit;
        }
    }

    public function getEdit(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $partyId = (int)($params['id'] ?? 0);
        $party = Party::getById($partyId, (int)$firm['id']);

        if (!$party) {
            Flash::set('error_msg', 'Party not found.');
            header('Location: /parties');
            exit;
        }

        view('parties/form', [
            'title' => "Edit {$party['name']}",
            'party' => $party,
            'defaultType' => $party['type'],
            'gstStates' => Model::GST_STATES,
            'activeMenu' => 'parties'
        ]);
    }

    public function postEdit(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $partyId = (int)($params['id'] ?? 0);

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Party name is required.');
                header("Location: /parties/edit/{$partyId}");
                exit;
            }

            $cleanPhone = null;
            $rawPhone = trim($_POST['phone'] ?? '');
            if ($rawPhone !== '') {
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (strlen($cleanPhone) !== 10) {
                    Flash::set('error_msg', "Invalid Phone Number: Mobile number must contain exactly 10 digits.");
                    header("Location: /parties/edit/{$partyId}");
                    exit;
                }
            }

            $cleanGstin = null;
            $rawGstin = trim($_POST['gstin'] ?? '');
            if ($rawGstin !== '') {
                $cleanGstin = strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rawGstin));
                if (strlen($cleanGstin) !== 15) {
                    Flash::set('error_msg', "Invalid GSTIN Number: GST number must contain exactly 15 characters.");
                    header("Location: /parties/edit/{$partyId}");
                    exit;
                }
            }

            $email = trim($_POST['email'] ?? '');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flash::set('error_msg', 'Invalid Email: Please provide a valid email address.');
                header("Location: /parties/edit/{$partyId}");
                exit;
            }

            $state = trim($_POST['state'] ?? '');
            $stateCode = trim($_POST['state_code'] ?? '');
            if ($stateCode === '' && $state !== '') {
                foreach (Model::GST_STATES as $s) {
                    if (strcasecmp($s['name'], $state) === 0) {
                        $stateCode = $s['code'];
                        break;
                    }
                }
            }

            Party::update($partyId, $firmId, [
                'type' => $_POST['type'] ?? 'customer',
                'name' => $name,
                'phone' => $cleanPhone,
                'email' => $email ?: null,
                'gstin' => $cleanGstin,
                'pan' => !empty($_POST['pan']) ? strtoupper(trim($_POST['pan'])) : null,
                'billing_address' => $_POST['billing_address'] ?? null,
                'shipping_address' => $_POST['shipping_address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $state ?: null,
                'state_code' => $stateCode ?: null,
                'pincode' => $_POST['pincode'] ?? null,
                'opening_balance' => Security::parseCleanFloat($_POST['opening_balance'] ?? 0)
            ]);

            Flash::set('success_msg', 'Party details updated.');
            header('Location: /parties');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update party.'));
            header("Location: /parties/edit/{$partyId}");
            exit;
        }
    }

    public function getLedger(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $partyId = (int)($params['id'] ?? 0);
        $ledgerData = Party::getLedger($partyId, (int)$firm['id']);

        if (!$ledgerData) {
            Flash::set('error_msg', 'Party not found.');
            header('Location: /parties');
            exit;
        }

        view('parties/ledger', [
            'title' => "Ledger Statement - {$ledgerData['party']['name']}",
            'party' => $ledgerData['party'],
            'opening_balance' => $ledgerData['opening_balance'],
            'total_billed' => $ledgerData['total_billed'],
            'total_paid' => $ledgerData['total_paid'],
            'closing_balance' => $ledgerData['closing_balance'],
            'pending_bills' => $ledgerData['pending_bills'],
            'transactions' => $ledgerData['transactions'],
            'activeMenu' => 'parties'
        ]);
    }

    public function postDelete(array $params): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $partyId = (int)($params['id'] ?? 0);

        try {
            Party::delete($partyId, (int)$firm['id']);
            Flash::set('success_msg', 'Party deleted.');
            header('Location: /parties');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to delete party.'));
            header('Location: /parties');
            exit;
        }
    }

    public function apiGetParties(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $type = $_GET['type'] ?? null;
        $parties = Party::getByFirmId((int)$firm['id'], $type);

        header('Content-Type: application/json');
        echo json_encode($parties);
        exit;
    }

    public function postQuickCreate(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        header('Content-Type: application/json');

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Party / Customer name is required.']);
                exit;
            }

            $cleanPhone = null;
            $rawPhone = trim($_POST['phone'] ?? '');
            if ($rawPhone !== '') {
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (strlen($cleanPhone) !== 10) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid Phone: Mobile number must contain exactly 10 digits.']);
                    exit;
                }
            }

            $cleanGstin = null;
            $rawGstin = trim($_POST['gstin'] ?? '');
            if ($rawGstin !== '') {
                $cleanGstin = strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rawGstin));
                if (strlen($cleanGstin) !== 15) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid GSTIN: GSTIN must be exactly 15 characters long.']);
                    exit;
                }
            }

            $email = trim($_POST['email'] ?? '');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid Email: Please provide a valid email address.']);
                exit;
            }

            $state = trim($_POST['state'] ?? '');
            $stateCode = trim($_POST['state_code'] ?? '');
            if ($stateCode === '' && $state !== '') {
                foreach (Model::GST_STATES as $s) {
                    if (strcasecmp($s['name'], $state) === 0) {
                        $stateCode = $s['code'];
                        break;
                    }
                }
            }
            if ($stateCode === '' && $cleanGstin && strlen($cleanGstin) === 15) {
                $stateCode = substr($cleanGstin, 0, 2);
            }

            $party = Party::create([
                'firm_id' => (int)$firm['id'],
                'type' => $_POST['type'] ?? 'customer',
                'name' => $name,
                'phone' => $cleanPhone,
                'email' => $email ?: null,
                'gstin' => $cleanGstin,
                'pan' => !empty($_POST['pan']) ? strtoupper(trim($_POST['pan'])) : null,
                'billing_address' => $_POST['billing_address'] ?? null,
                'shipping_address' => $_POST['shipping_address'] ?? ($_POST['billing_address'] ?? null),
                'city' => $_POST['city'] ?? null,
                'state' => $state ?: null,
                'state_code' => $stateCode ?: null,
                'pincode' => $_POST['pincode'] ?? null,
                'opening_balance' => Security::parseCleanFloat($_POST['opening_balance'] ?? 0)
            ]);

            echo json_encode([
                'success' => true,
                'party' => $party,
                'message' => "Party \"{$party['name']}\" created successfully!"
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => Security::getSafeErrorMessage($e, 'Failed to create party.')]);
            exit;
        }
    }
}
