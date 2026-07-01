<?php
$config = require __DIR__ . '/config/app.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== hotel_invoice_items columns ===\n";
    $r = $pdo->query('SHOW COLUMNS FROM hotel_invoice_items');
    while ($f = $r->fetch(PDO::FETCH_ASSOC)) {
        echo $f['Field'] . ' - ' . $f['Type'] . "\n";
    }
    
    echo "\n=== Table collations ===\n";
    $r = $pdo->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$db['name']}' AND TABLE_NAME IN ('hotel_invoice_items','invoice_items_catalog','hotel_invoices')");
    while ($f = $r->fetch(PDO::FETCH_ASSOC)) {
        echo $f['TABLE_NAME'] . ' -> ' . $f['TABLE_COLLATION'] . "\n";
    }
    
    echo "\n=== Column collations ===\n";
    $r = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='{$db['name']}' AND TABLE_NAME IN ('hotel_invoice_items','invoice_items_catalog') AND COLLATION_NAME IS NOT NULL");
    while ($f = $r->fetch(PDO::FETCH_ASSOC)) {
        echo $f['TABLE_NAME'] . '.' . $f['COLUMN_NAME'] . ' -> ' . $f['COLLATION_NAME'] . "\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}