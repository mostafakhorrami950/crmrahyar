<?php
namespace Shared\Interfaces;

interface CacheInterface
{
    public function get(string $key);
    public function set(string $key, $value, ?int $ttl = null, array $tags = []): void;
    public function delete(string $key): void;
    public function has(string $key): bool;
    public function invalidateByTag(string $tag): void;
    public function invalidateByTags(array $tags): void;
    public function flush(): void;
}