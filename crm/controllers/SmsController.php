<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class SmsController
{
    public function history(): void
    {
        $db = Database::getInstance();
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];

        // Scope filter: non-admin users see only their own SMS
        if (!Auth::hasPermission('settings.manage')) {
            $where .= " AND sh.sent_by = :current_user_id";
            $params[':current_user_id'] = Auth::id();
        }

        if ($search) {
            $where .= " AND (sh.recipient LIKE :search OR sh.message LIKE :search2 OR c.full_name LIKE :search3 OR d.title LIKE :search4)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
            $params[':search4'] = "%{$search}%";
        }
        if ($status) {
            $where .= " AND sh.status = :status";
            $params[':status'] = $status;
        }

        // Get total count for pagination
        $countResult = $db->fetch(
            "SELECT COUNT(*) as total
             FROM sms_history sh
             LEFT JOIN contacts c ON sh.contact_id = c.id
             LEFT JOIN deals d ON sh.deal_id = d.id
             {$where}",
            $params
        );
        $totalRecords = (int)($countResult->total ?? 0);
        $totalPages = max(1, ceil($totalRecords / $perPage));

        $messages = $db->fetchAll(
            "SELECT sh.*, 
                    d.title as deal_title, d.id as deal_id,
                    c.full_name as contact_name, c.phone as contact_phone,
                    u.full_name as sender_name
             FROM sms_history sh
             LEFT JOIN deals d ON sh.deal_id = d.id
             LEFT JOIN contacts c ON sh.contact_id = c.id
             LEFT JOIN users u ON sh.sent_by = u.id
             {$where}
             ORDER BY sh.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $stats = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
        ];
        $all = $db->fetch("SELECT COUNT(*) as total, SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) as sent, SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) as failed FROM sms_history");
        if ($all) {
            $stats['total'] = (int)$all->total;
            $stats['sent'] = (int)($all->sent ?? 0);
            $stats['failed'] = (int)($all->failed ?? 0);
        }

        View::render('sms/history', [
            'title' => 'تاریخچه پیامک‌ها',
            'messages' => $messages,
            'search' => $search,
            'selectedStatus' => $status,
            'stats' => $stats,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
        ]);
    }

    public function showSendForm(): void
    {
        $db = Database::getInstance();
        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");
        $categories = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY sort_order ASC, name ASC");

        // Get available sender numbers
        $config = $GLOBALS['app_config'];
        $senderNumbers = $config['sms']['sender_numbers'] ?? ['+983000505'];

        View::render('sms/send', [
            'title' => 'ارسال پیامک',
            'pipelines' => $pipelines,
            'categories' => $categories,
            'senderNumbers' => $senderNumbers,
        ]);
    }

    public function send(): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $dealId = (int)($_POST['deal_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Operator can only send SMS for own deals
        if ($dealId && !Auth::ownsDeal($dealId)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'شما فقط برای معاملات خودتان می‌توانید پیامک ارسال کنید.']); exit; }
            Session::setFlash('danger', 'شما فقط برای معاملات خودتان می‌توانید پیامک ارسال کنید.');
            View::redirect('/deals');
        }

        if (empty($message) || empty($phone)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'شماره موبایل و متن پیام الزامی است.']); exit; }
            Session::setFlash('danger', 'شماره موبایل و متن پیام الزامی است.');
            View::redirect('/sms/send/' . $dealId);
        }

        $config = $GLOBALS['app_config'];
        $apiToken = $config['sms']['api_token'] ?? '';
        $fromNumber = trim($_POST['from_number'] ?? '') ?: ($config['sms']['from_number'] ?? '+983000505');
        $panelUrl = $config['sms']['api_url'] ?? 'https://edge.ippanel.com/v1/api/send';

        $db = Database::getInstance();
        $contactId = null;
        if ($dealId) {
            $deal = $db->fetch("SELECT contact_id FROM deals WHERE id = :id", [':id' => $dealId]);
            if ($deal) $contactId = $deal->contact_id;
        }

        $sentStatus = 'failed';
        $outboxId = '';
        $errorMsg = '';

        if (!empty($apiToken)) {
            // Normalize phone to E.164 format
            $normalizedPhone = $this->normalizePhone($phone);
            
            $smsData = [
                'sending_type' => 'webservice',
                'from_number' => $fromNumber,
                'message' => $message,
                'params' => [
                    'recipients' => [$normalizedPhone],
                ],
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $panelUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: ' . $apiToken,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || !empty($curlError)) {
                $sentStatus = 'failed';
                $errorMsg = 'خطای ارتباط: ' . ($curlError ?: 'عدم پاسخ سرور');
            } else {
                $result = json_decode($response);
                if ($result && isset($result->meta->status) && $result->meta->status === true) {
                    $sentStatus = 'sent';
                    $ids = $result->data->message_outbox_ids ?? $result->data->message_ids ?? [];
                    $outboxId = is_array($ids) ? implode(',', $ids) : (string)$ids;
                } else {
                    $apiMsg = $result->meta->message ?? 'خطای نامشخص';
                    $errorMsg = "خطای API ({$httpCode}): {$apiMsg}";
                }
            }
        } else {
            $sentStatus = 'failed';
            $errorMsg = 'API token تنظیم نشده';
        }

        $smsId = $db->insert('sms_history', [
            'recipient' => $phone,
            'message' => $message,
            'status' => $sentStatus,
            'message_outbox_id' => $outboxId,
            'error_message' => $errorMsg,
            'deal_id' => $dealId ?: null,
            'contact_id' => $contactId,
            'sent_by' => Auth::id(),
        ]);

        ActivityLog::log('send_sms', 'sms', $smsId, "پیامک به {$phone} ارسال شد");

        if ($isAjax) {
            echo json_encode(['success' => true, 'message' => 'پیامک با موفقیت ارسال شد.']);
            exit;
        }
        Session::setFlash('success', 'پیامک با موفقیت ارسال شد.');
        View::redirect('/sms/history');
    }

    public function sendBulk(): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $message = trim($_POST['message'] ?? '');
        $filterType = $_POST['filter_type'] ?? '';
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $pipelineId = (int)($_POST['pipeline_id'] ?? 0);
        $stageId = (int)($_POST['stage_id'] ?? 0);
        $dealStatus = $_POST['deal_status'] ?? '';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';

        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'متن پیام الزامی است.']);
            exit;
        }

        $db = Database::getInstance();

        // Build query based on filters
        $where = "WHERE c.phone IS NOT NULL AND c.phone != ''";
        $params = [];

        if ($categoryId) {
            $where .= " AND c.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }

        if ($filterType === 'deal_status' && $dealStatus) {
            if ($dealStatus === 'won') {
                $where .= " AND EXISTS (SELECT 1 FROM deals d WHERE d.contact_id = c.id AND d.is_won = 1)";
            } elseif ($dealStatus === 'lost') {
                $where .= " AND EXISTS (SELECT 1 FROM deals d WHERE d.contact_id = c.id AND d.is_lost = 1)";
            } elseif ($dealStatus === 'open') {
                $where .= " AND EXISTS (SELECT 1 FROM deals d WHERE d.contact_id = c.id AND d.is_won = 0 AND d.is_lost = 0)";
            }
        }

        if ($filterType === 'pipeline' && $pipelineId) {
            $where .= " AND EXISTS (SELECT 1 FROM deals d WHERE d.contact_id = c.id AND d.pipeline_id = :pipe_id)";
            $params[':pipe_id'] = $pipelineId;

            if ($stageId) {
                $where .= " AND EXISTS (SELECT 1 FROM deals d WHERE d.contact_id = c.id AND d.stage_id = :stg_id)";
                $params[':stg_id'] = $stageId;
            }
        }

        if ($filterType === 'date_range' && $dateFrom && $dateTo) {
            $where .= " AND c.created_at BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $dateFrom . ' 00:00:00';
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }

        $contacts = $db->fetchAll(
            "SELECT c.id, c.full_name, c.phone 
             FROM contacts c
             {$where}
             ORDER BY c.id ASC",
            $params
        );

        if (empty($contacts)) {
            echo json_encode(['success' => false, 'message' => 'مخاطبی با این فیلتر یافت نشد.', 'count' => 0]);
            exit;
        }

        // Return count for preview (don't send yet)
        if (isset($_POST['preview']) && $_POST['preview'] == '1') {
            echo json_encode([
                'success' => true,
                'count' => count($contacts),
                'message' => count($contacts) . ' مخاطب یافت شد. آیا ارسال شود؟',
            ]);
            exit;
        }

        // Actually send
        $config = $GLOBALS['app_config'];
        $apiToken = $config['sms']['api_token'] ?? '';
        $fromNumber = $config['sms']['from_number'] ?? '+983000505';
        $panelUrl = $config['sms']['api_url'] ?? 'https://edge.ippanel.com/v1/api/send';

        $sent = 0;
        $failed = 0;

        foreach ($contacts as $contact) {
            $sentStatus = 'failed';
            $outboxId = '';
            $errMsg = '';

            if (!empty($apiToken)) {
                $normalizedPhone = $this->normalizePhone($contact->phone);
                
                $smsData = [
                    'sending_type' => 'webservice',
                    'from_number' => $fromNumber,
                    'message' => $message,
                    'params' => [
                        'recipients' => [$normalizedPhone],
                    ],
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $panelUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: ' . $apiToken,
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response);
                if ($result && isset($result->meta->status) && $result->meta->status === true) {
                    $sentStatus = 'sent';
                    $ids = $result->data->message_outbox_ids ?? $result->data->message_ids ?? [];
                    $outboxId = is_array($ids) ? implode(',', $ids) : (string)$ids;
                    $sent++;
                } else {
                    $errMsg = 'خطا در ارسال پیامک';
                    $failed++;
                }
            } else {
                $sentStatus = 'failed';
                $errMsg = 'API token تنظیم نشده';
                $failed++;
            }

            $db->insert('sms_history', [
                'recipient' => $contact->phone,
                'message' => $message,
                'status' => $sentStatus,
                'message_outbox_id' => $outboxId,
                'error_message' => $errMsg,
                'contact_id' => $contact->id,
                'sent_by' => Auth::id(),
            ]);
        }

        ActivityLog::log('send_bulk_sms', 'sms', 0, "پیامک انبوه به {$sent} نفر ارسال شد ({$failed} ناموفق)");

        // Get last error message for debugging
        $lastError = '';
        if ($failed > 0) {
            $lastErr = $db->fetch("SELECT error_message FROM sms_history WHERE status = 'failed' ORDER BY created_at DESC LIMIT 1");
            $lastError = $lastErr->error_message ?? '';
        }

        echo json_encode([
            'success' => $sent > 0,
            'message' => "ارسال شد: {$sent} موفق، {$failed} ناموفق از " . count($contacts) . " مخاطب.",
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($contacts),
            'debug_error' => $lastError,
        ]);
        exit;
    }

    /**
     * Static method to send SMS via webservice API (used by automation engine)
     * Returns array ['success' => bool, 'message' => string, 'outbox_id' => string]
     */
    public static function sendWebservice(string $phone, string $message): array
    {
        $config = $GLOBALS['app_config'];
        $apiToken = $config['sms']['api_token'] ?? '';
        $fromNumber = $config['sms']['from_number'] ?? '+983000505';
        $panelUrl = $config['sms']['api_url'] ?? 'https://edge.ippanel.com/v1/api/send';

        if (empty($apiToken)) {
            return ['success' => false, 'message' => 'API token تنظیم نشده', 'outbox_id' => ''];
        }

        $normalizedPhone = (new self())->normalizePhone($phone);

        $smsData = [
            'sending_type' => 'webservice',
            'from_number' => $fromNumber,
            'message' => $message,
            'params' => [
                'recipients' => [$normalizedPhone],
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $panelUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $apiToken,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);
        if ($result && isset($result->meta->status) && $result->meta->status === true) {
            $ids = $result->data->message_outbox_ids ?? $result->data->message_ids ?? [];
            $outboxId = is_array($ids) ? implode(',', $ids) : (string)$ids;
            return ['success' => true, 'message' => 'ارسال شد', 'outbox_id' => $outboxId];
        }

        $errMsg = $result->meta->message ?? 'خطای نامشخص';
        return ['success' => false, 'message' => $errMsg, 'outbox_id' => ''];
    }

    /**
     * Normalize phone number to E.164 format (+98...)
     */
    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        // Remove any spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Already in E.164 format
        if (strpos($phone, '+98') === 0) {
            return $phone;
        }
        
        // Starts with 0098
        if (strpos($phone, '0098') === 0) {
            return '+' . substr($phone, 2);
        }
        
        // Starts with 98 (without +)
        if (strpos($phone, '98') === 0 && strlen($phone) > 10) {
            return '+' . $phone;
        }
        
        // Starts with 0 (local format like 0912...)
        if (strpos($phone, '0') === 0) {
            return '+98' . substr($phone, 1);
        }
        
        // Just the number without prefix (like 912...)
        if (strlen($phone) === 10 && $phone[0] === '9') {
            return '+98' . $phone;
        }
        
        // Default: assume it needs +98 prefix
        return '+98' . $phone;
    }
}
