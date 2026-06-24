<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;

class DashboardController
{
    public function index(): void

    {
        $db = Database::getInstance();
        $user = Auth::user();
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        $userId = Auth::id();

        if ($isAdmin) {
            // Admin sees all data
            $totalDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_lost = 0");
            $wonDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_won = 1");
            $lostDeals = $db->fetch("SELECT COUNT(*) as count FROM deals WHERE is_lost = 1");
            $totalContacts = $db->fetch("SELECT COUNT(*) as count FROM contacts");
            $totalPipelines = $db->fetch("SELECT COUNT(*) as count FROM pipelines WHERE is_active = 1");

            $recentDeals = $db->fetchAll(
                "SELECT d.*, s.name as stage_name, s.color as stage_color, c.full_name as contact_name, p.name as pipeline_name,
                        u.full_name as assigned_name
                 FROM deals d 
                 JOIN stages s ON d.stage_id = s.id 
                 JOIN pipelines p ON d.pipeline_id = p.id 
                 LEFT JOIN contacts c ON d.contact_id = c.id 
                 LEFT JOIN users u ON d.assigned_to = u.id 
                 ORDER BY d.created_at DESC LIMIT 10"
            );

            $recentActivities = $db->fetchAll(
                "SELECT al.*, u.full_name as user_name 
                 FROM activity_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 ORDER BY al.created_at DESC LIMIT 10"
            );
        } else {
            // Non-admin: only own data
            $totalDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_lost = 0 AND (assigned_to = :uid OR created_by = :uid2)", [':uid' => $userId, ':uid2' => $userId]);
            $wonDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_won = 1 AND (assigned_to = :uid OR created_by = :uid2)", [':uid' => $userId, ':uid2' => $userId]);
            $lostDeals = $db->fetch("SELECT COUNT(*) as count FROM deals WHERE is_lost = 1 AND (assigned_to = :uid OR created_by = :uid2)", [':uid' => $userId, ':uid2' => $userId]);
            $totalContacts = $db->fetch("SELECT COUNT(*) as count FROM contacts WHERE created_by = :uid", [':uid' => $userId]);
            $totalPipelines = $db->fetch("SELECT COUNT(*) as count FROM pipelines WHERE is_active = 1");

            $recentDeals = $db->fetchAll(
                "SELECT d.*, s.name as stage_name, s.color as stage_color, c.full_name as contact_name, p.name as pipeline_name
                 FROM deals d 
                 JOIN stages s ON d.stage_id = s.id 
                 JOIN pipelines p ON d.pipeline_id = p.id 
                 LEFT JOIN contacts c ON d.contact_id = c.id 
                 WHERE d.assigned_to = :uid OR d.created_by = :uid2
                 ORDER BY d.created_at DESC LIMIT 10",
                [':uid' => $userId, ':uid2' => $userId]
            );

            $recentActivities = $db->fetchAll(
                "SELECT al.*, u.full_name as user_name 
                 FROM activity_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 WHERE al.user_id = :uid
                 ORDER BY al.created_at DESC LIMIT 10",
                [':uid' => $userId]
            );
        }

        // Deals by stage for chart
        $dealsByStage = $db->fetchAll(
            "SELECT s.name, s.color, COUNT(d.id) as count, COALESCE(SUM(d.amount), 0) as total
             FROM stages s
             LEFT JOIN deals d ON d.stage_id = s.id AND d.is_lost = 0" . ($isAdmin ? '' : " AND (d.assigned_to = :uid OR d.created_by = :uid2)") . "
             WHERE s.is_active = 1
             GROUP BY s.id, s.name, s.color, s.order_index
             ORDER BY s.order_index",
            $isAdmin ? [] : [':uid' => $userId, ':uid2' => $userId]
        );

        // Upcoming follow-ups
        $upcomingFollowUps = $db->fetchAll(
            "SELECT da.*, d.title as deal_title, c.full_name as contact_name
             FROM deal_activities da
             JOIN deals d ON da.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE da.is_done = 0 AND da.reminder_at IS NOT NULL" . ($isAdmin ? '' : " AND da.user_id = :uid") . "
             ORDER BY da.reminder_at ASC LIMIT 5",
            $isAdmin ? [] : [':uid' => $userId]
        );

        // Admin notes for this user (handle missing is_archived column gracefully)
        $adminNotes = [];
        try {
            $adminNotes = $db->fetchAll(
                "SELECT dn.*, u.full_name as author_name 
                 FROM dashboard_notes dn 
                 LEFT JOIN users u ON dn.created_by = u.id 
                 WHERE dn.target_user_id = :uid AND (dn.is_archived IS NULL OR dn.is_archived = 0)
                 ORDER BY dn.is_pinned DESC, dn.created_at DESC LIMIT 5",
                [':uid' => $userId]
            );
        } catch (\Exception $e) {
            // Column might not exist yet, try without it
            $adminNotes = $db->fetchAll(
                "SELECT dn.*, u.full_name as author_name 
                 FROM dashboard_notes dn 
                 LEFT JOIN users u ON dn.created_by = u.id 
                 WHERE dn.target_user_id = :uid
                 ORDER BY dn.created_at DESC LIMIT 5",
                [':uid' => $userId]
            );
        }

        // Check for activity reminders and create notifications
        $this->checkActivityReminders($db, $userId);

        // Unread notifications count
        $unreadNotifs = $db->fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = :uid AND is_read = 0",
            [':uid' => $userId]
        );

