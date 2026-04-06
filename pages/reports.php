<?php
session_start();
include("../config/database.php");

// Check login
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

// ✅ ROLE CHECK — Cashiers cannot access this page
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager'){
    header("Location: ../pages/sales.php");
    exit();
}

// Total Sales and Transactions
$query = "SELECT SUM(total_amount) as total_sales FROM sales";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

$query2 = "SELECT COUNT(*) as transactions FROM sales";
$result2 = mysqli_query($conn, $query2);
$data2 = mysqli_fetch_assoc($result2);

// Today's Sales
$query3 = "SELECT SUM(total_amount) as today_sales FROM sales WHERE DATE(sale_date) = CURDATE()";
$result3 = mysqli_query($conn, $query3);
$data3 = mysqli_fetch_assoc($result3);

// This Month's Sales
$query4 = "SELECT SUM(total_amount) as month_sales FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())";
$result4 = mysqli_query($conn, $query4);
$data4 = mysqli_fetch_assoc($result4);

// Top Selling Products
$query5 = "SELECT p.product_name, SUM(si.quantity) as total_sold
           FROM sales_items si
           JOIN products p ON si.product_id = p.product_id
           GROUP BY si.product_id, p.product_name
           ORDER BY total_sold DESC
           LIMIT 5";
$result5 = mysqli_query($conn, $query5);

// Low Stock Products
$query6 = "SELECT product_name, quantity FROM products WHERE quantity < 5 ORDER BY quantity ASC";
$result6 = mysqli_query($conn, $query6);

// Recent Sales
$query7 = "SELECT s.sale_id, s.sale_date, s.total_amount, c.name as customer_name
           FROM sales s
           LEFT JOIN customers c ON s.customer_id = c.customer_id
           ORDER BY s.sale_date DESC
           LIMIT 10";
$result7 = mysqli_query($conn, $query7);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Grocenix</title>
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
            --border-color: #dee2e6;
            --dark-border: #404040;
        }

        [data-theme="dark"] {
            --bg-color: var(--dark-bg);
            --card-bg: var(--dark-card);
            --text-color: var(--dark-text);
            --text-secondary: var(--dark-text-secondary);
            --border-color: var(--dark-border);
        }

        * { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-size: 14px;
            line-height: 1.5;
            background-color: var(--light-bg, #f8f9fa);
            color: var(--text-color, #212529);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] body { background-color: var(--dark-bg); color: var(--dark-text); }

        [data-theme="dark"] .card,
        [data-theme="dark"] .report-card {
            background-color: var(--dark-card) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text) !important;
        }

        [data-theme="dark"] .text-muted { color: var(--dark-text-secondary) !important; }
        [data-theme="dark"] .table { color: var(--dark-text); }

        .report-card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            transition: transform 0.2s;
        }
        .report-card:hover { transform: translateY(-2px); }

        .metric-card {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            border-radius: 15px;
        }

        .alert-low-stock {
            background-color: #ffe6e6;
            border-left: 4px solid #dc3545;
        }

        [data-theme="dark"] .alert-low-stock {
            background-color: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </a>
                    <div class="text-center flex-grow-1">
                        <h1 class="h3 mb-0"><i class="bi bi-graph-up me-2"></i>Sales Reports & Analytics</h1>
                        <p class="text-muted mb-0">Comprehensive business insights and performance metrics</p>
                    </div>
                </div>
                <hr class="my-3">
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card metric-card text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-cash-coin display-4 mb-2"></i>
                        <h5 class="card-title">Total Revenue</h5>
                        <h2 class="mb-0">GH₵ <?php echo number_format($data['total_sales'] ?? 0, 2); ?></h2>
                        <small class="opacity-75">All time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card metric-card text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-receipt display-4 mb-2"></i>
                        <h5 class="card-title">Total Transactions</h5>
                        <h2 class="mb-0"><?php echo number_format($data2['transactions'] ?? 0); ?></h2>
                        <small class="opacity-75">All time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-day display-4 mb-2"></i>
                        <h5 class="card-title">Today's Sales</h5>
                        <h2 class="mb-0">GH₵ <?php echo number_format($data3['today_sales'] ?? 0, 2); ?></h2>
                        <small class="opacity-75">Today</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-month display-4 mb-2"></i>
                        <h5 class="card-title">Monthly Sales</h5>
                        <h2 class="mb-0">GH₵ <?php echo number_format($data4['month_sales'] ?? 0, 2); ?></h2>
                        <small class="opacity-75">This month</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Selling Products -->
            <div class="col-lg-6 mb-4">
                <div class="card report-card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-trophy-fill me-2 text-warning"></i>Top Selling Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Units Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $rank = 1;
                                    while($row = mysqli_fetch_assoc($result5)){
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-warning me-3"><?php echo $rank++; ?></span>
                                                <span><?php echo htmlspecialchars($row['product_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold"><?php echo $row['total_sold']; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-lg-6 mb-4">
                <div class="card report-card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Low Stock Alert
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($result6) > 0){ ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Current Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($result6)){ ?>
                                        <tr class="alert-low-stock">
                                            <td>
                                                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                                                <?php echo htmlspecialchars($row['product_name']); ?>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-danger"><?php echo $row['quantity']; ?> units</span>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success display-4 mb-3"></i>
                                <h5 class="text-success">All Stock Levels Good!</h5>
                                <p class="text-muted">No products are currently low on stock.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="row">
            <div class="col-12">
                <div class="card report-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Recent Transactions
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold px-4 py-3">Sale ID</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Date</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Customer</th>
                                        <th class="border-0 fw-semibold px-4 py-3 text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result7)){ ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-secondary">#<?php echo $row['sale_id']; ?></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <i class="bi bi-calendar me-2 text-muted"></i>
                                            <?php echo date('M d, Y H:i', strtotime($row['sale_date'])); ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <i class="bi bi-person me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in Customer'); ?>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <span class="fw-bold text-success">GH₵ <?php echo number_format($row['total_amount'], 2); ?></span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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