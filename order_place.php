<?php
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

// Require login
requireLogin('checkout.php');

$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

// Get form data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$address = trim($_POST['address']);
$payment_method = $_POST['payment_method'];
$total_amount = floatval($_POST['total_amount']);
$address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : null;

// Validate
if (empty($name) || empty($email) || empty($address) || empty($payment_method)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: checkout.php");
    exit();
}


// Fetch cart items
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

// Check if cart is empty
if (empty($cartItems)) {
    $_SESSION['error'] = "Your cart is empty.";
    header("Location: cart.php");
    exit();
}


// ---------------------------------------
// RECALCULATE DISCOUNT (Server-side validation)
// ---------------------------------------
$currentDate = date('Y-m-d');
$discount_amount = 0;
$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$applied_offer = null;

// 1. Check for Coupon Code if provided
if (!empty($coupon_code)) {
    $stmt = $con->prepare("SELECT * FROM promotions WHERE code = ? AND status='active' AND start_date <= ? AND end_date >= ?");
    $stmt->bind_param("sss", $coupon_code, $currentDate, $currentDate);
    $stmt->execute();
    $promoRes = $stmt->get_result();
    
    if ($promoRes && $promoRes->num_rows > 0) {
        $applied_offer = $promoRes->fetch_assoc();
        
        // Calculate discount
        if ($applied_offer['discount_percentage'] > 0) {
            $discount_amount = ($calculated_total * $applied_offer['discount_percentage']) / 100;
        } elseif ($applied_offer['discount_amount'] > 0) {
            $discount_amount = $applied_offer['discount_amount'];
        }
    }
    $stmt->close();
}

// 2. If no coupon applied, check for automatic offers
if (!$applied_offer) {
    $offerSql = "SELECT * FROM offers WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate' ORDER BY discount_percentage DESC LIMIT 1";
    $offerRes = $con->query($offerSql);

    if ($offerRes && $offerRes->num_rows > 0) {
        $applied_offer = $offerRes->fetch_assoc();
        $discount_percentage = $applied_offer['discount_percentage'];
        $discount_amount = ($calculated_total * $discount_percentage) / 100;
    }
}

// Ensure discount doesn't exceed total
if ($discount_amount > $calculated_total) {
    $discount_amount = $calculated_total;
}

$final_amount = $calculated_total - $discount_amount;


// If no address_id, create a temporary address entry
if (!$address_id) {
    $addr_stmt = $con->prepare("INSERT INTO user_address (user_id, address_line1, city, state, postal_code, country) VALUES (?, ?, 'N/A', 'N/A', '000000', 'India')");
    $addr_stmt->bind_param("is", $user_id, $address);
    $addr_stmt->execute();
    $address_id = $con->insert_id;
    $addr_stmt->close();
}

// Start transaction
$con->begin_transaction();

try {
    // Create order
    $order_stmt = $con->prepare("INSERT INTO orders (user_id, shipping_address, address_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
    $order_stmt->bind_param("isiddds", $user_id, $address, $address_id, $calculated_total, $discount_amount, $final_amount, $payment_method);
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
    
    // Create payment details
    // Capture extra payment info if available
    $payment_info = "";
    if ($payment_method === 'upi' && isset($_POST['upi_id'])) {
        $payment_info = "UPI ID: " . $_POST['upi_id'];
    } elseif ($payment_method === 'card' && isset($_POST['card_number'])) {
        $payment_info = "Card: " . substr($_POST['card_number'], -4); // Only store last 4 digits
    }

    // Note: You might want to add a 'payment_info' column to payment_details table later
    // For now, we just insert the basic record
    $payment_stmt = $con->prepare("INSERT INTO payment_details (order_id, payment_method, amount, payment_status) VALUES (?, ?, ?, 'pending')");
    $payment_stmt->bind_param("isd", $order_id, $payment_method, $final_amount);
    $payment_stmt->execute();
    $payment_stmt->close();
    
    // Clear cart
    $clear_stmt = $con->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
    $clear_stmt->close();

    
    // Commit transaction
    $con->commit();
    
    // Redirect to success page
    $_SESSION['order_id'] = $order_id;
    header("Location: order_success.php?order_id=" . $order_id);
    exit();
    
} catch (Exception $e) {
    // Rollback on error
    $con->rollback();
    $_SESSION['error'] = "Failed to place order. Please try again.";
    header("Location: checkout.php");
    exit();
}
?>
