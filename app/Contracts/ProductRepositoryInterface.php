<?php

namespace App\Contracts;

interface ProductRepositoryInterface
{
    public function all(int $limit = 50, int $offset = 0, string $orderBy = "created_at", string $sortDirection = "DESC", bool $activeOnly = true): array;
    
    public function count(bool $activeOnly = true): int;
    
    public function find(int $id): ?array;
    
    public function create(array $data): bool;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function updateQuantity(int $id, int $newQuantity): bool;
    
    public function decreaseQuantity(int $id, int $amount): bool;
    
    public function hasStock(int $id, int $requiredQuantity): bool;
    
    public function search(string $searchTerm, int $limit = 20): array;
}
