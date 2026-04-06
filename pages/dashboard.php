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
$low_stock = 0;

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

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE quantity < 5");
if($result) $low_stock = mysqli_fetch_assoc($result)['count'];

// Get current user role
$role = $_SESSION['role'] ?? 'cashier';

// Redirect cashiers to dedicated cashier dashboard
if($role === 'cashier'){
    header("Location: cashier_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Grocenix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --light-bg: #f8f9fa;
            --dark-bg: #1a1a1a;
            --dark-card: #2d2d2d;
            --dark-text: #ffffff;
            --dark-text-secondary: #b0b0b0;
        }

        [data-theme="dark"] {
            --bg-color: var(--dark-bg);
            --card-bg: var(--dark-card);
            --text-color: var(--dark-text);
        }

        * { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-size: 14px;
            line-height: 1.5;
            background-color: var(--light-bg, #f8f9fa);
            color: var(--text-color, #212529);
        }

        [data-theme="dark"] body {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .card {
            background-color: var(--dark-card) !important;
            color: var(--dark-text) !important;
        }

        [data-theme="dark"] .text-muted {
            color: var(--dark-text-secondary) !important;
        }

        .metric-card {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            border-radius: 10px;
        }

        .nav-link {
            color: var(--text-color) !important;
            text-decoration: none;
            display: block;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: white;
        }

        [data-theme="dark"] .nav-link { background: var(--dark-card); }

        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        h1, h2, h3 { font-weight: 600; }

        /* Role badge styling */
        .role-badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h2 mb-1">
                                <i class="bi bi-shop me-2"></i>Welcome to Grocenix
                            </h1>
                            <p class="text-muted mb-0">Manage your store efficiently</p>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-1">Logged in as:</h6>
                            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($_SESSION['user']); ?></p>
                            <!-- Role Badge -->
                            <?php if($role == 'admin'): ?>
                                <span class="badge bg-danger role-badge">Administrator</span>
                            <?php elseif($role == 'manager'): ?>
                                <span class="badge bg-warning text-dark role-badge">Manager</span>
                            <?php else: ?>
                                <span class="badge bg-success role-badge">Cashier</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics — Admin/Manager see all, Cashier sees only sales -->
        <div class="row mb-4">

            <?php if($role == 'admin' || $role == 'manager'): ?>
            <div class="col-md-3 mb-3">
                <div class="card metric-card text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam display-4 mb-2"></i>
                        <h5 class="card-title">Total Products</h5>
                        <h2 class="mb-0"><?php echo number_format($products_count); ?></h2>
                        <small class="opacity-75">In inventory</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-receipt display-4 mb-2"></i>
                        <h5 class="card-title">Total Sales</h5>
                        <h2 class="mb-0"><?php echo number_format($sales_count); ?></h2>
                        <small class="opacity-75">Transactions</small>
                    </div>
                </div>
            </div>

            <?php if($role == 'admin' || $role == 'manager'): ?>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-people display-4 mb-2"></i>
                        <h5 class="card-title">Customers</h5>
                        <h2 class="mb-0"><?php echo number_format($customers_count); ?></h2>
                        <small class="opacity-75">Registered</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-cash-coin display-4 mb-2"></i>
                        <h5 class="card-title">Revenue</h5>
                        <h2 class="mb-0">GH₵ <?php echo number_format($total_revenue, 2); ?></h2>
                        <small class="opacity-75">Total earned</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Quick Actions
                </h3>
            </div>
        </div>

        <!-- Navigation — role-based -->
        <div class="row g-3">

            <!-- Point of Sale: visible to ALL roles -->
            <div class="col-lg-4 col-md-6">
                <a href="sales.php" class="nav-link text-decoration-none text-dark h-100">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cart-check-fill display-6 me-3 text-success"></i>
                        <div>
                            <h5 class="mb-0">Point of Sale</h5>
                            <small class="text-muted">Process sales</small>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Admin/Manager only pages below -->
            <?php if($role == 'admin' || $role == 'manager'): ?>

            <div class="col-lg-4 col-md-6">
                <a href="products.php" class="nav-link text-decoration-none text-dark h-100">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-box-seam-fill display-6 me-3 text-primary"></i>
                        <div>
                            <h5 class="mb-0">Products</h5>
                            <small class="text-muted">Manage inventory</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="customers.php" class="nav-link text-decoration-none text-dark h-100">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people-fill display-6 me-3 text-info"></i>
                        <div>
                            <h5 class="mb-0">Customers</h5>
                            <small class="text-muted">Manage clients</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="inventory.php" class="nav-link text-decoration-none text-dark h-100">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clipboard-data-fill display-6 me-3 text-warning"></i>
                        <div>
                            <h5 class="mb-0">Inventory</h5>
                            <small class="text-muted">Stock levels</small>
                            <?php if($low_stock > 0): ?>
                                <br><span class="badge bg-danger"><?php echo $low_stock; ?> items low</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="reports.php" class="nav-link text-decoration-none text-dark h-100">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-graph-up-arrow display-6 me-3 text-danger"></i>
                        <div>
                            <h5 class="mb-0">Reports</h5>
                            <small class="text-muted">Analytics & insights</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php endif; ?>

            <!-- Settings: Admin only -->
            <?php if($role == 'admin'): ?>
            <div class="col-lg-4 col-md-6">
                <a href="settings.php" class="nav-link text-decoration-none text-dark h-100">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-gear-fill display-6 me-3 text-secondary"></i>
                        <div>
                            <h5 class="mb-0">Settings</h5>
                            <small class="text-muted">Configuration</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

        </div>

        <!-- Logout -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="../auth/logout.php" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</body>
</html>