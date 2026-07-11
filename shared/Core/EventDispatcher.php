<?php
namespace Shared\Core;

class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function listen(string $eventType, callable $listener, int $priority = 10): void
    {
        $this->listeners[$eventType][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];
        // Sort by priority
        usort($this->listeners[$eventType], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    public function dispatch(string $eventType, array $payload = []): void
    {
        if (!isset($this->listeners[$eventType])) return;

        foreach ($this->listeners[$eventType] as $entry) {
            try {
                ($entry['listener'])($payload);
            } catch (\Exception $e) {
                Logger::error("Event listener failed: {$eventType}", [
                    'error' => $e->getMessage(),
                    'payload' => $payload,
                ]);
            }
        }
    }

    public function getListeners(string $eventType): array
    {
        return $this->listeners[$eventType] ?? [];
    }

    public function hasListeners(string $eventType): bool
    {
        return !empty($this->listeners[$eventType]);
    }

    public function flush(): void
    {
        $this->listeners = [];
    }
}