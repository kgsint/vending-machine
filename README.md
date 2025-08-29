# PHP Vending Machine System

A complete, modern PHP-based vending machine management system with admin controls, user authentication, transaction processing, and RESTful API with JWT authentication.

## Project Overview

This system provides a robust solution for managing a digital vending machine with comprehensive features:

- **Product Management**: Complete CRUD operations with inventory tracking
- **User Authentication**: Role-based access control (Admin/User)
- **Transaction Processing**: Secure purchase processing with inventory updates
- **Admin Dashboard**: Comprehensive administrative interface with analytics
- **RESTful API**: JWT-authenticated API for frontend applications
- **Modern Architecture**: MVC pattern with dependency injection
- **Responsive Design**: Bootstrap 5 UI with mobile-friendly design

## Features

### 🛍️ Core Functionality
- [x] Product catalog with search, sort, and pagination
- [x] Secure user registration and authentication
- [x] Shopping cart and purchase processing
- [x] Inventory management with stock tracking
- [x] Transaction history and reporting
- [x] Admin panel for system management

### Security & Authentication
- [x] JWT token-based API authentication
- [x] Password hashing with bcrypt
- [x] Role-based access control
- [x] SQL injection prevention
- [x] XSS protection
- [x] Session security

### API Features
- [x] RESTful API endpoints
- [x] JWT authentication with refresh tokens
- [x] Comprehensive API documentation
- [x] Error handling and validation
- [x] Rate limiting ready

### Quality Assurance
- [x] Unit tests with PHPUnit
- [x] Dependency injection container
- [x] Mock objects for testing
- [x] Code validation and error handling

### Folder Structure

```
vending-maching-php/
├── 📁 app/
│   ├── 📁 Contracts/
│   │   ├── ProductRepositoryInterface.php
│   │   └── TransactionRepositoryInterface.php
│   ├── 📁 Controllers/
│   │   ├── 📁 Api/
│   │   │   ├── AuthApiController.php
│   │   │   └── ProductsApiController.php
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── ProductsController.php
│   │   └── ProductsControllerDI.php
│   ├── 📁 Exceptions/
│   │   └── ValidationException.php
│   ├── 📁 FormRequests/
│   │   ├── BaseFormRequest.php
│   │   └── LoginFormRequest.php
│   ├── 📁 Models/
│   │   ├── Product.php
│   │   ├── Transaction.php
│   │   └── User.php
│   ├── 📁 views/
│   │   ├── 📁 admin/
│   │   │   ├── 📁 products/
│   │   │   │   ├── create.view.php
│   │   │   │   ├── edit.view.php
│   │   │   │   └── index.view.php
│   │   │   ├── 📁 transactions/
│   │   │   │   └── index.view.php
│   │   │   ├── 📁 users/
│   │   │   │   ├── create.view.php
│   │   │   │   ├── edit.view.php
│   │   │   │   └── index.view.php
│   │   │   ├── dashboard.view.php
│   │   │   └── layout.php
│   │   ├── 📁 auth/
│   │   │   └── login.view.php
│   │   ├── 📁 components/
│   │   │   ├── pagination.php
│   │   │   └── pagination-demo.php
│   │   ├── 📁 products/
│   │   │   ├── index.view.php
│   │   │   ├── purchase.view.php
│   │   │   └── show.view.php
│   │   ├── 📁 transactions/
│   │   │   └── history.view.php
│   │   ├── home.view.php
│   │   └── layout.php
│   └── helper.php
├── 📁 Core/
│   ├── Auth.php
│   ├── Container.php
│   ├── Database.php
│   ├── JwtAuth.php
│   ├── Logger.php
│   ├── Router.php
│   ├── Session.php
│   ├── Validator.php
│   └── View.php
├── 📁 config/
│   ├── database.php
├── 📁 public/
│   ├── 📁 css/
│   │   ├── bootstrap.min.css
│   │   ├── fontawesome.min.css
│   │   └── style.css
│   ├── 📁 js/
│   │   ├── app.js
│   │   └── bootstrap.bundle.js
│   └── index.php
├── 📁 storage/
│   └── 📁 logs/
│       └── app.log
├── 📁 tests/
│   ├── 📁 Mocks/
│   │   ├── MockProductRepository.php
│   │   └── MockTransactionRepository.php
│   ├── 📁 Unit/
│   │   ├── ProductModelTest.php
│   │   ├── ProductsControllerDITest.php
│   │   ├── ProductsControllerSimpleTest.php
│   │   ├── ProductsControllerTest.php
│   │   ├── ProductsControllerUnitTest.php
│   │   └── TransactionModelTest.php
│   ├── TestCase.php
│   └── bootstrap.php
├── 📁 vendor/
├── .env.example
├── .gitignore
├── API_DOCUMENTATION.md
├── README.md
├── composer.json
├── composer.lock
├── phpunit.xml
```

