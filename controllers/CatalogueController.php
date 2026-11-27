<?php
/**
 * Online Product Catalogue Management & Public Read-Only Storefront Controller
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Catalogue.php';
require_once __DIR__ . '/../models/AuditLog.php';

class CatalogueController {
    private static function requireFeatureEnabled(): void {
        if (!defined('ENABLE_CATALOGUE') || !ENABLE_CATALOGUE) {
            Flash::set('info_msg', 'Online Product Catalogue module is disabled in configuration.');
            header('Location: /items');
            exit;
        }
    }

    public function manage(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_catalogue');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        $catSettings = Catalogue::ensureSettings($firmId);
        $publicUrl = rtrim(APP_URL, '/') . '/c/' . $catSettings['slug'];

        view('catalogue/manage', [
            'title' => 'Online Product Catalogue',
            'catSettings' => $catSettings,
            'publicUrl' => $publicUrl,
            'activeMenu' => 'catalogue'
        ]);
    }

    public function postUpdate(): void {
        self::requireFeatureEnabled();
        RBAC::requirePermission('manage_catalogue');
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];

        try {
            $updated = Catalogue::updateSettings($firmId, [
                'is_published' => !empty($_POST['is_published']),
                'slug' => $_POST['slug'] ?? '',
                'headline' => $_POST['headline'] ?? '',
                'description' => $_POST['description'] ?? '',
                'whatsapp_number' => $_POST['whatsapp_number'] ?? '',
                'show_prices' => !empty($_POST['show_prices']),
                'show_stock_badge' => !empty($_POST['show_stock_badge']),
                'hide_out_of_stock' => !empty($_POST['hide_out_of_stock'])
            ]);
            AuditLog::log($firmId, 'UPDATE', 'catalogue', $updated['id'] ?? null, "Updated catalogue /c/{$updated['slug']} published=" . ($updated['is_published'] ? 'yes' : 'no'));
            Flash::set('success_msg', 'Online catalogue settings saved!');
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to update catalogue settings.'));
        }
        header('Location: /catalogue');
        exit;
    }

    /**
     * Public unauthenticated storefront route: /c/:slug
     */
    public function publicView(array $params): void {
        if (!defined('ENABLE_CATALOGUE') || !ENABLE_CATALOGUE) {
            http_response_code(404);
            echo 'Catalogue disabled.';
            exit;
        }
        $slug = trim((string)($params['slug'] ?? ''));
        $data = Catalogue::getPublicCatalogueBySlug($slug);
        if (!$data) {
            http_response_code(404);
            echo '<!doctype html><html><body style="font-family:sans-serif;text-align:center;padding:60px;"><h2>Store Catalogue Not Found</h2><p>This catalogue link is unpublished or does not exist.</p></body></html>';
            exit;
        }

        view('catalogue/public', [
            'title' => ($data['settings']['headline'] ?: $data['settings']['firm_name']) . ' — Product Catalogue',
            'settings' => $data['settings'],
            'items' => $data['items'],
            'categories' => $data['categories']
        ], false);
    }
}
