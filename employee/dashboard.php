<?php
$page_title = "Employee Dashboard";
require_once '../includes/header.php';

// Get statistics relevant to employees
$database = new Database();
$conn = $database->getConnection();

// Get today's orders for this employee (if they have permission)
$today_orders = 0;
if ($auth->hasPermission('orders')) {
    $query = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $today_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Get pending orders
$pending_orders = 0;
if ($auth->hasPermission('orders')) {
    $query = "SELECT COUNT(*) as total FROM orders WHERE status IN ('pending', 'confirmed', 'preparing')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Get available tables
$available_tables = 0;
if ($auth->hasPermission('tables')) {
    $query = "SELECT COUNT(*) as total FROM restaurant_tables WHERE status = 'available'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $available_tables = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Get occupied tables
$occupied_tables = 0;
if ($auth->hasPermission('tables')) {
    $query = "SELECT COUNT(*) as total FROM restaurant_tables WHERE status = 'occupied'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $occupied_tables = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Get recent orders (limited to employee's access)
$recent_orders = [];
if ($auth->hasPermission('orders')) {
    $query = "SELECT o.*, rt.table_number, u.first_name, u.last_name 
              FROM orders o 
              LEFT JOIN restaurant_tables rt ON o.table_id = rt.id 
              LEFT JOIN users u ON o.customer_id = u.id 
              WHERE DATE(o.created_at) = CURDATE()
              ORDER BY o.created_at DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get table status overview
$table_status = [];
if ($auth->hasPermission('tables')) {
    $query = "SELECT rt.*, s.name as store_name,
              (SELECT COUNT(*) FROM orders WHERE table_id = rt.id AND status IN ('pending', 'confirmed', 'preparing', 'ready')) as active_orders
              FROM restaurant_tables rt 
              LEFT JOIN stores s ON rt.store_id = s.id 
              ORDER BY rt.table_number";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $table_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Employee Dashboard</h1>
        <div>
            <span class="badge badge-info"><?php echo date('F j, Y'); ?></span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-grid">
        <?php if ($auth->hasPermission('orders')): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_orders; ?></div>
                <div class="stat-label">Orders Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_orders; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        <?php endif; ?>
        
        <?php if ($auth->hasPermission('tables')): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $available_tables; ?></div>
                <div class="stat-label">Available Tables</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $occupied_tables; ?></div>
                <div class="stat-label">Occupied Tables</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-3">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <div class="dashboard-grid">
                <?php if ($auth->hasPermission('orders')): ?>
                    <a href="new_order.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Order
                    </a>
                    <a href="orders.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> View Orders
                    </a>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('tables')): ?>
                    <a href="tables.php" class="btn btn-warning">
                        <i class="fas fa-chair"></i> Table Status
                    </a>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('products')): ?>
                    <a href="products.php" class="btn btn-info">
                        <i class="fas fa-utensils"></i> Menu Items
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <?php if ($auth->hasPermission('orders') && !empty($recent_orders)): ?>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Today's Orders
                        <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
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
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
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
                                                <?php else: ?>
                                                    <span class="text-muted">Guest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="badge order-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Table Status -->
        <?php if ($auth->hasPermission('tables')): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Table Status
                        <a href="tables.php" class="btn btn-primary btn-sm">Manage</a>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach (array_slice($table_status, 0, 10) as $table): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded table-<?php echo $table['status']; ?>">
                                    <div>
                                        <strong><?php echo htmlspecialchars($table['table_number']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i> <?php echo $table['capacity']; ?> seats
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?php echo $table['status'] === 'available' ? 'success' : ($table['status'] === 'occupied' ? 'danger' : ($table['status'] === 'reserved' ? 'warning' : 'secondary')); ?>">
                                            <?php echo ucfirst($table['status']); ?>
                                        </span>
                                        <?php if ($table['active_orders'] > 0): ?>
                                            <br><small class="text-muted"><?php echo $table['active_orders']; ?> active orders</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tips and Information -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Tips & Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6><i class="fas fa-clock"></i> Today's Shifts</h6>
                    <p class="text-muted">
                        Check your scheduled shifts and break times in the employee portal.
                    </p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-utensils"></i> Menu Updates</h6>
                    <p class="text-muted">
                        Always check for daily specials and menu changes before starting your shift.
                    </p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-bell"></i> Notifications</h6>
                    <p class="text-muted">
                        Stay alert for order notifications and table status changes throughout your shift.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>