<?php
$title = 'Welcome - Vending Machine';
ob_start();
?>

<!-- Hero Section -->
<div class="jumbotron bg-primary text-white p-5 rounded mb-5">
    <div class="container text-center">
        <h1 class="display-4"><i class="fas fa-shopping-cart"></i> Welcome to Our Vending Machine</h1>
        <p class="lead">Fresh drinks, instant delivery, convenient payment</p>
        
        <?php if (!empty($_SESSION["user"])): ?>
            <div class="mt-4">
                <h5>Welcome back, <?= htmlspecialchars($_SESSION["user"]["username"]) ?>!</h5>
                <p class="mb-3">Ready to grab your favorite drink?</p>
                <a href="/products" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-shopping-cart"></i> Browse Products
                </a>
                <a href="/transactions/history" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-history"></i> My Orders
                </a>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <p class="mb-3">Sign in to start purchasing or browse our products</p>
                <a href="/login" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/products" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-eye"></i> Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Features Section -->
<div class="row mb-5">
    <div class="col-md-4 text-center mb-4">
        <div class="card h-100">
            <div class="card-body">
                <i class="fas fa-bolt fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Instant Service</h5>
                <p class="card-text">Select your drink and get it instantly. No waiting, no hassle.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-center mb-4">
        <div class="card h-100">
            <div class="card-body">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5 class="card-title">Secure Payment</h5>
                <p class="card-text">Safe and secure transactions with instant confirmation.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-center mb-4">
        <div class="card h-100">
            <div class="card-body">
                <i class="fas fa-leaf fa-3x text-info mb-3"></i>
                <h5 class="card-title">Fresh Products</h5>
                <p class="card-text">All beverages are kept fresh and at the perfect temperature.</p>
            </div>
        </div>
    </div>
</div>

<!-- Popular Products Preview -->
<div class="row">
    <div class="col-12">
        <h2 class="text-center mb-4">Popular Products</h2>
        <div class="row justify-content-center">
            <div class="col-md-3 text-center mb-3">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-tint fa-3x text-danger mb-3"></i>
                        <h6>Coke</h6>
                        <p class="text-success fw-bold">$3.99</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-tint fa-3x text-primary mb-3"></i>
                        <h6>Pepsi</h6>
                        <p class="text-success fw-bold">$6.885</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-tint fa-3x text-info mb-3"></i>
                        <h6>Water</h6>
                        <p class="text-success fw-bold">$0.50</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="/products" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right"></i> View All Products
            </a>
        </div>
    </div>
</div>

<!-- Statistics Section (if user is logged in) -->
<?php if (!empty($_SESSION["user"]) && $_SESSION["user"]["role"] === "admin"): ?>
    <div class="alert alert-info mt-5" role="alert">
        <h5><i class="fas fa-crown"></i> Admin Quick Access</h5>
        <p>Manage your vending machine system efficiently</p>
        <a href="/admin/products" class="btn btn-sm btn-outline-primary me-2">Manage Products</a>
        <a href="/admin/transactions" class="btn btn-sm btn-outline-primary">View Sales</a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'layout.php';
?>
