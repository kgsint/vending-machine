<?php

namespace App;

class Router
{
    private array $routes = [];

    public function get(string $uri, array $action)
    {
        $this->routes[] = [
            "uri" => $uri,
            "method" => "GET",
            "action" => $action,
        ];
    }

    public function post(string $uri, array $action)
    {
        $this->routes[] = [
            "uri" => $uri,
            "method" => "POST",
            "action" => $action,
        ];
    }

    public function put(string $uri, array $action)
    {
        $this->routes[] = [
            "uri" => $uri,
            "method" => "PUT",
            "action" => $action,
        ];
    }

    public function patch(string $uri, array $action)
    {
        $this->routes[] = [
            "uri" => $uri,
            "method" => "PATCH",
            "action" => $action,
        ];
    }

    public function delete(string $uri, array $action)
    {
        $this->routes[] = [
            "uri" => $uri,
            "method" => "DELETE",
            "action" => $action,
        ];
    }

    public function resolve(string $requestUri, string $requestMethod)
    {
        foreach ($this->routes as $route) {
            if (
                $route["uri"] === $requestUri &&
                $route["method"] === strtoupper($requestMethod)
            ) {
                if (is_array($route["action"])) {
                    [$class, $action] = $route["action"];

                    if (!class_exists($class)) {
                        throw new Exception("Class $class not found");
                    }

                    if (!method_exists($class, $action)) {
                        throw new Exception(
                            "Method $action not found in class $class",
                        );
                    }

                    $class = new $class();

                    return call_user_func([$class, $action], []);
                }
            }
        }

        return false;
    }
}
