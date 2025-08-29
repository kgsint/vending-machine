<?php

namespace App\Controllers\Api;

use App\Models\User;
use Core\Database;
use Core\JwtAuth;
use Exception;

class AuthApiController
{
    private $db;
    private $userModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User($this->db);
    }

    /**
     * POST /api/auth/login - Login user
     */
    public function login()
    {
        try {
            $input = $this->getJsonInput();

            // Validate required fields
            if (empty($input['email']) || empty($input['password'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email and password are required'
                ], 400);
                return;
            }

            // Find user by email
            $user = $this->userModel->findByEmail($input['email']);
            
            if (!$user || !password_verify($input['password'], $user['password'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
                return;
            }

            // Generate JWT tokens
            $tokens = JwtAuth::generateToken([
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);

            // Remove password from response
            unset($user['password']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'data' => array_merge($tokens, [
                    'user' => $user
                ])
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/logout - Logout user
     */
    public function logout()
    {
        try {
            session_start();
            session_destroy();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/register - Register new user
     */
    public function register()
    {
        try {
            $input = $this->getJsonInput();

            // Validate required fields
            $errors = $this->validateRegistrationData($input);
            if (!empty($errors)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 422);
                return;
            }

            // Create user
            $userId = $this->userModel->create([
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'user' // Default role
            ]);

            if ($userId) {
                $user = $this->userModel->find($userId);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'data' => [
                        'user' => $user
                    ]
                ], 201);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create user'
                ], 500);
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/refresh - Refresh access token
     */
    public function refresh()
    {
        try {
            $input = $this->getJsonInput();
            
            if (empty($input['refresh_token'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Refresh token is required'
                ], 400);
                return;
            }

            // For refresh, we need to get user data from the database
            // since refresh tokens don't contain full user data
            $refreshToken = $input['refresh_token'];
            
            // First validate the refresh token to get user ID
            $payload = JwtAuth::validateToken($refreshToken);
            if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid refresh token'
                ], 401);
                return;
            }

            $userId = $payload['sub'];
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
                return;
            }

            // Generate new tokens
            $tokens = JwtAuth::refreshToken($refreshToken, [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => $tokens
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * GET /api/auth/me - Get current authenticated user (Requires JWT Authentication)
     */
    public function me()
    {
        try {
            // Use JWT authentication instead of sessions
            $user = JwtAuth::getAuthenticatedUser();
            
            if (!$user) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
                return;
            }

            // Get fresh user data from database
            $userId = $user['id'];
            $freshUser = $this->userModel->find($userId);

            if (!$freshUser) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
                return;
            }

            // Remove password from response
            unset($freshUser['password']);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'user' => $freshUser
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error fetching user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?? [];
    }

    private function validateRegistrationData(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        // Check for existing email/username
        try {
            if (!empty($data['email']) && $this->userModel->findByEmail($data['email'])) {
                $errors['email'] = 'Email already exists';
            }

            if (!empty($data['username']) && $this->userModel->findByUsername($data['username'])) {
                $errors['username'] = 'Username already exists';
            }
        } catch (Exception $e) {
            // Handle database errors silently for validation
        }

        return $errors;
    }
}
