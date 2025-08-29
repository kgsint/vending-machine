<?php

namespace Core;

class Session
{
    public static function error(string $key)
    {
        return $_SESSION["_flash"]["errors"][$key] ?? "";
    }

    public static function oldValue(string $key)
    {
        return $_SESSION["_flash"]["old"][$key] ?? "";
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function flush()
    {
        $_SESSION = [];
    }

    public static function flashError($key, $value)
    {
        $_SESSION["_flash"]["errors"][$key] = $value;
    }

    public static function flashOld($key, $value)
    {
        $_SESSION["_flash"]["old"][$key] = $value;
    }

    public static function flashErrors($errors)
    {
        $_SESSION["_flash"]["errors"] = $errors;
        $_SESSION["_flash"]["_age"] = 0; // Mark as fresh
    }

    public static function flashOldValues($oldValues)
    {
        $_SESSION["_flash"]["old"] = $oldValues;
        $_SESSION["_flash"]["_age"] = 0; // Mark as fresh
    }

    public static function clearFlash()
    {
        unset($_SESSION["_flash"]);
    }

    public static function hasFlash()
    {
        return isset($_SESSION["_flash"]) && !empty($_SESSION["_flash"]);
    }

    public static function ageFlashMessages()
    {
        if (isset($_SESSION["_flash"])) {
            if (!isset($_SESSION["_flash"]["_age"])) {
                $_SESSION["_flash"]["_age"] = 0;
            }
            
            $_SESSION["_flash"]["_age"]++;
            
            // Clear flash messages after they've aged (been through one request cycle)
            if ($_SESSION["_flash"]["_age"] > 1) {
                self::clearFlash();
            }
        }
    }
}
