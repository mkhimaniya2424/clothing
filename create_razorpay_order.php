<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';
require_once 'razorpay_config.php';

header('Content-Type: application/json');

// Check Login
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to continue']);
    exit;
}

$user_id = getUserId();

// ---------------------------------------
// 1. CALCULATE TOTAL AMOUNT (Server-side)
// ---------------------------------------
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
}
$stmt->close();

if ($calculated_total == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

// ---------------------------------------
// 2. APPLY DISCOUNTS (Same logic as checkout)
// ---------------------------------------
$currentDate = date('Y-m-d');
$discount_amount = 0;
$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$applied_offer = null;

// Check Coupon
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

// Check Automatic Offers if no coupon
if (!$applied_offer) {
    $offerSql = "SELECT * FROM offers WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate' ORDER BY discount_percentage DESC LIMIT 1";
    $offerRes = $con->query($offerSql);

    if ($offerRes && $offerRes->num_rows > 0) {
        $applied_offer = $offerRes->fetch_assoc();
        $discount_percentage = $applied_offer['discount_percentage'];
        $discount_amount = ($calculated_total * $discount_percentage) / 100;
    }
}

if ($discount_amount > $calculated_total) {
    $discount_amount = $calculated_total;
}

$final_amount = $calculated_total - $discount_amount;

// ---------------------------------------
// 3. CREATE RAZORPAY ORDER
// ---------------------------------------
try {
    $orderData = [
        'receipt'         => 'rcpt_' . time(),
        'amount'          => $final_amount * 100, // Amount in paise
        'currency'        => 'INR',
        'payment_capture' => 1 // Auto capture
    ];

    $razorpayOrder = $api->order->create($orderData);

    echo json_encode([
        'status' => 'success',
        'order_id' => $razorpayOrder['id'],
        'amount' => $final_amount * 100,
        'key' => $keyId,
        'user_name' => $_SESSION['user_name'] ?? 'User', // Assuming session has name
        'user_email' => $_SESSION['user_email'] ?? '',
        'user_contact' => $_SESSION['user_phone'] ?? ''
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
