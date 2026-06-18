<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class CategoryController
{
    public function index(): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM contact_categories ORDER BY sort_order ASC, name ASC");
        $contactsCount = $db->fetchAll(
            "SELECT cc.id, COUNT(c.id) as cnt 
             FROM contact_categories cc 
             LEFT JOIN contacts c ON c.category_id = cc.id 
             GROUP BY cc.id"
        );
        $countMap = [];
        foreach ($contactsCount as $row) {
            $countMap[$row->id] = $row->cnt;
        }

        View::render('settings/categories', [
            'title' => 'مدیریت دسته‌بندی مخاطبین',
            'categories' => $categories,
            'countMap' => $countMap,
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('settings.manage');
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'نام دسته‌بندی الزامی است.']);
            exit;
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM contact_categories WHERE name = :name", [':name' => $name]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'این دسته‌بندی قبلاً ثبت شده است.']);
            exit;
        }

        $description = trim($_POST['description'] ?? '');
        $color = trim($_POST['color'] ?? '#6B7280');

        $db->insert('contact_categories', [
            'name' => $name,
            'description' => $description,
            'color' => $color,
        ]);

        ActivityLog::log('create_category', 'setting', 0, "دسته‌بندی {$name} ایجاد شد");
        echo json_encode(['success' => true, 'message' => 'دسته‌بندی با موفقیت ایجاد شد.']);
        exit;
    }

    public function update(array $params): void
    {
        Auth::requirePermission('settings.manage');
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'نام دسته‌بندی الزامی است.']);
            exit;
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM contact_categories WHERE name = :name AND id != :id", [':name' => $name, ':id' => $params['id']]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'این دسته‌بندی قبلاً ثبت شده است.']);
            exit;
        }

        $db->update('contact_categories', [
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'color' => trim($_POST['color'] ?? '#6B7280'),
        ], 'id = :id', [':id' => $params['id']]);

        ActivityLog::log('update_category', 'setting', 0, "دسته‌بندی {$name} ویرایش شد");
        echo json_encode(['success' => true, 'message' => 'دسته‌بندی با موفقیت ویرایش شد.']);
        exit;
    }

    public function delete(array $params): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        $category = $db->fetch("SELECT name FROM contact_categories WHERE id = :id", [':id' => $params['id']]);
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'دسته‌بندی یافت نشد.']);
            exit;
        }

        // Reset contacts in this category to default
        $default = $db->fetch("SELECT id FROM contact_categories WHERE is_default = 1");
        if ($default) {
            $db->update('contacts', ['category_id' => $default->id], 'category_id = :cat_id', [':cat_id' => $params['id']]);
        }

        $db->delete('contact_categories', 'id = :id', [':id' => $params['id']]);
        ActivityLog::log('delete_category', 'setting', 0, "دسته‌بندی {$category->name} حذف شد");
        echo json_encode(['success' => true, 'message' => 'دسته‌بندی با موفقیت حذف شد.']);
        exit;
    }

    public function getCategories(): void
    {
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT id, name, color, is_default FROM contact_categories ORDER BY sort_order ASC, name ASC");
        echo json_encode(['success' => true, 'data' => $categories]);
        exit;
    }
}