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

$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Grocenix</title>
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

        [data-theme="dark"] body {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        [data-theme="dark"] .card {
            background-color: var(--dark-card) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text) !important;
        }

        [data-theme="dark"] .text-muted { color: var(--dark-text-secondary) !important; }
        [data-theme="dark"] .table { color: var(--dark-text); }

        .low-stock {
            background-color: #ffe6e6;
            border-left: 4px solid #dc3545;
        }
        .normal-stock {
            background-color: #e6ffe6;
            border-left: 4px solid #28a745;
        }
        .inventory-card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        [data-theme="dark"] .low-stock {
            background-color: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
        }

        [data-theme="dark"] .normal-stock {
            background-color: rgba(40, 167, 69, 0.1);
            border-left-color: #28a745;
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
                        <h1 class="h3 mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Management</h1>
                        <p class="text-muted mb-0">Monitor your product stock levels</p>
                    </div>
                </div>
                <hr class="my-3">
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="row">
            <div class="col-12">
                <div class="card inventory-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Product Inventory</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold px-4 py-3">Product Name</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Category</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Stock Level</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)){
                                        $stockClass = $row['quantity'] < 5 ? 'low-stock' : 'normal-stock';
                                        $statusIcon = $row['quantity'] < 5 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success';
                                        $statusText = $row['quantity'] < 5 ? 'Low Stock' : 'In Stock';
                                    ?>
                                    <tr class="<?php echo $stockClass; ?>">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-box me-3 text-muted"></i>
                                                <span class="fw-medium"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['category']); ?></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="fw-bold fs-5"><?php echo $row['quantity']; ?></span>
                                            <small class="text-muted ms-1">units</small>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge <?php echo $row['quantity'] < 5 ? 'bg-danger' : 'bg-success'; ?>">
                                                <i class="bi <?php echo $statusIcon; ?> me-1"></i>
                                                <?php echo $statusText; ?>
                                            </span>
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

        <!-- Summary Stats -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-box-seam display-4 text-primary mb-2"></i>
                        <h5 class="card-title">Total Products</h5>
                        <?php
                        $count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
                        $count_row = mysqli_fetch_assoc($count_result);
                        ?>
                        <p class="card-text fs-4 fw-bold text-primary"><?php echo $count_row['total']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-exclamation-triangle display-4 text-warning mb-2"></i>
                        <h5 class="card-title">Low Stock Items</h5>
                        <?php
                        $low_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE quantity < 5");
                        $low_row = mysqli_fetch_assoc($low_result);
                        ?>
                        <p class="card-text fs-4 fw-bold text-warning"><?php echo $low_row['total']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-check-circle display-4 text-success mb-2"></i>
                        <h5 class="card-title">Well Stocked</h5>
                        <?php
                        $well_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE quantity >= 5");
                        $well_row = mysqli_fetch_assoc($well_result);
                        ?>
                        <p class="card-text fs-4 fw-bold text-success"><?php echo $well_row['total']; ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
    </script>
</body>
</html>