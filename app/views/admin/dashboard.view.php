<?php
$title = 'Admin Dashboard - Vending Machine';
ob_start();
?>

<div class="admin-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-primary">
                <i class="fas fa-crown me-2"></i>
                Admin Dashboard
            </h1>
            <p class="text-muted">Manage your vending machine system</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-success fs-6">
                Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?>
            </span>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Total Products</h6>
                        <h2 class="mb-0"><?= $totalProducts ?></h2>
                        <small><?= $activeProducts ?> active</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Total Users</h6>
                        <h2 class="mb-0"><?= $totalUsers ?></h2>
                        <small>Registered customers</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Transactions</h6>
                        <h2 class="mb-0"><?= $totalTransactions ?></h2>
                        <small>Total orders</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase">Monthly Revenue</h6>
                        <h2 class="mb-0">$<?= number_format($monthlySales['total_revenue'] ?? 0, 2) ?></h2>
                        <small><?= $monthlySales['total_transactions'] ?? 0 ?> orders this month</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="/admin/products/create" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-plus-circle me-2"></i>
                            Add New Product
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="/admin/users/create" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-user-plus me-2"></i>
                            Add New User
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="/admin/transactions" class="btn btn-outline-info btn-lg w-100">
                            <i class="fas fa-chart-line me-2"></i>
                            View Sales Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Transactions -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Transactions
                </h5>
                <a href="/admin/transactions" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentTransactions)): ?>
                    <p class="text-muted text-center">No transactions yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentTransactions, 0, 8) as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['username']) ?></td>
                                        <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                                        <td>$<?= number_format($transaction['total_price'], 2) ?></td>
                                        <td><?= date('M j, H:i', strtotime($transaction['transaction_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Top Products (This Month)
                </h5>
                <a href="/admin/products" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body">
                <?php if (empty($topProducts)): ?>
                    <p class="text-muted text-center">No sales data yet</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($topProducts, 0, 5) as $index => $product): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="badge bg-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light text-dark') ?> me-2">
                                        #<?= $index + 1 ?>
                                    </span>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <div class="small text-muted">
                                        <?= $product['total_sold'] ?> sold • $<?= number_format($product['total_revenue'], 2) ?> revenue
                                    </div>
                                </div>
                                <span class="text-success fw-bold">$<?= number_format($product['price'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Cards -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-box fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Product Management</h5>
                <p class="card-text">Add, edit, and manage your vending machine inventory.</p>
                <a href="/admin/products" class="btn btn-primary">Manage Products</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-success mb-3"></i>
                <h5 class="card-title">User Management</h5>
                <p class="card-text">View and manage customer accounts and admin users.</p>
                <a href="/admin/users" class="btn btn-success">Manage Users</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 border-info">
            <div class="card-body text-center">
                <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                <h5 class="card-title">Sales Analytics</h5>
                <p class="card-text">Monitor transactions, sales reports, and revenue.</p>
                <a href="/admin/transactions" class="btn btn-info">View Analytics</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'admin/layout.php';
?>
