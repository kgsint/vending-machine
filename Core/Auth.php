<?php

namespace Core;

class Auth
{
    public static function check()
    {
        if (empty($_SESSION["user"])) {
            return false;
        }

        return true;
    }
}
