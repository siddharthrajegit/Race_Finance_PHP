<?php
/**
 * Firm Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Firm.php';
require_once __DIR__ . '/../models/Model.php';

class FirmController {
    public function listFirms(): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firms = Firm::getByUserId($user['id']);

        view('firms/list', [
            'title' => 'My Businesses & Firms',
            'firms' => $firms,
            'activeMenu' => 'firms',
            'canCreateNewFirm' => count($firms) < 2
        ]);
    }

    public function getCreate(): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $existing = Firm::getByUserId($user['id']);
        if (count($existing) >= 2) {
            Flash::set('error_msg', 'Maximum firm limit reached. A user can only register up to 2 business firms.');
            header('Location: /firms');
            exit;
        }

        view('firms/form', [
            'title' => 'Register New Business Firm',
            'firm' => null,
            'gstStates' => Model::GST_STATES,
            'activeMenu' => 'firms'
        ]);
    }

    public function postCreate(): void {
        Auth::requireUserOnly();
        $user = Auth::user();

        try {
            $existing = Firm::getByUserId($user['id']);
            if (count($existing) >= 2) {
                Flash::set('error_msg', 'Maximum firm limit reached. A user can only register up to 2 business firms.');
                header('Location: /firms');
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Business/Firm name is required.');
                header('Location: /firms/create');
                exit;
            }

            $email = trim($_POST['email'] ?? '');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flash::set('error_msg', 'Invalid Email: Please provide a valid email address.');
                header('Location: /firms/create');
                exit;
            }

            $logoPath = Upload::handle('logo');
            $signaturePath = Upload::handle('signature');

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

            $newFirm = Firm::create([
                'user_id' => $user['id'],
                'name' => $name,
                'gstin' => $_POST['gstin'] ?? null,
                'pan' => $_POST['pan'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'email' => $email ?: null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $state ?: null,
                'state_code' => $stateCode ?: null,
                'pincode' => $_POST['pincode'] ?? null,
                'bank_name' => $_POST['bank_name'] ?? null,
                'bank_account_no' => $_POST['bank_account_no'] ?? null,
                'bank_ifsc' => $_POST['bank_ifsc'] ?? null,
                'bank_branch' => $_POST['bank_branch'] ?? null,
                'upi_id' => $_POST['upi_id'] ?? null,
                'terms' => $_POST['terms'] ?? null,
                'logo_path' => $logoPath,
                'signature_path' => $signaturePath,
                'is_default' => !empty($_POST['is_default']) ? 1 : 0
            ]);

            $_SESSION['activeFirmId'] = $newFirm['id'];
            Flash::set('success_msg', "Firm \"{$newFirm['name']}\" registered successfully!");
            header('Location: /dashboard');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to create business firm. Please try again.'));
            header('Location: /firms/create');
            exit;
        }
    }

    public function getEdit(array $params): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firmId = (int)($params['id'] ?? 0);
        $firm = Firm::getById($firmId, (int)$user['id']);

        if (!$firm) {
            Flash::set('error_msg', 'Firm not found.');
            header('Location: /firms');
            exit;
        }

        view('firms/form', [
            'title' => "Edit {$firm['name']}",
            'firm' => $firm,
            'gstStates' => Model::GST_STATES,
            'activeMenu' => 'firms'
        ]);
    }

    public function postEdit(array $params): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firmId = (int)($params['id'] ?? 0);
        $existing = Firm::getById($firmId, (int)$user['id']);

        if (!$existing) {
            Flash::set('error_msg', 'Firm not found.');
            header('Location: /firms');
            exit;
        }

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                Flash::set('error_msg', 'Business/Firm name is required.');
                header("Location: /firms/edit/{$firmId}");
                exit;
            }

            $email = trim($_POST['email'] ?? '');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flash::set('error_msg', 'Invalid Email: Please provide a valid email address.');
                header("Location: /firms/edit/{$firmId}");
                exit;
            }

            $firmData = [
                'name' => $name,
                'gstin' => $_POST['gstin'] ?? null,
                'pan' => $_POST['pan'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'email' => $email ?: null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'state_code' => $_POST['state_code'] ?? null,
                'pincode' => $_POST['pincode'] ?? null,
                'bank_name' => $_POST['bank_name'] ?? null,
                'bank_account_no' => $_POST['bank_account_no'] ?? null,
                'bank_ifsc' => $_POST['bank_ifsc'] ?? null,
                'bank_branch' => $_POST['bank_branch'] ?? null,
                'upi_id' => $_POST['upi_id'] ?? null,
                'terms' => $_POST['terms'] ?? null,
                'is_default' => !empty($_POST['is_default']) ? 1 : 0
            ];

            $newLogo = Upload::handle('logo');
            if ($newLogo) {
                Security::safeDeleteUpload($existing['logo_path']);
                $firmData['logo_path'] = $newLogo;
            }

            $newSig = Upload::handle('signature');
            if ($newSig) {
                Security::safeDeleteUpload($existing['signature_path']);
                $firmData['signature_path'] = $newSig;
            }

            Firm::update($firmId, (int)$user['id'], $firmData);

            Flash::set('success_msg', 'Firm details updated successfully!');
            header('Location: /firms');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update firm.'));
            header("Location: /firms/edit/{$firmId}");
            exit;
        }
    }

    public function postSwitch(array $params): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firmId = (int)($params['id'] ?? 0);
        $firm = Firm::getById($firmId, (int)$user['id']);

        if (!$firm) {
            Flash::set('error_msg', 'Firm not found.');
            header('Location: /dashboard');
            exit;
        }

        $_SESSION['activeFirmId'] = $firm['id'];
        Flash::set('success_msg', "Switched active firm to \"{$firm['name']}\".");
        $returnUrl = Security::getSafeRefererUrl('/dashboard');
        header("Location: {$returnUrl}");
        exit;
    }

    public function postSetDefault(array $params): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firmId = (int)($params['id'] ?? 0);
        $firm = Firm::getById($firmId, (int)$user['id']);

        if ($firm) {
            Firm::setDefault($firmId, (int)$user['id']);
            Flash::set('success_msg', "\"{$firm['name']}\" is now your default business firm.");
        }
        header('Location: /firms');
        exit;
    }

    public function postDelete(array $params): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firmId = (int)($params['id'] ?? 0);
        $firms = Firm::getByUserId((int)$user['id']);

        if (count($firms) <= 1) {
            Flash::set('error_msg', 'You must have at least one business firm.');
            header('Location: /firms');
            exit;
        }

        foreach ($firms as $f) {
            if ((int)$f['id'] === $firmId) {
                Security::safeDeleteUpload($f['logo_path']);
                Security::safeDeleteUpload($f['signature_path']);
                break;
            }
        }

        Firm::delete($firmId, (int)$user['id']);

        if (($_SESSION['activeFirmId'] ?? null) == $firmId) {
            unset($_SESSION['activeFirmId']);
        }

        Flash::set('success_msg', 'Business firm and associated records deleted.');
        header('Location: /firms');
        exit;
    }
}
