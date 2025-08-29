<?php

use Core\Session;

require_once __DIR__ . "/../vendor/autoload.php";

function dd(mixed $value, $typeHint = false): void
{
    echo "<pre style='background-color: #111; color: white; padding:1em; line-height: 1.8;'>";
    if ($typeHint) {
        var_dump($value);
    } else {
        print_r($value);
    }
    echo "</pre>";
    die();
}

function redirect(string $uri, int $responseCode = 302): void
{
    header("Location:{$uri}", response_code: $responseCode);
    exit();
}

function redirectBack(): void
{
    redirect($_SERVER["HTTP_REFERER"], responseCode: 302);
    exit();
}

function sessionError(string $key)
{
    return $_SESSION["_flash"]["errors"][$key] ?? "";
}

function oldValue(string $key)
{
    return $_SESSION["_flash"]["old"][$key] ?? "";
}
