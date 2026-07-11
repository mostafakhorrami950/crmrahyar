<?php
namespace Shared\Repositories;

use Shared\Core\Database;

class BookingRepository extends BaseRepository
{
    protected string $table = 'site_bookings';

    public function findByCode(string $code): ?object
    {
        return $this->db->fetch(
            "SELECT b.*, r.room_type_key, rp.slug as room_slug
             FROM site_bookings b
             LEFT JOIN site_rooms r ON b.room_id = r.id
             LEFT JOIN site_hotel_profiles hp ON b.crm_hotel_id = hp.crm_hotel_id
             WHERE b.booking_code = :code AND b.deleted_at IS NULL",
            [':code' => $code]
        );
    }

    public function findByIdempotencyKey(string $key): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM site_bookings WHERE idempotency_key = :key AND deleted_at IS NULL",
            [':key' => $key]
        );
    }

    public function findPendingPayments(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM site_bookings WHERE booking_status = 'waiting_payment' AND payment_status = 'unpaid' AND deleted_at IS NULL ORDER BY created_at ASC"
        );
    }

    public function findPendingConfirmation(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM site_bookings WHERE booking_status = 'reserved' AND deleted_at IS NULL ORDER BY created_at ASC"
        );
    }

    public function findByUser(int $userId, ?string $status = null): array
    {
        $where = "b.user_id = :uid AND b.deleted_at IS NULL";
        $params = [':uid' => $userId];
        if ($status) {
            $where .= " AND b.booking_status = :status";
            $params[':status'] = $status;
        }
        return $this->db->fetchAll(
            "SELECT b.*, hp.slug as hotel_slug, r.room_type_key
             FROM site_bookings b
             LEFT JOIN site_hotel_profiles hp ON b.crm_hotel_id = hp.crm_hotel_id
             LEFT JOIN site_rooms r ON b.room_id = r.id
             WHERE {$where} ORDER BY b.created_at DESC",
            $params
        );
    }

    public function findOverlapping(int $roomId, string $dateFrom, string $dateTo): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM site_bookings
             WHERE room_id = :rid AND checkin_date < :dt AND checkout_date > :df
             AND booking_status NOT IN ('cancelled','expired') AND payment_status != 'refunded'
             AND deleted_at IS NULL",
            [':rid' => $roomId, ':df' => $dateFrom, ':dt' => $dateTo]
        );
    }

    public function countByStatus(string $status, ?string $from = null, ?string $to = null): int
    {
        $where = "booking_status = :status AND deleted_at IS NULL";
        $params = [':status' => $status];
        if ($from) { $where .= " AND created_at >= :from"; $params[':from'] = $from; }
        if ($to) { $where .= " AND created_at <= :to"; $params[':to'] = $to; }
        $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_bookings WHERE {$where}", $params);
        return $result ? (int)$result->cnt : 0;
    }

    public function generateBookingCode(): string
    {
        $prefix = 'BK';
        $date = date('ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        return $prefix . $date . $random;
    }

    public function updateStatus(int $id, string $fromStatus, string $toStatus, ?int $changedBy = null, string $reason = ''): bool
    {
        $updated = $this->db->update($this->table, [
            'booking_status' => $toStatus,
            'version' => $this->db->fetch("SELECT version FROM site_bookings WHERE id = :id", [':id' => $id])->version + 1,
        ], "id = :id AND version = (SELECT version FROM (SELECT version FROM site_bookings WHERE id = :vid) AS v)", [':id' => $id, ':vid' => $id]);

        if ($updated) {
            $this->db->insert('site_booking_status_log', [
                'booking_id' => $id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => $changedBy,
                'reason' => $reason,
            ]);
        }
        return $updated;
    }
}