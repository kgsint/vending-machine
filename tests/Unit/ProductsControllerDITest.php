<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Controllers\ProductsControllerDI;
use Tests\Mocks\MockProductRepository;
use Tests\Mocks\MockTransactionRepository;

class ProductsControllerDITest extends TestCase
{
    private ProductsControllerDI $controller;
    private MockProductRepository $productRepo;
    private MockTransactionRepository $transactionRepo;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock repositories
        $this->productRepo = new MockProductRepository();
        $this->transactionRepo = new MockTransactionRepository($this->productRepo);
        
        // Create controller with mocked dependencies
        $this->controller = new ProductsControllerDI(
            $this->productRepo,
            $this->transactionRepo
        );
    }

    public function testIndexReturnsProductsSuccessfully(): void
    {
        $_GET = ['page' => 1, 'per_page' => 12, 'sort' => 'name', 'order' => 'ASC'];

        $result = $this->controller->index();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('currentPage', $result);
        $this->assertArrayHasKey('totalPages', $result);
        $this->assertEquals(1, $result['currentPage']);
        $this->assertNotEmpty($result['products']);
    }

    public function testIndexWithSearchReturnsFilteredResults(): void
    {
        $_GET = ['search' => 'Coke'];

        $result = $this->controller->index();

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result['products']));
        $this->assertEquals('Test Coke', $result['products'][0]['name']);
    }

    public function testIndexValidatesSortParameters(): void
    {
        $_GET = ['sort' => 'invalid_field', 'order' => 'INVALID'];

        $result = $this->controller->index();

        $this->assertEquals('name', $result['sortBy']);
        $this->assertEquals('ASC', $result['order']);
    }

    public function testIndexValidatesPerPageParameter(): void
    {
        $_GET = ['per_page' => 999]; // Invalid per_page

        $result = $this->controller->index();

        $this->assertEquals(12, $result['perPage']); // Should default to 12
    }

    public function testShowReturnsProductWhenExists(): void
    {
        $product = $this->controller->show(1);

        $this->assertNotNull($product);
        $this->assertEquals('Test Coke', $product['name']);
        $this->assertEquals(3.999, $product['price']);
    }

    public function testShowReturnsNullForInvalidId(): void
    {
        $product = $this->controller->show(0);
        $this->assertNull($product);

        $product = $this->controller->show(999);
        $this->assertNull($product);
    }

    public function testShowReturnsNullForInactiveProduct(): void
    {
        // Make product inactive
        $this->productRepo->update(1, ['is_active' => 0]);

        $product = $this->controller->show(1);

        $this->assertNull($product);
    }

    public function testCanPurchaseReturnsTrueWhenStockAvailable(): void
    {
        $canPurchase = $this->controller->canPurchase(1, 5); // Request 5, available 10

        $this->assertTrue($canPurchase);
    }

    public function testCanPurchaseReturnsFalseWhenInsufficientStock(): void
    {
        $canPurchase = $this->controller->canPurchase(1, 15); // Request 15, available 10

        $this->assertFalse($canPurchase);
    }

    public function testCanPurchaseReturnsFalseWhenProductOutOfStock(): void
    {
        $canPurchase = $this->controller->canPurchase(3, 1); // Product 3 has 0 quantity

        $this->assertFalse($canPurchase);
    }

    public function testProcessPurchaseSucceedsWithValidData(): void
    {
        $initialQuantity = $this->productRepo->find(1)['quantity_available'];

        $result = $this->controller->processPurchase(1, 1, 2); // User 1, Product 1, Quantity 2

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertArrayHasKey('total_price', $result);

        // Verify inventory was updated
        $updatedProduct = $this->productRepo->find(1);
        $this->assertEquals($initialQuantity - 2, $updatedProduct['quantity_available']);

        // Verify transaction was created
        $transaction = $this->transactionRepo->find($result['transaction_id']);
        $this->assertNotNull($transaction);
        $this->assertEquals(1, $transaction['user_id']);
        $this->assertEquals(1, $transaction['product_id']);
        $this->assertEquals(2, $transaction['quantity']);
    }

    public function testProcessPurchaseThrowsExceptionWithInvalidProductId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid purchase data');

        $this->controller->processPurchase(1, 0, 1);
    }

    public function testProcessPurchaseThrowsExceptionWithInvalidQuantity(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid purchase data');

        $this->controller->processPurchase(1, 1, 0);
    }

    public function testProcessPurchaseThrowsExceptionWhenProductNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found');

        $this->controller->processPurchase(1, 999, 1);
    }

    public function testProcessPurchaseThrowsExceptionWhenInsufficientStock(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->controller->processPurchase(1, 1, 20); // Request more than available
    }

    public function testGetUserTransactionHistoryReturnsUserTransactions(): void
    {
        // Create some test transactions
        $this->transactionRepo->create([
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999
        ]);

        $this->transactionRepo->create([
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 3.999,
            'total_price' => 7.998
        ]);

        $userTransactions = $this->controller->getUserTransactionHistory(1);

        $this->assertIsArray($userTransactions);
        $this->assertEquals(1, count($userTransactions));
        $this->assertEquals(1, $userTransactions[0]['user_id']);
    }

    public function testGetUserTransactionHistoryReturnsEmptyForUserWithNoTransactions(): void
    {
        $userTransactions = $this->controller->getUserTransactionHistory(999);

        $this->assertIsArray($userTransactions);
        $this->assertEmpty($userTransactions);
    }

    // Edge case tests

    public function testIndexWithLargePageNumber(): void
    {
        $_GET = ['page' => 1000];

        $result = $this->controller->index();

        $this->assertIsArray($result);
        // Should handle gracefully and return empty products
        $this->assertArrayHasKey('products', $result);
    }

    public function testProcessPurchaseCalculatesCorrectTotalPrice(): void
    {
        $result = $this->controller->processPurchase(1, 2, 3); // Product 2 costs 6.885, quantity 3

        $expectedTotal = 6.885 * 3;
        $this->assertEquals($expectedTotal, $result['total_price']);
    }

    public function testControllerHandlesRepositoryExceptions(): void
    {
        // Create a mock that throws an exception
        $mockProductRepo = new class implements \App\Contracts\ProductRepositoryInterface {
            public function all(int $limit = 50, int $offset = 0, string $orderBy = "created_at", string $sortDirection = "DESC", bool $activeOnly = true): array
            {
                throw new \Exception("Database error");
            }
            
            public function count(bool $activeOnly = true): int { return 0; }
            public function find(int $id): ?array { return null; }
            public function create(array $data): bool { return false; }
            public function update(int $id, array $data): bool { return false; }
            public function delete(int $id): bool { return false; }
            public function updateQuantity(int $id, int $newQuantity): bool { return false; }
            public function decreaseQuantity(int $id, int $amount): bool { return false; }
            public function hasStock(int $id, int $requiredQuantity): bool { return false; }
            public function search(string $searchTerm, int $limit = 20): array { return []; }
        };

        $controller = new ProductsControllerDI($mockProductRepo, $this->transactionRepo);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $controller->index();
    }
}
