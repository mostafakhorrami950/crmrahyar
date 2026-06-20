<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;
use Core\Logger;

class AutomationController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $rules = $db->fetchAll("SELECT * FROM automation_rules ORDER BY is_active DESC, name ASC");
        View::render('automation/index', ['title' => 'اتوماسیون', 'rules' => $rules]);
    }

    public function create(): void
    {
        View::render('automation/create', ['title' => 'قانون اتوماسیون جدید']);
    }

    public function store(): void
    {
        $db = Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $triggerType = $_POST['trigger_type'] ?? '';
        $actionType = $_POST['action_type'] ?? '';

        // Clean trigger conditions - remove empty values
        $rawConditions = $_POST['trigger_conditions'] ?? [];
        $triggerConditions = array_filter($rawConditions, function($v) { return $v !== '' && $v !== null; });
        $triggerConditionsJson = json_encode($triggerConditions);

        // Store action config as-is
        $actionConfig = json_encode($_POST['action_config'] ?? []);

        if (empty($name) || empty($triggerType) || empty($actionType)) {
            Session::setFlash('danger', 'فیلدهای ضروری را پر کنید.');
            View::redirect('/automation/create');
            return;
        }

        $db->insert('automation_rules', [
            'name' => $name,
            'description' => $desc,
            'trigger_type' => $triggerType,
            'trigger_conditions' => $triggerConditionsJson,
            'action_type' => $actionType,
            'action_config' => $actionConfig,
        ]);

        Session::setFlash('success', 'قانون اتوماسیون ایجاد شد.');
        View::redirect('/automation');
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $rule = $db->fetch("SELECT * FROM automation_rules WHERE id=:id", [':id' => $params['id']]);
        if (!$rule) {
            View::redirect('/automation');
            return;
        }
        View::render('automation/edit', ['title' => 'ویرایش قانون', 'rule' => $rule]);
    }

    public function update(array $params): void
    {
        $db = Database::getInstance();

        // Clean trigger conditions - remove empty values
        $rawConditions = $_POST['trigger_conditions'] ?? [];
        $triggerConditions = array_filter($rawConditions, function($v) { return $v !== '' && $v !== null; });

        $db->update('automation_rules', [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'trigger_type' => $_POST['trigger_type'] ?? '',
            'trigger_conditions' => json_encode($triggerConditions),
            'action_type' => $_POST['action_type'] ?? '',
            'action_config' => json_encode($_POST['action_config'] ?? []),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], 'id=:id', [':id' => $params['id']]);

        Session::setFlash('success', 'قانون بروزرسانی شد.');
        View::redirect('/automation');
    }

    public function toggle(array $params): void
    {
        $db = Database::getInstance();
        $rule = $db->fetch("SELECT * FROM automation_rules WHERE id=:id", [':id' => $params['id']]);
        if ($rule) {
            $db->update('automation_rules', ['is_active' => $rule->is_active ? 0 : 1], 'id=:id', [':id' => $params['id']]);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $db->delete('automation_rules', 'id=:id', [':id' => $params['id']]);
        Session::setFlash('success', 'قانون حذف شد.');
        View::redirect('/automation');
    }

    public function logs(): void
    {
        $db = Database::getInstance();
        $logs = $db->fetchAll(
            "SELECT al.*, ar.name as rule_name 
             FROM automation_logs al LEFT JOIN automation_rules ar ON al.rule_id = ar.id 
             ORDER BY al.created_at DESC LIMIT 100"
        );
        View::render('automation/logs', ['title' => 'لاگ اتوماسیون', 'logs' => $logs]);
    }

    // ═══════════════════════════════════════════════════
    // موتور اتوماسیون - فراخوانی از کنترلرهای دیگر
    // ═══════════════════════════════════════════════════
    public static function execute(string $triggerType, string $entityType, int $entityId, array $extra = []): void
    {
        try {
            $db = Database::getInstance();
            $rules = $db->fetchAll(
                "SELECT * FROM automation_rules WHERE trigger_type = :tt AND is_active = 1",
                [':tt' => $triggerType]
            );

            foreach ($rules as $rule) {
                try {
                    $conditions = json_decode($rule->trigger_conditions, true) ?: [];
                    $config = json_decode($rule->action_config, true) ?: [];

                    // بررسی شرایط ماشه
                    if (!self::checkConditions($conditions, $extra)) {
                        $db->insert('automation_logs', [
                            'rule_id' => $rule->id,
                            'entity_type' => $entityType,
                            'entity_id' => $entityId,
                            'status' => 'skipped',
                            'result_message' => 'شرایط برقرار نبود (conditions: ' . json_encode($conditions) . ', extra stage_id: ' . ($extra['stage_id'] ?? 'N/A') . ', extra pipeline_id: ' . ($extra['pipeline_id'] ?? 'N/A') . ')',
                        ]);
                        continue;
                    }

                    $result = self::executeAction($rule->action_type, $config, $entityType, $entityId, $extra);
                    $db->query("UPDATE automation_rules SET execution_count = execution_count + 1 WHERE id = :id", [':id' => $rule->id]);
                    $db->insert('automation_logs', [
                        'rule_id' => $rule->id,
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'status' => 'success',
                        'result_message' => $result,
                    ]);
                } catch (\Exception $e) {
                    Logger::error("Automation rule {$rule->id} failed: " . $e->getMessage());
                    $db->insert('automation_logs', [
                        'rule_id' => $rule->id,
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'status' => 'failed',
                        'result_message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Logger::error("Automation engine error: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // بررسی شرایط ماشه
    // ═══════════════════════════════════════════════════
    private static function checkConditions(array $conditions, array $extra): bool
    {
        // اگر شرطی وجود ندارد، همیشه مجاز است
        if (empty($conditions)) return true;

        foreach ($conditions as $key => $val) {
            // نادیده گرفتن مقادیر خالی (مثلاً وقتی کاربر "همه" انتخاب کرده)
            if ($val === '' || $val === null) continue;
            if (is_string($val) && trim($val) === '') continue;

            switch ($key) {
                case 'stage_id':
                    if (!isset($extra['stage_id'])) continue;
                    if ((int)$extra['stage_id'] !== (int)$val) return false;
                    break;

                case 'pipeline_id':
                    if (!isset($extra['pipeline_id'])) continue;
                    if ((int)$extra['pipeline_id'] !== (int)$val) return false;
                    break;

                case 'source':
                    if (!isset($extra['source'])) continue;
                    if ($extra['source'] !== $val) return false;
                    break;

                case 'min_amount':
                    if (!isset($extra['amount'])) continue;
                    if ((int)$extra['amount'] < (int)$val) return false;
                    break;
            }
        }
        return true;
    }

    // ═══════════════════════════════════════════════════
    // ساخت لینک‌های پرداخت برای یک معامله
    // ═══════════════════════════════════════════════════
    private static function buildPaymentLinks(int $dealId): string
    {
        $db = Database::getInstance();
        $payments = $db->fetchAll(
            "SELECT p.public_token FROM payments p WHERE p.deal_id = :did AND p.public_token IS NOT NULL AND p.public_token != ''",
            [':did' => $dealId]
        );
        $links = [];
        foreach ($payments as $pay) {
            $links[] = ($GLOBALS['app_config']['url'] ?? '') . '/pay/' . $pay->public_token;
        }
        return !empty($links) ? implode("\n", $links) : 'ندارد';
    }

    // ═══════════════════════════════════════════════════
    // جایگزینی متغیرها در متن
    // ═══════════════════════════════════════════════════
    private static function replacePlaceholders(string $template, array $extra, string $paymentLinks = ''): string
    {
        $search = [
            '{contact_name}', '{deal_title}', '{amount}',
            '{payment_link}', '{stage_name}', '{pipeline_name}',
        ];
        $replace = [
            $extra['contact_name'] ?? $extra['contact_phone'] ?? '',
            $extra['title'] ?? '',
            !empty($extra['amount']) ? number_format((float)$extra['amount']) . ' ریال' : '',
            $paymentLinks,
            $extra['stage_name'] ?? '',
            $extra['pipeline_name'] ?? '',
        ];
        return str_replace($search, $replace, $template);
    }

    // ═══════════════════════════════════════════════════
    // تبدیل نوع فعالیت به مقادیر معتبر ENUM
    // ENUM: 'note','call','meeting','email','sms','follow_up','other'
    // ═══════════════════════════════════════════════════
    private static function mapActivityType(string $type): string
    {
        $validTypes = ['note', 'call', 'meeting', 'email', 'sms', 'follow_up', 'other'];
        if (in_array($type, $validTypes)) return $type;
        $map = [
            'reminder' => 'follow_up',
            'todo' => 'other',
        ];
        return $map[$type] ?? 'other';
    }

    // ═══════════════════════════════════════════════════
    // اجرای اقدام
    // ═══════════════════════════════════════════════════
    private static function executeAction(string $actionType, array $config, string $entityType, int $entityId, array $extra): string
    {
        $db = Database::getInstance();

        switch ($actionType) {

            // ─── ارسال پیامک ───────────────────────────
            case 'send_sms':
                $phone = ($config['phone_field'] ?? 'contact') === 'contact'
                    ? ($extra['contact_phone'] ?? '')
                    : '';

                if (empty($phone)) return "شماره تلفن مخاطب یافت نشد";

                $msgTemplate = trim($config['message_template'] ?? '');
                if (empty($msgTemplate)) return "متن پیامک تعریف نشده";

                $paymentLinks = !empty($extra['deal_id'])
                    ? self::buildPaymentLinks((int)$extra['deal_id'])
                    : '';
                $finalMessage = self::replacePlaceholders($msgTemplate, $extra, $paymentLinks);

                $result = \Controllers\SmsController::sendWebservice($phone, $finalMessage);

                // ثبت در تاریخچه پیامک - sent_by = null (سیستم)
                $db->insert('sms_history', [
                    'recipient' => $phone,
                    'message' => $finalMessage,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'message_outbox_id' => $result['outbox_id'] ?? '',
                    'error_message' => $result['success'] ? '' : $result['message'],
                    'deal_id' => !empty($extra['deal_id']) ? (int)$extra['deal_id'] : null,
                    'contact_id' => !empty($extra['contact_id']) ? (int)$extra['contact_id'] : null,
                    'sent_by' => null,
                ]);

                return $result['success']
                    ? "پیامک به {$phone} ارسال شد"
                    : "خطا در ارسال پیامک: " . $result['message'];

            // ─── ارسال اعلان ───────────────────────────
            case 'send_notification':
                $cfgUserId = !empty($config['user_id']) ? (int)$config['user_id'] : 0;
                $extraUserId = !empty($extra['assigned_to']) ? (int)$extra['assigned_to'] : 0;
                $userId = $cfgUserId > 0 ? $cfgUserId : $extraUserId;

                if ($userId <= 0) {
                    return "کاربر گیرنده اعلان مشخص نشده";
                }

                $title = trim($config['title'] ?? 'اعلان اتوماسیون');
                $msg = trim($config['message'] ?? '');
                $title = self::replacePlaceholders($title, $extra);
                $msg = self::replacePlaceholders($msg, $extra);

                // درج مستقیم در دیتابیس (بدون Auth::id که ممکن null باشد)
                $db->insert('notifications', [
                    'user_id' => $userId,
                    'from_user_id' => null,
                    'type' => 'automation',
                    'title' => $title,
                    'message' => $msg,
                    'link' => "/deals/view/{$entityId}",
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'is_read' => 0,
                ]);

                return "اعلان به کاربر {$userId} ارسال شد";

            // ─── ایجاد فعالیت/یادآوری ──────────────────
            case 'create_activity':
                $activityUserId = !empty($extra['assigned_to']) ? (int)$extra['assigned_to'] : 0;
                if ($activityUserId <= 0) {
                    $currentUserId = (int)(Auth::id() ?: 0);
                    $activityUserId = $currentUserId > 0 ? $currentUserId : 0;
                    if ($activityUserId <= 0) {
                        $admin = $db->fetch("SELECT id FROM users ORDER BY id ASC LIMIT 1");
                        $activityUserId = $admin ? (int)$admin->id : 1;
                    }
                }

                $days = isset($config['days']) && $config['days'] !== '' ? (int)$config['days'] : 1;
                $rawType = $config['activity_type'] ?? 'follow_up';
                $validType = self::mapActivityType($rawType);
                $subject = trim($config['subject'] ?? 'فعالیت خودکار');
                $subject = self::replacePlaceholders($subject, $extra);
                $description = self::replacePlaceholders($config['description'] ?? '', $extra);

                $activityDate = $days > 0
                    ? date('Y-m-d H:i:s', strtotime("+{$days} days"))
                    : date('Y-m-d H:i:s');

                $db->insert('deal_activities', [
                    'deal_id' => $entityId,
                    'user_id' => $activityUserId,
                    'type' => $validType,
                    'subject' => $subject,
                    'description' => $description,
                    'is_done' => 0,
                    'activity_date' => $activityDate,
                ]);

                return "فعالیت '{$subject}' ایجاد شد";

            // ─── تخصیص معامله به کاربر ─────────────────
            case 'assign_user':
                $assignTo = !empty($config['assign_to']) ? (int)$config['assign_to'] : 0;
                if ($assignTo <= 0) return "کاربر مسئول مشخص نشده";

                $user = $db->fetch("SELECT id, full_name FROM users WHERE id = :id AND is_active = 1", [':id' => $assignTo]);
                if (!$user) return "کاربر {$assignTo} یافت نشد یا غیرفعال است";

                $db->update('deals', [
                    'assigned_to' => $assignTo,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = :id', [':id' => $entityId]);

                return "معامله به {$user->full_name} اختصاص یافت";

            default:
                return "نوع اقدام نامعتبر: {$actionType}";
        }
    }
}