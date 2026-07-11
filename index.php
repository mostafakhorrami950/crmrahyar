<?php
/**
 * Hotel Booking Platform - Main Entry Point
 * API-Driven Architecture
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefixes = [
        'Shared\\' => __DIR__ . '/shared/',
        'Site\\' => __DIR__ . '/site/',
        'Api\\' => __DIR__ . '/api/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) { require $file; return; }
        }
    }
});

// Bootstrap
use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Logger;
use Shared\Core\Container;

try {
    // Load CRM config
    $GLOBALS['app_config'] = require __DIR__ . '/crm/config/app.php';

    // Initialize Container
    $container = Container::getInstance();

    // Database (shared with CRM)
    $container->singleton(Database::class, function() {
        $config = $GLOBALS['app_config'];
        return new Database($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
    });

    // Config
    $container->singleton(Config::class, function($c) {
        return Config::getInstance();
    });

    // Set DB on config
    $config = $container->make(Config::class);
    $config->setDatabase($container->make(Database::class));

    // Route the request
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);

    // Remove base path
    if ($scriptName !== '/' && $scriptName !== '\\') {
        $requestUri = substr($requestUri, strlen($scriptName));
    }

    // Remove query string for routing
    $path = parse_url($requestUri, PHP_URL_PATH);
    $path = '/' . trim($path, '/');

    // Skip CRM routes
    if (strpos($path, '/crm') === 0) {
        // Let CRM handle itself
        require __DIR__ . '/crm/index.php';
        exit;
    }

    // Skip static files
    $staticPaths = ['/assets/', '/storage/', '/uploads/', '/public/'];
    foreach ($staticPaths as $sp) {
        if (strpos($path, $sp) === 0) return false; // Let Apache handle
    }

    // Load site routes
    $router = new \Shared\Core\Router();

    // Load route definitions
    require __DIR__ . '/site/Routes.php';

    // Dispatch
    $router->dispatch($path, $_SERVER['REQUEST_METHOD']);

} catch (\Throwable $e) {
    Logger::logException($e, 'Application Error');
    http_response_code(500);
    echo '<h1>خطای سرور</h1><p>لطفاً بعداً تلاش کنید.</p>';
}