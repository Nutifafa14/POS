<?php
session_start();
include("../config/database.php");

// Check login
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

// Only cashiers should see this page
// Admin and manager get redirected to their dashboard
if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager'){
    header("Location: ../pages/dashboard.php");
    exit();
}

// Get today's sales count for this cashier
$today_sales_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) = CURDATE()");
$today_sales = mysqli_fetch_assoc($today_sales_result)['count'];

// Get today's total revenue
$today_revenue_result = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()");
$today_revenue_row = mysqli_fetch_assoc($today_revenue_result);
$today_revenue = $today_revenue_row['total'] ?? 0;

// Get total customers registered today
$today_customers_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = CURDATE()");
$today_customers = $today_customers_result ? mysqli_fetch_assoc($today_customers_result)['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - Grocenix</title>
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

        * { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-size: 14px;
            line-height: 1.5;
            background-color: #f8f9fa;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
        }

        .action-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: inherit;
        }

        .action-card .card-body {
            padding: 30px 20px;
            text-align: center;
        }

        .action-card i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .action-card h5 {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .time-display {
            font-size: 0.9rem;
            opacity: 0.85;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">

    <!-- Welcome Banner -->
    <div class="welcome-banner mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-person-badge me-2"></i>
                    Hello, <?php echo htmlspecialchars($_SESSION['user']); ?>!
                </h2>
                <p class="mb-0 opacity-75">Cashier — Grocenix POS</p>
            </div>
            <div class="text-end">
                <div class="time-display" id="current-time"></div>
                <div class="time-display"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>
    </div>

    <!-- Today's Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-receipt display-5 text-success mb-2"></i>
                    <h5 class="fw-bold text-success mb-0"><?php echo $today_sales; ?></h5>
                    <small class="text-muted">Sales Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cash-coin display-5 text-primary mb-2"></i>
                    <h5 class="fw-bold text-primary mb-0">GH₵ <?php echo number_format($today_revenue, 2); ?></h5>
                    <small class="text-muted">Revenue Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-people display-5 text-info mb-2"></i>
                    <h5 class="fw-bold text-info mb-0"><?php echo $today_customers; ?></h5>
                    <small class="text-muted">New Customers Today</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Actions -->
    <h5 class="fw-bold mb-3"><i class="bi bi-grid me-2"></i>What would you like to do?</h5>
    <div class="row g-3 mb-4">

        <!-- POS Sale — primary action -->
        <div class="col-md-6">
            <a href="sales.php" class="action-card card bg-success text-white">
                <div class="card-body">
                    <i class="bi bi-cart-check-fill text-white"></i>
                    <h5>Start a Sale</h5>
                    <p class="mb-0 opacity-75 small">Scan products and process a customer transaction</p>
                </div>
            </a>
        </div>

        <!-- Customers -->
        <div class="col-md-6">
            <a href="customers.php" class="action-card card bg-info text-white">
                <div class="card-body">
                    <i class="bi bi-people-fill text-white"></i>
                    <h5>Customers</h5>
                    <p class="mb-0 opacity-75 small">Register a new customer or look up an existing one</p>
                </div>
            </a>
        </div>

    </div>

    <!-- Cashier Notice -->
    <div class="alert alert-light border d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle-fill text-primary me-3 fs-5"></i>
        <div>
            <strong>Cashier Access:</strong> You can process sales and manage customer records.
            For product or inventory changes, please contact your manager or admin.
        </div>
    </div>

    <!-- Logout -->
    <div class="text-center mt-4">
        <a href="../auth/logout.php" class="btn btn-outline-danger">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Live clock
    function updateTime() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        document.getElementById('current-time').textContent =
            `${String(hours).padStart(2,'0')}:${minutes}:${seconds} ${ampm}`;
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>
</body>
</html>