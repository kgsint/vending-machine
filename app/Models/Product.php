<?php

namespace App\Models;

use PDO;
use PDOException;

class Product
{
    private $db;
    
    public int $id;
    public string $name;
    public float $price;
    public int $quantity_available;
    public ?string $description;
    public ?string $image_url;
    public bool $is_active;
    public string $created_at;
    public string $updated_at;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Get all products with optional pagination and sorting
     */
    public function all(int $limit = 50, int $offset = 0, string $orderBy = 'created_at', string $sortDirection = 'DESC', bool $activeOnly = true)
    {
        $activeCondition = $activeOnly ? 'WHERE is_active = 1' : '';
        $query = "SELECT * FROM products $activeCondition ORDER BY $orderBy $sortDirection LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error fetching products: " . $e->getMessage());
        }
    }
    
    /**
     * Get total count of products
     */
    public function count(bool $activeOnly = true): int
    {
        $activeCondition = $activeOnly ? 'WHERE is_active = 1' : '';
        $query = "SELECT COUNT(*) as total FROM products $activeCondition";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            throw new \Exception("Error counting products: " . $e->getMessage());
        }
    }
    
    /**
     * Find a product by ID
     */
    public function find(int $id): ?array
    {
        $query = "SELECT * FROM products WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new \Exception("Error finding product: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new product
     */
    public function create(array $data): bool
    {
        $query = "INSERT INTO products (name, price, quantity_available, description, image_url, is_active) 
                  VALUES (:name, :price, :quantity_available, :description, :image_url, :is_active)";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':quantity_available', $data['quantity_available'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':is_active', $data['is_active'] ?? true, PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error creating product: " . $e->getMessage());
        }
    }
    
    /**
     * Update an existing product
     */
    public function update(int $id, array $data): bool
    {
        $query = "UPDATE products 
                  SET name = :name, price = :price, quantity_available = :quantity_available, 
                      description = :description, image_url = :image_url, is_active = :is_active,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':quantity_available', $data['quantity_available'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error updating product: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a product (soft delete by setting is_active to false)
     */
    public function delete(int $id): bool
    {
        $query = "UPDATE products SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error deleting product: " . $e->getMessage());
        }
    }
    
    /**
     * Hard delete a product (permanently remove from database)
     */
    public function hardDelete(int $id): bool
    {
        $query = "DELETE FROM products WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error hard deleting product: " . $e->getMessage());
        }
    }
    
    /**
     * Update product quantity (for inventory management)
     */
    public function updateQuantity(int $id, int $newQuantity): bool
    {
        if ($newQuantity < 0) {
            throw new \Exception("Quantity cannot be negative");
        }
        
        $query = "UPDATE products SET quantity_available = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $newQuantity, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error updating product quantity: " . $e->getMessage());
        }
    }
    
    /**
     * Decrease product quantity (for purchases)
     */
    public function decreaseQuantity(int $id, int $amount): bool
    {
        if ($amount <= 0) {
            throw new \Exception("Amount must be positive");
        }
        
        // Get current quantity first
        $product = $this->find($id);
        if (!$product) {
            throw new \Exception("Product not found");
        }
        
        $newQuantity = $product['quantity_available'] - $amount;
        if ($newQuantity < 0) {
            throw new \Exception("Insufficient inventory. Available: " . $product['quantity_available'] . ", Requested: " . $amount);
        }
        
        return $this->updateQuantity($id, $newQuantity);
    }
    
    /**
     * Check if product has sufficient quantity for purchase
     */
    public function hasStock(int $id, int $requiredQuantity): bool
    {
        $product = $this->find($id);
        if (!$product || !$product['is_active']) {
            return false;
        }
        
        return $product['quantity_available'] >= $requiredQuantity;
    }
    
    /**
     * Search products by name
     */
    public function search(string $searchTerm, int $limit = 20): array
    {
        $query = "SELECT * FROM products 
                  WHERE is_active = 1 AND name LIKE :search 
                  ORDER BY name ASC 
                  LIMIT :limit";
        
        try {
            $stmt = $this->db->prepare($query);
            $searchPattern = '%' . $searchTerm . '%';
            $stmt->bindParam(':search', $searchPattern);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error searching products: " . $e->getMessage());
        }
    }
}
