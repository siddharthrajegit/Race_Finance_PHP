<?php
/**
 * Firm-Scoped User & Staff Activity Audit Log Model
 * Records sensitive actions safely without logging passwords, tokens, or secrets.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';

class AuditLog extends Model {
    public static function log(?int $firmId, string $action, ?string $targetType = null, $targetId = null, ?string $details = null, ?array $actor = null): void {
        try {
            $user = $actor ?? Auth::user();
            $userId = $user ? (int)$user['id'] : null;
            $userName = $user ? (string)($user['name'] ?? 'User') : 'System';
            $staffRole = $user ? RBAC::getStaffRole($user) : 'system';

            // Scrub accidental secret/password patterns
            $safeDetails = $details ? preg_replace('/(password|secret|token|key)\s*[:=]\s*\S+/i', '$1=[REDACTED]', $details) : null;
            if ($safeDetails && mb_strlen($safeDetails) > 1000) {
                $safeDetails = mb_substr($safeDetails, 0, 997) . '...';
            }

            $stmt = self::db()->prepare("
                INSERT INTO user_audit_logs (firm_id, user_id, user_name, staff_role, action, target_type, target_id, details, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $firmId,
                $userId,
                $userName,
                $staffRole,
                strtoupper(trim($action)),
                $targetType,
                $targetId !== null ? (string)$targetId : null,
                $safeDetails,
                Security::getClientIp()
            ]);
        } catch (Throwable $e) {
            // Never crash business operations if audit logging encounters a notice during early migration
            error_log('AuditLog notice: ' . $e->getMessage());
        }
    }

    public static function getByFirmId(int $firmId, array $filters = [], int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM user_audit_logs WHERE firm_id = ?";
        $params = [$firmId];

        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = strtoupper(trim((string)$filters['action']));
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);
        $sql .= " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByFirmId(int $firmId, array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM user_audit_logs WHERE firm_id = ?";
        $params = [$firmId];
        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = strtoupper(trim((string)$filters['action']));
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
