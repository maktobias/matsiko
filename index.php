<?php
require_once 'includes/auth.php';

if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect based on user role
switch ($_SESSION['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'employee':
        header("Location: employee/dashboard.php");
        break;
    default:
        header("Location: customer/dashboard.php");
        break;
}
exit();
?>