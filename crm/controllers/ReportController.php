<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\View;

class ReportController
{
    public function index(): void
    {
        $db = Database::getInstance();
        
        // Summary statistics
        $totalDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_lost = 0");
        $wonDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_won = 1");
        $conversionRate = $totalDeals->count > 0 ? round(($wonDeals->count / $totalDeals->count) * 100) : 0;
        
        // Monthly sales
        $monthlySales = $db->fetchAll(
            "SELECT DATE_FORMAT(closed_at, '%Y-%m') as month, 
                    COUNT(*) as deals_count, 
                    COALESCE(SUM(amount), 0) as total
             FROM deals 
             WHERE is_won = 1 AND closed_at IS NOT NULL
             GROUP BY DATE_FORMAT(closed_at, '%Y-%m')
             ORDER BY month DESC LIMIT 12"
        );

        // Sales by pipeline
        $salesByPipeline = $db->fetchAll(
            "SELECT p.name, COUNT(d.id) as deals_count, COALESCE(SUM(d.amount), 0) as total
             FROM pipelines p
             LEFT JOIN deals d ON d.pipeline_id = p.id AND d.is_won = 1
             GROUP BY p.id, p.name"
        );

        // Top performing users
        $topUsers = $db->fetchAll(
            "SELECT u.full_name, COUNT(d.id) as deals_count, COALESCE(SUM(d.amount), 0) as total
             FROM users u
             JOIN deals d ON d.assigned_to = u.id AND d.is_won = 1
             GROUP BY u.id, u.full_name
             ORDER BY total DESC LIMIT 10"
        );

        // Lost reasons
        $lostReasons = $db->fetchAll(
            "SELECT lost_reason, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
             FROM deals 
             WHERE is_lost = 1 AND lost_reason IS NOT NULL AND lost_reason != ''
             GROUP BY lost_reason
             ORDER BY count DESC"
        );

        // Lost deals
        $lostDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_lost = 1");
        $openDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_won = 0 AND is_lost = 0");

        // Payment stats
        $paymentStats = $db->fetch("SELECT COUNT(*) as total, SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as successful, COALESCE(SUM(CASE WHEN status='success' THEN amount ELSE 0 END), 0) as total_paid FROM payments");

        // SMS stats
        $smsStats = $db->fetch("SELECT COUNT(*) as total, SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) as sent FROM sms_history");

        // Contacts stats
        $contactStats = $db->fetch("SELECT COUNT(*) as total FROM contacts");

        // Deal sources breakdown
        $dealSources = $db->fetchAll(
            "SELECT source, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
             FROM deals 
             WHERE source IS NOT NULL AND source != ''
             GROUP BY source 
             ORDER BY count DESC"
        );

        // Contact categories breakdown
        $contactCategories = $db->fetchAll(
            "SELECT cc.name, cc.color, COUNT(c.id) as count
             FROM contact_categories cc
             LEFT JOIN contacts c ON c.category_id = cc.id
             GROUP BY cc.id, cc.name, cc.color
             ORDER BY count DESC"
        );

        // Today's stats
        $todayDeals = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE DATE(created_at) = CURDATE()");
        $todayWon = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM deals WHERE is_won = 1 AND DATE(closed_at) = CURDATE()");

        // Stage distribution (all deals)
        $stageDistribution = $db->fetchAll(
            "SELECT s.name, s.color, COUNT(d.id) as count, COALESCE(SUM(d.amount), 0) as total
             FROM stages s
             LEFT JOIN deals d ON d.stage_id = s.id
             WHERE s.is_active = 1
             GROUP BY s.id, s.name, s.color
             ORDER BY s.order_index"
        );

        // Weekly deals trend (last 12 weeks)
        $weeklyTrend = $db->fetchAll(
            "SELECT YEARWEEK(created_at, 1) as week, 
                    MIN(DATE(created_at)) as week_start,
                    COUNT(*) as total_deals,
                    SUM(CASE WHEN is_won = 1 THEN 1 ELSE 0 END) as won,
                    SUM(CASE WHEN is_lost = 1 THEN 1 ELSE 0 END) as lost,
                    COALESCE(SUM(amount), 0) as total_amount
             FROM deals
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
             GROUP BY YEARWEEK(created_at, 1)
             ORDER BY week"
        );

        // Travel stats
        $travelStats = $db->fetch(
            "SELECT COUNT(*) as total, 
                    COALESCE(SUM(passengers_count), 0) as total_passengers,
                    COALESCE(AVG(passengers_count), 0) as avg_passengers
             FROM deals WHERE travel_date_from IS NOT NULL"
        );

        // Activity stats (last 30 days)
        $activityStats = $db->fetchAll(
            "SELECT type, COUNT(*) as count
             FROM deal_activities
             WHERE activity_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY type ORDER BY count DESC"
        );

        // Average deal amount
        $avgDealAmount = $db->fetch("SELECT COALESCE(AVG(amount), 0) as avg_amount FROM deals WHERE amount > 0");

        // Deals by month (last 6 months - created)
        $dealsByMonth = $db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as created,
                    SUM(CASE WHEN is_won = 1 THEN 1 ELSE 0 END) as won,
                    SUM(CASE WHEN is_lost = 1 THEN 1 ELSE 0 END) as lost,
                    SUM(CASE WHEN is_won = 0 AND is_lost = 0 THEN 1 ELSE 0 END) as open_deals
             FROM deals
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month"
        );

