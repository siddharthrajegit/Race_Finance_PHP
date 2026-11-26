<?php
/**
 * Staff, Role-Based Access Control (RBAC), Dedicated CA Access & Audit Log Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Firm.php';
require_once __DIR__ . '/../models/StaffAccount.php';
require_once __DIR__ . '/../models/AuditLog.php';

class StaffController {
    public function listStaff(): void {
        RBAC::requireOwnerAccount();
        $firm = Auth::requireActiveFirm();
        $owner = Auth::user();

        if (!defined('ENABLE_RBAC') || !ENABLE_RBAC) {
            Flash::set('info_msg', 'Staff & RBAC module is disabled in configuration.');
            header('Location: /dashboard');
            exit;
        }

        $staffList = StaffAccount::getByOwner((int)$owner['id']);

        view('staff/list', [
            'title' => 'Staff & CA Team Access',
            'staffList' => $staffList,
            'roles' => RBAC::ROLES,
            'activeMenu' => 'staff'
        ]);
    }

    public function getCreate(): void {
        RBAC::requireOwnerAccount();
        Auth::requireActiveFirm();
        $owner = Auth::user();

        $ownerFirms = Firm::getByUserId((int)$owner['id']);

        view('staff/form', [
            'title' => 'Add Staff / CA Member',
            'staff' => null,
            'ownerFirms' => $ownerFirms,
            'roles' => array_filter(RBAC::ROLES, fn($k) => $k !== 'owner', ARRAY_FILTER_USE_KEY),
            'roleDefaults' => RBAC::ROLE_DEFAULTS,
            'permissionGroups' => RBAC::PERMISSION_GROUPS,
            'activeMenu' => 'staff'
        ]);
    }

    public function postCreate(): void {
        RBAC::requireOwnerAccount();
        $firm = Auth::requireActiveFirm();
        $owner = Auth::user();

        try {
            $firmIds = $_POST['firm_ids'] ?? [];
            $role = $_POST['role'] ?? 'cashier';
            $useCustom = !empty($_POST['use_custom_permissions']);
            $customPerms = null;
            if ($useCustom) {
                $posted = is_array($_POST['permissions'] ?? null) ? $_POST['permissions'] : [];
                $customPerms = array_values(array_intersect($posted, RBAC::allPermissionKeys()));
            }

            $created = StaffAccount::createStaffUser(
                (int)$owner['id'],
                [
                    'name' => $_POST['name'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? ''
                ],
                is_array($firmIds) ? $firmIds : [],
                $role,
                $customPerms,
                $_POST['notes'] ?? null
            );

            AuditLog::log((int)$firm['id'], 'CREATE', 'staff_account', $created['user_id'] ?? null, "Created staff account {$created['name']} ({$role})");
            Flash::set('success_msg', "Staff login created for {$created['name']} ({$role}).");
            header('Location: /staff');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to create staff account.'));
            header('Location: /staff/create');
            exit;
        }
    }

    public function getEdit(array $params): void {
        RBAC::requireOwnerAccount();
        Auth::requireActiveFirm();
        $owner = Auth::user();
        $id = (int)($params['id'] ?? 0);

        $staff = StaffAccount::getStaffDetailForOwner((int)$owner['id'], $id);
        if (!$staff) {
            Flash::set('error_msg', 'Staff account not found.');
            header('Location: /staff');
            exit;
        }

        $ownerFirms = Firm::getByUserId((int)$owner['id']);

        view('staff/form', [
            'title' => 'Edit Staff / CA Permissions',
            'staff' => $staff,
            'ownerFirms' => $ownerFirms,
            'roles' => array_filter(RBAC::ROLES, fn($k) => $k !== 'owner', ARRAY_FILTER_USE_KEY),
            'roleDefaults' => RBAC::ROLE_DEFAULTS,
            'permissionGroups' => RBAC::PERMISSION_GROUPS,
            'activeMenu' => 'staff'
        ]);
    }

    public function postEdit(array $params): void {
        RBAC::requireOwnerAccount();
        $firm = Auth::requireActiveFirm();
        $owner = Auth::user();
        $id = (int)($params['id'] ?? 0);

        try {
            $firmIds = $_POST['firm_ids'] ?? [];
            $role = $_POST['role'] ?? 'cashier';
            $useCustom = !empty($_POST['use_custom_permissions']);
            $customPerms = null;
            if ($useCustom) {
                $posted = is_array($_POST['permissions'] ?? null) ? $_POST['permissions'] : [];
                $customPerms = array_values(array_intersect($posted, RBAC::allPermissionKeys()));
            }

            $updated = StaffAccount::updateStaffUser(
                (int)$owner['id'],
                $id,
                [
                    'name' => $_POST['name'] ?? '',
                    'new_password' => $_POST['new_password'] ?? '',
                    'is_active' => !empty($_POST['is_active']),
                    'notes' => $_POST['notes'] ?? null
                ],
                is_array($firmIds) ? $firmIds : [],
                $role,
                $customPerms
            );

            if (!$updated) {
                Flash::set('error_msg', 'Staff account not found.');
                header('Location: /staff');
                exit;
            }

            AuditLog::log((int)$firm['id'], 'UPDATE', 'staff_account', $id, "Updated staff {$updated['name']} role={$role} active=" . ($updated['is_active'] ? 'yes' : 'no'));
            Flash::set('success_msg', "Permissions updated for {$updated['name']}.");
            header('Location: /staff');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update staff account.'));
            header("Location: /staff/edit/{$id}");
            exit;
        }
    }

    public function postDelete(array $params): void {
        RBAC::requireOwnerAccount();
        $firm = Auth::requireActiveFirm();
        $owner = Auth::user();
        $id = (int)($params['id'] ?? 0);

        try {
            $detail = StaffAccount::getStaffDetailForOwner((int)$owner['id'], $id);
            if (StaffAccount::deleteStaffUser((int)$owner['id'], $id)) {
                AuditLog::log((int)$firm['id'], 'DELETE', 'staff_account', $id, "Removed staff " . ($detail['name'] ?? $id));
                Flash::set('success_msg', 'Staff access revoked and removed.');
            } else {
                Flash::set('error_msg', 'Staff account not found.');
            }
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to remove staff account.'));
        }
        header('Location: /staff');
        exit;
    }

    public function listAuditLogs(): void {
        if (!defined('ENABLE_AUDIT_LOGS') || !ENABLE_AUDIT_LOGS) {
            Flash::set('info_msg', 'Audit log viewer is disabled in configuration.');
            header('Location: /dashboard');
            exit;
        }

        RBAC::requirePermission('view_audit_logs');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $filters = [
            'action' => trim($_GET['action'] ?? ''),
            'from_date' => trim($_GET['from_date'] ?? ''),
            'to_date' => trim($_GET['to_date'] ?? '')
        ];

        $logs = AuditLog::getByFirmId($firmId, $filters, 200);
        $totalCount = AuditLog::countByFirmId($firmId, $filters);

        view('staff/audit_logs', [
            'title' => 'Activity Audit Logs',
            'logs' => $logs,
            'totalCount' => $totalCount,
            'filters' => $filters,
            'activeMenu' => 'audit_logs'
        ]);
    }
}
