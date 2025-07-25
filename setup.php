<?php
// Restaurant Management System Setup Script

require_once 'config/database.php';

echo "<h1>Restaurant Management System Setup</h1>";

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Could not connect to database. Please check your database configuration.");
    }
    
    echo "<p>✓ Database connection successful</p>";
    
    // Read and execute schema
    $schema = file_get_contents('database/schema.sql');
    if (!$schema) {
        throw new Exception("Could not read schema file.");
    }
    
    // Split schema into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    
    echo "<p>✓ Database schema created successfully</p>";
    
    // Update admin password with correct hash
    $admin_password = password_hash('password', PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = ? WHERE username = 'admin'";
    $stmt = $conn->prepare($update_query);
    $stmt->execute([$admin_password]);
    
    echo "<p>✓ Admin user password updated</p>";
    
    // Add some sample products
    $products = [
        ['Margherita Pizza', 'Classic pizza with tomato sauce, mozzarella, and basil', 1, 15.99, 8.50, 'Tomato sauce, mozzarella, basil, olive oil', 15],
        ['Caesar Salad', 'Fresh romaine lettuce with caesar dressing and croutons', 1, 9.99, 4.50, 'Romaine lettuce, caesar dressing, croutons, parmesan', 8],
        ['Grilled Chicken', 'Juicy grilled chicken breast with herbs', 2, 18.99, 9.00, 'Chicken breast, herbs, olive oil, garlic', 20],
        ['Chocolate Cake', 'Rich chocolate cake with cream frosting', 3, 6.99, 2.50, 'Chocolate, flour, eggs, cream, sugar', 5],
        ['Fresh Orange Juice', 'Freshly squeezed orange juice', 4, 4.99, 1.50, 'Fresh oranges', 2]
    ];
    
    foreach ($products as $product) {
        $query = "INSERT INTO products (name, description, category_id, price, cost_price, ingredients, preparation_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute($product);
    }
    
    echo "<p>✓ Sample products added</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Setup Complete!</h3>";
    echo "<p>Your Restaurant Management System has been set up successfully.</p>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <strong>admin</strong></li>";
    echo "<li>Password: <strong>password</strong></li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h4>Important Security Note:</h4>";
    echo "<p>Please change the default admin password immediately after logging in for security reasons.</p>";
    echo "<p>Also consider deleting this setup.php file after installation.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Setup Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management System - Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1 { color: #2c3e50; }
        p { margin: 10px 0; }
    </style>
</head>
<body>
    <!-- Content is echoed from PHP above -->
</body>
</html>