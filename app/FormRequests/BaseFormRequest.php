<?php

namespace App\FormRequests;

use App\Exceptions\ValidationException;

class BaseFormRequest
{
    protected array $errors = [];

    public function __construct(protected array $attributes) {}

    public static function validate(array $attributes)
    {
        $instance = new static($attributes);
        // dd($instance->errors);
        if ($instance->hasErrors()) {
            $instance->throw();
        }

        return $instance;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function throw()
    {
        ValidationException::throw($this->errors, $this->attributes);
    }

    public function setError(string $key, string $message)
    {
        $this->errors[$key] = $message;

        return $this;
    }
}
