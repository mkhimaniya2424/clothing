<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

// Require login
requireLogin('orders.php');

$user_id = getUserId();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id > 0) {
    // Verify order belongs to user and is pending
    $stmt = $con->prepare("SELECT order_status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['order_status'] === 'pending') {
            // Update order status to cancelled
            $update_stmt = $con->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?");
            $update_stmt->bind_param("i", $order_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            $_SESSION['success'] = "Order cancelled successfully.";
        } else {
            $_SESSION['error'] = "This order cannot be cancelled.";
        }
    } else {
        $_SESSION['error'] = "Order not found.";
    }
    $stmt->close();
}

header("Location: orders.php");
exit();
?>
