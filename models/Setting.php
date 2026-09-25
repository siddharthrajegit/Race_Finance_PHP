<?php
/**
 * Firm Settings Model
 * Manages invoice, print, and tax calculation preferences per business firm
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/Model.php';

class Setting extends Model {
    public const DEFAULT_SETTINGS = [
        'sales' => [
            'default_gst_type' => 'gst',
            'gst_calc_mode' => 'separate',
            'enable_discount_column' => true,
            'enable_item_description' => true,
            'default_due_days' => 0
        ],
        'purchases' => [
            'default_gst_type' => 'gst',
            'gst_calc_mode' => 'separate',
            'enable_discount_column' => true
        ],
        'print' => [
            'show_bank_details' => true,
            'show_upi_qr' => true,
            'show_signature' => true,
            'footer_notes' => ''
        ],
        'general' => [
            'currency_symbol' => '₹',
            'date_format' => 'YYYY-MM-DD'
        ]
    ];

    public static function get(int $firmId): array {
        try {
            $stmt = self::db()->prepare("SELECT * FROM firm_settings WHERE firm_id = ?");
            $stmt->execute([$firmId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['settings_json'])) {
                return self::DEFAULT_SETTINGS;
            }

            $parsed = json_decode($row['settings_json'], true) ?: [];
            return [
                'sales' => array_merge(self::DEFAULT_SETTINGS['sales'], $parsed['sales'] ?? []),
                'purchases' => array_merge(self::DEFAULT_SETTINGS['purchases'], $parsed['purchases'] ?? []),
                'print' => array_merge(self::DEFAULT_SETTINGS['print'], $parsed['print'] ?? []),
                'general' => array_merge(self::DEFAULT_SETTINGS['general'], $parsed['general'] ?? [])
            ];
        } catch (Throwable $e) {
            return self::DEFAULT_SETTINGS;
        }
    }

    public static function update(int $firmId, string|array $sectionOrFull, ?array $updates = null): array {
        $current = self::get($firmId);
        if (is_string($sectionOrFull)) {
            $current[$sectionOrFull] = array_merge($current[$sectionOrFull] ?? [], $updates ?? []);
        } elseif (is_array($sectionOrFull)) {
            $current = array_merge($current, $sectionOrFull);
        }

        $json = json_encode($current, JSON_UNESCAPED_UNICODE);

        if (DB::isMysql()) {
            $stmt = self::db()->prepare("
                INSERT INTO firm_settings (firm_id, settings_json, updated_at)
                VALUES (?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    settings_json = VALUES(settings_json),
                    updated_at = CURRENT_TIMESTAMP
            ");
        } else {
            $stmt = self::db()->prepare("
                INSERT INTO firm_settings (firm_id, settings_json, updated_at)
                VALUES (?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(firm_id) DO UPDATE SET
                    settings_json = excluded.settings_json,
                    updated_at = CURRENT_TIMESTAMP
            ");
        }

        $stmt->execute([$firmId, $json]);
        return $current;
    }
}
