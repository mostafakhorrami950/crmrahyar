<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class SmsController
{
    public function showSendForm(array $params): void
    {
        $db = Database::getInstance();
        $deal = $db->fetch(
            "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone
             FROM deals d 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             WHERE d.id = :id",
            [':id' => $params['deal_id']]
        );

        if (!$deal) {
            Session::setFlash('danger', 'معامله مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        View::render('sms/send', [
            'title' => 'ارسال پیامک',
            'deal' => $deal,
        ]);
    }

    public function send(): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $recipient = trim($_POST['recipient'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $dealId = (int)($_POST['deal_id'] ?? 0);
        $contactId = (int)($_POST['contact_id'] ?? 0) ?: null;
        $sendTime = trim($_POST['send_time'] ?? '');

        if (empty($recipient)) {
            $errorMsg = 'لطفا شماره گیرنده را وارد کنید.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $errorMsg]); exit; }
            Session::setFlash('danger', $errorMsg);
            View::redirect('/sms/send/' . $dealId);
        }

        // Format phone number
        if (substr($recipient, 0, 1) === '0') {
            $recipient = '+98' . substr($recipient, 1);
        } elseif (substr($recipient, 0, 1) !== '+') {
            $recipient = '+98' . $recipient;
        }

        $config = $GLOBALS['app_config'];
        $apiToken = $config['sms']['api_token'];
        $fromNumber = $config['sms']['from_number'];

        if (empty($apiToken)) {
            $errorMsg = 'توکن API تنظیم نشده است.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $errorMsg]); exit; }
            Session::setFlash('danger', $errorMsg);
            View::redirect('/settings');
        }

        // Build request payload - Use webservice API per docs
        $payload = [
            'sending_type' => 'webservice',
            'from_number' => $fromNumber,
            'message' => $message,
            'params' => [
                'recipients' => [$recipient]
            ],
        ];
        
        if (!empty($sendTime)) {
            $payload['send_time'] = $sendTime;
        }

        // cURL to IPPanel
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://edge.ippanel.com/v1/api/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $apiToken,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response);
        $status = 'sent';
        $errorMessage = null;
        $outboxId = null;

        if ($result && isset($result->meta) && $result->meta->status === true) {
            $status = 'sent';
            $outboxId = $result->data->message_outbox_ids[0] ?? null;
        } else {
            $status = 'failed';
            $errorMessage = $result->meta->message ?? 'خطا در ارسال';
        }

        // Save to history
        $db = Database::getInstance();
        $db->insert('sms_history', [
            'deal_id' => $dealId ?: null,
            'contact_id' => $contactId,
            'recipient' => $recipient,
            'message' => $message,
            'status' => $status,
            'message_outbox_id' => $outboxId,
            'error_message' => $errorMessage,
            'sent_by' => Auth::id(),
        ]);

        if ($status === 'sent') {
            ActivityLog::log('send_sms', 'sms', $dealId, "پیامک به {$recipient} ارسال شد");
            if ($isAjax) { echo json_encode(['success' => true, 'message' => 'پیامک با موفقیت ارسال شد.']); exit; }
            Session::setFlash('success', 'پیامک با موفقیت ارسال شد.');
        } else {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $errorMessage]); exit; }
            Session::setFlash('danger', "خطا: {$errorMessage}");
        }

        if ($dealId) { View::redirect('/deals/view/' . $dealId); }
        View::redirect('/sms/history');
    }

    public function history(): void
    {
        $db = Database::getInstance();
        $history = $db->fetchAll(
            "SELECT sh.*, u.full_name as sent_by_name, d.title as deal_title
             FROM sms_history sh 
             LEFT JOIN users u ON sh.sent_by = u.id 
             LEFT JOIN deals d ON sh.deal_id = d.id 
             ORDER BY sh.created_at DESC LIMIT 100"
        );

        View::render('sms/history', ['title' => 'تاریخچه پیامک‌ها', 'history' => $history]);
    }
}