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
                'invoice_title' => 'فاکتور رزرو هتل',
                'invoice_subtitle' => 'آژانس مسافرتی',
                'invoice_company_name' => 'علاءالدین سفیر اسمان',
                'invoice_logo_url' => '',
                'invoice_primary_color' => '#0d6efd',
                'invoice_secondary_color' => '#6c757d',
                'invoice_success_color' => '#198754',
                'invoice_footer_text' => 'این فاکتور به صورت الکترونیکی صادر شده است.',
                'invoice_terms' => 'شرایط پرداخت: پرداخت نقدی یا انتقال بانکی.',
            ];
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return "{$prefix}-{$date}-{$random}";
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
            $where .= " AND (hi.hotel_name LIKE :search OR hi.invoice_number LIKE :search OR hi.guest_name LIKE :search OR d.title LIKE :search OR c.full_name LIKE :search)";
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
        $guestName = trim($_POST['guest_name'] ?? '');
        $guestPhone = trim($_POST['guest_phone'] ?? '');
        $roomType = trim($_POST['room_type'] ?? '');
        $roomNumber = trim($_POST['room_number'] ?? '');
        $mealPlan = trim($_POST['meal_plan'] ?? '');
        $checkInDate = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $transferIncluded = isset($_POST['transfer_included']) ? 1 : 0;
        $visaIncluded = isset($_POST['visa_included']) ? 1 : 0;
        $insuranceIncluded = isset($_POST['insurance_included']) ? 1 : 0;
        $extraServices = trim($_POST['extra_services'] ?? '');
        $paymentTerms = trim($_POST['payment_terms'] ?? '');
        $validUntil = $_POST['valid_until'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        $footerText = trim($_POST['footer_text'] ?? '');
        $invoiceType = $_POST['invoice_type'] ?? 'proforma';
        $invoiceStatus = $_POST['invoice_status'] ?? 'draft';

        // Line items
        $itemDescriptions = $_POST['item_description'] ?? [];
        $itemQuantities = $_POST['item_quantity'] ?? [];
        $itemUnitPrices = $_POST['item_unit_price'] ?? [];

        if (!$dealId || empty($hotelName) || empty($checkInDate) || empty($checkOutDate)) {
            $msg = 'لطفاً فیلدهای الزامی (نام هتل، تاریخ ورود، تاریخ خروج) را پر کنید.';
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

        // Calculate subtotal from line items
        $subtotal = 0;
        $items = [];
        if (!empty($itemDescriptions) && is_array($itemDescriptions)) {
            foreach ($itemDescriptions as $i => $desc) {
                if (empty($desc)) continue;
                $qty = (float)($itemQuantities[$i] ?? 1);
                $price = (float)str_replace(',', '', $itemUnitPrices[$i] ?? '0');
                $total = $qty * $price;
                $subtotal += $total;
                $items[] = [
                    'description' => $desc,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $total,
                    'sort_order' => $i,
                ];
            }
        }

        // Calculate tax and final amount
        $taxPercent = (float)($_POST['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $serviceFee = (float)str_replace(',', '', $_POST['service_fee'] ?? '0');
        $discountAmount = (float)str_replace(',', '', $_POST['discount_amount'] ?? '0');
        $finalAmount = $subtotal + $taxAmount + $serviceFee - $discountAmount;
        $depositAmount = (float)str_replace(',', '', $_POST['deposit_amount'] ?? '0');

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Get default footer from settings if not provided
        if (empty($footerText)) {
            $footerText = $invoiceSettings['invoice_footer_text'] ?? '';
        }
        if (empty($paymentTerms)) {
            $paymentTerms = $invoiceSettings['invoice_terms'] ?? '';
        }

        try {
            $invoiceId = $db->insert('hotel_invoices', [
                'deal_id' => $dealId,
                'hotel_name' => $hotelName,
                'guest_name' => $guestName,
                'guest_phone' => $guestPhone,
                'room_type' => $roomType,
                'room_number' => $roomNumber,
                'meal_plan' => $mealPlan,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'persons_count' => 0,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'service_fee' => $serviceFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $subtotal,
                'final_amount' => $finalAmount,
                'deposit_amount' => $depositAmount,
                'transfer_included' => $transferIncluded,
                'visa_included' => $visaIncluded,
                'insurance_included' => $insuranceIncluded,
                'extra_services' => $extraServices,
                'payment_terms' => $paymentTerms,
                'valid_until' => $validUntil,
                'notes' => $notes,
                'footer_text' => $footerText,
                'invoice_status' => $invoiceStatus,
                'invoice_type' => $invoiceType,
                'invoice_number' => $invoiceNumber,
                'created_by' => Auth::id(),
            ]);

            // Insert line items
            foreach ($items as $item) {
                $db->insert('hotel_invoice_items', array_merge($item, ['invoice_id' => $invoiceId]));
            }

            ActivityLog::log('create_hotel_invoice', 'hotel_invoice', $invoiceId, "فاکتور هتل {$hotelName} ({$invoiceNumber}) برای معامله {$dealId} ایجاد شد");

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
            View::redirect('/hotel-invoice/view/' . $invoiceId);
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

        $items = $db->fetchAll(
            "SELECT * FROM hotel_invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order ASC",
            [':invoice_id' => $invoiceId]
        );

        $invoiceSettings = $this->getSettings();

        View::render('hotel_invoice/edit', [
            'title' => 'ویرایش فاکتور هتل',
            'invoice' => $invoice,
            'deal' => $deal,
            'items' => $items,
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
        $guestName = trim($_POST['guest_name'] ?? '');
        $guestPhone = trim($_POST['guest_phone'] ?? '');
        $roomType = trim($_POST['room_type'] ?? '');
        $roomNumber = trim($_POST['room_number'] ?? '');
        $mealPlan = trim($_POST['meal_plan'] ?? '');
        $checkInDate = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $transferIncluded = isset($_POST['transfer_included']) ? 1 : 0;
        $visaIncluded = isset($_POST['visa_included']) ? 1 : 0;
        $insuranceIncluded = isset($_POST['insurance_included']) ? 1 : 0;
        $extraServices = trim($_POST['extra_services'] ?? '');
        $paymentTerms = trim($_POST['payment_terms'] ?? '');
        $validUntil = $_POST['valid_until'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        $footerText = trim($_POST['footer_text'] ?? '');
        $invoiceType = $_POST['invoice_type'] ?? 'proforma';
        $invoiceStatus = $_POST['invoice_status'] ?? 'draft';

        $itemDescriptions = $_POST['item_description'] ?? [];
        $itemQuantities = $_POST['item_quantity'] ?? [];
        $itemUnitPrices = $_POST['item_unit_price'] ?? [];

        if (empty($hotelName) || empty($checkInDate) || empty($checkOutDate)) {
            $msg = 'لطفاً فیلدهای الزامی را پر کنید.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }

        $checkIn = new \DateTime($checkInDate);
        $checkOut = new \DateTime($checkOutDate);
        $nights = $checkOut->diff($checkIn)->days;

        if ($nights <= 0) {
            $msg = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }

        $subtotal = 0;
        $items = [];
        if (!empty($itemDescriptions) && is_array($itemDescriptions)) {
            foreach ($itemDescriptions as $i => $desc) {
                if (empty($desc)) continue;
                $qty = (float)($itemQuantities[$i] ?? 1);
                $price = (float)str_replace(',', '', $itemUnitPrices[$i] ?? '0');
                $total = $qty * $price;
                $subtotal += $total;
                $items[] = [
                    'description' => $desc,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $total,
                    'sort_order' => $i,
                ];
            }
        }

        $taxPercent = (float)($_POST['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $serviceFee = (float)str_replace(',', '', $_POST['service_fee'] ?? '0');
        $discountAmount = (float)str_replace(',', '', $_POST['discount_amount'] ?? '0');
        $finalAmount = $subtotal + $taxAmount + $serviceFee - $discountAmount;
        $depositAmount = (float)str_replace(',', '', $_POST['deposit_amount'] ?? '0');

        if (empty($footerText)) {
            $footerText = $this->getSettings()['invoice_footer_text'] ?? '';
        }
        if (empty($paymentTerms)) {
            $paymentTerms = $this->getSettings()['invoice_terms'] ?? '';
        }

        try {
            $db->update('hotel_invoices', [
                'hotel_name' => $hotelName,
                'guest_name' => $guestName,
                'guest_phone' => $guestPhone,
                'room_type' => $roomType,
                'room_number' => $roomNumber,
                'meal_plan' => $mealPlan,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'service_fee' => $serviceFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $subtotal,
                'final_amount' => $finalAmount,
                'deposit_amount' => $depositAmount,
                'transfer_included' => $transferIncluded,
                'visa_included' => $visaIncluded,
                'insurance_included' => $insuranceIncluded,
                'extra_services' => $extraServices,
                'payment_terms' => $paymentTerms,
                'valid_until' => $validUntil,
                'notes' => $notes,
                'footer_text' => $footerText,
                'invoice_status' => $invoiceStatus,
                'invoice_type' => $invoiceType,
            ], 'id = :id', [':id' => $invoiceId]);

            // Delete old items and insert new ones
            $db->delete('hotel_invoice_items', 'invoice_id = :id', [':id' => $invoiceId]);
            foreach ($items as $item) {
                $db->insert('hotel_invoice_items', array_merge($item, ['invoice_id' => $invoiceId]));
            }

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

                $shortCode = strtolower(substr(md5($publicToken), 0, 6));
                $existing = $db->fetch("SELECT id FROM hotel_invoices WHERE short_code = :sc", [':sc' => $shortCode]);
                if ($existing) {
                    $shortCode = strtolower(substr(md5($publicToken . time()), 0, 6));
                }

                $db->update('hotel_invoices', [
                    'payment_token' => $publicToken,
                    'short_code' => $shortCode,
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

        $items = $db->fetchAll(
            "SELECT * FROM hotel_invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order ASC",
            [':invoice_id' => $invoiceId]
        );

        $invoiceSettings = $this->getSettings();

        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE deal_id = :deal_id ORDER BY created_at DESC",
            [':deal_id' => $invoice->deal_id]
        );

        View::render('hotel_invoice/view', [
            'title' => 'فاکتور هتل: ' . $invoice->hotel_name,
            'invoice' => $invoice,
            'items' => $items,
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

        $items = $db->fetchAll(
            "SELECT * FROM hotel_invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order ASC",
            [':invoice_id' => $invoiceId]
        );

        $invoiceSettings = $this->getSettings();

        $config = $GLOBALS['app_config'];
        $viewPath = __DIR__ . '/../views/hotel_invoice/print.php';
        extract(['title' => 'چاپ فاکتور هتل', 'invoice' => $invoice, 'items' => $items, 'config' => $config, 'invoiceSettings' => $invoiceSettings]);
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
            // Delete line items first
            $db->delete('hotel_invoice_items', 'invoice_id = :id', [':id' => $invoiceId]);
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
        // This is now handled client-side with line items
        echo json_encode(['success' => true]);
        exit;
    }

    // ============================================
    // Public Hotel Invoice (no authentication)
    // ============================================

    public function publicView(array $params): void
    {
        $token = $params['token'] ?? '';
        if (empty($token)) { $this->showPublicError('لینک نامعتبر است.'); return; }

        $db = Database::getInstance();
        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title, c.full_name as contact_name, c.phone as contact_phone
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE hi.payment_token = :token",
            [':token' => $token]
        );

        if (!$invoice) { $this->showPublicError('فاکتور یافت نشد.'); return; }

        $items = $db->fetchAll(
            "SELECT * FROM hotel_invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order ASC",
            [':invoice_id' => $invoice->id]
        );

        $invoiceSettings = $this->getSettings();
        $payAmount = ($invoice->deposit_amount > 0) ? $invoice->deposit_amount : $invoice->final_amount;

        $config = $GLOBALS['app_config'];
        $viewPath = __DIR__ . '/../views/hotel_invoice/public.php';
        extract(['invoice' => $invoice, 'items' => $items, 'config' => $config, 'invoiceSettings' => $invoiceSettings, 'payAmount' => $payAmount]);
        require $viewPath;
        exit;
    }

    public function publicViewByShortCode(array $params): void
    {
        $shortCode = $params['code'] ?? '';
        if (empty($shortCode)) { $this->showPublicError('لینک نامعتبر است.'); return; }

        $db = Database::getInstance();
        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title, c.full_name as contact_name, c.phone as contact_phone
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE hi.short_code = :code",
            [':code' => $shortCode]
        );

        if (!$invoice) { $this->showPublicError('فاکتور یافت نشد.'); return; }

        $items = $db->fetchAll(
            "SELECT * FROM hotel_invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order ASC",
            [':invoice_id' => $invoice->id]
        );

        $invoiceSettings = $this->getSettings();
        $payAmount = ($invoice->deposit_amount > 0) ? $invoice->deposit_amount : $invoice->final_amount;

        $config = $GLOBALS['app_config'];
        $viewPath = __DIR__ . '/../views/hotel_invoice/public.php';
        extract(['invoice' => $invoice, 'items' => $items, 'config' => $config, 'invoiceSettings' => $invoiceSettings, 'payAmount' => $payAmount]);
        require $viewPath;
        exit;
    }

    public function publicPay(): void
    {
        $token = $_POST['token'] ?? '';
        if (empty($token)) { echo json_encode(['success' => false, 'message' => 'لینک نامعتبر است.']); exit; }

        $db = Database::getInstance();
        $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE payment_token = :token", [':token' => $token]);

        if (!$invoice) { echo json_encode(['success' => false, 'message' => 'فاکتور یافت نشد.']); exit; }
        if ($invoice->invoice_status === 'paid') { echo json_encode(['success' => false, 'message' => 'این فاکتور قبلاً پرداخت شده است.']); exit; }

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

        $deal = $db->fetch("SELECT c.phone FROM deals d LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.id = :id", [':id' => $invoice->deal_id]);
        if ($deal && !empty($deal->phone)) { $data['mobile'] = $deal->phone; }

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
            $db->insert('payments', [
                'deal_id' => $invoice->deal_id,
                'amount' => $payAmount,
                'currency' => 'IRR',
                'payment_type' => 'online',
                'status' => 'pending',
                'track_id' => $result->trackId,
                'description' => 'پرداخت فاکتور هتل #' . $invoice->id,
            ]);

            echo json_encode(['success' => true, 'message' => 'در حال اتصال به درگاه پرداخت...', 'redirect' => 'https://gateway.zibal.ir/start/' . $result->trackId]);
        } else {
            $errorMsg = $result->message ?? 'خطا در اتصال به درگاه پرداخت';
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }

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
                $config = $GLOBALS['app_config'];
                $merchant = $config['zibal']['merchant'];

                $data = ['merchant' => $merchant, 'trackId' => $trackId];

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

                    $invoiceId = 0;
                    if (preg_match('/فاکتور هتل #(\d+)/', $payment->description ?? '', $m)) {
                        $invoiceId = (int)$m[1];
                    }
                    if ($invoiceId > 0) {
                        $db->update('hotel_invoices', ['invoice_status' => 'paid'], 'id = :id', [':id' => $invoiceId]);
                    } else {
                        $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE deal_id = :deal_id AND payment_token IS NOT NULL ORDER BY id DESC LIMIT 1", [':deal_id' => $payment->deal_id]);
                        if ($invoice) { $db->update('hotel_invoices', ['invoice_status' => 'paid'], 'id = :id', [':id' => $invoice->id]); }
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

    private function showPublicError(string $message): void
    {
        $config = $GLOBALS['app_config'];
        $invoice = null;
        $items = [];
        $invoiceSettings = $this->getSettings();
        $payAmount = 0;
        $errorMessage = $message;
        $viewPath = __DIR__ . '/../views/hotel_invoice/public.php';
        extract(['invoice' => $invoice, 'items' => $items, 'config' => $config, 'invoiceSettings' => $invoiceSettings, 'payAmount' => $payAmount, 'errorMessage' => $message]);
        require $viewPath;
        exit;
    }
}