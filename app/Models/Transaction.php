<?php

namespace App\Models;

use PDO;
use PDOException;

class Transaction
{
    private $db;

    public int $id;
    public int $user_id;
    public int $product_id;
    public int $quantity;
    public float $unit_price;
    public float $total_price;
    public string $transaction_date;
    public string $status;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all(
        int $limit = 50,
        int $offset = 0,
        string $orderBy = "transaction_date",
        string $sortDirection = "DESC",
        ?int $userId = null,
    ): array {
        $userCondition = $userId ? "WHERE user_id = :user_id" : "";
        $query = "SELECT t.*, u.username, u.email, p.name as product_name
                  FROM transactions t
                  JOIN users u ON t.user_id = u.id
                  JOIN products p ON t.product_id = p.id
                  $userCondition
                  ORDER BY t.$orderBy $sortDirection
                  LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($query);
            if ($userId) {
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            }
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception(
                "Error fetching transactions: " . $e->getMessage(),
            );
        }
    }

    public function count(?int $userId = null): int
    {
        $userCondition = $userId ? "WHERE user_id = :user_id" : "";
        $query = "SELECT COUNT(*) as total FROM transactions $userCondition";

        try {
            $stmt = $this->db->prepare($query);
            if ($userId) {
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) $result["total"];
        } catch (PDOException $e) {
            throw new \Exception(
                "Error counting transactions: " . $e->getMessage(),
            );
        }
    }

    public function find(int $id): ?array
    {
        $query = "SELECT t.*, u.username, u.email, p.name as product_name
                  FROM transactions t
                  JOIN users u ON t.user_id = u.id
                  JOIN products p ON t.product_id = p.id
                  WHERE t.id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new \Exception(
                "Error finding transaction: " . $e->getMessage(),
            );
        }
    }

    public function create(array $data): int
    {
        $query = "INSERT INTO transactions (user_id, product_id, quantity, unit_price, total_price, status)
                  VALUES (:user_id, :product_id, :quantity, :unit_price, :total_price, :status)";

        try {
            $stmt = $this->db->prepare($query);

            $userId = $data["user_id"];
            $productId = $data["product_id"];
            $quantity = $data["quantity"];
            $unitPrice = $data["unit_price"];
            $totalPrice = $data["total_price"];
            $status = $data["status"] ?? "completed";

            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
            $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
            $stmt->bindParam(":unit_price", $unitPrice);
            $stmt->bindParam(":total_price", $totalPrice);
            $stmt->bindParam(":status", $status);

            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception(
                "Error creating transaction: " . $e->getMessage(),
            );
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ["pending", "completed", "cancelled"];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception(
                "Invalid status. Must be one of: " .
                    implode(", ", $validStatuses),
            );
        }

        $query = "UPDATE transactions SET status = :status WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":status", $status);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception(
                "Error updating transaction status: " . $e->getMessage(),
            );
        }
    }

    /**
     * Get user's transaction history
     */
    public function getUserTransactions(int $userId, int $limit = 20): array
    {
        $query = "SELECT t.*, p.name as product_name, p.image_url
                  FROM transactions t
                  JOIN products p ON t.product_id = p.id
                  WHERE t.user_id = :user_id
                  ORDER BY t.transaction_date DESC
                  LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception(
                "Error fetching user transactions: " . $e->getMessage(),
            );
        }
    }

    /**
     * Get product sales statistics
     */
    public function getProductSales(int $productId): array
    {
        $query = "SELECT
                    COUNT(*) as total_transactions,
                    SUM(quantity) as total_quantity_sold,
                    SUM(total_price) as total_revenue,
                    AVG(unit_price) as average_unit_price
                  FROM transactions
                  WHERE product_id = :product_id AND status = 'completed'";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            throw new \Exception(
                "Error fetching product sales: " . $e->getMessage(),
            );
        }
    }

    /**
     * Get sales statistics for a date range
     */
    public function getSalesStats(string $startDate, string $endDate): array
    {
        $query = "SELECT
                    COUNT(*) as total_transactions,
                    SUM(quantity) as total_items_sold,
                    SUM(total_price) as total_revenue,
                    COUNT(DISTINCT user_id) as unique_customers,
                    COUNT(DISTINCT product_id) as products_sold
                  FROM transactions
                  WHERE status = 'completed'
                    AND DATE(transaction_date) >= :start_date
                    AND DATE(transaction_date) <= :end_date";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":start_date", $startDate);
            $stmt->bindParam(":end_date", $endDate);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            throw new \Exception(
                "Error fetching sales statistics: " . $e->getMessage(),
            );
        }
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts(
        int $limit = 10,
        string $period = "all",
    ): array {
        $dateCondition = "";
        if ($period === "today") {
            $dateCondition = "AND DATE(transaction_date) = CURDATE()";
        } elseif ($period === "week") {
            $dateCondition =
                "AND transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } elseif ($period === "month") {
            $dateCondition =
                "AND transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }

        $query = "SELECT
                    p.id, p.name, p.price,
                    SUM(t.quantity) as total_sold,
                    SUM(t.total_price) as total_revenue,
                    COUNT(t.id) as transaction_count
                  FROM transactions t
                  JOIN products p ON t.product_id = p.id
                  WHERE t.status = 'completed' $dateCondition
                  GROUP BY p.id, p.name, p.price
                  ORDER BY total_sold DESC
                  LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception(
                "Error fetching top selling products: " . $e->getMessage(),
            );
        }
    }

    /**
     * Process a purchase transaction
     */
    public function processPurchase(
        int $userId,
        int $productId,
        int $quantity,
    ): array {
        // Validate input parameters
        if ($userId <= 0 || $productId <= 0 || $quantity <= 0) {
            throw new \Exception("Invalid purchase data");
        }

        try {
            // Start transaction
            $this->db->beginTransaction();

            // Get product details
            $productQuery =
                "SELECT * FROM products WHERE id = :id FOR UPDATE";
            $stmt = $this->db->prepare($productQuery);
            $stmt->bindParam(":id", $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new \Exception("Product not found");
            }

            if (!$product["is_active"]) {
                throw new \Exception("Product not available");
            }

            if ($product["quantity_available"] < $quantity) {
                throw new \Exception("Insufficient stock");
            }

            // Calculate prices
            $unitPrice = $product["price"];
            $totalPrice = $unitPrice * $quantity;

            // Create transaction record
            $transactionData = [
                "user_id" => $userId,
                "product_id" => $productId,
                "quantity" => $quantity,
                "unit_price" => $unitPrice,
                "total_price" => $totalPrice,
                "status" => "completed",
            ];

            $transactionId = $this->create($transactionData);

            // Update product quantity
            $newQuantity = $product["quantity_available"] - $quantity;
            $updateQuery =
                "UPDATE products SET quantity_available = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bindParam(":quantity", $newQuantity, PDO::PARAM_INT);
            $stmt->bindParam(":id", $productId, PDO::PARAM_INT);
            $stmt->execute();

            // Commit transaction
            $this->db->commit();

            return [
                "success" => true,
                "transaction_id" => $transactionId,
                "total_price" => $totalPrice,
                "remaining_stock" => $newQuantity,
            ];
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            throw $e;
        }
    }
}
