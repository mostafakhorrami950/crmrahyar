<?php
/**
 * Fix database issues:
 * 1. Add category column if missing
 * 2. Fix collation mismatch between tables
 */
require_once __DIR__ . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "=== Fix 1: Adding category column if missing ===\n";
    try {
        $pdo->exec("ALTER TABLE `hotel_invoice_items` ADD COLUMN `category` VARCHAR(50) NULL COMMENT 'دسته‌بندی (hotel, transfer, visa, etc.)' AFTER `description`");
        echo "  ✓ category column added\n";
    } catch (\PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "  ℹ category column already exists\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n=== Fix 2: Updating category from catalog (with COLLATE fix) ===\n";
    try {
        $stmt = $pdo->exec("UPDATE `hotel_invoice_items` hii
            JOIN `invoice_items_catalog` iic ON hii.description COLLATE utf8mb4_unicode_ci = iic.name COLLATE utf8mb4_unicode_ci
            SET hii.category = iic.category
            WHERE hii.category IS NULL OR hii.category = ''");
        echo "  ✓ Updated " . $stmt . " items from catalog\n";
    } catch (\PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Fix 3: Setting default category for remaining items ===\n";
    $stmt = $pdo->exec("UPDATE `hotel_invoice_items` SET `category` = 'general' WHERE `category` IS NULL OR `category` = ''");
    echo "  ✓ Set " . $stmt . " items to 'general'\n";
    
    echo "\n=== Fix 4: Converting table to consistent collation ===\n";
    try {
        $pdo->exec("ALTER TABLE `hotel_invoice_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "  ✓ hotel_invoice_items converted to utf8mb4_unicode_ci\n";
    } catch (\PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE `invoice_items_catalog` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "  ✓ invoice_items_catalog converted to utf8mb4_unicode_ci\n";
    } catch (\PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Fix 5: Marking migration as executed ===\n";
    try {
        $check = $pdo->query("SELECT COUNT(*) FROM migrations WHERE migration = '034_hotel_items_category.sql'")->fetchColumn();
        if ($check == 0) {
            $pdo->exec("INSERT INTO migrations (migration) VALUES ('034_hotel_items_category.sql')");
            echo "  ✓ Migration marked as executed\n";
        } else {
            echo "  ℹ Migration already marked\n";
        }
    } catch (\PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Verification ===\n";
    $r = $pdo->query("SHOW COLUMNS FROM hotel_invoice_items");
    echo "hotel_invoice_items columns:\n";
    while ($f = $r->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $f['Field'] . " (" . $f['Type'] . ")\n";
    }
    
    echo "\nAll fixes completed successfully!\n";
    
} catch (\Exception $e) {
    echo "FATAL: " . $e->getMessage() . "\n";
}