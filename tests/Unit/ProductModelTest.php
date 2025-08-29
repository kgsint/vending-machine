<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;

class ProductModelTest extends TestCase
{
    private Product $productModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productModel = new Product($this->pdo);
    }

    public function testCanRetrieveAllProducts(): void
    {
        $products = $this->productModel->all();
        
        $this->assertIsArray($products);
        $this->assertCount(4, $products); // Should have 4 active products
        
        // Check structure of first product
        $firstProduct = $products[0];
        $this->assertArrayHasKey('id', $firstProduct);
        $this->assertArrayHasKey('name', $firstProduct);
        $this->assertArrayHasKey('price', $firstProduct);
        $this->assertArrayHasKey('quantity_available', $firstProduct);
    }

    public function testCanRetrieveAllProductsWithPagination(): void
    {
        $products = $this->productModel->all(2, 0, 'name', 'ASC');
        
        $this->assertIsArray($products);
        $this->assertCount(2, $products);
        $this->assertEquals('Coke', $products[0]['name']);
    }

    public function testCanCountActiveProducts(): void
    {
        $count = $this->productModel->count(true);
        $this->assertEquals(4, $count);
    }

    public function testCanCountAllProducts(): void
    {
        $count = $this->productModel->count(false);
        $this->assertEquals(5, $count); // Including inactive product
    }

    public function testCanFindProductById(): void
    {
        $product = $this->productModel->find(1);
        
        $this->assertIsArray($product);
        $this->assertEquals('Coke', $product['name']);
        $this->assertEquals(3.999, $product['price']);
        $this->assertEquals(10, $product['quantity_available']);
    }

    public function testReturnsNullForNonexistentProduct(): void
    {
        $product = $this->productModel->find(999);
        $this->assertNull($product);
    }

    public function testCanCreateNewProduct(): void
    {
        $productData = [
            'name' => 'Test Cola',
            'price' => 2.50,
            'quantity_available' => 15,
            'description' => 'Test cola drink',
            'image_url' => null,
            'is_active' => true
        ];

        $created = $this->productModel->create($productData);
        $this->assertTrue($created);

        // Verify the product was created
        $products = $this->productModel->search('Test Cola');
        $this->assertNotEmpty($products);
        $this->assertEquals('Test Cola', $products[0]['name']);
    }

    public function testCanUpdateExistingProduct(): void
    {
        $updateData = [
            'name' => 'Updated Coke',
            'price' => 4.99,
            'quantity_available' => 8,
            'description' => 'Updated description',
            'image_url' => null,
            'is_active' => true
        ];

        $updated = $this->productModel->update(1, $updateData);
        $this->assertTrue($updated);

        // Verify the update
        $product = $this->productModel->find(1);
        $this->assertEquals('Updated Coke', $product['name']);
        $this->assertEquals(4.99, $product['price']);
        $this->assertEquals(8, $product['quantity_available']);
    }

    public function testCanSoftDeleteProduct(): void
    {
        $deleted = $this->productModel->delete(1);
        $this->assertTrue($deleted);

        // Product should still exist but be inactive
        $product = $this->productModel->find(1);
        $this->assertNotNull($product);
        $this->assertEquals(0, $product['is_active']);

        // Should not appear in active products
        $activeProducts = $this->productModel->all();
        $this->assertCount(3, $activeProducts); // One less active product
    }

    public function testCanUpdateProductQuantity(): void
    {
        $updated = $this->productModel->updateQuantity(1, 15);
        $this->assertTrue($updated);

        $product = $this->productModel->find(1);
        $this->assertEquals(15, $product['quantity_available']);
    }

    public function testCannotSetNegativeQuantity(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Quantity cannot be negative');
        
        $this->productModel->updateQuantity(1, -5);
    }

    public function testCanDecreaseQuantity(): void
    {
        $decreased = $this->productModel->decreaseQuantity(1, 3);
        $this->assertTrue($decreased);

        $product = $this->productModel->find(1);
        $this->assertEquals(7, $product['quantity_available']); // 10 - 3 = 7
    }

    public function testCannotDecreaseQuantityBelowZero(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient inventory');
        
        $this->productModel->decreaseQuantity(1, 15); // Trying to decrease by more than available
    }

    public function testCanCheckStock(): void
    {
        $hasStock = $this->productModel->hasStock(1, 5);
        $this->assertTrue($hasStock);

        $hasStock = $this->productModel->hasStock(1, 15);
        $this->assertFalse($hasStock);

        // Out of stock product
        $hasStock = $this->productModel->hasStock(4, 1);
        $this->assertFalse($hasStock);

        // Inactive product
        $hasStock = $this->productModel->hasStock(5, 1);
        $this->assertFalse($hasStock);
    }

    public function testCanSearchProducts(): void
    {
        $results = $this->productModel->search('Coke');
        $this->assertNotEmpty($results);
        $this->assertEquals('Coke', $results[0]['name']);

        $results = $this->productModel->search('nonexistent');
        $this->assertEmpty($results);

        // Case insensitive search
        $results = $this->productModel->search('coke');
        $this->assertNotEmpty($results);
    }

    public function testSearchRespectsLimit(): void
    {
        $results = $this->productModel->search('', 2); // Empty search should return all
        $this->assertCount(2, $results);
    }

    public function testCanHandleSortingParameters(): void
    {
        // Test sorting by price ascending
        $products = $this->productModel->all(10, 0, 'price', 'ASC');
        $this->assertEquals('Water', $products[0]['name']); // Cheapest product

        // Test sorting by price descending
        $products = $this->productModel->all(10, 0, 'price', 'DESC');
        $this->assertEquals('Pepsi', $products[0]['name']); // Most expensive product

        // Test sorting by quantity
        $products = $this->productModel->all(10, 0, 'quantity_available', 'DESC');
        $this->assertEquals('Water', $products[0]['name']); // Highest quantity (20)
    }

    public function testPaginationWorksCorrectly(): void
    {
        // First page
        $page1 = $this->productModel->all(2, 0, 'name', 'ASC');
        $this->assertCount(2, $page1);
        $this->assertEquals('Coke', $page1[0]['name']);

        // Second page
        $page2 = $this->productModel->all(2, 2, 'name', 'ASC');
        $this->assertCount(2, $page2);
        $this->assertEquals('Pepsi', $page2[0]['name']);

        // Make sure pages are different
        $this->assertNotEquals($page1[0]['id'], $page2[0]['id']);
    }

    public function testActiveOnlyFilterWorks(): void
    {
        // Active products only
        $activeProducts = $this->productModel->all(10, 0, 'name', 'ASC', true);
        $this->assertCount(4, $activeProducts);

        // All products including inactive
        $allProducts = $this->productModel->all(10, 0, 'name', 'ASC', false);
        $this->assertCount(5, $allProducts);
    }
}
