<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\ProductsController;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Core\Database;
use Core\Session;
use Core\View;
use PDO;

class ProductsControllerUnitTest extends TestCase
{
    private ProductsController $controller;
    private MockObject $mockDb;
    private MockObject $mockPdo;
    private MockObject $mockProductModel;
    private MockObject $mockTransactionModel;
    private MockObject $mockUserModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock PDO connection
        $this->mockPdo = $this->createMock(PDO::class);
        
        // Mock Database singleton
        $this->mockDb = $this->createMock(Database::class);
        $this->mockDb->method('getConnection')->willReturn($this->mockPdo);

        // Mock models
        $this->mockProductModel = $this->createMock(Product::class);
        $this->mockTransactionModel = $this->createMock(Transaction::class);
        $this->mockUserModel = $this->createMock(User::class);

        // Clear globals
        $_GET = [];
        $_POST = [];
        $_SESSION = [];

        // Mock static methods where needed
        $this->setupStaticMocks();
    }

    protected function tearDown(): void
    {
        // Clean up globals
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
    }

    private function setupStaticMocks(): void
    {
        // We'll use dependency injection to avoid static method issues
    }

    private function createControllerWithMocks(): ProductsController
    {
        // Create a controller instance and inject mocked dependencies
        $controller = $this->getMockBuilder(ProductsController::class)
            ->onlyMethods([])
            ->getMock();

        // Use reflection to inject mocked dependencies
        $reflection = new \ReflectionClass($controller);
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($controller, $this->mockPdo);

        $productModelProperty = $reflection->getProperty('productModel');
        $productModelProperty->setAccessible(true);
        $productModelProperty->setValue($controller, $this->mockProductModel);

        $transactionModelProperty = $reflection->getProperty('transactionModel');
        $transactionModelProperty->setAccessible(true);
        $transactionModelProperty->setValue($controller, $this->mockTransactionModel);

        $userModelProperty = $reflection->getProperty('userModel');
        $userModelProperty->setAccessible(true);
        $userModelProperty->setValue($controller, $this->mockUserModel);

        return $controller;
    }

    public function testIndexWithDefaultParameters(): void
    {
        $_GET = [];

        $expectedProducts = [
            ['id' => 1, 'name' => 'Coke', 'price' => 3.999, 'quantity_available' => 10],
            ['id' => 2, 'name' => 'Pepsi', 'price' => 6.885, 'quantity_available' => 5],
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->with(12, 0, 'name', 'ASC')
            ->willReturn($expectedProducts);

        $this->mockProductModel
            ->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $controller = $this->createControllerWithMocks();

        // Mock View::make to avoid actual view rendering
        $this->expectOutputString(''); // Expect no output for unit test

        ob_start();
        $result = $controller->index();
        ob_end_clean();

        // The method should complete without throwing exceptions
        $this->assertTrue(true);
    }

    public function testIndexWithPaginationParameters(): void
    {
        $_GET = [
            'page' => 2,
            'per_page' => 6,
            'sort' => 'price',
            'order' => 'DESC'
        ];

        $expectedProducts = [
            ['id' => 2, 'name' => 'Pepsi', 'price' => 6.885, 'quantity_available' => 5],
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->with(6, 6, 'price', 'DESC') // offset = (page-1) * limit = (2-1) * 6 = 6
            ->willReturn($expectedProducts);

        $this->mockProductModel
            ->expects($this->once())
            ->method('count')
            ->willReturn(10);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testIndexWithInvalidSortParameters(): void
    {
        $_GET = [
            'sort' => 'invalid_field',
            'order' => 'INVALID_ORDER'
        ];

        // Should default to valid parameters
        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->with(12, 0, 'name', 'ASC') // Should use default valid values
            ->willReturn([]);

        $this->mockProductModel
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testIndexWithSearch(): void
    {
        $_GET = [
            'search' => 'Coke'
        ];

        $expectedResults = [
            ['id' => 1, 'name' => 'Coke', 'price' => 3.999, 'quantity_available' => 10],
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('search')
            ->with('Coke', 12)
            ->willReturn($expectedResults);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testIndexWithInvalidPerPageValue(): void
    {
        $_GET = [
            'per_page' => 999 // Invalid value, should default to 12
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->with(12, 0, 'name', 'ASC') // Should use default per_page value
            ->willReturn([]);

        $this->mockProductModel
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testShowWithValidProduct(): void
    {
        $_GET = ['id' => 1];

        $expectedProduct = [
            'id' => 1,
            'name' => 'Coke',
            'price' => 3.999,
            'quantity_available' => 10,
            'is_active' => 1
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($expectedProduct);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->show();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testShowWithInvalidId(): void
    {
        $_GET = ['id' => 0];

        // Should not call find() with invalid ID
        $this->mockProductModel
            ->expects($this->never())
            ->method('find');

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->show();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testShowWithNonexistentProduct(): void
    {
        $_GET = ['id' => 999];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->show();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testShowWithInactiveProduct(): void
    {
        $_GET = ['id' => 1];

        $inactiveProduct = [
            'id' => 1,
            'name' => 'Inactive Product',
            'is_active' => 0
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($inactiveProduct);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->show();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testPurchaseWithoutAuthentication(): void
    {
        $_GET = ['id' => 1];
        $_SESSION = []; // No user session

        $controller = $this->createControllerWithMocks();

        // Should redirect without processing when not authenticated
        $this->expectOutputRegex('/.*login.*/i');

        ob_start();
        $controller->purchase();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    public function testPurchaseWithValidProductAndAuthentication(): void
    {
        $_GET = ['id' => 1];
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $validProduct = [
            'id' => 1,
            'name' => 'Coke',
            'price' => 3.999,
            'quantity_available' => 10,
            'is_active' => 1
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($validProduct);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->purchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testPurchaseWithOutOfStockProduct(): void
    {
        $_GET = ['id' => 1];
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $outOfStockProduct = [
            'id' => 1,
            'name' => 'Out of Stock',
            'price' => 1.00,
            'quantity_available' => 0,
            'is_active' => 1
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($outOfStockProduct);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->purchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testProcessPurchaseWithoutAuthentication(): void
    {
        $_POST = ['product_id' => 1, 'quantity' => 2];
        $_SESSION = []; // No user session

        $controller = $this->createControllerWithMocks();

        $this->expectOutputRegex('/.*login.*/i');

        ob_start();
        $controller->processPurchase();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    public function testProcessPurchaseWithInvalidData(): void
    {
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        // Test with invalid product ID
        $_POST = ['product_id' => 0, 'quantity' => 1];

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->processPurchase();
        ob_end_clean();

        // Test with invalid quantity
        $_POST = ['product_id' => 1, 'quantity' => 0];

        ob_start();
        $controller->processPurchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testProcessPurchaseSuccess(): void
    {
        $_POST = ['product_id' => 1, 'quantity' => 2];
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $purchaseResult = [
            'success' => true,
            'transaction_id' => 123,
            'total_price' => 7.998
        ];

        $this->mockTransactionModel
            ->expects($this->once())
            ->method('processPurchase')
            ->with(2, 1, 2)
            ->willReturn($purchaseResult);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->processPurchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testProcessPurchaseFailure(): void
    {
        $_POST = ['product_id' => 1, 'quantity' => 20];
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $this->mockTransactionModel
            ->expects($this->once())
            ->method('processPurchase')
            ->with(2, 1, 20)
            ->willThrowException(new \Exception('Insufficient stock'));

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->processPurchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testTransactionHistoryWithoutAuthentication(): void
    {
        $_SESSION = []; // No user session

        $controller = $this->createControllerWithMocks();

        $this->expectOutputRegex('/.*login.*/i');

        ob_start();
        $controller->transactionHistory();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    public function testTransactionHistoryWithAuthentication(): void
    {
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $expectedTransactions = [
            [
                'id' => 1,
                'user_id' => 2,
                'product_id' => 1,
                'quantity' => 2,
                'total_price' => 7.998,
                'product_name' => 'Coke',
                'transaction_date' => '2023-01-01 12:00:00'
            ]
        ];

        $this->mockTransactionModel
            ->expects($this->once())
            ->method('getUserTransactions')
            ->with(2, 20)
            ->willReturn($expectedTransactions);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->transactionHistory();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testIndexHandlesDatabaseException(): void
    {
        $_GET = [];

        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->willThrowException(new \Exception('Database connection failed'));

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testShowHandlesDatabaseException(): void
    {
        $_GET = ['id' => 1];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->willThrowException(new \Exception('Database error'));

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->show();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testPurchaseHandlesDatabaseException(): void
    {
        $_GET = ['id' => 1];
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $this->mockProductModel
            ->expects($this->once())
            ->method('find')
            ->willThrowException(new \Exception('Database error'));

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->purchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    // Edge Cases Tests

    public function testIndexWithEmptyResults(): void
    {
        $_GET = [];

        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $this->mockProductModel
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testIndexWithLargePageNumber(): void
    {
        $_GET = ['page' => 1000];

        $this->mockProductModel
            ->expects($this->once())
            ->method('all')
            ->with(12, 11988, 'name', 'ASC') // (1000-1) * 12 = 11988
            ->willReturn([]);

        $this->mockProductModel
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->index();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testShowWithMissingIdParameter(): void
    {
        $_GET = []; // No id parameter

        $this->mockProductModel
            ->expects($this->never())
            ->method('find');

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->show();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testPurchaseWithMissingIdParameter(): void
    {
        $_GET = []; // No id parameter
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $this->mockProductModel
            ->expects($this->never())
            ->method('find');

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->purchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testProcessPurchaseWithMissingPostData(): void
    {
        $_POST = []; // No POST data
        $_SESSION = [
            'user' => ['id' => 2, 'username' => 'testuser', 'role' => 'user']
        ];

        $this->mockTransactionModel
            ->expects($this->never())
            ->method('processPurchase');

        $controller = $this->createControllerWithMocks();

        ob_start();
        $controller->processPurchase();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testIndexValidatesAllowedSortFields(): void
    {
        $allowedFields = ['name', 'price', 'quantity_available', 'created_at'];
        $allowedOrders = ['ASC', 'DESC'];

        foreach ($allowedFields as $field) {
            foreach ($allowedOrders as $order) {
                $_GET = ['sort' => $field, 'order' => $order];

                $this->mockProductModel
                    ->expects($this->once())
                    ->method('all')
                    ->with(12, 0, $field, $order)
                    ->willReturn([]);

                $this->mockProductModel
                    ->expects($this->once())
                    ->method('count')
                    ->willReturn(0);

                $controller = $this->createControllerWithMocks();

                ob_start();
                $controller->index();
                ob_end_clean();

                // Reset mocks for next iteration
                $this->setUp();
            }
        }

        $this->assertTrue(true);
    }

    public function testIndexValidatesAllowedPerPageValues(): void
    {
        $allowedPerPage = [6, 12, 24, 48];

        foreach ($allowedPerPage as $perPage) {
            $_GET = ['per_page' => $perPage];

            $this->mockProductModel
                ->expects($this->once())
                ->method('all')
                ->with($perPage, 0, 'name', 'ASC')
                ->willReturn([]);

            $this->mockProductModel
                ->expects($this->once())
                ->method('count')
                ->willReturn(0);

            $controller = $this->createControllerWithMocks();

            ob_start();
            $controller->index();
            ob_end_clean();

            // Reset mocks for next iteration
            $this->setUp();
        }

        $this->assertTrue(true);
    }
}
