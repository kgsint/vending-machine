<?php

namespace App\Controllers\Api;

use App\Models\Product;
use App\Models\Transaction;
use Core\Database;
use Core\JwtAuth;
use Exception;

class ProductsApiController
{
    private $db;
    private $productModel;
    private $transactionModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->productModel = new Product($this->db);
        $this->transactionModel = new Transaction($this->db);
    }

    /**
     * GET /api/products - List all products (Public endpoint)
     */
    public function index()
    {
        try {
            // Get query parameters
            $page = (int) ($_GET["page"] ?? 1);
            $perPage = min((int) ($_GET["per_page"] ?? 10), 100); // Max 100 per page
            $sortBy = $_GET["sort"] ?? "name";
            $order = strtoupper($_GET["order"] ?? "ASC");
            $search = $_GET["search"] ?? "";
            $activeOnly = ($_GET["active_only"] ?? "true") === "true";

            // Validate sort parameters
            $allowedSorts = [
                "name",
                "price",
                "quantity_available",
                "created_at",
            ];
            $allowedOrders = ["ASC", "DESC"];

            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = "name";
            }

            if (!in_array($order, $allowedOrders)) {
                $order = "ASC";
            }

            $limit = $perPage;
            $offset = ($page - 1) * $limit;

            if ($search) {
                $products = $this->productModel->search($search, $limit);
                $totalProducts = count(
                    $this->productModel->search($search, 1000),
                ); // Get total for pagination
            } else {
                $products = $this->productModel->all(
                    $limit,
                    $offset,
                    $sortBy,
                    $order,
                    $activeOnly,
                );
                $totalProducts = $this->productModel->count($activeOnly);
            }

            $totalPages = ceil($totalProducts / $limit);

            $this->jsonResponse([
                "success" => true,
                "data" => [
                    "products" => $products,
                    "pagination" => [
                        "current_page" => $page,
                        "per_page" => $perPage,
                        "total_pages" => $totalPages,
                        "total_items" => $totalProducts,
                    ],
                    "filters" => [
                        "sort_by" => $sortBy,
                        "order" => $order,
                        "search" => $search,
                        "active_only" => $activeOnly,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Error fetching products",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * GET /api/products/{id} - Get product by ID (Public endpoint)
     */
    public function show()
    {
        try {
            $id = (int) ($_GET["id"] ?? 0);

            if (!$id) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product ID is required",
                    ],
                    400,
                );
                return;
            }

            $product = $this->productModel->find($id);

            if (!$product) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product not found",
                    ],
                    404,
                );
                return;
            }

            $this->jsonResponse([
                "success" => true,
                "data" => $product,
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Error fetching product",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * POST /api/products - Create new product (Admin only)
     */
    public function store()
    {
        try {
            // Check authentication and admin role
            if (!$this->requireApiAuth() || !$this->requireApiAdmin()) {
                return;
            }

            $input = $this->getJsonInput();

            // Validate required fields
            $errors = $this->validateProductData($input);
            if (!empty($errors)) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $errors,
                    ],
                    422,
                );
                return;
            }

            $created = $this->productModel->create([
                "name" => $input["name"],
                "price" => (float) $input["price"],
                "quantity_available" => (int) $input["quantity_available"],
                "description" => $input["description"] ?? null,
                "image_url" => $input["image_url"] ?? null,
                "is_active" => $input["is_active"] ?? true,
            ]);

            if ($created) {
                $this->jsonResponse(
                    [
                        "success" => true,
                        "message" => "Product created successfully",
                    ],
                    201,
                );
            } else {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Failed to create product",
                    ],
                    500,
                );
            }
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Error creating product",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * PUT /api/products/{id} - Update product (Admin only)
     */
    public function update()
    {
        try {
            if (!$this->requireApiAuth() || !$this->requireApiAdmin()) {
                return;
            }

            $id = (int) ($_GET["id"] ?? 0);
            $input = $this->getJsonInput();

            if (!$id) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product ID is required",
                    ],
                    400,
                );
                return;
            }

            // Check if product exists
            $product = $this->productModel->find($id);
            if (!$product) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product not found",
                    ],
                    404,
                );
                return;
            }

            $errors = $this->validateProductData($input);
            if (!empty($errors)) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $errors,
                    ],
                    422,
                );
                return;
            }

            $updated = $this->productModel->update($id, [
                "name" => $input["name"],
                "price" => (float) $input["price"],
                "quantity_available" => (int) $input["quantity_available"],
                "description" => $input["description"] ?? null,
                "image_url" => $input["image_url"] ?? null,
                "is_active" => $input["is_active"] ?? true,
            ]);

            if ($updated) {
                $updatedProduct = $this->productModel->find($id);
                $this->jsonResponse([
                    "success" => true,
                    "message" => "Product updated successfully",
                    "data" => $updatedProduct,
                ]);
            } else {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Failed to update product",
                    ],
                    500,
                );
            }
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Error updating product",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * DELETE /api/products/{id} - Delete product (Admin only)
     */
    public function delete()
    {
        try {
            if (!$this->requireApiAuth() || !$this->requireApiAdmin()) {
                return;
            }

            $id = (int) ($_GET["id"] ?? 0);

            if (!$id) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product ID is required",
                    ],
                    400,
                );
                return;
            }

            $product = $this->productModel->find($id);
            if (!$product) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product not found",
                    ],
                    404,
                );
                return;
            }

            $deleted = $this->productModel->delete($id);

            if ($deleted) {
                $this->jsonResponse([
                    "success" => true,
                    "message" => "Product deleted successfully",
                ]);
            } else {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Failed to delete product",
                    ],
                    500,
                );
            }
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Error deleting product",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * POST /api/products/{id}/purchase - Purchase a product (Requires JWT Authentication)
     */
    public function purchase()
    {
        try {
            // Require JWT authentication for purchases
            if (!$this->requireApiAuth()) {
                return;
            }

            $id = (int) ($_GET["id"] ?? 0);
            $input = $this->getJsonInput();

            if (!$id) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Product ID is required",
                    ],
                    400,
                );
                return;
            }

            $quantity = (int) ($input["quantity"] ?? 1);
            if ($quantity <= 0) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Quantity must be positive",
                    ],
                    400,
                );
                return;
            }

            // Get user from JWT token (will be implemented in task 17)
            $userId = $this->getAuthenticatedUserId();

            $result = $this->transactionModel->processPurchase(
                $userId,
                $id,
                $quantity,
            );

            $this->jsonResponse([
                "success" => true,
                "message" => "Purchase completed successfully",
                "data" => $result,
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Purchase failed",
                    "error" => $e->getMessage(),
                ],
                400,
            );
        }
    }

    /**
     * GET /api/users/{userId}/transactions - Get user's transaction history (Requires JWT Authentication)
     */
    public function userTransactions()
    {
        try {
            // Require JWT authentication for transaction history
            if (!$this->requireApiAuth()) {
                return;
            }

            $userId = (int) ($_GET["user_id"] ?? 0);
            $authUserId = $this->getAuthenticatedUserId();

            // Users can only view their own transactions unless they're admin
            if ($userId !== $authUserId && !$this->isApiAdmin()) {
                $this->jsonResponse(
                    [
                        "success" => false,
                        "message" => "Unauthorized access to user transactions",
                    ],
                    403,
                );
                return;
            }

            $limit = min((int) ($_GET["limit"] ?? 20), 100);
            $transactions = $this->transactionModel->getUserTransactions(
                $userId,
                $limit,
            );

            $this->jsonResponse([
                "success" => true,
                "data" => [
                    "transactions" => $transactions,
                    "user_id" => $userId,
                    "limit" => $limit,
                ],
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => "Error fetching transactions",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    // Helper methods

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }

    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents("php://input"), true);
        return $input ?? [];
    }

    private function validateProductData(array $data): array
    {
        $errors = [];

        if (empty($data["name"])) {
            $errors["name"] = "Product name is required";
        }

        if (
            !isset($data["price"]) ||
            !is_numeric($data["price"]) ||
            $data["price"] <= 0
        ) {
            $errors["price"] = "Price must be a positive number";
        }

        if (
            !isset($data["quantity_available"]) ||
            !is_numeric($data["quantity_available"]) ||
            $data["quantity_available"] < 0
        ) {
            $errors["quantity_available"] =
                "Quantity must be a non-negative number";
        }

        return $errors;
    }

    private function requireApiAuth(): bool
    {
        try {
            $user = JwtAuth::requireAuth();
            return true;
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => $e->getMessage(),
                ],
                $e->getCode() ?: 401,
            );
            return false;
        }
    }

    private function requireApiAdmin(): bool
    {
        try {
            $user = JwtAuth::requireAdmin();
            return true;
        } catch (Exception $e) {
            $this->jsonResponse(
                [
                    "success" => false,
                    "message" => $e->getMessage(),
                ],
                $e->getCode() ?: 403,
            );
            return false;
        }
    }

    private function isApiAdmin(): bool
    {
        return JwtAuth::isAdmin();
    }

    private function getAuthenticatedUserId(): int
    {
        $user = JwtAuth::getAuthenticatedUser();
        return (int) ($user["id"] ?? 0);
    }
}
