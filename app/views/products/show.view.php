<?php
$title = htmlspecialchars($product['name']) . ' - Vending Machine';
ob_start();
?>

<div class="row">
    <!-- Back Button -->
    <div class="col-12 mb-3">
        <a href="/products" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <!-- Product Image -->
        <div class="card">
            <?php if ($product['image_url']): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     style="height: 400px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <div class="text-center">
                        <i class="fas fa-image fa-5x text-muted mb-3"></i>
                        <p class="text-muted">No image available</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Product Details -->
        <div class="card">
            <div class="card-body">
                <h1 class="card-title h2"><?= htmlspecialchars($product['name']) ?></h1>
                
                <!-- Price -->
                <div class="price-display mb-3">
                    $<?= number_format($product['price'], 3) ?>
                </div>
                
                <!-- Stock Status -->
                <div class="mb-3">
                    <?php if ($product['quantity_available'] > 10): ?>
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-check-circle"></i> <?= $product['quantity_available'] ?> in stock
                        </span>
                    <?php elseif ($product['quantity_available'] > 0): ?>
                        <span class="badge bg-warning fs-6">
                            <i class="fas fa-exclamation-triangle"></i> Only <?= $product['quantity_available'] ?> left
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger fs-6">
                            <i class="fas fa-times-circle"></i> Out of stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <?php if ($product['description']): ?>
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p class="text-muted"><?= htmlspecialchars($product['description']) ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Purchase Options -->
                <div class="d-grid gap-2">
                    <?php if ($product['quantity_available'] > 0): ?>
                        <?php if (!empty($_SESSION['user'])): ?>
                            <a href="/products/purchase?id=<?= $product['id'] ?>" 
                               class="btn btn-success btn-lg">
                                <i class="fas fa-shopping-cart"></i> Purchase Now
                            </a>
                            
                            <!-- Quick Purchase Buttons -->
                            <div class="row mt-3">
                                <?php for ($i = 1; $i <= min(3, $product['quantity_available']); $i++): ?>
                                    <div class="col">
                                        <form method="POST" action="/products/process-purchase" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="quantity" value="<?= $i ?>">
                                            <button type="submit" class="btn btn-outline-success w-100">
                                                Buy <?= $i ?> for $<?= number_format($product['price'] * $i, 3) ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php else: ?>
                            <a href="/login" class="btn btn-warning btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login to Purchase
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-times"></i> Currently Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Product Meta -->
                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted">
                        <strong>Product ID:</strong> #<?= $product['id'] ?><br>
                        <strong>Added:</strong> <?= date('M j, Y', strtotime($product['created_at'])) ?>
                        <?php if ($product['updated_at'] !== $product['created_at']): ?>
                            <br><strong>Updated:</strong> <?= date('M j, Y', strtotime($product['updated_at'])) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Additional Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Freshness Guarantee</h6>
                        <p class="text-muted small">All our beverages are fresh and stored at optimal temperature.</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Instant Delivery</h6>
                        <p class="text-muted small">Purchase and receive your item immediately from our vending machine.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'layout.php';
?>
