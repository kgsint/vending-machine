<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Core\Database;
use Core\Session;
use Core\View;
use App\Exceptions\ValidationException;

class ProductsController
{
    private $db;
    private $productModel;
    private $transactionModel;
    private $userModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->productModel = new Product($this->db);
        $this->transactionModel = new Transaction($this->db);
        $this->userModel = new User($this->db);
    }
    
    /**
     * Display products list with pagination and sorting
     */
    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 12; // Products per page
        $offset = ($page - 1) * $limit;
        
        $sortBy = $_GET['sort'] ?? 'name';
        $order = $_GET['order'] ?? 'ASC';
        $search = $_GET['search'] ?? '';
        
        // Validate sorting parameters
        $allowedSortFields = ['name', 'price', 'quantity_available', 'created_at'];
        $allowedOrders = ['ASC', 'DESC'];
        
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }
        
        if (!in_array($order, $allowedOrders)) {
            $order = 'ASC';
        }
        
        try {
            if ($search) {
                $products = $this->productModel->search($search, $limit);
                $totalProducts = count($products);
            } else {
                $products = $this->productModel->all($limit, $offset, $sortBy, $order);
                $totalProducts = $this->productModel->count();
            }
            
            $totalPages = ceil($totalProducts / $limit);
            
            return View::make('products/index', [
                'products' => $products,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'sortBy' => $sortBy,
                'order' => $order,
                'search' => $search,
                'totalProducts' => $totalProducts
            ]);
        } catch (\Exception $e) {
            Session::flashError('error', 'Error loading products: ' . $e->getMessage());
            return redirect('/');
        }
    }
    
    /**
     * Show single product details
     */
    public function show()
    {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            Session::flashError('error', 'Product not found');
            return redirect('/products');
        }
        
        try {
            $product = $this->productModel->find($id);
            
            if (!$product || !$product['is_active']) {
                Session::flashError('error', 'Product not found');
                return redirect('/products');
            }
            
            return View::make('products/show', ['product' => $product]);
        } catch (\Exception $e) {
            Session::flashError('error', 'Error loading product: ' . $e->getMessage());
            return redirect('/products');
        }
    }
    
    /**
     * Show purchase form for a product
     */
    public function purchase()
    {
        $this->requireAuth();
        
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            Session::flashError('error', 'Product not found');
            return redirect('/products');
        }
        
        try {
            $product = $this->productModel->find($id);
            
            if (!$product || !$product['is_active'] || $product['quantity_available'] <= 0) {
                Session::flashError('error', 'Product not available for purchase');
                return redirect('/products');
            }
            
            return View::make('products/purchase', ['product' => $product]);
        } catch (\Exception $e) {
            Session::flashError('error', 'Error loading product: ' . $e->getMessage());
            return redirect('/products');
        }
    }
    
    /**
     * Process purchase transaction
     */
    public function processPurchase()
    {
        $this->requireAuth();
        
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $userId = Session::get('user')['id'];
        
        if (!$productId || $quantity <= 0) {
            Session::flashError('error', 'Invalid purchase data');
            return redirect('/products');
        }
        
        try {
            $result = $this->transactionModel->processPurchase($userId, $productId, $quantity);
            
            if ($result['success']) {
                Session::flashError('success', 'Purchase completed successfully! Total: $' . number_format($result['total_price'], 3));
                return redirect('/transactions/history');
            }
        } catch (\Exception $e) {
            Session::flashError('error', 'Purchase failed: ' . $e->getMessage());
            return redirect('/products/purchase?id=' . $productId);
        }
    }
    
    /**
     * Show user's transaction history
     */
    public function transactionHistory()
    {
        $this->requireAuth();
        
        $userId = Session::get('user')['id'];
        
        try {
            $transactions = $this->transactionModel->getUserTransactions($userId);
            
            return View::make('transactions/history', [
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            Session::flashError('error', 'Error loading transaction history: ' . $e->getMessage());
            return redirect('/');
        }
    }
    
    /**
     * Require user to be authenticated
     */
    private function requireAuth()
    {
        if (!Session::get('user')) {
            Session::flashError('error', 'Please log in to continue');
            redirect('/login');
            exit;
        }
    }
    
    /**
     * Require user to be admin
     */
    private function requireAdmin()
    {
        $user = Session::get('user');
        if (!$user) {
            Session::flashError('error', 'Please log in to continue');
            redirect('/login');
            exit;
        }
        
        // Get full user data to check role
        try {
            $userData = $this->userModel->find($user['id']);
            if (!$userData || $userData['role'] !== 'admin') {
                Session::flashError('error', 'Access denied. Admin privileges required.');
                redirect('/');
                exit;
            }
        } catch (\Exception $e) {
            Session::flashError('error', 'Authentication error');
            redirect('/login');
            exit;
        }
    }
}
