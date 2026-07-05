<?php
namespace Core;

class Router
{
    private static $routes = [];
    private static $currentGroup = '';

    public static function get(string $path, $handler, string $permission = null): void
    {
        self::addRoute('GET', $path, $handler, $permission);
    }

    public static function post(string $path, $handler, string $permission = null): void
    {
        self::addRoute('POST', $path, $handler, $permission);
    }

    public static function group(string $prefix, callable $callback): void
    {
        $oldGroup = self::$currentGroup;
        self::$currentGroup .= $prefix;
        $callback();
        self::$currentGroup = $oldGroup;
    }

    private static function addRoute(string $method, string $path, $handler, string $permission = null): void
    {
        $fullPath = self::$currentGroup . $path;
        
        // Convert route parameters to regex: {param} -> (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $fullPath);
        $pattern = '#^' . $pattern . '$#';
        
        self::$routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'permission' => $permission,
        ];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_GET['url'] ?? '';
        $url = '/' . trim($url, '/');
        
        // If URL is empty or just slash, keep as / (landing page handles it)
        if ($url === '') {
            $url = '/';
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $url, $matches)) {
                // Check authentication - skip for public routes
                $publicPrefixes = ['/login', '/install', '/setup', '/payment/result', '/payment/verify', '/payment/callback', '/pay/', '/p/', '/hi/', '/hotel-pay/', '/hotel-rates/display'];
                // Exact public routes (not prefixes)
                $publicExact = ['/'];
                $isPublic = false;
                foreach ($publicPrefixes as $prefix) {
                    if (strpos($url, $prefix) === 0) {
                        $isPublic = true;
                        break;
                    }
                }
                
                if (!$isPublic && !in_array($url, $publicExact)) {
                    Auth::requireAuth();
                }

                // Check permission
                if ($route['permission']) {
                    Auth::requirePermission($route['permission']);
                }

                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Call handler - pass params array for controller methods
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    // Pass $params array directly (controllers expect array $params)
                    $controller->$methodName($params);
                } elseif (is_callable($handler)) {
                    $handler($params);
                }

                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        View::render('errors/404', ['title' => 'صفحه مورد نظر یافت نشد']);
    }
}