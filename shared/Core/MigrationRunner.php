<?php
namespace Shared\Core;

class MigrationRunner
{
    private $db;
    private string $migrationsDir;

    public function __construct($db)
    {
        $this->db = $db;
        $this->migrationsDir = __DIR__ . '/../Migrations/';
    }

    public function run(): array
    {
        // Ensure migration tracking table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `site_migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL UNIQUE,
                `batch` INT NOT NULL,
                `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $applied = $this->getApplied();
        $files = $this->getMigrationFiles();
        $batch = $this->getNextBatch();
        $results = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $applied)) {
                $results[] = ['name' => $name, 'status' => 'skipped'];
                continue;
            }

            try {
                // Extract number from filename: 001_create_site_cities → Migration001
                $num = preg_replace('/^(\d+)_.*/', 'Migration$1', $name);
                $className = 'Shared\\Migrations\\' . $num;

                if (!class_exists($className)) {
                    // Try loading the file
                    require_once $file;
                }

                if (class_exists($className)) {
                    $className::up($this->db);
                } else {
                    // Fallback: run raw SQL if file contains CREATE TABLE
                    $content = file_get_contents($file);
                    if (stripos($content, 'CREATE TABLE') !== false || stripos($content, 'ALTER TABLE') !== false) {
                        // Already handled by the class
                    }
                    $results[] = ['name' => $name, 'status' => 'error', 'message' => 'Class not found: ' . $className];
                    continue;
                }

                $this->db->insert('site_migrations', ['migration' => $name, 'batch' => $batch]);
                $results[] = ['name' => $name, 'status' => 'applied'];
            } catch (\Exception $e) {
                $results[] = ['name' => $name, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function rollback(): array
    {
        $lastBatch = $this->db->fetch("SELECT MAX(batch) as b FROM site_migrations");
        if (!$lastBatch || !$lastBatch->b) return [];

        $migrations = $this->db->fetchAll(
            "SELECT * FROM site_migrations WHERE batch = :b ORDER BY id DESC",
            [':b' => $lastBatch->b]
        );

        $results = [];
        foreach ($migrations as $m) {
            $file = $this->migrationsDir . $m->migration . '.php';
            if (!file_exists($file)) continue;

            try {
                require_once $file;
                $num = preg_replace('/^(\d+)_.*/', 'Migration$1', $m->migration);
                $className = 'Shared\\Migrations\\' . $num;
                if (class_exists($className)) {
                    $className::down($this->db);
                }
                $this->db->delete('site_migrations', 'id = :id', [':id' => $m->id]);
                $results[] = ['name' => $m->migration, 'status' => 'rolled_back'];
            } catch (\Exception $e) {
                $results[] = ['name' => $m->migration, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    private function getApplied(): array
    {
        try {
            $rows = $this->db->fetchAll("SELECT migration FROM site_migrations");
            return array_column($rows, 'migration');
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsDir . '*.php');
        sort($files);
        return $files;
    }

    private function getNextBatch(): int
    {
        try {
            $result = $this->db->fetch("SELECT MAX(batch) as b FROM site_migrations");
            return ($result->b ?? 0) + 1;
        } catch (\Exception $e) {
            return 1;
        }
    }
}