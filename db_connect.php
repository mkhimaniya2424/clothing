<?php
// Database connection configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "clothing_store";

// Establish connection
$con = mysqli_connect($host, $user, $pass);

// Check connection
if (!$con) {
    die("❌ Database Connection Failed: " . mysqli_connect_error());
}

// Select database
try {
    mysqli_select_db($con, $dbname);
} catch (Exception $e) {
    die("❌ Error selecting database: " . $e->getMessage());
}

// Uncomment this section ONCE if you want to auto-create the table
// Table creation code
$create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_pic VARCHAR(255),
    gender ENUM('male','female','other'),
    dob DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$create_address_table = "CREATE TABLE IF NOT EXISTS user_address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Unknown',
    address_type ENUM('home','office','other') DEFAULT 'home',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

$create_admin_table = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$create_categories_table = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$create_products_table = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)";

$create_orders_table = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending','completed','failed') DEFAULT 'pending',
    order_status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

$create_order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

$create_brands_table = "CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$create_product_stock_table = "CREATE TABLE IF NOT EXISTS product_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNIQUE,
    stock INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

$create_offers_table = "CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_percentage DECIMAL(5,2) NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('active','disabled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Execute table creation
if (isset($_GET['setup_db'])) {
    if (mysqli_query($con, $create_users_table)) {
        echo "✅ Table 'users' created/checked successfully.<br>";
    } else {
        echo "❌ Error creating 'users' table: " . mysqli_error($con) . "<br>";
    }

    if (mysqli_query($con, $create_address_table)) {
        echo "✅ Table 'user_address' created/checked successfully.<br>";
    } else {
        echo "❌ Error creating 'user_address' table: " . mysqli_error($con) . "<br>";
    }

    if (mysqli_query($con, $create_admin_table)) {
        echo "✅ Table 'admin' created/checked successfully.<br>";
        
        // Insert default admin if not exists (password: admin123)
        // Hash: *SHA1(SHA1(password))
        $admin_pass = "*" . strtoupper(sha1(sha1("admin123", true)));
        $check_admin = mysqli_query($con, "SELECT * FROM admin WHERE username='admin'");
        if (mysqli_num_rows($check_admin) == 0) {
            mysqli_query($con, "INSERT INTO admin (username, password, full_name) VALUES ('admin', '$admin_pass', 'Super Admin')");
            echo "✅ Default admin user created (User: admin, Pass: admin123)<br>";
        }
    } else {
        echo "❌ Error creating 'admin' table: " . mysqli_error($con) . "<br>";
    }

    if (mysqli_query($con, $create_categories_table)) echo "✅ Table 'categories' created/checked successfully.<br>";
    else echo "❌ Error creating 'categories' table: " . mysqli_error($con) . "<br>";

    if (mysqli_query($con, $create_products_table)) echo "✅ Table 'products' created/checked successfully.<br>";
    else echo "❌ Error creating 'products' table: " . mysqli_error($con) . "<br>";

    if (mysqli_query($con, $create_orders_table)) echo "✅ Table 'orders' created/checked successfully.<br>";
    else echo "❌ Error creating 'orders' table: " . mysqli_error($con) . "<br>";

    if (mysqli_query($con, $create_order_items_table)) echo "✅ Table 'order_items' created/checked successfully.<br>";
    else echo "❌ Error creating 'order_items' table: " . mysqli_error($con) . "<br>";

    if (mysqli_query($con, $create_brands_table)) echo "✅ Table 'brands' created/checked successfully.<br>";
    else echo "❌ Error creating 'brands' table: " . mysqli_error($con) . "<br>";

    if (mysqli_query($con, $create_product_stock_table)) echo "✅ Table 'product_stock' created/checked successfully.<br>";
    else echo "❌ Error creating 'product_stock' table: " . mysqli_error($con) . "<br>";

    if (mysqli_query($con, $create_offers_table)) echo "✅ Table 'offers' created/checked successfully.<br>";
    else echo "❌ Error creating 'offers' table: " . mysqli_error($con) . "<br>";
}
?>
