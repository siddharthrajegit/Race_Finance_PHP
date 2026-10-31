<?php
/**
 * Custom Unit Model
 * User-Scoped Measurement Units
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Security.php';

class CustomUnit extends Model {
    public const SYSTEM_UNITS = [
        ['code' => 'PCS', 'label' => 'PCS (Pieces)',   'is_custom' => false],
        ['code' => 'KG',  'label' => 'KG (Kilograms)',  'is_custom' => false],
        ['code' => 'BOX', 'label' => 'BOX (Boxes)',     'is_custom' => false],
        ['code' => 'MTR', 'label' => 'MTR (Meters)',    'is_custom' => false],
        ['code' => 'LTR', 'label' => 'LTR (Liters)',    'is_custom' => false],
        ['code' => 'NOS', 'label' => 'NOS (Numbers)',   'is_custom' => false],
        ['code' => 'BAG', 'label' => 'BAG (Bags)',      'is_custom' => false],
        ['code' => 'PKT', 'label' => 'PKT (Packets)',   'is_custom' => false],
        ['code' => 'BDL', 'label' => 'BDL (Bundle)',    'is_custom' => false],
        ['code' => 'DZ',  'label' => 'DZ (Dozen)',      'is_custom' => false],
        ['code' => 'ROL', 'label' => 'ROL (Rolls)',     'is_custom' => false]
    ];

    /**
     * Get all custom units created by a specific user
     */
    public static function getByUserId(int $userId): array {
        $stmt = self::db()->prepare("
            SELECT id, user_id, unit_code AS code, unit_label AS label, created_at 
            FROM custom_units 
            WHERE user_id = ? 
            ORDER BY unit_code ASC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        return array_map(function($r) {
            $r['is_custom'] = true;
            return $r;
        }, $rows ?: []);
    }

    /**
     * Get combined list of system units + user's custom units
     */
    public static function getAllForUser(?int $userId): array {
        $units = self::SYSTEM_UNITS;
        if (!$userId) {
            return $units;
        }

        $custom = self::getByUserId($userId);
        $existingCodes = array_column($units, 'code');

        foreach ($custom as $cu) {
            if (!in_array($cu['code'], $existingCodes, true)) {
                $units[] = $cu;
                $existingCodes[] = $cu['code'];
            }
        }

        return $units;
    }

    /**
     * Create a new custom unit for a user
     */
    public static function create(int $userId, string $code, string $label): ?array {
        $cleanCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($code)), 0, 15));
        $cleanLabel = substr(strip_tags(trim($label)), 0, 50);

        if ($cleanCode === '') {
            throw new Exception("Unit code cannot be empty and must be alphanumeric (max 15 characters).");
        }
        if ($cleanLabel === '') {
            $cleanLabel = $cleanCode;
        }

        // Check if code matches standard system unit
        foreach (self::SYSTEM_UNITS as $su) {
            if ($su['code'] === $cleanCode) {
                return $su;
            }
        }

        // Check if user already added this unit code
        $stmt = self::db()->prepare("SELECT * FROM custom_units WHERE user_id = ? AND unit_code = ?");
        $stmt->execute([$userId, $cleanCode]);
        $existing = $stmt->fetch();
        if ($existing) {
            return [
                'id' => (int)$existing['id'],
                'user_id' => (int)$existing['user_id'],
                'code' => $existing['unit_code'],
                'label' => $existing['unit_label'],
                'is_custom' => true
            ];
        }

        $insert = self::db()->prepare("
            INSERT INTO custom_units (user_id, unit_code, unit_label) 
            VALUES (?, ?, ?)
        ");
        $insert->execute([$userId, $cleanCode, $cleanLabel]);
        $newId = (int)self::db()->lastInsertId();

        return [
            'id' => $newId,
            'user_id' => $userId,
            'code' => $cleanCode,
            'label' => $cleanLabel,
            'is_custom' => true
        ];
    }

    /**
     * Delete a custom unit owned by a user
     */
    public static function delete(int $id, int $userId): bool {
        $stmt = self::db()->prepare("DELETE FROM custom_units WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}
