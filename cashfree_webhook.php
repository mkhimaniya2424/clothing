<?php
/**
 * Cashfree Webhook Handler
 * This file receives payment notifications from Cashfree
 */

require_once 'db_connect.php';
require_once 'cashfree_config.php';

// Get the raw POST data
$postData = file_get_contents('php://input');
$webhookData = json_decode($postData, true);

// Log webhook for debugging (optional)
file_put_contents('cashfree_webhook_log.txt', date('Y-m-d H:i:s') . " - " . $postData . "\n", FILE_APPEND);

if (!$webhookData) {
    http_response_code(400);
    exit('Invalid webhook data');
}

// Verify webhook signature (recommended for production)
// You can implement signature verification here for added security

// Extract data
$event_type = $webhookData['type'] ?? '';
$order_data = $webhookData['data']['order'] ?? [];

if (empty($order_data)) {
    http_response_code(400);
    exit('No order data');
}

$cashfree_order_id = $order_data['order_id'] ?? '';
$order_status = $order_data['order_status'] ?? '';
$order_amount = $order_data['order_amount'] ?? 0;

if (empty($cashfree_order_id)) {
    http_response_code(400);
    exit('No order ID');
}

// Get our internal order ID from the Cashfree order ID
$stmt = $con->prepare("SELECT order_id FROM payment_details WHERE transaction_id = ?");
$stmt->bind_param("s", $cashfree_order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(404);
    exit('Order not found');
}

$row = $result->fetch_assoc();
$order_id = $row['order_id'];
$stmt->close();

// Process based on payment status
if ($order_status == 'PAID') {
    // Payment successful
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
        
        $con->commit();
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Payment confirmed']);
        
    } catch (Exception $e) {
        $con->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
} elseif ($order_status == 'FAILED' || $order_status == 'CANCELLED') {
    // Payment failed or cancelled
    $con->begin_transaction();
    
    try {
        // Update payment status
        $update_payment = $con->prepare("UPDATE payment_details SET payment_status = 'failed' WHERE order_id = ?");
        $update_payment->bind_param("i", $order_id);
        $update_payment->execute();
        $update_payment->close();
        
        // Update order status
        $update_order = $con->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE id = ?");
        $update_order->bind_param("i", $order_id);
        $update_order->execute();
        $update_order->close();
        
        $con->commit();
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Payment failed/cancelled']);
        
    } catch (Exception $e) {
        $con->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
} else {
    // Other status
    http_response_code(200);
    echo json_encode(['status' => 'info', 'message' => 'Status: ' . $order_status]);
}
?>
