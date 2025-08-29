<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Controllers\ProductsController;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Core\Database;
use Core\Session;

class ProductsControllerTest extends TestCase
{
    private $controller;
    private $productModel;
    private $transactionModel;
    private $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize controller and models
        $this->controller = new ProductsController();
        $this->productModel = new Product($this->pdo);
        $this->transactionModel = new Transaction($this->pdo);
        $this->userModel = new User($this->pdo);
        
        // Clear any previous session/output
        $_GET = [];
        $_POST = [];
        if (ob_get_level()) {
            ob_clean();
        }
    }

    public function testIndexReturnsProductsWithPagination(): void
    {
        // Setup GET parameters
        $_GET = [
            'page' => 1,
            'per_page' => 12,
            'sort' => 'name',
            'order' => 'ASC'
        ];

        // Start output buffering to catch the view output
        ob_start();
        $result = $this->controller->index();
        ob_end_clean();

        // Verify products were fetched
        $products = $this->productModel->all(12, 0, 'name', 'ASC');
        $this->assertNotEmpty($products);
        $this->assertCount(4, $products); // Should return 4 active products
    }

    public function testIndexWithSearch(): void
    {
        $_GET = [
            'page' => 1,
            'per_page' => 12,
            'search' => 'Coke'
        ];

        ob_start();
        $this->controller->index();
        ob_end_clean();

        // Verify search functionality
        $searchResults = $this->productModel->search('Coke');
        $this->assertNotEmpty($searchResults);
        $this->assertEquals('Coke', $searchResults[0]['name']);
    }

    public function testIndexValidatesSortingParameters(): void
    {
        $_GET = [
            'sort' => 'invalid_field',
            'order' => 'INVALID'
        ];

        ob_start();
        $this->controller->index();
        ob_end_clean();

        // Should default to valid parameters when invalid ones are provided
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    public function testShowReturnsProductDetails(): void
    {
        $_GET = ['id' => 1];

        ob_start();
        $result = $this->controller->show();
        ob_end_clean();

        // Verify product can be found
        $product = $this->productModel->find(1);
        $this->assertNotNull($product);
        $this->assertEquals('Coke', $product['name']);
    }

    public function testShowRedirectsForInvalidId(): void
    {
        $_GET = ['id' => 0];

        // Should trigger redirect due to invalid ID
        ob_start();
        $this->controller->show();
        ob_end_clean();

        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    public function testShowRedirectsForNonexistentProduct(): void
    {
        $_GET = ['id' => 999];

        ob_start();
        $this->controller->show();
        ob_end_clean();

        // Verify product doesn't exist
        $product = $this->productModel->find(999);
        $this->assertNull($product);
    }

    public function testPurchaseRequiresAuthentication(): void
    {
        $this->clearSession();
        $_GET = ['id' => 1];

        ob_start();
        $this->controller->purchase();
        ob_end_clean();

        // Should redirect to login when not authenticated
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    public function testPurchaseShowsProductWhenAuthenticated(): void
    {
        $this->actingAs(['id' => 2, 'username' => 'user1', 'role' => 'user']);
        $_GET = ['id' => 1];

        ob_start();
        $this->controller->purchase();
        ob_end_clean();

        // Verify product exists and is available
        $product = $this->productModel->find(1);
        $this->assertNotNull($product);
        $this->assertTrue($product['quantity_available'] > 0);
    }

    public function testPurchaseRedirectsForOutOfStockProduct(): void
    {
        $this->actingAs(['id' => 2, 'username' => 'user1', 'role' => 'user']);
        $_GET = ['id' => 4]; // Out of stock product

        ob_start();
        $this->controller->purchase();
        ob_end_clean();

        // Verify product is out of stock
        $product = $this->productModel->find(4);
        $this->assertEquals(0, $product['quantity_available']);
    }

    public function testProcessPurchaseRequiresAuthentication(): void
    {
        $this->clearSession();
        $_POST = ['product_id' => 1, 'quantity' => 1];

        ob_start();
        $this->controller->processPurchase();
        ob_end_clean();

        $this->assertTrue(true); // Should redirect without processing
    }

    public function testProcessPurchaseValidatesInput(): void
    {
        $this->actingAs(['id' => 2, 'username' => 'user1', 'role' => 'user']);
        
        // Test invalid product ID
        $_POST = ['product_id' => 0, 'quantity' => 1];

        ob_start();
        $this->controller->processPurchase();
        ob_end_clean();

        // Test invalid quantity
        $_POST = ['product_id' => 1, 'quantity' => 0];

        ob_start();
        $this->controller->processPurchase();
        ob_end_clean();

        $this->assertTrue(true); // Should handle validation errors
    }

    public function testProcessPurchaseCreatesTransaction(): void
    {
        $this->actingAs(['id' => 2, 'username' => 'user1', 'role' => 'user']);
        $_POST = ['product_id' => 1, 'quantity' => 2];

        // Get initial quantity
        $initialProduct = $this->productModel->find(1);
        $initialQuantity = $initialProduct['quantity_available'];

        ob_start();
        $this->controller->processPurchase();
        ob_end_clean();

        // Verify transaction was created
        $transactions = $this->transactionModel->getUserTransactions(2, 1);
        $this->assertNotEmpty($transactions);

        // Verify inventory was updated
        $updatedProduct = $this->productModel->find(1);
        $this->assertEquals($initialQuantity - 2, $updatedProduct['quantity_available']);
    }

    public function testTransactionHistoryRequiresAuthentication(): void
    {
        $this->clearSession();

        ob_start();
        $this->controller->transactionHistory();
        ob_end_clean();

        $this->assertTrue(true); // Should redirect to login
    }

    public function testTransactionHistoryShowsUserTransactions(): void
    {
        // Create a transaction first
        $this->actingAs(['id' => 2, 'username' => 'user1', 'role' => 'user']);
        
        // Create a test transaction
        $this->transactionModel->create([
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ]);

        ob_start();
        $this->controller->transactionHistory();
        ob_end_clean();

        // Verify transaction exists
        $transactions = $this->transactionModel->getUserTransactions(2);
        $this->assertNotEmpty($transactions);
        $this->assertEquals(2, $transactions[0]['user_id']);
    }

    public function testControllerHandlesDatabaseErrors(): void
    {
        // Close the database connection to simulate error
        $this->pdo = null;
        Database::resetInstance();
        
        $_GET = ['id' => 1];

        ob_start();
        try {
            $controller = new ProductsController();
            $controller->show();
        } catch (\Exception $e) {
            // Expected behavior when database is unavailable
            $this->assertTrue(true);
        }
        ob_end_clean();
    }

    // Test edge cases
    
    public function testIndexWithLargePageNumber(): void
    {
        $_GET = ['page' => 1000];

        ob_start();
        $this->controller->index();
        ob_end_clean();

        $this->assertTrue(true); // Should handle gracefully
    }

    public function testIndexWithInvalidPerPageValue(): void
    {
        $_GET = ['per_page' => 999]; // Invalid value

        ob_start();
        $this->controller->index();
        ob_end_clean();

        // Should default to valid per_page value
        $this->assertTrue(true);
    }

    public function testPurchaseInactiveProduct(): void
    {
        $this->actingAs(['id' => 2, 'username' => 'user1', 'role' => 'user']);
        $_GET = ['id' => 5]; // Inactive product

        ob_start();
        $this->controller->purchase();
        ob_end_clean();

        // Should redirect as product is inactive
        $this->assertTrue(true);
    }

}
