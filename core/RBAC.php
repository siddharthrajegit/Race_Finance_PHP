<?php
/**
 * Role-Based Access Control (RBAC) & Dedicated CA / Staff Permission Engine
 * Enforces permissions strictly on server-side PHP endpoints and filters UI menus.
 */

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Flash.php';
require_once __DIR__ . '/../models/StaffAccount.php';

class RBAC {
    public const ROLES = [
        'owner'       => 'Business Owner (Full Access)',
        'manager'     => 'Store / Operations Manager',
        'cashier'     => 'Billing Cashier / Counter Staff',
        'storekeeper' => 'Storekeeper / Inventory Staff',
        'accountant'  => 'Accountant / Dedicated CA'
    ];

    public const PERMISSION_GROUPS = [
        'Dashboard & Core' => [
            'view_dashboard' => 'View Dashboard & KPIs'
        ],
        'Sales & Quotations' => [
            'view_sales'     => 'View Sales Records',
            'create_sales'   => 'Create Sales Records',
            'edit_sales'     => 'Edit Sales Records',
            'delete_sales'   => 'Delete Sales Records',
            'view_estimates' => 'View Estimates & Rate Lists',
            'manage_estimates' => 'Create & Delete Estimates'
        ],
        'Purchases' => [
            'view_purchases'   => 'View Purchase Bills',
            'create_purchases' => 'Create Purchase Bills',
            'edit_purchases'   => 'Edit Purchase Bills',
            'delete_purchases' => 'Delete Purchase Bills'
        ],
        'Inventory & Multi-Warehouse' => [
            'view_items'        => 'View Items & Stock Levels',
            'manage_items'      => 'Add & Edit Items / Units / Barcodes',
            'adjust_stock'      => 'Manual Stock Adjustments & Valuation',
            'view_warehouses'   => 'View Warehouses & Godowns',
            'manage_warehouses' => 'Create / Edit Warehouses',
            'transfer_stock'    => 'Execute Inter-Warehouse Stock Transfers',
            'manage_bom'        => 'Manufacturing / Bill of Materials (BOM)'
        ],
        'Parties & Payments' => [
            'view_parties'    => 'View Customers, Suppliers & Ledgers',
            'manage_parties'  => 'Create & Edit Parties',
            'view_payments'   => 'View Payment Receipts & Vouchers',
            'create_payments' => 'Record Payments (In / Out)',
            'delete_payments' => 'Delete Payment Records',
            'manage_loyalty'  => 'Manage Customer Loyalty Points'
        ],
        'Expenses & Double-Entry Accounting' => [
            'view_expenses'     => 'View Expenses',
            'manage_expenses'   => 'Create, Edit & Delete Expenses',
            'view_accounting'   => 'View Chart of Accounts, Journals, Trial Balance, P&L, Balance Sheet & Cash Flow',
            'manage_accounting' => 'Post Manual Journals & Bank Reconciliation'
        ],
        'Reports, Storefront & Compliance' => [
            'view_reports'     => 'View Tax, Party, Item & GSTR / Tally Exports',
            'manage_catalogue' => 'Manage Online Product Catalogue'
        ],
        'Governance, Staff & Backup' => [
            'view_audit_logs' => 'View Activity Audit Logs',
            'manage_staff'    => 'Manage Staff & CA Accounts',
            'manage_settings' => 'Modify Firm & Tax Settings',
            'manage_firms'    => 'Create / Edit / Delete Business Firms',
            'manage_backup'   => 'Export & Restore JSON Backups / Cloud Sync'
        ]
    ];

    public const ROLE_DEFAULTS = [
        'owner' => ['*'],
        'manager' => [
            'view_dashboard',
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            'view_estimates', 'manage_estimates',
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            'view_items', 'manage_items', 'adjust_stock',
            'view_warehouses', 'manage_warehouses', 'transfer_stock', 'manage_bom',
            'view_parties', 'manage_parties',
            'view_payments', 'create_payments',
            'view_expenses', 'manage_expenses',
            'manage_loyalty', 'manage_catalogue',
            'view_reports'
        ],
        'cashier' => [
            'view_dashboard',
            'view_sales', 'create_sales', 'edit_sales',
            'view_estimates', 'manage_estimates',
            'view_items',
            'view_parties', 'manage_parties',
            'view_payments', 'create_payments'
        ],
        'storekeeper' => [
            'view_dashboard',
            'view_items', 'manage_items', 'adjust_stock',
            'view_warehouses', 'manage_warehouses', 'transfer_stock',
            'view_purchases', 'create_purchases',
            'manage_bom'
        ],
        'accountant' => [
            'view_dashboard',
            'view_sales', 'view_purchases', 'view_estimates',
            'view_items', 'view_warehouses',
            'view_parties', 'view_payments',
            'view_expenses', 'manage_expenses',
            'view_accounting', 'manage_accounting',
            'view_reports',
            'view_audit_logs'
        ]
    ];

    private static array $permissionCache = [];

