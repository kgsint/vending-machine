<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Core\Database;
use Core\Session;
use Core\View;
use App\Exceptions\ValidationException;

class AdminController
{
    private $db;
    private $productModel;
    private $transactionModel;
    private $userModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->productModel = new Product($this->db);
        $this->transactionModel = new Transaction($this->db);
        $this->userModel = new User($this->db);
    }

    public function dashboard()
    {
        $this->requireAdmin();

        try {
            // Get statistics
            $totalProducts = $this->productModel->count(false); // Include inactive
            $activeProducts = $this->productModel->count(true);
            $totalUsers = $this->userModel->count();
            $totalTransactions = $this->transactionModel->count();

            // Get recent transactions
            $recentTransactions = $this->transactionModel->all(10, 0);

            // Get top selling products
            $topProducts = $this->transactionModel->getTopSellingProducts(
                5,
                "month",
            );

            // Get sales stats for current month
            $startDate = date("Y-m-01");
            $endDate = date("Y-m-t");
            $monthlySales = $this->transactionModel->getSalesStats(
                $startDate,
                $endDate,
            );

            return View::make("admin/dashboard", [
                "totalProducts" => $totalProducts,
                "activeProducts" => $activeProducts,
                "totalUsers" => $totalUsers,
                "totalTransactions" => $totalTransactions,
                "recentTransactions" => $recentTransactions,
                "topProducts" => $topProducts,
                "monthlySales" => $monthlySales,
            ]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading dashboard: " . $e->getMessage(),
            );
            return redirect("/");
        }
    }

    public function products()
    {
        $this->requireAdmin();

        $page = (int) ($_GET["page"] ?? 1);
        $perPage = (int) ($_GET["per_page"] ?? 10);

        // Validate per-page options
        $allowedPerPage = [10, 20, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 20;
        }

        $limit = $perPage;
        $offset = ($page - 1) * $limit;

        try {
            $products = $this->productModel->all(
                $limit,
                $offset,
                "created_at",
                "DESC",
                false,
            ); // Include inactive
            $totalProducts = $this->productModel->count(false);
            $totalPages = ceil($totalProducts / $limit);

            return View::make("admin/products/index", [
                "products" => $products,
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalProducts" => $totalProducts,
                "perPage" => $perPage,
            ]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading products: " . $e->getMessage(),
            );
            return redirect("/admin/dashboard");
        }
    }

    public function createProduct()
    {
        $this->requireAdmin();
        return View::make("admin/products/create");
    }

    public function storeProduct()
    {
        $this->requireAdmin();

        $errors = $this->validateProductData($_POST);
        if (!empty($errors)) {
            Session::flashErrors($errors);
            Session::flashOldValues($_POST);
            return redirect("/admin/products/create");
        }

        try {
            $this->productModel->create([
                "name" => $_POST["name"],
                "price" => (float) $_POST["price"],
                "quantity_available" => (int) $_POST["quantity_available"],
                "description" => $_POST["description"] ?? null,
                "image_url" => $_POST["image_url"] ?? null,
                "is_active" => isset($_POST["is_active"]),
            ]);

            Session::flashError("success", "Product created successfully");
            return redirect("/admin/products");
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error creating product: " . $e->getMessage(),
            );
            return redirect("/admin/products/create");
        }
    }

    public function editProduct()
    {
        $this->requireAdmin();

        $id = (int) ($_GET["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "Product not found");
            return redirect("/admin/products");
        }

        try {
            $product = $this->productModel->find($id);
            if (!$product) {
                Session::flashError("error", "Product not found");
                return redirect("/admin/products");
            }

            return View::make("admin/products/edit", ["product" => $product]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading product: " . $e->getMessage(),
            );
            return redirect("/admin/products");
        }
    }

    public function updateProduct()
    {
        $this->requireAdmin();

        $id = (int) ($_POST["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "Product not found");
            return redirect("/admin/products");
        }

        $errors = $this->validateProductData($_POST);
        if (!empty($errors)) {
            Session::flashErrors($errors);
            Session::flashOldValues($_POST);
            return redirect("/admin/products/edit?id=" . $id);
        }

        try {
            $this->productModel->update($id, [
                "name" => $_POST["name"],
                "price" => (float) $_POST["price"],
                "quantity_available" => (int) $_POST["quantity_available"],
                "description" => $_POST["description"] ?? null,
                "image_url" => $_POST["image_url"] ?? null,
                "is_active" => isset($_POST["is_active"]),
            ]);

            Session::flashError("success", "Product updated successfully");
            return redirect("/admin/products");
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error updating product: " . $e->getMessage(),
            );
            return redirect("/admin/products/edit?id=" . $id);
        }
    }

    public function deleteProduct()
    {
        $this->requireAdmin();

        $id = (int) ($_POST["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "Product not found");
            return redirect("/admin/products");
        }

        try {
            $this->productModel->delete($id);
            Session::flashError("success", "Product deleted successfully");
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error deleting product: " . $e->getMessage(),
            );
        }

        return redirect("/admin/products");
    }

    public function transactions()
    {
        $this->requireAdmin();

        $page = (int) ($_GET["page"] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $transactions = $this->transactionModel->all($limit, $offset);
            $totalTransactions = $this->transactionModel->count();
            $totalPages = ceil($totalTransactions / $limit);

            return View::make("admin/transactions/index", [
                "transactions" => $transactions,
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalTransactions" => $totalTransactions,
            ]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading transactions: " . $e->getMessage(),
            );
            return redirect("/admin/dashboard");
        }
    }

    public function showTransaction()
    {
        $this->requireAdmin();

        $id = (int) ($_GET["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "Transaction not found");
            return redirect("/admin/transactions");
        }

        try {
            $transaction = $this->transactionModel->find($id);
            if (!$transaction) {
                Session::flashError("error", "Transaction not found");
                return redirect("/admin/transactions");
            }

            return View::make("admin/transactions/show", [
                "transaction" => $transaction,
            ]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading transaction: " . $e->getMessage(),
            );
            return redirect("/admin/transactions");
        }
    }

    public function users()
    {
        $this->requireAdmin();

        $page = (int) ($_GET["page"] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $users = $this->userModel->all($limit, $offset);
            $totalUsers = $this->userModel->count();
            $totalPages = ceil($totalUsers / $limit);

            return View::make("admin/users/index", [
                "users" => $users,
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalUsers" => $totalUsers,
            ]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading users: " . $e->getMessage(),
            );
            return redirect("/admin/dashboard");
        }
    }

    public function createUser()
    {
        $this->requireAdmin();
        return View::make("admin/users/create");
    }

    public function storeUser()
    {
        $this->requireAdmin();

        $errors = $this->validateUserData($_POST);
        if (!empty($errors)) {
            Session::flashErrors($errors);
            Session::flashOldValues($_POST);
            return redirect("/admin/users/create");
        }

        try {
            $this->userModel->create([
                "username" => $_POST["username"],
                "email" => $_POST["email"],
                "password" => $_POST["password"],
                "role" => $_POST["role"],
            ]);

            Session::flashError("success", "User created successfully");
            return redirect("/admin/users");
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error creating user: " . $e->getMessage(),
            );
            return redirect("/admin/users/create");
        }
    }

    public function editUser()
    {
        $this->requireAdmin();

        $id = (int) ($_GET["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "User not found");
            return redirect("/admin/users");
        }

        try {
            $user = $this->userModel->find($id);
            if (!$user) {
                Session::flashError("error", "User not found");
                return redirect("/admin/users");
            }

            return View::make("admin/users/edit", ["user" => $user]);
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error loading user: " . $e->getMessage(),
            );
            return redirect("/admin/users");
        }
    }

    public function updateUser()
    {
        $this->requireAdmin();

        $id = (int) ($_POST["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "User not found");
            return redirect("/admin/users");
        }

        $errors = $this->validateUserData($_POST, true);
        if (!empty($errors)) {
            Session::flashErrors($errors);
            Session::flashOldValues($_POST);
            return redirect("/admin/users/edit?id=" . $id);
        }

        try {
            $updateData = [
                "username" => $_POST["username"],
                "email" => $_POST["email"],
                "role" => $_POST["role"],
            ];

            if (!empty($_POST["password"])) {
                $updateData["password"] = $_POST["password"];
            }

            $this->userModel->update($id, $updateData);

            Session::flashError("success", "User updated successfully");
            return redirect("/admin/users");
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error updating user: " . $e->getMessage(),
            );
            return redirect("/admin/users/edit?id=" . $id);
        }
    }

    public function deleteUser()
    {
        $this->requireAdmin();

        $id = (int) ($_POST["id"] ?? 0);
        if (!$id) {
            Session::flashError("error", "User not found");
            return redirect("/admin/users");
        }

        // Prevent deleting self
        if ($id == Session::get("user")["id"]) {
            Session::flashError("error", "You cannot delete your own account");
            return redirect("/admin/users");
        }

        try {
            $this->userModel->delete($id);
            Session::flashError("success", "User deleted successfully");
        } catch (\Exception $e) {
            Session::flashError(
                "error",
                "Error deleting user: " . $e->getMessage(),
            );
        }

        return redirect("/admin/users");
    }

    private function requireAdmin()
    {
        $user = Session::get("user");
        if (!$user) {
            Session::flashError("error", "Please log in to continue");
            redirect("/login");
            exit();
        }

        if ($user["role"] !== "admin") {
            Session::flashError(
                "error",
                "Access denied. Admin privileges required.",
            );
            redirect("/");
            exit();
        }
    }

    private function validateProductData($data)
    {
        $errors = [];

        if (empty($data["name"])) {
            $errors["name"] = "Product name is required";
        }

        if (
            empty($data["price"]) ||
            !is_numeric($data["price"]) ||
            (float) $data["price"] <= 0
        ) {
            $errors["price"] = "Price must be a positive number";
        }

        if (
            !isset($data["quantity_available"]) ||
            !is_numeric($data["quantity_available"]) ||
            (int) $data["quantity_available"] < 0
        ) {
            $errors["quantity_available"] =
                "Quantity must be a non-negative number";
        }

        return $errors;
    }

    private function validateUserData($data, $isUpdate = false)
    {
        $errors = [];

        if (empty($data["username"])) {
            $errors["username"] = "Username is required";
        }

        if (
            empty($data["email"]) ||
            !filter_var($data["email"], FILTER_VALIDATE_EMAIL)
        ) {
            $errors["email"] = "Valid email is required";
        }

        if (!$isUpdate && empty($data["password"])) {
            $errors["password"] = "Password is required";
        }

        if (!empty($data["password"]) && strlen($data["password"]) < 6) {
            $errors["password"] = "Password must be at least 6 characters";
        }

        if (
            empty($data["role"]) ||
            !in_array($data["role"], ["admin", "user"])
        ) {
            $errors["role"] = "Valid role is required";
        }

        return $errors;
    }
}
