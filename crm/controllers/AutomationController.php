<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;
use Core\Notification;
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
        $triggerConditions = json_encode($_POST['trigger_conditions'] ?? []);
        $actionType = $_POST['action_type'] ?? '';
        $actionConfig = json_encode($_POST['action_config'] ?? []);

        if (empty($name) || empty($triggerType) || empty($actionType)) {
            Session::setFlash('danger', 'فیلدهای ضروری را پر کنید.');
            View::redirect('/automation/create');
            return;
        }

        $db->insert('automation_rules', [
            'name' => $name, 'description' => $desc,
            'trigger_type' => $triggerType, 'trigger_conditions' => $triggerConditions,
            'action_type' => $actionType, 'action_config' => $actionConfig,
        ]);

        Session::setFlash('success', 'قانون اتوماسیون ایجاد شد.');
        View::redirect('/automation');
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $rule = $db->fetch("SELECT * FROM automation_rules WHERE id=:id", [':id'=>$params['id']]);
        if (!$rule) { View::redirect('/automation'); return; }
        View::render('automation/edit', ['title'=>'ویرایش قانون', 'rule'=>$rule]);
    }

    public function update(array $params): void
    {
        $db = Database::getInstance();
        $db->update('automation_rules', [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'trigger_type' => $_POST['trigger_type'] ?? '',
            'trigger_conditions' => json_encode($_POST['trigger_conditions'] ?? []),
            'action_type' => $_POST['action_type'] ?? '',
            'action_config' => json_encode($_POST['action_config'] ?? []),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], 'id=:id', [':id'=>$params['id']]);
        
        Session::setFlash('success', 'قانون بروزرسانی شد.');
        View::redirect('/automation');
    }

    public function toggle(array $params): void
    {
        $db = Database::getInstance();
        $rule = $db->fetch("SELECT * FROM automation_rules WHERE id=:id", [':id'=>$params['id']]);
        if ($rule) {
            $db->update('automation_rules', ['is_active' => $rule->is_active ? 0 : 1], 'id=:id', [':id'=>$params['id']]);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $db->delete('automation_rules', 'id=:id', [':id'=>$params['id']]);
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

    /**
     * Execute automation rules for a given trigger event
     * Call this from other controllers when events happen
     */
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

                    // Check conditions
                    if (!self::checkConditions($conditions, $entityType, $entityId, $extra)) {
                        $db->insert('automation_logs', ['rule_id'=>$rule->id, 'entity_type'=>$entityType, 'entity_id'=>$entityId, 'status'=>'skipped', 'result_message'=>'شرایط برقرار نبود']);
                        continue;
                    }

                    $result = self::executeAction($rule->action_type, $config, $entityType, $entityId, $extra);
                    $db->query("UPDATE automation_rules SET execution_count = execution_count + 1 WHERE id = :id", [':id'=>$rule->id]);
                    $db->insert('automation_logs', ['rule_id'=>$rule->id, 'entity_type'=>$entityType, 'entity_id'=>$entityId, 'status'=>'success', 'result_message'=>$result]);
                } catch (\Exception $e) {
                    Logger::error("Automation rule {$rule->id} failed: " . $e->getMessage());
                    $db->insert('automation_logs', ['rule_id'=>$rule->id, 'entity_type'=>$entityType, 'entity_id'=>$entityId, 'status'=>'failed', 'result_message'=>$e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Logger::error("Automation engine error: " . $e->getMessage());
        }
    }

    private static function checkConditions(array $conditions, string $entityType, int $entityId, array $extra): bool
    {
        if (empty($conditions)) return true;
        $db = Database::getInstance();

        foreach ($conditions as $key => $val) {
            if ($key === 'stage_id' && isset($extra['stage_id'])) {
                if ((int)$extra['stage_id'] !== (int)$val) return false;
            }
            if ($key === 'pipeline_id' && isset($extra['pipeline_id'])) {
                if ((int)$extra['pipeline_id'] !== (int)$val) return false;
            }
            if ($key === 'source' && isset($extra['source'])) {
                if ($extra['source'] !== $val) return false;
            }
            if ($key === 'min_amount' && isset($extra['amount'])) {
                if ((int)$extra['amount'] < (int)$val) return false;
            }
        }
        return true;
    }

    private static function executeAction(string $actionType, array $config, string $entityType, int $entityId, array $extra): string
    {
        $db = Database::getInstance();

        switch ($actionType) {
            case 'send_sms':
                $phone = $config['phone_field'] === 'contact' ? ($extra['contact_phone'] ?? '') : '';
                if (empty($phone)) return "شماره تلفن مخاطب یافت نشد";
                
                $msgTemplate = $config['message_template'] ?? '';
                if (empty($msgTemplate)) return "متن پیامک تعریف نشده";
                
                // Build payment links
                $paymentLinks = '';
                if (!empty($extra['deal_id'])) {
                    $dbPay = Database::getInstance();
                    $payments = $dbPay->fetchAll(
                        "SELECT p.public_token FROM payments p WHERE p.deal_id = :did AND p.public_token IS NOT NULL AND p.public_token != ''",
                        [':did' => $extra['deal_id']]
                    );
                    $links = [];
                    foreach ($payments as $pay) {
                        $links[] = ($GLOBALS['app_config']['url'] ?? '') . '/pay/' . $pay->public_token;
                    }
                    $paymentLinks = !empty($links) ? implode("\n", $links) : 'ندارد';
                }
                
                // Replace placeholders
                $search = ['{contact_name}', '{deal_title}', '{amount}', '{payment_link}', '{stage_name}', '{pipeline_name}'];
                $replace = [
                    $extra['contact_name'] ?? $extra['contact_phone'] ?? '',
                    $extra['title'] ?? '',
                    isset($extra['amount']) ? number_format($extra['amount']) . ' ریال' : '',
                    $paymentLinks,
                    $extra['stage_name'] ?? '',
                    $extra['pipeline_name'] ?? '',
                ];
                $finalMessage = str_replace($search, $replace, $msgTemplate);
                
                $result = \Controllers\SmsController::sendWebservice($phone, $finalMessage);
                
                // Log to sms_history
                $dbLog = Database::getInstance();
                $dbLog->insert('sms_history', [
                    'recipient' => $phone, 'message' => $finalMessage,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'message_outbox_id' => $result['outbox_id'] ?? '',
                    'error_message' => $result['success'] ? '' : $result['message'],
                    'deal_id' => $extra['deal_id'] ?? null,
                    'contact_id' => $extra['contact_id'] ?? null,
                    'sent_by' => 0, // system
                ]);
                
                return $result['success'] ? "پیامک به {$phone} ارسال شد" : "خطا: " . $result['message'];

            case 'send_notification':
                $cfgUserId = !empty($config['user_id']) ? (int)$config['user_id'] : 0;
                $extraUserId = !empty($extra['assigned_to']) ? (int)$extra['assigned_to'] : 0;
                $userId = $cfgUserId ?: $extraUserId;
                if ($userId > 0) {
                    $title = $config['title'] ?? 'اعلان اتوماسیون';
                    $title = str_replace(['{deal_title}', '{amount}', '{contact_name}'], 
                        [$extra['title'] ?? '', $extra['amount'] ?? '', $extra['contact_name'] ?? ''], $title);
                    $msg = $config['message'] ?? '';
                    $msg = str_replace(['{deal_title}', '{amount}', '{contact_name}'], 
                        [$extra['title'] ?? '', $extra['amount'] ?? '', $extra['contact_name'] ?? ''], $msg);
                    Notification::create($userId, 'automation', $title, $msg, "/deals/view/{$entityId}", $entityType, $entityId);
                    return "اعلان به کاربر {$userId} ارسال شد";
                }
                return "کاربر مشخص نشده (شناسه: 0)";

            case 'create_activity':
                $activityUserId = !empty($extra['assigned_to']) ? (int)$extra['assigned_to'] : (Auth::id() ?: 1);
                $days = isset($config['days']) && $config['days'] !== '' ? (int)$config['days'] : 1;
                $db->insert('deal_activities', [
                    'deal_id' => $entityId,
                    'user_id' => $activityUserId,
                    'type' => $config['activity_type'] ?? 'reminder',
                    'subject' => $config['subject'] ?? 'فعالیت خودکار',
                    'description' => $config['description'] ?? '',
                    'activity_date' => date('Y-m-d H:i:s', strtotime('+' . $days . ' days')),
                ]);
                return "فعالیت جدید ایجاد شد";

            case 'assign_user':
                $assignTo = !empty($config['assign_to']) ? (int)$config['assign_to'] : 0;
                if ($assignTo > 0) {
                    $db->update('deals', ['assigned_to' => $assignTo], 'id=:id', [':id'=>$entityId]);
                    return "معامله به کاربر {$assignTo} اختصاص یافت";
                }
                return "کاربر مشخص نشده";

            default:
                return "نوع اقدام نامعتبر: {$actionType}";
        }
    }
}