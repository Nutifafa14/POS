<?php
session_start();
include("../config/database.php");

// Check login
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

// ✅ All roles can access this page — cashiers can view and add only
$role = $_SESSION['role'] ?? 'cashier';

$query = "SELECT * FROM customers";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Grocenix</title>
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

        [data-theme="dark"] .card {
            background-color: var(--dark-card) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text) !important;
        }

        [data-theme="dark"] .text-muted { color: var(--dark-text-secondary) !important; }
        [data-theme="dark"] .table { color: var(--dark-text); }

        .customer-card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .form-section {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            border-radius: 15px;
            color: white;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <!-- Back button goes to correct dashboard based on role -->
                    <a href="<?php echo ($role == 'cashier') ? 'cashier_dashboard.php' : 'dashboard.php'; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </a>
                    <div class="text-center flex-grow-1">
                        <h1 class="h3 mb-0"><i class="bi bi-people-fill me-2"></i>Customer Management</h1>
                        <p class="text-muted mb-0">
                            <?php if($role == 'cashier'): ?>
                                Register new customers or look up existing ones
                            <?php else: ?>
                                Manage your customer database
                            <?php endif; ?>
                        </p>
                    </div>
                    <!-- Role badge -->
                    <div class="text-end">
                        <?php if($role == 'cashier'): ?>
                            <span class="badge bg-success">Cashier View</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Admin View</span>
                        <?php endif; ?>
                    </div>
                </div>
                <hr class="my-3">
            </div>
        </div>

        <div class="row">
            <!-- Add Customer Form — visible to ALL roles -->
            <div class="col-lg-4 mb-4">
                <div class="card customer-card form-section">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 text-white">
                            <i class="bi bi-person-plus-fill me-2"></i>Add New Customer
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="../actions/add_customer.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label text-white">
                                    <i class="bi bi-person me-1"></i>Customer Name *
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                       placeholder="Enter customer name" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label text-white">
                                    <i class="bi bi-telephone me-1"></i>Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       placeholder="Enter phone number">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label text-white">
                                    <i class="bi bi-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="Enter email address">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label text-white">
                                    <i class="bi bi-geo-alt me-1"></i>Address
                                </label>
                                <textarea class="form-control" id="address" name="address" rows="3"
                                          placeholder="Enter customer address"></textarea>
                            </div>
                            <button type="submit" class="btn btn-light w-100">
                                <i class="bi bi-person-plus me-2"></i>Add Customer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Customer List -->
            <div class="col-lg-8">
                <div class="card customer-card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-check me-2"></i>Customer Directory
                            </h5>
                            <span class="badge bg-primary fs-6">
                                <?php echo mysqli_num_rows($result); ?> Customers
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold px-4 py-3">ID</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Customer Name</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Phone</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Email</th>
                                        <th class="border-0 fw-semibold px-4 py-3">Address</th>
                                        <!-- Actions column only shown to admin/manager -->
                                        <?php if($role == 'admin' || $role == 'manager'): ?>
                                        <th class="border-0 fw-semibold px-4 py-3">Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)){ ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-secondary">#<?php echo $row['customer_id']; ?></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle me-3 text-primary"></i>
                                                <span class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if($row['phone']): ?>
                                                <i class="bi bi-telephone me-2 text-muted"></i>
                                                <?php echo htmlspecialchars($row['phone']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if($row['email']): ?>
                                                <i class="bi bi-envelope me-2 text-muted"></i>
                                                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($row['email']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if($row['address']): ?>
                                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                                <?php echo htmlspecialchars($row['address']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Edit/Delete — admin and manager only -->
                                        <?php if($role == 'admin' || $role == 'manager'): ?>
                                        <td class="px-4 py-3">
                                            <button class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm ms-1">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                        <?php endif; ?>
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
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
    </script>
</body>
</html>