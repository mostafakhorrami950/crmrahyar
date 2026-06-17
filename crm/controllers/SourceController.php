<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class SourceController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $sources = $db->fetchAll("SELECT * FROM deal_sources ORDER BY sort_order ASC, name ASC");
        
        View::render('settings/sources', [
            'title' => 'مدیریت نحوه آشنایی',
            'sources' => $sources,
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '📌');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'نام نحوه آشنایی الزامی است.']);
            exit;
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM deal_sources WHERE name = :name", [':name' => $name]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'این مورد قبلاً ثبت شده است.']);
            exit;
        }

        $db->insert('deal_sources', [
            'name' => $name,
            'icon' => $icon,
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        ActivityLog::log('create_source', 'settings', 0, "نحوه آشنایی {$name} ایجاد شد");

        $config = $GLOBALS['app_config'];
        echo json_encode(['success' => true, 'message' => 'نحوه آشنایی با موفقیت اضافه شد.', 'redirect' => '/settings/sources']);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '📌');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || !$id) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر است.']);
            exit;
        }

        $db = Database::getInstance();
        $db->update('deal_sources', [
            'name' => $name,
            'icon' => $icon,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ], 'id = :id', [':id' => $id]);

        ActivityLog::log('update_source', 'settings', 0, "نحوه آشنایی {$name} ویرایش شد");

        $config = $GLOBALS['app_config'];
        echo json_encode(['success' => true, 'message' => 'نحوه آشنایی با موفقیت ویرایش شد.', 'redirect' => '/settings/sources']);
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
        $source = $db->fetch("SELECT name FROM deal_sources WHERE id = :id", [':id' => $id]);
        if ($source) {
            $db->delete('deal_sources', 'id = :id', [':id' => $id]);
            ActivityLog::log('delete_source', 'settings', 0, "نحوه آشنایی {$source->name} حذف شد");
        }

        echo json_encode(['success' => true, 'message' => 'نحوه آشنایی با موفقیت حذف شد.', 'redirect' => '/settings/sources']);
        exit;
    }

    /**
     * Get all active sources as JSON (for AJAX calls)
     */
    public function getActive(): void
    {
        $db = Database::getInstance();
        $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        
        echo json_encode(['success' => true, 'sources' => $sources]);
        exit;
    }
}