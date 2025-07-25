// Restaurant Management System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initModals();
    initForms();
    initTables();
    initAlerts();
});

// Sidebar functionality
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }
    
    // Set active menu item
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
}

// Modal functionality
function initModals() {
    // Modal open/close handlers
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modals = document.querySelectorAll('.modal');
    const modalCloseBtns = document.querySelectorAll('.modal-close, [data-dismiss="modal"]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                showModal(modal);
            }
        });
    });
    
    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                hideModal(modal);
            }
        });
    });
    
    // Close modal on background click
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal(this);
            }
        });
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                hideModal(openModal);
            }
        }
    });
}

function showModal(modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function hideModal(modal) {
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Form functionality
function initForms() {
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
    
    // Auto-save forms
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');
    autoSaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', debounce(function() {
                autoSaveForm(form);
            }, 1000));
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
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
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    else if (fieldType === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    
    // Phone validation
    else if (field.hasAttribute('data-phone') && value) {
        const phoneRegex = /^[\+]?[\d\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number';
        }
    }
    
    // Password validation
    else if (fieldType === 'password' && value && field.hasAttribute('data-min-length')) {
        const minLength = parseInt(field.getAttribute('data-min-length'));
        if (value.length < minLength) {
            isValid = false;
            errorMessage = `Password must be at least ${minLength} characters long`;
        }
    }
    
    // Confirm password validation
    else if (field.hasAttribute('data-confirm')) {
        const confirmField = document.getElementById(field.getAttribute('data-confirm'));
        if (confirmField && value !== confirmField.value) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorDiv = field.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    
    errorDiv.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.remove();
    }
}

function autoSaveForm(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Save to localStorage
    const formId = form.id || 'autosave-form';
    localStorage.setItem(`autosave-${formId}`, JSON.stringify(data));
    
    // Show auto-save indicator
    showNotification('Draft saved automatically', 'info', 2000);
}

// Table functionality
function initTables() {
    // Sortable tables
    const sortableTables = document.querySelectorAll('table[data-sortable]');
    
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, this);
            });
        });
    });
    
    // Searchable tables
    const searchInputs = document.querySelectorAll('input[data-table-search]');
    
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('input', debounce(function() {
                searchTable(table, this.value);
            }, 300));
        }
    });
}

function sortTable(table, header) {
    const column = parseInt(header.getAttribute('data-sort'));
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isAscending = !header.classList.contains('sort-asc');
    
    // Clear all sort classes
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Add sort class to current header
    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    
    // Sort rows
    rows.sort((a, b) => {
        const aVal = a.cells[column].textContent.trim();
        const bVal = b.cells[column].textContent.trim();
        
        // Try to parse as numbers
        const aNum = parseFloat(aVal);
        const bNum = parseFloat(bVal);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // String comparison
        return isAscending ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    
    // Reorder rows in DOM
    rows.forEach(row => tbody.appendChild(row));
}

function searchTable(table, searchTerm) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// Alert functionality
function initAlerts() {
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    
    alerts.forEach(alert => {
        const delay = parseInt(alert.getAttribute('data-auto-hide')) || 5000;
        setTimeout(() => {
            hideAlert(alert);
        }, delay);
    });
    
    // Close button functionality
    const closeButtons = document.querySelectorAll('.alert .alert-close');
    
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const alert = this.closest('.alert');
            hideAlert(alert);
        });
    });
}

function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fade-in`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="alert-close" style="background: none; border: none; color: inherit; float: right; font-size: 1.2rem; cursor: pointer;">&times;</button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-hide
    if (duration > 0) {
        setTimeout(() => {
            hideAlert(alert);
        }, duration);
    }
    
    // Close button
    const closeBtn = alert.querySelector('.alert-close');
    closeBtn.addEventListener('click', () => hideAlert(alert));
    
    return alert;
}

function hideAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 300);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
    document.body.appendChild(container);
    return container;
}

function showNotification(message, type = 'info', duration = 3000) {
    return showAlert(message, type, duration);
}

// Utility functions
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

function formatCurrency(amount, currency = '$') {
    return currency + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

// AJAX helpers
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    const config = { ...defaultOptions, ...options };
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

function postData(url, data) {
    return makeRequest(url, {
        method: 'POST',
        body: JSON.stringify(data),
    });
}

function putData(url, data) {
    return makeRequest(url, {
        method: 'PUT',
        body: JSON.stringify(data),
    });
}

function deleteData(url) {
    return makeRequest(url, {
        method: 'DELETE',
    });
}

// Order management functions
function addToOrder(productId, quantity = 1) {
    const orderData = {
        product_id: productId,
        quantity: quantity
    };
    
    postData('api/add-to-order.php', orderData)
        .then(response => {
            if (response.success) {
                showNotification('Item added to order', 'success');
                updateOrderDisplay();
            } else {
                showNotification('Error adding item to order', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error adding item to order', 'danger');
        });
}

function updateOrderDisplay() {
    // This would be implemented based on specific order display requirements
    const orderContainer = document.getElementById('currentOrder');
    if (orderContainer) {
        // Fetch and display current order
        makeRequest('api/current-order.php')
            .then(order => {
                renderOrder(order);
            });
    }
}

function renderOrder(order) {
    const orderContainer = document.getElementById('currentOrder');
    if (!orderContainer || !order) return;
    
    let html = '<div class="order-items">';
    
    order.items.forEach(item => {
        html += `
            <div class="order-item">
                <div class="order-item-info">
                    <h6>${item.name}</h6>
                    <p>Qty: ${item.quantity} x ${formatCurrency(item.unit_price)}</p>
                </div>
                <div class="order-item-price">
                    ${formatCurrency(item.total_price)}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    html += `
        <div class="order-total">
            <strong>Total: ${formatCurrency(order.total_amount)}</strong>
        </div>
    `;
    
    orderContainer.innerHTML = html;
}

// Export functions for global use
window.RestaurantApp = {
    showModal,
    hideModal,
    showAlert,
    hideAlert,
    showNotification,
    formatCurrency,
    formatDate,
    formatDateTime,
    makeRequest,
    postData,
    putData,
    deleteData,
    addToOrder,
    updateOrderDisplay
};