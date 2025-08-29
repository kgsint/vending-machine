<?php
/**
 * Reusable Pagination Component
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param array $queryParams Additional query parameters to preserve
 * @param string $baseUrl Base URL for pagination links
 */
function renderPagination($currentPage, $totalPages, $queryParams = [], $baseUrl = '') {
    if ($totalPages <= 1) {
        return '';
    }
    
    // Build query string from parameters
    $queryString = '';
    if (!empty($queryParams)) {
        $queryString = '&' . http_build_query($queryParams);
    }
    
    ob_start();
    ?>
    <nav aria-label="Pagination Navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            
            <!-- First Page -->
            <?php if ($currentPage > 3): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=1<?= $queryString ?>" title="First Page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Previous Page -->
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage - 1 ?><?= $queryString ?>" title="Previous Page">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </span>
                </li>
            <?php endif; ?>
            
            <!-- Page Numbers -->
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            // Show ellipsis if we're not starting from page 1
            if ($startPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=1<?= $queryString ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif;
            
            // Display page numbers
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <?php if ($i === $currentPage): ?>
                        <span class="page-link">
                            <?= $i ?>
                            <span class="visually-hidden">(current)</span>
                        </span>
                    <?php else: ?>
                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?><?= $queryString ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endfor;
            
            // Show ellipsis if we're not ending at the last page
            if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $totalPages ?><?= $queryString ?>"><?= $totalPages ?></a>
                </li>
            <?php endif; ?>
            
            <!-- Next Page -->
            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage + 1 ?><?= $queryString ?>" title="Next Page">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            <?php endif; ?>
            
            <!-- Last Page -->
            <?php if ($currentPage < $totalPages - 2): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $totalPages ?><?= $queryString ?>" title="Last Page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            <?php endif; ?>
            
        </ul>
        
        <!-- Results Summary -->
        <div class="text-center text-muted mt-3">
            <small>
                Page <?= $currentPage ?> of <?= $totalPages ?>
                <?php if (isset($totalItems)): ?>
                    (<?= number_format($totalItems) ?> total items)
                <?php endif; ?>
            </small>
        </div>
    </nav>
    
    <?php
    return ob_get_clean();
}

/**
 * Enhanced pagination with per-page options
 */
function renderAdvancedPagination($currentPage, $totalPages, $totalItems, $itemsPerPage, $queryParams = [], $baseUrl = '') {
    if ($totalPages <= 1 && $totalItems <= $itemsPerPage) {
        return '';
    }
    
    // Build query string from parameters (excluding 'per_page')
    $baseParams = $queryParams;
    unset($baseParams['per_page']);
    $baseQueryString = !empty($baseParams) ? '&' . http_build_query($baseParams) : '';
    
    $startItem = ($currentPage - 1) * $itemsPerPage + 1;
    $endItem = min($currentPage * $itemsPerPage, $totalItems);
    
    ob_start();
    ?>
    <div class="row align-items-center mt-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3">
                <small class="text-muted">
                    Showing <?= number_format($startItem) ?> to <?= number_format($endItem) ?> 
                    of <?= number_format($totalItems) ?> entries
                </small>
                
                <!-- Per Page Selector -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Show:</small>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <?php foreach ([10, 20, 50, 100] as $option): ?>
                            <option value="<?= $option ?>" <?= $itemsPerPage == $option ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">per page</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Advanced Pagination">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        
                        <!-- Previous Page -->
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage - 1 ?><?= $baseQueryString ?>&per_page=<?= $itemsPerPage ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Page Numbers (condensed for advanced pagination) -->
                        <?php
                        $startPage = max(1, $currentPage - 1);
                        $endPage = min($totalPages, $currentPage + 1);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <?php if ($i === $currentPage): ?>
                                    <span class="page-link"><?= $i ?></span>
                                <?php else: ?>
                                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?><?= $baseQueryString ?>&per_page=<?= $itemsPerPage ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage + 1 ?><?= $baseQueryString ?>&per_page=<?= $itemsPerPage ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        <?php endif; ?>
                        
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function changePerPage(perPage) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', '1'); // Reset to first page
        window.location.href = url.toString();
    }
    </script>
    
    <?php
    return ob_get_clean();
}
?>
