<?php
/**
 * Public Shareable Online Product Catalogue Model
 * Strict tenant isolation via unique public slug. No admin/accounting/invoice data exposed publicly.
 */

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/Firm.php';

class Catalogue extends Model {
    public static function ensureSettings(int $firmId): array {
        $stmt = self::db()->prepare("SELECT * FROM catalogue_settings WHERE firm_id = ? LIMIT 1");
        $stmt->execute([$firmId]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }

        $firm = Firm::getById($firmId);
        $baseSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', (string)($firm['name'] ?? 'store')));
        $baseSlug = trim($baseSlug, '-') ?: 'store';
        $slug = $baseSlug . '-' . $firmId;

        self::db()->prepare("
            INSERT INTO catalogue_settings (firm_id, is_published, slug, headline, description, whatsapp_number, show_prices, show_stock_badge, hide_out_of_stock)
            VALUES (?, 0, ?, ?, NULL, ?, 1, 1, 0)
        ")->execute([$firmId, $slug, ($firm['name'] ?? 'Our Store') . ' Catalogue', $firm['phone'] ?? null]);

        $stmt->execute([$firmId]);
        return $stmt->fetch();
    }

    public static function updateSettings(int $firmId, array $data): array {
        $existing = self::ensureSettings($firmId);

        $rawSlug = strtolower(trim((string)($data['slug'] ?? $existing['slug'])));
        $slug = trim((string)preg_replace('/[^a-z0-9\-]+/', '-', $rawSlug), '-');
        if ($slug === '') {
            $slug = 'store-' . $firmId;
        }

        // Verify unique slug
        $chk = self::db()->prepare("SELECT firm_id FROM catalogue_settings WHERE slug = ? AND firm_id != ? LIMIT 1");
        $chk->execute([$slug, $firmId]);
        if ($chk->fetch()) {
            throw new Exception("Catalogue URL slug '{$slug}' is already in use by another store.");
        }

        $waPhone = preg_replace('/[^0-9]/', '', (string)($data['whatsapp_number'] ?? ''));

        self::db()->prepare("
            UPDATE catalogue_settings
            SET is_published = ?, slug = ?, headline = ?, description = ?, whatsapp_number = ?,
                show_prices = ?, show_stock_badge = ?, hide_out_of_stock = ?, updated_at = CURRENT_TIMESTAMP
            WHERE firm_id = ?
        ")->execute([
            !empty($data['is_published']) ? 1 : 0,
            $slug,
            trim((string)($data['headline'] ?? '')) ?: $existing['headline'],
            trim((string)($data['description'] ?? '')) ?: null,
            $waPhone !== '' ? $waPhone : null,
            !empty($data['show_prices']) ? 1 : 0,
            !empty($data['show_stock_badge']) ? 1 : 0,
            !empty($data['hide_out_of_stock']) ? 1 : 0,
            $firmId
        ]);

        return self::ensureSettings($firmId);
    }

    public static function getPublicCatalogueBySlug(string $slug): ?array {
        $cleanSlug = trim(strtolower($slug));
        if ($cleanSlug === '') return null;

        $stmt = self::db()->prepare("
            SELECT cs.*, f.name AS firm_name, f.city, f.state, f.phone AS firm_phone, f.logo_path
            FROM catalogue_settings cs
            JOIN firms f ON f.id = cs.firm_id
            WHERE cs.slug = ? AND cs.is_published = 1
            LIMIT 1
        ");
        $stmt->execute([$cleanSlug]);
        $settings = $stmt->fetch();
        if (!$settings) {
            return null;
        }

        $firmId = (int)$settings['firm_id'];
        $sql = "SELECT id, name, category, item_code, unit, sale_price, tax_rate, current_stock, description FROM items WHERE firm_id = ?";
        if (!empty($settings['hide_out_of_stock'])) {
            $sql .= " AND current_stock > 0";
        }
        $sql .= " ORDER BY category ASC, name ASC";

        $itStmt = self::db()->prepare($sql);
        $itStmt->execute([$firmId]);
        $items = $itStmt->fetchAll();

        $categories = [];
        foreach ($items as $it) {
            if (!empty($it['category']) && !in_array($it['category'], $categories, true)) {
                $categories[] = $it['category'];
            }
        }

        return [
            'settings' => $settings,
            'items' => $items,
            'categories' => $categories
        ];
    }
}
