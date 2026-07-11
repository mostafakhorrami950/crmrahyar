<?php
namespace Shared\Interfaces;

interface AvailabilityServiceInterface
{
    public function check(int $roomId, string $dateFrom, string $dateTo, int $roomsNeeded = 1): object;
    public function reserve(int $roomId, string $dateFrom, string $dateTo, int $quantity, string $reservationToken): bool;
    public function release(string $reservationToken): bool;
    public function getInventory(int $roomId, string $dateFrom, string $dateTo): array;
    public function updateDaily(int $roomId, string $date, int $availableCount, bool $stopSell = false): bool;
}