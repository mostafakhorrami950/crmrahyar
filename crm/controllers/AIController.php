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
            $p = "تو یک تحلیلگر حرفه‌ای CRM و فروش هستی. اطلاعات زیر از سیستم CRM آژانس مسافرتی را تحلیل کن و گزارش جامعی به فارسی ارائه بده.\n\n";
            $p .= "تاریخ: " . \Core\JDate::displayDate(date('Y-m-d')) . "\n\n";
            
            // Always include basic deal stats
            $winRate = $dealsTotal->c > 0 ? round(($dealsWon->c / $dealsTotal->c) * 100, 1) : 0;
            $p .= "== آمار کلی معاملات ==\n";
            $p .= "کل: {$dealsTotal->c} (مبلغ: " . number_format($dealsTotal->t) . " تومان)\n";
            $p .= "موفق: {$dealsWon->c} (" . number_format($dealsWon->t) . " ت)\n";
            $p .= "ناموفق: {$dealsLost->c} (" . number_format($dealsLost->t) . " ت)\n";
            $p .= "درجریان: {$dealsActive->c} (" . number_format($dealsActive->t) . " ت)\n";
            $p .= "نرخ برد: {$winRate}%\n\n";

            // Stages
            if (in_array('stages', $selectedCats)) {
                $dealsByStage = $db->fetchAll(
                    "SELECT s.name, COUNT(d.id) as cnt, COALESCE(SUM(d.amount),0) as tot
                     FROM stages s LEFT JOIN deals d ON d.stage_id=s.id AND d.is_lost=0" . ($isAdmin ? '' : " AND (d.assigned_to=:uid OR d.created_by=:uid2)") . "
                     WHERE s.is_active=1 GROUP BY s.id,s.name ORDER BY s.order_index", $sp
                );
                $p .= "== بر اساس مرحله ==\n";
                foreach ($dealsByStage as $s) $p .= "- {$s->name}: {$s->cnt} معامله (" . number_format($s->tot) . " ت)\n";
                $p .= "\n";
            }

            // Sources
            if (in_array('sources', $selectedCats)) {
                $dealsBySource = $db->fetchAll(
                    "SELECT COALESCE(source,'نامشخص') as src, COUNT(*) as cnt, COALESCE(SUM(amount),0) as tot
                     FROM deals WHERE 1=1" . $sw . " GROUP BY source ORDER BY cnt DESC", $sp
                );
                $p .= "== بر اساس منبع ==\n";
                foreach ($dealsBySource as $s) $p .= "- {$s->src}: {$s->cnt} (" . number_format($s->tot) . " ت)\n";
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
                $p .= "== روند هفتگی ==\n";
                foreach ($weeklyTrend as $w) $p .= "- {$w['week']}: {$w['count']} (" . number_format($w['total']) . " ت)\n";
                $p .= "\n";

                $dailyTrend = [];
                for ($d = 6; $d >= 0; $d--) {
                    $day = date('Y-m-d', strtotime("-{$d} days"));
                    $dd = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE DATE(created_at)=:d" . $sw,
                        array_merge([':d' => $day], $sp));
                    $dailyTrend[] = ['date' => $day, 'count' => (int)$dd->c, 'total' => (int)$dd->t];
                }
                $p .= "== روند روزانه ==\n";
                foreach ($dailyTrend as $d) $p .= "- {$d['date']}: {$d['count']} (" . number_format($d['total']) . " ت)\n";
                $p .= "\n";
            }

            // Pipelines
            if (in_array('pipelines', $selectedCats)) {
                $pipePerf = $db->fetchAll(
                    "SELECT p.name, COUNT(d.id) as td,
                            SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END) as w,
                            SUM(CASE WHEN d.is_lost=1 THEN 1 ELSE 0 END) as l,
                            COALESCE(SUM(d.amount),0) as ta
                     FROM pipelines p LEFT JOIN deals d ON d.pipeline_id=p.id" . ($isAdmin ? '' : " AND (d.assigned_to=:uid OR d.created_by=:uid2)") . "
                     WHERE p.is_active=1 GROUP BY p.id,p.name"
                );
                $p .= "== پایپ لاین‌ها ==\n";
                foreach ($pipePerf as $pp) $p .= "- {$pp->name}: کل={$pp->td} موفق={$pp->w} ناموفق={$pp->l}\n";
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
                $p .= "== کاربران ==\n";
                foreach ($userPerf as $u) $p .= "- {$u->full_name}: کل={$u->td} موفق={$u->w} مبلغ=" . number_format($u->wa) . " هفته={$u->lw}\n";
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
                $p .= "== فعالیت‌ها(30روز) ==\n";
                foreach ($actSum as $a) $p .= "- {$a->type}: {$a->cnt} (done={$a->done})\n";
                $p .= "سررسیدگذشته: {$overdue->c}\n";
                $p .= "\n";
            }

            // Loss reasons
            if (in_array('loss_reasons', $selectedCats)) {
                $lossReasons = $db->fetchAll(
                    "SELECT COALESCE(dlr.name, d.lost_reason, 'نامشخص') as r, COUNT(*) as c FROM deals d LEFT JOIN deal_loss_reasons dlr ON d.loss_reason_id=dlr.id WHERE d.is_lost=1" . $sw . " GROUP BY r ORDER BY c DESC LIMIT 5", $sp
                );
                $p .= "== دلایل باخت ==\n";
                foreach ($lossReasons as $r) $p .= "- {$r->r}: {$r->c}\n";
                $p .= "\n";
            }

            // Contacts
            if (in_array('contacts', $selectedCats)) {
                $contactsTotal = $db->fetch("SELECT COUNT(*) as c FROM contacts" . ($dateFrom ? " WHERE created_at>=:date_from" : "") . ($dateTo ? ($dateFrom ? " AND" : " WHERE") . " created_at<=:date_to" : ""), $dateParams);
                $contactsWeek = $db->fetch("SELECT COUNT(*) as c FROM contacts WHERE created_at>=DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $p .= "== مخاطبان ==\n";
                $p .= "کل: {$contactsTotal->c} | جدید هفته: {$contactsWeek->c}\n\n";
            }

            // Win reasons
            if (in_array('win_reasons', $selectedCats)) {
                $winReasons = $db->fetchAll(
                    "SELECT COALESCE(dwr.name, d.win_reason_note, 'نامشخص') as r, COUNT(*) as c FROM deals d LEFT JOIN deal_win_reasons dwr ON d.win_reason_id=dwr.id WHERE d.is_won=1" . $sw . " GROUP BY r ORDER BY c DESC LIMIT 5", $sp
                );
                $p .= "== دلایل موفقیت ==\n";
                foreach ($winReasons as $r) $p .= "- {$r->r}: {$r->c}\n";
                $p .= "\n";
            }

            // Targets
            if (in_array('targets', $selectedCats)) {
                $targets = $db->fetchAll(
                    "SELECT t.year, t.month, u.full_name, t.target_amount, t.target_count,
                            COALESCE(SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END),0) as ach_count,
                            COALESCE(SUM(CASE WHEN d.is_won=1 THEN d.amount ELSE 0 END),0) as ach_amount
                     FROM targets t
                     LEFT JOIN users u ON t.user_id=u.id
                     LEFT JOIN deals d ON d.assigned_to=t.user_id AND YEAR(d.closed_at)=t.year AND MONTH(d.closed_at)=t.month AND d.is_won=1
                     WHERE t.year>=YEAR(NOW())-1
                     GROUP BY t.id, t.year, t.month, u.full_name, t.target_amount, t.target_count
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

            $p .= "\nلطفاً شامل:\n";
            $p .= "1. 📊 خلاصه وضعیت\n2. 📈 پیش‌بینی فروش هفته آینده (عدد ریالی)\n";
            $p .= "3. 💪 نقاط قوت\n4. ⚠️ نقاط ضعف\n5. 🔍 الگوهای مشکوک\n";
            $p .= "6. 💡 پیشنهادات بهبود\n7. 🎯 اقدامات فوری هفته آینده\n";

            $apiUrl = $config['openrouter']['api_url'] ?? 'https://openrouter.ai/api/v1/chat/completions';
            $postData = json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'تو تحلیلگر CRM و فروش هستی. پاسخ را با فرمت Markdown به فارسی بنویس. از هدینگ، لیست، bold و ایموجی استفاده کن.'],
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