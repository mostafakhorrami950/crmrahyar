<?php
namespace Shared\Services;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Logger;
use Shared\Core\EventDispatcher;
use Shared\Interfaces\BookingServiceInterface;
use Shared\Repositories\BookingRepository;
use Shared\Repositories\RoomRepository;
use Shared\Repositories\SettingsRepository;

class BookingService implements BookingServiceInterface
{
    private Database $db;
    private Config $config;
    private PricingService $pricing;
    private AvailabilityService $availability;
    private CurrencyService $currency;
    private BookingRepository $bookingRepo;
    private RoomRepository $roomRepo;
    private SettingsRepository $settings;
    private EventDispatcher $events;

    public function __construct(
        Database $db, Config $config, PricingService $pricing,
        AvailabilityService $availability, CurrencyService $currency,
        BookingRepository $bookingRepo, RoomRepository $roomRepo,
        SettingsRepository $settings, EventDispatcher $events
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->pricing = $pricing;
        $this->availability = $availability;
        $this->currency = $currency;
        $this->bookingRepo = $bookingRepo;
        $this->roomRepo = $roomRepo;
        $this->settings = $settings;
        $this->events = $events;
    }

    /**
     * Create a new booking with reservation hold
     */
    public function create(int $roomId, string $dateFrom, string $dateTo, int $guests, array $guestData, array $options = []): object
    {
        // Check availability
        $avail = $this->availability->check($roomId, $dateFrom, $dateTo, $options['rooms_count'] ?? 1);
        if (!$avail->available) {
            return (object)['success' => false, 'error' => 'availability', 'message' => $avail->reason];
        }

        // Calculate pricing
        $pricing = $this->pricing->calculate($roomId, $dateFrom, $dateTo, $guests, $options);
        if ($pricing->total_price <= 0) {
            return (object)['success' => false, 'error' => 'pricing', 'message' => 'قیمت محاسبه نشد'];
        }

        // Generate reservation token
        $reservationToken = bin2hex(random_bytes(32));
        $holdMinutes = $this->settings->get('reservation_hold_minutes', 10);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$holdMinutes} minutes"));

