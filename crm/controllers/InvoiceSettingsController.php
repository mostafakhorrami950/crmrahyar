<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;

class InvoiceSettingsController
{
    public function index(): void
    {
        Auth::requireAdmin();
        $db = Database::getInstance();
        $settings = $db->fetchAll("SELECT * FROM invoice_settings ORDER BY id");
        
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s->setting_key] = $s->setting_value;
        }

        View::render('settings/invoice_settings', [
            'title' => 'تنظیمات فاکتور',
            'settings' => $settingsMap,
        ]);
    }

    public function update(): void
    {
        Auth::requireAdmin();
        $db = Database::getInstance();
        
        $keys = [
            'invoice_title',
            'invoice_subtitle',
            'invoice_company_name',
            'invoice_logo_url',
            'invoice_primary_color',
            'invoice_secondary_color',
            'invoice_success_color',
            'invoice_notes',
            'invoice_terms',
            'invoice_footer_text',
        ];

        foreach ($keys as $key) {
            $value = $_POST[$key] ?? '';
            $existing = $db->fetch("SELECT id FROM invoice_settings WHERE setting_key = :key", [':key' => $key]);
            if ($existing) {
                $db->update('invoice_settings', ['setting_value' => $value], 'setting_key = :key', [':key' => $key]);
            } else {
                $db->insert('invoice_settings', ['setting_key' => $key, 'setting_value' => $value]);
            }
        }

        Session::setFlash('success', 'تنظیمات فاکتور با موفقیت ذخیره شد.');
        View::redirect('/settings/invoice');
    }

    /**
     * Get invoice settings as array (helper)
     */
    public static function getSettings(): array
    {
        $db = Database::getInstance();
        $settings = $db->fetchAll("SELECT setting_key, setting_value FROM invoice_settings");
        $map = [];
        foreach ($settings as $s) {
            $map[$s->setting_key] = $s->setting_value;
        }
        return $map;
    }
}