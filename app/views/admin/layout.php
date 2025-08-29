<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Admin - Vending Machine" ?></title>

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom Admin Styles -->
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .admin-main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .btn {
            border-radius: 8px;
        }

        .table th {
            border-top: none;
            background-color: #f8f9fa;
        }

        .alert {
            border: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Sidebar -->
            <nav class="col-md-3 col-lg-2 admin-sidebar px-0">
                <div class="p-3">
                    <a href="/admin/dashboard" class="navbar-brand text-white text-decoration-none d-flex align-items-center">
                        <i class="fas fa-cog me-2"></i>
                        <span>Admin Panel</span>
                    </a>

                    <hr class="text-white-50">

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="/admin/dashboard" class="nav-link <?= str_contains(
                                $_SERVER["REQUEST_URI"],
                                "/admin/dashboard",
                            ) || $_SERVER["REQUEST_URI"] === "/admin"
                                ? "active"
                                : "" ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/admin/products" class="nav-link <?= str_contains(
                                $_SERVER["REQUEST_URI"],
                                "/admin/products",
                            )
                                ? "active"
                                : "" ?>">
                                <i class="fas fa-box"></i>
                                Products
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/admin/transactions" class="nav-link <?= str_contains(
                                $_SERVER["REQUEST_URI"],
                                "/admin/transactions",
                            )
                                ? "active"
                                : "" ?>">
                                <i class="fas fa-chart-line"></i>
                                Transactions
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/admin/users" class="nav-link <?= str_contains(
                                $_SERVER["REQUEST_URI"],
                                "/admin/users",
                            )
                                ? "active"
                                : "" ?>">
                                <i class="fas fa-users"></i>
                                Users
                            </a>
                        </li>

                        <hr class="text-white-50">

                        <li class="nav-item">
                            <a href="/" class="nav-link">
                                <i class="fas fa-home"></i>
                                Back to Website
                            </a>
                        </li>

                        <li class="nav-item">
                            <form method="POST" action="/logout" class="d-inline">
                                <button type="submit" class="nav-link btn btn-link text-start w-100" style="color: rgba(255, 255, 255, 0.8);">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 admin-main-content px-4 py-4">
                <!-- Alert Messages -->
                <?php if ($error = \Core\Session::error("error")): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success = \Core\Session::error("success")): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Main Content Area -->
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="/js/bootstrap.bundle.js"></script>

    <!-- Custom Admin Scripts -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Confirmation dialogs for delete actions
        document.querySelectorAll('[data-confirm]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
