<?php
namespace Shared\Services;

use Shared\Core\Logger;
use Shared\Interfaces\CacheInterface;

class CacheService implements CacheInterface
{
    private string $cacheDir;
    private array $tagIndex = []; // tag => [keys]

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../storage/cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key)
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) return null;

        $data = json_decode(file_get_contents($file), true);
        if (!$data) return null;

        // Check TTL
        if (isset($data['expires_at']) && $data['expires_at'] > 0 && $data['expires_at'] < time()) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, $value, ?int $ttl = null, array $tags = []): void
    {
        $expiresAt = $ttl ? time() + $ttl : 0;
        $data = [
            'key' => $key,
            'value' => $value,
            'expires_at' => $expiresAt,
            'tags' => $tags,
            'created_at' => time(),
        ];

        $file = $this->getFilePath($key);
        file_put_contents($file, json_encode($data), LOCK_EX);

        // Update tag index
        foreach ($tags as $tag) {
            $this->addToTagIndex($tag, $key);
        }
    }

    public function delete(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function invalidateByTag(string $tag): void
    {
        $keys = $this->getKeysByTag($tag);
        foreach ($keys as $key) {
            $this->delete($key);
        }
        // Remove tag index
        $tagFile = $this->cacheDir . 'tag_' . md5($tag) . '.json';
        if (file_exists($tagFile)) unlink($tagFile);
    }

    public function invalidateByTags(array $tags): void
    {
        foreach ($tags as $tag) {
            $this->invalidateByTag($tag);
        }
    }

    public function flush(): void
    {
        $files = glob($this->cacheDir . '*.json');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    private function getFilePath(string $key): string
    {
        return $this->cacheDir . md5($key) . '.json';
    }

    private function addToTagIndex(string $tag, string $key): void
    {
        $tagFile = $this->cacheDir . 'tag_' . md5($tag) . '.json';
        $keys = [];
        if (file_exists($tagFile)) {
            $keys = json_decode(file_get_contents($tagFile), true) ?: [];
        }
        if (!in_array($key, $keys)) {
            $keys[] = $key;
        }
        file_put_contents($tagFile, json_encode($keys), LOCK_EX);
    }

    private function getKeysByTag(string $tag): array
    {
        $tagFile = $this->cacheDir . 'tag_' . md5($tag) . '.json';
        if (!file_exists($tagFile)) return [];
        return json_decode(file_get_contents($tagFile), true) ?: [];
    }
}