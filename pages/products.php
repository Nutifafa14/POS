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
<html>

<head>
<title>Products</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
    <div class="text-center flex-grow-1">
        <h2 class="mb-0">Product Management</h2>
        <p class="text-muted mb-0">Add, view, and manage your products</p>
    </div>
</div>
<hr class="my-3">

<!-- Add Product Form -->
<div class="card p-3 mb-4">
<h4>Add Product</h4>
<form action="../actions/add_product.php" method="POST" enctype="multipart/form-data">
    <input class="form-control mb-2" type="text" name="product_name" placeholder="Product Name" required>
    <input class="form-control mb-2" type="text" name="category" placeholder="Category" required>
    <input class="form-control mb-2" type="number" name="price" placeholder="Price" required>
    <input class="form-control mb-2" type="number" name="quantity" placeholder="Quantity" required>
    <input class="form-control mb-2" type="text" name="barcode" placeholder="Barcode" required>
    <label class="form-label text-muted small">Product Image (optional)</label>
    <input class="form-control mb-2" type="file" name="product_image" accept="image/*">
    <button class="btn btn-primary">Add Product</button>
</form>
</div>

<!-- Product List -->
<div class="card p-3">
<h4>Product List</h4>
<table class="table table-bordered">
<tr>
    <th>ID</th>
    <th>Image</th>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['product_id']; ?></td>
    <td>
        <img
            src="../uploads/products/<?php echo !empty($row['image']) ? $row['image'] : 'default.png'; ?>"
            alt="<?php echo $row['product_name']; ?>"
            style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px;"
        >
    </td>
    <td><?php echo $row['product_name']; ?></td>
    <td><?php echo $row['category']; ?></td>
    <td>GH₵ <?php echo number_format($row['price'], 2); ?></td>
    <td><?php echo $row['quantity']; ?></td>
    <td>
        <button
            class="btn btn-warning btn-sm"
            onclick="openEdit(
                '<?php echo $row['product_id']; ?>',
                '<?php echo addslashes($row['product_name']); ?>',
                '<?php echo addslashes($row['category']); ?>',
                '<?php echo $row['price']; ?>',
                '<?php echo $row['quantity']; ?>',
                '<?php echo $row['barcode']; ?>'
            )"
        >Edit</button>
    </td>
</tr>
<?php } ?>

</table>
</div>

</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="../actions/update_product.php" method="POST" enctype="multipart/form-data">
      <div class="modal-body">
            <input type="hidden" name="product_id" id="edit_id">
            <input class="form-control mb-2" type="text" name="product_name" id="edit_name" placeholder="Product Name" required>
            <input class="form-control mb-2" type="text" name="category" id="edit_category" placeholder="Category" required>
            <input class="form-control mb-2" type="number" name="price" id="edit_price" placeholder="Price" required>
            <input class="form-control mb-2" type="number" name="quantity" id="edit_quantity" placeholder="Quantity" required>
            <input class="form-control mb-2" type="text" name="barcode" id="edit_barcode" placeholder="Barcode">
            <label class="form-label text-muted small">Upload New Image (optional)</label>
            <input class="form-control mb-2" type="file" name="product_image" accept="image/*">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEdit(id, name, category, price, quantity, barcode) {
    document.getElementById('edit_id').value       = id;
    document.getElementById('edit_name').value     = name;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_price').value    = price;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_barcode').value  = barcode;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

</body>
</html>