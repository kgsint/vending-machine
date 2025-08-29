<?php
$title = "Transaction Management - Admin";
// Include pagination component
require_once VIEW_PATH . "../views/components/pagination.php";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-chart-line me-2"></i>
            Transaction Management
        </h1>
        <p class="text-muted">Monitor all sales and transaction activity</p>
    </div>
    <div>
        <span class="badge bg-info fs-6">
            Total: <?= $totalTransactions ?> transactions
        </span>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            All Transactions
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($transactions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No transactions found</h5>
                <p class="text-muted">Transactions will appear here once customers start making purchases.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary">#<?= $transaction[
                                        "id"
                                    ] ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars(
                                            $transaction["username"],
                                        ) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars(
                                            $transaction["email"],
                                        ) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(
                                        $transaction["product_name"],
                                    ) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= $transaction[
                                        "quantity"
                                    ] ?>x</span>
                                </td>
                                <td>
                                    $<?= number_format(
                                        $transaction["unit_price"],
                                        2,
                                    ) ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">$<?= number_format(
                                        $transaction["total_price"],
                                        2,
                                    ) ?></span>
                                </td>
                                <td>
                                    <?php $statusColor = match (
                                        $transaction["status"]
                                    ) {
                                        "completed" => "success",
                                        "pending" => "warning",
                                        "cancelled" => "danger",
                                        default => "secondary",
                                    }; ?>
                                    <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(
    $transaction["status"],
) ?></span>
                                </td>
                                <td>
                                    <div>
                                        <?= date(
                                            "M j, Y",
                                            strtotime(
                                                $transaction[
                                                    "transaction_date"
                                                ],
                                            ),
                                        ) ?>
                                        <div class="small text-muted"><?= date(
                                            "H:i:s",
                                            strtotime(
                                                $transaction[
                                                    "transaction_date"
                                                ],
                                            ),
                                        ) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="/admin/transactions/show?id=<?= $transaction[
                                        "id"
                                    ] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
                $totalTransactions,
                $perPage,
                [],
                "/admin/transactions",
            );
            ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . "admin/layout.php";


?>
