<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class HotelInvoiceController
{
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

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return "{$prefix}-{$date}-{$random}";
    }

    private function recalcNights(string $checkIn, string $checkOut): int
    {
        $d1 = new \DateTime($checkIn);
        $d2 = new \DateTime($checkOut);
        $nights = (int)$d2->diff($d1)->days;
        return max($nights, 0);
    }

    /**
     * Calculate line total based on category and nights
     */
    private function calcLineTotal(float $qty, float $unitPrice, string $category, int $nights): float
    {
        if ($category === 'hotel' && $nights > 0) {
            return $qty * $unitPrice * $nights;
        }
        return $qty * $unitPrice;
    }

    /**
     * Extract submitted items from POST with calculations
     */
    private function extractItems(array $post, int $nights): array
    {
        $descriptions = $post['item_description'] ?? [];
        $quantities   = $post['item_quantity'] ?? [];
        $defaultPrices = $post['item_default_price'] ?? [];
        $newPrices    = $post['item_new_price'] ?? [];
        $categories   = $post['item_category'] ?? [];

        $items = [];
        $subtotal = 0;
        $itemsDiscount = 0;

        if (!empty($descriptions) && is_array($descriptions)) {
            foreach ($descriptions as $i => $desc) {
                $desc = trim($desc);
                if (empty($desc)) continue;

                $qty     = (float)str_replace(',', '', $quantities[$i] ?? '1');
                $defPrice = (float)str_replace(',', '', $defaultPrices[$i] ?? '0');
                $newPrice = (float)str_replace(',', '', $newPrices[$i] ?? '0');
                $cat     = $categories[$i] ?? 'general';

                // Use new price if provided, otherwise use default price
                $actualPrice = ($newPrice > 0) ? $newPrice : $defPrice;

                $total   = $this->calcLineTotal($qty, $actualPrice, $cat, $nights);
                $subtotal += $total;

                // Calculate item-level discount (difference between default and new price)
                if ($newPrice > 0 && $newPrice < $defPrice) {
                    $diff = $defPrice - $newPrice;
                    $itemDiscount = ($cat === 'hotel' && $nights > 0) ? $diff * $qty * $nights : $diff * $qty;
                    $itemsDiscount += $itemDiscount;
                }

                $items[] = [
                    'description' => $desc,
                    'category'    => $cat,
                    'quantity'    => $qty,
                    'default_price' => $defPrice,
                    'unit_price'  => $actualPrice,
                    'total_price' => $total,
                    'sort_order'  => $i,
                ];
            }
        }

        return ['items' => $items, 'subtotal' => $subtotal, 'items_discount' => $itemsDiscount];
    }

    // ============================================================
    // INDEX
    // ============================================================
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
            "SELECT hi.*, d.title as deal_title, c.full_name as contact_name, u.full_name as creator_name
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN users u ON hi.created_by = u.id
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

    // ============================================================
    // CREATE (show form)
    // ============================================================
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

    // ============================================================
    // STORE (save new invoice)
    // ============================================================
    public function store(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();

        $dealId       = (int)($_POST['deal_id'] ?? 0);
        $hotelName    = trim($_POST['hotel_name'] ?? '');
        $guestName    = trim($_POST['guest_name'] ?? '');
        $guestPhone   = trim($_POST['guest_phone'] ?? '');
        $checkInDate  = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $extraServices = trim($_POST['extra_services'] ?? '');
        $notes        = trim($_POST['notes'] ?? '');
        $paymentTerms = trim($_POST['payment_terms'] ?? '');
        $footerText   = trim($_POST['footer_text'] ?? '');
        $validUntil   = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
        $invoiceType  = $_POST['invoice_type'] ?? 'proforma';
        $invoiceStatus= $_POST['invoice_status'] ?? 'pending';
        $taxPercent   = (float)($_POST['tax_percent'] ?? 0);
        $serviceFee   = (float)str_replace(',', '', $_POST['service_fee'] ?? '0');
        $discountAmount = (float)str_replace(',', '', $_POST['discount_amount'] ?? '0');
        $depositAmount = (float)str_replace(',', '', $_POST['deposit_amount'] ?? '0');
        $transferIncluded = isset($_POST['transfer_included']) ? 1 : 0;
        $visaIncluded     = isset($_POST['visa_included']) ? 1 : 0;
        $insuranceIncluded= isset($_POST['insurance_included']) ? 1 : 0;

        if (!$dealId || empty($hotelName) || empty($checkInDate) || empty($checkOutDate)) {
            $msg = 'لطفاً فیلدهای الزامی (نام هتل، تاریخ ورود، تاریخ خروج) را پر کنید.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/create/' . $dealId);
        }

        $nights = $this->recalcNights($checkInDate, $checkOutDate);
        if ($nights <= 0) {
            $msg = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/create/' . $dealId);
        }

        // Extract and calculate line items
        $extracted = $this->extractItems($_POST, $nights);
        $items = $extracted['items'];
        $subtotal = $extracted['subtotal'];

        // Final calculations
        $taxAmount   = $subtotal * ($taxPercent / 100);
        $finalAmount = $subtotal + $taxAmount + $serviceFee - $discountAmount;
        $finalAmount = max($finalAmount, 0);

        $invoiceNumber = $this->generateInvoiceNumber();
        $invoiceSettings = $this->getSettings();
        if (empty($footerText))    $footerText = $invoiceSettings['invoice_footer_text'] ?? '';
        if (empty($paymentTerms))  $paymentTerms = $invoiceSettings['invoice_terms'] ?? '';

        try {
            $invoiceId = $db->insert('hotel_invoices', [
                'deal_id'          => $dealId,
                'hotel_name'       => $hotelName,
                'guest_name'       => $guestName,
                'guest_phone'      => $guestPhone,
                'check_in_date'    => $checkInDate,
                'check_out_date'   => $checkOutDate,
                'nights'           => $nights,
                'persons_count'    => 0,
                'subtotal'         => $subtotal,
                'tax_percent'      => $taxPercent,
                'tax_amount'       => $taxAmount,
                'service_fee'      => $serviceFee,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $subtotal,
                'final_amount'     => $finalAmount,
                'deposit_amount'   => $depositAmount,
                'transfer_included' => $transferIncluded,
                'visa_included'    => $visaIncluded,
                'insurance_included' => $insuranceIncluded,
                'extra_services'   => $extraServices,
                'payment_terms'    => $paymentTerms,
                'valid_until'      => $validUntil,
                'notes'            => $notes,
                'footer_text'      => $footerText,
                'invoice_status'   => $invoiceStatus,
                'invoice_type'     => $invoiceType,
                'invoice_number'   => $invoiceNumber,
                'created_by'       => Auth::id(),
            ]);

            // Insert line items with category
            foreach ($items as $item) {
                $db->insert('hotel_invoice_items', array_merge($item, ['invoice_id' => $invoiceId]));
            }

            ActivityLog::log('create_hotel_invoice', 'hotel_invoice', $invoiceId,
                "فاکتور هتل {$hotelName} ({$invoiceNumber}) برای معامله {$dealId} ایجاد شد");

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

    // ============================================================
    // EDIT (show edit form)
    // ============================================================
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

    // ============================================================
    // UPDATE
    // ============================================================
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

        $hotelName    = trim($_POST['hotel_name'] ?? '');
        $guestName    = trim($_POST['guest_name'] ?? '');
        $guestPhone   = trim($_POST['guest_phone'] ?? '');
        $checkInDate  = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $extraServices = trim($_POST['extra_services'] ?? '');
        $notes        = trim($_POST['notes'] ?? '');
        $paymentTerms = trim($_POST['payment_terms'] ?? '');
        $footerText   = trim($_POST['footer_text'] ?? '');
        $validUntil   = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
        $invoiceType  = $_POST['invoice_type'] ?? 'proforma';
        $invoiceStatus= $_POST['invoice_status'] ?? 'pending';
        $taxPercent   = (float)($_POST['tax_percent'] ?? 0);
        $serviceFee   = (float)str_replace(',', '', $_POST['service_fee'] ?? '0');
        $discountAmount = (float)str_replace(',', '', $_POST['discount_amount'] ?? '0');
        $depositAmount = (float)str_replace(',', '', $_POST['deposit_amount'] ?? '0');
        $transferIncluded = isset($_POST['transfer_included']) ? 1 : 0;
        $visaIncluded     = isset($_POST['visa_included']) ? 1 : 0;
        $insuranceIncluded= isset($_POST['insurance_included']) ? 1 : 0;

        if (empty($hotelName) || empty($checkInDate) || empty($checkOutDate)) {
            $msg = 'لطفاً فیلدهای الزامی را پر کنید.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }

        $nights = $this->recalcNights($checkInDate, $checkOutDate);
        if ($nights <= 0) {
            $msg = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            Session::setFlash('danger', $msg);
            View::redirect('/hotel-invoice/edit/' . $invoiceId);
        }

        // Extract and calculate line items
        $extracted = $this->extractItems($_POST, $nights);
        $items = $extracted['items'];
        $subtotal = $extracted['subtotal'];

        $taxAmount   = $subtotal * ($taxPercent / 100);
        $finalAmount = $subtotal + $taxAmount + $serviceFee - $discountAmount;
        $finalAmount = max($finalAmount, 0);

        if (empty($footerText))   $footerText = $this->getSettings()['invoice_footer_text'] ?? '';
        if (empty($paymentTerms)) $paymentTerms = $this->getSettings()['invoice_terms'] ?? '';

        try {
            $db->update('hotel_invoices', [
                'hotel_name'       => $hotelName,
                'guest_name'       => $guestName,
                'guest_phone'      => $guestPhone,
                'check_in_date'    => $checkInDate,
                'check_out_date'   => $checkOutDate,
                'nights'           => $nights,
                'subtotal'         => $subtotal,
                'tax_percent'      => $taxPercent,
                'tax_amount'       => $taxAmount,
                'service_fee'      => $serviceFee,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $subtotal,
                'final_amount'     => $finalAmount,
                'deposit_amount'   => $depositAmount,
                'transfer_included' => $transferIncluded,
                'visa_included'    => $visaIncluded,
                'insurance_included' => $insuranceIncluded,
                'extra_services'   => $extraServices,
                'payment_terms'    => $paymentTerms,
                'valid_until'      => $validUntil,
                'notes'            => $notes,
                'footer_text'      => $footerText,
                'invoice_status'   => $invoiceStatus,
                'invoice_type'     => $invoiceType,
            ], 'id = :id', [':id' => $invoiceId]);

            // Delete old items, insert new
            $db->delete('hotel_invoice_items', 'invoice_id = :id', [':id' => $invoiceId]);
            foreach ($items as $item) {
                $db->insert('hotel_invoice_items', array_merge($item, ['invoice_id' => $invoiceId]));
            }

            ActivityLog::log('update_hotel_invoice', 'hotel_invoice', $invoiceId,
                "فاکتور هتل {$hotelName} بروزرسانی شد");

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

    // ============================================================
    // VIEW
    // ============================================================
    public function view(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title, d.amount as deal_amount,
                    c.full_name as contact_name, c.phone as contact_phone,
                    u.full_name as creator_name
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN users u ON hi.created_by = u.id
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

    // ============================================================
    // PRINT
    // ============================================================
    public function print(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $invoiceId = (int)$params['id'];

        $invoice = $db->fetch(
            "SELECT hi.*, d.title as deal_title, d.amount as deal_amount,
                    c.full_name as contact_name, c.phone as contact_phone,
                    u.full_name as creator_name
             FROM hotel_invoices hi
             JOIN deals d ON hi.deal_id = d.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN users u ON hi.created_by = u.id
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

    // ============================================================
    // DELETE
    // ============================================================
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
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]); exit; }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/hotel-invoice/create/' . $invoice->deal_id);
        }
    }

    // ============================================================
    // UPDATE STATUS
    // ============================================================
    public function updateStatus(array $params): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();
        // Auto-fix ENUM on every status-related operation
        try { $db->query("ALTER TABLE `hotel_invoices` MODIFY COLUMN `invoice_status` ENUM('draft','final','paid','cancelled','pending','settled','prepaid') DEFAULT 'pending'"); } catch (\Exception $e) {}
        $invoiceId = (int)$params['id'];
        $status = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'settled', 'prepaid', 'paid'];
        if (!in_array($status, $validStatuses)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'وضعیت نامعتبر.']); exit; }
            Session::setFlash('danger', 'وضعیت نامعتبر.');
            View::redirect('/deals');
        }

        try {
            $db->update('hotel_invoices', ['invoice_status' => $status], 'id = :id', [':id' => $invoiceId]);
            if ($isAjax) { echo json_encode(['success' => true, 'message' => 'وضعیت فاکتور بروزرسانی شد.']); exit; }
            Session::setFlash('success', 'وضعیت فاکتور بروزرسانی شد.');
            View::redirect('/hotel-invoice/view/' . $invoiceId);
        } catch (\Exception $e) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]); exit; }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/hotel-invoice/view/' . $invoiceId);
        }
    }

    // ============================================================
    // CALCULATE (AJAX endpoint)
    // ============================================================
    public function calculate(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // ============================================================
    // ITEMS CATALOG
    // ============================================================
    public function getItemsCatalog(): void
    {
        header('Content-Type: application/json');
        Auth::requireAuth();
        $db = Database::getInstance();
        try {
            $items = $db->fetchAll("SELECT * FROM invoice_items_catalog WHERE is_active=1 ORDER BY category, name");
            echo json_encode(['success' => true, 'items' => $items]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function itemsCatalog(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $items = $db->fetchAll("SELECT * FROM invoice_items_catalog ORDER BY category, name");
        View::render('hotel_invoice/items_catalog', [
            'title' => 'مدیریت آیتم‌های فاکتور',
            'items' => $items,
        ]);
    }

    public function storeItem(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $defaultPrice = (float)str_replace(',', '', $_POST['default_price'] ?? '0');
        $category = trim($_POST['category'] ?? 'general');

        if (empty($name)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'نام آیتم الزامی است.']); exit; }
            Session::setFlash('danger', 'نام آیتم الزامی است.');
            View::redirect('/hotel-invoice/items-catalog');
        }

        try {
            $db->insert('invoice_items_catalog', [
                'name' => $name,
                'description' => $description,
                'default_price' => $defaultPrice,
                'category' => $category,
            ]);
            if ($isAjax) { echo json_encode(['success' => true, 'message' => 'آیتم اضافه شد.']); exit; }
            Session::setFlash('success', 'آیتم اضافه شد.');
            View::redirect('/hotel-invoice/items-catalog');
        } catch (\Exception $e) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); exit; }
            Session::setFlash('danger', $e->getMessage());
            View::redirect('/hotel-invoice/items-catalog');
        }
    }

    public function deleteItem(array $params): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        Auth::requireAuth();
        $db = Database::getInstance();
        $id = (int)$params['id'];
        try {
            $db->delete('invoice_items_catalog', 'id = :id', [':id' => $id]);
            if ($isAjax) { echo json_encode(['success' => true, 'message' => 'آیتم حذف شد.']); exit; }
            Session::setFlash('success', 'آیتم حذف شد.');
            View::redirect('/hotel-invoice/items-catalog');
        } catch (\Exception $e) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); exit; }
            Session::setFlash('danger', $e->getMessage());
            View::redirect('/hotel-invoice/items-catalog');
        }
    }

    // ============================================================
    // PAYMENT LINK
    // ============================================================
    private function createPaymentLink(int $invoiceId, int $dealId, float $finalAmount, float $depositAmount): void
    {
        try {
            $db = Database::getInstance();

            $payAmount = ($depositAmount > 0) ? $depositAmount : $finalAmount;

            // Always generate a short_code so the public link works
            $publicToken = bin2hex(random_bytes(16));
            $shortCode = strtolower(substr(md5($publicToken), 0, 6));
            $existing = $db->fetch("SELECT id FROM hotel_invoices WHERE short_code = :sc", [':sc' => $shortCode]);
            if ($existing) {
                $shortCode = strtolower(substr(md5($publicToken . time()), 0, 6));
            }

            $db->update('hotel_invoices', [
                'payment_token' => $publicToken,
                'short_code' => $shortCode,
            ], 'id = :id', [':id' => $invoiceId]);

            // Try to create Zibal payment link (non-critical)
            $config = $GLOBALS['app_config'];
            $merchant = $config['zibal']['merchant'];
            $callbackUrl = $config['zibal']['callback_url'];
            $amountRial = $payAmount * 10;

            if (!empty($merchant) && $amountRial >= 1000) {
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
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);

                if ($result && isset($result['result']) && $result['result'] === 100) {
                    $trackId = $result['trackId'];
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
                }
            }
        } catch (\Exception $e) {
            error_log('Failed to create payment link for invoice ' . $invoiceId . ': ' . $e->getMessage());
        }
    }

    // ============================================================
    // PUBLIC VIEW (no auth)
    // ============================================================
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
        $code  = $_POST['code'] ?? '';
        if (empty($token) && empty($code)) { echo json_encode(['success' => false, 'message' => 'لینک نامعتبر است.']); exit; }

        $db = Database::getInstance();
        $invoice = null;
        if (!empty($token)) {
            $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE payment_token = :token", [':token' => $token]);
        }
        if (!$invoice && !empty($code)) {
            $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE short_code = :code", [':code' => $code]);
        }

        if (!$invoice) { echo json_encode(['success' => false, 'message' => 'فاکتور یافت نشد.']); exit; }
        if ($invoice->invoice_status !== 'prepaid') {
            $statusMsg = 'این فاکتور قابل پرداخت نیست.';
            if ($invoice->invoice_status === 'paid' || $invoice->invoice_status === 'settled') {
                $statusMsg = 'این فاکتور قبلاً پرداخت شده است.';
            } elseif ($invoice->invoice_status === 'pending') {
                $statusMsg = 'این فاکتور مانده دارد و در انتظار تسویه نهایی است.';
            }
            echo json_encode(['success' => false, 'message' => $statusMsg]);
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

    /**
     * Ensure invoice_status ENUM accepts new values (pending, settled, prepaid)
     * Runs once per request and caches the result
     */
    private function ensureStatusEnum(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        try {
            $db = Database::getInstance();
            $col = $db->fetch("SHOW COLUMNS FROM hotel_invoices WHERE Field = 'invoice_status'");
            if ($col && strpos($col->Type ?? '', 'pending') === false) {
                $db->query("ALTER TABLE `hotel_invoices` MODIFY COLUMN `invoice_status` ENUM('draft','final','paid','cancelled','pending','settled','prepaid') DEFAULT 'pending'");
            }
        } catch (\Exception $e) {
            error_log('ensureStatusEnum failed: ' . $e->getMessage());
        }
    }

    /**
     * Update invoice status with fallback if ENUM doesn't accept the value
     */
    private function updateInvoiceStatus(int $invoiceId, string $status): bool
    {
        $this->ensureStatusEnum();
        $db = Database::getInstance();
        
        // Try the desired status first
        try {
            $db->query("UPDATE hotel_invoices SET invoice_status = :status WHERE id = :id", [':status' => $status, ':id' => $invoiceId]);
            // Verify it was actually saved
            $row = $db->fetch("SELECT invoice_status FROM hotel_invoices WHERE id = :id", [':id' => $invoiceId]);
            if ($row && $row->invoice_status === $status) return true;
        } catch (\Exception $e) {
            error_log("Status '$status' rejected for invoice $invoiceId: " . $e->getMessage());
        }
        
        // Fallback: use 'paid' which exists in the old ENUM
        if ($status !== 'paid') {
            try {
                $db->query("UPDATE hotel_invoices SET invoice_status = 'paid' WHERE id = :id", [':id' => $invoiceId]);
                error_log("Invoice $invoiceId: fell back to 'paid' instead of '$status'");
                return true;
            } catch (\Exception $e) {
                error_log("Fallback 'paid' also failed for invoice $invoiceId: " . $e->getMessage());
            }
        }
        return false;
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

                    // Find the invoice from description or orderId
                    $invoiceId = 0;
                    if (preg_match('/فاکتور هتل #(\d+)/', $payment->description ?? '', $m)) {
                        $invoiceId = (int)$m[1];
                    }
                    // Fallback: try orderId format INV-{id}-{timestamp}
                    if (!$invoiceId && preg_match('/INV-(\d+)-/', $payment->order_id ?? $payment->description ?? '', $m)) {
                        $invoiceId = (int)$m[1];
                    }

                    if ($invoiceId > 0) {
                        $invoice = $db->fetch("SELECT * FROM hotel_invoices WHERE id = :id", [':id' => $invoiceId]);
                        if ($invoice) {
                            // If invoice has deposit, check if paid amount matches deposit or full
                            if ($invoice->deposit_amount > 0 && $payment->amount >= $invoice->deposit_amount && $payment->amount < $invoice->final_amount) {
                                // Deposit paid → set to 'pending' (مانده دارد)
                                $newStatus = 'pending';
                                // Reduce final amount by deposit amount
                                $newFinalAmount = max($invoice->final_amount - $invoice->deposit_amount, 0);
                                $db->update('hotel_invoices', [
                                    'final_amount' => $newFinalAmount,
                                    'deposit_amount' => 0,
                                ], 'id = :id', [':id' => $invoiceId]);
                            } else {
                                // Full amount paid or no deposit
                                $newStatus = 'paid';
                            }

                            $this->updateInvoiceStatus($invoiceId, $newStatus);

                            // Update deal as won only for full payment
                            if ($newStatus === 'paid' && $payment->deal_id) {
                                try {
                                    $db->update('deals', ['is_won' => 1, 'closed_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $payment->deal_id]);
                                } catch (\Exception $e) {
                                    error_log('Deal update failed: ' . $e->getMessage());
                                }
                            }
                        }
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