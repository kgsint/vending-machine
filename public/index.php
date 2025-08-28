<?php

use App\Controllers\HomeController;
use Core\Router;

session_start();

// constants
define("BASE_PATH", __DIR__ . "/../");
define("APP_PATH", BASE_PATH . "app/");
define("VIEW_PATH", APP_PATH . "views/");
define("CONFIG_PATH", BASE_PATH . "config/");

require BASE_PATH . "vendor/autoload.php";

$router = new Router();

// register routes
$router->get("/", [HomeController::class, "index"]);
$router->get("/home", [HomeController::class, "index"]);

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); // only uri without query strings or parameters
$requestMethod = isset($_POST["_method"])
    ? strtoupper($_POST["_method"])
    : $_SERVER["REQUEST_METHOD"];

// resolve routes from request
$router->resolve($uri, $requestMethod);
