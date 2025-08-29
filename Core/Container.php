<?php

namespace Core;

use ReflectionClass;
use Exception;

class Container
{
    private static $instance = null;
    private $bindings = [];
    private $instances = [];

    private function __construct() {}

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new Container();
        }
        return self::$instance;
    }

    /**
     * Bind a class or interface to a concrete implementation
     */
    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];
    }

    /**
     * Register a singleton binding
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve a class from the container
     */
    public function resolve(string $abstract)
    {
        // Return existing singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get binding info
        $binding = $this->bindings[$abstract] ?? null;
        $concrete = $binding['concrete'] ?? $abstract;

        // If concrete is a closure, execute it
        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        // Store singleton instances
        if ($binding && $binding['singleton']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Build a class with dependency injection
     */
    private function build(string $concrete)
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        // If no constructor, return new instance
        if (is_null($constructor)) {
            return new $concrete;
        }

        // Get constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();
            
            if ($dependency === null) {
                // Check if parameter has default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve dependency {$parameter->getName()}");
                }
            } else {
                $dependencyName = $dependency->getName();
                $dependencies[] = $this->resolve($dependencyName);
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Register an existing instance
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Check if a binding exists
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Remove a binding
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->instances[$abstract]);
    }

    /**
     * Clear all bindings and instances
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }

    /**
     * Reset container instance for testing
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
