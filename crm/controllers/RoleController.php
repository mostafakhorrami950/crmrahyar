<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class RoleController
{
    /**
     * Define all available permission modules with their permissions and labels.
     * This makes the system modular - add new modules/permissions here.
     */
    public static function getPermissionModules(): array
    {
        return [
            'داشبورد' => [
                ['slug' => 'dashboard.view', 'name' => 'مشاهده داشبورد', 'icon' => '📊', 'hasScope' => false],
            ],
            'معاملات' => [
                ['slug' => 'deals.view', 'name' => 'مشاهده معاملات', 'icon' => '👁️', 'hasScope' => true],
                ['slug' => 'deals.create', 'name' => 'ایجاد معامله', 'icon' => '➕', 'hasScope' => true],
                ['slug' => 'deals.edit', 'name' => 'ویرایش معامله', 'icon' => '✏️', 'hasScope' => true],
                ['slug' => 'deals.delete', 'name' => 'حذف معامله', 'icon' => '🗑️', 'hasScope' => true],
                ['slug' => 'deals.move', 'name' => 'تغییر مرحله', 'icon' => '➡️', 'hasScope' => true],
                ['slug' => 'deals.activity', 'name' => 'ثبت فعالیت', 'icon' => '📝', 'hasScope' => true],
            ],
            'مخاطبین' => [
                ['slug' => 'contacts.view', 'name' => 'مشاهده مخاطبین', 'icon' => '👁️', 'hasScope' => true],
                ['slug' => 'contacts.create', 'name' => 'ایجاد مخاطب', 'icon' => '➕', 'hasScope' => true],
                ['slug' => 'contacts.edit', 'name' => 'ویرایش مخاطب', 'icon' => '✏️', 'hasScope' => true],
                ['slug' => 'contacts.delete', 'name' => 'حذف مخاطب', 'icon' => '🗑️', 'hasScope' => true],
                ['slug' => 'contacts.detail', 'name' => 'جزئیات مخاطب', 'icon' => '📋', 'hasScope' => true],
            ],
            'پایپ لاین' => [
                ['slug' => 'pipelines.view', 'name' => 'مشاهده پایپ لاین', 'icon' => '👁️', 'hasScope' => false],
                ['slug' => 'pipelines.manage', 'name' => 'مدیریت پایپ لاین', 'icon' => '⚙️', 'hasScope' => false],
            ],
            'پرداخت' => [
                ['slug' => 'payments.view', 'name' => 'مشاهده پرداخت‌ها', 'icon' => '👁️', 'hasScope' => true],
                ['slug' => 'payments.create', 'name' => 'ایجاد لینک پرداخت', 'icon' => '💳', 'hasScope' => true],
            ],
            'پیامک' => [
                ['slug' => 'sms.send', 'name' => 'ارسال پیامک', 'icon' => '📤', 'hasScope' => true],
                ['slug' => 'sms.view', 'name' => 'تاریخچه پیامک', 'icon' => '📋', 'hasScope' => true],
            ],
            'گزارشات' => [
                ['slug' => 'reports.view', 'name' => 'مشاهده گزارشات', 'icon' => '📈', 'hasScope' => false],
            ],
            'کاربران' => [
                ['slug' => 'users.view', 'name' => 'مشاهده کاربران', 'icon' => '👁️', 'hasScope' => false],
                ['slug' => 'users.manage', 'name' => 'مدیریت کاربران', 'icon' => '⚙️', 'hasScope' => false],
            ],
            'نقش‌ها' => [
                ['slug' => 'roles.view', 'name' => 'مشاهده نقش‌ها', 'icon' => '👁️', 'hasScope' => false],
                ['slug' => 'roles.manage', 'name' => 'مدیریت نقش‌ها', 'icon' => '⚙️', 'hasScope' => false],
            ],
            'تنظیمات' => [
                ['slug' => 'settings.view', 'name' => 'مشاهده تنظیمات', 'icon' => '👁️', 'hasScope' => false],
                ['slug' => 'settings.manage', 'name' => 'مدیریت تنظیمات', 'icon' => '⚙️', 'hasScope' => false],
            ],
            'پایگاه داده' => [
                ['slug' => 'database.view', 'name' => 'مشاهده بکاپ‌ها', 'icon' => '👁️', 'hasScope' => false],
                ['slug' => 'database.manage', 'name' => 'مدیریت پایگاه داده', 'icon' => '⚙️', 'hasScope' => false],
            ],
            'لاگ فعالیت‌ها' => [
                ['slug' => 'activitylog.view', 'name' => 'مشاهده لاگ', 'icon' => '📋', 'hasScope' => false],
            ],
        ];
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $roles = $db->fetchAll(
            "SELECT r.*, (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
             FROM roles r ORDER BY r.is_system DESC, r.created_at DESC"
        );
        
        // Get permissions for each role
        $rolePermissions = [];
        foreach ($roles as $role) {
            $perms = $db->fetchAll("SELECT permission, scope FROM role_permissions WHERE role_id = :id", [':id' => $role->id]);
            $rolePermissions[$role->id] = $perms;
        }

        View::render('roles/index', [
            'title' => 'مدیریت نقش‌ها و دسترسی‌ها',
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function create(): void
    {
        $modules = self::getPermissionModules();
        View::render('roles/create', [
            'title' => 'ایجاد نقش جدید',
            'modules' => $modules,
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        $scopes = $_POST['scopes'] ?? [];

        if (empty($name)) {
            Session::setFlash('danger', 'لطفا نام نقش را وارد کنید.');
            View::redirect('/roles/create');
        }

        $db = Database::getInstance();
        $slug = 'role_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
        
        $roleId = $db->insert('roles', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'is_system' => 0,
        ]);

        // Insert permissions with scope
        foreach ($permissions as $permSlug) {
            $scope = 'all';
            if (isset($scopes[$permSlug]) && in_array($scopes[$permSlug], ['own', 'all'])) {
                $scope = $scopes[$permSlug];
            }
            $db->insert('role_permissions', [
                'role_id' => $roleId,
                'permission' => $permSlug,
                'scope' => $scope,
            ]);
        }

        ActivityLog::log('create_role', 'role', $roleId, "نقش {$name} ایجاد شد");
        Session::setFlash('success', 'نقش «' . $name . '» با موفقیت ایجاد شد.');
        View::redirect('/roles');
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $role = $db->fetch("SELECT * FROM roles WHERE id = :id", [':id' => $params['id']]);
        if (!$role) {
            Session::setFlash('danger', 'نقش مورد نظر یافت نشد.');
            View::redirect('/roles');
        }

        $modules = self::getPermissionModules();

        // Get current role permissions with scope
        $rolePerms = $db->fetchAll("SELECT permission, scope FROM role_permissions WHERE role_id = :id", [':id' => $params['id']]);
        $rolePermsMap = [];
        foreach ($rolePerms as $p) {
            $rolePermsMap[$p->permission] = $p->scope;
        }

        View::render('roles/edit', [
            'title' => 'ویرایش نقش: ' . $role->name,
            'role' => $role,
            'modules' => $modules,
            'rolePermsMap' => $rolePermsMap,
        ]);
    }

    public function update(array $params): void
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        $scopes = $_POST['scopes'] ?? [];

        $db = Database::getInstance();
        $role = $db->fetch("SELECT * FROM roles WHERE id = :id", [':id' => $params['id']]);
        if (!$role) {
            Session::setFlash('danger', 'نقش مورد نظر یافت نشد.');
            View::redirect('/roles');
        }

        $db->update('roles', [
            'name' => $name,
            'description' => $description,
        ], 'id = :id', [':id' => $params['id']]);

        // Update permissions with scope
        $db->delete('role_permissions', 'role_id = :id', [':id' => $params['id']]);
        foreach ($permissions as $permSlug) {
            $scope = 'all';
            if (isset($scopes[$permSlug]) && in_array($scopes[$permSlug], ['own', 'all'])) {
                $scope = $scopes[$permSlug];
            }
            $db->insert('role_permissions', [
                'role_id' => $params['id'],
                'permission' => $permSlug,
                'scope' => $scope,
            ]);
        }

        ActivityLog::log('update_role', 'role', $params['id'], "نقش {$name} ویرایش شد");
        Session::setFlash('success', 'نقش «' . $name . '» با موفقیت ویرایش شد.');
        View::redirect('/roles');
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $role = $db->fetch("SELECT * FROM roles WHERE id = :id", [':id' => $params['id']]);
        
        if ($role && !$role->is_system) {
            // Check if any users have this role
            $userCount = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role_id = :id", [':id' => $params['id']]);
            if ($userCount && $userCount->count > 0) {
                Session::setFlash('danger', 'این نقش دارای ' . $userCount->count . ' کاربر است و قابل حذف نیست.');
            } else {
                $db->delete('role_permissions', 'role_id = :id', [':id' => $params['id']]);
                $db->delete('roles', 'id = :id', [':id' => $params['id']]);
                ActivityLog::log('delete_role', 'role', $params['id'], "نقش {$role->name} حذف شد");
                Session::setFlash('success', 'نقش با موفقیت حذف شد.');
            }
        } else {
            Session::setFlash('danger', 'این نقش سیستمی قابل حذف نیست.');
        }
        View::redirect('/roles');
    }
}