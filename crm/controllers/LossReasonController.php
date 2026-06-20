<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class LossReasonController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $reasons = $db->fetchAll("SELECT * FROM deal_loss_reasons ORDER BY sort_order ASC, name ASC");
        
        View::render('settings/loss_reasons', [
            'title' => 'مدیریت دلایل شکست معاملات',
            'reasons' => $reasons,
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '😞');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'نام دلیل شکست الزامی است.']);
            exit;
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM deal_loss_reasons WHERE name = :name", [':name' => $name]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'این مورد قبلاً ثبت شده است.']);
            exit;
        }

        $db->insert('deal_loss_reasons', [
            'name' => $name,
            'icon' => $icon,
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        ActivityLog::log('create_loss_reason', 'settings', 0, "دلیل شکست {$name} ایجاد شد");

        echo json_encode(['success' => true, 'message' => 'دلیل شکست با موفقیت اضافه شد.', 'redirect' => '/settings/loss-reasons']);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '😞');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || !$id) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر است.']);
            exit;
        }

        $db = Database::getInstance();
        $db->update('deal_loss_reasons', [
            'name' => $name,
            'icon' => $icon,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ], 'id = :id', [':id' => $id]);

        ActivityLog::log('update_loss_reason', 'settings', 0, "دلیل شکست {$name} ویرایش شد");

        echo json_encode(['success' => true, 'message' => 'دلیل شکست با موفقیت ویرایش شد.', 'redirect' => '/settings/loss-reasons']);
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
        $reason = $db->fetch("SELECT name FROM deal_loss_reasons WHERE id = :id", [':id' => $id]);
        if ($reason) {
            $db->delete('deal_loss_reasons', 'id = :id', [':id' => $id]);
            ActivityLog::log('delete_loss_reason', 'settings', 0, "دلیل شکست {$reason->name} حذف شد");
        }

        echo json_encode(['success' => true, 'message' => 'دلیل شکست با موفقیت حذف شد.', 'redirect' => '/settings/loss-reasons']);
        exit;
    }

    /**
     * Get all active loss reasons as JSON (for AJAX calls)
     */
    public function getActive(): void
    {
        $db = Database::getInstance();
        $reasons = $db->fetchAll("SELECT id, name, icon FROM deal_loss_reasons WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        
        echo json_encode(['success' => true, 'reasons' => $reasons]);
        exit;
    }
}