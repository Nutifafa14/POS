<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "pos_system"; 

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$tables = [];

$tables[] = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'cashier') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$tables[] = "CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 0,
    barcode VARCHAR(50) UNIQUE
)";

$tables[] = "CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    loyalty_points INT DEFAULT 0
)";

$tables[] = "CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    customer_id INT,
    total_amount DECIMAL(10,2),
    payment_method ENUM('cash', 'mobile_money', 'card'),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
)";

$tables[] = "CREATE TABLE IF NOT EXISTS sales_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
)";

$tables[] = "CREATE TABLE IF NOT EXISTS inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    adjustment INT,
    reason VARCHAR(100),
    adjusted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
)";

$tables[] = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    amount_paid DECIMAL(10,2),
    change_given DECIMAL(10,2),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id)
)";

foreach ($tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Table created successfully <br>";
    } else {
        echo "Error: " . mysqli_error($conn) . "<br>";
    }
}

// Insert default admin user
$default_password = password_hash('admin123', PASSWORD_DEFAULT);
$default_user_sql = "INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$default_password', 'admin')";

if (mysqli_query($conn, $default_user_sql)) {
    echo "Default admin user created successfully! <br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
} else {
    echo "Error creating default user: " . mysqli_error($conn) . "<br>";
}

echo "<br> All tables are ready and default user is set up!";
mysqli_close($conn);
?>