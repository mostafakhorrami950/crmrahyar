<?php
namespace Shared\Core;

class Config
{
    private static ?Config $instance = null;
    private array $config = [];
    private array $dbSettings = [];
    private ?Database $db = null;
    private bool $dbLoaded = false;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->loadFile();
        }
        return self::$instance;
    }

    private function loadFile(): void
    {
        $path = __DIR__ . '/../../crm/config/app.php';
        if (file_exists($path)) {
            $this->config = require $path;
        }
    }

    public function setDatabase(Database $db): void
    {
        $this->db = $db;
    }

    public function get(string $key, $default = null)
    {
        // Check file config first (dot notation)
        $value = $this->getFromFile($key);
        if ($value !== null) return $value;

        // Check DB settings
        if ($this->db && !$this->dbLoaded) {
            $this->loadDbSettings();
        }
        return $this->dbSettings[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->dbSettings[$key] = $value;
    }

    public function getGroup(string $group): array
    {
        if ($this->db && !$this->dbLoaded) {
            $this->loadDbSettings();
        }
        $result = [];
        $prefix = $group . '_';
        foreach ($this->dbSettings as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $result[substr($key, strlen($prefix))] = $value;
            }
        }
        return $result;
    }

    public function all(): array
    {
        if ($this->db && !$this->dbLoaded) {
            $this->loadDbSettings();
        }
        return array_merge($this->getFromFileAll(), $this->dbSettings);
    }

    private function getFromFile(string $key)
    {
        $parts = explode('.', $key);
        $value = $this->config;
        foreach ($parts as $part) {
            if (!is_array($value) || !isset($value[$part])) return null;
            $value = $value[$part];
        }
        return $value;
    }

    private function getFromFileAll(): array
    {
        $flat = [];
        $this->flattenArray($this->config, '', $flat);
        return $flat;
    }

    private function flattenArray(array $arr, string $prefix, array &$result): void
    {
        foreach ($arr as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($value)) {
                $this->flattenArray($value, $newKey, $result);
            } else {
                $result[$newKey] = $value;
            }
        }
    }

    private function loadDbSettings(): void
    {
        $this->dbLoaded = true;
        try {
            $rows = $this->db->fetchAll("SELECT setting_key, setting_value, setting_type FROM site_settings WHERE 1=1");
            foreach ($rows as $row) {
                $value = $row->setting_value;
                switch ($row->setting_type ?? 'string') {
                    case 'boolean': $value = (bool)$value; break;
                    case 'number': $value = (float)$value; break;
                    case 'json': $value = json_decode($value, true) ?: $value; break;
                }
                $this->dbSettings[$row->setting_key] = $value;
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }
    }

    public function url(): string
    {
        return $this->get('url', '');
    }
}