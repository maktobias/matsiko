<?php
require_once 'includes/session.php';
require_once 'classes/Order.php';
require_once 'classes/Store.php';
require_once 'classes/User.php';

Session::requireLogin();

$order = new Order();
$store = new Store();
$user = new User();

// Get statistics
$todayEarnings = $order->getEarningsReport(date('Y-m-d'), date('Y-m-d'));
$monthlyEarnings = $order->getEarningsReport(date('Y-m-01'), date('Y-m-t'));
$recentOrders = $order->getAll(['date_from' => date('Y-m-d')]);
$totalUsers = count($user->getAll());
$totalStores = count($store->getAll());
$totalTables = count($store->getAllTables());

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <div class="text-right">
            <small class="text-muted">Welcome back, <?php echo Session::get('user_name'); ?></small><br>
            <small class="text-muted"><?php echo date('l, F j, Y'); ?></small>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-number">
                $<?php echo number_format($todayEarnings['total_earnings'] ?? 0, 2); ?>
            </div>
            <div class="stat-label">Today's Earnings</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-number">
                $<?php echo number_format($monthlyEarnings['total_earnings'] ?? 0, 2); ?>
            </div>
            <div class="stat-label">Monthly Earnings</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-number">
                <?php echo count($recentOrders); ?>
            </div>
            <div class="stat-label">Today's Orders</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-table"></i>
            </div>
            <div class="stat-number">
                <?php echo $totalTables; ?>
            </div>
            <div class="stat-label">Total Tables</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-receipt"></i> Recent Orders</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-center text-muted">No orders today yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentOrders, 0, 10) as $orderItem): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($orderItem['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($orderItem['customer_name'] ?: 'Walk-in'); ?></td>
                                        <td>$<?php echo number_format($orderItem['total_amount'], 2); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($orderItem['status']) {
                                                case 'pending': $statusClass = 'badge-warning'; break;
                                                case 'confirmed': $statusClass = 'badge-info'; break;
                                                case 'preparing': $statusClass = 'badge-warning'; break;
                                                case 'ready': $statusClass = 'badge-success'; break;
                                                case 'served': $statusClass = 'badge-success'; break;
                                                case 'completed': $statusClass = 'badge-success'; break;
                                                case 'cancelled': $statusClass = 'badge-danger'; break;
                                                default: $statusClass = 'badge-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo ucfirst($orderItem['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('H:i', strtotime($orderItem['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="orders.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> View All Orders
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="orders/create.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> New Order
                    </a>
                    
                    <?php if (Session::hasPermission('products')): ?>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-utensils"></i> Manage Products
                        </a>
                    <?php endif; ?>
                    
                    <?php if (Session::hasPermission('tables')): ?>
                        <a href="tables.php" class="btn btn-info">
                            <i class="fas fa-table"></i> Manage Tables
                        </a>
                    <?php endif; ?>
                    
                    <?php if (Session::hasPermission('reports')): ?>
                        <a href="reports.php" class="btn btn-warning">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                    <?php endif; ?>
                    
                    <?php if (Session::isAdmin()): ?>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Status Overview -->
    <?php if (Session::hasPermission('tables')): ?>
        <?php $tableStatus = $store->getTableStatusSummary(); ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3><i class="fas fa-chair"></i> Table Status Overview</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <?php
                    $statusColors = [
                        'available' => 'success',
                        'occupied' => 'warning',
                        'reserved' => 'info',
                        'maintenance' => 'danger'
                    ];
                    
                    $statusCounts = [];
                    foreach ($tableStatus as $status) {
                        $statusCounts[$status['status']] = $status['count'];
                    }
                    ?>
                    
                    <?php foreach (['available', 'occupied', 'reserved', 'maintenance'] as $status): ?>
                        <div class="stat-card">
                            <div class="stat-icon" style="color: var(--<?php echo $statusColors[$status]; ?>-color, #667eea);">
                                <i class="fas fa-chair"></i>
                            </div>
                            <div class="stat-number">
                                <?php echo $statusCounts[$status] ?? 0; ?>
                            </div>
                            <div class="stat-label"><?php echo ucfirst($status); ?> Tables</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>