<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ProductsController;
use App\Controllers\AdminController;
use Core\Router;
use App\Exceptions\ValidationException;
use Core\Session;

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
$router->get("/login", [AuthController::class, "loginView"]);
$router->post("/login", [AuthController::class, "login"]);
$router->post("/logout", [AuthController::class, "logout"]);

// Product routes
$router->get("/products", [ProductsController::class, "index"]);
$router->get("/products/show", [ProductsController::class, "show"]);
$router->get("/products/purchase", [ProductsController::class, "purchase"]);
$router->post("/products/process-purchase", [ProductsController::class, "processPurchase"]);
$router->get("/transactions/history", [ProductsController::class, "transactionHistory"]);

// Admin routes
$router->get("/admin", [AdminController::class, "dashboard"]);
$router->get("/admin/dashboard", [AdminController::class, "dashboard"]);

// Admin Product Management
$router->get("/admin/products", [AdminController::class, "products"]);
$router->get("/admin/products/create", [AdminController::class, "createProduct"]);
$router->post("/admin/products/store", [AdminController::class, "storeProduct"]);
$router->get("/admin/products/edit", [AdminController::class, "editProduct"]);
$router->post("/admin/products/update", [AdminController::class, "updateProduct"]);
$router->post("/admin/products/delete", [AdminController::class, "deleteProduct"]);

// Admin Transaction Management
$router->get("/admin/transactions", [AdminController::class, "transactions"]);
$router->get("/admin/transactions/show", [AdminController::class, "showTransaction"]);

// Admin User Management
$router->get("/admin/users", [AdminController::class, "users"]);
$router->get("/admin/users/create", [AdminController::class, "createUser"]);
$router->post("/admin/users/store", [AdminController::class, "storeUser"]);
$router->get("/admin/users/edit", [AdminController::class, "editUser"]);
$router->post("/admin/users/update", [AdminController::class, "updateUser"]);
$router->post("/admin/users/delete", [AdminController::class, "deleteUser"]);

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); // only uri without query strings or parameters
$requestMethod = isset($_POST["_method"])
    ? strtoupper($_POST["_method"])
    : $_SERVER["REQUEST_METHOD"];

// Age flash messages at the start of each request
Session::ageFlashMessages();

// resolve routes from request
try {
    $router->resolve($uri, $requestMethod);
} catch (ValidationException $e) {
    Session::flashErrors($e->errors);
    Session::flashOldValues($e->old);

    redirectBack();
}
