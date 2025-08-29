<?php

namespace App\Controllers;

use App\Contracts\ProductRepositoryInterface;
use App\Contracts\TransactionRepositoryInterface;
use Core\Session;
use Core\View;

class ProductsControllerDI
{
    private ProductRepositoryInterface $productRepository;
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(): array
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 12);
        
        // Validate per-page options
        $allowedPerPage = [6, 12, 24, 48];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 12;
        }
        
        $limit = $perPage;
        $offset = ($page - 1) * $limit;

        $sortBy = $_GET["sort"] ?? "name";
        $order = $_GET["order"] ?? "ASC";
        $search = $_GET["search"] ?? "";

        // Validate sorting parameters
        $allowedSortFields = [
            "name",
            "price", 
            "quantity_available",
            "created_at",
        ];
        $allowedOrders = ["ASC", "DESC"];

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = "name";
        }

        if (!in_array($order, $allowedOrders)) {
            $order = "ASC";
        }

        try {
            if ($search) {
                $products = $this->productRepository->search($search, $limit);
                $totalProducts = count($products);
            } else {
                $products = $this->productRepository->all(
                    $limit,
                    $offset,
                    $sortBy,
                    $order
                );
                $totalProducts = $this->productRepository->count();
            }

            $totalPages = ceil($totalProducts / $limit);

            return [
                "products" => $products,
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "sortBy" => $sortBy,
                "order" => $order,
                "search" => $search,
                "totalProducts" => $totalProducts,
                "perPage" => $perPage,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function show(int $id): ?array
    {
        if (!$id) {
            return null;
        }

        try {
            $product = $this->productRepository->find($id);

            if (!$product || !$product["is_active"]) {
                return null;
            }

            return $product;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function canPurchase(int $productId, int $quantity): bool
    {
        try {
            return $this->productRepository->hasStock($productId, $quantity);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function processPurchase(int $userId, int $productId, int $quantity): array
    {
        if (!$productId || $quantity <= 0) {
            throw new \Exception("Invalid purchase data");
        }

        try {
            return $this->transactionRepository->processPurchase(
                $userId,
                $productId,
                $quantity
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getUserTransactionHistory(int $userId): array
    {
        try {
            return $this->transactionRepository->getUserTransactions($userId);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
