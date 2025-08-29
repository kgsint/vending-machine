<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagination Component Demo - Vending Machine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">
            <i class="fas fa-list me-2"></i>
            Pagination Component Demo
        </h1>
        
        <!-- Basic Pagination Example -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Basic Pagination</h3>
                        <p class="mb-0 text-muted">Standard pagination with page numbers and navigation</p>
                    </div>
                    <div class="card-body">
                        <?php
                        require_once 'pagination.php';
                        echo renderPagination(5, 15, ['sort' => 'name', 'order' => 'asc'], '/demo');
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Pagination Example -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Advanced Pagination with Per-Page Options</h3>
                        <p class="mb-0 text-muted">Enhanced pagination with items per page selector and detailed info</p>
                    </div>
                    <div class="card-body">
                        <?php
                        echo renderAdvancedPagination(
                            3, 
                            8, 
                            156, 
                            20, 
                            ['category' => 'beverages', 'sort' => 'price'], 
                            '/admin/products'
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Different States -->
        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4>First Page</h4>
                    </div>
                    <div class="card-body">
                        <?php echo renderPagination(1, 10, [], '/demo'); ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Last Page</h4>
                    </div>
                    <div class="card-body">
                        <?php echo renderPagination(10, 10, [], '/demo'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Different Page Counts -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Small Dataset (3 pages)</h5>
                    </div>
                    <div class="card-body">
                        <?php echo renderPagination(2, 3, [], '/demo'); ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Medium Dataset (25 pages)</h5>
                    </div>
                    <div class="card-body">
                        <?php echo renderPagination(12, 25, [], '/demo'); ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Large Dataset (100 pages)</h5>
                    </div>
                    <div class="card-body">
                        <?php echo renderPagination(47, 100, [], '/demo'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Examples -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Usage Examples</h3>
                    </div>
                    <div class="card-body">
                        <h4>1. Basic Pagination</h4>
                        <pre><code>&lt;?php
// Include the pagination component
require_once VIEW_PATH . '../views/components/pagination.php';

// Render basic pagination
echo renderPagination(
    $currentPage,    // Current page number
    $totalPages,     // Total number of pages
    ['sort' => 'name', 'filter' => 'active'], // Query parameters to preserve
    '/products'      // Base URL
);
?&gt;</code></pre>

                        <h4 class="mt-4">2. Advanced Pagination</h4>
                        <pre><code>&lt;?php
// Render advanced pagination with per-page options
echo renderAdvancedPagination(
    $currentPage,    // Current page number
    $totalPages,     // Total number of pages
    $totalItems,     // Total number of items
    $itemsPerPage,   // Items per page
    ['category' => 'drinks'], // Query parameters to preserve
    '/admin/products' // Base URL
);
?&gt;</code></pre>

                        <h4 class="mt-4">3. Controller Integration</h4>
                        <pre><code>// In your controller
public function index() {
    $page = (int) ($_GET['page'] ?? 1);
    $perPage = (int) ($_GET['per_page'] ?? 20);
    
    // Validate per-page options
    $allowedPerPage = [10, 20, 50, 100];
    if (!in_array($perPage, $allowedPerPage)) {
        $perPage = 20;
    }
    
    $limit = $perPage;
    $offset = ($page - 1) * $limit;
    
    $items = $this->model->all($limit, $offset);
    $totalItems = $this->model->count();
    $totalPages = ceil($totalItems / $limit);
    
    return View::make('items/index', [
        'items' => $items,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems,
        'perPage' => $perPage,
    ]);
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Features</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fas fa-check-circle text-success me-2"></i>Basic Features</h4>
                                <ul>
                                    <li>Previous/Next navigation</li>
                                    <li>Page number links</li>
                                    <li>Current page highlighting</li>
                                    <li>Ellipsis for large page counts</li>
                                    <li>First/Last page shortcuts</li>
                                    <li>Query parameter preservation</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4><i class="fas fa-star text-warning me-2"></i>Advanced Features</h4>
                                <ul>
                                    <li>Items per page selector</li>
                                    <li>Detailed results summary</li>
                                    <li>JavaScript per-page changing</li>
                                    <li>Responsive design</li>
                                    <li>Bootstrap 5 styling</li>
                                    <li>Accessibility features</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
