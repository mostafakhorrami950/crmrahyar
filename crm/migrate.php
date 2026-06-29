<?php
/**
 * Database Migration Script
 * Access this file via browser to run migrations
 * DELETE THIS FILE AFTER RUNNING
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$config = require __DIR__ . '/config/app.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

// Run migration 029
$sqlFile = __DIR__ . '/database/migrations/029_update_hotel_invoices_and_settings.sql';
if (!file_exists($sqlFile)) {
    die("فایل مایگریشن یافت نشد.");
}

$sql = file_get_contents($sqlFile);
$sqlStatements = array_filter(array_map('trim', explode(';', $sql)));

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($sqlStatements as $stmt) {
    if (empty($stmt)) continue;
    try {
        $pdo->exec($stmt);
        $successCount++;
    } catch (PDOException $e) {
        $errorCount++;
        $errors[] = $e->getMessage();
    }
}

echo "<!DOCTYPE html><html lang='fa' dir='rtl'><head><meta charset='UTF-8'><title>مایگریشن</title>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css'>";
echo "</head><body><div class='container mt-5'>";
echo "<h2>نتیجه مایگریشن</h2>";
echo "<div class='alert alert-success'>عملیات موفق: {$successCount}</div>";
if ($errorCount > 0) {
    echo "<div class='alert alert-warning'>خطا: {$errorCount}</div>";
    foreach ($errors as $err) {
        echo "<div class='alert alert-danger'>{$err}</div>";
    }
}
echo "<a href='" . ($config['url'] ?? '/') . "/settings/invoice' class='btn btn-primary'>رفتن به تنظیمات فاکتور</a>";
echo "</div></body></html>";