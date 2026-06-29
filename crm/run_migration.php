<?php
$pdo = new PDO(
    'mysql:host=localhost;port=3306;dbname=tvhwswck_crm;charset=utf8mb4',
    'root',
    ''
);
$sql = file_get_contents(__DIR__ . '/database/migrations/029_update_hotel_invoices_and_settings.sql');
$pdo->exec($sql);
echo 'Migration 029 executed successfully.';