    public static function clearCache(): void {
        self::$permissionCache = [];
    }

    public static function allPermissionKeys(): array {
        $keys = [];
        foreach (self::PERMISSION_GROUPS as $perms) {
            foreach ($perms as $k => $label) {
                $keys[] = $k;
            }
        }
        return $keys;
    }

    public static function getRoleDefaultPermissions(string $role): array {
        if ($role === 'owner') {
            return self::allPermissionKeys();
        }
        return self::ROLE_DEFAULTS[$role] ?? self::ROLE_DEFAULTS['cashier'];
    }

    public static function getStaffRole(?array $user = null, ?array $firm = null): string {
        $user = $user ?? Auth::user();
        $firm = $firm ?? Auth::getActiveFirm();
        if (!$user) {
            return 'guest';
        }
        if (($user['role'] ?? '') === 'admin') {
            return 'admin';
        }
        if ($firm && (int)$firm['user_id'] === (int)$user['id']) {
            return 'owner';
        }
        if ($firm && defined('ENABLE_RBAC') && ENABLE_RBAC) {
            $rec = StaffAccount::getForUserAndFirm((int)$user['id'], (int)$firm['id']);
            if ($rec && !empty($rec['is_active'])) {
                return (string)$rec['role'];
            }
        }
        return ($user['role'] === 'staff') ? 'none' : 'owner';
    }

    public static function getPermissionsForContext(?array $user = null, ?array $firm = null): array {
        $user = $user ?? Auth::user();
        $firm = $firm ?? Auth::getActiveFirm();
        if (!$user) {
            return [];
        }
        if (($user['role'] ?? '') === 'admin') {
            return ['*'];
        }
        if (!$firm) {
            // User without firm yet: allow creating first firm only if subscriber owner
            return (($user['role'] ?? 'user') !== 'staff') ? ['manage_firms', 'view_dashboard'] : [];
        }

        $cacheKey = $user['id'] . ':' . $firm['id'];
        if (isset(self::$permissionCache[$cacheKey])) {
            return self::$permissionCache[$cacheKey];
        }

        // Firm owner always has full permissions on their own firms
        if ((int)$firm['user_id'] === (int)$user['id']) {
            return self::$permissionCache[$cacheKey] = ['*'];
        }

        if (!defined('ENABLE_RBAC') || !ENABLE_RBAC) {
            return self::$permissionCache[$cacheKey] = [];
        }

        $rec = StaffAccount::getForUserAndFirm((int)$user['id'], (int)$firm['id']);
        if (!$rec || empty($rec['is_active'])) {
            return self::$permissionCache[$cacheKey] = [];
        }

        $role = (string)$rec['role'];
        $perms = self::getRoleDefaultPermissions($role);

        if (!empty($rec['permissions_json'])) {
            $custom = json_decode((string)$rec['permissions_json'], true);
            if (is_array($custom)) {
                // Support explicit allow/deny map or full list
                if (array_is_list($custom)) {
                    $perms = array_values(array_intersect($custom, self::allPermissionKeys()));
                } else {
                    foreach ($custom as $pKey => $allowed) {
                        if (!in_array($pKey, self::allPermissionKeys(), true)) {
                            continue;
                        }
                        if ($allowed) {
                            if (!in_array($pKey, $perms, true)) {
                                $perms[] = $pKey;
                            }
                        } else {
                            $perms = array_values(array_diff($perms, [$pKey]));
                        }
                    }
                }
            }
        }

        return self::$permissionCache[$cacheKey] = $perms;
    }

    public static function hasPermission(string $permission, ?array $user = null, ?array $firm = null): bool {
        if (!defined('ENABLE_RBAC') || !ENABLE_RBAC) {
            // Even when RBAC toggle is disabled, prevent non-owners from admin-only ops if staff
            $user = $user ?? Auth::user();
            return $user && ($user['role'] ?? '') !== 'staff';
        }

        $perms = self::getPermissionsForContext($user, $firm);
        if (in_array('*', $perms, true)) {
            return true;
        }
        return in_array($permission, $perms, true);
    }

    public static function requirePermission(string $permission, bool $isJson = false): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $user = Auth::user();

        if (self::hasPermission($permission, $user, $firm)) {
            return;
        }

        $isAjax = $isJson
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Access denied: your account role does not have permission (' . $permission . ').'
            ]);
            exit;
        }

        http_response_code(403);
        Flash::set('error_msg', 'Access denied: your current role does not have permission to perform that action.');
        header('Location: /dashboard');
        exit;
    }

    /**
     * Require that the logged-in user is a subscriber business owner (not a staff login).
     */
    public static function requireOwnerAccount(bool $isJson = false): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        if (($user['role'] ?? 'user') !== 'staff') {
            return;
        }
        if ($isJson) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Only the business owner account can perform this action.']);
            exit;
        }
        http_response_code(403);
        Flash::set('error_msg', 'Only the primary business owner account can manage firms or backups.');
        header('Location: /dashboard');
        exit;
    }
}
