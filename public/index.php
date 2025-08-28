<?php

use App\Controllers\HomeController;
use App\Router;

session_start();

// constants
define("BASE_PATH", __DIR__ . "/../");
define("APP_PATH", BASE_PATH . "app/");
define("VIEW_PATH", APP_PATH . "views/");

require BASE_PATH . "vendor/autoload.php";

$router = new Router();
// register routes
$router->get("/", [HomeController::class, "index"]);
$router->get("/home", [HomeController::class, "index"]);

$uri = $_SERVER["REQUEST_URI"];
$requestMethod = $_SERVER["REQUEST_METHOD"];

// resolve routes from request
$router->resolve($uri, $requestMethod);
