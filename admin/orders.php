<?php
$page_title = "Orders Management";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $id = $_POST['id'];
                $status = $_POST['status'];
                
                $query = "UPDATE orders SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$status, $id])) {
                    $success_message = "Order status updated successfully.";
                } else {
                    $error_message = "Error updating order status.";
                }
                break;
                
            case 'update_payment':
                $id = $_POST['id'];
                $payment_status = $_POST['payment_status'];
                $payment_method = $_POST['payment_method'];
                
                $query = "UPDATE orders SET payment_status = ?, payment_method = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$payment_status, $payment_method, $id])) {
                    $success_message = "Payment status updated successfully.";
                } else {
                    $error_message = "Error updating payment status.";
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(o.created_at) = ?";
    $params[] = $date_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get orders with related information
$query = "SELECT o.*, rt.table_number, s.name as store_name, 
          u.first_name, u.last_name, u.email,
          emp.first_name as emp_first_name, emp.last_name as emp_last_name
          FROM orders o 
          LEFT JOIN restaurant_tables rt ON o.table_id = rt.id 
          LEFT JOIN stores s ON o.store_id = s.id
          LEFT JOIN users u ON o.customer_id = u.id 
          LEFT JOIN users emp ON o.employee_id = emp.id
          $where_clause
          ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status IN ('pending', 'confirmed', 'preparing') THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
    SUM(CASE WHEN payment_status = 'unpaid' THEN total_amount ELSE 0 END) as unpaid_revenue
    FROM orders o $where_clause";

$stmt = $conn->prepare($stats_query);
$stmt->execute($params);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Orders Management</h1>
        <div>
            <a href="new_order.php" class="btn btn-success">
                <i class="fas fa-plus"></i> New Order
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Order Statistics -->
    <div class="dashboard-grid mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['completed_orders']; ?></div>
            <div class="stat-label">Completed Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($stats['paid_revenue'], 2); ?></div>
            <div class="stat-label">Paid Revenue</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="d-flex align-items-center gap-3">
                <div class="form-group mb-0">
                    <label class="form-label">Status:</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                        <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                        <option value="served" <?php echo $status_filter === 'served' ? 'selected' : ''; ?>>Served</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Date:</label>
                    <input type="date" name="date" value="<?php echo $date_filter; ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="orders.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            Orders
            <div class="d-flex align-items-center" style="float: right;">
                <input type="text" placeholder="Search orders..." class="form-control" data-table-search="ordersTable" style="width: 200px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                            <th data-sort="order_number">Order #</th>
                            <th data-sort="table_number">Table</th>
                            <th>Customer</th>
                            <th>Employee</th>
                            <th data-sort="total_amount">Total</th>
                            <th data-sort="status">Status</th>
                            <th data-sort="payment_status">Payment</th>
                            <th data-sort="created_at">Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr data-order-id="<?php echo $order['id']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <br><small class="text-muted"><?php echo ucfirst($order['order_type']); ?></small>
                                </td>
                                <td>
                                    <?php if ($order['table_number']): ?>
                                        <span class="badge badge-info"><?php echo htmlspecialchars($order['table_number']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['first_name']): ?>
                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['emp_first_name']): ?>
                                        <?php echo htmlspecialchars($order['emp_first_name'] . ' ' . $order['emp_last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <select class="form-control form-control-sm order-status" data-order-id="<?php echo $order['id']; ?>" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                        <option value="served" <?php echo $order['status'] === 'served' ? 'selected' : ''; ?>>Served</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control form-control-sm payment-status" data-order-id="<?php echo $order['id']; ?>" onchange="updatePaymentStatus(<?php echo $order['id']; ?>, this.value, '<?php echo $order['payment_method']; ?>')">
                                        <option value="unpaid" <?php echo $order['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="partial" <?php echo $order['payment_status'] === 'partial' ? 'selected' : ''; ?>>Partial</option>
                                        <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    </select>
                                    <small class="text-muted"><?php echo ucfirst($order['payment_method']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M j, H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-success" onclick="printInvoice(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
function updateOrderStatus(orderId, status) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="${orderId}">
        <input type="hidden" name="status" value="${status}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function updatePaymentStatus(orderId, paymentStatus, paymentMethod) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_payment">
        <input type="hidden" name="id" value="${orderId}">
        <input type="hidden" name="payment_status" value="${paymentStatus}">
        <input type="hidden" name="payment_method" value="${paymentMethod}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function printInvoice(orderId) {
    window.open(`print_invoice.php?id=${orderId}`, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>