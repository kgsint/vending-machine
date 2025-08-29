<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private ?PDO $db = null;

    private function __construct()
    {
        // Use test config in testing environment
        $configFile = ($_ENV['APP_ENV'] ?? 'production') === 'testing' 
            ? CONFIG_PATH . "database_test.php" 
            : CONFIG_PATH . "database.php";
            
        $dbConfig = require_once $configFile;

        // Build DSN based on driver
        if ($dbConfig['driver'] === 'sqlite') {
            $dsn = "sqlite:" . $dbConfig['database'];
            $username = null;
            $password = null;
        } else {
            $dsn = "mysql:host={$dbConfig["host"]};dbname={$dbConfig["database"]}";
            $username = $dbConfig["username"];
            $password = $dbConfig["password"];
        }

        try {
            $this->db = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->db;
    }

    // Reset the singleton instance for testing
    public static function resetInstance()
    {
        self::$instance = null;
    }
}
