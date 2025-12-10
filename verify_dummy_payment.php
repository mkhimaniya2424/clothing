<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

if (!isset($_POST['order_id']) || !isset($_POST['payment_status'])) {
    header("Location: checkout.php");
    exit;
}

$orderId = $_POST['order_id'];
$status = $_POST['payment_status'];

if ($status === 'success') {
    // PAYMENT SUCCESS
    $user_id = getUserId();
    
    // Recalculate totals (Same logic as before)
    $cartItems = [];
    $calculated_total = 0;
    $sql = "SELECT c.quantity, p.id, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $qty = $row['quantity'];
        $price = $row['price'];
        $subtotal = $price * $qty;
        $calculated_total += $subtotal;
        $cartItems[] = ['product_id' => $row['id'], 'quantity' => $qty, 'price' => $price, 'total' => $subtotal];
    }
    $stmt->close();

    // Apply Discount (Simplified)
    $discount_amount = 0; 
    // In a real app, pass discount info securely. Here we assume 0 or recalculate if needed.
    // For the dummy gateway, we'll just use the cart total for simplicity or you can re-run the discount logic.
    
    $final_amount = $calculated_total - $discount_amount;
    
    // Get Address
    // For this dummy flow, we'll just use a placeholder or fetch the last used address
    $address_text = "Dummy Address"; 
    $address_id = null;

    // DB Transaction
    $con->begin_transaction();
    try {
        $order_stmt = $con->prepare("INSERT INTO orders (user_id, shipping_address, address_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, 'online_card', 'paid', 'confirmed')");
        $order_stmt->bind_param("isiddd", $user_id, $address_text, $address_id, $calculated_total, $discount_amount, $final_amount);
        $order_stmt->execute();
        $new_order_id = $con->insert_id;
        $order_stmt->close();
        
        $item_stmt = $con->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $item_stmt->bind_param("iiidd", $new_order_id, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
            $item_stmt->execute();
        }
        $item_stmt->close();
        
        $payment_stmt = $con->prepare("INSERT INTO payment_details (order_id, payment_method, transaction_id, amount, payment_status) VALUES (?, 'online_card', ?, ?, 'success')");
        $payment_stmt->bind_param("isd", $new_order_id, $orderId, $final_amount);
        $payment_stmt->execute();
        $payment_stmt->close();
        
        $con->query("DELETE FROM cart WHERE user_id = $user_id");
        $con->commit();
        
        header("Location: order_success.php?order_id=" . $new_order_id);
        exit;
        
    } catch (Exception $e) {
        $con->rollback();
        die("Error processing order: " . $e->getMessage());
    }

} else {
    $_SESSION['error'] = "Payment Failed.";
    header("Location: checkout.php");
    exit;
}
?>
