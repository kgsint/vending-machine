<?php
$title = 'Add New Product - Admin';
$oldValues = $_SESSION['_flash']['old'] ?? [];
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-plus-circle me-2"></i>
            Add New Product
        </h1>
        <p class="text-muted">Add a new product to your vending machine inventory</p>
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
                <form method="POST" action="/admin/products/store">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($oldValues['name'] ?? '') ?>"
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
                                           value="<?= htmlspecialchars($oldValues['price'] ?? '') ?>"
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
                                <label for="quantity_available" class="form-label">Initial Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity_available" 
                                       name="quantity_available" 
                                       min="0"
                                       value="<?= htmlspecialchars($oldValues['quantity_available'] ?? '0') ?>"
                                       placeholder="0"
                                       required>
                                <div class="form-text">Number of units available for sale</div>
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
                                   <?= isset($oldValues['is_active']) ? 'checked' : 'checked' ?>>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Product</strong>
                            </label>
                            <div class="form-text">Customers can see and purchase active products</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>
                            Create Product
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
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Product Guidelines
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <h6>Product Name</h6>
                    <ul>
                        <li>Use clear, descriptive names</li>
                        <li>Include brand if relevant</li>
                        <li>Keep it concise</li>
                    </ul>

                    <h6>Pricing</h6>
                    <ul>
                        <li>Use competitive pricing</li>
                        <li>Consider your target market</li>
                        <li>Account for operating costs</li>
                    </ul>

                    <h6>Stock Management</h6>
                    <ul>
                        <li>Monitor inventory levels regularly</li>
                        <li>Set up low-stock alerts</li>
                        <li>Update quantities after restocking</li>
                    </ul>

                    <h6>Images</h6>
                    <ul>
                        <li>Use high-quality product photos</li>
                        <li>Ensure images load quickly</li>
                        <li>Show the actual product</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sample Products -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Sample Products
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="border-bottom pb-2 mb-2">
                        <strong>Coca Cola</strong><br>
                        Price: $3.99<br>
                        <span class="text-muted">Classic cola drink</span>
                    </div>
                    <div class="border-bottom pb-2 mb-2">
                        <strong>Pepsi</strong><br>
                        Price: $6.885<br>
                        <span class="text-muted">Refreshing cola beverage</span>
                    </div>
                    <div class="pb-2">
                        <strong>Water</strong><br>
                        Price: $0.50<br>
                        <span class="text-muted">Pure drinking water</span>
                    </div>
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
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'admin/layout.php';
?>
