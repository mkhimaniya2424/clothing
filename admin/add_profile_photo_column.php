<?php
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Add profile_photo column to users table if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL AFTER email";

if ($con->query($sql)) {
    echo "Column 'profile_photo' added successfully to users table.";
} else {
    // Check if column already exists
    if (strpos($con->error, 'Duplicate column name') !== false) {
        echo "Column 'profile_photo' already exists in users table.";
    } else {
        echo "Error: " . $con->error;
    }
}

header("Location: admin_dashboard.php?msg=Profile photo column added");
exit;
?>
