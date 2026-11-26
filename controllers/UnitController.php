<?php
/**
 * Unit Controller
 * Manages User-Scoped Custom Units
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/CustomUnit.php';

class UnitController {
    /**
     * JSON API: Get all units available to the current user (system + custom)
     */
    public function apiGetUnits(): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $firm = Auth::getActiveFirm();
        $ownerId = ($firm && !empty($firm['user_id'])) ? (int)$firm['user_id'] : (int)$user['id'];
        header('Content-Type: application/json; charset=utf-8');

        try {
            $units = CustomUnit::getAllForUser($ownerId);
            echo json_encode([
                'success' => true,
                'units' => $units
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => Security::getSafeErrorMessage($e, 'Failed to fetch units.')
            ]);
            exit;
        }
    }

    /**
     * JSON API: Create a new custom unit for current user
     */
    public function postCreate(): void {
        RBAC::requirePermission('manage_items', true);
        $user = Auth::user();
        $firm = Auth::getActiveFirm();
        $ownerId = ($firm && !empty($firm['user_id'])) ? (int)$firm['user_id'] : (int)$user['id'];
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = $_POST;
            if (empty($input['unit_code']) && empty($input['code'])) {
                $rawInput = file_get_contents('php://input');
                if (!empty($rawInput)) {
                    $jsonInput = json_decode($rawInput, true);
                    if (is_array($jsonInput)) {
                        $input = array_merge($input, $jsonInput);
                    }
                }
            }

            $code = trim($input['unit_code'] ?? $input['code'] ?? '');
            $label = trim($input['unit_label'] ?? $input['label'] ?? '');

            if ($code === '') {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Unit code is required (e.g. PAIR, SET, CAN).'
                ]);
                exit;
            }

            $unit = CustomUnit::create($ownerId, $code, $label);

            echo json_encode([
                'success' => true,
                'unit' => $unit,
                'message' => "Unit \"{$unit['code']}\" added successfully!"
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => Security::getSafeErrorMessage($e, 'Failed to add custom unit.')
            ]);
            exit;
        }
    }

    /**
     * JSON API: Delete a custom unit owned by current user
     */
    public function postDelete(array $params): void {
        RBAC::requirePermission('manage_items', true);
        $user = Auth::user();
        $firm = Auth::getActiveFirm();
        $ownerId = ($firm && !empty($firm['user_id'])) ? (int)$firm['user_id'] : (int)$user['id'];
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = (int)($params['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid unit ID.']);
                exit;
            }

            $deleted = CustomUnit::delete($id, $ownerId);
            echo json_encode([
                'success' => $deleted,
                'message' => $deleted ? 'Custom unit deleted successfully.' : 'Unit not found or unauthorized.'
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => Security::getSafeErrorMessage($e, 'Failed to delete unit.')
            ]);
            exit;
        }
    }
}
