<?php
namespace Shared\Interfaces;

interface BookingServiceInterface
{
    public function create(int $roomId, string $dateFrom, string $dateTo, int $guests, array $guestData, array $options = []): object;
    public function finalize(int $bookingId): object;
    public function cancel(int $bookingId, string $reason = ''): bool;
    public function find(int $bookingId): ?object;
    public function findByCode(string $bookingCode): ?object;
    public function findByUser(int $userId, ?string $status = null): array;
    public function refresh(string $reservationToken): object;
}