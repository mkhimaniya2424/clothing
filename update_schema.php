<?php
require_once 'db_connect.php';

$sql1 = "ALTER TABLE orders ADD COLUMN shipping_address TEXT AFTER user_id";
$sql2 = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) AFTER final_amount";

if ($con->query($sql1)) {
    echo "Added shipping_address column.\n";
} else {
    echo "Error adding shipping_address: " . $con->error . "\n";
}

if ($con->query($sql2)) {
    echo "Added payment_method column.\n";
} else {
    echo "Error adding payment_method: " . $con->error . "\n";
}
?>
