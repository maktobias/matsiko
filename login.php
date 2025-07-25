<?php
require_once 'includes/session.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (Session::isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $user = new User();
        $userData = $user->login($username, $password);
        
        if ($userData) {
            // Set session data
            Session::set('user_id', $userData['id']);
            Session::set('username', $userData['username']);
            Session::set('user_name', $userData['first_name'] . ' ' . $userData['last_name']);
            Session::set('user_group', [
                'id' => $userData['user_group_id'],
                'name' => $userData['group_name'],
                'permissions' => $userData['permissions']
            ]);
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-utensils" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                <h2>Restaurant Management System</h2>
                <p>Please sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate>
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #dee2e6; text-align: center;">
                <small style="color: #7f8c8d;">
                    <strong>Default Login:</strong><br>
                    Username: admin<br>
                    Password: password
                </small>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>