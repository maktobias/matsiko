<?php
require_once 'includes/session.php';
require_once 'classes/Order.php';
require_once 'classes/Store.php';

Session::requireLogin();

$pageTitle = 'Orders Management';
$order = new Order();
$store = new Store();

// Handle status updates
if ($_POST && isset($_POST['action'])) {
    $orderId = $_POST['order_id'] ?? 0;
    $response = ['success' => false, 'message' => ''];
    
    switch ($_POST['action']) {
        case 'update_status':
            $newStatus = $_POST['status'] ?? '';
            if ($order->updateStatus($orderId, $newStatus)) {
                $response = ['success' => true, 'message' => 'Order status updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update order status'];
            }
            break;
            
        case 'update_payment':
            $paymentStatus = $_POST['payment_status'] ?? '';
            $paymentMethod = $_POST['payment_method'] ?? null;
            if ($order->updatePaymentStatus($orderId, $paymentStatus, $paymentMethod)) {
                $response = ['success' => true, 'message' => 'Payment status updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update payment status'];
            }
            break;
    }
    
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Get filters
$filters = [
    'store_id' => $_GET['store_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'payment_status' => $_GET['payment_status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? date('Y-m-d')
];

$orders = $order->getAll($filters);
$stores = $store->getAll();

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-receipt"></i> Orders Management</h1>
        <a href="orders/create.php" class="btn btn-success">
            <i class="fas fa-plus"></i> New Order
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <div class="form-group">
                    <label for="store_id">Store</label>
                    <select name="store_id" id="store_id" class="form-control">
                        <option value="">All Stores</option>
                        <?php foreach ($stores as $storeItem): ?>
                            <option value="<?php echo $storeItem['id']; ?>" 
                                    <?php echo $filters['store_id'] == $storeItem['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($storeItem['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $filters['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="preparing" <?php echo $filters['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                        <option value="ready" <?php echo $filters['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                        <option value="served" <?php echo $filters['status'] === 'served' ? 'selected' : ''; ?>>Served</option>
                        <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="<?php echo $filters['date_from']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="<?php echo $filters['date_to']; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="orders.php" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Orders (<?php echo count($orders); ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <p class="text-center text-muted">No orders found with the current filters.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table" data-sortable>
                        <thead>
                            <tr>
                                <th data-sort="0">Order #</th>
                                <th data-sort="1">Store</th>
                                <th data-sort="2">Table</th>
                                <th data-sort="3">Customer</th>
                                <th data-sort="4">Total</th>
                                <th data-sort="5">Status</th>
                                <th data-sort="6">Payment</th>
                                <th data-sort="7">Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $orderItem): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($orderItem['order_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($orderItem['store_name']); ?></td>
                                    <td>
                                        <?php echo $orderItem['table_number'] ? 'Table ' . $orderItem['table_number'] : 'N/A'; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($orderItem['customer_name'] ?: 'Walk-in'); ?></td>
                                    <td>
                                        <strong>$<?php echo number_format($orderItem['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <select class="form-control btn-sm status-select" 
                                                data-order-id="<?php echo $orderItem['id']; ?>"
                                                onchange="updateOrderStatus(this)">
                                            <option value="pending" <?php echo $orderItem['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $orderItem['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="preparing" <?php echo $orderItem['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="ready" <?php echo $orderItem['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                            <option value="served" <?php echo $orderItem['status'] === 'served' ? 'selected' : ''; ?>>Served</option>
                                            <option value="completed" <?php echo $orderItem['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $orderItem['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control btn-sm payment-select" 
                                                data-order-id="<?php echo $orderItem['id']; ?>"
                                                onchange="updatePaymentStatus(this)">
                                            <option value="unpaid" <?php echo $orderItem['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                            <option value="partial" <?php echo $orderItem['payment_status'] === 'partial' ? 'selected' : ''; ?>>Partial</option>
                                            <option value="paid" <?php echo $orderItem['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        </select>
                                    </td>
                                    <td><?php echo date('M j, Y H:i', strtotime($orderItem['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-sm" onclick="viewOrder(<?php echo $orderItem['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="printInvoice(<?php echo $orderItem['id']; ?>)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-dialog" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Order Details</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="orderModalBody">
            <!-- Order details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>

<script>
function updateOrderStatus(select) {
    const orderId = select.getAttribute('data-order-id');
    const newStatus = select.value;
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    formData.append('ajax', '1');
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            RestaurantApp.showNotification(data.message, 'success');
        } else {
            RestaurantApp.showNotification(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        RestaurantApp.showNotification('Error updating status', 'danger');
    });
}

function updatePaymentStatus(select) {
    const orderId = select.getAttribute('data-order-id');
    const newStatus = select.value;
    
    const formData = new FormData();
    formData.append('action', 'update_payment');
    formData.append('order_id', orderId);
    formData.append('payment_status', newStatus);
    formData.append('ajax', '1');
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            RestaurantApp.showNotification(data.message, 'success');
        } else {
            RestaurantApp.showNotification(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        RestaurantApp.showNotification('Error updating payment status', 'danger');
    });
}

function viewOrder(orderId) {
    fetch(`api/order-details.php?id=${orderId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('orderModalBody').innerHTML = data.html;
            RestaurantApp.showModal(document.getElementById('orderModal'));
        } else {
            RestaurantApp.showNotification('Error loading order details', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        RestaurantApp.showNotification('Error loading order details', 'danger');
    });
}

function printInvoice(orderId) {
    window.open(`invoice.php?order_id=${orderId}`, '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>