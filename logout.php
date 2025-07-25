<?php
require_once 'includes/session.php';

// Destroy session and redirect to login
Session::destroy();

// Clear any remember me cookies if they exist
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php?logged_out=1');
exit();
?>