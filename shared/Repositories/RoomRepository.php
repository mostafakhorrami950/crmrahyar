<?php
namespace Shared\Repositories;

use Shared\Core\Database;

class RoomRepository extends BaseRepository
{
    protected string $table = 'site_rooms';

    public function findByHotel(int $crmHotelId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM site_rooms WHERE crm_hotel_id = :hid AND is_active = 1 AND deleted_at IS NULL ORDER BY sort_order ASC",
            [':hid' => $crmHotelId]
        );
    }

    public function findByCrmKey(int $crmHotelId, string $roomTypeKey): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM site_rooms WHERE crm_hotel_id = :hid AND room_type_key = :key AND deleted_at IS NULL",
            [':hid' => $crmHotelId, ':key' => $roomTypeKey]
        );
    }

    public function findByHotelWithRates(int $crmHotelId, string $dateFrom, string $dateTo): array
    {
        return $this->db->fetchAll(
            "SELECT r.*,
                (SELECT MIN(price) FROM site_room_daily_rates WHERE room_id = r.id AND date BETWEEN :df AND :dt AND price IS NOT NULL AND stop_sell = 0) as min_daily_price,
                (SELECT COUNT(*) FROM site_room_daily_rates WHERE room_id = r.id AND date BETWEEN :df2 AND :dt2 AND stop_sell = 0) as available_days
             FROM site_rooms r
             WHERE r.crm_hotel_id = :hid AND r.is_active = 1 AND r.deleted_at IS NULL
             ORDER BY r.sort_order ASC",
            [':hid' => $crmHotelId, ':df' => $dateFrom, ':dt' => $dateTo, ':df2' => $dateFrom, ':dt2' => $dateTo]
        );
    }

    public function getDailyRates(int $roomId, string $dateFrom, string $dateTo): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM site_room_daily_rates WHERE room_id = :rid AND date BETWEEN :df AND :dt ORDER BY date ASC",
            [':rid' => $roomId, ':df' => $dateFrom, ':dt' => $dateTo]
        );
    }

    public function upsertDailyRate(int $roomId, string $date, array $data): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM site_room_daily_rates WHERE room_id = :rid AND date = :date",
            [':rid' => $roomId, ':date' => $date]
        );
        if ($existing) {
            $this->db->update('site_room_daily_rates', $data, 'id = :id', [':id' => $existing->id]);
        } else {
            $data['room_id'] = $roomId;
            $data['date'] = $date;
            $this->db->insert('site_room_daily_rates', $data);
        }
    }
}