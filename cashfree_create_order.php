<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';
require_once 'cashfree_config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to continue']);
    exit;
}

$user_id = getUserId();

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : null;
$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';

// Fetch user phone if not provided
if (empty($phone)) {
    $u_stmt = $con->prepare("SELECT phone FROM users WHERE id = ?");
    $u_stmt->bind_param("i", $user_id);
    $u_stmt->execute();
    $u_result = $u_stmt->get_result();
    if ($u_row = $u_result->fetch_assoc()) {
        $phone = $u_row['phone'] ?? '9999999999';
    }
    $u_stmt->close();
}

// Validate
if (empty($name) || empty($email) || empty($address)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Calculate Total Amount (Server-side)
$cartItems = [];
$calculated_total = 0;

$sql = "SELECT c.quantity, p.id, p.price, p.title FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $qty = $row['quantity'];
    $price = $row['price'];
    $subtotal = $price * $qty;
    $calculated_total += $subtotal;
    
    $cartItems[] = [
        'product_id' => $row['id'],
        'title' => $row['title'],
        'quantity' => $qty,
        'price' => $price,
        'total' => $subtotal
    ];
}
$stmt->close();

if ($calculated_total == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

// Apply Discounts
$currentDate = date('Y-m-d');
$discount_amount = 0;
$applied_offer = null;

if (!empty($coupon_code)) {
    $stmt = $con->prepare("SELECT * FROM promotions WHERE code = ? AND status='active' AND start_date <= ? AND end_date >= ?");
    $stmt->bind_param("sss", $coupon_code, $currentDate, $currentDate);
    $stmt->execute();
    $promoRes = $stmt->get_result();
    if ($promoRes && $promoRes->num_rows > 0) {
        $applied_offer = $promoRes->fetch_assoc();
        if ($applied_offer['discount_percentage'] > 0) {
            $discount_amount = ($calculated_total * $applied_offer['discount_percentage']) / 100;
        } elseif ($applied_offer['discount_amount'] > 0) {
            $discount_amount = $applied_offer['discount_amount'];
        }
    }
    $stmt->close();
}

if (!$applied_offer) {
    $offerSql = "SELECT * FROM offers WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate' ORDER BY discount_percentage DESC LIMIT 1";
    $offerRes = $con->query($offerSql);
    if ($offerRes && $offerRes->num_rows > 0) {
        $applied_offer = $offerRes->fetch_assoc();
        $discount_percentage = $applied_offer['discount_percentage'];
        $discount_amount = ($calculated_total * $discount_percentage) / 100;
    }
}

if ($discount_amount > $calculated_total) $discount_amount = $calculated_total;
$final_amount = $calculated_total - $discount_amount;

// Create order in database first
$con->begin_transaction();

try {
    // If no address_id, create a temporary address entry
    if (!$address_id) {
        $addr_stmt = $con->prepare("INSERT INTO user_address (user_id, address_line1, city, state, postal_code, country) VALUES (?, ?, 'N/A', 'N/A', '000000', 'India')");
        $addr_stmt->bind_param("is", $user_id, $address);
        $addr_stmt->execute();
        $address_id = $con->insert_id;
        $addr_stmt->close();
    }
    
    // Create order with pending status
    $order_stmt = $con->prepare("INSERT INTO orders (user_id, shipping_address, address_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, 'online', 'pending', 'pending')");
    $order_stmt->bind_param("isiddd", $user_id, $address, $address_id, $calculated_total, $discount_amount, $final_amount);
    $order_stmt->execute();
    $order_id = $con->insert_id;
    $order_stmt->close();
    
    // Create order items
    $item_stmt = $con->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $item_stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
        $item_stmt->execute();
    }
    $item_stmt->close();
    
    // Create payment details entry
    $payment_stmt = $con->prepare("INSERT INTO payment_details (order_id, payment_method, amount, payment_status) VALUES (?, 'online', ?, 'pending')");
    $payment_stmt->bind_param("id", $order_id, $final_amount);
    $payment_stmt->execute();
    $payment_id = $con->insert_id;
    $payment_stmt->close();
    
    $con->commit();
    
    // Now create Cashfree order
    $cashfree_order_id = 'order_' . $order_id . '_' . time();
    
    // Prepare order data according to Cashfree API v2023-08-01
    $orderData = [
        'order_id' => $cashfree_order_id,
        'order_amount' => (float)round($final_amount, 2),
        'order_currency' => 'INR',
        'customer_details' => [
            'customer_id' => 'cust_' . $user_id,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone
        ],
        'order_meta' => [
            'return_url' => CASHFREE_RETURN_URL . '?order_id=' . $order_id
        ]
    ];
    
    // Make API call to Cashfree using correct endpoint
    $api_url = getCashfreeEndpoint('/orders');
    
    // Ensure no trailing slash
    $api_url = rtrim($api_url, '/');
    
    // Log request details
    $log_data = "Time: " . date('Y-m-d H:i:s') . "\n";
    $log_data .= "URL: " . $api_url . "\n";
    $log_data .= "Order ID: " . $cashfree_order_id . "\n";
    $log_data .= "Payload: " . json_encode($orderData) . "\n";
    file_put_contents('cashfree_debug.log', $log_data, FILE_APPEND);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, getCashfreeHeaders());
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Log response
    file_put_contents('cashfree_debug.log', "Response Code: $httpCode\nResponse: $response\n----------------\n", FILE_APPEND);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log the response for debugging
    error_log("Cashfree API Response (HTTP $httpCode): " . $response);
    
    if ($httpCode == 200 || $httpCode == 201) {
        $responseData = json_decode($response, true);
        
        if (isset($responseData['payment_session_id'])) {
            // Update order with Cashfree order ID
            $update_stmt = $con->prepare("UPDATE payment_details SET transaction_id = ? WHERE id = ?");
            $update_stmt->bind_param("si", $cashfree_order_id, $payment_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Store session data
            $_SESSION['pending_order_id'] = $order_id;
            $_SESSION['cashfree_order_id'] = $cashfree_order_id;
            $_SESSION['payment_session_id'] = $responseData['payment_session_id'];
            
            // Return payment session data
            echo json_encode([
                'status' => 'success',
                'order_id' => $order_id,
                'cashfree_order_id' => $cashfree_order_id,
                'payment_session_id' => $responseData['payment_session_id'],
                'return_url' => CASHFREE_RETURN_URL . '?order_id=' . $order_id
            ]);
        } else {
            throw new Exception('Invalid response from payment gateway: ' . json_encode($responseData));
        }
    } else {
        $errorMsg = $response;
        if ($curlError) {
            $errorMsg = 'cURL Error: ' . $curlError;
        }
        throw new Exception('Payment gateway error (HTTP ' . $httpCode . '): ' . $errorMsg);
    }
    
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()]);
}
?>
