<?php

namespace Core;

class View
{
    public function __construct(
        private string $path,
        private array $data = [],
    ) {}

    public static function make(string $path, array $data = [])
    {
        $instance = new static($path, $data);
        $instance->render();

        return $instance;
    }

    public function render()
    {
        $fullViewPath = VIEW_PATH . $this->path . ".view.php";

        if (!file_exists($fullViewPath)) {
            throw new \Exception("View file not found: $fullViewPath");
        }

        extract($this->data);
        require_once $fullViewPath;

        exit();
    }
}
