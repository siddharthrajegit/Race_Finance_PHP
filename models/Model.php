<?php
/**
 * Base Model
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Security.php';

class Model {
    public const GST_STATES = [
        ['code' => '01', 'name' => 'Jammu & Kashmir'],
        ['code' => '02', 'name' => 'Himachal Pradesh'],
        ['code' => '03', 'name' => 'Punjab'],
        ['code' => '04', 'name' => 'Chandigarh'],
        ['code' => '05', 'name' => 'Uttarakhand'],
        ['code' => '06', 'name' => 'Haryana'],
        ['code' => '07', 'name' => 'Delhi'],
        ['code' => '08', 'name' => 'Rajasthan'],
        ['code' => '09', 'name' => 'Uttar Pradesh'],
        ['code' => '10', 'name' => 'Bihar'],
        ['code' => '11', 'name' => 'Sikkim'],
        ['code' => '12', 'name' => 'Arunachal Pradesh'],
        ['code' => '13', 'name' => 'Nagaland'],
        ['code' => '14', 'name' => 'Manipur'],
        ['code' => '15', 'name' => 'Mizoram'],
        ['code' => '16', 'name' => 'Tripura'],
        ['code' => '17', 'name' => 'Meghalaya'],
        ['code' => '18', 'name' => 'Assam'],
        ['code' => '19', 'name' => 'West Bengal'],
        ['code' => '20', 'name' => 'Jharkhand'],
        ['code' => '21', 'name' => 'Odisha'],
        ['code' => '22', 'name' => 'Chhattisgarh'],
        ['code' => '23', 'name' => 'Madhya Pradesh'],
        ['code' => '24', 'name' => 'Gujarat'],
        ['code' => '26', 'name' => 'Dadra and Nagar Haveli and Daman and Diu'],
        ['code' => '27', 'name' => 'Maharashtra'],
        ['code' => '29', 'name' => 'Karnataka'],
        ['code' => '30', 'name' => 'Goa'],
        ['code' => '31', 'name' => 'Lakshadweep'],
        ['code' => '32', 'name' => 'Kerala'],
        ['code' => '33', 'name' => 'Tamil Nadu'],
        ['code' => '34', 'name' => 'Puducherry'],
        ['code' => '35', 'name' => 'Andaman & Nicobar Islands'],
        ['code' => '36', 'name' => 'Telangana'],
        ['code' => '37', 'name' => 'Andhra Pradesh'],
        ['code' => '38', 'name' => 'Ladakh'],
        ['code' => '97', 'name' => 'Other Territory']
    ];

    protected static function db(): PDO {
        return DB::getConnection();
    }

    public static function formatDocumentNumber(int|string|null $num): string {
        $parsed = (int)$num;
        if ($parsed < 0) return '001';
        return str_pad((string)$parsed, 3, '0', STR_PAD_LEFT);
    }

    public static function transaction(callable $callback) {
        $db = self::db();
        $isTopLevel = !$db->inTransaction();
        if ($isTopLevel) {
            $db->beginTransaction();
        }
        try {
            $result = $callback($db);
            if ($isTopLevel) {
                $db->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($isTopLevel && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
