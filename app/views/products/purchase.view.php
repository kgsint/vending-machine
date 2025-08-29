<?php
$title = 'Purchase ' . htmlspecialchars($product['name']) . ' - Vending Machine';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="/products/show?id=<?= $product['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Product
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Complete Your Purchase</h4>
            </div>
            <div class="card-body">
                <!-- Product Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 style="height: 150px; width: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 150px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="price-display">$<?= number_format($product['price'], 3) ?> per unit</div>
                        <small class="text-muted"><?= $product['quantity_available'] ?> available</small>
                    </div>
                </div>

                <!-- Purchase Form -->
                <form method="POST" action="/products/process-purchase" id="purchaseForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">
                                    <i class="fas fa-hashtag"></i> Quantity
                                </label>
                                <select name="quantity" id="quantity" class="form-select" required onchange="updateTotal()">
                                    <?php for ($i = 1; $i <= min(10, $product['quantity_available']); $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calculator"></i> Total Price
                                </label>
                                <div class="form-control bg-light" id="totalPrice">
                                    $<?= number_format($product['price'], 3) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mb-4">
                        <h6><i class="fas fa-user"></i> Customer Information</h6>
                        <div class="bg-light p-3 rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> <?= htmlspecialchars($_SESSION['user']['username']) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Email:</strong> <?= htmlspecialchars($_SESSION['user']['email']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="termsAccepted" required>
                            <label class="form-check-label" for="termsAccepted">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                            </label>
                        </div>
                    </div>

                    <!-- Purchase Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="purchaseBtn">
                            <i class="fas fa-credit-card"></i> Complete Purchase
                        </button>
                        <a href="/products" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                        <h6>Secure Payment</h6>
                        <small class="text-muted">Your payment is processed securely</small>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-bolt fa-2x text-warning mb-2"></i>
                        <h6>Instant Delivery</h6>
                        <small class="text-muted">Product dispensed immediately</small>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-undo-alt fa-2x text-info mb-2"></i>
                        <h6>Satisfaction Guaranteed</h6>
                        <small class="text-muted">Quality products every time</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Vending Machine Purchase Terms</h6>
                <ul>
                    <li>All sales are final once the product is dispensed</li>
                    <li>Products are sold as-is with quality guarantee</li>
                    <li>In case of machine malfunction, contact support immediately</li>
                    <li>Refunds are available only for machine errors</li>
                    <li>One purchase per transaction</li>
                    <li>Valid payment required before dispensing</li>
                </ul>
                <p><small class="text-muted">Last updated: <?= date('M j, Y') ?></small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const productPrice = <?= $product['price'] ?>;

function updateTotal() {
    const quantity = document.getElementById('quantity').value;
    const total = (productPrice * quantity).toFixed(3);
    document.getElementById('totalPrice').textContent = '$' + total;
}

// Form validation
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const termsAccepted = document.getElementById('termsAccepted').checked;
    const quantity = document.getElementById('quantity').value;
    
    if (!termsAccepted) {
        e.preventDefault();
        alert('Please accept the terms and conditions to continue.');
        return false;
    }
    
    if (quantity < 1) {
        e.preventDefault();
        alert('Please select a valid quantity.');
        return false;
    }
    
    // Show loading state
    const btn = document.getElementById('purchaseBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'layout.php';
?>
