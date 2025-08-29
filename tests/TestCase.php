<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Core\Database;
use PDO;

class TestCase extends PHPUnitTestCase
{
    protected $db;
    protected $pdo;

    protected function setUp(): void
    {
        // Set testing environment
        $_ENV['APP_ENV'] = 'testing';
        
        // Reset database instance
        Database::resetInstance();
        
        // Get test database connection
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
        
        // Setup test schema
        $this->setupTestSchema();
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        // Clean up
        Database::resetInstance();
        unset($this->db, $this->pdo);
    }

    /**
     * Setup SQLite schema for testing
     */
    private function setupTestSchema(): void
    {
        // Users table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(10) DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Products table  
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                price DECIMAL(10, 3) NOT NULL,
                quantity_available INTEGER NOT NULL DEFAULT 0,
                description TEXT,
                image_url VARCHAR(255),
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Transactions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price DECIMAL(10, 3) NOT NULL,
                total_price DECIMAL(10, 3) NOT NULL,
                transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'completed',
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");
    }

    /**
     * Seed test data
     */
    private function seedTestData(): void
    {
        // Insert test users
        $this->pdo->exec("
            INSERT INTO users (username, email, password, role) VALUES 
            ('admin', 'admin@test.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
            ('user1', 'user1@test.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
        ");

        // Insert test products
        $this->pdo->exec("
            INSERT INTO products (name, price, quantity_available, description, is_active) VALUES 
            ('Coke', 3.999, 10, 'Classic Coca-Cola', 1),
            ('Pepsi', 6.885, 5, 'Pepsi Cola', 1),
            ('Water', 0.500, 20, 'Pure Water', 1),
            ('Out of Stock', 1.000, 0, 'Test product', 1),
            ('Inactive Product', 2.000, 5, 'Inactive test product', 0)
        ");
    }

    /**
     * Create a mock user session
     */
    protected function actingAs(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    /**
     * Clear session
     */
    protected function clearSession(): void
    {
        $_SESSION = [];
    }
}
