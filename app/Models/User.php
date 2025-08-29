<?php

namespace App\Models;

use PDO;
use PDOException;
use DateTime;

class User
{
    private $db;

    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $role;
    public DateTime $created_at;
    public DateTime $updated_at;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get all users with pagination
     */
    public function all(int $limit = 50, int $offset = 0, string $orderBy = "created_at", string $sortBy = "DESC"): array
    {
        $query = "SELECT id, username, email, role, created_at, updated_at FROM users ORDER BY $orderBy $sortBy LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error fetching users: " . $e->getMessage());
        }
    }

    /**
     * Find user by email (including password for authentication)
     */
    public function findByEmail(string $email): ?array
    {
        $query = "SELECT * FROM users WHERE email = :email";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(["email" => $email]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new \Exception("Error finding user by email: " . $e->getMessage());
        }
    }
    
    /**
     * Find user by ID (without password)
     */
    public function find(int $id): ?array
    {
        $query = "SELECT id, username, email, role, created_at, updated_at FROM users WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new \Exception("Error finding user: " . $e->getMessage());
        }
    }
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        $query = "SELECT id, username, email, role, created_at, updated_at FROM users WHERE username = :username";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(["username" => $username]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new \Exception("Error finding user by username: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new user
     */
    public function create(array $data): int
    {
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new \Exception("Username, email, and password are required");
        }
        
        // Check if email already exists
        if ($this->findByEmail($data['email'])) {
            throw new \Exception("Email already exists");
        }
        
        // Check if username already exists
        if ($this->findByUsername($data['username'])) {
            throw new \Exception("Username already exists");
        }
        
        $query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindParam(':role', $data['role'] ?? 'user');
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }
    
    /**
     * Update user information
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];
        
        if (!empty($data['username'])) {
            $fields[] = "username = :username";
            $params[':username'] = $data['username'];
        }
        
        if (!empty($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (!empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role']) && in_array($data['role'], ['admin', 'user'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \Exception("Error updating user: " . $e->getMessage());
        }
    }
    
    /**
     * Delete user (soft delete by deactivation could be implemented later)
     */
    public function delete(int $id): bool
    {
        $query = "DELETE FROM users WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error deleting user: " . $e->getMessage());
        }
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Return user without password
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'admin';
    }
    
    /**
     * Get users count
     */
    public function count(): int
    {
        $query = "SELECT COUNT(*) as total FROM users";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            throw new \Exception("Error counting users: " . $e->getMessage());
        }
    }
    
    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        if (!in_array($role, ['admin', 'user'])) {
            throw new \Exception("Invalid role");
        }
        
        $query = "SELECT id, username, email, role, created_at, updated_at FROM users WHERE role = :role ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error fetching users by role: " . $e->getMessage());
        }
    }
}
