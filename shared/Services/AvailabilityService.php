<?php
namespace Shared\Services;

use Shared\Core\Database;
use Shared\Core\Logger;
use Shared\Interfaces\AvailabilityServiceInterface;
use Shared\Repositories\RoomRepository;

class AvailabilityService implements AvailabilityServiceInterface
{
    private Database $db;
    private RoomRepository $roomRepo;

    public function __construct(Database $db, RoomRepository $roomRepo)
    {
        $this->db = $db;
        $this->roomRepo = $roomRepo;
    }

    /**
     * Check availability for a room in date range
     */
    public function check(int $roomId, string $dateFrom, string $dateTo, int $roomsNeeded = 1): object
    {
        $room = $this->roomRepo->find($roomId);
        if (!$room) {
            return (object)['available' => false, 'reason' => 'اتاق یافت نشد', 'available_count' => 0];
        }

        $maxInventory = $room->max_inventory;

        // Check daily rates for stop_sell and capacity
        $dailyRates = $this->roomRepo->getDailyRates($roomId, $dateFrom, $dateTo);
        $d1 = new \DateTime($dateFrom);
        $d2 = new \DateTime($dateTo);
        $nights = (int)$d2->diff($d1)->days;

        for ($i = 0; $i < $nights; $i++) {
            $date = clone $d1;
            $date->modify("+{$i} days");
            $dateStr = $date->format('Y-m-d');

            // Find daily rate for this date
            $dailyRate = null;
            foreach ($dailyRates as $dr) {
                if ($dr->date === $dateStr) { $dailyRate = $dr; break; }
            }

            if ($dailyRate && $dailyRate->stop_sell) {
                return (object)['available' => false, 'reason' => "توقف فروش در تاریخ {$dateStr}", 'available_count' => 0];
            }

            // Check existing bookings for this date
            $bookedCount = $this->getBookedCount($roomId, $dateStr);
            $dayMax = ($dailyRate && $dailyRate->capacity_override) ? $dailyRate->capacity_override : $maxInventory;
            $availableForDay = $dayMax - $bookedCount;

            if ($availableForDay < $roomsNeeded) {
                return (object)['available' => false, 'reason' => "موجودی کافی در تاریخ {$dateStr} نیست", 'available_count' => $availableForDay];
            }
        }

        return (object)['available' => true, 'reason' => '', 'available_count' => $maxInventory];
    }

    /**
     * Reserve inventory (decrease available count)
     * Uses FOR UPDATE for concurrency control
     */
    public function reserve(int $roomId, string $dateFrom, string $dateTo, int $quantity, string $reservationToken): bool
    {
        $this->db->beginTransaction();
        try {
            // Lock and check
            $d1 = new \DateTime($dateFrom);
            $d2 = new \DateTime($dateTo);
            $nights = (int)$d2->diff($d1)->days;

            for ($i = 0; $i < $nights; $i++) {
                $date = clone $d1;
                $date->modify("+{$i} days");
                $dateStr = $date->format('Y-m-d');

                // FOR UPDATE lock
                $locked = $this->db->fetch(
                    "SELECT id, stop_sell, capacity_override FROM site_room_daily_rates WHERE room_id = :rid AND date = :date FOR UPDATE",
                    [':rid' => $roomId, ':date' => $dateStr]
                );

                if ($locked && $locked->stop_sell) {
                    $this->db->rollback();
                    return false;
                }

                $room = $this->roomRepo->find($roomId);
                $maxInventory = ($locked && $locked->capacity_override) ? $locked->capacity_override : ($room ? $room->max_inventory : 0);
                $bookedCount = $this->getBookedCount($roomId, $dateStr);

                if (($maxInventory - $bookedCount) < $quantity) {
                    $this->db->rollback();
                    return false;
                }
            }

            $this->db->commit();
            Logger::info("Inventory reserved", ['room_id' => $roomId, 'from' => $dateFrom, 'to' => $dateTo, 'qty' => $quantity, 'token' => $reservationToken]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error("Reserve failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Release reserved inventory
     */
    public function release(string $reservationToken): bool
    {
        try {
            $reservation = $this->db->fetch(
                "SELECT * FROM site_reservations WHERE reservation_token = :token AND status = 'active'",
                [':token' => $reservationToken]
            );
            if (!$reservation) return false;

            $this->db->update('site_reservations', ['status' => 'expired'], 'id = :id', [':id' => $reservation->id]);
            Logger::info("Reservation released", ['token' => $reservationToken]);
            return true;
        } catch (\Exception $e) {
            Logger::error("Release failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getInventory(int $roomId, string $dateFrom, string $dateTo): array
    {
        return $this->roomRepo->getDailyRates($roomId, $dateFrom, $dateTo);
    }

    public function updateDaily(int $roomId, string $date, int $availableCount, bool $stopSell = false): bool
    {
        try {
            $this->roomRepo->upsertDailyRate($roomId, $date, [
                'capacity_override' => $availableCount,
                'stop_sell' => $stopSell ? 1 : 0,
            ]);
            return true;
        } catch (\Exception $e) {
            Logger::error("updateDaily failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function getBookedCount(int $roomId, string $date): int
    {
        $result = $this->db->fetch(
            "SELECT COALESCE(SUM(rooms_count), 0) as total FROM site_bookings 
             WHERE room_id = :rid AND checkin_date <= :date AND checkout_date > :date
             AND booking_status NOT IN ('cancelled','expired','draft') AND payment_status != 'refunded' AND deleted_at IS NULL",
            [':rid' => $roomId, ':date' => $date]
        );
        return $result ? (int)$result->total : 0;
    }
}