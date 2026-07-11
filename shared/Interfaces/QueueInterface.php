<?php
namespace Shared\Interfaces;

interface QueueInterface
{
    const PRIORITY_HIGH = 'high';
    const PRIORITY_DEFAULT = 'default';
    const PRIORITY_LOW = 'low';

    public function dispatch($job, string $priority = self::PRIORITY_DEFAULT, int $delaySeconds = 0): int;
    public function process(?string $priority = null, int $limit = 10): int;
    public function retry(int $jobId): bool;
    public function purge(?string $queue = null): int;
    public function size(?string $queue = null): int;
}