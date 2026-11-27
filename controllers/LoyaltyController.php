<?php
/**
 * Customer Loyalty Points Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Loyalty.php';
require_once __DIR__ . '/../models/Party.php';
require_once __DIR__ . '/../models/AuditLog.php';

class LoyaltyController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_LOYALTY') || !ENABLE_LOYALTY) {
            Flash::set('info_msg', 'Customer loyalty module is disabled in configuration.');
            header('Location: /parties');
            exit;
        }
    }

    public function index(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_loyalty');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $program = Loyalty::getProgram($firmId);
        $customers = Party::getByFirmId($firmId, 'customer');
        $transactions = Loyalty::getTransactions($firmId, null, 100);

        view('loyalty/index', [
            'title' => 'Customer Loyalty Rewards',
            'program' => $program,
            'customers' => $customers,
            'transactions' => $transactions,
            'activeMenu' => 'loyalty'
        ]);
    }

    public function postSaveProgram(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_loyalty');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            Loyalty::updateProgram($firmId, $_POST);
            AuditLog::log($firmId, 'UPDATE', 'loyalty_program', $firmId, 'Updated loyalty rules');
            Flash::set('success_msg', 'Loyalty program rules updated!');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save loyalty settings.'));
        }
        header('Location: /loyalty');
        exit;
    }

    public function postAdjustPoints(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_loyalty');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $user = Auth::user();

        try {
            $partyId = (int)($_POST['party_id'] ?? 0);
            $party = Party::getById($partyId, $firmId);
            if (!$party) {
                throw new Exception('Customer party not found.');
            }
            $action = ($_POST['action'] ?? 'add') === 'deduct' ? -1 : 1;
            $pts = abs(Security::parseCleanFloat($_POST['points'] ?? 0));
            if ($pts <= 0) {
                throw new Exception('Points quantity must be greater than 0.');
            }
            $notes = trim((string)($_POST['notes'] ?? '')) ?: 'Manual adjustment';
            Loyalty::adjustPoints($firmId, $partyId, $action * $pts, 'adjust', null, $notes, $user ? (int)$user['id'] : null);
            AuditLog::log($firmId, 'ADJUST_LOYALTY', 'party', $partyId, "Adjusted loyalty points by " . ($action * $pts));
            Flash::set('success_msg', 'Customer loyalty points updated!');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update points.'));
        }
        header('Location: /loyalty');
        exit;
    }
}
