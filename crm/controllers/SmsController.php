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

        $where = "WHERE 1=1";
        $params = [];

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
             LIMIT 200",
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
        ]);
    }

    public function showSendForm(): void
    {
        $db = Database::getInstance();
        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");
        $categories = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY sort_order ASC, name ASC");

        View::render('sms/send', [
            'title' => 'ارسال پیامک',
            'pipelines' => $pipelines,
            'categories' => $categories,
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
        $fromNumber = $config['sms']['from_number'] ?? '+983000505';
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
            $smsData = [
                'from' => $fromNumber,
                'to' => [$phone],
                'text' => $message,
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

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response);
            if ($result && isset($result->data->bulk_id)) {
                $sentStatus = 'sent';
                $outboxId = (string)$result->data->bulk_id;
            } elseif ($result && isset($result->data->message_ids)) {
                $sentStatus = 'sent';
                $outboxId = is_array($result->data->message_ids) ? implode(',', $result->data->message_ids) : (string)$result->data->message_ids;
            } elseif ($result && isset($result->outbox_id)) {
                $sentStatus = 'sent';
                $outboxId = is_array($result->outbox_id) ? implode(',', $result->outbox_id) : (string)$result->outbox_id;
            } else {
                $errorMsg = $result->message ?? $result->error ?? json_encode($result) ?? 'خطای نامشخص';
            }
        } else {
            $sentStatus = 'sent';
            $errorMsg = 'API token تنظیم نشده - تست';
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
                $smsData = [
                    'from' => $fromNumber,
                    'to' => [$contact->phone],
                    'text' => $message,
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

                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response);
                if ($result && isset($result->data->bulk_id)) {
                    $sentStatus = 'sent';
                    $outboxId = (string)$result->data->bulk_id;
                    $sent++;
                } elseif ($result && isset($result->data->message_ids)) {
                    $sentStatus = 'sent';
                    $outboxId = is_array($result->data->message_ids) ? implode(',', $result->data->message_ids) : (string)$result->data->message_ids;
                    $sent++;
                } elseif ($result && isset($result->outbox_id)) {
                    $sentStatus = 'sent';
                    $outboxId = is_array($result->outbox_id) ? implode(',', $result->outbox_id) : (string)$result->outbox_id;
                    $sent++;
                } else {
                    $errMsg = $result->message ?? $result->error ?? json_encode($result) ?? 'خطای نامشخص';
                    $failed++;
                }
            } else {
                $sentStatus = 'sent';
                $sent++;
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
}
