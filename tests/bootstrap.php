<?php

// Define constants for testing
define("BASE_PATH", __DIR__ . "/../");
define("APP_PATH", BASE_PATH . "app/");
define("VIEW_PATH", APP_PATH . "views/");
define("CONFIG_PATH", BASE_PATH . "config/");

// Load Composer autoloader
require_once BASE_PATH . "vendor/autoload.php";

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
