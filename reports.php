<?php
require_once 'includes/session.php';
require_once 'classes/Order.php';

Session::requireLogin();

if (!Session::hasPermission('reports')) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'Reports & Analytics';
$order = new Order();

// Get date range from filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today
$reportType = $_GET['report_type'] ?? 'earnings';

// Get earnings report
$earningsReport = $order->getEarningsReport($dateFrom, $dateTo);

// Get monthly report
$monthlyReport = $order->getMonthlyReport(date('Y', strtotime($dateFrom)), date('m', strtotime($dateFrom)));

// Get orders for the period
$ordersInPeriod = $order->getAll(['date_from' => $dateFrom, 'date_to' => $dateTo]);

// Calculate additional statistics
$totalOrders = count($ordersInPeriod);
$paidOrders = array_filter($ordersInPeriod, function($order) { return $order['payment_status'] === 'paid'; });
$unpaidOrders = array_filter($ordersInPeriod, function($order) { return $order['payment_status'] === 'unpaid'; });

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
        <button onclick="exportReport()" class="btn btn-success">
            <i class="fas fa-download"></i> Export Report
        </button>
    </div>

    <!-- Report Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-filter"></i> Report Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-3 align-items-end">
                <div class="form-group">
                    <label for="report_type">Report Type</label>
                    <select name="report_type" id="report_type" class="form-control">
                        <option value="earnings" <?php echo $reportType === 'earnings' ? 'selected' : ''; ?>>Earnings Report</option>
                        <option value="monthly" <?php echo $reportType === 'monthly' ? 'selected' : ''; ?>>Monthly Report</option>
                        <option value="orders" <?php echo $reportType === 'orders' ? 'selected' : ''; ?>>Orders Report</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-number">
                $<?php echo number_format($earningsReport['total_earnings'] ?? 0, 2); ?>
            </div>
            <div class="stat-label">Total Earnings</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-number">
                <?php echo $earningsReport['total_orders'] ?? 0; ?>
            </div>
            <div class="stat-label">Total Orders</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-number">
                $<?php echo number_format($earningsReport['average_order_value'] ?? 0, 2); ?>
            </div>
            <div class="stat-label">Average Order Value</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-number">
                <?php echo $totalOrders > 0 ? round((count($paidOrders) / $totalOrders) * 100, 1) : 0; ?>%
            </div>
            <div class="stat-label">Payment Rate</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Main Report Content -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-chart-line"></i> 
                    <?php echo ucfirst($reportType); ?> Report 
                    <small>(<?php echo date('M j, Y', strtotime($dateFrom)); ?> - <?php echo date('M j, Y', strtotime($dateTo)); ?>)</small>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($reportType === 'earnings'): ?>
                    <div class="earnings-report">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Revenue Breakdown</h5>
                                <div class="revenue-item">
                                    <div class="d-flex justify-content-between">
                                        <span>Paid Orders:</span>
                                        <strong class="text-success">$<?php echo number_format(array_sum(array_column($paidOrders, 'total_amount')), 2); ?></strong>
                                    </div>
                                </div>
                                <div class="revenue-item">
                                    <div class="d-flex justify-content-between">
                                        <span>Unpaid Orders:</span>
                                        <strong class="text-danger">$<?php echo number_format(array_sum(array_column($unpaidOrders, 'total_amount')), 2); ?></strong>
                                    </div>
                                </div>
                                <hr>
                                <div class="revenue-item">
                                    <div class="d-flex justify-content-between">
                                        <span><strong>Total Revenue:</strong></span>
                                        <strong>$<?php echo number_format(array_sum(array_column($ordersInPeriod, 'total_amount')), 2); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Order Status</h5>
                                <?php
                                $statusCounts = [];
                                foreach ($ordersInPeriod as $orderItem) {
                                    $status = $orderItem['status'];
                                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                                }
                                ?>
                                <?php foreach ($statusCounts as $status => $count): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><?php echo ucfirst($status); ?>:</span>
                                        <span class="badge badge-info"><?php echo $count; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($reportType === 'monthly'): ?>
                    <div class="monthly-report">
                        <?php if (empty($monthlyReport)): ?>
                            <p class="text-center text-muted">No data available for the selected month.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Orders</th>
                                            <th>Paid Amount</th>
                                            <th>Unpaid Amount</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($monthlyReport as $dayReport): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($dayReport['order_date'])); ?></td>
                                                <td><?php echo $dayReport['total_orders']; ?></td>
                                                <td class="text-success">$<?php echo number_format($dayReport['paid_amount'], 2); ?></td>
                                                <td class="text-danger">$<?php echo number_format($dayReport['unpaid_amount'], 2); ?></td>
                                                <td><strong>$<?php echo number_format($dayReport['total_amount'], 2); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php else: // orders report ?>
                    <div class="orders-report">
                        <?php if (empty($ordersInPeriod)): ?>
                            <p class="text-center text-muted">No orders found for the selected period.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($ordersInPeriod, 0, 50) as $orderItem): // Limit to 50 for performance ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($orderItem['order_number']); ?></td>
                                                <td><?php echo htmlspecialchars($orderItem['customer_name'] ?: 'Walk-in'); ?></td>
                                                <td>$<?php echo number_format($orderItem['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $orderItem['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($orderItem['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $orderItem['payment_status'] === 'paid' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($orderItem['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, H:i', strtotime($orderItem['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($ordersInPeriod) > 50): ?>
                                <p class="text-muted text-center">Showing first 50 orders. Export full report to see all <?php echo count($ordersInPeriod); ?> orders.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats Sidebar -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Quick Stats</h3>
            </div>
            <div class="card-body">
                <div class="quick-stat mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Orders:</span>
                        <strong><?php echo $totalOrders; ?></strong>
                    </div>
                </div>
                
                <div class="quick-stat mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Paid Orders:</span>
                        <strong class="text-success"><?php echo count($paidOrders); ?></strong>
                    </div>
                </div>
                
                <div class="quick-stat mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Unpaid Orders:</span>
                        <strong class="text-danger"><?php echo count($unpaidOrders); ?></strong>
                    </div>
                </div>
                
                <hr>
                
                <div class="quick-stat mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Daily Average:</span>
                        <strong>
                            $<?php 
                            $days = max(1, (strtotime($dateTo) - strtotime($dateFrom)) / (60 * 60 * 24) + 1);
                            echo number_format(($earningsReport['total_earnings'] ?? 0) / $days, 2); 
                            ?>
                        </strong>
                    </div>
                </div>
                
                <?php if ($reportType === 'earnings'): ?>
                    <div class="chart-placeholder mt-4 p-3" style="background: #f8f9fa; border-radius: 8px; text-align: center;">
                        <i class="fas fa-chart-pie" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-2">Chart visualization would go here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    const reportType = '<?php echo $reportType; ?>';
    const dateFrom = '<?php echo $dateFrom; ?>';
    const dateTo = '<?php echo $dateTo; ?>';
    
    // In a real implementation, this would trigger a server-side export
    const exportUrl = `export.php?type=${reportType}&date_from=${dateFrom}&date_to=${dateTo}`;
    
    // For now, we'll show a notification
    RestaurantApp.showNotification('Export functionality would be implemented here', 'info');
    
    // Uncomment this line when export.php is implemented
    // window.open(exportUrl, '_blank');
}

// Auto-refresh data every 5 minutes for real-time reports
setInterval(function() {
    if (document.visibilityState === 'visible') {
        // Only refresh if page is visible
        location.reload();
    }
}, 300000); // 5 minutes
</script>

<?php include 'includes/footer.php'; ?>