<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config/database.php");

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

// Get metrics
$products_count = 0;
$sales_count = 0;
$customers_count = 0;
$total_revenue = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if($result) $products_count = mysqli_fetch_assoc($result)['count'];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM sales");
if($result) $sales_count = mysqli_fetch_assoc($result)['count'];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM customers");
if($result) $customers_count = mysqli_fetch_assoc($result)['count'];

$result = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM sales");
if($result) {
    $row = mysqli_fetch_assoc($result);
    $total_revenue = $row['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Grocenix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .card { margin: 10px 0; }
        h1 { color: #4a90e2; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="bi bi-shop"></i> Grocenix Dashboard</h1>
        <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></p>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Products</h5>
                        <h2><?php echo $products_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Sales</h5>
                        <h2><?php echo $sales_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Customers</h5>
                        <h2><?php echo $customers_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Revenue</h5>
                        <h2>GH₵ <?php echo number_format($total_revenue, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="mt-4">
            <h3>Navigation</h3>
            <a href="products.php" class="btn btn-primary">Products</a>
            <a href="inventory.php" class="btn btn-info">Inventory</a>
            <a href="customers.php" class="btn btn-success">Customers</a>
            <a href="sales.php" class="btn btn-warning">POS</a>
            <a href="reports.php" class="btn btn-danger">Reports</a>
            <a href="settings.php" class="btn btn-secondary">Settings</a>
            <a href="../auth/logout.php" class="btn btn-dark">Logout</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>