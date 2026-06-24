<?php
// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}

require_once __DIR__ . '/../core/Database.php';
$db = \Core\Database::getInstance();

$sql = file_get_contents(__DIR__ . '/migrations/022_add_ai_settings.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        $db->execute($stmt);
        echo "OK: " . substr($stmt, 0, 60) . "...\n";
    }
}
echo "\nAI settings migration completed!\n";