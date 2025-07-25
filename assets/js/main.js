// Restaurant Management System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initModals();
    initForms();
    initTables();
    initDashboard();
});

// Modal functionality
function initModals() {
    const modals = document.querySelectorAll('.modal');
    const openButtons = document.querySelectorAll('[data-modal-target]');
    const closeButtons = document.querySelectorAll('.modal-close, [data-modal-close]');

    openButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-modal-target');
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('show');
            }
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });

    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
}

// Form validation and handling
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = '#e74c3c';
}

function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

// Table management
function initTables() {
    // Make tables sortable
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const table = header.closest('table');
            const column = header.getAttribute('data-sort');
            sortTable(table, column);
        });
    });
    
    // Search functionality
    const searchInputs = document.querySelectorAll('[data-table-search]');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', () => {
            const targetTable = document.getElementById(input.getAttribute('data-table-search'));
            if (targetTable) {
                filterTable(targetTable, input.value);
            }
        });
    });
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = Array.from(table.querySelectorAll('th')).findIndex(th => th.getAttribute('data-sort') === column);
    
    if (columnIndex === -1) return;
    
    const isAscending = table.getAttribute('data-sort-direction') !== 'asc';
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        if (isNumeric(aValue) && isNumeric(bValue)) {
            return isAscending ? 
                parseFloat(aValue) - parseFloat(bValue) : 
                parseFloat(bValue) - parseFloat(aValue);
        }
        
        return isAscending ? 
            aValue.localeCompare(bValue) : 
            bValue.localeCompare(aValue);
    });
    
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    
    table.setAttribute('data-sort-direction', isAscending ? 'asc' : 'desc');
}

function filterTable(table, searchTerm) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

function isNumeric(str) {
    return !isNaN(str) && !isNaN(parseFloat(str));
}

// Dashboard functionality
function initDashboard() {
    loadDashboardStats();
    initRealTimeUpdates();
}

function loadDashboardStats() {
    // Load statistics via AJAX
    fetch('ajax/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            updateDashboardCards(data);
        })
        .catch(error => console.error('Error loading dashboard stats:', error));
}

function updateDashboardCards(stats) {
    const cards = {
        'total-orders': stats.total_orders,
        'total-revenue': stats.total_revenue,
        'pending-orders': stats.pending_orders,
        'available-tables': stats.available_tables
    };
    
    Object.keys(cards).forEach(cardId => {
        const element = document.getElementById(cardId);
        if (element) {
            element.textContent = cards[cardId];
        }
    });
}

function initRealTimeUpdates() {
    // Update dashboard every 30 seconds
    setInterval(loadDashboardStats, 30000);
    
    // Update order status in real-time
    if (window.location.pathname.includes('orders')) {
        setInterval(refreshOrderStatus, 10000);
    }
}

function refreshOrderStatus() {
    fetch('ajax/order_status.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(order => {
                const statusElement = document.querySelector(`[data-order-id="${order.id}"] .order-status`);
                if (statusElement) {
                    statusElement.textContent = order.status;
                    statusElement.className = `badge order-${order.status}`;
                }
            });
        })
        .catch(error => console.error('Error refreshing order status:', error));
}

// Utility functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.main-content');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// AJAX helper functions
function ajaxGet(url) {
    return fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
}

function ajaxPost(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    });
}

// Order management functions
function updateOrderStatus(orderId, status) {
    ajaxPost('ajax/update_order_status.php', {
        order_id: orderId,
        status: status
    })
    .then(data => {
        if (data.success) {
            showAlert('Order status updated successfully', 'success');
            location.reload();
        } else {
            showAlert('Error updating order status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating order status', 'danger');
    });
}

function printInvoice(orderId) {
    window.open(`print_invoice.php?id=${orderId}`, '_blank');
}

// Table management functions
function updateTableStatus(tableId, status) {
    ajaxPost('ajax/update_table_status.php', {
        table_id: tableId,
        status: status
    })
    .then(data => {
        if (data.success) {
            showAlert('Table status updated successfully', 'success');
            location.reload();
        } else {
            showAlert('Error updating table status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating table status', 'danger');
    });
}

// Mobile menu toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Initialize mobile menu button if it exists
document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('mobile-menu-btn');
    if (menuButton) {
        menuButton.addEventListener('click', toggleSidebar);
    }
});