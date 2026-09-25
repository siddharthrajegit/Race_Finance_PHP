<?php
/**
 * Setting Controller
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Setting.php';

class SettingController {
    public function getSettings(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $settings = Setting::get($firmId);
        $activeTab = $_GET['tab'] ?? 'sales';

        view('settings/index', [
            'title' => 'Business & Bill Settings',
            'settings' => $settings,
            'activeTab' => $activeTab,
            'activeMenu' => 'settings'
        ]);
    }

    public function postSettings(): void {
        Auth::requireUserOnly();
        $firm = Auth::requireActiveFirm();
        $firmId = (int)$firm['id'];
        $section = $_POST['section'] ?? 'sales';

        try {
            if ($section === 'sales') {
                Setting::update($firmId, 'sales', [
                    'default_gst_type' => ($_POST['default_gst_type'] ?? '') === 'non_gst' ? 'non_gst' : 'gst',
                    'gst_calc_mode' => ($_POST['gst_calc_mode'] ?? '') === 'final_amount' ? 'final_amount' : 'separate',
                    'enable_discount_column' => !empty($_POST['enable_discount_column']),
                    'enable_item_description' => !empty($_POST['enable_item_description']),
                    'default_due_days' => (int)($_POST['default_due_days'] ?? 0)
                ]);
            } elseif ($section === 'purchases') {
                Setting::update($firmId, 'purchases', [
                    'default_gst_type' => ($_POST['default_gst_type'] ?? '') === 'non_gst' ? 'non_gst' : 'gst',
                    'gst_calc_mode' => ($_POST['gst_calc_mode'] ?? '') === 'final_amount' ? 'final_amount' : 'separate',
                    'enable_discount_column' => !empty($_POST['enable_discount_column'])
                ]);
            } elseif ($section === 'print') {
                Setting::update($firmId, 'print', [
                    'show_bank_details' => !empty($_POST['show_bank_details']),
                    'show_upi_qr' => !empty($_POST['show_upi_qr']),
                    'show_signature' => !empty($_POST['show_signature']),
                    'footer_notes' => trim($_POST['footer_notes'] ?? '')
                ]);
            } elseif ($section === 'general') {
                Setting::update($firmId, 'general', [
                    'currency_symbol' => $_POST['currency_symbol'] ?? '₹',
                    'date_format' => $_POST['date_format'] ?? 'YYYY-MM-DD'
                ]);
            }

            Flash::set('success_msg', "Settings updated successfully for {$firm['name']}!");
            header("Location: /settings?tab={$section}");
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to save settings.'));
            header('Location: /settings');
            exit;
        }
    }
}
