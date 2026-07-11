<?php
namespace Shared\Services;

use Shared\Core\Database;
use Shared\Core\Logger;

class WorkflowService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get allowed next statuses for a booking
     */
    public function getNextStatuses(string $currentStatus, ?string $userRole = null): array
    {
        $workflow = $this->getDefaultWorkflow('booking');
        if (!$workflow) return [];

        $transitions = $this->db->fetchAll(
            "SELECT * FROM site_workflow_transitions WHERE workflow_id = :wid AND from_status = :fs AND is_active = 1 ORDER BY sort_order ASC",
            [':wid' => $workflow->id, ':fs' => $currentStatus]
        );

        $allowed = [];
        foreach ($transitions as $t) {
            if ($t->allowed_role && $userRole && $t->allowed_role !== $userRole) continue;
            $allowed[] = [
                'status' => $t->to_status,
                'label' => $this->getStatusLabel($t->to_status),
                'requires_payment' => (bool)$t->requires_payment,
            ];
        }
        return $allowed;
    }

    /**
     * Validate and execute status transition
     */
    public function transition(int $bookingId, string $toStatus, ?int $userId = null, string $reason = ''): bool
    {
        $booking = $this->db->fetch("SELECT booking_status FROM site_bookings WHERE id = :id", [':id' => $bookingId]);
        if (!$booking) return false;

        $fromStatus = $booking->booking_status;
        if ($fromStatus === $toStatus) return true;

        // Validate transition is allowed
        $allowed = $this->getNextStatuses($fromStatus);
        $allowedStatuses = array_column($allowed, 'status');
        if (!in_array($toStatus, $allowedStatuses)) {
            Logger::warning('Invalid workflow transition', ['from' => $fromStatus, 'to' => $toStatus, 'booking' => $bookingId]);
            return false;
        }

        // Execute transition
        $this->db->update('site_bookings', ['booking_status' => $toStatus], 'id = :id', [':id' => $bookingId]);
        $this->db->insert('site_booking_status_log', [
            'booking_id' => $bookingId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $userId,
            'reason' => $reason,
        ]);

        Logger::info('Workflow transition', ['booking' => $bookingId, 'from' => $fromStatus, 'to' => $toStatus]);
        return true;
    }

    public function getWorkflow(string $entityType): ?object
    {
        return $this->getDefaultWorkflow($entityType);
    }

    public function getStatusLabel(string $status): string
    {
        $labels = [
            'draft' => 'پیش‌نویس',
            'reserved' => 'رزرو شده',
            'waiting_payment' => 'در انتظار پرداخت',
            'payment_processing' => 'در حال پرداخت',
            'paid' => 'پرداخت شده',
            'confirmed' => 'تأیید شده',
            'checked_in' => 'ورود',
            'checked_out' => 'خروج',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            'expired' => 'منقضی شده',
            'refunded' => 'بازپرداخت شده',
            'payment_failed' => 'خطای پرداخت',
        ];
        return $labels[$status] ?? $status;
    }

    private function getDefaultWorkflow(string $entityType): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM site_workflows WHERE entity_type = :et AND is_default = 1 LIMIT 1",
            [':et' => $entityType]
        );
    }
}