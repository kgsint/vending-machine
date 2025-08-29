# Vending Machine API Documentation

A RESTful API for the PHP Vending Machine System.

## Base URL
```
http://localhost:8080/api
```

## Authentication
The API uses JWT (JSON Web Token) based authentication for secure, stateless authentication.

### Authentication Endpoints
- **POST /api/auth/login** - Get JWT tokens (public)
- **POST /api/auth/register** - Register new user (public)
- **POST /api/auth/refresh** - Refresh access token (public)
- **GET /api/auth/me** - Get current user (requires JWT)

### Protected Endpoints
The following endpoints require JWT authentication:
- **POST /api/products/\*/purchase** - Purchase products
- **GET /api/users/\*/transactions** - View transaction history
- **POST /api/products** - Create products (admin only)
- **PUT /api/products/\*/update** - Update products (admin only)
- **DELETE /api/products/\*/delete** - Delete products (admin only)

### Public Endpoints
These endpoints are publicly accessible:
- **GET /api/products** - List products
- **GET /api/products/show** - View product details

## Response Format
All API responses follow this format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data here
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message"
}
```

## API Endpoints

### Authentication

#### POST /api/auth/login
Login to the system.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "username": "user1",
      "email": "user@example.com",
      "role": "user"
    },
    "session_based": true
  }
}
```

#### POST /api/auth/register
Register a new user.

**Request:**
```json
{
  "username": "newuser",
  "email": "newuser@example.com",
  "password": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 2,
      "username": "newuser",
      "email": "newuser@example.com",
      "role": "user"
    }
  }
}
```

#### GET /api/auth/me
Get current authenticated user information.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "user1",
      "email": "user@example.com",
      "role": "user"
    }
  }
}
```

#### POST /api/auth/logout
Logout from the system.

**Response (200):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

### Products

#### GET /api/products
Get all products with pagination and filtering.

**Query Parameters:**
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 20, max: 100)
- `sort` (string): Sort field (name, price, quantity_available, created_at)
- `order` (string): Sort order (ASC, DESC)
- `search` (string): Search term
- `active_only` (boolean): Filter active products only (default: true)

**Example Request:**
```
GET /api/products?page=1&per_page=10&sort=name&order=ASC&active_only=true
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Coke",
        "price": "3.999",
        "quantity_available": 10,
        "description": "Classic Coca-Cola",
        "image_url": null,
        "is_active": 1,
        "created_at": "2025-01-01 00:00:00",
        "updated_at": "2025-01-01 00:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total_pages": 1,
      "total_items": 1
    },
    "filters": {
      "sort_by": "name",
      "order": "ASC",
      "search": "",
      "active_only": true
    }
  }
}
```

#### GET /api/products/show?id={id}
Get a specific product by ID.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Coke",
    "price": "3.999",
    "quantity_available": 10,
    "description": "Classic Coca-Cola",
    "image_url": null,
    "is_active": 1,
    "created_at": "2025-01-01 00:00:00",
    "updated_at": "2025-01-01 00:00:00"
  }
}
```

#### POST /api/products
Create a new product (Admin only).

**Request:**
```json
{
  "name": "New Product",
  "price": 5.99,
  "quantity_available": 20,
  "description": "Product description",
  "image_url": "https://example.com/image.jpg",
  "is_active": true
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product created successfully"
}
```

#### PUT /api/products/update?id={id}
Update an existing product (Admin only).

**Request:**
```json
{
  "name": "Updated Product",
  "price": 6.99,
  "quantity_available": 15,
  "description": "Updated description",
  "image_url": "https://example.com/new-image.jpg",
  "is_active": true
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    // Updated product data
  }
}
```

#### DELETE /api/products/delete?id={id}
Delete a product (Admin only).

**Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

#### POST /api/products/purchase?id={id}
Purchase a product (Authentication required).

**Request:**
```json
{
  "quantity": 2
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Purchase completed successfully",
  "data": {
    "success": true,
    "transaction_id": 1,
    "total_price": 7.998
  }
}
```

### Transactions

#### GET /api/users/transactions?user_id={userId}
Get user transaction history (User can only view their own transactions).

**Query Parameters:**
- `user_id` (int): User ID
- `limit` (int): Number of transactions to return (default: 20, max: 100)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 1,
        "user_id": 1,
        "product_id": 1,
        "quantity": 2,
        "unit_price": "3.999",
        "total_price": "7.998",
        "transaction_date": "2025-01-01 12:00:00",
        "status": "completed",
        "product_name": "Coke",
        "image_url": null
      }
    ],
    "user_id": 1,
    "limit": 20
  }
}
```

## Error Codes

- **400 Bad Request**: Invalid request data
- **401 Unauthorized**: Authentication required
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **500 Internal Server Error**: Server error

## Example Usage with cURL

### Login and Get JWT Token
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@vendingmachine.com","password":"admin123"}'

# Response will include access_token and refresh_token
# Save the access_token for subsequent requests
```

### Get Products (Public)
```bash
curl -X GET "http://localhost:8080/api/products?page=1&per_page=5" \
  -H "Content-Type: application/json"
```

### Purchase Product (Requires JWT)
```bash
curl -X POST "http://localhost:8080/api/products/purchase?id=1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"quantity":1}'
```

### Create Product (Admin JWT Required)
```bash
curl -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_ACCESS_TOKEN" \
  -d '{"name":"Energy Drink","price":4.99,"quantity_available":15,"description":"Energy boost drink"}'
```

## JWT Authentication Usage

### Getting a Token
1. Login via POST /api/auth/login with email and password
2. Save the `access_token` from the response
3. Include the token in subsequent requests using the Authorization header

### Using the Token
```bash
# Include in all protected requests
-H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Token Refresh
```bash
curl -X POST http://localhost:8080/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":"YOUR_REFRESH_TOKEN"}'
```

## Notes
- JWT tokens expire after 1 hour (access_token) and 24 hours (refresh_token)
- Admin endpoints require admin role in the JWT payload
- All prices are stored with 3 decimal precision
- Product quantities must be non-negative integers
- Use refresh tokens to get new access tokens when they expire
