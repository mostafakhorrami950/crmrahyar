<?php
namespace Core;

class Logger
{
    private static $logDir = null;

    private static function getLogDir(): string
    {
        if (self::$logDir === null) {
            self::$logDir = __DIR__ . '/../storage/logs/';
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
        }
        return self::$logDir;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $file = self::getLogDir() . "{$date}.log";

        $userId = 'guest';
        try {
            if (class_exists('\Core\Auth') && Auth::check()) {
                $userId = Auth::id();
            }
        } catch (\Exception $e) {}

        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$time}] [{$level}] [user:{$userId}] {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    public static function getFiles(): array
    {
        $dir = self::getLogDir();
        $files = glob($dir . '*.log');
        rsort($files);
        return array_map(function($f) {
            return [
                'name' => basename($f),
                'size' => filesize($f),
                'modified' => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }, $files);
    }

    public static function read(string $filename): string
    {
        $file = self::getLogDir() . basename($filename);
        if (!file_exists($file) || pathinfo($file, PATHINFO_EXTENSION) !== 'log') {
            return '';
        }
        return file_get_contents($file);
    }

    public static function clear(): void
    {
        $files = glob(self::getLogDir() . '*.log');
        foreach ($files as $f) {
            unlink($f);
        }
    }
}