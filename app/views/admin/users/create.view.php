<?php
$title = 'Add New User - Admin';
$oldValues = $_SESSION['_flash']['old'] ?? [];
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 text-primary">
            <i class="fas fa-user-plus me-2"></i>
            Add New User
        </h1>
        <p class="text-muted">Create a new user account</p>
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
                <form method="POST" action="/admin/users/store">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?= htmlspecialchars($oldValues['username'] ?? '') ?>"
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
                                       value="<?= htmlspecialchars($oldValues['email'] ?? '') ?>"
                                       placeholder="user@example.com"
                                       required>
                                <div class="form-text">User's email address for login</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       minlength="6"
                                       placeholder="Minimum 6 characters"
                                       required>
                                <div class="form-text">Secure password for user login</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">User Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="user" <?= ($oldValues['role'] ?? '') === 'user' ? 'selected' : '' ?>>
                                        User (Customer)
                                    </option>
                                    <option value="admin" <?= ($oldValues['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                        Admin (Manager)
                                    </option>
                                </select>
                                <div class="form-text">Set user permissions level</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>
                            Create User
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
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    User Roles
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <h6><i class="fas fa-user text-primary me-2"></i>User (Customer)</h6>
                    <ul>
                        <li>Can browse products</li>
                        <li>Can make purchases</li>
                        <li>Can view transaction history</li>
                        <li>Cannot access admin panel</li>
                    </ul>

                    <h6><i class="fas fa-crown text-warning me-2"></i>Admin (Manager)</h6>
                    <ul>
                        <li>Full system access</li>
                        <li>Can manage products</li>
                        <li>Can view all transactions</li>
                        <li>Can manage users</li>
                        <li>Can access admin dashboard</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Security Guidelines
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <h6>Password Requirements:</h6>
                    <ul>
                        <li>Minimum 6 characters</li>
                        <li>Use strong passwords</li>
                        <li>Avoid common passwords</li>
                        <li>Include numbers and symbols</li>
                    </ul>

                    <h6>Account Security:</h6>
                    <ul>
                        <li>Use unique usernames</li>
                        <li>Verify email addresses</li>
                        <li>Grant minimum necessary permissions</li>
                        <li>Review user access regularly</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('password').addEventListener('input', function() {
    const value = this.value;
    if (value.length < 6) {
        this.setCustomValidity('Password must be at least 6 characters long');
    } else {
        this.setCustomValidity('');
    }
});

// Role selection helper
document.getElementById('role').addEventListener('change', function() {
    const roleInfo = document.querySelector('.role-info');
    if (roleInfo) roleInfo.remove();
    
    if (this.value === 'admin') {
        const info = document.createElement('div');
        info.className = 'alert alert-warning mt-2 role-info';
        info.innerHTML = '<small><i class="fas fa-exclamation-triangle me-1"></i>Admin users have full system access. Grant this role carefully.</small>';
        this.parentNode.appendChild(info);
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . 'admin/layout.php';
?>
