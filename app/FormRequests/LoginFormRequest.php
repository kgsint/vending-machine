<?php

namespace App\FormRequests;

use Core\Validator;

class LoginFormRequest extends BaseFormRequest
{
    public function __construct(protected array $attributes)
    {
        parent::__construct($attributes);

        // required validation
        foreach ($attributes as $name => $value) {
            if (Validator::required($attributes[$name])) {
                $this->errors[$name] =
                    str_replace(["-", "_"], " ", ucfirst($name)) .
                    " cannot be empty";
            }
        }
    }
}
