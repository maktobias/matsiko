<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Restaurant Management System'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="brand">
                <i class="fas fa-utensils"></i> Restaurant Management
            </a>
            
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <?php if (Session::hasPermission('products')): ?>
                    <li><a href="products.php"><i class="fas fa-utensils"></i> Menu</a></li>
                <?php endif; ?>
                <?php if (Session::hasPermission('reports')): ?>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?php echo Session::get('user_name'); ?></span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><?php echo Session::get('user_group')['name'] ?? 'User'; ?> Panel</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li>
                <a href="orders.php">
                    <i class="fas fa-receipt"></i> Orders
                </a>
            </li>
            
            <?php if (Session::hasPermission('orders')): ?>
                <li>
                    <a href="orders/create.php">
                        <i class="fas fa-plus"></i> New Order
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (Session::hasPermission('products')): ?>
                <li>
                    <a href="products.php">
                        <i class="fas fa-utensils"></i> Products
                    </a>
                </li>
                
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (Session::hasPermission('tables')): ?>
                <li>
                    <a href="tables.php">
                        <i class="fas fa-table"></i> Tables
                    </a>
                </li>
                
                <li>
                    <a href="stores.php">
                        <i class="fas fa-store"></i> Stores
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (Session::hasPermission('reports')): ?>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                
                <li>
                    <a href="earnings.php">
                        <i class="fas fa-dollar-sign"></i> Earnings
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (Session::isAdmin()): ?>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                
                <li>
                    <a href="user-groups.php">
                        <i class="fas fa-user-tag"></i> User Groups
                    </a>
                </li>
                
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                
                <li>
                    <a href="company.php">
                        <i class="fas fa-building"></i> Company Info
                    </a>
                </li>
            <?php endif; ?>
            
            <li>
                <a href="profile.php">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </li>
            
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content"><?php // Content will be loaded here ?>