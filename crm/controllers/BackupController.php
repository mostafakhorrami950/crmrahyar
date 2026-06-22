<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\Logger;

class BackupController
{
    public function index(): void
    {
        $config = $GLOBALS['app_config'];
        $backupDir = __DIR__ . '/../storage/backups/';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        
        $files = [];
        $glob = glob($backupDir . '*.sql');
        if ($glob) {
            rsort($glob);
            foreach ($glob as $f) {
                $files[] = [
                    'name' => basename($f),
                    'size' => self::humanSize(filesize($f)),
                    'date' => date('Y-m-d H:i:s', filemtime($f)),
                ];
            }
        }
        
        \Core\View::render('backup/index', ['title' => 'بکاپ دیتابیس', 'files' => $files]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $config = $GLOBALS['app_config'];
        $db = $config['db'];
        
        $backupDir = __DIR__ . '/../storage/backups/';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . $filename;
        
        // Get all tables
        $pdo = Database::getInstance()->getPdo();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        
        $sql = "-- CRM Backup: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$db['name']}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        foreach ($tables as $table) {
            // Get CREATE TABLE
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            $sql .= "-- Table: {$table}\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $create[1] . ";\n\n";
            
            // Get data
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $sql .= "INSERT INTO `{$table}` (`" . implode('`,`', $columns) . "`) VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = array_map(function($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        return $pdo->quote($v);
                    }, array_values($row));
                    $values[] = '(' . implode(',', $rowValues) . ')';
                }
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        file_put_contents($filepath, $sql);
        Logger::info("Database backup created: {$filename}");
        
        Session::setFlash('success', "بکاپ {$filename} با موفقیت ایجاد شد (" . self::humanSize(filesize($filepath)) . ")");
        \Core\View::redirect('/backup');
    }

    public function download(array $params): void
    {
        Auth::requireAdmin();
        $filename = basename($params['file']);
        $filepath = __DIR__ . '/../storage/backups/' . $filename;
        
        if (!file_exists($filepath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
            Session::setFlash('danger', 'فایل یافت نشد.');
            \Core\View::redirect('/backup');
            return;
        }
        
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function delete(array $params): void
    {
        Auth::requireAdmin();
        $filename = basename($params['file']);
        $filepath = __DIR__ . '/../storage/backups/' . $filename;
        
        if (file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
            unlink($filepath);
            Logger::info("Backup deleted: {$filename}");
            Session::setFlash('success', 'بکاپ حذف شد.');
        }
        \Core\View::redirect('/backup');
    }

    private static function humanSize(int $bytes): string
    {
        $units = ['B','KB','MB','GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}

