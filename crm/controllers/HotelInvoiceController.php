<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class HotelInvoiceController
{
    /**
     * Get invoice settings
     */
    private function getSettings(): array
    {
        try {
            $db = Database::getInstance();
            $settings = $db->fetchAll("SELECT setting_key, setting_value FROM invoice_settings");
            $map = [];
            foreach ($settings as $s) {
                $map[$s->setting_key] = $s->setting_value;
            }
            return $map;
        } catch (\Exception $e) {
            return [
                'invoice_title' => 'فاکتور هتل',
                'invoice_subtitle' => 'آژانس مسافرتی',
                'invoice_company_name' => 'علاءالدین سفیر اسمان',
                'invoice_logo_url' => '',
                'invoice_primary_color' => '#0d6efd',
                'invoice_secondary_color' => '#6c757d',
                'invoice_success_color' => '#198754',
            ];
        }
    }

    /**
     * List all invoices
     */
    public function index(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $where = "WHERE 1=1";
        $params = [];

        if ($search) {
            $where .= " AND (hi.hotel_name LIKE :search OR d.title LIKE :search OR c.full_name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        if ($status) {
            $where .= " AND hi.invoice_status = :status";
            $params[':status'] = $status;
        }

        $invoices = $db->fetchAll(
            "SELECT hi.*, d.title as deal_title, c.full_name as contact_name
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             {$where}
             ORDER BY hi.created_at DESC",
            $params
        );

        View::render('hotel_invoice/index', [
            'title' => 'لیست فاکتورهای هتل',
            'invoices' => $invoices,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show hotel invoice form for a deal
     */
    public function create(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $dealId = (int)$params['deal_id'];

        $deal = $db->fetch(
            "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.id = :id",
            [':id' => $dealId]
        );

        if (!$deal) {
            Session::setFlash('danger', 'معامله مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        if (!Auth::ownsDeal($dealId)) {
            Session::setFlash('danger', 'شما فقط برای معاملات خودتان می‌توانید فاکتور صادر کنید.');
            View::redirect('/deals');
        }

        $invoices = $db->fetchAll(
            "SELECT * FROM hotel_invoices WHERE deal_id = :deal_id ORDER BY created_at DESC",
            [':deal_id' => $dealId]
        );

        $invoiceSettings = $this->getSettings();

        View::render('hotel_invoice/create', [
            'title' => 'فاکتور هتل',
            'deal' => $deal,
            'invoices' => $invoices,
            'invoiceSettings' => $invoiceSettings,
        ]);
    }

    /**
     * Store a new hotel invoice
     */
    public function store(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();

        $dealId = (int)($_POST['deal_id'] ?? 0);
        $hotelName = trim($_POST['hotel_name'] ?? '');
        $checkInDate = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $adultsCount = (int)($_POST['adults_count'] ?? 0);
        $children3to5Count = (int)($_POST['children_3to5_count'] ?? 0);
        $childrenUnder3Count = (int)($_POST['children_under3_count'] ?? 0);
        $pricePerPersonNight = (float)str_replace(',', '', $_POST['price_per_person_night'] ?? '0');
        $newPricePerPersonNightRaw = $_POST['new_price_per_person_night'] ?? '';
        $depositAmount = (float)str_replace(',', '', $_POST['deposit_amount'] ?? '0');
        $invoiceType = $_POST['invoice_type'] ?? 'proforma';
        $invoiceStatus = $_POST['invoice_status'] ?? 'draft';
        $notes = trim($_POST['notes'] ?? '');

        if (!$dealId || empty($hotelName) || empty($checkInDate) || empty($checkOutDate)) {
            $msg = 'لطفا تمام فیلدهای الزامی را پر کنید.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/create/' . $dealId);
        }

        // Calculate nights
        $checkIn = new \DateTime($checkInDate);
        $checkOut = new \DateTime($checkOutDate);
        $nights = $checkOut->diff($checkIn)->days;

        if ($nights <= 0) {
            $msg = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/create/' . $dealId);
        }

        // Total persons
        $personsCount = $adultsCount + $children3to5Count + $childrenUnder3Count;

        // Calculate effective person-nights
        $adultPersonNights = $adultsCount * $nights;
        $child3to5PersonNights = $children3to5Count * $nights;
        $childUnder3PersonNights = $childrenUnder3Count * $nights;
        $personNightCount = $adultPersonNights + $child3to5PersonNights + $childUnder3PersonNights;

        // Calculate total amount
        $totalAmount = ($adultsCount * $nights * $pricePerPersonNight)
                     + ($children3to5Count * $nights * $pricePerPersonNight * 0.5)
                     + ($childrenUnder3Count * $nights * 0);

        // Handle new price and discount
        $newPricePerPersonNight = null;
        $discountAmount = 0;
        $discountPercent = 0;
        $finalAmount = $totalAmount;

        if (!empty($newPricePerPersonNightRaw)) {
            $newPricePerPersonNight = (float)str_replace(',', '', $newPricePerPersonNightRaw);
            $totalAmount = ($adultsCount * $nights * $newPricePerPersonNight)
                         + ($children3to5Count * $nights * $newPricePerPersonNight * 0.5)
                         + ($childrenUnder3Count * $nights * 0);
            $discountAmount = ($adultsCount * $nights * $pricePerPersonNight)
                            + ($children3to5Count * $nights * $pricePerPersonNight * 0.5)
                            - $totalAmount;
            if ($discountAmount < 0) $discountAmount = 0;
            $originalTotal = ($adultsCount * $nights * $pricePerPersonNight)
                           + ($children3to5Count * $nights * $pricePerPersonNight * 0.5);
            $discountPercent = $originalTotal > 0 ? round(($discountAmount / $originalTotal) * 100, 2) : 0;
            $finalAmount = $totalAmount;
        } else {
            $discountPercent = 0;
            $finalAmount = $totalAmount;
        }

        try {
            $invoiceId = $db->insert('hotel_invoices', [
                'deal_id' => $dealId,
                'hotel_name' => $hotelName,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'persons_count' => $personsCount,
                'adults_count' => $adultsCount,
                'children_3to5_count' => $children3to5Count,
                'children_under3_count' => $childrenUnder3Count,
                'person_night_count' => $personNightCount,
                'price_per_person_night' => $pricePerPersonNight,
                'total_amount' => $totalAmount,
                'new_price_per_person_night' => $newPricePerPersonNight,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'deposit_amount' => $depositAmount,
                'notes' => $notes,
                'invoice_status' => $invoiceStatus,
                'invoice_type' => $invoiceType,
                'created_by' => Auth::id(),
            ]);

            ActivityLog::log('create_hotel_invoice', 'hotel_invoice', $invoiceId, "فاکتور هتل {$hotelName} برای معامله {$dealId} ایجاد شد");

            // Auto-create payment link
            $this->createPaymentLink($invoiceId, $dealId, $finalAmount, $depositAmount);

            if ($isAjax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'فاکتور هتل با موفقیت ایجاد شد.',
                    'invoice_id' => $invoiceId,
                ]);
                exit;
            }
            Session::setFlash('success', 'فاکتور هتل با موفقیت ایجاد شد.');
            View::redirect('/hotel-invoice/create/' . $dealId);
        } catch (\Exception $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/hotel-invoice/create/' . $dealId);
        }
    }

    /**
     * Show edit form for a hotel invoice
     */
    public function edit(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             WHERE hi.id = :id",
            [':id' => $invoiceId]
        );

        if (!$invoice) {
            Session::setFlash('danger', 'فاکتور مورد نظر یافت نشد.');
            View::redirect('/hotel-invoice');
        }

        $deal = $db->fetch(
            "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.id = :id",
            [':id' => $invoice->deal_id]
        );

        $invoiceSettings = $this->getSettings();

        View::render('hotel_invoice/edit', [
            'title' => 'ویرایش فاکتور هتل',
            'invoice' => $invoice,
            'deal' => $deal,
            'invoiceSettings' => $invoiceSettings,
        ]);
    }

    /**
     * Update an existing hotel invoice
     */
    public function update(array $params): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE id = :id", [':id' => $invoiceId]);
        if (!$invoice) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'فاکتور یافت نشد.']); exit; }
            Session::setFlash('danger', 'فاکتور یافت نشد.');
            View::redirect('/hotel-invoice');
        }

        $hotelName = trim($_POST['hotel_name'] ?? '');
        $checkInDate = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $adultsCount = (int)($_POST['adults_count'] ?? 0);
        $children3to5Count = (int)($_POST['children_3to5_count'] ?? 0);
        $childrenUnder3Count = (int)($_POST['children_under3_count'] ?? 0);
        $pricePerPersonNight = (float)str_replace(',', '', $_POST['price_per_person_night'] ?? '0');
        $newPricePerPersonNightRaw = $_POST['new_price_per_person_night'] ?? '';
        $depositAmount = (float)str_replace(',', '', $_POST['deposit_amount'] ?? '0');
        $invoiceType = $_POST['invoice_type'] ?? 'proforma';
        $invoiceStatus = $_POST['invoice_status'] ?? 'draft';
        $notes = trim($_POST['notes'] ?? '');

        if (empty($hotelName) || empty($checkInDate) || empty($checkOutDate)) {
            $msg = 'لطفا تمام فیلدهای الزامی را پر کنید.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }

        // Calculate nights
        $checkIn = new \DateTime($checkInDate);
        $checkOut = new \DateTime($checkOutDate);
        $nights = $checkOut->diff($checkIn)->days;

        if ($nights <= 0) {
            $msg = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }

        $personsCount = $adultsCount + $children3to5Count + $childrenUnder3Count;
        $personNightCount = ($adultsCount + $children3to5Count + $childrenUnder3Count) * $nights;

        $totalAmount = ($adultsCount * $nights * $pricePerPersonNight)
                     + ($children3to5Count * $nights * $pricePerPersonNight * 0.5);

        $newPricePerPersonNight = null;
        $discountAmount = 0;
        $discountPercent = 0;
        $finalAmount = $totalAmount;

        if (!empty($newPricePerPersonNightRaw)) {
            $newPricePerPersonNight = (float)str_replace(',', '', $newPricePerPersonNightRaw);
            $newTotal = ($adultsCount * $nights * $newPricePerPersonNight)
                      + ($children3to5Count * $nights * $newPricePerPersonNight * 0.5);
            $discountAmount = $totalAmount - $newTotal;
            if ($discountAmount < 0) $discountAmount = 0;
            $discountPercent = $totalAmount > 0 ? round(($discountAmount / $totalAmount) * 100, 2) : 0;
            $finalAmount = $newTotal;
        }

        try {
            $db->update('hotel_invoices', [
                'hotel_name' => $hotelName,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'persons_count' => $personsCount,
                'adults_count' => $adultsCount,
                'children_3to5_count' => $children3to5Count,
                'children_under3_count' => $childrenUnder3Count,
                'person_night_count' => $personNightCount,
                'price_per_person_night' => $pricePerPersonNight,
                'total_amount' => $totalAmount,
                'new_price_per_person_night' => $newPricePerPersonNight,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'deposit_amount' => $depositAmount,
                'notes' => $notes,
                'invoice_status' => $invoiceStatus,
                'invoice_type' => $invoiceType,
            ], 'id = :id', [':id' => $invoiceId]);

            ActivityLog::log('update_hotel_invoice', 'hotel_invoice', $invoiceId, "فاکتور هتل {$hotelName} بروزرسانی شد");

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'فاکتور بروزرسانی شد.', 'invoice_id' => $invoiceId]);
                exit;
            }
            Session::setFlash('success', 'فاکتور با موفقیت بروزرسانی شد.');
            View::redirect('/hotel-invoice/view/' . $invoiceId);
        } catch (\Exception $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }
    }

    /**
     * Create payment link for invoice
     */
    private function createPaymentLink(int $invoiceId, int $dealId, float $finalAmount, float $depositAmount): void
    {
        try {
            $db = Database::getInstance();
            $config = $GLOBALS['app_config'];
            $merchant = $config['zibal']['merchant'];
            $callbackUrl = $config['zibal']['callback_url'];

            $payAmount = ($depositAmount > 0) ? $depositAmount : $finalAmount;
            $amountRial = $payAmount * 10;

            if ($amountRial < 1000) return;

            $data = [
                'merchant' => $merchant,
                'amount' => $amountRial,
                'callbackUrl' => $callbackUrl,
                'description' => 'پرداخت فاکتور هتل #' . $invoiceId,
                'orderId' => 'INV-' . $invoiceId . '-' . time(),
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://gateway.zibal.ir/v1/request');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($result && isset($result['result']) && $result['result'] === 100) {
                $trackId = $result['trackId'];
                $publicToken = bin2hex(random_bytes(16));

                $db->insert('payments', [
                    'deal_id' => $dealId,
                    'amount' => $payAmount,
                    'currency' => 'IRR',
                    'payment_type' => 'online',
                    'status' => 'pending',
                    'track_id' => $trackId,
                    'description' => 'پرداخت فاکتور هتل #' . $invoiceId,
                    'created_by' => Auth::id(),
                ]);

                $db->update('hotel_invoices', [
                    'payment_token' => $publicToken,
                ], 'id = :id', [':id' => $invoiceId]);
            }
        } catch (\Exception $e) {
            error_log('Failed to create payment link for invoice ' . $invoiceId . ': ' . $e->getMessage());
        }
    }

    /**
     * View a specific hotel invoice
     */
    public function view(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title, d.amount as deal_amount,
                    c.full_name as contact_name, c.phone as contact_phone
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE hi.id = :id",
            [':id' => $invoiceId]
        );

        if (!$invoice) {
            Session::setFlash('danger', 'فاکتور مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        $invoiceSettings = $this->getSettings();

        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE deal_id = :deal_id ORDER BY created_at DESC",
            [':deal_id' => $invoice->deal_id]
        );

        View::render('hotel_invoice/view', [
            'title' => 'فاکتور هتل: ' . $invoice->hotel_name,
            'invoice' => $invoice,
            'invoiceSettings' => $invoiceSettings,
            'payments' => $payments,
        ]);
    }

    /**
     * Print a hotel invoice
     */
    public function print(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title, d.amount as deal_amount,
                    c.full_name as contact_name, c.phone as contact_phone
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE hi.id = :id",
            [':id' => $invoiceId]
        );

        if (!$invoice) {
            Session::setFlash('danger', 'فاکتور مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        $invoiceSettings = $this->getSettings();

        $config = $GLOBALS['app_config'];
        $viewPath = __DIR__ . '/../views/hotel_invoice/print.php';
        extract(['title' => 'چاپ فاکتور هتل', 'invoice' => $invoice, 'config' => $config, 'invoiceSettings' => $invoiceSettings]);
        require $viewPath;
        exit;
    }

    /**
     * Delete a hotel invoice
     */
    public function delete(array $params): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE id = :id", [':id' => $invoiceId]);
        if (!$invoice) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'فاکتور یافت نشد.']); exit; }
            Session::setFlash('danger', 'فاکتور یافت نشد.');
            View::redirect('/deals');
        }

        try {
            $db->delete('hotel_invoices', 'id = :id', [':id' => $invoiceId]);
            ActivityLog::log('delete_hotel_invoice', 'hotel_invoice', $invoiceId, "فاکتور هتل {$invoice->hotel_name} حذف شد");

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'فاکتور حذف شد.']);
                exit;
            }
            Session::setFlash('success', 'فاکتور حذف شد.');
            View::redirect('/hotel-invoice/create/' . $invoice->deal_id);
        } catch (\Exception $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/hotel-invoice/create/' . $invoice->deal_id);
        }
    }

    /**
     * Update invoice status
     */
    public function updateStatus(array $params): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];
        $status = $_POST['status'] ?? '';

        $validStatuses = ['draft', 'final', 'paid', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'وضعیت نامعتبر.']); exit; }
            Session::setFlash('danger', 'وضعیت نامعتبر.');
            View::redirect('/deals');
        }

        try {
            $db->update('hotel_invoices', ['invoice_status' => $status], 'id = :id', [':id' => $invoiceId]);
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'وضعیت فاکتور بروزرسانی شد.']);
                exit;
            }
            Session::setFlash('success', 'وضعیت فاکتور بروزرسانی شد.');
            View::redirect('/hotel-invoice/view/' . $invoiceId);
        } catch (\Exception $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/hotel-invoice/view/' . $invoiceId);
        }
    }

    /**
     * API: Calculate invoice amounts (AJAX)
     */
    public function calculate(): void
    {
        header('Content-Type: application/json');

        $adultsCount = (int)($_POST['adults_count'] ?? 0);
        $children3to5Count = (int)($_POST['children_3to5_count'] ?? 0);
        $childrenUnder3Count = (int)($_POST['children_under3_count'] ?? 0);
        $checkInDate = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $pricePerPersonNight = (float)str_replace(',', '', $_POST['price_per_person_night'] ?? '0');
        $newPricePerPersonNightRaw = $_POST['new_price_per_person_night'] ?? '';

        $nights = 0;
        $personNightCount = 0;
        $totalAmount = 0;
        $newPricePerPersonNight = null;
        $discountAmount = 0;
        $discountPercent = 0;
        $finalAmount = 0;
        $personsCount = $adultsCount + $children3to5Count + $childrenUnder3Count;

        if (!empty($checkInDate) && !empty($checkOutDate)) {
            $checkIn = new \DateTime($checkInDate);
            $checkOut = new \DateTime($checkOutDate);
            $nights = $checkOut->diff($checkIn)->days;
            if ($nights < 0) $nights = 0;

            $personNightCount = $personsCount * $nights;

            $totalAmount = ($adultsCount * $nights * $pricePerPersonNight)
                         + ($children3to5Count * $nights * $pricePerPersonNight * 0.5);

            if (!empty($newPricePerPersonNightRaw)) {
                $newPricePerPersonNight = (float)str_replace(',', '', $newPricePerPersonNightRaw);
                $newTotalAmount = ($adultsCount * $nights * $newPricePerPersonNight)
                                + ($children3to5Count * $nights * $newPricePerPersonNight * 0.5);
                $discountAmount = $totalAmount - $newTotalAmount;
                if ($discountAmount < 0) $discountAmount = 0;
                $discountPercent = $totalAmount > 0 ? round(($discountAmount / $totalAmount) * 100, 2) : 0;
                $totalAmount = $newTotalAmount;
                $finalAmount = $totalAmount;
            } else {
                $finalAmount = $totalAmount;
            }
        }

        echo json_encode([
            'success' => true,
            'nights' => $nights,
            'persons_count' => $personsCount,
            'person_night_count' => $personNightCount,
            'total_amount' => $totalAmount,
            'new_price_per_person_night' => $newPricePerPersonNight,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ]);
        exit;
    }

    // ============================================
    // Public Hotel Invoice (no authentication)
    // ============================================

    /**
     * Public view of hotel invoice for online payment
     */
    public function publicView(array $params): void
    {
        $token = $params['token'] ?? '';
        if (empty($token)) {
            $this->showPublicError('لینک نامعتبر است.');
            return;
        }

        $db = Database::getInstance();
        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title,
                    c.full_name as contact_name, c.phone as contact_phone
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE hi.payment_token = :token",
            [':token' => $token]
        );

        if (!$invoice) {
            $this->showPublicError('فاکتور یافت نشد.');
            return;
        }

        $invoiceSettings = $this->getSettings();

        // Determine payment amount
        $payAmount = ($invoice->deposit_amount > 0) ? $invoice->deposit_amount : $invoice->final_amount;

        $config = $GLOBALS['app_config'];
        $viewPath = __DIR__ . '/../views/hotel_invoice/public.php';
        extract(['invoice' => $invoice, 'config' => $config, 'invoiceSettings' => $invoiceSettings, 'payAmount' => $payAmount]);
        require $viewPath;
        exit;
    }

    /**
     * Process payment for hotel invoice (AJAX)
     */
    public function publicPay(): void
    {
        $token = $_POST['token'] ?? '';

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'لینک نامعتبر است.']);
            exit;
        }

        $db = Database::getInstance();
        $invoice = $db->fetch(
            "SELECT * FROM hotel_invoices WHERE payment_token = :token",
            [':token' => $token]
        );

        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'فاکتور یافت نشد.']);
            exit;
        }

        if ($invoice->invoice_status === 'paid') {
            echo json_encode(['success' => false, 'message' => 'این فاکتور قبلاً پرداخت شده است.']);
            exit;
        }

        $payAmount = ($invoice->deposit_amount > 0) ? $invoice->deposit_amount : $invoice->final_amount;
        $amountRial = $payAmount * 10;

        $config = $GLOBALS['app_config'];
        $merchant = $config['zibal']['merchant'];
        $callbackUrl = $config['url'] . '/hotel-pay/result';

        $data = [
            'merchant' => $merchant,
            'amount' => $amountRial,
            'callbackUrl' => $callbackUrl,
            'description' => 'پرداخت فاکتور هتل #' . $invoice->id,
            'orderId' => 'INV-' . $invoice->id . '-' . time(),
        ];

        // Get mobile from deal contact
        $deal = $db->fetch("SELECT c.phone FROM deals d LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.id = :id", [':id' => $invoice->deal_id]);
        if ($deal && !empty($deal->phone)) {
            $data['mobile'] = $deal->phone;
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
        curl_close($ch);

        $result = json_decode($response);

        if ($result && isset($result->trackId) && $result->result == 100) {
            // Save payment record
            $db->insert('payments', [
                'deal_id' => $invoice->deal_id,
                'amount' => $payAmount,
                'currency' => 'IRR',
                'payment_type' => 'online',
                'status' => 'pending',
                'track_id' => $result->trackId,
                'description' => 'پرداخت فاکتور هتل #' . $invoice->id,
            ]);

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
     * Payment result callback for hotel invoices
     */
    public function paymentResult(): void
    {
        $trackId = $_GET['trackId'] ?? '';
        $success = false;
        $message = '';
        $refNumber = '';
        $amount = 0;

        if (!empty($trackId)) {
            $db = Database::getInstance();
            $payment = $db->fetch("SELECT * FROM payments WHERE track_id = :track_id", [':track_id' => $trackId]);

            if ($payment) {
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

                if ($result && $result->result == 100) {
                    $db->update('payments', [
                        'status' => 'success',
                        'ref_number' => $result->refNumber ?? '',
                        'card_number' => $result->cardNumber ?? '',
                        'paid_at' => date('Y-m-d H:i:s'),
                    ], 'id = :id', [':id' => $payment->id]);

                    // Update deal
                    if ($payment->deal_id) {
                        $db->update('deals', ['is_won' => 1, 'closed_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $payment->deal_id]);
                    }

                    // Update hotel invoice status to paid
                    $invoice = $db->fetch(
                        "SELECT * FROM hotel_invoices WHERE deal_id = :deal_id AND payment_token IS NOT NULL ORDER BY id DESC LIMIT 1",
                        [':deal_id' => $payment->deal_id]
                    );
                    if ($invoice) {
                        $db->update('hotel_invoices', ['invoice_status' => 'paid'], 'id = :id', [':id' => $invoice->id]);
                    }

                    $success = true;
                    $refNumber = $result->refNumber ?? '';
                    $amount = $payment->amount;
                    $message = 'پرداخت شما با موفقیت انجام شد.';
                } else {
                    $message = 'پرداخت ناموفق بود.';
                }
            } else {
                $message = 'اطلاعات پرداخت یافت نشد.';
            }
        } else {
            $message = 'اطلاعات پرداخت نامعتبر است.';
        }

        $config = $GLOBALS['app_config'];
        require __DIR__ . '/../views/hotel_invoice/payment_result.php';
        exit;
    }

    /**
     * Show error page for public invoice
     */
    private function showPublicError(string $message): void
    {
        $config = $GLOBALS['app_config'];
        $invoice = null;
        $invoiceSettings = $this->getSettings();
        $payAmount = 0;
        $errorMessage = $message;
        $viewPath = __DIR__ . '/../views/hotel_invoice/public.php';
        extract(['invoice' => $invoice, 'config' => $config, 'invoiceSettings' => $invoiceSettings, 'payAmount' => $payAmount, 'errorMessage' => $message]);
        require $viewPath;
        exit;
    }
}