<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class WinReasonController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $reasons = $db->fetchAll("SELECT * FROM deal_win_reasons ORDER BY sort_order ASC, name ASC");
        
        View::render('settings/win_reasons', [
            'title' => 'مدیریت دلایل موفقیت معاملات',
            'reasons' => $reasons,
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '✅');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'نام دلیل موفقیت الزامی است.']);
            exit;
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM deal_win_reasons WHERE name = :name", [':name' => $name]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'این مورد قبلاً ثبت شده است.']);
            exit;
        }

        $db->insert('deal_win_reasons', [
            'name' => $name,
            'icon' => $icon,
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        ActivityLog::log('create_win_reason', 'settings', 0, "دلیل موفقیت {$name} ایجاد شد");

        echo json_encode(['success' => true, 'message' => 'دلیل موفقیت با موفقیت اضافه شد.', 'redirect' => '/settings/win-reasons']);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '✅');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || !$id) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر است.']);
            exit;
        }

        $db = Database::getInstance();
        $db->update('deal_win_reasons', [
            'name' => $name,
            'icon' => $icon,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ], 'id = :id', [':id' => $id]);

        ActivityLog::log('update_win_reason', 'settings', 0, "دلیل موفقیت {$name} ویرایش شد");

        echo json_encode(['success' => true, 'message' => 'دلیل موفقیت با موفقیت ویرایش شد.', 'redirect' => '/settings/win-reasons']);
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر است.']);
            exit;
        }

        $db = Database::getInstance();
        $reason = $db->fetch("SELECT name FROM deal_win_reasons WHERE id = :id", [':id' => $id]);
        if ($reason) {
            $db->delete('deal_win_reasons', 'id = :id', [':id' => $id]);
            ActivityLog::log('delete_win_reason', 'settings', 0, "دلیل موفقیت {$reason->name} حذف شد");
        }

        echo json_encode(['success' => true, 'message' => 'دلیل موفقیت با موفقیت حذف شد.', 'redirect' => '/settings/win-reasons']);
        exit;
    }

    public function getActive(): void
    {
        $db = Database::getInstance();
        $reasons = $db->fetchAll("SELECT id, name, icon FROM deal_win_reasons WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        echo json_encode($reasons);
        exit;
    }
}