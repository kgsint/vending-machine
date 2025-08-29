<?php

namespace Tests\Mocks;

use App\Contracts\TransactionRepositoryInterface;

class MockTransactionRepository implements TransactionRepositoryInterface
{
    private array $transactions = [];
    private int $nextId = 1;
    private $productRepository;

    public function __construct($productRepository = null)
    {
        $this->productRepository = $productRepository;
    }

    public function all(int $limit = 50, int $offset = 0, string $orderBy = "transaction_date", string $sortDirection = "DESC", ?int $userId = null): array
    {
        $filtered = $userId 
            ? array_filter($this->transactions, fn($t) => $t['user_id'] === $userId)
            : $this->transactions;
        
        $slice = array_slice($filtered, $offset, $limit);
        return array_values($slice);
    }

    public function count(?int $userId = null): int
    {
        if ($userId) {
            return count(array_filter($this->transactions, fn($t) => $t['user_id'] === $userId));
        }
        return count($this->transactions);
    }

    public function find(int $id): ?array
    {
        return $this->transactions[$id] ?? null;
    }

    public function create(array $data): int
    {
        $transaction = array_merge([
            'id' => $this->nextId,
            'transaction_date' => date('Y-m-d H:i:s'),
            'status' => 'completed'
        ], $data);

        $this->transactions[$this->nextId] = $transaction;
        return $this->nextId++;
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!isset($this->transactions[$id])) {
            return false;
        }

        $validStatuses = ['pending', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $this->transactions[$id]['status'] = $status;
        return true;
    }

    public function getUserTransactions(int $userId, int $limit = 20): array
    {
        $userTransactions = array_filter($this->transactions, function($transaction) use ($userId) {
            return $transaction['user_id'] === $userId;
        });

        return array_values(array_slice($userTransactions, 0, $limit));
    }

    public function processPurchase(int $userId, int $productId, int $quantity): array
    {
        // Mock the purchase process
        if ($this->productRepository) {
            $product = $this->productRepository->find($productId);
            if (!$product) {
                throw new \Exception("Product not found");
            }

            if (!$this->productRepository->hasStock($productId, $quantity)) {
                throw new \Exception("Insufficient stock");
            }

            // Calculate total
            $unitPrice = $product['price'];
            $totalPrice = $unitPrice * $quantity;

            // Create transaction
            $transactionId = $this->create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'status' => 'completed'
            ]);

            // Update inventory
            $this->productRepository->decreaseQuantity($productId, $quantity);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'total_price' => $totalPrice
            ];
        }

        // Fallback for testing without product repository
        $transactionId = $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => 1.0,
            'total_price' => $quantity * 1.0,
            'status' => 'completed'
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'total_price' => $quantity * 1.0
        ];
    }

    // Helper methods for testing
    public function getTransaction(int $id): ?array
    {
        return $this->transactions[$id] ?? null;
    }

    public function getAllTransactions(): array
    {
        return $this->transactions;
    }

    public function reset(): void
    {
        $this->transactions = [];
        $this->nextId = 1;
    }
}
