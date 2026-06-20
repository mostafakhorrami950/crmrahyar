<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class SettingController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $settings = $db->fetchAll("SELECT * FROM settings ORDER BY setting_group, setting_key");
        
        $groupedSettings = [];
        foreach ($settings as $s) {
            $groupedSettings[$s->setting_group][] = $s;
        }
        
        // Get all features status
        $features = [];
        $featureSettings = $db->fetchAll("SELECT * FROM settings WHERE setting_group = 'features'");
        foreach ($featureSettings as $fs) {
            $featureName = str_replace('feature_', '', $fs->setting_key);
            $features[$featureName] = $fs->setting_value == '1';
        }

        // Get counts for dashboard stats
        $stats = [
            'users' => $db->fetch("SELECT COUNT(*) as c FROM users")->c ?? 0,
            'contacts' => $db->fetch("SELECT COUNT(*) as c FROM contacts")->c ?? 0,
            'deals' => $db->fetch("SELECT COUNT(*) as c FROM deals")->c ?? 0,
            'pipelines' => $db->fetch("SELECT COUNT(*) as c FROM pipelines")->c ?? 0,
            'categories' => $db->fetch("SELECT COUNT(*) as c FROM contact_categories")->c ?? 0,
            'sources' => $db->fetch("SELECT COUNT(*) as c FROM deal_sources")->c ?? 0,
            'roles' => $db->fetch("SELECT COUNT(*) as c FROM roles")->c ?? 0,
            'custom_fields' => $db->fetch("SELECT COUNT(*) as c FROM custom_fields")->c ?? 0,
            'payments' => $db->fetch("SELECT COUNT(*) as c FROM payments")->c ?? 0,
            'sms_sent' => $db->fetch("SELECT COUNT(*) as c FROM sms_history")->c ?? 0,
        ];

        View::render('settings/index', [
            'title' => 'تنظیمات سیستم',
            'groupedSettings' => $groupedSettings,
            'features' => $features,
            'stats' => $stats,
        ]);
    }

    public function update(): void
    {
        $settings = $_POST['settings'] ?? [];

        if (empty($settings)) {
            Session::setFlash('danger', 'هیچ مقداری برای ذخیره وجود ندارد.');
            View::redirect('/settings');
        }

        $db = Database::getInstance();
        
        foreach ($settings as $key => $value) {
            $db->update('settings', ['setting_value' => $value], 'setting_key = :key', [':key' => $key]);
        }

        ActivityLog::log('update_settings', 'settings', 0, 'تنظیمات سیستم به‌روزرسانی شد');
        Session::setFlash('success', 'تنظیمات با موفقیت ذخیره شد.');
        View::redirect('/settings');
    }

    public function toggleFeature(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $feature = $_POST['feature'] ?? '';
        $enabled = (int)($_POST['enabled'] ?? 0);

        if (empty($feature)) {
            echo json_encode(['success' => false, 'message' => 'ویژگی نامعتبر است']);
            exit;
        }

        $db = Database::getInstance();
        $key = 'feature_' . $feature;
        
        $db->update('settings', ['setting_value' => $enabled], 'setting_key = :key', [':key' => $key]);
        
        ActivityLog::log('toggle_feature', 'settings', 0, "ویژگی {$feature} " . ($enabled ? 'فعال' : 'غیرفعال') . " شد");
        
        echo json_encode(['success' => true, 'message' => 'تغییرات اعمال شد']);
        exit;
    }
}