<?php
namespace Shared\Core;

class Router
{
    private array $routes = [];
    private array $notFoundHandler = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function set404(array $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function dispatch(string $path, string $method): void
    {
        $path = $this->normalizePath($path);
        $method = strtoupper($method);

        // Check exact match first
        if (isset($this->routes[$method][$path])) {
            $this->callHandler($this->routes[$method][$path], []);
            return;
        }

        // Check parameterized routes
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                $params = $this->matchRoute($route, $path);
                if ($params !== false) {
                    $this->callHandler($handler, $params);
                    return;
                }
            }
        }

        // 404
        if (!empty($this->notFoundHandler)) {
            $this->callHandler($this->notFoundHandler, []);
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    private function callHandler(array $handler, array $params): void
    {
        [$class, $method] = $handler;

        if (!class_exists($class)) {
            http_response_code(500);
            echo "Controller class not found: {$class}";
            return;
        }

        $controller = new $class();
        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Method not found: {$class}@{$method}";
            return;
        }

        $controller->$method($params);
    }

    private function matchRoute(string $route, string $path): false|array
    {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) return false;

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (strpos($part, '{') === 0 && strrpos($part, '}') === strlen($part) - 1) {
                $params[trim($part, '{}')] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return false;
            }
        }
        return $params;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}