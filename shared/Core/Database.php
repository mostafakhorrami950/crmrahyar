<?php
namespace Shared\Core;

class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;
    private bool $inTransaction = false;

    public function __construct(string $host, string $username, string $password, string $database)
    {
        $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
        $this->pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $config = $GLOBALS['app_config'];
            self::$instance = new self(
                $config['db']['host'],
                $config['db']['username'],
                $config['db']['password'],
                $config['db']['database']
            );
        }
        return self::$instance;
    }

    public function fetch(string $sql, array $params = []): ?object
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function query(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($c) => ":{$c}", array_keys($data)));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        $params = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $params = []): bool
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "`{$key}` = :set_{$key}";
            $params[":set_{$key}"] = $value;
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(string $table, string $where, array $params = []): bool
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
        $this->inTransaction = true;
    }

    public function commit(): void
    {
        $this->pdo->commit();
        $this->inTransaction = false;
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
        $this->inTransaction = false;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}