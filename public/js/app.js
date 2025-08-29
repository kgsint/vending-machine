// Vending Machine JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupFormValidation();
    setupPurchaseForm();
    setupProductForms();
    setupLoadingStates();
    setupNotifications();
}

// Form Validation
function setupFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        errorMessage = `${getFieldLabel(field)} is required`;
        isValid = false;
    }
    // Email validation
    else if (fieldType === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email address';
            isValid = false;
        }
    }
    // Password validation
    else if (fieldType === 'password' && value) {
        if (value.length < 6) {
            errorMessage = 'Password must be at least 6 characters long';
            isValid = false;
        }
    }
    // Number validation
    else if (fieldType === 'number' && value) {
        const numValue = parseFloat(value);
        if (isNaN(numValue)) {
            errorMessage = 'Please enter a valid number';
            isValid = false;
        }
        
        // Price validation
        if (fieldName === 'price' && numValue <= 0) {
            errorMessage = 'Price must be greater than 0';
            isValid = false;
        }
        
        // Quantity validation
        if (fieldName === 'quantity_available' && numValue < 0) {
            errorMessage = 'Quantity cannot be negative';
            isValid = false;
        }
        
        // Purchase quantity validation
        if (fieldName === 'quantity' && numValue <= 0) {
            errorMessage = 'Quantity must be at least 1';
            isValid = false;
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

function getFieldLabel(field) {
    const label = field.parentNode.querySelector('label');
    return label ? label.textContent.replace(':', '') : field.name;
}

// Purchase Form Handling
function setupPurchaseForm() {
    const purchaseForm = document.getElementById('purchaseForm');
    if (purchaseForm) {
        purchaseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            processPurchase(this);
        });
    }
    
    // Quantity input validation
    const quantityInput = document.getElementById('quantity');
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const quantity = parseInt(this.value);
            const maxQuantity = parseInt(this.getAttribute('max'));
            
            if (quantity > maxQuantity) {
                showFieldError(this, `Only ${maxQuantity} items available`);
            } else {
                clearFieldError(this);
            }
        });
    }
}

function processPurchase(form) {
    const formData = new FormData(form);
    const quantity = parseInt(formData.get('quantity'));
    const productId = formData.get('product_id');
    
    // Show loading state
    showLoadingSpinner(form);
    disableForm(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingSpinner(form);
        enableForm(form);
        
        if (data.success) {
            showNotification('Purchase successful!', 'success');
            // Optionally redirect or update UI
            setTimeout(() => {
                window.location.href = '/transactions/history';
            }, 2000);
        } else {
            showNotification(data.message || 'Purchase failed', 'error');
        }
    })
    .catch(error => {
        hideLoadingSpinner(form);
        enableForm(form);
        showNotification('An error occurred. Please try again.', 'error');
        console.error('Purchase error:', error);
    });
}

// Product Form Handling
function setupProductForms() {
    // Product creation/edit forms
    const productForms = document.querySelectorAll('form[data-product-form]');
    
    productForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateProductForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateProductForm(form) {
    const name = form.querySelector('[name="name"]').value.trim();
    const price = parseFloat(form.querySelector('[name="price"]').value);
    const quantity = parseInt(form.querySelector('[name="quantity_available"]').value);
    
    let isValid = true;
    
    if (!name) {
        showNotification('Product name is required', 'error');
        isValid = false;
    }
    
    if (isNaN(price) || price <= 0) {
        showNotification('Price must be a positive number', 'error');
        isValid = false;
    }
    
    if (isNaN(quantity) || quantity < 0) {
        showNotification('Quantity must be a non-negative number', 'error');
        isValid = false;
    }
    
    return isValid;
}

// Loading States
function setupLoadingStates() {
    // Add loading spinners to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (!form.querySelector('.loading-spinner')) {
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner';
            form.appendChild(spinner);
        }
    });
}

function showLoadingSpinner(form) {
    const spinner = form.querySelector('.loading-spinner');
    if (spinner) {
        spinner.style.display = 'block';
    }
}

function hideLoadingSpinner(form) {
    const spinner = form.querySelector('.loading-spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

function disableForm(form) {
    const inputs = form.querySelectorAll('input, textarea, select, button');
    inputs.forEach(input => {
        input.disabled = true;
    });
}

function enableForm(form) {
    const inputs = form.querySelectorAll('input, textarea, select, button');
    inputs.forEach(input => {
        input.disabled = false;
    });
}

// Notifications
function setupNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    notification.style.cssText = `
        margin-bottom: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    container.appendChild(notification);
    
    // Auto-remove after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.add('fade');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, duration);
    
    // Add slide-in animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    if (!document.querySelector('style[data-notification-styles]')) {
        style.setAttribute('data-notification-styles', 'true');
        document.head.appendChild(style);
    }
}

// Search Functionality
function setupSearch() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    
    if (searchForm && searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    searchForm.submit();
                }
            }, 300);
        });
    }
}

// Stock Level Indicators
function updateStockIndicators() {
    const stockBadges = document.querySelectorAll('.stock-badge');
    
    stockBadges.forEach(badge => {
        const quantity = parseInt(badge.textContent);
        
        if (quantity === 0) {
            badge.className = 'stock-badge out-of-stock';
            badge.textContent = 'Out of Stock';
        } else if (quantity <= 5) {
            badge.className = 'stock-badge low-stock';
            badge.textContent = `Low Stock: ${quantity}`;
        } else {
            badge.className = 'stock-badge';
            badge.textContent = `Stock: ${quantity}`;
        }
    });
}

// Initialize stock indicators on page load
document.addEventListener('DOMContentLoaded', updateStockIndicators);

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// AJAX Helper
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// Initialize all functionality
setupSearch();
updateStockIndicators();
