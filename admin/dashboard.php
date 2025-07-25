<?php
$page_title = "Admin Dashboard";
require_once '../includes/header.php';

// Get statistics
$database = new Database();
$conn = $database->getConnection();

// Get total orders today
$query = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
$stmt = $conn->prepare($query);
$stmt->execute();
$orders_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total revenue today
$query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'";
$stmt = $conn->prepare($query);
$stmt->execute();
$revenue_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get pending orders
$query = "SELECT COUNT(*) as total FROM orders WHERE status IN ('pending', 'confirmed', 'preparing')";
$stmt = $conn->prepare($query);
$stmt->execute();
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get available tables
$query = "SELECT COUNT(*) as total FROM restaurant_tables WHERE status = 'available'";
$stmt = $conn->prepare($query);
$stmt->execute();
$available_tables = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent orders
$query = "SELECT o.*, rt.table_number, u.first_name, u.last_name 
          FROM orders o 
          LEFT JOIN restaurant_tables rt ON o.table_id = rt.id 
          LEFT JOIN users u ON o.customer_id = u.id 
          ORDER BY o.created_at DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Dashboard</h1>
        <div>
            <span class="badge badge-info"><?php echo date('F j, Y'); ?></span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-number" id="total-orders"><?php echo $orders_today; ?></div>
            <div class="stat-label">Orders Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="total-revenue">$<?php echo number_format($revenue_today, 2); ?></div>
            <div class="stat-label">Revenue Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="pending-orders"><?php echo $pending_orders; ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="available-tables"><?php echo $available_tables; ?></div>
            <div class="stat-label">Available Tables</div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            Recent Orders
            <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_orders)): ?>
                <p class="text-center">No orders found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Table</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr data-order-id="<?php echo $order['id']; ?>">
                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo $order['table_number'] ? htmlspecialchars($order['table_number']) : 'N/A'; ?></td>
                                    <td><?php echo $order['first_name'] ? htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) : 'Guest'; ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge order-status order-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <div class="dashboard-grid">
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Manage Orders
                </a>
                <a href="tables.php" class="btn btn-success">
                    <i class="fas fa-chair"></i> Table Status
                </a>
                <a href="products.php" class="btn btn-warning">
                    <i class="fas fa-utensils"></i> Menu Items
                </a>
                <a href="reports.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>