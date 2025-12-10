<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to continue']);
    exit;
}

$user_id = getUserId();

// 1. Calculate Total Amount (Server-side)
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

// 2. Apply Discounts
$currentDate = date('Y-m-d');
$discount_amount = 0;
$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
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

// 3. Create Dummy Order Response
// We simulate a successful "order creation" for our dummy gateway
$dummy_order_id = 'DUMMY_' . time() . '_' . rand(1000, 9999);

echo json_encode([
    'status' => 'success',
    'order_id' => $dummy_order_id,
    'amount' => $final_amount,
    'currency' => 'INR'
]);
?>
