<?php
$title = 'Products - Vending Machine';
// Include pagination component
require_once VIEW_PATH . '../views/components/pagination.php';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="fas fa-shopping-cart"></i> Products</h1>
        <p class="text-muted">Choose from our selection of refreshing drinks</p>
    </div>
    <div class="col-md-6">
        <!-- Search and Filter -->
        <form method="GET" class="d-flex flex-column flex-md-row gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <select name="sort" class="form-select">
                <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Name</option>
                <option value="price" <?= $sortBy === 'price' ? 'selected' : '' ?>>Price</option>
                <option value="quantity_available" <?= $sortBy === 'quantity_available' ? 'selected' : '' ?>>Stock</option>
                <option value="created_at" <?= $sortBy === 'created_at' ? 'selected' : '' ?>>Date Added</option>
            </select>
            <select name="order" class="form-select">
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>A-Z / Low-High</option>
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Z-A / High-Low</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
        <h3>No products found</h3>
        <p class="text-muted">
            <?php if ($search): ?>
                No products match your search term "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                There are no products available at the moment.
            <?php endif; ?>
        </p>
        <?php if ($search): ?>
            <a href="/products" class="btn btn-primary">View All Products</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Products Grid -->
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Stock Badge -->
                        <span class="badge stock-badge <?= $product['quantity_available'] > 10 ? 'bg-success' : ($product['quantity_available'] > 0 ? 'bg-warning' : 'bg-danger') ?>">
                            <?= $product['quantity_available'] ?> left
                        </span>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        
                        <?php if ($product['description']): ?>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>
                                <?= strlen($product['description']) > 80 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <div class="price-display mb-2">
                                $<?= number_format($product['price'], 3) ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="/products/show?id=<?= $product['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <?php if ($product['quantity_available'] > 0 && !empty($_SESSION['user'])): ?>
                                    <a href="/products/purchase?id=<?= $product['id'] ?>" 
                                       class="btn btn-success btn-sm flex-fill">
                                        <i class="fas fa-shopping-cart"></i> Buy
                                    </a>
                                <?php elseif ($product['quantity_available'] == 0): ?>
                                    <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                        <i class="fas fa-times"></i> Out of Stock
                                    </button>
                                <?php elseif (empty($_SESSION['user'])): ?>
                                    <a href="/login" class="btn btn-warning btn-sm flex-fill">
                                        <i class="fas fa-sign-in-alt"></i> Login to Buy
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Products pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>&sort=<?= $sortBy ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sortBy ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>&sort=<?= $sortBy ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Results Summary -->
    <div class="text-center text-muted mt-3">
        <small>
            Showing <?= count($products) ?> of <?= $totalProducts ?> products
            <?php if ($search): ?>
                for "<?= htmlspecialchars($search) ?>"
            <?php endif; ?>
        </small>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'layout.php';
?>
