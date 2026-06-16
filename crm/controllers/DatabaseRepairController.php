<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;

class DatabaseRepairController
{
    public function index(): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        
        // Get all expected tables
        $expectedTables = [
            'roles', 'permissions', 'role_permissions', 'users', 'activity_logs',
            'pipelines', 'stages', 'deals', 'deal_activities', 'contacts',
            'payments', 'sms_history', 'settings',
            'custom_fields', 'custom_field_values', 'db_repair_log'
        ];
        
        // Get actual tables
        $actualTables = [];
        $tables = $db->fetchAll("SHOW TABLES");
        foreach ($tables as $t) {
            $row = (array)$t;
            $actualTables[] = reset($row);
        }
        
        $missingTables = array_diff($expectedTables, $actualTables);
        $existsCount = count(array_intersect($expectedTables, $actualTables));
        
        // Get migration files
        $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
        sort($migrationFiles);
        
        // Get repair log
        $repairLog = $db->fetchAll("SELECT * FROM db_repair_log ORDER BY created_at DESC LIMIT 50");
        
        View::render('database/index', [
            'title' => 'تعمیر دیتابیس',
            'expectedTables' => $expectedTables,
            'actualTables' => $actualTables,
            'missingTables' => $missingTables,
            'existsCount' => $existsCount,
            'totalExpected' => count($expectedTables),
            'migrationFiles' => $migrationFiles,
            'repairLog' => $repairLog,
        ]);
    }

    public function runRepair(): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        $results = [];
        
        try {
            // Run all migration files
            $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
            sort($migrationFiles);
            
            foreach ($migrationFiles as $file) {
                $sql = file_get_contents($file);
                $statements = explode(';', $sql);
                
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (empty($stmt)) continue;
                    
                    try {
                        $db->query($stmt);
                    } catch (\Exception $e) {
                        // Table may already exist, that's ok
                        if (stripos($e->getMessage(), 'already exists') === false) {
                            throw $e;
                        }
                    }
                }
                
                $results[] = [
                    'table' => basename($file),
                    'status' => 'success',
                    'message' => 'اجرا شد'
                ];
            }
            
            // Now check for missing columns in each table
            $repairs = [];
            
            // Check users table
            $result = $this->ensureColumn($db, 'users', 'phone', 'VARCHAR(20) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'deals', 'expected_close_date', 'DATE DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'deals', 'probability', 'INT DEFAULT 0');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'deals', 'lost_reason', 'TEXT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'national_code', 'VARCHAR(20) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'passport_number', 'VARCHAR(50) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'address', 'TEXT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'company', 'VARCHAR(200) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'source', 'VARCHAR(100) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'tags', 'VARCHAR(500) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'notes', 'TEXT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'pipelines', 'is_default', 'TINYINT(1) DEFAULT 0');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'pipelines', 'is_active', 'TINYINT(1) DEFAULT 1');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'stages', 'is_active', 'TINYINT(1) DEFAULT 1');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'payments', 'merchant', 'VARCHAR(100) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'payments', 'callback_url', 'VARCHAR(500) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'sms_history', 'deal_id', 'INT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'sms_history', 'contact_id', 'INT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'sms_history', 'message_outbox_id', 'VARCHAR(100) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'sms_history', 'error_message', 'TEXT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            
            // Log repairs
            foreach ($repairs as $r) {
                $db->insert('db_repair_log', [
                    'table_name' => $r['table'],
                    'action' => $r['action'],
                    'description' => $r['description'],
                    'status' => 'success'
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'تعمیر دیتابیس با موفقیت انجام شد. ' . (count($results) + count($repairs)) . ' عملیات اجرا شد.',
                'migrations' => $results,
                'column_repairs' => $repairs
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
        exit;
    }

    private function ensureColumn(Database $db, string $table, string $column, string $definition): ?array
    {
        try {
            $columns = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
            $colNames = array_map(function($c) { return $c->Field; }, $columns);
            
            if (!in_array($column, $colNames)) {
                $db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                ActivityLog::log('db_repair', 'table', 0, "ستون {$column} به جدول {$table} اضافه شد");
                return [
                    'table' => $table,
                    'column' => $column,
                    'action' => 'add_column',
                    'description' => "ستون {$column} به جدول {$table} اضافه شد"
                ];
            }
        } catch (\Exception $e) {
            return [
                'table' => $table,
                'column' => $column,
                'action' => 'error',
                'description' => "خطا: " . $e->getMessage()
            ];
        }
        return null;
    }
}