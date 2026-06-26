<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class UserController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.*, r.name as role_name, r.slug as role_slug,
                    (SELECT COUNT(*) FROM deals WHERE assigned_to = u.id) as deals_count,
                    (SELECT COALESCE(SUM(amount),0) FROM deals WHERE assigned_to = u.id AND is_won = 1) as won_amount,
                    (SELECT COUNT(*) FROM contacts WHERE created_by = u.id) as contacts_count,
                    (SELECT COUNT(*) FROM sms_history WHERE sent_by = u.id) as sms_count
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             ORDER BY u.created_at DESC"
        );
        View::render('users/index', ['title' => 'مدیریت کاربران', 'users' => $users]);
    }

    public function create(): void
    {
        $db = Database::getInstance();
        $roles = $db->fetchAll("SELECT * FROM roles WHERE is_active = 1 ORDER BY name");
        View::render('users/create', ['title' => 'ایجاد کاربر جدید', 'roles' => $roles]);
    }

    public function store(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (empty($username) || empty($password) || empty($fullName) || empty($roleId)) {
            Session::setFlash('danger', 'لطفا تمام فیلدهای ضروری را پر کنید.');
            View::redirect('/users/create');
        }

        $db = Database::getInstance();
        
        // Check duplicate username
        $exists = $db->fetch("SELECT id FROM users WHERE username = :username", [':username' => $username]);
        if ($exists) {
            Session::setFlash('danger', 'این نام کاربری قبلاً ثبت شده است.');
            View::redirect('/users/create');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userId = $db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role_id' => $roleId,
            'is_active' => 1,
        ]);

        ActivityLog::log('create_user', 'user', $userId, "کاربر {$fullName} ایجاد شد");
        Session::setFlash('success', 'کاربر با موفقیت ایجاد شد.');
        View::redirect('/users');
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.*, r.name as role_name, r.slug as role_slug,
                    (SELECT COUNT(*) FROM deals WHERE assigned_to = u.id) as deals_count,
                    (SELECT COALESCE(SUM(amount),0) FROM deals WHERE assigned_to = u.id AND is_won = 1) as won_amount,
                    (SELECT COUNT(*) FROM contacts WHERE created_by = u.id) as contacts_count,
                    (SELECT COUNT(*) FROM sms_history WHERE sent_by = u.id) as sms_count,
                    (SELECT COUNT(*) FROM deals WHERE assigned_to = u.id AND is_won = 0 AND is_lost = 0) as open_deals
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.id = :id",
            [':id' => $params['id']]
        );
        if (!$user) {
            Session::setFlash('danger', 'کاربر مورد نظر یافت نشد.');
            View::redirect('/users');
        }
        $roles = $db->fetchAll("SELECT * FROM roles WHERE is_active = 1 ORDER BY name");
        View::render('users/edit', ['title' => 'ویرایش کاربر', 'user' => $user, 'roles' => $roles]);
    }

    public function update(array $params): void
    {
        // Prevent admin from deactivating themselves
        if ((int)$params['id'] === Auth::id() && isset($_POST['is_active']) && !$_POST['is_active']) {
            Session::setFlash('danger', 'نمی‌توانید حساب خودتان را غیرفعال کنید.');
            View::redirect('/users/edit/' . $params['id']);
        }
        
        // Prevent changing own role
        if ((int)$params['id'] === Auth::id() && isset($_POST['role_id']) && (int)$_POST['role_id'] !== Auth::user()->role_id) {
            Session::setFlash('danger', 'نمی‌توانید نقش خودتان را تغییر دهید.');
            View::redirect('/users/edit/' . $params['id']);
        }
        
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance();
        
        $data = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role_id' => $roleId,
            'is_active' => $isActive,
        ];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $db->update('users', $data, 'id = :id', [':id' => $params['id']]);
        
        ActivityLog::log('update_user', 'user', $params['id'], "کاربر {$fullName} ویرایش شد");
        Session::setFlash('success', 'کاربر با موفقیت ویرایش شد.');
        View::redirect('/users');
    }

    /**
     * Show transfer & delete confirmation page
     */
    public function transferAndDelete(array $params): void
    {
        Auth::requireAdmin();
        
        if ((int)$params['id'] === Auth::id()) {
            Session::setFlash('danger', 'نمی‌توانید حساب خودتان را حذف کنید.');
            View::redirect('/users');
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.*, r.name as role_name,
                    (SELECT COUNT(*) FROM deals WHERE assigned_to = u.id) as deals_count,
                    (SELECT COUNT(*) FROM deals WHERE created_by = u.id) as created_deals_count,
                    (SELECT COUNT(*) FROM contacts WHERE created_by = u.id) as contacts_count,
                    (SELECT COUNT(*) FROM contacts WHERE assigned_to = u.id) as assigned_contacts_count,
                    (SELECT COUNT(*) FROM deal_activities WHERE user_id = u.id) as activities_count,
                    (SELECT COUNT(*) FROM sms_history WHERE sent_by = u.id) as sms_count,
                    (SELECT COUNT(*) FROM payments WHERE created_by = u.id) as payments_count,
                    (SELECT COUNT(*) FROM deal_activities da JOIN deals d ON da.deal_id = d.id WHERE d.assigned_to = u.id) as deal_activities_count
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.id = :id",
            [':id' => $params['id']]
        );

        if (!$user) {
            Session::setFlash('danger', 'کاربر مورد نظر یافت نشد.');
            View::redirect('/users');
        }

        // Get all other active users for transfer dropdown
        $otherUsers = $db->fetchAll(
            "SELECT u.id, u.full_name, u.username, r.name as role_name
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.id != :id AND u.is_active = 1
             ORDER BY u.full_name",
            [':id' => $params['id']]
        );

        View::render('users/transfer_delete', [
            'title' => 'انتقال اطلاعات و حذف کاربر',
            'user' => $user,
            'otherUsers' => $otherUsers,
        ]);
    }

    /**
     * Execute data transfer and delete user
     */
    public function executeTransferAndDelete(array $params): void
    {
        Auth::requireAdmin();
        
        $userId = (int)$params['id'];
        $transferTo = (int)($_POST['transfer_to'] ?? 0);
        
        if ($userId === Auth::id()) {
            Session::setFlash('danger', 'نمی‌توانید حساب خودتان را حذف کنید.');
            View::redirect('/users');
        }

        if (!$transferTo) {
            Session::setFlash('danger', 'لطفاً کاربر مقصد برای انتقال اطلاعات را انتخاب کنید.');
            View::redirect('/users/transfer-delete/' . $userId);
        }

        // Verify target user exists
        $db = Database::getInstance();
        $targetUser = $db->fetch("SELECT id, full_name FROM users WHERE id = :id AND is_active = 1", [':id' => $transferTo]);
        if (!$targetUser) {
            Session::setFlash('danger', 'کاربر مقصد معتبر نیست.');
            View::redirect('/users/transfer-delete/' . $userId);
        }

        $sourceUser = $db->fetch("SELECT full_name FROM users WHERE id = :id", [':id' => $userId]);
        if (!$sourceUser) {
            Session::setFlash('danger', 'کاربر مورد نظر یافت نشد.');
            View::redirect('/users');
        }

        $db->beginTransaction();

        try {
            // 1. Transfer assigned deals
            $db->update('deals', ['assigned_to' => $transferTo], 'assigned_to = :old', [':old' => $userId]);

            // 2. Transfer created deals
            $db->update('deals', ['created_by' => $transferTo], 'created_by = :old', [':old' => $userId]);

            // 3. Transfer created contacts
            $db->update('contacts', ['created_by' => $transferTo], 'created_by = :old', [':old' => $userId]);

            // 4. Transfer assigned contacts (if assigned_to column exists)
            try {
                $db->update('contacts', ['assigned_to' => $transferTo], 'assigned_to = :old', [':old' => $userId]);
            } catch (\Exception $e) {
                // assigned_to column might not exist on contacts, ignore
            }

            // 5. Transfer deal activities
            $db->update('deal_activities', ['user_id' => $transferTo], 'user_id = :old', [':old' => $userId]);

            // 6. Transfer SMS history
            $db->update('sms_history', ['sent_by' => $transferTo], 'sent_by = :old', [':old' => $userId]);

            // 7. Transfer payments
            try {
                $db->update('payments', ['created_by' => $transferTo], 'created_by = :old', [':old' => $userId]);
            } catch (\Exception $e) {}

            // 8. Transfer activity logs
            $db->update('activity_logs', ['user_id' => $transferTo], 'user_id = :old', [':old' => $userId]);

            // 9. Transfer change logs (audit trail)
            try {
                $db->update('change_logs', ['user_id' => $transferTo], 'user_id = :old', [':old' => $userId]);
            } catch (\Exception $e) {}

            // 10. Transfer notifications
            try {
                $db->update('notifications', ['user_id' => $transferTo], 'user_id = :old', [':old' => $userId]);
            } catch (\Exception $e) {}

            // 11. Transfer dashboard notes
            try {
                $db->update('dashboard_notes', ['user_id' => $transferTo], 'user_id = :old', [':old' => $userId]);
            } catch (\Exception $e) {}

            // 12. Finally, delete the user
            $db->delete('users', 'id = :id', [':id' => $userId]);

            $db->commit();

            ActivityLog::log('transfer_delete_user', 'user', $userId,
                "کاربر {$sourceUser->full_name} حذف شد و اطلاعات به {$targetUser->full_name} منتقل شد"
            );

            Session::setFlash('success',
                "کاربر «{$sourceUser->full_name}» با موفقیت حذف شد و اطلاعات به «{$targetUser->full_name}» منتقل شد."
            );
        } catch (\Exception $e) {
            $db->rollback();
            Session::setFlash('danger', 'خطا در انتقال اطلاعات: ' . $e->getMessage());
        }

        View::redirect('/users');
    }

    public function delete(array $params): void
    {
        // Redirect to transfer page instead of direct delete
        View::redirect('/users/transfer-delete/' . $params['id']);
    }
}