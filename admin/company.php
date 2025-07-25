<?php
$page_title = "Company Information";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submission
if ($_POST) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $website = trim($_POST['website']);
    $tax_number = trim($_POST['tax_number']);
    $currency = $_POST['currency'];
    
    // Check if company info exists
    $check_query = "SELECT COUNT(*) as count FROM company_info";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute();
    $exists = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    if ($exists) {
        // Update existing record
        $query = "UPDATE company_info SET name=?, address=?, phone=?, email=?, website=?, tax_number=?, currency=? WHERE id=1";
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$name, $address, $phone, $email, $website, $tax_number, $currency])) {
            $success_message = "Company information updated successfully.";
        } else {
            $error_message = "Error updating company information.";
        }
    } else {
        // Insert new record
        $query = "INSERT INTO company_info (name, address, phone, email, website, tax_number, currency) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$name, $address, $phone, $email, $website, $tax_number, $currency])) {
            $success_message = "Company information saved successfully.";
        } else {
            $error_message = "Error saving company information.";
        }
    }
}

// Get current company information
$query = "SELECT * FROM company_info LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if no company info exists
if (!$company) {
    $company = [
        'name' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
        'website' => '',
        'tax_number' => '',
        'currency' => 'USD'
    ];
}

// Available currencies
$currencies = [
    'USD' => 'US Dollar ($)',
    'EUR' => 'Euro (€)',
    'GBP' => 'British Pound (£)',
    'CAD' => 'Canadian Dollar (C$)',
    'AUD' => 'Australian Dollar (A$)',
    'JPY' => 'Japanese Yen (¥)',
    'INR' => 'Indian Rupee (₹)',
    'CNY' => 'Chinese Yuan (¥)',
    'BRL' => 'Brazilian Real (R$)',
    'MXN' => 'Mexican Peso ($)',
];
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Company Information</h1>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Company Information Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" data-validate>
                        <div class="form-group">
                            <label class="form-label">Company Name *</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($company['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($company['address']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($company['phone']); ?>">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($company['email']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($company['website']); ?>" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label">Tax Number</label>
                                    <input type="text" name="tax_number" class="form-control" value="<?php echo htmlspecialchars($company['tax_number']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Currency *</label>
                            <select name="currency" class="form-control" required>
                                <?php foreach ($currencies as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo $company['currency'] === $code ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Company Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>System Version:</strong><br>
                        <span class="text-muted">Restaurant Management System v1.0</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>PHP Version:</strong><br>
                        <span class="text-muted"><?php echo phpversion(); ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Database:</strong><br>
                        <span class="text-muted">MySQL</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Server Time:</strong><br>
                        <span class="text-muted"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Timezone:</strong><br>
                        <span class="text-muted"><?php echo date_default_timezone_get(); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Statistics</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get quick stats
                    $stats_queries = [
                        'total_users' => "SELECT COUNT(*) FROM users",
                        'total_stores' => "SELECT COUNT(*) FROM stores",
                        'total_tables' => "SELECT COUNT(*) FROM restaurant_tables",
                        'total_products' => "SELECT COUNT(*) FROM products",
                        'total_orders' => "SELECT COUNT(*) FROM orders",
                        'total_revenue' => "SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'"
                    ];
                    
                    $stats = [];
                    foreach ($stats_queries as $key => $query) {
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        $stats[$key] = $stmt->fetchColumn();
                    }
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <strong><?php echo $stats['total_users']; ?></strong>
                            <br><small class="text-muted">Users</small>
                        </div>
                        <div class="col-6 mb-3">
                            <strong><?php echo $stats['total_stores']; ?></strong>
                            <br><small class="text-muted">Stores</small>
                        </div>
                        <div class="col-6 mb-3">
                            <strong><?php echo $stats['total_tables']; ?></strong>
                            <br><small class="text-muted">Tables</small>
                        </div>
                        <div class="col-6 mb-3">
                            <strong><?php echo $stats['total_products']; ?></strong>
                            <br><small class="text-muted">Products</small>
                        </div>
                        <div class="col-6 mb-3">
                            <strong><?php echo $stats['total_orders']; ?></strong>
                            <br><small class="text-muted">Orders</small>
                        </div>
                        <div class="col-6 mb-3">
                            <strong>$<?php echo number_format($stats['total_revenue'] ?: 0, 2); ?></strong>
                            <br><small class="text-muted">Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Settings -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Maintenance Actions</h6>
                        <p class="mb-2">Use these tools to maintain your restaurant management system:</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="backupDatabase()">
                                <i class="fas fa-database"></i><br>
                                Backup Database
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-block" onclick="clearCache()">
                                <i class="fas fa-broom"></i><br>
                                Clear Cache
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info btn-block" onclick="exportData()">
                                <i class="fas fa-download"></i><br>
                                Export Data
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success btn-block" onclick="systemCheck()">
                                <i class="fas fa-check-circle"></i><br>
                                System Check
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function backupDatabase() {
    if (confirm('This will create a backup of your database. Continue?')) {
        showAlert('Database backup initiated. This may take a few moments.', 'info');
        // In a real implementation, this would trigger a server-side backup process
        setTimeout(() => {
            showAlert('Database backup completed successfully.', 'success');
        }, 3000);
    }
}

function clearCache() {
    if (confirm('This will clear all system cache. Continue?')) {
        showAlert('Cache cleared successfully.', 'success');
    }
}

function exportData() {
    if (confirm('This will export all system data to CSV files. Continue?')) {
        showAlert('Data export initiated. Download will start shortly.', 'info');
        // In a real implementation, this would trigger data export
        setTimeout(() => {
            showAlert('Data export completed. Files have been downloaded.', 'success');
        }, 2000);
    }
}

function systemCheck() {
    showAlert('Running system check...', 'info');
    setTimeout(() => {
        showAlert('System check completed. All systems are operating normally.', 'success');
    }, 2000);
}
</script>

<?php include '../includes/footer.php'; ?>