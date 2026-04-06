<?php
session_start();
include("../config/database.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_GET['sale_id']) || empty($_GET['sale_id'])){
    die('Sale ID is missing. Please go back and try again.');
}

$sale_id = intval($_GET['sale_id']);

// Fetch sale data
$sale_query = "SELECT * FROM sales WHERE sale_id = $sale_id";
$sale_result = mysqli_query($conn, $sale_query);

if(!$sale_result || mysqli_num_rows($sale_result) === 0){
    die('Sale not found. Receipt cannot be generated.');
}

$sale = mysqli_fetch_assoc($sale_result);

$items_query = "SELECT si.quantity, si.price, p.product_name
FROM sales_items si
LEFT JOIN products p ON si.product_id = p.product_id
WHERE si.sale_id = $sale_id";
$items_result = mysqli_query($conn, $items_query);

if(!$items_result){
    die('Unable to fetch sale items.');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Grocenix</title>
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

        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

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

        .card, .receipt-container {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] .card,
        [data-theme="dark"] .receipt-container {
            background-color: var(--dark-card) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text) !important;
        }

        [data-theme="dark"] .text-muted {
            color: var(--dark-text-secondary) !important;
        }

        [data-theme="dark"] .table {
            color: var(--dark-text);
        }

        [data-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .h1, h1 { font-size: 2rem; }
        .h2, h2 { font-size: 1.75rem; }
        .h3, h3 { font-size: 1.5rem; }
        .h4, h4 { font-size: 1.25rem; }
        .h5, h5 { font-size: 1.1rem; }
        .h6, h6 { font-size: 1rem; }

        .display-4 { font-size: 2.5rem; }
        .display-5 { font-size: 2rem; }
        .display-6 { font-size: 1.75rem; }

        .btn-lg { font-size: 1rem; }
        .btn-sm { font-size: 0.875rem; }

        .badge {
            font-size: 0.75rem;
        }

        .alert {
            font-size: 0.875rem;
        }

        .small {
            font-size: 0.875rem;
        }

        .receipt-container {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid #e3e3e3;
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .receipt-container .table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .receipt-container .table th,
        .receipt-container .table td {
            padding: 0.45rem 0.4rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
            word-break: break-word;
            white-space: break-spaces;
        }

        .receipt-container .table th {
            text-transform: uppercase;
            letter-spacing: 0.01em;
            font-size: 0.75rem;
            min-width: 55px;
        }

        .receipt-container .table td {
            font-size: 0.825rem;
        }

        .receipt-container .table td.text-end,
        .receipt-container .table th.text-end {
            text-align: right !important;
        }

        @media print {
            .no-print { display: none !important; }
            .receipt-container {
                box-shadow: none;
                border: none;
                background: white;
                margin: 0;
                width: 100%;
                max-width: 380px;
                padding: 12px;
            }

            .receipt-container .table th,
            .receipt-container .table td {
                font-size: 0.72rem;
                padding: 3px 4px;
            }
        }
    </style>
</head>

<body class="bg-light py-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="sales.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <div class="text-center flex-grow-1">
            <h3 class="mb-0">Sale Receipt</h3>
            <p class="text-muted mb-0">Transaction Details</p>
        </div>
    </div>
    <hr class="my-3 no-print">

    <div class="receipt-container">
        <div class="text-center mb-4">
            <h4 class="mb-1">GROCENIX RECEIPT</h4>
            <hr class="my-2">
        </div>

        <div class="mb-3">
            <div class="row">
                <div class="col-6"><strong>Transaction ID:</strong></div>
                <div class="col-6 text-end"><?php echo $sale_id; ?></div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Date:</strong></div>
                <div class="col-6 text-end"><?php echo date('M d, Y H:i', strtotime($sale['sale_date'])); ?></div>
            </div>
        </div>

        <hr class="my-3">

        <div class="table-responsive">
            <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $total = 0;
                while($row = mysqli_fetch_assoc($items_result)){
                    $item_total = $row['price'] * $row['quantity'];
                    $total += $item_total;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td class="text-center"><?php echo $row['quantity']; ?></td>
                    <td class="text-end">GH₵ <?php echo number_format($row['price'], 2); ?></td>
                    <td class="text-end">GH₵ <?php echo number_format($item_total, 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        </div>

        <hr class="my-3">

        <div class="row">
            <div class="col-6"><strong>Total Amount:</strong></div>
            <div class="col-6 text-end"><h5 class="mb-0 text-success">GH₵ <?php echo number_format($total, 2); ?></h5></div>
        </div>

        <div class="row mt-2">
            <div class="col-6"><strong>Payment Method:</strong></div>
            <div class="col-6 text-end"><?php echo htmlspecialchars($sale['payment_method']); ?></div>
        </div>
    </div>

    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="bi bi-printer me-1"></i>Print Receipt
        </button>
        <a href="sales.php" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>New Sale
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Dark mode functionality
    function initTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    }

    // Toast notification function
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', initTheme);

    // Add keyboard shortcut for theme toggle (Ctrl/Cmd + Shift + D)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            toggleTheme();
            showToast(`Switched to ${document.documentElement.getAttribute('data-theme')} mode`, 'info');
        }
    });
</script>
</body>
</html>