<?php
session_start();
include("../config/database.php");

// Check login
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

// ✅ ROLE CHECK — Only admin can access settings
if($_SESSION['role'] != 'admin'){
    header("Location: ../pages/sales.php");
    exit();
}

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['update_profile'])){
        $message = "Profile updated successfully!";
    }
    if(isset($_POST['update_store'])){
        $message = "Store settings updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Grocenix</title>
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

        [data-theme="dark"] .settings-card,
        [data-theme="dark"] .card {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }

        [data-theme="dark"] .text-muted { color: var(--dark-text-secondary) !important; }

        [data-theme="dark"] .form-control {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }

        [data-theme="dark"] .input-group-text {
            background-color: var(--dark-border);
            border-color: var(--dark-border);
            color: var(--dark-text-secondary);
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
                        <h1 class="h3 mb-0"><i class="bi bi-gear me-2"></i>System Settings</h1>
                        <p class="text-muted mb-0">Configure your POS system preferences</p>
                    </div>
                </div>
                <hr class="my-3">
            </div>
        </div>

        <?php if(isset($message)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- User Profile Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card settings-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-gear me-2"></i>User Profile
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?php echo htmlspecialchars($_SESSION['user']); ?>" readonly>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Current Password
                                </label>
                                <input type="password" class="form-control" id="current_password" name="current_password"
                                       placeholder="Enter current password">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>New Password
                                </label>
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                       placeholder="Enter new password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Confirm New Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                       placeholder="Confirm new password">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Store Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card settings-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shop me-2"></i>Store Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="store_name" class="form-label">
                                    <i class="bi bi-building me-1"></i>Store Name
                                </label>
                                <input type="text" class="form-control" id="store_name" name="store_name"
                                       value="Grocenix" placeholder="Enter store name">
                            </div>
                            <div class="mb-3">
                                <label for="store_address" class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i>Store Address
                                </label>
                                <textarea class="form-control" id="store_address" name="store_address" rows="3"
                                          placeholder="Enter store address">123 Main Street, City, Country</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="store_phone" class="form-label">
                                    <i class="bi bi-telephone me-1"></i>Store Phone
                                </label>
                                <input type="tel" class="form-control" id="store_phone" name="store_phone"
                                       value="+1-234-567-8900" placeholder="Enter store phone">
                            </div>
                            <div class="mb-3">
                                <label for="store_email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Store Email
                                </label>
                                <input type="email" class="form-control" id="store_email" name="store_email"
                                       value="store@grocenix.com" placeholder="Enter store email">
                            </div>
                            <div class="mb-3">
                                <label for="tax_rate" class="form-label">
                                    <i class="bi bi-percent me-1"></i>Tax Rate (%)
                                </label>
                                <input type="number" class="form-control" id="tax_rate" name="tax_rate"
                                       value="15" step="0.01" min="0" max="100">
                            </div>
                            <button type="submit" name="update_store" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Update Store Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Preferences -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card settings-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-sliders me-2"></i>System Preferences
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Display Settings</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="dark_mode" onchange="toggleDarkMode()">
                                    <label class="form-check-label" for="dark_mode">Enable Dark Mode</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="compact_view">
                                    <label class="form-check-label" for="compact_view">Compact View for Tables</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="auto_refresh" checked>
                                    <label class="form-check-label" for="auto_refresh">Auto-refresh Dashboard Data</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Notification Settings</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="low_stock_alerts" checked>
                                    <label class="form-check-label" for="low_stock_alerts">Low Stock Alerts</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="daily_reports" checked>
                                    <label class="form-check-label" for="daily_reports">Daily Sales Reports</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="backup_reminders">
                                    <label class="form-check-label" for="backup_reminders">Database Backup Reminders</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary">
                                <i class="bi bi-save me-1"></i>Save Preferences
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card settings-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Account Actions
                                </h5>
                                <p class="mb-0 text-muted">Manage your account and session</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="../auth/logout.php?confirm=1" class="btn btn-danger btn-lg">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </div>
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

        if (currentTheme === 'dark') {
            document.getElementById('dark_mode').checked = true;
        }

        function toggleDarkMode() {
            const checkbox = document.getElementById('dark_mode');
            const theme = checkbox.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const compactView = localStorage.getItem('compact_view') === 'true';
            const autoRefresh = localStorage.getItem('auto_refresh') !== 'false';
            document.getElementById('compact_view').checked = compactView;
            document.getElementById('auto_refresh').checked = autoRefresh;
        });

        document.getElementById('compact_view').addEventListener('change', function() {
            localStorage.setItem('compact_view', this.checked);
        });

        document.getElementById('auto_refresh').addEventListener('change', function() {
            localStorage.setItem('auto_refresh', this.checked);
        });
    </script>
</body>
</html>