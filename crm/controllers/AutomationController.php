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
    // ═══════════════════════════════════════════════════
    // لیست تمام ماشه‌ها (Triggers) با توضیحات کامل
    // ═══════════════════════════════════════════════════
    public static function getTriggerTypes(): array
    {
        return [
            'stage_change' => [
                'label' => '🔄 تغییر مرحله معامله',
                'description' => 'وقتی یک معامله از مرحله‌ای به مرحله دیگر منتقل می‌شود (مثلاً از «در حال پیگیری» به «پرداخت شده»)',
                'conditions' => ['pipeline_id', 'stage_id'],
                'category' => 'معاملات',
            ],
            'deal_created' => [
                'label' => '💼 ایجاد معامله جدید',
                'description' => 'وقتی یک معامله جدید در سیستم ثبت می‌شود. می‌توانید برای خوش‌آمدگویی یا اطلاع‌رسانی خودکار استفاده کنید.',
                'conditions' => ['pipeline_id', 'source', 'min_amount'],
                'category' => 'معاملات',
            ],
            'deal_won' => [
                'label' => '🏆 موفق شدن معامله (برد)',
                'description' => 'وقتی یک معامله به مرحله نهایی موفق (مثلاً «پرداخت شده» یا «تکمیل شده») منتقل می‌شود.',
                'conditions' => ['pipeline_id'],
                'category' => 'معاملات',
            ],
            'deal_lost' => [
                'label' => '😞 ناموفق شدن معامله (باخت)',
                'description' => 'وقتی یک معامله به مرحله لغو/ناموفق منتقل می‌شود.',
                'conditions' => ['pipeline_id'],
                'category' => 'معاملات',
            ],
            'payment_created' => [
                'label' => '💳 ایجاد لینک پرداخت',
                'description' => 'وقتی یک لینک پرداخت جدید برای معامله ساخته می‌شود. برای ارسال خودکار لینک کوتاه پرداخت به مشتری عالی است.',
                'conditions' => ['min_amount'],
                'extra' => ['payment_link', 'payment_short_link', 'payment_amount'],
                'category' => 'پرداخت',
            ],
            'payment_verified' => [
                'label' => '✅ تایید پرداخت',
                'description' => 'وقتی پرداختی با موفقیت تایید و وریفای می‌شود.',
                'conditions' => ['min_amount'],
                'category' => 'پرداخت',
            ],
            'new_contact' => [
                'label' => '👤 افزودن مخاطب جدید',
                'description' => 'وقتی مخاطب (مشتری) جدیدی در سیستم ثبت می‌شود.',
                'conditions' => [],
                'category' => 'مخاطبان',
            ],
            'activity_reminder' => [
                'label' => '⏰ یادآوری فعالیت',
                'description' => 'وقتی زمان انجام یک فعالیت (تماس، جلسه، پیگیری و...) فرا می‌رسد.',
                'conditions' => [],
                'category' => 'فعالیت‌ها',
            ],
        ];
    }

    // ═══════════════════════════════════════════════════
    // لیست تمام اقدام‌ها (Actions) با توضیحات کامل
    // ═══════════════════════════════════════════════════
    public static function getActionTypes(): array
    {
        return [
            'send_sms' => [
                'label' => '✉️ ارسال پیامک سفارشی',
                'description' => 'ارسال پیامک با متن دلخواه به مخاطب معامله. از متغیرها مانند {contact_name} و {deal_title} استفاده کنید.',
                'category' => 'ارتباطات',
            ],
            'send_payment_sms' => [
                'label' => '💰 ارسال پیامک لینک پرداخت',
                'description' => 'ارسال خودکار پیامک حاوی لینک کوتاه پرداخت به مخاطب. لینک از آخرین پرداخت ایجاد شده معامله استخراج می‌شود.',
                'category' => 'ارتباطات',
            ],
            'send_notification' => [
                'label' => '🔔 ارسال اعلان به کاربر',
                'description' => 'ارسال اعلان داخلی به یک کاربر سیستم (مثلاً اطلاع‌رسانی به مدیر هنگام پرداخت موفق).',
                'category' => 'اطلاع‌رسانی',
            ],
            'create_activity' => [
                'label' => '📅 ایجاد فعالیت/یادآوری',
                'description' => 'ایجاد خودکار یک فعالیت جدید برای معامله (مثلاً «پیگیری تلفنی ۳ روز دیگر» یا «ارسال مدارک سفر»).',
                'category' => 'فعالیت‌ها',
            ],
            'assign_user' => [
                'label' => '👤 تخصیص معامله به کاربر',
                'description' => 'تخصیص خودکار معامله به یک کاربر مشخص (مثلاً هنگام ورود به مرحله «رزرو بلیط» به کاربر بخش بلیط اختصاص یابد).',
                'category' => 'مدیریت',
            ],
            'update_deal_field' => [
                'label' => '✏️ بروزرسانی فیلد معامله',
                'description' => 'تغییر خودکار فیلدهای معامله مانند اولویت، منبع یا توضیحات.',
                'category' => 'مدیریت',
            ],
        ];
    }

    // ═══════════════════════════════════════════════════
    // لیست متغیرهای قابل استفاده در هر ماشه
    // ═══════════════════════════════════════════════════
    public static function getPlaceholderHelp(string $triggerType): array
    {
        $common = [
            '{contact_name}' => 'نام مخاطب',
            '{contact_phone}' => 'تلفن مخاطب',
            '{deal_title}' => 'عنوان معامله',
            '{amount}' => 'مبلغ معامله (تومان)',
            '{stage_name}' => 'نام مرحله فعلی',
            '{pipeline_name}' => 'نام پایپ‌لاین',
        ];

        $payment = [
            '{payment_link}' => 'لینک کوتاه پرداخت',
            '{payment_short_link}' => 'لینک کوتاه پرداخت (همان لینک بالا)',
            '{payment_amount}' => 'مبلغ پرداخت (تومان)',
        ];

        $triggerMap = [
            'payment_created' => array_merge($common, $payment),
            'payment_verified' => array_merge($common, $payment),
            'stage_change' => $common,
            'deal_created' => $common,
            'deal_won' => $common,
            'deal_lost' => $common,
            'new_contact' => ['{contact_name}' => 'نام مخاطب', '{contact_phone}' => 'تلفن مخاطب', '{contact_email}' => 'ایمیل مخاطب'],
            'activity_reminder' => $common,
        ];

        return $triggerMap[$triggerType] ?? $common;
    }

    // ═══════════════════════════════════════════════════
    // لیست انواع فعالیت (ENUM معتبر)
    // ═══════════════════════════════════════════════════
    public static function getActivityTypes(): array
    {
        return [
            'follow_up' => '📌 پیگیری / یادآوری',
            'call' => '📞 تماس تلفنی',
            'meeting' => '🤝 جلسه',
            'note' => '📝 یادداشت',
            'email' => '📧 ایمیل',
            'sms' => '✉️ پیامک',
            'other' => '📋 سایر',
        ];
    }

    // ═══════════════════════════════════════════════════
    // صفحه لیست قوانین
    // ═══════════════════════════════════════════════════
    public function index(): void
    {
        $db = Database::getInstance();
        $rules = $db->fetchAll("SELECT * FROM automation_rules ORDER BY is_active DESC, name ASC");
        View::render('automation/index', ['title' => 'اتوماسیون', 'rules' => $rules]);
    }

    public function create(): void
    {
        View::render('automation/create', [
            'title' => 'قانون اتوماسیون جدید',
            'triggerTypes' => self::getTriggerTypes(),
            'actionTypes' => self::getActionTypes(),
            'activityTypes' => self::getActivityTypes(),
        ]);
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

        // Store action config as-is
        $actionConfig = $_POST['action_config'] ?? [];

        if (empty($name) || empty($triggerType) || empty($actionType)) {
            Session::setFlash('danger', 'فیلدهای ضروری را پر کنید.');
            View::redirect('/automation/create');
            return;
        }

        $db->insert('automation_rules', [
            'name' => $name,
            'description' => $desc,
            'trigger_type' => $triggerType,
            'trigger_conditions' => json_encode($triggerConditions),
            'action_type' => $actionType,
            'action_config' => json_encode($actionConfig),
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
        View::render('automation/edit', [
            'title' => 'ویرایش قانون',
            'rule' => $rule,
            'triggerTypes' => self::getTriggerTypes(),
            'actionTypes' => self::getActionTypes(),
            'activityTypes' => self::getActivityTypes(),
        ]);
    }

    public function update(array $params): void
    {
        $db = Database::getInstance();

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

                    if (!self::checkConditions($conditions, $extra)) {
                        $db->insert('automation_logs', [
                            'rule_id' => $rule->id,
                            'entity_type' => $entityType,
                            'entity_id' => $entityId,
                            'status' => 'skipped',
                            'result_message' => 'شرایط برقرار نبود',
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
        if (empty($conditions)) return true;

        foreach ($conditions as $key => $val) {
            // Skip empty/null conditions - they mean "no filter"
            if ($val === '' || $val === null) continue;
            if (is_string($val) && trim($val) === '') continue;
            if (is_numeric($val) && (int)$val === 0) continue;

            switch ($key) {
                case 'stage_id':
                    // If extra doesn't have stage_id, we can't filter - skip
                    if (!isset($extra['stage_id'])) break;
                    // If stage_id is 0 in extra, it means not set
                    if ((int)$extra['stage_id'] === 0) break;
                    if ((int)$extra['stage_id'] !== (int)$val) return false;
                    break;
                case 'pipeline_id':
                    if (!isset($extra['pipeline_id'])) break;
                    if ((int)$extra['pipeline_id'] === 0) break;
                    if ((int)$extra['pipeline_id'] !== (int)$val) return false;
                    break;
                case 'source':
                    if (!isset($extra['source'])) break;
                    if (empty(trim($extra['source']))) break;
                    if ($extra['source'] !== $val) return false;
                    break;
                case 'min_amount':
                    if (!isset($extra['amount'])) break;
                    $amount = (int)$extra['amount'];
                    $minAmount = (int)$val;
                    if ($minAmount <= 0) break; // No limit
                    if ($amount < $minAmount) return false;
                    break;
            }
        }
        return true;
    }

    // ═══════════════════════════════════════════════════
    // ساخت لینک کوتاه پرداخت برای یک معامله
    // ═══════════════════════════════════════════════════
    private static function getLatestPaymentShortLink(int $dealId): string
    {
        $db = Database::getInstance();
        $payment = $db->fetch(
            "SELECT short_code FROM payments WHERE deal_id = :did AND short_code IS NOT NULL AND short_code != '' ORDER BY id DESC LIMIT 1",
            [':did' => $dealId]
        );
        if ($payment) {
            return ($GLOBALS['app_config']['url'] ?? '') . '/p/' . $payment->short_code;
        }
        return '';
    }

    // ═══════════════════════════════════════════════════
    // جایگزینی متغیرها در متن - فقط از لینک کوتاه استفاده می‌شود
    // ═══════════════════════════════════════════════════
    private static function replacePlaceholders(string $template, array $extra): string
    {
        // Resolve payment short link: extra -> db lookup
        $paymentLink = $extra['payment_short_link'] ?? '';
        if (empty($paymentLink)) {
            $paymentLink = $extra['payment_link'] ?? '';
        }
        if (empty($paymentLink) && !empty($extra['deal_id'])) {
            $paymentLink = self::getLatestPaymentShortLink((int)$extra['deal_id']);
        }

        $search = [
            '{contact_name}', '{contact_phone}', '{contact_email}',
            '{deal_title}', '{amount}',
            '{payment_link}', '{payment_short_link}', '{payment_amount}',
            '{stage_name}', '{pipeline_name}',
        ];
        $replace = [
            $extra['contact_name'] ?? $extra['contact_phone'] ?? '',
            $extra['contact_phone'] ?? '',
            $extra['contact_email'] ?? '',
            $extra['title'] ?? '',
            !empty($extra['amount']) ? number_format((float)$extra['amount']) . ' تومان' : '',
            $paymentLink,  // Both {payment_link} and {payment_short_link} use same short link
            $paymentLink,
            !empty($extra['payment_amount']) ? number_format((float)$extra['payment_amount']) . ' تومان' : '',
            $extra['stage_name'] ?? '',
            $extra['pipeline_name'] ?? '',
        ];
        return str_replace($search, $replace, $template);
    }

    // ═══════════════════════════════════════════════════
    // تبدیل نوع فعالیت به مقادیر معتبر ENUM
    // ═══════════════════════════════════════════════════
    private static function mapActivityType(string $type): string
    {
        $validTypes = ['note', 'call', 'meeting', 'email', 'sms', 'follow_up', 'other'];
        if (in_array($type, $validTypes)) return $type;
        $map = ['reminder' => 'follow_up', 'todo' => 'other'];
        return $map[$type] ?? 'other';
    }

    // ═══════════════════════════════════════════════════
    // اجرای اقدام
    // ═══════════════════════════════════════════════════
    private static function executeAction(string $actionType, array $config, string $entityType, int $entityId, array $extra): string
    {
        $db = Database::getInstance();

        switch ($actionType) {

            // ─── ارسال پیامک سفارشی ──────────────────────
            case 'send_sms':
                $phone = ($config['phone_field'] ?? 'contact') === 'contact'
                    ? ($extra['contact_phone'] ?? '') : '';

                if (empty($phone)) return "شماره تلفن مخاطب یافت نشد";

                $msgTemplate = trim($config['message_template'] ?? '');
                if (empty($msgTemplate)) return "متن پیامک تعریف نشده";

                $finalMessage = self::replacePlaceholders($msgTemplate, $extra);

                $result = \Controllers\SmsController::sendWebservice($phone, $finalMessage);

                $db->insert('sms_history', [
                    'recipient' => $phone, 'message' => $finalMessage,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'message_outbox_id' => $result['outbox_id'] ?? '',
                    'error_message' => $result['success'] ? '' : $result['message'],
                    'deal_id' => !empty($extra['deal_id']) ? (int)$extra['deal_id'] : null,
                    'contact_id' => !empty($extra['contact_id']) ? (int)$extra['contact_id'] : null,
                    'sent_by' => null,
                ]);

                return $result['success']
                    ? "پیامک به {$phone} ارسال شد"
                    : "خطا: " . $result['message'];

            // ─── ارسال پیامک لینک پرداخت (کوتاه) ─────────
            case 'send_payment_sms':
                $phone = $extra['contact_phone'] ?? '';
                if (empty($phone)) return "شماره تلفن مخاطب یافت نشد";

                // لینک کوتاه پرداخت از extra یا از آخرین پرداخت معامله
                $shortLink = $extra['payment_short_link'] ?? '';
                if (empty($shortLink) && !empty($extra['deal_id'])) {
                    $latestPayment = $db->fetch(
                        "SELECT short_code FROM payments WHERE deal_id = :did AND short_code IS NOT NULL AND short_code != '' ORDER BY id DESC LIMIT 1",
                        [':did' => (int)$extra['deal_id']]
                    );
                    if ($latestPayment) {
                        $shortLink = ($GLOBALS['app_config']['url'] ?? '') . '/p/' . $latestPayment->short_code;
                    }
                }
                if (empty($shortLink)) return "لینک پرداخت یافت نشد. ابتدا لینک پرداخت ایجاد کنید.";

                $paymentAmount = $extra['payment_amount'] ?? $extra['amount'] ?? 0;
                $formattedAmount = !empty($paymentAmount) ? number_format((float)$paymentAmount) . ' تومان' : '';

                // متن پیامک پیش‌فرض یا سفارشی
                $msgTemplate = trim($config['message_template'] ?? '');
                if (empty($msgTemplate)) {
                    $contactName = $extra['contact_name'] ?? 'مشتری گرامی';
                    $dealTitle = $extra['title'] ?? '';
                    $msgTemplate = "{$contactName} عزیز" . ($dealTitle ? "، {$dealTitle}" : "") . ";\n";
                    $msgTemplate .= "مبلغ قابل پرداخت: {$formattedAmount}\n";
                    $msgTemplate .= "لینک پرداخت: {$shortLink}";
                }

                // Set short link in extra so replacePlaceholders uses it
                $extra['payment_short_link'] = $shortLink;
                $extra['payment_link'] = $shortLink;
                $extra['payment_amount'] = $paymentAmount;

                $finalMessage = self::replacePlaceholders($msgTemplate, $extra);

                $result = \Controllers\SmsController::sendWebservice($phone, $finalMessage);

                $db->insert('sms_history', [
                    'recipient' => $phone, 'message' => $finalMessage,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'message_outbox_id' => $result['outbox_id'] ?? '',
                    'error_message' => $result['success'] ? '' : $result['message'],
                    'deal_id' => !empty($extra['deal_id']) ? (int)$extra['deal_id'] : null,
                    'contact_id' => !empty($extra['contact_id']) ? (int)$extra['contact_id'] : null,
                    'sent_by' => null,
                ]);

                return $result['success']
                    ? "پیامک لینک پرداخت به {$phone} ارسال شد (لینک: {$shortLink})"
                    : "خطا: " . $result['message'];

            // ─── ارسال اعلان به کاربر ───────────────────
            case 'send_notification':
                $cfgUserId = !empty($config['user_id']) ? (int)$config['user_id'] : 0;
                $extraUserId = !empty($extra['assigned_to']) ? (int)$extra['assigned_to'] : 0;
                $userId = $cfgUserId > 0 ? $cfgUserId : $extraUserId;

                if ($userId <= 0) return "کاربر گیرنده اعلان مشخص نشده";

                $title = trim($config['title'] ?? 'اعلان اتوماسیون');
                $msg = trim($config['message'] ?? '');
                $title = self::replacePlaceholders($title, $extra);
                $msg = self::replacePlaceholders($msg, $extra);

                $db->insert('notifications', [
                    'user_id' => $userId, 'from_user_id' => null, 'type' => 'automation',
                    'title' => $title, 'message' => $msg,
                    'link' => "/deals/view/{$entityId}",
                    'entity_type' => $entityType, 'entity_id' => $entityId, 'is_read' => 0,
                ]);

                return "اعلان به کاربر {$userId} ارسال شد";

            // ─── ایجاد فعالیت/یادآوری ───────────────────
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

                // Support new hours/minutes format, with backward compatibility for old 'days' format
                $delayHours = 0;
                $delayMinutes = 0;
                if (isset($config['delay_hours']) || isset($config['delay_minutes'])) {
                    $delayHours = isset($config['delay_hours']) && $config['delay_hours'] !== '' ? (int)$config['delay_hours'] : 0;
                    $delayMinutes = isset($config['delay_minutes']) && $config['delay_minutes'] !== '' ? (int)$config['delay_minutes'] : 0;
                } elseif (isset($config['days']) && $config['days'] !== '') {
                    // Backward compatibility: convert old days to hours
                    $delayHours = (int)$config['days'] * 24;
                }

                $rawType = $config['activity_type'] ?? 'follow_up';
                $validType = self::mapActivityType($rawType);
                $subject = trim($config['subject'] ?? 'فعالیت خودکار');
                $subject = self::replacePlaceholders($subject, $extra);
                $description = self::replacePlaceholders($config['description'] ?? '', $extra);

                $totalMinutes = ($delayHours * 60) + $delayMinutes;
                $activityDate = $totalMinutes > 0
                    ? date('Y-m-d H:i:s', strtotime("+{$totalMinutes} minutes"))
                    : date('Y-m-d H:i:s');

                $db->insert('deal_activities', [
                    'deal_id' => $entityId, 'user_id' => $activityUserId,
                    'type' => $validType, 'subject' => $subject,
                    'description' => $description, 'is_done' => 0,
                    'activity_date' => $activityDate,
                ]);

                return "فعالیت '{$subject}' ({$validType}) ایجاد شد";

            // ─── تخصیص معامله به کاربر ──────────────────
            case 'assign_user':
                $assignTo = !empty($config['assign_to']) ? (int)$config['assign_to'] : 0;
                if ($assignTo <= 0) return "کاربر مسئول مشخص نشده";

                $user = $db->fetch("SELECT id, full_name FROM users WHERE id = :id AND is_active = 1", [':id' => $assignTo]);
                if (!$user) return "کاربر {$assignTo} یافت نشد";

                $db->update('deals', [
                    'assigned_to' => $assignTo, 'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = :id', [':id' => $entityId]);

                return "معامله به {$user->full_name} اختصاص یافت";

            // ─── بروزرسانی فیلد معامله ───────────────────
            case 'update_deal_field':
                $field = $config['field'] ?? '';
                $value = $config['value'] ?? '';
                if (empty($field)) return "فیلد مشخص نشده";

                $allowedFields = ['source', 'priority', 'description', 'tags'];
                if (!in_array($field, $allowedFields)) return "فیلد '{$field}' مجاز نیست";

                $db->update('deals', [
                    $field => $value, 'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = :id', [':id' => $entityId]);

                return "فیلد {$field} بروزرسانی شد";

            default:
                return "نوع اقدام نامعتبر: {$actionType}";
        }
    }
}