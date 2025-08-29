<?php
$title = 'Edit User - Admin';
$oldValues = $_SESSION['_flash']['old'] ?? $user;
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-user-edit me-2"></i>
            Edit User: <?= htmlspecialchars($user['username']) ?>
        </h1>
        <p class="text-muted">Update user information and permissions</p>
    </div>
    <a href="/admin/users" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        Back to Users
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/users/update">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?= htmlspecialchars($oldValues['username']) ?>"
                                       placeholder="Enter username"
                                       required>
                                <div class="form-text">Unique identifier for the user</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($oldValues['email']) ?>"
                                       placeholder="user@example.com"
                                       required>
                                <div class="form-text">User's email address for login</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       minlength="6"
                                       placeholder="Leave empty to keep current password">
                                <div class="form-text">Only enter if you want to change the password</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">User Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="user" <?= $oldValues['role'] === 'user' ? 'selected' : '' ?>>
                                        User (Customer)
                                    </option>
                                    <option value="admin" <?= $oldValues['role'] === 'admin' ? 'selected' : '' ?>>
                                        Admin (Manager)
                                    </option>
                                </select>
                                <div class="form-text">Set user permissions level</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Update User
                        </button>
                        <a href="/admin/users" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Current User Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Current User Info
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td><?= $user['id'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
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
                    </tr>
                    <tr>
                        <td><strong>Joined:</strong></td>
                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td><?= date('M j, Y', strtotime($user['updated_at'])) ?></td>
                    </tr>
                </table>
                
                <?php if ($user['id'] == $_SESSION['user']['id']): ?>
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <small>This is your own account</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- User Role Guide -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Role Permissions
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <h6><i class="fas fa-user text-primary me-2"></i>User (Customer)</h6>
                    <ul>
                        <li>Browse and view products</li>
                        <li>Make purchases</li>
                        <li>View personal transaction history</li>
                        <li>Update profile information</li>
                    </ul>

                    <h6><i class="fas fa-crown text-warning me-2"></i>Admin (Manager)</h6>
                    <ul>
                        <li>All user permissions</li>
                        <li>Access admin dashboard</li>
                        <li>Manage products and inventory</li>
                        <li>View all transactions</li>
                        <li>Manage user accounts</li>
                        <li>System configuration</li>
                    </ul>
                    
                    <?php if ($user['role'] === 'admin' && $user['id'] == $_SESSION['user']['id']): ?>
                        <div class="alert alert-warning py-2 mt-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <small><strong>Warning:</strong> Changing your own role from admin will revoke your admin access.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('password').addEventListener('input', function() {
    const value = this.value;
    if (value && value.length < 6) {
        this.setCustomValidity('Password must be at least 6 characters long');
    } else {
        this.setCustomValidity('');
    }
});

// Role change warning
document.getElementById('role').addEventListener('change', function() {
    const warningElement = document.querySelector('.role-change-warning');
    if (warningElement) warningElement.remove();
    
    const currentUserRole = '<?= $user["role"] ?>';
    const isCurrentUser = <?= $user['id'] == $_SESSION['user']['id'] ? 'true' : 'false' ?>;
    
    if (this.value === 'user' && currentUserRole === 'admin' && isCurrentUser) {
        const warning = document.createElement('div');
        warning.className = 'alert alert-danger mt-2 role-change-warning';
        warning.innerHTML = '<small><i class="fas fa-exclamation-triangle me-1"></i><strong>Warning:</strong> You are about to remove your own admin privileges!</small>';
        this.parentNode.appendChild(warning);
    }
    
    if (this.value === 'admin' && currentUserRole === 'user') {
        const info = document.createElement('div');
        info.className = 'alert alert-warning mt-2 role-change-warning';
        info.innerHTML = '<small><i class="fas fa-info-circle me-1"></i>This user will gain full system access.</small>';
        this.parentNode.appendChild(info);
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'admin/layout.php';
?>