        View::render('dashboard/index', [
            'title' => 'داشبورد',
            'isAdmin' => $isAdmin,
            'totalDeals' => $totalDeals,
            'wonDeals' => $wonDeals,
            'lostDeals' => $lostDeals,
            'totalContacts' => $totalContacts,
            'totalPipelines' => $totalPipelines,
            'recentDeals' => $recentDeals,
            'dealsByStage' => $dealsByStage,
            'recentActivities' => $recentActivities,
            'upcomingFollowUps' => $upcomingFollowUps,
            'adminNotes' => $adminNotes,
            'unreadNotifs' => $unreadNotifs,
        ]);
    }

    // Admin: Add note to a user's dashboard
    public function addNote(): void
    {
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
            exit;
        }

        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        $isPinned = (int)($_POST['is_pinned'] ?? 0);

        if (!$targetUserId || empty($note)) {
            echo json_encode(['success' => false, 'message' => 'لطفا کاربر و یادداشت را وارد کنید']);
            exit;
        }

        $db = Database::getInstance();
        $db->insert('dashboard_notes', [
            'target_user_id' => $targetUserId,
            'note' => $note,
            'is_pinned' => $isPinned,
            'created_by' => Auth::id(),
        ]);

        echo json_encode(['success' => true, 'message' => 'یادداشت اضافه شد']);
        exit;
    }

    // Admin: Delete note
    public function deleteNote(): void
    {
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
            exit;
        }

        $noteId = (int)($_POST['note_id'] ?? 0);
        if (!$noteId) {
            echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
            exit;
        }

        $db = Database::getInstance();
        $db->query("DELETE FROM dashboard_notes WHERE id = :id", [':id' => $noteId]);

        echo json_encode(['success' => true, 'message' => 'یادداشت حذف شد']);
        exit;
    }

    // Admin: Edit note
    public function editNote(): void
    {
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
            exit;
        }

        $noteId = (int)($_POST['note_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        $isPinned = (int)($_POST['is_pinned'] ?? 0);

        if (!$noteId || empty($note)) {
            echo json_encode(['success' => false, 'message' => 'لطفا متن یادداشت را وارد کنید']);
            exit;
        }

        $db = Database::getInstance();
        $db->update('dashboard_notes', ['note' => $note, 'is_pinned' => $isPinned], 'id = :id', [':id' => $noteId]);

        echo json_encode(['success' => true, 'message' => 'یادداشت ویرایش شد']);
        exit;
    }

    // Admin: Archive note
    public function archiveNote(): void
    {
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
            exit;
        }

        $noteId = (int)($_POST['note_id'] ?? 0);
        if (!$noteId) {
            echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
            exit;
        }

        $db = Database::getInstance();
        $db->update('dashboard_notes', ['is_archived' => 1], 'id = :id', [':id' => $noteId]);

        echo json_encode(['success' => true, 'message' => 'یادداشت آرشیو شد']);
        exit;
    }

    // Admin: Send note to all users
    public function addNoteToAll(): void
    {
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
            exit;
        }

        $note = trim($_POST['note'] ?? '');
        $isPinned = (int)($_POST['is_pinned'] ?? 0);

        if (empty($note)) {
            echo json_encode(['success' => false, 'message' => 'لطفا متن یادداشت را وارد کنید']);
            exit;
        }

        $db = Database::getInstance();
        $users = $db->fetchAll("SELECT id FROM users WHERE is_active = 1");
        $count = 0;
        foreach ($users as $u) {
            $db->insert('dashboard_notes', [
                'target_user_id' => $u->id,
                'note' => $note,
                'is_pinned' => $isPinned,
                'created_by' => Auth::id(),
            ]);
            $count++;
        }

        echo json_encode(['success' => true, 'message' => "یادداشت به {$count} کاربر ارسال شد"]);
        exit;
    }

    // Admin: List all notes (manage page)
    public function notes(): void
    {
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            Session::setFlash('danger', 'دسترسی غیرمجاز');
            View::redirect('/dashboard');
        }

        $db = Database::getInstance();
        $filterUser = (int)($_GET['user_id'] ?? 0);
        $filterArchived = (int)($_GET['archived'] ?? 0);

        $where = "WHERE 1=1";
        $params = [];
        if ($filterUser) {
            $where .= " AND dn.target_user_id = :uid";
            $params[':uid'] = $filterUser;
        }
        $where .= " AND dn.is_archived = :archived";
        $params[':archived'] = $filterArchived;

        $notes = $db->fetchAll(
            "SELECT dn.*, u.full_name as author_name, t.full_name as target_name 
             FROM dashboard_notes dn 
             LEFT JOIN users u ON dn.created_by = u.id 
             LEFT JOIN users t ON dn.target_user_id = t.id 
             {$where} 
             ORDER BY dn.created_at DESC",
            $params
        );

        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name");

        View::render('dashboard/notes', [
            'title' => 'مدیریت یادداشت‌ها',
            'notes' => $notes,
            'users' => $users,
            'filterUser' => $filterUser,
            'filterArchived' => $filterArchived,
        ]);
    }

    // Get notifications (AJAX)
    public function notifications(): void
    {
        $db = Database::getInstance();
        $userId = Auth::id();

        $notifs = $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 20",
            [':uid' => $userId]
        );
        $unreadCount = $db->fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = :uid AND is_read = 0",
            [':uid' => $userId]
        );

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'notifications' => $notifs,
            'unread_count' => $unreadCount->count ?? 0
        ]);
        exit;
    }

    // Mark single notification as read
    public function markNotificationRead(): void
    {
        $notifId = (int)($_POST['id'] ?? 0);
        if (!$notifId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $db = Database::getInstance();
        $db->query("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid", [
            ':id' => $notifId,
            ':uid' => Auth::id()
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    // Mark all as read
    public function markAllRead(): void
    {
        $db = Database::getInstance();
        $db->query("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0", [':uid' => Auth::id()]);

        echo json_encode(['success' => true]);
        exit;
    }

    // Check for activity reminders and send notifications
    private function checkActivityReminders(Database $db, int $userId): void
    {
        try {
            $now = date('Y-m-d H:i:s');
            $soon = date('Y-m-d H:i:s', time() + 15 * 60);
            $recentlyPassed = date('Y-m-d H:i:s', time() - 60 * 60);

            $activities = $db->fetchAll(
                "SELECT da.*, d.title as deal_title 
                 FROM deal_activities da
                 JOIN deals d ON da.deal_id = d.id
                 WHERE da.is_done = 0 
                 AND da.activity_date IS NOT NULL
                 AND da.activity_date >= :recent
                 AND da.activity_date <= :soon
                 AND da.user_id = :uid",
                [':recent' => $recentlyPassed, ':soon' => $soon, ':uid' => $userId]
            );

            $typeNames = ['call' => 'تماس', 'meeting' => 'جلسه', 'sms' => 'پیامک', 'email' => 'ایمیل', 'follow_up' => 'پیگیری', 'note' => 'یادداشت'];

            foreach ($activities as $act) {
                // Check if we already sent a notification for this activity recently
                $existing = $db->fetch(
                    "SELECT id FROM notifications WHERE user_id = :uid AND link LIKE :link AND created_at > :since",
                    [':uid' => $userId, ':link' => '%/deals/view/' . $act->deal_id, ':since' => date('Y-m-d H:i:s', time() - 30 * 60)]
                );
                if ($existing) continue;

                $isOverdue = strtotime($act->activity_date) < time();
                $title = $isOverdue ? '⏰ فعالیت سررسید گذشته' : '⏰ یادآوری فعالیت';
                $typeName = $typeNames[$act->type] ?? $act->type;
                $message = $typeName . ': ' . ($act->subject ?? 'فعالیت') . "\n";
                $message .= 'معامله: ' . ($act->deal_title ?? '-') . "\n";
                $message .= 'زمان: ' . \Core\JDate::displayDateTime($act->activity_date);

                $db->insert('notifications', [
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'type' => $isOverdue ? 'warning' : 'info',
                    'link' => '/deals/view/' . $act->deal_id,
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}
