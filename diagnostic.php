<?php
session_start();
include("config/database.php");

echo "<pre style='font-family: monospace; padding: 20px; background: #f5f5f5; border: 1px solid #ddd; margin: 20px; border-radius: 5px;'>";

echo "=== POS SYSTEM DIAGNOSTIC ===\n\n";

// Check session
echo "SESSION STATUS:\n";
echo "---------------\n";
if(isset($_SESSION['user'])){
    echo "✓ User is logged in: " . $_SESSION['user'] . "\n";
    if(isset($_SESSION['role'])){
        echo "✓ Role: " . $_SESSION['role'] . "\n";
    }
} else {
    echo "✗ User is NOT logged in\n";
}

// Check database connection
echo "\nDATABASE STATUS:\n";
echo "---------------\n";
if($conn){
    echo "✓ Database connected successfully\n";
    
    // Check users table
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    if($result){
        $row = mysqli_fetch_assoc($result);
        echo "✓ Users table accessible: " . $row['count'] . " user(s)\n";
    } else {
        echo "✗ Error accessing users table\n";
    }
} else {
    echo "✗ Database connection failed\n";
}

// Check file permissions
echo "\nFILE PATHS:\n";
echo "-----------\n";
echo "Current PHP file: " . __FILE__ . "\n";
echo "Config file: " . realpath(__DIR__ . '/config/database.php') . "\n";
echo "Auth folder: " . realpath(__DIR__ . '/auth') . "\n";
echo "Pages folder: " . realpath(__DIR__ . '/pages') . "\n";

// Recommended actions
echo "\n\nRECOMMENDED ACTIONS:\n";
echo "--------------------\n";

if(!isset($_SESSION['user'])){
    echo "1. Go to: http://localhost/pos_system/auth/login.php\n";
    echo "2. Login with: admin / admin123\n";
    echo "3. Then refresh this page\n";
} else {
    echo "1. Go to: http://localhost/pos_system/pages/dashboard.php\n";
    echo "2. If blank, try:\n";
    echo "   - Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "   - Open Developer Console (F12) to check for errors\n";
    echo "   - Try a different browser\n";
}

echo "\n</pre>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Diagnostics - Grocenix POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background: #4a90e2;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Grocenix POS System - Diagnostic Check</h2>
        
        <div style="margin: 20px 0;">
            <a href="index.php" class="btn btn-primary">← Go to Home</a>
            <a href="auth/login.php" class="btn btn-primary">→ Go to Login</a>
            <?php if(isset($_SESSION['user'])): ?>
                <a href="pages/dashboard.php" class="btn btn-primary">→ Go to Dashboard</a>
                <a href="auth/logout.php" class="btn btn-danger">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>