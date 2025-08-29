<?php
$title = 'My Purchase History - Vending Machine';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1><i class="fas fa-history"></i> My Purchase History</h1>
                <p class="text-muted">View all your past transactions</p>
            </div>
            <a href="/products" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Continue Shopping
            </a>
        </div>
    </div>
</div>

<?php if (empty($transactions)): ?>
    <div class="text-center py-5">
        <i class="fas fa-receipt fa-5x text-muted mb-4"></i>
        <h3>No Purchase History</h3>
        <p class="text-muted">You haven't made any purchases yet.</p>
        <a href="/products" class="btn btn-primary btn-lg">
            <i class="fas fa-shopping-cart"></i> Browse Products
        </a>
    </div>
<?php else: ?>
    <!-- Transactions List -->
    <div class="row">
        <?php foreach ($transactions as $transaction): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> 
                            <?= date('M j, Y g:i A', strtotime($transaction['transaction_date'])) ?>
                        </small>
                        <span class="badge <?= $transaction['status'] === 'completed' ? 'bg-success' : 'bg-warning' ?>">
                            <?= ucfirst($transaction['status']) ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <?php if ($transaction['image_url']): ?>
                                    <img src="<?= htmlspecialchars($transaction['image_url']) ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?= htmlspecialchars($transaction['product_name']) ?>"
                                         style="height: 80px; width: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 80px;">
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-8">
                                <h6 class="card-title"><?= htmlspecialchars($transaction['product_name']) ?></h6>
                                <div class="mb-2">
                                    <small class="text-muted">Quantity:</small> 
                                    <strong><?= $transaction['quantity'] ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Unit Price:</small> 
                                    <span class="fw-bold text-success">$<?= number_format($transaction['unit_price'], 3) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Paid:</span>
                            <span class="h5 mb-0 text-success">$<?= number_format($transaction['total_price'], 3) ?></span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Transaction #<?= $transaction['id'] ?>
                            </small>
                            <div>
                                <a href="/products/show?id=<?= $transaction['product_id'] ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Product
                                </a>
                                <?php if ($transaction['status'] === 'completed'): ?>
                                    <button class="btn btn-success btn-sm" disabled>
                                        <i class="fas fa-check"></i> Delivered
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Transaction Summary -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Purchase Summary</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="border-end">
                        <h4 class="text-primary"><?= count($transactions) ?></h4>
                        <small class="text-muted">Total Orders</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <h4 class="text-success">
                            $<?= number_format(array_sum(array_column($transactions, 'total_price')), 3) ?>
                        </h4>
                        <small class="text-muted">Total Spent</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <h4 class="text-info">
                            <?= array_sum(array_column($transactions, 'quantity')) ?>
                        </h4>
                        <small class="text-muted">Items Purchased</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <h4 class="text-warning">
                        $<?= count($transactions) > 0 ? number_format(array_sum(array_column($transactions, 'total_price')) / count($transactions), 3) : '0.000' ?>
                    </h4>
                    <small class="text-muted">Average Order</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorite Products -->
    <?php 
    $productCounts = [];
    foreach ($transactions as $transaction) {
        $productName = $transaction['product_name'];
        if (!isset($productCounts[$productName])) {
            $productCounts[$productName] = 0;
        }
        $productCounts[$productName] += $transaction['quantity'];
    }
    arsort($productCounts);
    $favoriteProducts = array_slice($productCounts, 0, 3, true);
    ?>

    <?php if (!empty($favoriteProducts)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-heart"></i> Your Favorite Products</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($favoriteProducts as $productName => $totalQuantity): ?>
                        <div class="col-md-4 text-center">
                            <h6><?= htmlspecialchars($productName) ?></h6>
                            <p class="text-muted">Purchased <?= $totalQuantity ?> times</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'layout.php';
?>
