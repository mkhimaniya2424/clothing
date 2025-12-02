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
/*
$q = "CREATE TABLE IF NOT EXISTS registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(50),
    email VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    gender CHAR(6),
    mobile BIGINT(10),
    profile_picture VARCHAR(255),
    address TEXT,
    email_otp VARCHAR(10),
    mobile_otp VARCHAR(10),
    otp_expiry DATETIME,
    email_verified TINYINT(1) DEFAULT 0,
    mobile_verified TINYINT(1) DEFAULT 0,
    status CHAR(8) DEFAULT 'Inactive',
    role CHAR(10) DEFAULT 'User',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $q)) {
    echo "✅ Table 'registration' created successfully";
} else {
    echo "⚠️ Error creating table: " . mysqli_error($con);
}
*/
?>
