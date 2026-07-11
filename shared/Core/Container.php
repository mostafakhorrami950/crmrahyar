<?php
namespace Shared\Core;

class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $singletons = [];
    private array $resolved = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = true;
    }

    public function make(string $abstract)
    {
        // Return cached singleton
        if (isset($this->singletons[$abstract]) && isset($this->resolved[$abstract])) {
            return $this->resolved[$abstract];
        }

        // Resolve from binding
        if (isset($this->bindings[$abstract])) {
            $instance = ($this->bindings[$abstract])($this);
            if (isset($this->singletons[$abstract])) {
                $this->resolved[$abstract] = $instance;
            }
            return $instance;
        }

        // Try auto-resolution via reflection
        if (class_exists($abstract)) {
            $instance = $this->resolve($abstract);
            if (isset($this->singletons[$abstract])) {
                $this->resolved[$abstract] = $instance;
            }
            return $instance;
        }

        throw new \RuntimeException("Cannot resolve: {$abstract}");
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || class_exists($abstract);
    }

    private function resolve(string $class)
    {
        $ref = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $deps = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                $deps[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $deps[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot resolve parameter: {$param->getName()} in {$class}");
            }
        }

        return $ref->newInstanceArgs($deps);
    }

    public function flush(): void
    {
        $this->bindings = [];
        $this->singletons = [];
        $this->resolved = [];
    }
}