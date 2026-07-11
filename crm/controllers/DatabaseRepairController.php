<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

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
            'custom_fields', 'custom_field_values', 'db_repair_log',
            'change_logs', 'phone_lines', 'phone_assignments',
            'hotel_rate_hotels', 'hotel_rate_list',
            // Site tables
            'site_cities', 'site_neighborhoods', 'site_hotel_profiles', 'site_rooms',
            'site_room_daily_rates', 'site_pricing_rules', 'site_campaigns',
            'site_bookings', 'site_booking_guests', 'site_booking_status_log',
            'site_booking_snapshots', 'site_reservations',
            'site_agencies', 'site_agency_transactions', 'site_ledger',
            'site_media', 'site_search_index', 'site_workflows', 'site_workflow_transitions',
            'site_blog_posts', 'site_pages', 'site_faqs', 'site_reviews', 'site_notifications',
            'site_settings', 'site_event_logs', 'site_analytics', 'site_seo_redirects',
            'site_queue_jobs', 'site_feature_flags', 'site_outbox', 'site_idempotency_keys',
            'site_audit_logs', 'site_migrations',
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
        
        // Get repair log - with error handling if table doesn't exist yet
        $repairLog = [];
        try {
            $repairLog = $db->fetchAll("SELECT * FROM db_repair_log ORDER BY created_at DESC LIMIT 50");
        } catch (\Exception $e) {
            // Table doesn't exist yet - that's ok
        }
        
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
            // Ensure migrations table exists
            try {
                $db->query("CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `migration` VARCHAR(255) NOT NULL UNIQUE,
                    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (\Exception $e) {}
            
            // Ensure db_repair_log table exists
            try {
                $db->query("CREATE TABLE IF NOT EXISTS `db_repair_log` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `table_name` VARCHAR(100),
                    `action` VARCHAR(100),
                    `description` TEXT,
                    `status` VARCHAR(20) DEFAULT 'success',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (\Exception $e) {}
            
            // Get already executed migrations from migrations table
            $executedMigrations = [];
            try {
                $rows = $db->fetchAll("SELECT migration FROM migrations");
                foreach ($rows as $row) {
                    $executedMigrations[] = $row->migration;
                }
            } catch (\Exception $e) {
                // migrations table might not exist yet
            }
            
            // Run only unexecuted migration files
            $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
            sort($migrationFiles);
            
            foreach ($migrationFiles as $file) {
                $filename = basename($file);
                
                // Skip if already executed
                if (in_array($filename, $executedMigrations)) {
                    $results[] = [
                        'table' => $filename,
                        'status' => 'skipped',
                        'message' => 'قبلا اجرا شده'
                    ];
                    continue;
                }
                
                $sql = file_get_contents($file);
                $statements = explode(';', $sql);
                
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (empty($stmt)) continue;
                    
                    try {
                        $db->query($stmt);
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        // Table may already exist or column already exists - that's ok
                        if (stripos($msg, 'already exists') === false && 
                            stripos($msg, 'Duplicate column') === false && 
                            stripos($msg, 'Duplicate key') === false &&
                            stripos($msg, 'Duplicate entry') === false) {
                            throw $e;
                        }
                    }
                }
                
                // Mark as executed
                $db->insert('migrations', ['migration' => $filename]);
                
                $results[] = [
                    'table' => $filename,
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
            $result = $this->ensureColumn($db, 'deals', 'source_id', 'INT DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'deals', 'loss_reason_id', 'INT DEFAULT NULL');
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
            $result = $this->ensureColumn($db, 'contacts', 'company_phone', 'VARCHAR(20) DEFAULT NULL');
            if ($result) $repairs[] = $result;
            $result = $this->ensureColumn($db, 'contacts', 'category_id', 'INT DEFAULT NULL');
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
            
            // Check hotel_invoices table columns
            try {
                $result = $this->ensureColumn($db, 'hotel_invoices', 'adults_count', 'INT DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'children_3to5_count', 'INT DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'children_under3_count', 'INT DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'deposit_amount', 'DECIMAL(15,2) DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'payment_token', 'VARCHAR(100) NULL');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'invoice_type', "ENUM('proforma','confirmed') DEFAULT 'proforma'");
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'discount_percent', 'DECIMAL(5,2) DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'short_code', 'VARCHAR(20) NULL');
                if ($result) $repairs[] = $result;
                // Ensure invoice_status ENUM includes 'paid'
                try {
                    $col = $db->fetch("SHOW COLUMNS FROM hotel_invoices WHERE Field = 'invoice_status'");
                    if ($col && strpos($col->Type ?? '', 'paid') === false) {
                        $db->query("ALTER TABLE `hotel_invoices` MODIFY COLUMN `invoice_status` ENUM('draft','final','paid','cancelled','pending','settled','prepaid') DEFAULT 'pending'");
                        $repairs[] = ['table' => 'hotel_invoices', 'column' => 'invoice_status', 'action' => 'update_enum', 'description' => 'ENUM وضعیت فاکتور بروزرسانی شد (اضافه شدن paid)'];
                    }
                } catch (\Exception $e) {}
            } catch (\Exception $e) {
                // hotel_invoices table might not exist yet
            }
            
            // Check hotel_invoices new columns
            try {
                $result = $this->ensureColumn($db, 'hotel_invoices', 'agency_name', 'VARCHAR(255) NULL');
                if ($result) $repairs[] = $result;

                $result = $this->ensureColumn($db, 'hotel_invoices', 'guest_address', 'TEXT NULL');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoices', 'ps_note', 'TEXT NULL');
                if ($result) $repairs[] = $result;
            } catch (\Exception $e) {}

            // Check hotel_invoice_items table columns
            try {
                $result = $this->ensureColumn($db, 'hotel_invoice_items', 'default_price', 'DECIMAL(15,2) NOT NULL DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoice_items', 'room_type', 'VARCHAR(100) NULL');
                if ($result) $repairs[] = $result;
                // Migrate from is_half_price (TINYINT) to half_price_qty (INT)
                try {
                    $col = $db->fetch("SHOW COLUMNS FROM hotel_invoice_items WHERE Field = 'is_half_price'");
                    if ($col) {
                        $db->query("ALTER TABLE hotel_invoice_items CHANGE COLUMN `is_half_price` `half_price_qty` INT DEFAULT 0");
                        $repairs[] = 'hotel_invoice_items: is_half_price → half_price_qty (INT)';
                    }
                } catch (\Exception $e) {}
                $result = $this->ensureColumn($db, 'hotel_invoice_items', 'half_price_qty', 'INT DEFAULT 0');
                if ($result) $repairs[] = $result;
                $result = $this->ensureColumn($db, 'hotel_invoice_items', 'half_price_rate', 'DECIMAL(15,2) DEFAULT 0');
                if ($result) {
                    $repairs[] = $result;
                    // Update existing items: set default_price = unit_price
                    try {
                        $db->query("UPDATE `hotel_invoice_items` SET `default_price` = `unit_price` WHERE `default_price` = 0");
                    } catch (\Exception $e) {}
                }
                $result = $this->ensureColumn($db, 'hotel_invoice_items', 'category', "VARCHAR(50) DEFAULT 'general'");
                if ($result) $repairs[] = $result;
            } catch (\Exception $e) {
                // hotel_invoice_items table might not exist yet
            }
            
            // Check invoice_settings table
            try {
                $db->query("CREATE TABLE IF NOT EXISTS `invoice_settings` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                    `setting_value` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (\Exception $e) {}
            
            // Run site migrations (PHP-based)
            $siteResults = [];
            try {
                $runner = new \Shared\Core\MigrationRunner($db);
                $siteResults = $runner->run();
            } catch (\Exception $e) {
                $siteResults[] = ['name' => 'site_migrations', 'status' => 'error', 'message' => $e->getMessage()];
            }
            
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
                'message' => 'تعمیر دیتابیس با موفقیت انجام شد. ' . (count($results) + count($repairs) + count($siteResults)) . ' عملیات اجرا شد.',
                'migrations' => $results,
                'column_repairs' => $repairs,
                'site_migrations' => $siteResults
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
        exit;
    }

    public function errorLogs(): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        
        try {
            // Get error logs from activity_logs (actions with 'error' or 'failed')
            $errorLogs = $db->fetchAll(
                "SELECT al.*, u.full_name as user_name 
                 FROM activity_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 WHERE al.action LIKE '%error%' OR al.action LIKE '%fail%' OR al.action LIKE 'delete%'
                 ORDER BY al.created_at DESC LIMIT 100"
            );
            
            $totalErrors = $db->fetch("SELECT COUNT(*) as count FROM activity_logs WHERE action LIKE '%error%' OR action LIKE '%fail%'");
            $todayErrors = $db->fetch("SELECT COUNT(*) as count FROM activity_logs WHERE (action LIKE '%error%' OR action LIKE '%fail%') AND DATE(created_at) = CURDATE()");
            $uniqueUsers = $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM activity_logs WHERE (action LIKE '%error%' OR action LIKE '%fail%') AND user_id IS NOT NULL");
        } catch (\Exception $e) {
            $errorLogs = [];
            $totalErrors = (object)['count' => 0];
            $todayErrors = (object)['count' => 0];
            $uniqueUsers = (object)['count' => 0];
        }
        
        View::render('database/error_logs', [
            'title' => 'گزارش خطاها',
            'errorLogs' => $errorLogs,
            'totalErrors' => $totalErrors->count ?? 0,
            'todayErrors' => $todayErrors->count ?? 0,
            'uniqueUsers' => $uniqueUsers->count ?? 0,
        ]);
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