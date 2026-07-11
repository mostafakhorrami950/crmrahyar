<?php
namespace Shared\Repositories;

use Shared\Core\Database;
use Shared\Core\Logger;
use Shared\Interfaces\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(int $id): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id AND `deleted_at` IS NULL",
            [':id' => $id]
        );
    }

    public function findAll(array $filters = [], string $orderBy = 'id DESC', int $limit = 100, int $offset = 0): array
    {
        $where = "`deleted_at` IS NULL";
        $params = [];

        foreach ($filters as $key => $value) {
            if ($value === null) continue;
            $paramKey = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
            $where .= " AND `{$key}` = {$paramKey}";
            $params[$paramKey] = $value;
        }

        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function create(array $data): int
    {
        $this->audit('INSERT', null, null, $data);
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): bool
    {
        $old = $this->find($id);
        $this->audit('UPDATE', $id, $old ? (array)$old : null, $data);
        return $this->db->update($this->table, $data, "`{$this->primaryKey}` = :id", [':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $old = $this->find($id);
        $this->audit('DELETE', $id, $old ? (array)$old : null, null);
        return $this->db->delete($this->table, "`{$this->primaryKey}` = :id", [':id' => $id]);
    }

    public function softDelete(int $id): bool
    {
        $old = $this->find($id);
        $this->audit('DELETE', $id, $old ? (array)$old : null, ['deleted_at' => date('Y-m-d H:i:s')]);
        return $this->db->update($this->table, ['deleted_at' => date('Y-m-d H:i:s')], "`{$this->primaryKey}` = :id", [':id' => $id]);
    }

    public function restore(int $id): bool
    {
        return $this->db->update($this->table, ['deleted_at' => null], "`{$this->primaryKey}` = :id", [':id' => $id]);
    }

    public function count(array $filters = []): int
    {
        $where = "`deleted_at` IS NULL";
        $params = [];

        foreach ($filters as $key => $value) {
            if ($value === null) continue;
            $paramKey = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
            $where .= " AND `{$key}` = {$paramKey}";
            $params[$paramKey] = $value;
        }

        $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM `{$this->table}` WHERE {$where}", $params);
        return $result ? (int)$result->cnt : 0;
    }

    public function findBySlug(string $slug): ?object
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `slug` = :slug AND `deleted_at` IS NULL",
            [':slug' => $slug]
        );
    }

    protected function audit(string $action, ?int $recordId, ?array $oldValues, ?array $newValues): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $this->db->insert('site_audit_logs', [
                'table_name' => $this->table,
                'record_id' => $recordId ?? 0,
                'action' => $action,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'changed_by' => $userId,
                'ip' => $ip,
            ]);
        } catch (\Exception $e) {
            Logger::warning('Audit log failed', ['error' => $e->getMessage()]);
        }
    }
}