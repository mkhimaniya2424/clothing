<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? NULL; // NULL for guest
$name = $con->real_escape_string($_POST['name']);
$email = $con->real_escape_string($_POST['email']);
$address = $con->real_escape_string($_POST['address']);
$payment_method = $con->real_escape_string($_POST['payment']);
$total_amount = floatval($_POST['total_amount']);

// 1. Create Order
$sql = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, order_status) 
        VALUES (" . ($user_id ? $user_id : "NULL") . ", $total_amount, '$payment_method', '$address', 'pending')";

if ($con->query($sql)) {
    $order_id = $con->insert_id;

    // 2. Add Order Items
    $cart_res = $con->query("SELECT c.quantity, p.id, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
    
    while ($row = $cart_res->fetch_assoc()) {
        $qty = $row['quantity'];
        $price = $row['price'];
        $pid = $row['id'];
        
        $con->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $price)");
        
        // Update Stock
        $con->query("UPDATE product_stock SET stock = stock - $qty WHERE product_id = $pid");
    }

    // 3. Clear Cart
    $con->query("DELETE FROM cart WHERE user_id = $user_id");
    unset($_SESSION['cart']); // Just in case

    // 4. Redirect to Success
    header("Location: order_success.php?id=$order_id");
    exit;
} else {
    echo "Error: " . $con->error;
}
?>
