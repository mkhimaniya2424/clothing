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

// Table creation code
if (isset($_GET['setup_db'])) {
    
    // Users Table
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
    if (mysqli_query($con, $create_users_table)) echo "✅ Table 'users' created/checked successfully.<br>";
    else echo "❌ Error creating 'users' table: " . mysqli_error($con) . "<br>";

    // User Address Table
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
    if (mysqli_query($con, $create_address_table)) echo "✅ Table 'user_address' created/checked successfully.<br>";
    else echo "❌ Error creating 'user_address' table: " . mysqli_error($con) . "<br>";

    // Admin Table
    $create_admin_table = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        email VARCHAR(100),
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($con, $create_admin_table)) {
        echo "✅ Table 'admin' created/checked successfully.<br>";
        // Insert default admin if not exists
        $admin_pass = "*" . strtoupper(sha1(sha1("admin123", true)));
        $check_admin = mysqli_query($con, "SELECT * FROM admin WHERE username='admin'");
        if (mysqli_num_rows($check_admin) == 0) {
            mysqli_query($con, "INSERT INTO admin (username, password, full_name) VALUES ('admin', '$admin_pass', 'Super Admin')");
            echo "✅ Default admin user created (User: admin, Pass: admin123)<br>";
        }
    } else {
        echo "❌ Error creating 'admin' table: " . mysqli_error($con) . "<br>";
    }

    // Categories Table
    $create_categories_table = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($con, $create_categories_table)) echo "✅ Table 'categories' created/checked successfully.<br>";
    else echo "❌ Error creating 'categories' table: " . mysqli_error($con) . "<br>";

    // Brands Table
    $create_brands_table = "CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image VARCHAR(255),
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($con, $create_brands_table)) echo "✅ Table 'brands' created/checked successfully.<br>";
    else echo "❌ Error creating 'brands' table: " . mysqli_error($con) . "<br>";

    // Products Table (Merged with Stock)
    $create_products_table = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        compare_price DECIMAL(10,2),
        category_id INT,
        brand_id INT,
        images TEXT,
        stock INT DEFAULT 0,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
    )";
    if (mysqli_query($con, $create_products_table)) echo "✅ Table 'products' created/checked successfully.<br>";
    else echo "❌ Error creating 'products' table: " . mysqli_error($con) . "<br>";

    // Orders Table
    $create_orders_table = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10,2) NOT NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        final_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50),
        payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
        order_status ENUM('pending','confirmed','processing','packed','shipped','delivered','cancelled') DEFAULT 'pending',
        shipping_address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    if (mysqli_query($con, $create_orders_table)) echo "✅ Table 'orders' created/checked successfully.<br>";
    else echo "❌ Error creating 'orders' table: " . mysqli_error($con) . "<br>";

    // Order Items Table
    $create_order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    )";
    if (mysqli_query($con, $create_order_items_table)) echo "✅ Table 'order_items' created/checked successfully.<br>";
    else echo "❌ Error creating 'order_items' table: " . mysqli_error($con) . "<br>";

    // Offers Table
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
    if (mysqli_query($con, $create_offers_table)) echo "✅ Table 'offers' created/checked successfully.<br>";
    else echo "❌ Error creating 'offers' table: " . mysqli_error($con) . "<br>";

    // Visitors Table
    $create_visitors_table = "CREATE TABLE IF NOT EXISTS visitors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45),
        visit_date DATE,
        visit_time TIME,
        page_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($con, $create_visitors_table)) echo "✅ Table 'visitors' created/checked successfully.<br>";
    else echo "❌ Error creating 'visitors' table: " . mysqli_error($con) . "<br>";

    // Coupons Table
    $create_coupons_table = "CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        min_purchase_amount DECIMAL(10,2) DEFAULT 0,
        max_discount DECIMAL(10,2) NULL,
        usage_limit INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        valid_from DATE NOT NULL,
        valid_until DATE NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($con, $create_coupons_table)) echo "✅ Table 'coupons' created/checked successfully.<br>";
    else echo "❌ Error creating 'coupons' table: " . mysqli_error($con) . "<br>";

    // User Verification Table
    $create_verification_table = "CREATE TABLE IF NOT EXISTS user_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email_otp VARCHAR(6),
        email_otp_expiry DATETIME,
        email_verified TINYINT(1) DEFAULT 0,
        email_verified_at TIMESTAMP NULL,
        reset_token VARCHAR(255) NULL,
        reset_token_expiry DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if (mysqli_query($con, $create_verification_table)) echo "✅ Table 'user_verification' created/checked successfully.<br>";
    else echo "❌ Error creating 'user_verification' table: " . mysqli_error($con) . "<br>";

    // Wishlist Table
    $create_wishlist_table = "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE(user_id, product_id)
    )";
    if (mysqli_query($con, $create_wishlist_table)) echo "✅ Table 'wishlist' created/checked successfully.<br>";
    else echo "❌ Error creating 'wishlist' table: " . mysqli_error($con) . "<br>";

    // Contact Messages Table
    $create_contact_messages_table = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(255),
        message TEXT NOT NULL,
        status ENUM('pending','read','replied') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($con, $create_contact_messages_table)) echo "✅ Table 'contact_messages' created/checked successfully.<br>";
    else echo "❌ Error creating 'contact_messages' table: " . mysqli_error($con) . "<br>";
}
?>
