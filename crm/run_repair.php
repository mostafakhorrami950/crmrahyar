<?php
// Database credentials from .env
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'tvhwswck_crm';
$dbUser = 'tvhwswck_crm';
$dbPass = 'q!0kEXP?uLxk94$2';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to database: $dbName" . PHP_EOL;

    // Check if company_phone column exists in contacts
    $stmt = $pdo->query('SHOW COLUMNS FROM contacts');
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns in contacts: " . implode(', ', $cols) . PHP_EOL;
    
    if (!in_array('company_phone', $cols)) {
        $pdo->exec('ALTER TABLE contacts ADD COLUMN company_phone VARCHAR(20) NULL AFTER phone');
        echo "✓ company_phone column added successfully!" . PHP_EOL;
    } else {
        echo "✓ company_phone column already exists." . PHP_EOL;
    }

    // Also check deal_sources table exists
    $tables = $pdo->query('SHOW TABLES');
    $tableList = $tables->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('deal_sources', $tableList)) {
        echo "Creating deal_sources table..." . PHP_EOL;
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `deal_sources` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        // Insert default sources
        $sources = ['اینستاگرام', 'تلگرام', 'واتساپ', 'وبسایت', 'دوستان و آشنایان',
                    'تبلیغات گوگل', 'مراجعه حضوری', 'تماس تلفنی', 'بازاریابی', 'سایر'];
        foreach ($sources as $name) {
            $stmt = $pdo->prepare("INSERT INTO deal_sources (name) VALUES (?)");
            $stmt->execute([$name]);
        }
        echo "✓ deal_sources table created with 10 default sources!" . PHP_EOL;
    } else {
        echo "✓ deal_sources table already exists." . PHP_EOL;
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}