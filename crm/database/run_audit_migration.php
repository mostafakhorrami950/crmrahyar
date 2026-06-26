<?php
/**
 * Run Audit Trail & Call Center migration
 * Access: https://crm.mobixai.ir/crm/database/run_audit_migration.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/migrations/024_create_audit_and_callcenter.sql');
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $results = [];
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        try {
            $db->query($stmt);
            $results[] = '✅ ' . substr($stmt, 0, 80) . '...';
        } catch (\Exception $e) {
            $results[] = '⚠️ ' . $e->getMessage();
        }
    }
    
    echo "<h2>Migration Results:</h2>";
    echo "<pre>";
    foreach ($results as $r) {
        echo $r . "\n";
    }
    echo "</pre>";
    echo "<p style='color:green;font-weight:bold;'>✅ Migration completed!</p>";
    echo "<p><strong>⚠️ DELETE THIS FILE NOW!</strong></p>";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}