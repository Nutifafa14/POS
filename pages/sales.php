<?php
session_start();
include("../config/database.php");

// Check login
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit(); // ✅ exit() added
}

$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
<title>Sales</title>
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

    .product-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    .product-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .product-card.selected {
        border-color: #198754;
        background-color: #f0fff4;
    }
    .product-card.selected .card-title { color: #198754; }
</style>
</head>

<body class="bg-light">

<div class="container mt-4">

<div class="d-flex justify-content-between align-items-center mb-3">
    <?php $backPage = ($_SESSION['role'] ?? '') === 'cashier' ? 'cashier_dashboard.php' : 'dashboard.php'; ?>
    <a href="<?php echo $backPage; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
    <div class="text-center flex-grow-1">
        <h2 class="mb-0">POS Sales</h2>
        <p class="text-muted mb-0">Process customer transactions</p>
    </div>
    <!-- Show logged-in user and role -->
    <div class="text-end">
        <small class="text-muted d-block"><?php echo htmlspecialchars($_SESSION['user']); ?></small>
        <span class="badge bg-success"><?php echo ucfirst($_SESSION['role'] ?? 'cashier'); ?></span>
    </div>
</div>
<hr class="my-3">

<div class="row">

    <!-- Add to Cart Side -->
    <div class="col-md-6">
        <div class="card p-3 mb-3">
            <h4>Select Product</h4>

            <form action="../actions/add_to_cart.php" method="POST">

                <!-- Hidden input to hold selected product_id -->
                <input type="hidden" name="product_id" id="selected_product_id" required>

                <!-- Product Cards Grid -->
                <div class="row g-2 mb-3" id="product-grid">
                    <?php while($row = mysqli_fetch_assoc($result)){ ?>
                    <div class="col-6">
                        <div
                            class="card product-card p-2 text-center"
                            onclick="selectProduct(this, '<?php echo $row['product_id']; ?>')"
                            data-id="<?php echo $row['product_id']; ?>"
                        >
                            <img
                                src="../uploads/products/<?php echo !empty($row['image']) ? $row['image'] : 'default.png'; ?>"
                                alt="<?php echo $row['product_name']; ?>"
                                class="card-img-top"
                                style="height: 60px; object-fit: contain; border-radius: 6px; background-color: #f8f9fa;"
                            >
                            <div class="card-body p-1">
                                <h6 class="card-title mb-1"><?php echo $row['product_name']; ?></h6>
                                <span class="badge bg-primary">GH₵ <?php echo number_format($row['price'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>

                <!-- Selected product feedback -->
                <div id="selected-label" class="text-muted small mb-2">No product selected</div>

                <input class="form-control mb-2" type="number" name="quantity" placeholder="Quantity" min="1" required>

                <button class="btn btn-success w-100">Add to Cart</button>

            </form>
        </div>
    </div>

    <!-- Cart Side -->
    <div class="col-md-6">
        <div class="card p-3">
            <h4>Cart</h4>

            <?php
            if(!isset($_SESSION['cart'])){
                $_SESSION['cart'] = [];
            }

            $total = 0;

            if(count($_SESSION['cart']) > 0){
                echo "<table class='table'>";
                foreach($_SESSION['cart'] as $key => $item){
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                    echo "<tr>";
                    echo "<td>".$item['name']."</td>";
                    echo "<td>".$item['quantity']."</td>";
                    echo "<td>GH₵ ".number_format($item_total, 2)."</td>";
                    echo "<td>
                        <a href='../actions/remove_item.php?index=".$key."' class='btn btn-danger btn-sm'>Remove</a>
                    </td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<h5>Total: GH₵ ".number_format($total, 2)."</h5>";
            } else {
                echo "<p class='text-muted'>Cart is empty</p>";
            }
            ?>

            <form id="sale_form" action="../actions/process_sale.php" method="POST">
                <select class="form-control mb-2" id="payment_method" name="payment_method" onchange="togglePhoneField()">
                    <option value="Cash">Cash</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Card">Card</option>
                </select>
                
                <div id="mobile_money_fields" style="display: none;">
                    <input class="form-control mb-2" type="tel" name="mobile_number" id="mobile_number" placeholder="Mobile Money Number (e.g. 024XXXXXXX)">
                    <small class="text-muted">The Customer will receive a payment prompt on their device</small>
                </div>
                <button class="btn btn-primary w-100">Complete Sale</button>
            </form>

        </div>
    </div>

</div>
</div>

<script>
function selectProduct(card, productId) {
    document.querySelectorAll('.product-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('selected_product_id').value = productId;
    const productName = card.querySelector('.card-title').textContent;
    document.getElementById('selected-label').textContent = '✔ Selected: ' + productName;
    document.getElementById('selected-label').classList.replace('text-muted', 'text-success');
}

const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);

function togglePhoneField() {
    const method = document.getElementById('payment_method').value;
    const phoneField = document.getElementById('mobile_money_fields');
    const mobileInput = document.getElementById('mobile_number');
    const saleForm = document.getElementById('sale_form');
    
    phoneField.style.display = (method === 'Mobile Money') ? 'block' : 'none';
    
    if (method === 'Mobile Money') {
        mobileInput.required = true;
        saleForm.action = '../actions/process_mobile_money.php';
    } else {
        mobileInput.required = false;
        saleForm.action = '../actions/process_sale.php';
    }
}
</script>

</body>
</html>