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
        // Clean any buffered output to ensure clean JSON response
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
        
        // Read from DB settings first, fallback to .env config
        $apiKey = $config['openrouter']['api_key'] ?? '';
        $model = $config['openrouter']['model'] ?? '~openai/gpt-latest';
        
        try {
            $apiKeySetting = $db->fetch("SELECT setting_value FROM settings WHERE setting_key='openrouter_api_key' AND setting_group='ai'");
            $modelSetting = $db->fetch("SELECT setting_value FROM settings WHERE setting_key='openrouter_model' AND setting_group='ai'");
            if ($apiKeySetting && !empty($apiKeySetting->setting_value)) $apiKey = $apiKeySetting->setting_value;
            if ($modelSetting && !empty($modelSetting->setting_value)) $model = $modelSetting->setting_value;
        } catch (\Exception $e) {
            // Settings table may not have AI entries yet, use .env fallback
        }

        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'کلید API هوش مصنوعی تنظیم نشده. لطفاً از بخش تنظیمات آن را وارد کنید.']);
            exit;
        }

        try {

        $userId = Auth::id();
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        $sw = $isAdmin ? '' : " AND (d.assigned_to = :uid OR d.created_by = :uid2)";
        $sp = $isAdmin ? [] : [':uid' => $userId, ':uid2' => $userId];

        // Gather data
        $dealsTotal = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE is_lost=0" . $sw, $sp);
        $dealsWon = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE is_won=1" . $sw, $sp);
        $dealsLost = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE is_lost=1" . $sw, $sp);
        $dealsActive = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE is_lost=0 AND is_won=0" . $sw, $sp);

        $dealsByStage = $db->fetchAll(
            "SELECT s.name, COUNT(d.id) as cnt, COALESCE(SUM(d.amount),0) as tot
             FROM stages s LEFT JOIN deals d ON d.stage_id=s.id AND d.is_lost=0" . ($isAdmin ? '' : " AND (d.assigned_to=:uid OR d.created_by=:uid2)") . "
             WHERE s.is_active=1 GROUP BY s.id,s.name ORDER BY s.order_index", $sp
        );

        $dealsBySource = $db->fetchAll(
            "SELECT COALESCE(source,'نامشخص') as src, COUNT(*) as cnt, COALESCE(SUM(amount),0) as tot
             FROM deals WHERE 1=1" . $sw . " GROUP BY source ORDER BY cnt DESC", $sp
        );

        $weeklyTrend = [];
        for ($w = 3; $w >= 0; $w--) {
            $ws = date('Y-m-d', strtotime("-{$w} weeks monday"));
            $we = date('Y-m-d', strtotime("-{$w} weeks sunday"));
            $wd = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE created_at>=:s AND created_at<=:e" . $sw,
                array_merge([':s' => $ws.' 00:00:00', ':e' => $we.' 23:59:59'], $sp));
            $weeklyTrend[] = ['week' => $ws.' to '.$we, 'count' => (int)$wd->c, 'total' => (int)$wd->t];
        }

        $dailyTrend = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = date('Y-m-d', strtotime("-{$d} days"));
            $dd = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM deals WHERE DATE(created_at)=:d" . $sw,
                array_merge([':d' => $day], $sp));
            $dailyTrend[] = ['date' => $day, 'count' => (int)$dd->c, 'total' => (int)$dd->t];
        }

        $topContacts = $db->fetchAll(
            "SELECT c.full_name, COUNT(d.id) as dc, COALESCE(SUM(d.amount),0) as ta
             FROM contacts c JOIN deals d ON d.contact_id=c.id WHERE 1=1" . $sw . "
             GROUP BY c.id,c.full_name ORDER BY dc DESC LIMIT 10", $sp
        );

        $actSum = $db->fetchAll(
            "SELECT type, COUNT(*) as cnt, SUM(CASE WHEN is_done=1 THEN 1 ELSE 0 END) as done
             FROM deal_activities WHERE created_at>=:s" . ($isAdmin ? '' : " AND user_id=:uid") . " GROUP BY type",
            $isAdmin ? [':s' => date('Y-m-d', strtotime('-30 days'))] : [':s' => date('Y-m-d', strtotime('-30 days')), ':uid' => $userId]
        );

        $overdue = $db->fetch("SELECT COUNT(*) as c FROM deal_activities WHERE is_done=0 AND activity_date<NOW()" . ($isAdmin ? '' : " AND user_id=:uid"),
            $isAdmin ? [] : [':uid' => $userId]);

        $pipePerf = $db->fetchAll(
            "SELECT p.name, COUNT(d.id) as td,
                    SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END) as w,
                    SUM(CASE WHEN d.is_lost=1 THEN 1 ELSE 0 END) as l,
                    SUM(CASE WHEN d.is_won=0 AND d.is_lost=0 THEN 1 ELSE 0 END) as a,
                    COALESCE(SUM(d.amount),0) as ta
             FROM pipelines p LEFT JOIN deals d ON d.pipeline_id=p.id" . ($isAdmin ? '' : " AND (d.assigned_to=:uid OR d.created_by=:uid2)") . "
             WHERE p.is_active=1 GROUP BY p.id,p.name"
        );

        $userPerf = $db->fetchAll(
            "SELECT u.full_name, COUNT(d.id) as td,
                    SUM(CASE WHEN d.is_won=1 THEN 1 ELSE 0 END) as w,
                    COALESCE(SUM(CASE WHEN d.is_won=1 THEN d.amount ELSE 0 END),0) as wa,
                    COUNT(CASE WHEN d.created_at>=DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as lw
             FROM users u LEFT JOIN deals d ON d.assigned_to=u.id
             WHERE u.is_active=1 GROUP BY u.id,u.full_name ORDER BY wa DESC"
        );

        $payStats = $db->fetch("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM payments WHERE status='success'");

        $lossReasons = $db->fetchAll(
            "SELECT COALESCE(dlr.name, d.lost_reason, 'نامشخص') as r, COUNT(*) as c FROM deals d LEFT JOIN deal_loss_reasons dlr ON d.loss_reason_id=dlr.id WHERE d.is_lost=1" . $sw . " GROUP BY r ORDER BY c DESC LIMIT 5", $sp
        );

        $contactsTotal = $db->fetch("SELECT COUNT(*) as c FROM contacts");
        $contactsWeek = $db->fetch("SELECT COUNT(*) as c FROM contacts WHERE created_at>=DATE_SUB(NOW(), INTERVAL 7 DAY)");

        // Build prompt
        $p = "تو یک تحلیلگر حرفه‌ای CRM و فروش هستی. اطلاعات زیر از سیستم CRM آژانس مسافرتی را تحلیل کن و گزارش جامعی به فارسی ارائه بده.\n\n";
        $p .= "تاریخ: " . \Core\JDate::displayDate(date('Y-m-d')) . "\n\n";
        $p .= "== آمار کلی معاملات ==\n";
        $p .= "کل: {$dealsTotal->c} (مبلغ: " . number_format($dealsTotal->t) . " تومان)\n";
        $p .= "موفق: {$dealsWon->c} (" . number_format($dealsWon->t) . " ت)\n";
        $p .= "ناموفق: {$dealsLost->c} (" . number_format($dealsLost->t) . " ت)\n";
        $p .= "درجریان: {$dealsActive->c} (" . number_format($dealsActive->t) . " ت)\n";
        $winRate = $dealsTotal->c > 0 ? round(($dealsWon->c / $dealsTotal->c) * 100, 1) : 0;
        $p .= "نرخ برد: {$winRate}%\n\n";

        $p .= "== بر اساس مرحله ==\n";
        foreach ($dealsByStage as $s) $p .= "- {$s->name}: {$s->cnt} معامله (" . number_format($s->tot) . " ت)\n";
        $p .= "\n== بر اساس منبع ==\n";
        foreach ($dealsBySource as $s) $p .= "- {$s->src}: {$s->cnt} (" . number_format($s->tot) . " ت)\n";
        $p .= "\n== روند هفتگی ==\n";
        foreach ($weeklyTrend as $w) $p .= "- {$w['week']}: {$w['count']} (" . number_format($w['total']) . " ت)\n";
        $p .= "\n== روند روزانه ==\n";
        foreach ($dailyTrend as $d) $p .= "- {$d['date']}: {$d['count']} (" . number_format($d['total']) . " ت)\n";
        $p .= "\n== پایپ لاین‌ها ==\n";
        foreach ($pipePerf as $pp) $p .= "- {$pp->name}: کل={$pp->td} موفق={$pp->w} ناموفق={$pp->l}\n";
        $p .= "\n== کاربران ==\n";
        foreach ($userPerf as $u) $p .= "- {$u->full_name}: کل={$u->td} موفق={$u->w} مبلغ=" . number_format($u->wa) . " هفته={$u->lw}\n";
        $p .= "\n== دلایل باخت ==\n";
        foreach ($lossReasons as $r) $p .= "- {$r->r}: {$r->c}\n";
        $p .= "\n== فعالیت‌ها(30روز) ==\n";
        foreach ($actSum as $a) $p .= "- {$a->type}: {$a->cnt} (done={$a->done})\n";
        $p .= "سررسیدگذشته: {$overdue->c}\n";
        $p .= "\n== مخاطبان ==\n";
        $p .= "کل: {$contactsTotal->c} | جدید هفته: {$contactsWeek->c}\n";
        $p .= "\n== پرداخت‌ها ==\n";
        $p .= "موفق: {$payStats->c} (" . number_format($payStats->t) . " ت)\n";

        $p .= "\n\nلطفاً شامل:\n";
        $p .= "1. 📊 خلاصه وضعیت\n2. 📈 پیش‌بینی فروش هفته آینده (عدد ریالی)\n";
        $p .= "3. 💪 نقاط قوت\n4. ⚠️ نقاط ضعف\n5. 🔍 الگوهای مشکوک\n";
        $p .= "6. 💡 پیشنهادات بهبود\n7. 🎯 اقدامات فوری هفته آینده\n";

        // Call OpenRouter
        $apiUrl = $config['openrouter']['api_url'] ?? 'https://openrouter.ai/api/v1/chat/completions';
        $postData = json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'تو تحلیلگر CRM و فروش هستی. به فارسی پاسخ بده.'],
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
            echo json_encode([
                'success' => true,
                'analysis' => $result['choices'][0]['message']['content'],
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
}
