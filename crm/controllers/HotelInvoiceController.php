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

        // Check permission
        if (!Auth::ownsDeal($dealId)) {
            Session::setFlash('danger', 'شما فقط برای معاملات خودتان می‌توانید فاکتور صادر کنید.');
            View::redirect('/deals');
        }

        // Get existing invoices for this deal
        $invoices = $db->fetchAll(
            "SELECT * FROM hotel_invoices WHERE deal_id = :deal_id ORDER BY created_at DESC",
            [':deal_id' => $dealId]
        );

        View::render('hotel_invoice/create', [
            'title' => 'فاکتور هتل',
            'deal' => $deal,
            'invoices' => $invoices,
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
        $personsCount = (int)($_POST['persons_count'] ?? 1);
        $pricePerPersonNight = (float)str_replace(',', '', $_POST['price_per_person_night'] ?? '0');
        $newPricePerPersonNightRaw = $_POST['new_price_per_person_night'] ?? '';
        $discountPercent = (float)($_POST['discount_percent'] ?? '20');
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

        $personNightCount = $personsCount * $nights;
        $totalAmount = $personNightCount * $pricePerPersonNight;

        // Handle new price and discount
        $newPricePerPersonNight = null;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        if (!empty($newPricePerPersonNightRaw)) {
            $newPricePerPersonNight = (float)str_replace(',', '', $newPricePerPersonNightRaw);
            $totalAmount = $personNightCount * $newPricePerPersonNight;
            $discountAmount = $totalAmount * ($discountPercent / 100);
            $finalAmount = $totalAmount - $discountAmount;
        } else {
            $discountAmount = $totalAmount * ($discountPercent / 100);
            $finalAmount = $totalAmount - $discountAmount;
        }

        try {
            $invoiceId = $db->insert('hotel_invoices', [
                'deal_id' => $dealId,
                'hotel_name' => $hotelName,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'persons_count' => $personsCount,
                'person_night_count' => $personNightCount,
                'price_per_person_night' => $pricePerPersonNight,
                'total_amount' => $totalAmount,
                'new_price_per_person_night' => $newPricePerPersonNight,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'notes' => $notes,
                'invoice_status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            ActivityLog::log('create_hotel_invoice', 'hotel_invoice', $invoiceId, "فاکتور هتل {$hotelName} برای معامله {$dealId} ایجاد شد");

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

        View::render('hotel_invoice/view', [
            'title' => 'فاکتور هتل: ' . $invoice->hotel_name,
            'invoice' => $invoice,
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

        // Render print view without layout (standalone page)
        $config = $GLOBALS['app_config'];
        $viewPath = __DIR__ . '/../views/hotel_invoice/print.php';
        extract(['title' => 'چاپ فاکتور هتل', 'invoice' => $invoice, 'config' => $config]);
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

        $validStatuses = ['draft', 'final', 'cancelled'];
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

        $personsCount = (int)($_POST['persons_count'] ?? 0);
        $checkInDate = $_POST['check_in_date'] ?? '';
        $checkOutDate = $_POST['check_out_date'] ?? '';
        $pricePerPersonNight = (float)str_replace(',', '', $_POST['price_per_person_night'] ?? '0');
        $newPricePerPersonNightRaw = $_POST['new_price_per_person_night'] ?? '';
        $discountPercent = (float)($_POST['discount_percent'] ?? '20');

        $nights = 0;
        $personNightCount = 0;
        $totalAmount = 0;
        $newPricePerPersonNight = null;
        $discountAmount = 0;
        $finalAmount = 0;

        if (!empty($checkInDate) && !empty($checkOutDate)) {
            $checkIn = new \DateTime($checkInDate);
            $checkOut = new \DateTime($checkOutDate);
            $nights = $checkOut->diff($checkIn)->days;
            $personNightCount = $personsCount * $nights;
            $totalAmount = $personNightCount * $pricePerPersonNight;

            if (!empty($newPricePerPersonNightRaw)) {
                $newPricePerPersonNight = (float)str_replace(',', '', $newPricePerPersonNightRaw);
                $totalAmount = $personNightCount * $newPricePerPersonNight;
            }

            $discountAmount = $totalAmount * ($discountPercent / 100);
            $finalAmount = $totalAmount - $discountAmount;
        }

        echo json_encode([
            'success' => true,
            'nights' => $nights,
            'person_night_count' => $personNightCount,
            'total_amount' => $totalAmount,
            'new_price_per_person_night' => $newPricePerPersonNight,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ]);
        exit;
    }
}