<?php
$title = 'Edit Product - Admin';
$oldValues = $_SESSION['_flash']['old'] ?? $product;
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-edit me-2"></i>
            Edit Product: <?= htmlspecialchars($product['name']) ?>
        </h1>
        <p class="text-muted">Update product information and inventory</p>
    </div>
    <a href="/admin/products" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        Back to Products
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/products/update">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($oldValues['name']) ?>"
                                       placeholder="e.g. Coca Cola"
                                       required>
                                <div class="form-text">Enter a clear, descriptive product name</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (USD) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="price" 
                                           name="price" 
                                           step="0.01" 
                                           min="0"
                                           value="<?= htmlspecialchars($oldValues['price']) ?>"
                                           placeholder="0.00"
                                           required>
                                </div>
                                <div class="form-text">Set the selling price for this product</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity_available" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity_available" 
                                       name="quantity_available" 
                                       min="0"
                                       value="<?= htmlspecialchars($oldValues['quantity_available']) ?>"
                                       placeholder="0"
                                       required>
                                <div class="form-text">Current number of units available for sale</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Product Image URL</label>
                                <input type="url" 
                                       class="form-control" 
                                       id="image_url" 
                                       name="image_url" 
                                       value="<?= htmlspecialchars($oldValues['image_url'] ?? '') ?>"
                                       placeholder="https://example.com/product-image.jpg">
                                <div class="form-text">Optional: URL to product image</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Describe the product (optional)..."><?= htmlspecialchars($oldValues['description'] ?? '') ?></textarea>
                        <div class="form-text">Optional product description for customers</div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   <?= $oldValues['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Product</strong>
                            </label>
                            <div class="form-text">Customers can see and purchase active products</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Update Product
                        </button>
                        <a href="/admin/products" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Current Product Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Current Product Info
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($product['image_url']): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="img-fluid rounded" 
                             style="max-height: 120px;">
                    <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                             style="height: 120px;">
                            <i class="fas fa-box fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td><?= $product['id'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <?php if ($product['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Stock Level:</strong></td>
                        <td>
                            <span class="badge bg-<?= $product['quantity_available'] < 10 ? 'warning' : ($product['quantity_available'] > 0 ? 'success' : 'danger') ?>">
                                <?= $product['quantity_available'] ?> units
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td><?= date('M j, Y', strtotime($product['updated_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Stock Management Tips -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Stock Management Tips
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Low Stock Alert:</strong> Consider restocking when quantity falls below 10 units.
                    </div>
                    
                    <h6>Inventory Best Practices:</h6>
                    <ul>
                        <li>Monitor stock levels regularly</li>
                        <li>Update quantities after restocking</li>
                        <li>Track sales patterns</li>
                        <li>Plan ahead for popular items</li>
                        <li>Set alerts for low stock</li>
                    </ul>

                    <h6>Pricing Strategy:</h6>
                    <ul>
                        <li>Review competitor pricing</li>
                        <li>Consider seasonal adjustments</li>
                        <li>Factor in operating costs</li>
                        <li>Test different price points</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('price').addEventListener('input', function() {
    const value = parseFloat(this.value);
    if (value < 0) {
        this.setCustomValidity('Price must be positive');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('quantity_available').addEventListener('input', function() {
    const value = parseInt(this.value);
    if (value < 0) {
        this.setCustomValidity('Quantity cannot be negative');
    } else {
        this.setCustomValidity('');
    }
    
    // Show low stock warning
    const currentQuantity = parseInt(this.value);
    if (currentQuantity < 10 && currentQuantity > 0) {
        this.style.borderColor = '#ffc107';
        this.nextElementSibling.innerHTML = '<small class="text-warning">⚠️ Low stock warning</small>';
    } else {
        this.style.borderColor = '';
        this.nextElementSibling.innerHTML = 'Current number of units available for sale';
    }
});

// Image preview
document.getElementById('image_url').addEventListener('input', function() {
    const url = this.value;
    // You could add image preview functionality here
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'admin/layout.php';
?>
