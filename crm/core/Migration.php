<?php
namespace Core;

use PDO;

class Migration
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/app.php';
        $db = $this->config['db'];
        
        // First connect without database to create it if needed
        try {
            $pdo = new PDO(
                "mysql:host={$db['host']};port={$db['port']};charset=utf8mb4",
                $db['user'],
                $db['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$db['name']}`");
            
            $this->db = $pdo;
        } catch (\PDOException $e) {
            die("Migration failed: " . $e->getMessage());
        }
    }

    public function run(): void
    {
        echo "Running migrations...\n";
        
        $this->createMigrationsTable();
        $executed = $this->getExecutedMigrations();
        
        $migrations = $this->getMigrationFiles();
        
        foreach ($migrations as $migration) {
            if (!in_array($migration, $executed)) {
                $this->executeMigration($migration);
            }
        }
        
        echo "All migrations completed successfully!\n";
        
        // Seed default data
        $this->seed();
    }
    
    private function createMigrationsTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    
    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getMigrationFiles(): array
    {
        $files = glob(__DIR__ . '/../database/migrations/*.sql');
        sort($files);
        return array_map(function($f) {
            return basename($f);
        }, $files);
    }
    
    private function executeMigration(string $migration): void
    {
        echo "  Running: {$migration}\n";
        $sql = file_get_contents(__DIR__ . '/../database/migrations/' . $migration);
        
        try {
            // Split by semicolons and execute each statement separately
            // This handles multiple ALTER TABLE and other statements properly
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($s) { return !empty($s) && $s !== ''; }
            );
            
            $hasError = false;
            foreach ($statements as $statement) {
                // Skip empty lines and comments-only blocks
                $clean = trim(preg_replace('/--.*$/m', '', $statement));
                $clean = preg_replace('/\/\*.*?\*\//s', '', $clean);
                if (empty(trim($clean))) continue;
                
                // Skip DELIMITER and procedure blocks (not supported in PDO)
                if (stripos($statement, 'DELIMITER') !== false) continue;
                if (stripos($statement, 'CREATE PROCEDURE') !== false) continue;
                if (stripos($statement, 'DROP PROCEDURE') !== false) continue;
                if (stripos($statement, 'CALL ') !== false && stripos($statement, '(') !== false) continue;
                
                try {
                    $this->db->exec($statement);
                } catch (\PDOException $e) {
                    $code = $e->getCode();
                    $msg = $e->getMessage();
                    // Ignore duplicate column/table/index errors (1060=duplicate column, 1061=duplicate key, 1050=table exists, 1062=duplicate entry)
                    if (in_array($code, ['42S01', '42000']) || 
                        strpos($msg, '1060') !== false || 
                        strpos($msg, '1050') !== false ||
                        strpos($msg, '1061') !== false ||
                        strpos($msg, '1062') !== false ||
                        strpos($msg, 'Duplicate column') !== false ||
                        strpos($msg, 'already exists') !== false) {
                        echo "  ℹ Statement skipped (already applied): " . substr(trim($statement), 0, 80) . "...\n";
                    } else {
                        throw $e;
                    }
                }
            }
            
            $stmt = $this->db->prepare("INSERT INTO migrations (migration) VALUES (:m)");
            $stmt->execute([':m' => $migration]);
            echo "  ✓ {$migration} completed\n";
        } catch (\PDOException $e) {
            echo "  ✗ {$migration} failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function seed(): void
    {
        echo "  Seeding default data (INSERT IGNORE - safe to re-run)...\n";
        
        // Create default admin role (INSERT IGNORE = skip if exists)
        $this->db->exec("INSERT IGNORE INTO roles (name, slug, description, is_system) VALUES 
            ('مدیر اصلی', 'super_admin', 'دسترسی کامل به تمام بخش‌ها', 1),
            ('اپراتور', 'operator', 'مدیریت معاملات و مشتریان خود', 1),
            ('مدیر فروش', 'sales_manager', 'مدیریت تیم فروش و مشاهده گزارشات', 1)
        ");
        
        // Create super admin user only if not exists (username: admin, password: admin123)
        $existingAdmin = $this->db->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1")->fetch(PDO::FETCH_OBJ);
        if (!$existingAdmin) {
            $password = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password, full_name, role_id, is_active) VALUES 
                ('admin', :pass, 'مدیر اصلی', 1, 1)
            ");
            $stmt->execute([':pass' => $password]);
            echo "  ✓ Admin user created (admin/admin123)\n";
        }
        
        // Create default pipeline for travel agency (INSERT IGNORE)
        $this->db->exec("INSERT IGNORE INTO pipelines (name, description, is_default) VALUES 
            ('فروش تور', 'پایپ لاین فروش تورهای مسافرتی', 1)
        ");
        
        // Create default stages only if pipeline 1 has no stages
        $stageCount = $this->db->query("SELECT COUNT(*) as cnt FROM stages WHERE pipeline_id = 1")->fetch(PDO::FETCH_OBJ);
        if ($stageCount->cnt == 0) {
            $this->db->exec("INSERT INTO stages (pipeline_id, name, color, order_index) VALUES 
                (1, 'مشتری جدید', '#6B7280', 1),
                (1, 'در حال مذاکره', '#F59E0B', 2),
                (1, 'پیش‌فاکتور', '#3B82F6', 3),
                (1, 'در انتظار پرداخت', '#8B5CF6', 4),
                (1, 'پرداخت شده', '#10B981', 5),
                (1, 'لغو شده', '#EF4444', 6)
            ");
        }
        
        // Set permissions for roles (INSERT IGNORE)
        $this->db->exec("INSERT IGNORE INTO role_permissions (role_id, permission, scope) 
            SELECT 1, slug, 'all' FROM permissions");
        
        // Operator permissions (INSERT IGNORE)
        $this->db->exec("INSERT IGNORE INTO role_permissions (role_id, permission, scope) VALUES 
            (2, 'deals.view', 'own'), (2, 'deals.create', 'own'), (2, 'deals.edit', 'own'), (2, 'deals.delete', 'own'),
            (2, 'contacts.view', 'own'), (2, 'contacts.create', 'own'), (2, 'contacts.edit', 'own'),
            (2, 'pipelines.view', 'all')
        ");
        
        // Sales manager permissions (INSERT IGNORE)
        $this->db->exec("INSERT IGNORE INTO role_permissions (role_id, permission, scope) VALUES 
            (3, 'deals.view', 'all'), (3, 'deals.create', 'all'), (3, 'deals.edit', 'all'), (3, 'deals.delete', 'all'),
            (3, 'contacts.view', 'all'), (3, 'contacts.create', 'all'), (3, 'contacts.edit', 'all'), (3, 'contacts.delete', 'all'),
            (3, 'pipelines.view', 'all'), (3, 'pipelines.create', 'all'), (3, 'pipelines.edit', 'all'),
            (3, 'reports.view', 'all'), (3, 'reports.export', 'all')
        ");
        
        echo "  ✓ Default data seeded (safe re-run)\n";
    }
}