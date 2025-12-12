<?php
/**
 * Database Migration Script for Cashfree Integration
 * This script updates the payment_details table to support online payments
 */

require_once 'db_connect.php';

echo "Starting database migration for Cashfree integration...\n\n";

// 1. Update payment_method enum to include 'online'
$sql1 = "ALTER TABLE `payment_details` 
         MODIFY `payment_method` ENUM('cod','card','upi','netbanking','wallet','online') NOT NULL";

if ($con->query($sql1)) {
    echo "✓ Updated payment_method enum to include 'online'\n";
} else {
    echo "✗ Error updating payment_method: " . $con->error . "\n";
}

// 2. Ensure transaction_id column can store Cashfree order IDs (already exists, just verify)
$sql2 = "ALTER TABLE `payment_details` 
         MODIFY `transaction_id` VARCHAR(255) DEFAULT NULL";

if ($con->query($sql2)) {
    echo "✓ Verified transaction_id column\n";
} else {
    echo "✗ Error verifying transaction_id: " . $con->error . "\n";
}

// 3. Add index on transaction_id for faster lookups
$sql3 = "ALTER TABLE `payment_details` 
         ADD INDEX IF NOT EXISTS `idx_transaction_id` (`transaction_id`)";

// Check if index exists first
$check_index = "SHOW INDEX FROM `payment_details` WHERE Key_name = 'idx_transaction_id'";
$result = $con->query($check_index);

if ($result->num_rows == 0) {
    $sql3_alt = "ALTER TABLE `payment_details` ADD INDEX `idx_transaction_id` (`transaction_id`)";
    if ($con->query($sql3_alt)) {
        echo "✓ Added index on transaction_id\n";
    } else {
        echo "✗ Error adding index: " . $con->error . "\n";
    }
} else {
    echo "✓ Index on transaction_id already exists\n";
}

echo "\n✅ Database migration completed successfully!\n";
echo "\nYou can now use Cashfree payment gateway in your application.\n";

$con->close();
?>
