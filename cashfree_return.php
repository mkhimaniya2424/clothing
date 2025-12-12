<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';
require_once 'cashfree_config.php';

// Require login
requireLogin('checkout.php');

$user_id = getUserId();
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    $_SESSION['error'] = 'Invalid order';
    header('Location: checkout.php');
    exit;
}

// Get Cashfree order ID from database
$stmt = $con->prepare("SELECT pd.transaction_id, o.final_amount 
                       FROM payment_details pd 
                       JOIN orders o ON pd.order_id = o.id 
                       WHERE pd.order_id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Order not found';
    header('Location: orders.php');
    exit;
}

$row = $result->fetch_assoc();
$cashfree_order_id = $row['transaction_id'];
$stmt->close();

// Verify payment status with Cashfree
$api_url = getCashfreeEndpoint('/orders/' . $cashfree_order_id);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, getCashfreeHeaders());
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    
    // Check order status
    $order_status = $responseData['order_status'] ?? 'UNKNOWN';
    
    if ($order_status == 'PAID') {
        // Payment successful - Update database
        $con->begin_transaction();
        
        try {
            // Update payment details
            $update_payment = $con->prepare("UPDATE payment_details SET payment_status = 'success' WHERE order_id = ?");
            $update_payment->bind_param("i", $order_id);
            $update_payment->execute();
            $update_payment->close();
            
            // Update order status
            $update_order = $con->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'confirmed' WHERE id = ?");
            $update_order->bind_param("i", $order_id);
            $update_order->execute();
            $update_order->close();
            
            // Clear cart
            $clear_cart = $con->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart->bind_param("i", $user_id);
            $clear_cart->execute();
            $clear_cart->close();
            
            $con->commit();
            
            // Redirect to success page
            $_SESSION['order_id'] = $order_id;
            header('Location: order_success.php?order_id=' . $order_id);
            exit;
            
        } catch (Exception $e) {
            $con->rollback();
            $_SESSION['error'] = 'Failed to update order status';
            header('Location: orders.php');
            exit;
        }
        
    } elseif ($order_status == 'ACTIVE') {
        // Payment is still pending
        $_SESSION['info'] = 'Payment is being processed. Please wait...';
        header('Location: orders.php');
        exit;
        
    } else {
        // Payment failed or cancelled
        $con->begin_transaction();
        
        try {
            // Update payment status to failed
            $update_payment = $con->prepare("UPDATE payment_details SET payment_status = 'failed' WHERE order_id = ?");
            $update_payment->bind_param("i", $order_id);
            $update_payment->execute();
            $update_payment->close();
            
            // Update order status to cancelled
            $update_order = $con->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE id = ?");
            $update_order->bind_param("i", $order_id);
            $update_order->execute();
            $update_order->close();
            
            $con->commit();
            
        } catch (Exception $e) {
            $con->rollback();
        }
        
        $_SESSION['error'] = 'Payment failed or was cancelled. Please try again.';
        header('Location: checkout.php');
        exit;
    }
    
} else {
    // Failed to verify payment
    $_SESSION['error'] = 'Unable to verify payment status. Please contact support.';
    header('Location: orders.php');
    exit;
}
?>
