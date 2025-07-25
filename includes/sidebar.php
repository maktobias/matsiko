<nav class="sidebar">
    <ul class="nav-menu">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users nav-icon"></i>
                    User Management
                </a>
            </li>
            <li class="nav-item">
                <a href="user_groups.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user_groups.php') ? 'active' : ''; ?>">
                    <i class="fas fa-user-tag nav-icon"></i>
                    User Groups
                </a>
            </li>
            <li class="nav-item">
                <a href="stores.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'stores.php') ? 'active' : ''; ?>">
                    <i class="fas fa-store nav-icon"></i>
                    Store Management
                </a>
            </li>
            <li class="nav-item">
                <a href="tables.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'tables.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chair nav-icon"></i>
                    Table Management
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tags nav-icon"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
                    <i class="fas fa-utensils nav-icon"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a href="company.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'company.php') ? 'active' : ''; ?>">
                    <i class="fas fa-building nav-icon"></i>
                    Company Info
                </a>
            </li>
        <?php elseif ($_SESSION['role'] === 'employee'): ?>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    Dashboard
                </a>
            </li>
            <?php if ($auth->hasPermission('tables')): ?>
            <li class="nav-item">
                <a href="tables.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'tables.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chair nav-icon"></i>
                    Tables
                </a>
            </li>
            <?php endif; ?>
            <?php if ($auth->hasPermission('orders')): ?>
            <li class="nav-item">
                <a href="orders.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="new_order.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'new_order.php') ? 'active' : ''; ?>">
                    <i class="fas fa-plus nav-icon"></i>
                    New Order
                </a>
            </li>
            <?php endif; ?>
            <?php if ($auth->hasPermission('products')): ?>
            <li class="nav-item">
                <a href="products.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
                    <i class="fas fa-utensils nav-icon"></i>
                    Menu
                </a>
            </li>
            <?php endif; ?>
        <?php endif; ?>
        
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user nav-icon"></i>
                Profile
            </a>
        </li>
    </ul>
</nav>