<?php

namespace App\Contracts;

interface TransactionRepositoryInterface
{
    public function all(int $limit = 50, int $offset = 0, string $orderBy = "transaction_date", string $sortDirection = "DESC", ?int $userId = null): array;
    
    public function count(?int $userId = null): int;
    
    public function find(int $id): ?array;
    
    public function create(array $data): int;
    
    public function updateStatus(int $id, string $status): bool;
    
    public function getUserTransactions(int $userId, int $limit = 20): array;
    
    public function processPurchase(int $userId, int $productId, int $quantity): array;
}
