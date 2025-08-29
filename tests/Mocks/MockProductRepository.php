<?php

namespace Tests\Mocks;

use App\Contracts\ProductRepositoryInterface;

class MockProductRepository implements ProductRepositoryInterface
{
    private array $products = [];
    private int $nextId = 1;

    public function __construct()
    {
        // Initialize with test data
        $this->products = [
            1 => [
                'id' => 1,
                'name' => 'Test Coke',
                'price' => 3.999,
                'quantity_available' => 10,
                'description' => 'Test Coca-Cola',
                'image_url' => null,
                'is_active' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00'
            ],
            2 => [
                'id' => 2,
                'name' => 'Test Pepsi',
                'price' => 6.885,
                'quantity_available' => 5,
                'description' => 'Test Pepsi Cola',
                'image_url' => null,
                'is_active' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00'
            ],
            3 => [
                'id' => 3,
                'name' => 'Test Water',
                'price' => 0.5,
                'quantity_available' => 0,
                'description' => 'Out of stock',
                'image_url' => null,
                'is_active' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00'
            ]
        ];
        $this->nextId = 4;
    }

    public function all(int $limit = 50, int $offset = 0, string $orderBy = "created_at", string $sortDirection = "DESC", bool $activeOnly = true): array
    {
        $filtered = $activeOnly 
            ? array_filter($this->products, fn($p) => $p['is_active'])
            : $this->products;
        
        // Simple pagination simulation
        $slice = array_slice($filtered, $offset, $limit);
        return array_values($slice);
    }

    public function count(bool $activeOnly = true): int
    {
        if ($activeOnly) {
            return count(array_filter($this->products, fn($p) => $p['is_active']));
        }
        return count($this->products);
    }

    public function find(int $id): ?array
    {
        return $this->products[$id] ?? null;
    }

    public function create(array $data): bool
    {
        $product = array_merge([
            'id' => $this->nextId++,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ], $data);

        $this->products[$product['id']] = $product;
        return true;
    }

    public function update(int $id, array $data): bool
    {
        if (!isset($this->products[$id])) {
            return false;
        }

        $this->products[$id] = array_merge($this->products[$id], $data);
        $this->products[$id]['updated_at'] = date('Y-m-d H:i:s');
        return true;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->products[$id])) {
            return false;
        }

        $this->products[$id]['is_active'] = 0;
        $this->products[$id]['updated_at'] = date('Y-m-d H:i:s');
        return true;
    }

    public function updateQuantity(int $id, int $newQuantity): bool
    {
        if (!isset($this->products[$id]) || $newQuantity < 0) {
            return false;
        }

        $this->products[$id]['quantity_available'] = $newQuantity;
        $this->products[$id]['updated_at'] = date('Y-m-d H:i:s');
        return true;
    }

    public function decreaseQuantity(int $id, int $amount): bool
    {
        if (!isset($this->products[$id]) || $amount <= 0) {
            return false;
        }

        $currentQuantity = $this->products[$id]['quantity_available'];
        if ($currentQuantity < $amount) {
            throw new \Exception("Insufficient inventory");
        }

        return $this->updateQuantity($id, $currentQuantity - $amount);
    }

    public function hasStock(int $id, int $requiredQuantity): bool
    {
        $product = $this->find($id);
        return $product && 
               $product['is_active'] && 
               $product['quantity_available'] >= $requiredQuantity;
    }

    public function search(string $searchTerm, int $limit = 20): array
    {
        $results = array_filter($this->products, function($product) use ($searchTerm) {
            return $product['is_active'] && 
                   stripos($product['name'], $searchTerm) !== false;
        });

        return array_values(array_slice($results, 0, $limit));
    }

    // Helper methods for testing
    public function getProduct(int $id): ?array
    {
        return $this->products[$id] ?? null;
    }

    public function setProduct(int $id, array $product): void
    {
        $this->products[$id] = $product;
    }

    public function reset(): void
    {
        $this->__construct();
    }
}
