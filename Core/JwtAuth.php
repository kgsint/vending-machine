<?php

namespace Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class JwtAuth
{
    private static $secretKey = 'vending-machine-secret-key-2025'; // In production, use environment variable
    private static $algorithm = 'HS256';
    private static $expiryTime = 3600; // 1 hour
    private static $refreshExpiryTime = 86400; // 24 hours

    /**
     * Generate JWT token for user
     */
    public static function generateToken(array $userData): array
    {
        $now = time();
        $accessExpiry = $now + self::$expiryTime;
        $refreshExpiry = $now + self::$refreshExpiryTime;

        // Access token payload
        $accessPayload = [
            'iss' => 'vending-machine-api', // Issuer
            'aud' => 'vending-machine-users', // Audience
            'iat' => $now, // Issued at
            'exp' => $accessExpiry, // Expiry
            'sub' => $userData['id'], // Subject (user ID)
            'type' => 'access',
            'user' => [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ]
        ];

        // Refresh token payload
        $refreshPayload = [
            'iss' => 'vending-machine-api',
            'aud' => 'vending-machine-users',
            'iat' => $now,
            'exp' => $refreshExpiry,
            'sub' => $userData['id'],
            'type' => 'refresh'
        ];

        $accessToken = JWT::encode($accessPayload, self::$secretKey, self::$algorithm);
        $refreshToken = JWT::encode($refreshPayload, self::$secretKey, self::$algorithm);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => self::$expiryTime,
            'expires_at' => $accessExpiry
        ];
    }

    /**
     * Validate and decode JWT token
     */
    public static function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            $payload = (array) $decoded;

            // Check if token is access type
            if ($payload['type'] !== 'access') {
                return null;
            }

            // Convert user object to array
            $payload['user'] = (array) $payload['user'];

            return $payload;
        } catch (ExpiredException $e) {
            throw new Exception('Token has expired');
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid token signature');
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public static function refreshToken(string $refreshToken, array $userData): array
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key(self::$secretKey, self::$algorithm));
            $payload = (array) $decoded;

            // Check if token is refresh type
            if ($payload['type'] !== 'refresh') {
                throw new Exception('Invalid refresh token type');
            }

            // Generate new access token
            return self::generateToken($userData);
        } catch (ExpiredException $e) {
            throw new Exception('Refresh token has expired');
        } catch (Exception $e) {
            throw new Exception('Invalid refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Extract token from Authorization header
     */
    public static function extractTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            return null;
        }

        // Check for Bearer token format
        if (preg_match('/Bearer\\s+(\\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get authenticated user from token
     */
    public static function getAuthenticatedUser(): ?array
    {
        try {
            $token = self::extractTokenFromHeader();
            
            if (!$token) {
                return null;
            }

            $payload = self::validateToken($token);
            return $payload ? $payload['user'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if current user is authenticated
     */
    public static function check(): bool
    {
        return self::getAuthenticatedUser() !== null;
    }

    /**
     * Check if current user is admin
     */
    public static function isAdmin(): bool
    {
        $user = self::getAuthenticatedUser();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    /**
     * Require authentication (throws exception if not authenticated)
     */
    public static function requireAuth(): array
    {
        $user = self::getAuthenticatedUser();
        
        if (!$user) {
            throw new Exception('Authentication required', 401);
        }

        return $user;
    }

    /**
     * Require admin access (throws exception if not admin)
     */
    public static function requireAdmin(): array
    {
        $user = self::requireAuth();
        
        if (($user['role'] ?? '') !== 'admin') {
            throw new Exception('Admin access required', 403);
        }

        return $user;
    }

    /**
     * Generate API response for authentication errors
     */
    public static function unauthorizedResponse(string $message = 'Authentication required'): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Generate API response for authorization errors
     */
    public static function forbiddenResponse(string $message = 'Access denied'): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}
