<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';
require_once 'razorpay_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

$success = true;
$error = "Payment Failed";

// 1. VERIFY SIGNATURE
try {
    // Attributes required for verification
    $attributes = [
        'razorpay_order_id' => $_POST['razorpay_order_id'],
        'razorpay_payment_id' => $_POST['razorpay_payment_id'],
        'razorpay_signature' => $_POST['razorpay_signature']
    ];

    $api->utility->verifyPaymentSignature($attributes);
} catch (Exception $e) {
    $success = false;
    $error = "Razorpay Error: " . $e->getMessage();
}

if ($success === true) {
    // ---------------------------------------
    // PAYMENT VERIFIED - PLACE ORDER
    // ---------------------------------------
    
    $user_id = getUserId();
    
    // Get form data passed from checkout
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : null;
    $coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
    
    // Recalculate totals (Security best practice)
    // ... (Copying logic from create_razorpay_order.php or order_place.php)
    
    $cartItems = [];
    $calculated_total = 0;

    $sql = "SELECT c.quantity, p.id, p.price FROM cart c 
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
            'quantity' => $qty,
            'price' => $price,
            'total' => $subtotal
        ];
    }
    $stmt->close();
    
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


    // Handle Address
    if (!$address_id) {
        $addr_stmt = $con->prepare("INSERT INTO user_address (user_id, address_line1, city, state, postal_code, country) VALUES (?, ?, 'N/A', 'N/A', '000000', 'India')");
        $addr_stmt->bind_param("is", $user_id, $address);
        $addr_stmt->execute();
        $address_id = $con->insert_id;
        $addr_stmt->close();
    }

    // DB Transaction
    $con->begin_transaction();

    try {
        // Insert Order
        $order_stmt = $con->prepare("INSERT INTO orders (user_id, shipping_address, address_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, 'razorpay', 'paid', 'confirmed')");
        $order_stmt->bind_param("isiddd", $user_id, $address, $address_id, $calculated_total, $discount_amount, $final_amount);
        $order_stmt->execute();
        $order_id = $con->insert_id;
        $order_stmt->close();
        
        // Insert Items
        $item_stmt = $con->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $item_stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
            $item_stmt->execute();
        }
        $item_stmt->close();
        
        // Insert Payment Details
        $payment_stmt = $con->prepare("INSERT INTO payment_details (order_id, payment_method, transaction_id, amount, payment_status) VALUES (?, 'razorpay', ?, ?, 'success')");
        $payment_stmt->bind_param("isd", $order_id, $_POST['razorpay_payment_id'], $final_amount);
        $payment_stmt->execute();
        $payment_stmt->close();
        
        // Clear Cart
        $clear_stmt = $con->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        $clear_stmt->close();

        $con->commit();
        
        $_SESSION['order_id'] = $order_id;
        header("Location: order_success.php?order_id=" . $order_id);
        exit();

    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: checkout.php");
        exit();
    }

} else {
    $_SESSION['error'] = $error;
    header("Location: checkout.php");
    exit();
}
?>
