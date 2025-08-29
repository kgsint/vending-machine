<?php
$title = "User Management - Admin";
// Include pagination component
require_once VIEW_PATH . "../views/components/pagination.php";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-users me-2"></i>
            User Management
        </h1>
        <p class="text-muted">Manage customer accounts and admin users</p>
    </div>
    <a href="/admin/users/create" class="btn btn-success">
        <i class="fas fa-user-plus me-2"></i>
        Add New User
    </a>
</div>

<!-- Users Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="text-primary"><?= $totalUsers ?></h5>
                <p class="mb-0 small text-muted">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="text-warning"><?= count(
                    array_filter($users, fn($u) => $u["role"] === "admin"),
                ) ?></h5>
                <p class="mb-0 small text-muted">Admin Users</p>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            All Users
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No users found</h5>
                <p class="text-muted">Start by adding your first user.</p>
                <a href="/admin/users/create" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>
                    Add First User
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 bg-light rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars(
                                                $user["username"],
                                            ) ?></strong>
                                            <?php if (
                                                $user["id"] ==
                                                $_SESSION["user"]["id"]
                                            ): ?>
                                                <span class="badge bg-info ms-2">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user["email"]) ?></td>
                                <td>
                                    <?php if ($user["role"] === "admin"): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-crown me-1"></i>
                                            Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-user me-1"></i>
                                            User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date(
                                        "M j, Y",
                                        strtotime($user["created_at"]),
                                    ) ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/users/edit?id=<?= $user[
                                            "id"
                                        ] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (
                                            $user["id"] !=
                                            $_SESSION["user"]["id"]
                                        ): ?>
                                            <form method="POST" action="/admin/users/delete" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $user[
                                                    "id"
                                                ] ?>">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-confirm="Are you sure you want to delete user '<?= htmlspecialchars(
                                                            $user["username"],
                                                        ) ?>'?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete yourself">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
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
                $totalUsers,
                $perPage,
                [],
                "/admin/users",
            );
            ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . "admin/layout.php";

?>