        $this->db->beginTransaction();
        try {
            // Reserve inventory
            $reserved = $this->availability->reserve($roomId, $dateFrom, $dateTo, $options['rooms_count'] ?? 1, $reservationToken);
            if (!$reserved) {
                $this->db->rollback();
                return (object)['success' => false, 'error' => 'availability', 'message' => 'موجودی تغییر کرده. لطفاً دوباره تلاش کنید.'];
            }

            // Create booking (draft status)
            $bookingCode = $this->bookingRepo->generateBookingCode();
            $bookingId = $this->bookingRepo->create([
                'booking_code' => $bookingCode,
                'user_id' => $options['user_id'] ?? null,
                'room_id' => $roomId,
                'crm_hotel_id' => $options['crm_hotel_id'] ?? 0,
                'checkin_date' => $dateFrom,
                'checkout_date' => $dateTo,
                'nights' => $pricing->nights,
                'guests_adults' => $guests,
                'guests_children' => $options['children'] ?? 0,
                'rooms_count' => $options['rooms_count'] ?? 1,
                'base_price' => $this->currency->roundStorage($pricing->base_total),
                'markup_amount' => $this->currency->roundStorage($pricing->markup_amount),
                'total_price' => $this->currency->roundStorage($pricing->consumer_total),
                'final_price' => $this->currency->roundStorage($pricing->total_price),
                'currency' => 'IRR',
                'booking_status' => 'draft',
                'payment_status' => 'unpaid',
                'agency_id' => $options['agency_id'] ?? null,
                'idempotency_key' => $options['idempotency_key'] ?? null,
                'notes' => $options['notes'] ?? null,
            ]);

            // Create reservation
            $this->db->insert('site_reservations', [
                'booking_id' => $bookingId,
                'reservation_token' => $reservationToken,
                'room_id' => $roomId,
                'crm_hotel_id' => $options['crm_hotel_id'] ?? 0,
                'checkin_date' => $dateFrom,
                'checkout_date' => $dateTo,
                'quantity' => $options['rooms_count'] ?? 1,
                'status' => 'active',
                'expires_at' => $expiresAt,
                'pricing_snapshot_json' => json_encode($pricing->pricing_breakdown),
            ]);

            // Insert guests
            if (!empty($guestData)) {
                foreach ($guestData as $i => $g) {
                    $this->db->insert('site_booking_guests', [
                        'booking_id' => $bookingId,
                        'full_name' => $g['name'] ?? '',
                        'national_code' => $g['national_code'] ?? null,
                        'phone' => $g['phone'] ?? null,
                        'email' => $g['email'] ?? null,
                        'is_primary' => ($i === 0) ? 1 : 0,
                    ]);
                }
            }

            // Status log
            $this->db->insert('site_booking_status_log', [
                'booking_id' => $bookingId,
                'from_status' => null,
                'to_status' => 'draft',
                'changed_by' => $options['user_id'] ?? null,
                'reason' => 'رزرو ایجاد شد',
            ]);

            // Dispatch release job
            // Queue::dispatch(new ReleaseReservationJob($reservationToken), 'high', $holdMinutes * 60);

            $this->db->commit();

            $this->events->dispatch('reservation.held', [
                'booking_id' => $bookingId,
                'reservation_token' => $reservationToken,
                'expires_at' => $expiresAt,
                'pricing' => $pricing,
            ]);

            return (object)[
                'success' => true,
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode,
                'reservation_token' => $reservationToken,
                'expires_at' => $expiresAt,
                'pricing' => $pricing,
            ];
        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error('BookingService::create failed', ['error' => $e->getMessage()]);
            return (object)['success' => false, 'error' => 'system', 'message' => 'خطای سیستمی. لطفاً دوباره تلاش کنید.'];
        }
    }

    /**
     * Finalize booking (before payment)
     */
    public function finalize(int $bookingId): object
    {
        $booking = $this->bookingRepo->find($bookingId);
        if (!$booking) return (object)['success' => false, 'message' => 'رزرو یافت نشد.'];

        // Get reservation
        $reservation = $this->db->fetch(
            "SELECT * FROM site_reservations WHERE booking_id = :bid AND status = 'active'",
            [':bid' => $bookingId]
        );

        if (!$reservation) {
            return (object)['success' => false, 'message' => 'رزرو منقضی شده است.'];
        }

        // Re-validate availability
        $avail = $this->availability->check($booking->room_id, $booking->checkin_date, $booking->checkout_date, $booking->rooms_count);
        if (!$avail->available) {
            return (object)['success' => false, 'error' => 'availability_changed', 'message' => $avail->reason];
        }

        // Re-validate pricing
        $pricing = $this->pricing->calculate($booking->room_id, $booking->checkin_date, $booking->checkout_date, $booking->guests_adults);
        if ($this->currency->roundStorage($pricing->total_price) !== (int)$booking->final_price) {
            return (object)[
                'success' => false,
                'error' => 'price_changed',
                'message' => 'قیمت تغییر کرده',
                'new_price' => $pricing,
            ];
        }

        // Transition to waiting_payment
        $this->bookingRepo->updateStatus($bookingId, $booking->booking_status, 'waiting_payment');

        // Create booking snapshot
        $this->createSnapshot($bookingId, $pricing);

        // Convert reservation
        $this->db->update('site_reservations', ['status' => 'converted'], 'id = :id', [':id' => $reservation->id]);

        $this->events->dispatch('booking.finalized', ['booking_id' => $bookingId]);

        return (object)['success' => true, 'booking_id' => $bookingId];
    }

    /**
     * Cancel booking
     */
    public function cancel(int $bookingId, string $reason = ''): bool
    {
        $booking = $this->bookingRepo->find($bookingId);
        if (!$booking) return false;

        $this->bookingRepo->updateStatus($bookingId, $booking->booking_status, 'cancelled', null, $reason);
        $this->bookingRepo->update($bookingId, [
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancel_reason' => $reason,
        ]);

        // Release reservation
        $reservation = $this->db->fetch(
            "SELECT * FROM site_reservations WHERE booking_id = :bid AND status = 'active'",
            [':bid' => $bookingId]
        );
        if ($reservation) {
            $this->availability->release($reservation->reservation_token);
        }

        $this->events->dispatch('booking.cancelled', ['booking_id' => $bookingId, 'reason' => $reason]);
        return true;
    }

    public function find(int $bookingId): ?object
    {
        return $this->bookingRepo->find($bookingId);
    }

    public function findByCode(string $bookingCode): ?object
    {
        return $this->bookingRepo->findByCode($bookingCode);
    }

    public function findByUser(int $userId, ?string $status = null): array
    {
        return $this->bookingRepo->findByUser($userId, $status);
    }

    /**
     * Refresh reservation (re-check availability + pricing)
     */
    public function refresh(string $reservationToken): object
    {
        $reservation = $this->db->fetch(
            "SELECT * FROM site_reservations WHERE reservation_token = :token AND status = 'active'",
            [':token' => $reservationToken]
        );

        if (!$reservation) {
            return (object)['valid' => false, 'reason' => 'expired'];
        }

        if (strtotime($reservation->expires_at) < time()) {
            $this->availability->release($reservationToken);
            return (object)['valid' => false, 'reason' => 'expired'];
        }

        $booking = $this->bookingRepo->find($reservation->booking_id);
        if (!$booking) {
            return (object)['valid' => false, 'reason' => 'not_found'];
        }

        // Re-check availability
        $avail = $this->availability->check($booking->room_id, $booking->checkin_date, $booking->checkout_date, $booking->rooms_count);
        if (!$avail->available) {
            return (object)['valid' => false, 'reason' => 'availability_changed', 'message' => $avail->reason];
        }

        // Re-check pricing
        $pricing = $this->pricing->calculate($booking->room_id, $booking->checkin_date, $booking->checkout_date, $booking->guests_adults);
        $priceChanged = $this->currency->roundStorage($pricing->total_price) !== (int)$booking->final_price;

        // Extend expiration
        $holdMinutes = $this->settings->get('reservation_hold_minutes', 10);
        $newExpires = date('Y-m-d H:i:s', strtotime("+{$holdMinutes} minutes"));
        $this->db->update('site_reservations', ['expires_at' => $newExpires], 'id = :id', [':id' => $reservation->id]);

        return (object)[
            'valid' => !$priceChanged,
            'expires_at' => $newExpires,
            'time_remaining_seconds' => (int)(strtotime($newExpires) - time()),
            'pricing' => $this->currency->formatApi((int)$pricing->total_price),
            'price_changed' => $priceChanged,
            'availability_changed' => false,
            'new_price' => $priceChanged ? $pricing : null,
        ];
    }

    /**
     * Create booking snapshot
     */
    private function createSnapshot(int $bookingId, object $pricing): void
    {
        $booking = $this->bookingRepo->find($bookingId);
        $room = $this->roomRepo->find($booking->room_id);

        $hotelProfile = $this->db->fetch("SELECT * FROM site_hotel_profiles WHERE crm_hotel_id = :id", [':id' => $booking->crm_hotel_id]);
        $crmHotel = $this->db->fetch("SELECT * FROM hotel_rate_hotels WHERE id = :id", [':id' => $booking->crm_hotel_id]);

        $guests = $this->db->fetchAll("SELECT * FROM site_booking_guests WHERE booking_id = :bid", [':bid' => $bookingId]);

        $this->db->insert('site_booking_snapshots', [
            'booking_id' => $bookingId,
            'hotel_name' => $crmHotel->hotel_name ?? '',
            'hotel_star_rating' => $crmHotel->star_rating ?? null,
            'hotel_city' => $crmHotel->city ?? '',
            'hotel_address' => $hotelProfile->address ?? '',
            'hotel_phone' => $crmHotel->phone ?? null,
            'hotel_facilities_json' => $crmHotel->facilities ?? null,
            'room_type' => $room->room_type_key ?? '',
            'room_capacity' => $room->capacity_adults ?? null,
            'room_bed_type' => $room->bed_type ?? null,
            'room_size_sqm' => $room->size_sqm ?? null,
            'checkin_date' => $booking->checkin_date,
            'checkout_date' => $booking->checkout_date,
            'nights' => $booking->nights,
            'guests_adults' => $booking->guests_adults,
            'guests_children' => $booking->guests_children,
            'rooms_count' => $booking->rooms_count,
            'guests_json' => json_encode($guests),
            'base_price' => $booking->base_price,
            'markup_amount' => $booking->markup_amount,
            'total_price' => $booking->total_price,
            'final_price' => $booking->final_price,
            'currency' => 'IRR',
            'pricing_rules_applied_json' => json_encode($pricing->pricing_breakdown ?? []),
            'pricing_engine_version' => $this->settings->get('pricing_engine_version', '1.0.0'),
            'workflow_version' => $this->settings->get('workflow_version', '1.0.0'),
            'campaign_version' => $this->settings->get('campaign_engine_version', '1.0.0'),
        ]);
    }
}