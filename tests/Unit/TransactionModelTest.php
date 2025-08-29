<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\Product;

class TransactionModelTest extends TestCase
{
    private Transaction $transactionModel;
    private Product $productModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionModel = new Transaction($this->pdo);
        $this->productModel = new Product($this->pdo);
    }

    public function testCanCreateTransaction(): void
    {
        $transactionData = [
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 3.999,
            'total_price' => 7.998,
            'status' => 'completed'
        ];

        $created = $this->transactionModel->create($transactionData);
        $this->assertTrue($created);

        // Verify transaction was created
        $transactions = $this->transactionModel->getUserTransactions(2);
        $this->assertNotEmpty($transactions);
        $this->assertEquals(2, $transactions[0]['quantity']);
    }

    public function testCanProcessPurchase(): void
    {
        $userId = 2;
        $productId = 1;
        $quantity = 3;

        // Get initial product quantity
        $initialProduct = $this->productModel->find($productId);
        $initialQuantity = $initialProduct['quantity_available'];

        $result = $this->transactionModel->processPurchase($userId, $productId, $quantity);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertArrayHasKey('total_price', $result);
        $this->assertEquals(11.997, $result['total_price']); // 3.999 * 3

        // Verify product quantity was decreased
        $updatedProduct = $this->productModel->find($productId);
        $this->assertEquals($initialQuantity - $quantity, $updatedProduct['quantity_available']);

        // Verify transaction was recorded
        $transaction = $this->transactionModel->find($result['transaction_id']);
        $this->assertNotNull($transaction);
        $this->assertEquals($userId, $transaction['user_id']);
        $this->assertEquals($productId, $transaction['product_id']);
        $this->assertEquals($quantity, $transaction['quantity']);
    }

    public function testProcessPurchaseFailsForInvalidProduct(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found');
        
        $this->transactionModel->processPurchase(2, 999, 1);
    }

    public function testProcessPurchaseFailsForInsufficientStock(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');
        
        $this->transactionModel->processPurchase(2, 1, 50); // More than available
    }

    public function testProcessPurchaseFailsForOutOfStockProduct(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');
        
        $this->transactionModel->processPurchase(2, 4, 1); // Product with 0 quantity
    }

    public function testProcessPurchaseFailsForInactiveProduct(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not available');
        
        $this->transactionModel->processPurchase(2, 5, 1); // Inactive product
    }

    public function testCanGetUserTransactions(): void
    {
        // Create test transactions
        $this->transactionModel->create([
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ]);

        $this->transactionModel->create([
            'user_id' => 2,
            'product_id' => 2,
            'quantity' => 2,
            'unit_price' => 6.885,
            'total_price' => 13.770,
            'status' => 'completed'
        ]);

        $transactions = $this->transactionModel->getUserTransactions(2);
        
        $this->assertIsArray($transactions);
        $this->assertCount(2, $transactions);
        
        // Check if transactions include product names
        $this->assertArrayHasKey('product_name', $transactions[0]);
        $this->assertNotEmpty($transactions[0]['product_name']);
    }

    public function testGetUserTransactionsRespectsLimit(): void
    {
        // Create multiple transactions
        for ($i = 0; $i < 5; $i++) {
            $this->transactionModel->create([
                'user_id' => 2,
                'product_id' => 1,
                'quantity' => 1,
                'unit_price' => 3.999,
                'total_price' => 3.999,
                'status' => 'completed'
            ]);
        }

        $transactions = $this->transactionModel->getUserTransactions(2, 3);
        $this->assertCount(3, $transactions);
    }

    public function testCanFindTransactionById(): void
    {
        $transactionData = [
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ];

        $this->transactionModel->create($transactionData);
        
        // Get the transaction ID (assuming it's 1 for the first transaction)
        $transactions = $this->transactionModel->getUserTransactions(2, 1);
        $transactionId = $transactions[0]['id'];

        $transaction = $this->transactionModel->find($transactionId);
        
        $this->assertNotNull($transaction);
        $this->assertEquals(2, $transaction['user_id']);
        $this->assertEquals(1, $transaction['product_id']);
        $this->assertEquals(1, $transaction['quantity']);
    }

    public function testReturnsNullForNonexistentTransaction(): void
    {
        $transaction = $this->transactionModel->find(999);
        $this->assertNull($transaction);
    }

    public function testGetAllTransactionsWithPagination(): void
    {
        // Create test transactions for different users
        $this->transactionModel->create([
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ]);

        $this->transactionModel->create([
            'user_id' => 2,
            'product_id' => 2,
            'quantity' => 1,
            'unit_price' => 6.885,
            'total_price' => 6.885,
            'status' => 'completed'
        ]);

        $transactions = $this->transactionModel->all(10, 0);
        
        $this->assertIsArray($transactions);
        $this->assertCount(2, $transactions);
        
        // Test with limit
        $transactions = $this->transactionModel->all(1, 0);
        $this->assertCount(1, $transactions);
    }

    public function testTransactionIncludesProductAndUserInformation(): void
    {
        $this->transactionModel->create([
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ]);

        $transactions = $this->transactionModel->getUserTransactions(2);
        $transaction = $transactions[0];
        
        // Should include product information
        $this->assertArrayHasKey('product_name', $transaction);
        $this->assertEquals('Coke', $transaction['product_name']);
        
        // Should include price information
        $this->assertArrayHasKey('unit_price', $transaction);
        $this->assertArrayHasKey('total_price', $transaction);
        $this->assertEquals(3.999, $transaction['unit_price']);
        $this->assertEquals(3.999, $transaction['total_price']);
    }

    public function testProcessPurchaseValidatesInputParameters(): void
    {
        // Test invalid user ID
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid purchase data');
        
        $this->transactionModel->processPurchase(0, 1, 1);
    }

    public function testProcessPurchaseValidatesQuantity(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid purchase data');
        
        $this->transactionModel->processPurchase(2, 1, 0);
    }

    public function testProcessPurchaseCalculatesTotalCorrectly(): void
    {
        $result = $this->transactionModel->processPurchase(2, 2, 2); // Pepsi at 6.885 * 2
        
        $this->assertEquals(13.770, $result['total_price']);
        
        // Verify in database
        $transaction = $this->transactionModel->find($result['transaction_id']);
        $this->assertEquals(6.885, $transaction['unit_price']);
        $this->assertEquals(13.770, $transaction['total_price']);
    }

    public function testCanGetCountOfTransactions(): void
    {
        // Create some transactions
        $this->transactionModel->create([
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ]);

        $this->transactionModel->create([
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 1,
            'unit_price' => 3.999,
            'total_price' => 3.999,
            'status' => 'completed'
        ]);

        $count = $this->transactionModel->count();
        $this->assertEquals(2, $count);
    }
}
