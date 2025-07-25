<?php
/**
 * Restaurant Management System
 * Main entry point
 */

require_once 'includes/session.php';

// Redirect based on login status
if (Session::isLoggedIn()) {
    // User is logged in, redirect to dashboard
    header('Location: dashboard.php');
} else {
    // User is not logged in, redirect to login page
    header('Location: login.php');
}

exit();
?>