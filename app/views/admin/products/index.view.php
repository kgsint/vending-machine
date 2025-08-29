<?php
$title = "Manage Products - Admin";
// Include pagination component
require_once VIEW_PATH . "../views/components/pagination.php";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-box me-2"></i>
            Product Management
        </h1>
        <p class="text-muted">Manage your vending machine inventory</p>
    </div>
    <a href="/admin/products/create" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>
        Add New Product
    </a>
</div>

<!-- Products Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="text-primary"><?= $totalProducts ?></h5>
                <p class="mb-0 small text-muted">Total Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="text-success"><?= count(
                    array_filter($products, fn($p) => $p["is_active"]),
                ) ?></h5>
                <p class="mb-0 small text-muted">Active Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="text-warning"><?= array_sum(
                    array_column($products, "quantity_available"),
                ) ?></h5>
                <p class="mb-0 small text-muted">Total Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="text-info"><?= count(
                    array_filter(
                        $products,
                        fn($p) => $p["quantity_available"] < 10,
                    ),
                ) ?></h5>
                <p class="mb-0 small text-muted">Low Stock Items</p>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            All Products
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Start by adding your first product to the vending machine.</p>
                <a href="/admin/products/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add First Product
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($product["image_url"]): ?>
                                            <img src="<?= htmlspecialchars(
                                                $product["image_url"],
                                            ) ?>"
                                                 alt="<?= htmlspecialchars(
                                                     $product["name"],
                                                 ) ?>"
                                                 class="me-3 rounded"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="me-3 bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars(
                                                $product["name"],
                                            ) ?></strong>
                                            <?php if (
                                                $product["description"]
                                            ): ?>
                                                <div class="small text-muted">
                                                    <?= htmlspecialchars(
                                                        substr(
                                                            $product[
                                                                "description"
                                                            ],
                                                            0,
                                                            50,
                                                        ),
                                                    ) ?>...
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">$<?= number_format(
                                        $product["price"],
                                        2,
                                    ) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $product[
                                        "quantity_available"
                                    ] < 10
                                        ? "warning"
                                        : ($product["quantity_available"] > 0
                                            ? "success"
                                            : "danger") ?>">
                                        <?= $product[
                                            "quantity_available"
                                        ] ?> units
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product["is_active"]): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date(
                                        "M j, Y",
                                        strtotime($product["created_at"]),
                                    ) ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/products/edit?id=<?= $product[
                                            "id"
                                        ] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="/admin/products/delete" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $product[
                                                "id"
                                            ] ?>">
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-confirm="Are you sure you want to delete '<?= htmlspecialchars(
                                                        $product["name"],
                                                    ) ?>'?">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            <?php
            $perPage = $_GET["per_page"] ?? 10;
            echo renderAdvancedPagination(
                $currentPage,
                $totalPages,
                $totalProducts,
                $perPage,
                [],
                "/admin/products",
            );
            ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . "admin/layout.php";


?>
