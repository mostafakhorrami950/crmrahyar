<?php
namespace Shared\Repositories;

use Shared\Core\Database;

class SettingsRepository extends BaseRepository
{
    protected string $table = 'site_settings';

    public function get(string $key, $default = null)
    {
        $row = $this->db->fetch("SELECT setting_value, setting_type FROM site_settings WHERE setting_key = :key", [':key' => $key]);
        if (!$row) return $default;
        $value = $row->setting_value;
        switch ($row->setting_type ?? 'string') {
            case 'boolean': return (bool)$value;
            case 'number': return (float)$value;
            case 'json': return json_decode($value, true) ?: $value;
            default: return $value;
        }
    }

    public function set(string $key, $value, string $type = 'string', ?string $group = null): void
    {
        $existing = $this->db->fetch("SELECT id FROM site_settings WHERE setting_key = :key", [':key' => $key]);
        $storedValue = is_array($value) ? json_encode($value) : (string)$value;
        if ($existing) {
            $this->db->update('site_settings', ['setting_value' => $storedValue, 'setting_type' => $type], 'id = :id', [':id' => $existing->id]);
        } else {
            $this->db->insert('site_settings', ['setting_key' => $key, 'setting_value' => $storedValue, 'setting_type' => $type, 'setting_group' => $group]);
        }
    }

    public function getGroup(string $group): array
    {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value, setting_type FROM site_settings WHERE setting_group = :g", [':g' => $group]);
        $result = [];
        foreach ($rows as $row) {
            $value = $row->setting_value;
            switch ($row->setting_type) {
                case 'boolean': $value = (bool)$value; break;
                case 'number': $value = (float)$value; break;
                case 'json': $value = json_decode($value, true) ?: $value; break;
            }
            $result[$row->setting_key] = $value;
        }
        return $result;
    }

    public function getAll(): array
    {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value, setting_type FROM site_settings");
        $result = [];
        foreach ($rows as $row) {
            $value = $row->setting_value;
            switch ($row->setting_type) {
                case 'boolean': $value = (bool)$value; break;
                case 'number': $value = (float)$value; break;
                case 'json': $value = json_decode($value, true) ?: $value; break;
            }
            $result[$row->setting_key] = $value;
        }
        return $result;
    }
}