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
}
?>