        // Top contacts by deal count
        $topContacts = $db->fetchAll(
            "SELECT c.full_name, c.phone, COUNT(d.id) as deals_count, COALESCE(SUM(d.amount), 0) as total_amount
             FROM contacts c
             JOIN deals d ON d.contact_id = c.id
             GROUP BY c.id, c.full_name, c.phone
             ORDER BY deals_count DESC LIMIT 10"
        );

        View::render('reports/index', [
            'title' => 'گزارشات و تحلیل‌ها',
            'totalDeals' => $totalDeals,
            'wonDeals' => $wonDeals,
            'lostDeals' => $lostDeals,
            'openDeals' => $openDeals,
            'conversionRate' => $conversionRate,
            'monthlySales' => $monthlySales,
            'salesByPipeline' => $salesByPipeline,
            'topUsers' => $topUsers,
            'lostReasons' => $lostReasons,
            'paymentStats' => $paymentStats,
            'smsStats' => $smsStats,
            'contactStats' => $contactStats,
            'dealSources' => $dealSources,
            'contactCategories' => $contactCategories,
            'todayDeals' => $todayDeals,
            'todayWon' => $todayWon,
            'stageDistribution' => $stageDistribution,
            'weeklyTrend' => $weeklyTrend,
            'travelStats' => $travelStats,
            'activityStats' => $activityStats,
            'avgDealAmount' => $avgDealAmount,
            'dealsByMonth' => $dealsByMonth,
            'topContacts' => $topContacts,
        ]);
    }

    public function sales(): void
    {
        $db = Database::getInstance();
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $pipelineId = $_GET['pipeline_id'] ?? '';

        $where = "WHERE d.created_at >= :date_from AND d.created_at <= :date_to";
        $params = [':date_from' => $dateFrom . ' 00:00:00', ':date_to' => $dateTo . ' 23:59:59'];

        if ($pipelineId) {
            $where .= " AND d.pipeline_id = :pipeline_id";
            $params[':pipeline_id'] = $pipelineId;
        }

        $deals = $db->fetchAll(
            "SELECT d.*, s.name as stage_name, c.full_name as contact_name, 
                    u.full_name as assigned_name, p.name as pipeline_name
             FROM deals d 
             JOIN stages s ON d.stage_id = s.id 
             JOIN pipelines p ON d.pipeline_id = p.id 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             LEFT JOIN users u ON d.assigned_to = u.id 
             {$where}
             ORDER BY d.created_at DESC",
            $params
        );

        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");

        View::render('reports/sales', [
            'title' => 'گزارش فروش',
            'deals' => $deals,
            'pipelines' => $pipelines,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedPipeline' => $pipelineId,
        ]);
    }

    public function pipeline(): void
    {
        $db = Database::getInstance();
        
        $pipelineAnalysis = $db->fetchAll(
            "SELECT p.name as pipeline_name, s.name as stage_name, s.color,
                    COUNT(d.id) as deals_count, COALESCE(SUM(d.amount), 0) as total_amount
             FROM pipelines p
             CROSS JOIN stages s ON s.pipeline_id = p.id
             LEFT JOIN deals d ON d.stage_id = s.id AND d.is_lost = 0
             WHERE p.is_active = 1 AND s.is_active = 1
             GROUP BY p.id, p.name, s.id, s.name, s.color, s.order_index
             ORDER BY p.name, s.order_index"
        );

        $pipelineData = [];
        foreach ($pipelineAnalysis as $row) {
            $pipelineData[$row->pipeline_name][] = $row;
        }

        View::render('reports/pipeline', [
            'title' => 'گزارش پایپ لاین',
            'pipelineData' => $pipelineData,
        ]);
    }

    public function toggleActivity(array $params): void
    {
        $db = Database::getInstance();
        $activity = $db->fetch("SELECT id, is_done FROM deal_activities WHERE id = :id", [':id' => $params['id']]);
        if ($activity) {
            $newStatus = $activity->is_done ? 0 : 1;
            $db->update('deal_activities', ['is_done' => $newStatus], 'id = :id', [':id' => $params['id']]);
            
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
            if ($isAjax) {
                echo json_encode(['success' => true, 'is_done' => $newStatus]);
                exit;
            }
        }
        View::redirect('/activities');
    }

    public function activities(): void
    {
        $db = Database::getInstance();
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $userId = $_GET['user_id'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $isAdmin = Auth::hasPermission('settings.manage');

        $where = "WHERE da.activity_date >= :date_from AND da.activity_date <= :date_to";
        $params = [':date_from' => $dateFrom . ' 00:00:00', ':date_to' => $dateTo . ' 23:59:59'];

        // Non-admin users can only see their own activities
        if (!$isAdmin) {
            $where .= " AND da.user_id = :current_user_id";
            $params[':current_user_id'] = Auth::id();
        } elseif ($userId) {
            $where .= " AND da.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        if ($type) {
            $where .= " AND da.type = :type";
            $params[':type'] = $type;
        }
        if ($status === 'done') {
            $where .= " AND da.is_done = 1";
        } elseif ($status === 'pending') {
            $where .= " AND da.is_done = 0";
        } elseif ($status === 'overdue') {
            $where .= " AND da.is_done = 0 AND da.activity_date < NOW()";
        }

        $activities = $db->fetchAll(
            "SELECT da.*, u.full_name as user_name, d.title as deal_title, d.id as deal_id,
                    c.full_name as contact_name, c.phone as contact_phone,
                    s.name as stage_name, s.color as stage_color
             FROM deal_activities da 
             LEFT JOIN users u ON da.user_id = u.id 
             LEFT JOIN deals d ON da.deal_id = d.id 
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN stages s ON d.stage_id = s.id
             {$where}
             ORDER BY da.activity_date DESC",
            $params
        );

        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");

        // Activity summary by type
        $activitySummary = $db->fetchAll(
            "SELECT da.type, COUNT(*) as count
             FROM deal_activities da
             {$where}
             GROUP BY da.type
             ORDER BY count DESC",
            $params
        );

        // Stats (filtered by user for non-admin)
        $statsUserFilter = !$isAdmin ? " AND user_id = :stats_uid" : "";
        $statsParams = !$isAdmin ? [':stats_uid' => Auth::id()] : [];
        $overdueCount = $db->fetch("SELECT COUNT(*) as cnt FROM deal_activities WHERE is_done = 0 AND activity_date < NOW() {$statsUserFilter}", $statsParams);
        $todayCount = $db->fetch("SELECT COUNT(*) as cnt FROM deal_activities WHERE DATE(activity_date) = CURDATE() {$statsUserFilter}", $statsParams);
        $doneTodayCount = $db->fetch("SELECT COUNT(*) as cnt FROM deal_activities WHERE is_done = 1 AND DATE(activity_date) = CURDATE() {$statsUserFilter}", $statsParams);
        $upcomingCount = $db->fetch("SELECT COUNT(*) as cnt FROM deal_activities WHERE is_done = 0 AND activity_date > NOW() AND activity_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) {$statsUserFilter}", $statsParams);

        // Pipelines for filter
        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");

        View::render('activities/index', [
            'title' => 'مدیریت فعالیت‌ها',
            'activities' => $activities,
            'users' => $users,
            'activitySummary' => $activitySummary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedUser' => $userId,
            'selectedType' => $type,
            'selectedStatus' => $status,
            'overdueCount' => (int)($overdueCount->cnt ?? 0),
            'todayCount' => (int)($todayCount->cnt ?? 0),
            'doneTodayCount' => (int)($doneTodayCount->cnt ?? 0),
            'upcomingCount' => (int)($upcomingCount->cnt ?? 0),
            'pipelines' => $pipelines,
        ]);
    }

    public function contacts(): void
    {
        $db = Database::getInstance();
        
        $contacts = $db->fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM deals WHERE contact_id = c.id) as deals_count,
                    (SELECT COUNT(*) FROM deals WHERE contact_id = c.id AND is_won = 1) as won_deals,
                    (SELECT COALESCE(SUM(amount), 0) FROM deals WHERE contact_id = c.id AND is_won = 1) as total_purchases,
                    (SELECT MAX(created_at) FROM deals WHERE contact_id = c.id) as last_deal_date
             FROM contacts c
             ORDER BY total_purchases DESC"
        );

        // Source analysis
        $sources = $db->fetchAll(
            "SELECT source, COUNT(*) as count 
             FROM contacts 
             WHERE source IS NOT NULL AND source != ''
             GROUP BY source 
             ORDER BY count DESC"
        );

        View::render('reports/contacts', [
            'title' => 'گزارش مخاطبان',
            'contacts' => $contacts,
            'sources' => $sources,
        ]);
    }
}