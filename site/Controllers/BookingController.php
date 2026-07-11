<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Logger;
use Shared\Services\BookingService;
use Shared\Services\PricingService;
use Shared\Services\AvailabilityService;
use Shared\Services\CurrencyService;
use Shared\Repositories\RoomRepository;
use Shared\Repositories\HotelRepository;

class BookingController
{
    private Database $db;
    private Config $config;
    private BookingService $booking;
    private PricingService $pricing;
    private AvailabilityService $availability;
    private CurrencyService $currency;
    private RoomRepository $roomRepo;
    private HotelRepository $hotelRepo;

    public function __construct()
    {
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
        $this->booking = $c->make(BookingService::class);
        $this->pricing = $c->make(PricingService::class);
        $this->availability = $c->make(AvailabilityService::class);
        $this->currency = $c->make(CurrencyService::class);
        $this->roomRepo = new RoomRepository($this->db);
        $this->hotelRepo = new HotelRepository($this->db);
    }

    public function new(array $params = []): void
    {
        $roomId = (int)($_GET['room_id'] ?? 0);
        $checkin = $_GET['checkin'] ?? '';
        $checkout = $_GET['checkout'] ?? '';
        $guests = (int)($_GET['guests'] ?? 2);

        if (!$roomId || !$checkin || !$checkout) {
            header('Location: /hotels'); exit;
        }

        $room = $this->roomRepo->find($roomId);
        if (!$room) { header('Location: /hotels'); exit; }

        $hotel = $this->hotelRepo->findByCrmId($room->crm_hotel_id);
        $pricing = $this->pricing->calculate($roomId, $checkin, $checkout, $guests);
        $avail = $this->availability->check($roomId, $checkin, $checkout);

        $meta = ['title' => 'رزرو اتاق', 'description' => ''];
        $this->render('booking/new', [
            'room' => $room,
            'hotel' => $hotel,
            'pricing' => $pricing,
            'availability' => $avail,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
            'meta' => $meta,
        ]);
    }

    public function create(array $params = []): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? '/';
            header('Location: /login'); exit;
        }

        $roomId = (int)($_POST['room_id'] ?? 0);
        $checkin = $_POST['checkin'] ?? '';
        $checkout = $_POST['checkout'] ?? '';
        $guests = (int)($_POST['guests'] ?? 2);

        $guestData = [];
        if (!empty($_POST['guest_name'])) {
            $guestData[] = [
                'name' => trim($_POST['guest_name']),
                'phone' => trim($_POST['guest_phone'] ?? ''),
                'national_code' => trim($_POST['guest_national_code'] ?? ''),
                'email' => trim($_POST['guest_email'] ?? ''),
            ];
        }

        $result = $this->booking->create($roomId, $checkin, $checkout, $guests, $guestData, [
            'user_id' => $_SESSION['user_id'],
            'crm_hotel_id' => (int)($_POST['crm_hotel_id'] ?? 0),
            'rooms_count' => (int)($_POST['rooms_count'] ?? 1),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        if ($result->success) {
            header('Location: /booking/' . $result->reservation_token); exit;
        }

        $_SESSION['booking_error'] = $result->message ?? 'خطا در ثبت رزرو';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/hotels'); exit;
    }

    public function show(array $params = []): void
    {
        $token = $params['token'] ?? '';
        $reservation = $this->db->fetch(
            "SELECT r.*, b.* FROM site_reservations r JOIN site_bookings b ON r.booking_id = b.id WHERE r.reservation_token = :t",
            [':t' => $token]
        );

        if (!$reservation) {
            $meta = ['title' => 'رزرو یافت نشد'];
            $this->render('errors/404', ['meta' => $meta]); return;
        }

        $room = $this->roomRepo->find($reservation->room_id);
        $hotel = $this->hotelRepo->findByCrmId($reservation->crm_hotel_id);
        $pricing = $this->pricing->calculate($reservation->room_id, $reservation->checkin_date, $reservation->checkout_date, $reservation->guests_adults);

        $meta = ['title' => 'رزرو شما', 'description' => ''];
        $this->render('booking/show', [
            'reservation' => $reservation,
            'room' => $room,
            'hotel' => $hotel,
            'pricing' => $pricing,
            'meta' => $meta,
        ]);
    }

    public function refresh(array $params = []): void
    {
        header('Content-Type: application/json');
        $token = $params['token'] ?? '';
        if (empty($token)) { echo json_encode(['valid' => false, 'reason' => 'no_token']); return; }

        $result = $this->booking->refresh($token);
        echo json_encode($result);
    }

    public function heartbeat(array $params = []): void
    {
        header('Content-Type: application/json');
        $token = $params['token'] ?? '';
        if (empty($token)) { echo json_encode(['valid' => false]); return; }

        $reservation = $this->db->fetch(
            "SELECT expires_at FROM site_reservations WHERE reservation_token = :t AND status = 'active'",
            [':t' => $token]
        );

        if (!$reservation || strtotime($reservation->expires_at) < time()) {
            echo json_encode(['valid' => false, 'reason' => 'expired']); return;
        }

        echo json_encode([
            'valid' => true,
            'remaining' => max(0, (int)(strtotime($reservation->expires_at) - time())),
        ]);
    }

    public function pay(array $params = []): void
    {
        $token = $params['token'] ?? '';

        // Finalize and validate
        $reservation = $this->db->fetch(
            "SELECT * FROM site_reservations WHERE reservation_token = :t AND status = 'active'",
            [':t' => $token]
        );

        if (!$reservation) {
            $_SESSION['booking_error'] = 'رزرو منقضی شده است.';
            header('Location: /hotels'); exit;
        }

        $result = $this->booking->finalize($reservation->booking_id);
        if (!$result->success) {
            $_SESSION['booking_error'] = $result->message ?? 'خطا در نهایی‌سازی';
            header('Location: /booking/' . $token); exit;
        }

        // Redirect to payment gateway (placeholder)
        echo 'در حال انتقال به درگاه پرداخت...';
    }

    public function confirm(array $params = []): void
    {
        $code = $params['code'] ?? '';
        $booking = $this->booking->findByCode($code);

        $meta = ['title' => 'تأیید رزرو', 'description' => ''];
        $this->render('booking/confirm', ['booking' => $booking, 'meta' => $meta]);
    }

    public function track(array $params = []): void
    {
        $meta = ['title' => 'پیگیری رزرو', 'description' => ''];
        $booking = null;
        $error = null;

        if (!empty($_GET['code'])) {
            $booking = $this->booking->findByCode($_GET['code']);
            if (!$booking) $error = 'رزروی با این کد یافت نشد.';
        }

        $this->render('booking/track', ['booking' => $booking, 'error' => $error, 'meta' => $meta]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
    }
}