<?php
$page_title = "Reports & Analytics";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Get date range parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Sales Overview
$query = "SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
    SUM(CASE WHEN payment_status = 'unpaid' THEN total_amount ELSE 0 END) as unpaid_revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$sales_overview = $stmt->fetch(PDO::FETCH_ASSOC);

// Daily Sales Chart Data
$query = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as orders,
    SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top Products
$query = "SELECT 
    p.name,
    p.price,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.total_price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id, p.name, p.price
    ORDER BY total_quantity DESC
    LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Order Status Distribution
$query = "SELECT 
    status,
    COUNT(*) as count
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$status_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Payment Methods
$query = "SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(total_amount) as total_amount
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND payment_status = 'paid'
    GROUP BY payment_method";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly comparison (current vs previous month)
$current_month = date('Y-m');
$previous_month = date('Y-m', strtotime('-1 month'));

$query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as orders,
    SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE_FORMAT(created_at, '%Y-%m') IN (?, ?)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
$stmt = $conn->prepare($query);
$stmt->execute([$current_month, $previous_month]);
$monthly_comparison = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Table Performance
$query = "SELECT 
    rt.table_number,
    s.name as store_name,
    COUNT(o.id) as order_count,
    SUM(o.total_amount) as total_revenue,
    AVG(o.total_amount) as avg_order_value
    FROM restaurant_tables rt
    LEFT JOIN orders o ON rt.id = o.table_id AND DATE(o.created_at) BETWEEN ? AND ?
    LEFT JOIN stores s ON rt.store_id = s.id
    GROUP BY rt.id, rt.table_number, s.name
    ORDER BY total_revenue DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$start_date, $end_date]);
$table_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Reports & Analytics</h1>
        <button class="btn btn-primary" onclick="exportReport()">
            <i class="fas fa-download"></i> Export Report
        </button>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="d-flex align-items-center gap-3">
                <div class="form-group mb-0">
                    <label class="form-label">Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="reports.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    <!-- Sales Overview -->
    <div class="dashboard-grid mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $sales_overview['total_orders'] ?: 0; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($sales_overview['total_revenue'] ?: 0, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($sales_overview['avg_order_value'] ?: 0, 2); ?></div>
            <div class="stat-label">Avg Order Value</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($sales_overview['unpaid_revenue'] ?: 0, 2); ?></div>
            <div class="stat-label">Unpaid Bills</div>
        </div>
    </div>

    <div class="row">
        <!-- Charts Section -->
        <div class="col-md-8">
            <!-- Daily Sales Chart -->
            <div class="card mb-3">
                <div class="card-header">Daily Sales Trend</div>
                <div class="card-body">
                    <canvas id="dailySalesChart" height="100"></canvas>
                </div>
            </div>

            <!-- Order Status Chart -->
            <div class="card mb-3">
                <div class="card-header">Order Status Distribution</div>
                <div class="card-body">
                    <canvas id="statusChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Side Statistics -->
        <div class="col-md-4">
            <!-- Top Products -->
            <div class="card mb-3">
                <div class="card-header">Top Products</div>
                <div class="card-body">
                    <?php foreach (array_slice($top_products, 0, 5) as $product): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <br><small class="text-muted">$<?php echo number_format($product['price'], 2); ?></small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-primary"><?php echo $product['total_quantity']; ?> sold</span>
                                <br><small class="text-muted">$<?php echo number_format($product['total_revenue'], 2); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card mb-3">
                <div class="card-header">Payment Methods</div>
                <div class="card-body">
                    <?php foreach ($payment_methods as $method): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo ucfirst($method['payment_method']); ?></span>
                            <div class="text-right">
                                <span class="badge badge-info"><?php echo $method['count']; ?></span>
                                <br><small class="text-muted">$<?php echo number_format($method['total_amount'], 2); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row">
        <!-- Table Performance -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Table Performance</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                    <th>Avg Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($table_performance, 0, 10) as $table): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($table['table_number']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($table['store_name']); ?></small>
                                        </td>
                                        <td><?php echo $table['order_count'] ?: 0; ?></td>
                                        <td>$<?php echo number_format($table['total_revenue'] ?: 0, 2); ?></td>
                                        <td>$<?php echo number_format($table['avg_order_value'] ?: 0, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Monthly Comparison</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $current_data = null;
                                $previous_data = null;
                                
                                foreach ($monthly_comparison as $month_data) {
                                    if ($month_data['month'] === $current_month) {
                                        $current_data = $month_data;
                                    } elseif ($month_data['month'] === $previous_month) {
                                        $previous_data = $month_data;
                                    }
                                }
                                
                                if ($current_data):
                                    $growth = 0;
                                    if ($previous_data && $previous_data['revenue'] > 0) {
                                        $growth = (($current_data['revenue'] - $previous_data['revenue']) / $previous_data['revenue']) * 100;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($current_data['month'] . '-01')); ?></td>
                                        <td><?php echo $current_data['orders']; ?></td>
                                        <td>$<?php echo number_format($current_data['revenue'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $growth >= 0 ? 'success' : 'danger'; ?>">
                                                <?php echo ($growth >= 0 ? '+' : '') . number_format($growth, 1); ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if ($previous_data): ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($previous_data['month'] . '-01')); ?></td>
                                        <td><?php echo $previous_data['orders']; ?></td>
                                        <td>$<?php echo number_format($previous_data['revenue'], 2); ?></td>
                                        <td>-</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Daily Sales Chart
const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
const dailySalesChart = new Chart(dailySalesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($daily_sales, 'date')); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode(array_column($daily_sales, 'revenue')); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Orders',
            data: <?php echo json_encode(array_column($daily_sales, 'orders')); ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Orders'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Order Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($status_distribution, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($status_distribution, 'count')); ?>,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40',
                '#FF6384'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

function exportReport() {
    const startDate = '<?php echo $start_date; ?>';
    const endDate = '<?php echo $end_date; ?>';
    window.open(`export_report.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>