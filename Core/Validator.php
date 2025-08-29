<?php

namespace Core;

class Validator
{
    public static function required(mixed $input, $min = 1, $max = INF): bool
    {
        // string input
        if (is_string($input)) {
            $input = trim($input);

            if (
                empty($input) ||
                strlen($input) < $min ||
                strlen($input) > $max
            ) {
                return true;
            }
        }

        // array input
        if (is_array($input)) {
            if (
                count($input) === 0 ||
                count($input) < $min ||
                count($input) > $max
            ) {
                return true;
            }
        }

        return false;
    }
}
