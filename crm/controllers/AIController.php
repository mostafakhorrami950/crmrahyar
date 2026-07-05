<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;

class AIController
{
    public function analyze(): void
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            Auth::requireAuth();
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'لطفاً ابتدا وارد شوید']);
            exit;
        }
        
        $config = $GLOBALS['app_config'];
        $db = Database::getInstance();
        
        $apiKey = $config['openrouter']['api_key'] ?? '';
        $model = $config['openrouter']['model'] ?? '~openai/gpt-latest';
        
        try {
            $apiKeySetting = $db->fetch("SELECT setting_value FROM settings WHERE setting_key='openrouter_api_key' AND setting_group='ai'");
            $modelSetting = $db->fetch("SELECT setting_value FROM settings WHERE setting_key='openrouter_model' AND setting_group='ai'");
            if ($apiKeySetting && !empty($apiKeySetting->setting_value)) $apiKey = $apiKeySetting->setting_value;
            if ($modelSetting && !empty($modelSetting->setting_value)) $model = $modelSetting->setting_value;
        } catch (\Exception $e) { }

        // Auto-create ai_analyses table
        try {
            $db->query("CREATE TABLE IF NOT EXISTS `ai_analyses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `model` VARCHAR(100),
                `prompt_summary` TEXT,
                `result` LONGTEXT,
                `deals_count` INT DEFAULT 0,
                `total_amount` DECIMAL(15,2) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) { }

        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'کلید API هوش مصنوعی تنظیم نشده. لطفاً از بخش تنظیمات آن را وارد کنید.']);
            exit;
        }

        // Get selected data categories from POST
        $categories = $_POST['categories'] ?? 'deals,stages,activities,users';
        $selectedCats = is_array($categories) ? $categories : explode(',', $categories);
        
        // Date range filter
        $dateFrom = trim($_POST['date_from'] ?? '');
        $dateTo = trim($_POST['date_to'] ?? '');
        $dateFilter = '';
        $dateFilterPlain = '';
        if ($dateFrom && $dateTo) {
            $dateFilter = " AND d.created_at >= :date_from AND d.created_at <= :date_to ";
            $dateFilterPlain = " AND created_at >= :date_from AND created_at <= :date_to ";
        } elseif ($dateFrom) {
            $dateFilter = " AND d.created_at >= :date_from ";
            $dateFilterPlain = " AND created_at >= :date_from ";
        } elseif ($dateTo) {
            $dateFilter = " AND d.created_at <= :date_to ";
            $dateFilterPlain = " AND created_at <= :date_to ";
        }
        $dateParams = [];
        if ($dateFrom) $dateParams[':date_from'] = $dateFrom . ' 00:00:00';
        if ($dateTo) $dateParams[':date_to'] = $dateTo . ' 23:59:59';
        
        try {
            $userId = Auth::id();
            $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
            $sw = $isAdmin ? $dateFilter : " AND (d.assigned_to = :uid OR d.created_by = :uid2)" . $dateFilter;
            $sp = $isAdmin ? $dateParams : array_merge([':uid' => $userId, ':uid2' => $userId], $dateParams);
            $swPlain = $dateFilterPlain;
            $spPlain = $dateParams;

            $dealsTotal = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals d WHERE d.is_lost=0" . $sw, $sp);
            $dealsWon = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals d WHERE d.is_won=1" . $sw, $sp);
            $dealsLost = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals d WHERE d.is_lost=1" . $sw, $sp);
            $dealsActive = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals d WHERE d.is_lost=0 AND d.is_won=0" . $sw, $sp);

            // Build prompt based on selected categories
            $p = "تو یک تحلیلگر ارشد CRM و فروش با بیش از ۱۵ سال تجربه در صنعت گردشگری و آژانس‌های مسافرتی هستی. اطلاعات دقیق زیر از سیستم CRM یک آژانس مسافرتی فعال استخراج شده. وظیفه تو تحلیل عمیق، حرفه‌ای و عملیاتی این اطلاعات و ارائه گزارش مدیریتی جامع به فارسی است.\n\n";
            $p .= "تاریخ گزارش: " . \Core\JDate::displayDate(date('Y-m-d')) . "\n";
            $p .= "نوع کسب‌وکار: آژانس مسافرتی (فروش تور، رزرو هتل، بلیط هواپیما)\n\n";
            
            // Always include basic deal stats
            $winRate = $dealsTotal->c > 0 ? round(($dealsWon->c / $dealsTotal->c) * 100, 1) : 0;
            $avgDeal = $dealsTotal->c > 0 ? round($dealsTotal->t / $dealsTotal->c) : 0;
            $avgWonDeal = $dealsWon->c > 0 ? round($dealsWon->t / $dealsWon->c) : 0;
            $conversionRate = $dealsTotal->c > 0 ? round(($dealsActive->c / $dealsTotal->c) * 100, 1) : 0;
            $p .= "═══════════════════════════════════════\n";
            $p .= "📊 بخش ۱: آمار کلی معاملات\n";
            $p .= "═══════════════════════════════════════\n";
            $p .= "▸ کل معاملات ثبت شده: {$dealsTotal->c}\n";
            $p .= "▸ مجموع ارزش معاملات: " . number_format($dealsTotal->t) . " تومان\n";
            $p .= "▸ میانگین ارزش هر معامله: " . number_format($avgDeal) . " تومان\n";
            $p .= "▸ معاملات موفق: {$dealsWon->c} (" . number_format($dealsWon->t) . " تومان)\n";
            $p .= "  └─ میانگین ارزش معامله موفق: " . number_format($avgWonDeal) . " تومان\n";
            $p .= "▸ معاملات ناموفق: {$dealsLost->c} (" . number_format($dealsLost->t) . " تومان)\n";
            $p .= "▸ معاملات در جریان: {$dealsActive->c} (" . number_format($dealsActive->t) . " تومان)\n";
            $p .= "▸ نرخ برد (Win Rate): {$winRate}%\n";
            $p .= "▸ نرخ تبدیل (موفق+ناموفق به کل): {$conversionRate}%\n\n";

            // Stages
            if (in_array('stages', $selectedCats)) {
                $dealsByStage = $db->fetchAll(
                    "SELECT s.name, COUNT(d.id) as cnt, COALESCE(SUM(d.amount),0) as tot,
                            SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END) as won_in_stage,
                            SUM(CASE WHEN d.is_lost=1 THEN 1 ELSE 0 END) as lost_in_stage
                     FROM stages s LEFT JOIN deals d ON d.stage_id=s.id" . ($isAdmin ? '' : " AND (d.assigned_to=:uid OR d.created_by=:uid2)") . "
                     WHERE s.is_active=1 GROUP BY s.id,s.name ORDER BY s.order_index", $sp
                );
                $p .= "═══════════════════════════════════════\n";
                $p .= "📋 بخش ۲: تحلیل مراحل فروش\n";
                $p .= "═══════════════════════════════════════\n";
                foreach ($dealsByStage as $s) {
                    $stageConvRate = $s->cnt > 0 ? round(($s->won_in_stage / $s->cnt) * 100, 1) : 0;
                    $p .= "▸ {$s->name}:\n";
                    $p .= "  تعداد: {$s->cnt} | مبلغ: " . number_format($s->tot) . " ت\n";
                    $p .= "  موفق: {$s->won_in_stage} | ناموفق: {$s->lost_in_stage} | نرخ تبدیل: {$stageConvRate}%\n";
                }
                $p .= "\n";
                // Funnel analysis
                $totalStageDeals = array_sum(array_column($dealsByStage, 'cnt'));
                if ($totalStageDeals > 0) {
                    $p .= "🔹 تحلیل قیف فروش:\n";
                    foreach ($dealsByStage as $i => $s) {
                        $pctOfTotal = $totalStageDeals > 0 ? round(($s->cnt / $totalStageDeals) * 100, 1) : 0;
                        $p .= "  {$s->name}: {$pctOfTotal}% از کل\n";
                    }
                }
                $p .= "\n";
            }

            // Sources
            if (in_array('sources', $selectedCats)) {
                $dealsBySource = $db->fetchAll(
                    "SELECT COALESCE(source,'نامشخص') as src, COUNT(*) as cnt, COALESCE(SUM(amount),0) as tot,
                            SUM(CASE WHEN is_won=1 THEN 1 ELSE 0 END) as won_cnt,
                            SUM(CASE WHEN is_won=1 THEN amount ELSE 0 END) as won_tot
                     FROM deals WHERE 1=1" . $sw . " GROUP BY source ORDER BY cnt DESC", $sp
                );
                $p .= "═══════════════════════════════════════\n";
                $p .= "🔗 بخش ۳: تحلیل منابع ورودی\n";
                $p .= "═══════════════════════════════════════\n";
                foreach ($dealsBySource as $s) {
                    $srcConvRate = $s->cnt > 0 ? round(($s->won_cnt / $s->cnt) * 100, 1) : 0;
                    $p .= "▸ {$s->src}:\n";
                    $p .= "  کل: {$s->cnt} (" . number_format($s->tot) . " ت)\n";
                    $p .= "  موفق: {$s->won_cnt} (" . number_format($s->won_tot) . " ت)\n";
                    $p .= "  نرخ تبدیل: {$srcConvRate}%\n";
                }
                $p .= "\n";
            }

            // Weekly trend
            if (in_array('trends', $selectedCats)) {
                $weeklyTrend = [];
                for ($w = 3; $w >= 0; $w--) {
                    $ws = date('Y-m-d', strtotime("-{$w} weeks monday"));
                    $we = date('Y-m-d', strtotime("-{$w} weeks sunday"));
                    $wd = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE created_at>=:s AND created_at<=:e" . $sw,
                        array_merge([':s' => $ws.' 00:00:00', ':e' => $we.' 23:59:59'], $sp));
                    $weeklyTrend[] = ['week' => $ws.' to '.$we, 'count' => (int)$wd->c, 'total' => (int)$wd->t];
                }
                $p .= "═══════════════════════════════════════\n";
                $p .= "📈 بخش ۴: تحلیل روندها\n";
                $p .= "═══════════════════════════════════════\n";
                $p .= "▸ روند هفتگی (۴ هفته اخیر):\n";
                foreach ($weeklyTrend as $w) $p .= "  {$w['week']}: {$w['count']} معامله (" . number_format($w['total']) . " ت)\n";
                // Calculate weekly growth
                if (count($weeklyTrend) >= 2) {
                    $prevWeek = $weeklyTrend[count($weeklyTrend)-2]['count'];
                    $currWeek = $weeklyTrend[count($weeklyTrend)-1]['count'];
                    $weekGrowth = $prevWeek > 0 ? round((($currWeek - $prevWeek) / $prevWeek) * 100, 1) : 0;
                    $p .= "  └─ رشد هفته جاری نسبت به هفته قبل: {$weekGrowth}%\n";
                }
                $p .= "\n";

                $dailyTrend = [];
                for ($d = 6; $d >= 0; $d--) {
                    $day = date('Y-m-d', strtotime("-{$d} days"));
                    $dd = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE DATE(created_at)=:d" . $sw,
                        array_merge([':d' => $day], $sp));
                    $dailyTrend[] = ['date' => $day, 'count' => (int)$dd->c, 'total' => (int)$dd->t];
                }
                $p .= "▸ روند روزانه (۷ روز اخیر):\n";
                foreach ($dailyTrend as $d) $p .= "  {$d['date']}: {$d['count']} معامله (" . number_format($d['total']) . " ت)\n";
                $p .= "\n";
            }

            // Pipelines
            if (in_array('pipelines', $selectedCats)) {
                $pipePerf = $db->fetchAll(
                    "SELECT p.name, COUNT(d.id) as td,
                            SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END) as w,
                            SUM(CASE WHEN d.is_lost=1 THEN 1 ELSE 0 END) as l,
                            COALESCE(SUM(d.amount),0) as ta,
                            COALESCE(SUM(CASE WHEN d.is_won=1 THEN d.amount ELSE 0 END),0) as wa
                     FROM pipelines p LEFT JOIN deals d ON d.pipeline_id=p.id" . ($isAdmin ? '' : " AND (d.assigned_to=:uid OR d.created_by=:uid2)") . "
                     WHERE p.is_active=1 GROUP BY p.id,p.name"
                );
                $p .= "═══════════════════════════════════════\n";
                $p .= "🔀 بخش ۵: تحلیل پایپ‌لاین‌ها\n";
                $p .= "═══════════════════════════════════════\n";
                foreach ($pipePerf as $pp) {
                    $ppConvRate = $pp->td > 0 ? round(($pp->w / $pp->td) * 100, 1) : 0;
                    $p .= "▸ {$pp->name}:\n";
                    $p .= "  کل: {$pp->td} | موفق: {$pp->w} | ناموفق: {$pp->l}\n";
                    $p .= "  مجموع ارزش: " . number_format($pp->ta) . " ت\n";
                    $p .= "  ارزش موفق: " . number_format($pp->wa) . " ت\n";
                    $p .= "  نرخ تبدیل: {$ppConvRate}%\n";
                }
                $p .= "\n";
            }

            // Users
            if (in_array('users', $selectedCats)) {
                $userPerf = $db->fetchAll(
                    "SELECT u.full_name, COUNT(d.id) as td,
                            SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END) as w,
                            COALESCE(SUM(CASE WHEN d.is_won=1 THEN d.amount ELSE 0 END),0) as wa,
                            COUNT(CASE WHEN d.created_at>=DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as lw
                     FROM users u LEFT JOIN deals d ON d.assigned_to=u.id
                     WHERE u.is_active=1 GROUP BY u.id,u.full_name ORDER BY wa DESC"
                );
                $p .= "═══════════════════════════════════════\n";
                $p .= "👥 بخش ۶: عملکرد تیم فروش\n";
                $p .= "═══════════════════════════════════════\n";
                foreach ($userPerf as $u) {
                    $uConvRate = $u->td > 0 ? round(($u->w / $u->td) * 100, 1) : 0;
                    $uAvgDeal = $u->td > 0 ? round($u->wa / max($u->w, 1)) : 0;
                    $p .= "▸ {$u->full_name}:\n";
                    $p .= "  کل معاملات: {$u->td} | موفق: {$u->w} | نرخ تبدیل: {$uConvRate}%\n";
                    $p .= "  مبلغ فروش موفق: " . number_format($u->wa) . " ت\n";
                    $p .= "  میانگین ارزش معامله موفق: " . number_format($uAvgDeal) . " ت\n";
                    $p .= "  فعالیت هفته اخیر: {$u->lw} معامله جدید\n";
                }
                $p .= "\n";
            }

            // Activities
            if (in_array('activities', $selectedCats)) {
                $actSum = $db->fetchAll(
                    "SELECT type, COUNT(*) as cnt, SUM(CASE WHEN is_done=1 THEN 1 ELSE 0 END) as done
                     FROM deal_activities WHERE created_at>=:s" . ($isAdmin ? '' : " AND user_id=:uid") . " GROUP BY type",
                    $isAdmin ? [':s' => date('Y-m-d', strtotime('-30 days'))] : [':s' => date('Y-m-d', strtotime('-30 days')), ':uid' => $userId]
                );
                $overdue = $db->fetch("SELECT COUNT(*) as c FROM deal_activities WHERE is_done=0 AND activity_date<NOW()" . ($isAdmin ? '' : " AND user_id=:uid"),
                    $isAdmin ? [] : [':uid' => $userId]);
                $totalActs = $db->fetch("SELECT COUNT(*) as c FROM deal_activities WHERE created_at>=:s" . ($isAdmin ? '' : " AND user_id=:uid"),
                    $isAdmin ? [':s' => date('Y-m-d', strtotime('-30 days'))] : [':s' => date('Y-m-d', strtotime('-30 days')), ':uid' => $userId]);
                $totalDone = $db->fetch("SELECT COUNT(*) as c FROM deal_activities WHERE is_done=1 AND created_at>=:s" . ($isAdmin ? '' : " AND user_id=:uid"),
                    $isAdmin ? [':s' => date('Y-m-d', strtotime('-30 days'))] : [':s' => date('Y-m-d', strtotime('-30 days')), ':uid' => $userId]);
                $actDoneRate = $totalActs->c > 0 ? round(($totalDone->c / $totalActs->c) * 100, 1) : 0;
                $p .= "═══════════════════════════════════════\n";
                $p .= "📅 بخش ۷: تحلیل فعالیت‌ها (۳۰ روز اخیر)\n";
                $p .= "═══════════════════════════════════════\n";
                $activityTypeNames = ['note'=>'یادداشت','call'=>'تماس تلفنی','meeting'=>'جلسه','email'=>'ایمیل','sms'=>'پیامک','follow_up'=>'پیگیری','other'=>'سایر'];
                foreach ($actSum as $a) {
                    $typeName = $activityTypeNames[$a->type] ?? $a->type;
                    $doneRate = $a->cnt > 0 ? round(($a->done / $a->cnt) * 100, 1) : 0;
                    $p .= "▸ {$typeName}: {$a->cnt} مورد (انجام شده: {$a->done} | نرخ انجام: {$doneRate}%)\n";
                }
                $p .= "▸ کل فعالیت‌ها: {$totalActs->c} | انجام شده: {$totalDone->c} | نرخ انجام: {$actDoneRate}%\n";
                $p .= "▸ سررسید گذشته (عقب‌افتاده): {$overdue->c} مورد\n";
                if ($overdue->c > 0) {
                    $p .= "  ⚠️ توجه: {$overdue->c} فعالیت سررسید گذشته نیاز به پیگیری فوری دارد.\n";
                }
                $p .= "\n";
            }

            // Loss reasons
            if (in_array('loss_reasons', $selectedCats)) {
                $lossReasons = $db->fetchAll(
                    "SELECT COALESCE(dlr.name, d.lost_reason, 'نامشخص') as r, COUNT(*) as c, COALESCE(SUM(d.amount),0) as t
                     FROM deals d LEFT JOIN deal_loss_reasons dlr ON d.loss_reason_id=dlr.id WHERE d.is_lost=1" . $sw . " GROUP BY r ORDER BY c DESC LIMIT 10", $sp
                );
                $p .= "═══════════════════════════════════════\n";
                $p .= "❌ بخش ۸: تحلیل دلایل عدم موفقیت\n";
                $p .= "═══════════════════════════════════════\n";
                foreach ($lossReasons as $i => $r) {
                    $lossPct = $dealsLost->c > 0 ? round(($r->c / $dealsLost->c) * 100, 1) : 0;
                    $p .= "▸ " . ($i+1) . ". {$r->r}: {$r->c} مورد ({$lossPct}% از کل باخت‌ها)\n";
                    $p .= "   مبلغ از دست رفته: " . number_format($r->t) . " تومان\n";
                }
                $p .= "\n";
            }

            // Contacts
            if (in_array('contacts', $selectedCats)) {
                $contactsTotal = $db->fetch("SELECT COUNT(*) as c FROM contacts" . ($dateFrom ? " WHERE created_at>=:date_from" : "") . ($dateTo ? ($dateFrom ? " AND" : " WHERE") . " created_at<=:date_to" : ""), $dateParams);
                $contactsWeek = $db->fetch("SELECT COUNT(*) as c FROM contacts WHERE created_at>=DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $contactsMonth = $db->fetch("SELECT COUNT(*) as c FROM contacts WHERE created_at>=DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $contactsWithDeals = $db->fetch("SELECT COUNT(DISTINCT contact_id) as c FROM deals WHERE contact_id IS NOT NULL");
                $p .= "═══════════════════════════════════════\n";
                $p .= "👤 بخش ۹: تحلیل مخاطبان\n";
                $p .= "═══════════════════════════════════════\n";
                $p .= "▸ کل مخاطبان: {$contactsTotal->c}\n";
                $p .= "▸ جدید در ۷ روز اخیر: {$contactsWeek->c}\n";
                $p .= "▸ جدید در ۳۰ روز اخیر: {$contactsMonth->c}\n";
                $p .= "▸ مخاطبان دارای معامله: {$contactsWithDeals->c}\n";
                $contactConvRate = $contactsTotal->c > 0 ? round(($contactsWithDeals->c / $contactsTotal->c) * 100, 1) : 0;
                $p .= "▸ نرخ تبدیل مخاطب به معامله: {$contactConvRate}%\n";
                $p .= "\n";
            }

            // Win reasons
            if (in_array('win_reasons', $selectedCats)) {
                $winReasons = $db->fetchAll(
                    "SELECT COALESCE(dwr.name, d.win_reason_note, 'نامشخص') as r, COUNT(*) as c, COALESCE(SUM(d.amount),0) as t
                     FROM deals d LEFT JOIN deal_win_reasons dwr ON d.win_reason_id=dwr.id WHERE d.is_won=1" . $sw . " GROUP BY r ORDER BY c DESC LIMIT 10", $sp
                );
                $p .= "═══════════════════════════════════════\n";
                $p .= "🏆 بخش ۱۰: تحلیل دلایل موفقیت\n";
                $p .= "═══════════════════════════════════════\n";
                foreach ($winReasons as $i => $r) {
                    $winPct = $dealsWon->c > 0 ? round(($r->c / $dealsWon->c) * 100, 1) : 0;
                    $p .= "▸ " . ($i+1) . ". {$r->r}: {$r->c} مورد ({$winPct}% از کل موفقیت‌ها)\n";
                    $p .= "   مبلغ کسب شده: " . number_format($r->t) . " تومان\n";
                }
                $p .= "\n";
            }

            // Hotel Invoices
            if (in_array('hotel_invoices', $selectedCats)) {
                try {
                    $hotelInvoiceStats = $db->fetch(
                        "SELECT COUNT(*) as total, 
                                COALESCE(SUM(final_amount),0) as total_amount,
                                COALESCE(SUM(discount_amount),0) as total_discount,
                                COALESCE(SUM(deposit_amount),0) as total_deposit,
                                SUM(CASE WHEN invoice_status='paid' OR invoice_status='settled' THEN 1 ELSE 0 END) as paid_count,
                                SUM(CASE WHEN invoice_status='paid' OR invoice_status='settled' THEN final_amount ELSE 0 END) as paid_amount,
                                SUM(CASE WHEN invoice_status='prepaid' THEN 1 ELSE 0 END) as unpaid_count,
                                SUM(CASE WHEN invoice_status='prepaid' THEN final_amount ELSE 0 END) as unpaid_amount,
                                SUM(CASE WHEN invoice_status='pending' THEN 1 ELSE 0 END) as pending_count,
                                SUM(CASE WHEN invoice_status='pending' THEN final_amount ELSE 0 END) as pending_amount,
                                SUM(CASE WHEN invoice_status='pending' THEN deposit_amount ELSE 0 END) as pending_deposit
                         FROM hotel_invoices"
                    );
                    $hotelInvoiceByHotel = $db->fetchAll(
                        "SELECT hotel_name, COUNT(*) as cnt, COALESCE(SUM(final_amount),0) as tot,
                                SUM(CASE WHEN invoice_status='paid' OR invoice_status='settled' THEN 1 ELSE 0 END) as paid,
                                SUM(CASE WHEN invoice_status='pending' THEN 1 ELSE 0 END) as has_remainder
                         FROM hotel_invoices GROUP BY hotel_name ORDER BY tot DESC LIMIT 10"
                    );
                    $hotelInvoiceByType = $db->fetchAll(
                        "SELECT invoice_type, COUNT(*) as cnt, COALESCE(SUM(final_amount),0) as tot
                         FROM hotel_invoices GROUP BY invoice_type"
                    );
                    $hotelInvoiceByStatus = $db->fetchAll(
                        "SELECT invoice_status, COUNT(*) as cnt, COALESCE(SUM(final_amount),0) as tot
                         FROM hotel_invoices GROUP BY invoice_status"
                    );
                    // Calculate collection rate
                    $collectRate = $hotelInvoiceStats->total_amount > 0 ? round(($hotelInvoiceStats->paid_amount / $hotelInvoiceStats->total_amount) * 100, 1) : 0;
                    $remainderAmount = $hotelInvoiceStats->pending_amount - $hotelInvoiceStats->pending_deposit;

                    $p .= "═══════════════════════════════════════\n";
                    $p .= "🏨 بخش ۱۱: تحلیل فاکتورهای هتل\n";
                    $p .= "═══════════════════════════════════════\n";
                    $p .= "▸ کل فاکتورها: {$hotelInvoiceStats->total}\n";
                    $p .= "▸ مجموع مبلغ فاکتورها: " . number_format($hotelInvoiceStats->total_amount) . " تومان\n";
                    $p .= "▸ مجموع تخفیف‌ها: " . number_format($hotelInvoiceStats->total_discount) . " تومان\n";
                    $p .= "▸ مجموع بیعانه‌ها: " . number_format($hotelInvoiceStats->total_deposit) . " تومان\n";
                    $p .= "▸ پرداخت شده/تسویه شده: {$hotelInvoiceStats->paid_count} فاکتور (" . number_format($hotelInvoiceStats->paid_amount) . " تومان)\n";
                    $p .= "▸ پرداخت نشده (پیش فاکتور): {$hotelInvoiceStats->unpaid_count} فاکتور (" . number_format($hotelInvoiceStats->unpaid_amount) . " تومان)\n";
                    $p .= "▸ مانده دارد (بیعانه واریز شده): {$hotelInvoiceStats->pending_count} فاکتور (" . number_format($hotelInvoiceStats->pending_amount) . " تومان)\n";
                    $p .= "  └─ بیعانه دریافتی: " . number_format($hotelInvoiceStats->pending_deposit) . " تومان\n";
                    $p .= "  └─ مانده قابل وصول: " . number_format($remainderAmount) . " تومان\n";
                    $p .= "▸ نرخ وصول مطالبات: {$collectRate}%\n\n";

                    $p .= "🔹 عملکرد بر اساس هتل:\n";
                    foreach ($hotelInvoiceByHotel as $h) {
                        $hCollectRate = $h->tot > 0 ? round(($h->tot * ($h->paid / max($h->cnt,1))) / $h->tot * 100, 0) : 0;
                        $p .= "  ▸ {$h->hotel_name}: {$h->cnt} فاکتور (" . number_format($h->tot) . " ت)\n";
                        $p .= "    پرداخت شده: {$h->paid} | مانده دارد: {$h->has_remainder}\n";
                    }
                    $p .= "\n";

                    $p .= "🔹 بر اساس نوع:\n";
                    foreach ($hotelInvoiceByType as $t) {
                        $typeLabel = $t->invoice_type === 'confirmed' ? 'فاکتور تایید شده' : 'پیش فاکتور';
                        $p .= "  ▸ {$typeLabel}: {$t->cnt} فاکتور (" . number_format($t->tot) . " ت)\n";
                    }
                    $p .= "\n";

                    $p .= "🔹 بر اساس وضعیت:\n";
                    foreach ($hotelInvoiceByStatus as $s) {
                        $statusLabels = ['prepaid'=>'پرداخت نشده','pending'=>'مانده دارد','paid'=>'پرداخت شده','settled'=>'تسویه شده'];
                        $statusLabel = $statusLabels[$s->invoice_status] ?? $s->invoice_status;
                        $p .= "  ▸ {$statusLabel}: {$s->cnt} فاکتور (" . number_format($s->tot) . " ت)\n";
                    }
                    $p .= "\n";
                } catch (\Exception $e) {
                    // hotel_invoices table might not exist
                }
            }

            // Targets
            if (in_array('targets', $selectedCats)) {
                $targets = $db->fetchAll(
                    "SELECT t.year, t.month, u.full_name, t.target_amount, t.target_deals,
                            COALESCE(SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END),0) as ach_count,
                            COALESCE(SUM(CASE WHEN d.is_won=1 THEN d.amount ELSE 0 END),0) as ach_amount
                     FROM sales_targets t
                     LEFT JOIN users u ON t.target_type = 'user' AND t.target_id=u.id
                     LEFT JOIN deals d ON d.assigned_to=t.target_id AND YEAR(d.closed_at)=t.year AND MONTH(d.closed_at)=t.month AND d.is_won=1
                     WHERE t.year>=YEAR(NOW())-1
                     GROUP BY t.id, t.year, t.month, u.full_name, t.target_amount, t.target_deals
                     ORDER BY t.year DESC, t.month DESC, u.full_name
                     LIMIT 20"
                );
                $monthNames = [1=>'ژانویه','فوریه','مارس','آوریل','مه','ژوئن','ژوئیه','آگوست','سپتامبر','اکتبر','نوامبر','دسامبر'];
                $p .= "== اهداف فروش ==\n";
                foreach ($targets as $t) {
                    $mName = $monthNames[$t->month] ?? $t->month;
                    $pct = $t->target_amount > 0 ? round(($t->ach_amount / $t->target_amount) * 100, 1) : 0;
                    $p .= "- {$t->full_name} ({$mName} {$t->year}): هدف=" . number_format($t->target_amount) . " ت | عملکرد=" . number_format($t->ach_amount) . " ت ({$pct}%)\n";
                }
                $p .= "\n";
            }

            $p .= "═══════════════════════════════════════\n";
            $p .= "🎯 دستورالعمل تحلیل:\n";
            $p .= "═══════════════════════════════════════\n";
            $p .= "لطفاً گزارش خود را با ساختار زیر ارائه بده:\n\n";
            $p .= "۱. 📊 خلاصه اجرایی (Executive Summary)\n";
            $p .= "   - خلاصه‌ای کوتاه و مختصر از وضعیت کلی کسب‌وکار\n";
            $p .= "   - مهم‌ترین عدد و شاخص کلیدی عملکرد (KPI)\n\n";
            $p .= "۲. 📈 تحلیل روندها و الگوها\n";
            $p .= "   - روند رشد یا افت فروش\n";
            $p .= "   - الگوهای زمانی (روزانه/هفتگی)\n";
            $p .= "   - مقایسه عملکرد جاری با دوره‌های قبل\n\n";
            $p .= "۳. 💪 نقاط قوت شناسایی شده\n";
            $p .= "   - منابع ورودی پربازده\n";
            $p .= "   - کاربران برتر و دلیل موفقیت آن‌ها\n";
            $p .= "   - مراحل فروش با نرخ تبدیل بالا\n\n";
            $p .= "۴. ⚠️ نقاط ضعف و ریسک‌ها\n";
            $p .= "   - مراحل فروش با نرخ تبدیل پایین\n";
            $p .= "   - فعالیت‌های عقب‌افتاده\n";
            $p .= "   - دلایل اصلی باخت معاملات\n\n";
            $p .= "۵. 🔍 تحلیل فاکتورهای هتل\n";
            $p .= "   - وضعیت پرداخت و وصول مطالبات\n";
            $p .= "   - هتل‌های پرمعامله و عملکرد آن‌ها\n";
            $p .= "   - تخفیف‌ها و تاثیر آن بر درآمد\n\n";
            $p .= "۶. 💡 پیشنهادات عملیاتی (Actionable Insights)\n";
            $p .= "   - حداقل ۵ پیشنهاد مشخص و قابل اجرا\n";
            $p .= "   - اولویت‌بندی پیشنهادات بر اساس تاثیر\n\n";
            $p .= "۷. 📊 پیش‌بینی مالی\n";
            $p .= "   - پیش‌بینی درآمد هفته آینده (ریالی)\n";
            $p .= "   - پیش‌بینی درآمد ماه آینده\n";
            $p .= "   - سطح اطمینان پیش‌بینی (بالا/متوسط/پایین)\n\n";
            $p .= "۸. 🎯 اقدامات فوری (Top 5 Action Items)\n";
            $p .= "   - ۵ اقدام فوری با مسئول و مهلت زمانی\n\n";
            $p .= "۹. 🔮 تحلیل ریسک‌ها\n";
            $p .= "   - ریسک‌های پیش روی کسب‌وکار\n";
            $p .= "   - پیشنهادات کاهش ریسک\n\n";
            $p .= "لحن گزارش: حرفه‌ای، داده‌محور، عملیاتی\n";
            $p .= "از جداول، لیست‌ها، عدد و ارقام مشخص و ایموجی استفاده کن.\n";
            $p .= "هر بخش باید شامل تحلیل عددی دقیق و پیشنهاد عملیاتی باشد.\n";

            $apiUrl = $config['openrouter']['api_url'] ?? 'https://openrouter.ai/api/v1/chat/completions';
            $postData = json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'تو یک تحلیلگر ارشد CRM و فروش با بیش از ۱۵ سال تجربه در صنعت گردشگری و آژانس‌های مسافرتی هستی. وظیفه تو تحلیل عمیق، حرفه‌ای و عملیاتی اطلاعات CRM یک آژانس مسافرتی فعال است. پاسخ را با فرمت Markdown به فارسی بنویس. از هدینگ، لیست، bold، جداول و ایموجی استفاده کن. هر بخش باید شامل تحلیل عددی دقیق و پیشنهاد عملیاتی باشد. لحن گزارش حرفه‌ای، داده‌محور و عملیاتی باشد. از اعداد و ارقام مشخص استفاده کن و از اظهارنظرهای کلی و غیردقیق پرهیز کن.'],
                    ['role' => 'user', 'content' => $p],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: ' . ($config['url'] ?? ''),
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                echo json_encode(['success' => false, 'message' => 'خطای اتصال: ' . $curlError]);
                exit;
            }

            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result['choices'][0]['message']['content'])) {
                $analysisText = $result['choices'][0]['message']['content'];
                
                try {
                    $db->insert('ai_analyses', [
                        'user_id' => $userId,
                        'model' => $model,
                        'prompt_summary' => mb_substr($p, 0, 500),
                        'result' => $analysisText,
                        'deals_count' => (int)$dealsTotal->c,
                        'total_amount' => (float)$dealsTotal->t,
                    ]);
                } catch (\Exception $e) { }
                
                echo json_encode([
                    'success' => true,
                    'analysis' => $analysisText,
                    'model' => $model,
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $errMsg = $result['error']['message'] ?? 'خطای نامعتبر (کد: ' . $httpCode . ')';
                echo json_encode(['success' => false, 'message' => $errMsg]);
            }
            exit;
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
            exit;
        }
    }

    public function history(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $userId = Auth::id();
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        
        try {
            $db->query("CREATE TABLE IF NOT EXISTS `ai_analyses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `model` VARCHAR(100),
                `prompt_summary` TEXT,
                `result` LONGTEXT,
                `deals_count` INT DEFAULT 0,
                `total_amount` DECIMAL(15,2) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) { }
        
        $where = $isAdmin ? '' : ' WHERE a.user_id = :uid';
        $params = $isAdmin ? [] : [':uid' => $userId];
        
        $analyses = $db->fetchAll(
            "SELECT a.*, u.full_name as user_name FROM ai_analyses a LEFT JOIN users u ON a.user_id=u.id" . $where . " ORDER BY a.created_at DESC LIMIT 50",
            $params
        );
        
        View::render('reports/ai_history', [
            'title' => 'تاریخچه تحلیل‌های هوش مصنوعی',
            'analyses' => $analyses,
        ]);
    }
}
