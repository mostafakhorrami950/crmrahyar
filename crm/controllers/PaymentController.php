<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class PaymentController
{
    public function create(array $params): void
    {
        $db = Database::getInstance();
        $deal = $db->fetch(
            "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone, c.email as contact_email
             FROM deals d 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             WHERE d.id = :id",
            [':id' => $params['deal_id']]
        );

        if (!$deal) {
            Session::setFlash('danger', 'معامله مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        // Operator can only create payments for own deals
        if (!Auth::ownsDeal((int)$params['deal_id'])) {
            Session::setFlash('danger', 'شما فقط برای معاملات خودتان می‌توانید پرداخت ایجاد کنید.');
            View::redirect('/deals');
        }

        View::render('payment/create', [
            'title' => 'ایجاد لینک پرداخت',
            'deal' => $deal,
        ]);
    }

    public function requestPayment(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $dealId = (int)($_POST['deal_id'] ?? 0);
        
        $amountToman = (int)str_replace(',', '', $_POST['amount'] ?? '0');
        $description = trim($_POST['description'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');

        // Convert Toman to Rial (Zibal uses Rial)
        $amountRial = $amountToman * 10;

        if ($amountRial < 1000) {
            $msg = 'مبلغ باید حداقل ۱۰۰ تومان باشد.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/payment/create/' . $dealId);
        }

        $config = $GLOBALS['app_config'];
        $merchant = $config['zibal']['merchant'];
        $callbackUrl = $config['zibal']['callback_url'];

        // Request payment from Zibal
        $data = [
            'merchant' => $merchant,
            'amount' => $amountRial,
            'callbackUrl' => $callbackUrl,
            'description' => $description ?: 'پرداخت معامله',
            'orderId' => 'DEAL-' . $dealId . '-' . time(),
        ];

        if ($mobile) {
            $data['mobile'] = $mobile;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/request');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response);

        if ($result && isset($result->trackId) && $result->result == 100) {
            // Save payment record
            $db = Database::getInstance();
            $publicToken = $this->generatePublicToken();
            $shortCode = $this->generateShortCode();
            $paymentId = $db->insert('payments', [
                'deal_id' => $dealId,
                'short_code' => $shortCode,
                'amount' => $amountToman,
                'payment_type' => 'online',
                'status' => 'pending',
                'track_id' => $result->trackId,
                'public_token' => $publicToken,
                'description' => $description,
                'created_by' => Auth::id(),
            ]);

            // Get public payment URL
            $config = $GLOBALS['app_config'];
            $publicPayUrl = $config['url'] . '/p/' . $shortCode;

            ActivityLog::log('create_payment', 'payment', $paymentId, "لینک پرداخت به مبلغ " . number_format($amountToman) . " تومان ایجاد شد");

            // Fire automation trigger: payment_created
            $contact = $db->fetch(
                "SELECT c.full_name as contact_name, c.phone as contact_phone, c.email as contact_email,
                        d.title as deal_title, d.amount as deal_amount, d.assigned_to, 
                        d.pipeline_id, d.source, s.name as stage_name, p.name as pipeline_name
                 FROM deals d 
                 LEFT JOIN contacts c ON d.contact_id = c.id
                 LEFT JOIN stages s ON d.stage_id = s.id
                 LEFT JOIN pipelines p ON d.pipeline_id = p.id
                 WHERE d.id = :id", [':id' => $dealId]);
            
            $automationExtra = [
                'deal_id' => $dealId,
                'contact_id' => $contact->contact_phone ?? 0,
                'contact_name' => $contact->contact_name ?? '',
                'contact_phone' => $contact->contact_phone ?? '',
                'contact_email' => $contact->contact_email ?? '',
                'title' => $contact->deal_title ?? '',
                'amount' => $amountToman,
                'assigned_to' => $contact->assigned_to ?? 0,
                'pipeline_id' => $contact->pipeline_id ?? 0,
                'stage_name' => $contact->stage_name ?? '',
                'pipeline_name' => $contact->pipeline_name ?? '',
                'source' => $contact->source ?? '',
                'payment_id' => $paymentId,
                'payment_link' => $publicPayUrl,
                'payment_short_link' => $publicPayUrl,
                'payment_amount' => $amountToman,
            ];
            ob_start();
            \Controllers\AutomationController::execute('payment_created', 'deal', $dealId, $automationExtra);
            ob_end_clean();

            if ($isAjax) {
                // Return JSON with redirect URL to payment gateway
                echo json_encode([
                    'success' => true,
                    'message' => 'لینک پرداخت ایجاد شد. می‌توانید لینک را کپی کرده و برای مشتری ارسال کنید.',
                    'redirect' => 'https://gateway.zibal.ir/start/' . $result->trackId,
                    'public_link' => $publicPayUrl,
                    'payment_id' => $paymentId,
                ]);
                exit;
            }

            // Redirect to Zibal payment gateway
            header('Location: https://gateway.zibal.ir/start/' . $result->trackId);
            exit;
        } else {
            $errorMsg = 'خطا در اتصال به درگاه پرداخت';
            if ($result && isset($result->message)) {
                $errorMsg = $result->message;
            }
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $errorMsg]); exit; }
            Session::setFlash('danger', $errorMsg);
            View::redirect('/payment/create/' . $dealId);
        }
    }

    public function verify(): void
    {
        $trackId = $_GET['trackId'] ?? $_POST['trackId'] ?? '';
        $success = $_GET['success'] ?? '0';
        $status = $_GET['status'] ?? '';

        if (empty($trackId)) {
            Session::setFlash('danger', 'اطلاعات پرداخت نامعتبر است.');
            View::redirect('/deals');
        }

        // Verify with Zibal
        $config = $GLOBALS['app_config'];
        $merchant = $config['zibal']['merchant'];

        $data = [
            'merchant' => $merchant,
            'trackId' => $trackId,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/verify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        $db = Database::getInstance();
        $payment = $db->fetch("SELECT * FROM payments WHERE track_id = :track_id", [':track_id' => $trackId]);

        if ($result && $result->result == 100) {
            // Payment successful
                if ($payment) {
                    $db->update('payments', [
                        'status' => 'success',
                        'ref_number' => $result->refNumber ?? '',
                        'card_number' => $result->cardNumber ?? '',
                        'paid_at' => date('Y-m-d H:i:s'),
                    ], 'id = :id', [':id' => $payment->id]);

                    // Update deal if exists
                    if ($payment->deal_id) {
                        $db->update('deals', [
                            'is_won' => 1,
                            'closed_at' => date('Y-m-d H:i:s'),
                        ], 'id = :id', [':id' => $payment->deal_id]);

                        ActivityLog::log('payment_success', 'payment', $payment->id, "پرداخت معامله با موفقیت انجام شد. کد پیگیری: {$trackId}");

                        // Fire automation trigger: payment_verified
                        $dealInfo = $db->fetch(
                            "SELECT d.title, d.amount, d.assigned_to, d.pipeline_id, d.source,
                                    c.full_name as contact_name, c.phone as contact_phone, c.email as contact_email,
                                    s.name as stage_name, p.name as pipeline_name
                             FROM deals d 
                             LEFT JOIN contacts c ON d.contact_id = c.id
                             LEFT JOIN stages s ON d.stage_id = s.id
                             LEFT JOIN pipelines p ON d.pipeline_id = p.id
                             WHERE d.id = :id", [':id' => $payment->deal_id]);
                        
                        $shortLink = !empty($payment->short_code) ? ($config['url'] ?? '') . '/p/' . $payment->short_code : '';
                        
                        ob_start();
                        \Controllers\AutomationController::execute('payment_verified', 'deal', (int)$payment->deal_id, [
                            'deal_id' => (int)$payment->deal_id,
                            'contact_id' => 0,
                            'contact_name' => $dealInfo->contact_name ?? '',
                            'contact_phone' => $dealInfo->contact_phone ?? '',
                            'contact_email' => $dealInfo->contact_email ?? '',
                            'title' => $dealInfo->title ?? '',
                            'amount' => $payment->amount,
                            'assigned_to' => $dealInfo->assigned_to ?? 0,
                            'pipeline_id' => $dealInfo->pipeline_id ?? 0,
                            'stage_name' => $dealInfo->stage_name ?? '',
                            'pipeline_name' => $dealInfo->pipeline_name ?? '',
                            'source' => $dealInfo->source ?? '',
                            'payment_id' => (int)$payment->id,
                            'payment_link' => $shortLink,
                            'payment_short_link' => $shortLink,
                            'payment_amount' => $payment->amount,
                        ]);
                        ob_end_clean();
                    }

                    Session::setFlash('success', 'پرداخت با موفقیت انجام شد. کد پیگیری: ' . $trackId);
                    if ($payment->deal_id) {
                        View::redirect('/deals/view/' . $payment->deal_id);
                    }
            }
        } else {
            // Payment failed
            if ($payment) {
                $db->update('payments', ['status' => 'failed'], 'id = :id', [':id' => $payment->id]);
            }

            $errorMsg = 'پرداخت ناموفق بود.';
            if ($result && isset($result->message)) {
                $errorMsg = $result->message;
            }
            Session::setFlash('danger', $errorMsg);
            if ($payment && $payment->deal_id) {
                View::redirect('/deals/view/' . $payment->deal_id);
            }
        }

        View::redirect('/payment/history');
    }

    public function callback(): void
    {
        // Handle POST callback from Zibal (Lazy method)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input && isset($input['trackId'])) {
            $this->verifyCallback($input['trackId']);
        }
        
        echo json_encode(['status' => 'ok']);
        exit;
    }

    private function verifyCallback(string $trackId): void
    {
        $config = $GLOBALS['app_config'];
        $merchant = $config['zibal']['merchant'];

        $data = [
            'merchant' => $merchant,
            'trackId' => $trackId,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/verify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        if ($result && $result->result == 100) {
            $db = Database::getInstance();
            $payment = $db->fetch("SELECT * FROM payments WHERE track_id = :track_id", [':track_id' => $trackId]);
            
            if ($payment && $payment->status !== 'success') {
                $db->update('payments', [
                    'status' => 'success',
                    'ref_number' => $result->refNumber ?? '',
                    'card_number' => $result->cardNumber ?? '',
                    'paid_at' => date('Y-m-d H:i:s'),
                ], 'id = :id', [':id' => $payment->id]);

                if ($payment->deal_id) {
                    $db->update('deals', ['is_won' => 1, 'closed_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $payment->deal_id]);
                }
            }
        }
    }

    public function inquiry(array $params): void
    {
        $config = $GLOBALS['app_config'];
        $merchant = $config['zibal']['merchant'];

        $data = [
            'merchant' => $merchant,
            'trackId' => $params['track_id'],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/inquiry');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);
        View::json($result);
    }

    public function history(): void
    {
        $db = Database::getInstance();
        $user = Auth::user();
        
        $where = "WHERE 1=1";
        $params = [];

        // Operators can only see payments for their own deals
        if ($user->role_slug === 'operator') {
            $where .= " AND (d.assigned_to = :user_id OR d.created_by = :user_id2)";
            $params[':user_id'] = $user->id;
            $params[':user_id2'] = $user->id;
        }

        $payments = $db->fetchAll(
            "SELECT p.*, d.title as deal_title, c.full_name as contact_name, c.phone as contact_phone, d.contact_id
             FROM payments p 
             LEFT JOIN deals d ON p.deal_id = d.id 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             {$where}
             ORDER BY p.created_at DESC",
            $params
        );

        View::render('payment/history', [
            'title' => 'تاریخچه پرداخت‌ها',
            'payments' => $payments,
        ]);
    }

    // ============================================
    // Public Payment Page (no authentication)
    // ============================================

    /**
     * Show public payment page by token
     */
    public function publicPayPage(array $params): void
    {
        $token = $params['token'] ?? '';
        if (empty($token)) {
            $this->showPublicError('لینک پرداخت نامعتبر است.');
            return;
        }

        $db = Database::getInstance();
        $payment = $db->fetch(
            "SELECT * FROM payments WHERE short_code = :token AND status = 'pending'",
            [':token' => $token]
        );
        if (!$payment) {
            $payment = $db->fetch(
                "SELECT * FROM payments WHERE public_token = :token AND status = 'pending'",
                [':token' => $token]
            );
        }

        if (!$payment) {
            $this->showPublicError('لینک پرداخت نامعتبر یا منقضی شده است.');
            return;
        }

        // Get deal info
        $deal = null;
        if ($payment->deal_id) {
            $deal = $db->fetch(
                "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone
                 FROM deals d
                 LEFT JOIN contacts c ON d.contact_id = c.id
                 WHERE d.id = :id",
                [':id' => $payment->deal_id]
            );
        }

        // Render public view without layout - make $config available in scope
        $config = $GLOBALS['app_config'];
        $config['features'] = [];
        require __DIR__ . '/../views/payment/public.php';
        exit;
    }

    /**
     * Submit payment from public page (AJAX) - creates Zibal request
     */
    public function publicSubmit(array $params = []): void
    {
        $token = $_POST['token'] ?? '';

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'لینک پرداخت نامعتبر است.']);
            exit;
        }

        $db = Database::getInstance();
        $payment = $db->fetch(
            "SELECT * FROM payments WHERE short_code = :token AND status = 'pending'",
            [':token' => $token]
        );
        if (!$payment) {
            $payment = $db->fetch(
                "SELECT * FROM payments WHERE public_token = :token AND status = 'pending'",
                [':token' => $token]
            );
        }

        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'لینک پرداخت نامعتبر یا منقضی شده است.']);
            exit;
        }

        // Convert Toman to Rial for Zibal
        $amountRial = $payment->amount * 10;

        $config = $GLOBALS['app_config'];
        $merchant = $config['zibal']['merchant'];
        $callbackUrl = $config['url'] . '/payment/result';

        $data = [
            'merchant' => $merchant,
            'amount' => $amountRial,
            'callbackUrl' => $callbackUrl,
            'description' => $payment->description ?: 'پرداخت آنلاین',
            'orderId' => 'PUB-' . $payment->id . '-' . time(),
        ];

        // Get mobile from deal contact
        if ($payment->deal_id) {
            $deal = $db->fetch("SELECT c.phone FROM deals d LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.id = :id", [':id' => $payment->deal_id]);
            if ($deal && !empty($deal->phone)) {
                $data['mobile'] = $deal->phone;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/request');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response);

        if ($result && isset($result->trackId) && $result->result == 100) {
            // Update payment with track ID
            $db->update('payments', [
                'track_id' => $result->trackId,
            ], 'id = :id', [':id' => $payment->id]);

            echo json_encode([
                'success' => true,
                'message' => 'در حال اتصال به درگاه پرداخت...',
                'redirect' => 'https://gateway.zibal.ir/start/' . $result->trackId
            ]);
        } else {
            $errorMsg = 'خطا در اتصال به درگاه پرداخت';
            if ($result && isset($result->message)) {
                $errorMsg = $result->message;
            }
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }

    /**
     * Public verify result page - shown after payment completes
     */
    public function publicVerifyResult(array $params = []): void
    {
        $trackId = $_GET['trackId'] ?? '';

        $success = false;
        $message = '';
        $refNumber = '';
        $amount = 0;
        $returnUrl = '';

        if (!empty($trackId)) {
            $db = Database::getInstance();
            $payment = $db->fetch("SELECT * FROM payments WHERE track_id = :track_id", [':track_id' => $trackId]);

            if ($payment) {
                $success = ($payment->status === 'success');
                $refNumber = $payment->ref_number ?? '';
                $amount = $payment->amount;
                
                // If still pending, verify with Zibal
                if ($payment->status === 'pending' || empty($payment->status === 'success')) {
                    // Re-verify
                    $config = $GLOBALS['app_config'];
                    $merchant = $config['zibal']['merchant'];

                    $data = [
                        'merchant' => $merchant,
                        'trackId' => $trackId,
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/verify');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $result = json_decode($response);
                    if ($result && $result->result == 100) {
                        $db->update('payments', [
                            'status' => 'success',
                            'ref_number' => $result->refNumber ?? '',
                            'card_number' => $result->cardNumber ?? '',
                            'paid_at' => date('Y-m-d H:i:s'),
                        ], 'id = :id', [':id' => $payment->id]);

                        if ($payment->deal_id) {
                            $db->update('deals', ['is_won' => 1, 'closed_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $payment->deal_id]);
                        }

                        $success = true;
                        $refNumber = $result->refNumber ?? '';
                    } else {
                        $message = 'پرداخت ناموفق بود.';
                    }
                }

                // Get return URL from payment
                $returnUrl = $payment->return_url ?? '';

                if ($success) {
                    $message = 'پرداخت شما با موفقیت انجام شد.';
                }
            } else {
                $message = 'اطلاعات پرداخت یافت نشد.';
            }
        } else {
            $message = 'اطلاعات پرداخت نامعتبر است.';
        }

        $config = $GLOBALS['app_config'];
        $publicToken = $payment->public_token ?? '';
        $dealId = $payment->deal_id ?? '';
        require __DIR__ . '/../views/payment/result.php';
        exit;
    }

    /**
     * Generate public token for a payment
     */
    private function generatePublicToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate a short unique code for payment links (7 chars)
     */
    private function generateShortCode(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyz23456789';
        $code = '';
        for ($i = 0; $i < 7; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Show error page for public payment
     */
    private function showPublicError(string $message): void
    {
        $config = $GLOBALS['app_config'];
        $success = false;
        $trackId = '';
        $refNumber = '';
        $amount = 0;
        $returnUrl = '';
        $publicToken = $_GET['token'] ?? '';
        $dealId = '';
        require __DIR__ . '/../views/payment/result.php';
        exit;
    }

    public function delete(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $payment = $db->fetch("SELECT * FROM payments WHERE id = :id", [':id' => $params['id']]);
        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'پرداخت یافت نشد']);
            exit;
        }

        // Only admin or the deal owner can delete
        $isAdmin = Auth::hasPermission('settings.manage') || Auth::hasPermission('users.manage');
        if (!$isAdmin) {
            $deal = $db->fetch("SELECT assigned_to, created_by FROM deals WHERE id = :id", [':id' => $payment->deal_id]);
            if (!$deal || ($deal->assigned_to != Auth::id() && $deal->created_by != Auth::id())) {
                echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
                exit;
            }
        }

        $db->delete('payments', 'id = :id', [':id' => $params['id']]);
        ActivityLog::log('delete_payment', 'payment', $params['id'], "پرداخت {$params['id']} حذف شد");

        echo json_encode(['success' => true, 'message' => 'پرداخت حذف شد']);
        exit;
    }
}