## Installation & Setup

### Prerequisites

Before installing, ensure you have:

- **PHP 8.0+** with the following extensions:
  - `pdo`
  - `pdo_mysql`
  - `json`
  - `mbstring`
  - `openssl`
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Composer** (PHP dependency manager)
- **Web server** (Apache/Nginx) or PHP built-in server

### Quick Installation

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url> vending-machine-php
   cd vending-machine-php
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   ```bash
   # Create MySQL database
   mysql -u root -p -e "CREATE DATABASE vending_machine;"

   # Run migrations and seed data
   php database/migrate.php
   ```

4. **Start Development Server**
   ```bash
   php -S localhost:8080 -t public
   ```

5. **Access the Application**
   - **Frontend**: http://localhost:8080
   - **Admin Panel**: http://localhost:8080/admin
   - **API**: http://localhost:8080/api

### Default Credentials

- **Admin**: `admin@vendingmachine.com` / `password`
- **User**: `user@example.com` / `password`


### Database Configuration

Edit `config/database.php`:

```php
<?php
return [
    "driver" => "mysql",
    "host" => "localhost",
    "database" => "vending_machine",
    "username" => "your_username",
    "password" => "your_password",
    "charset" => "utf8mb4",
    "collation" => "utf8mb4_unicode_ci",
];
```

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit/

# Run specific test file
./vendor/bin/phpunit tests/Unit/ProductModelTest.php

# Run with coverage (if xdebug installed)
./vendor/bin/phpunit --coverage-html coverage/
```

### Database Testing

```bash
# Test database connection
php test_database.php

### Database Schema

**Users Table**
- id, username, email, password (hashed)
- role (admin/user)
- timestamps

**Products Table**
- id, name, price, quantity_available
- description, image_url, is_active
- timestamps

**Transactions Table**
- id, user_id, product_id, quantity
- unit_price, total_price, status
- transaction_date

## Key Features Implemented

### Admin Dashboard
- **Statistics Overview**: Product counts, user counts, revenue metrics
- **Quick Actions**: Easy access to common administrative tasks
- **Recent Activity**: Latest transactions and system activity
- **Top Products**: Sales analytics and performance metrics

### Product Management
- **CRUD Operations**: Create, read, update, delete products
- **Inventory Tracking**: Real-time stock levels
- **Image Support**: Product image URLs
- **Status Management**: Active/inactive product states
- **Search & Filter**: Find products quickly

### User Management
- **User CRUD**: Complete user account management
- **Role Assignment**: Admin/User role management
- **Security**: Password hashing and validation
- **Account Protection**: Prevent self-deletion

### Transaction Management
- **Purchase Processing**: Secure transaction handling
- **Inventory Updates**: Automatic stock reduction
- **Transaction History**: Complete audit trail
- **Status Tracking**: Transaction status management

### Security Features
- **Password Hashing**: Secure bcrypt hashing
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Ready**: Framework prepared for CSRF tokens
- **Session Security**: Secure session management

The system comes pre-configured with the specified products:

1. **Coke** - $3.99 - Classic Coca-Cola refreshing drink
2. **Pepsi** - $6.885 - Pepsi cola with great taste
3. **Water** - $0.50 - Pure drinking water

## etup Instructions

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Composer
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone/Setup Project**
   ```bash
   cd /path/to/your/project
   composer install
   ```

2. **Database Configuration**
   ```bash
   # Update database credentials in config/database.php
   # Create database: vending_machine
   ```

3. **Start Development Server**
   ```bash
   php -S localhost:8080 -t public
   ```

### Default Credentials
- **Admin**: admin@vendingmachine.com / password
- **User**: user@example.com / password
