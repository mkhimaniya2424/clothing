<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

$title_page = "Checkout";

// Require login
requireLogin('checkout.php');

$user_id = getUserId();

// Default values
$name = "";
$email = "";
$address_text = "";
$address_id = null;

// ---------------------------------------
// FETCH USER DETAILS
// ---------------------------------------
$u_stmt = $con->prepare("SELECT username, email FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$u_result = $u_stmt->get_result();

if ($u_row = $u_result->fetch_assoc()) {
    $name = $u_row['username'];
    $email = $u_row['email'];
}
$u_stmt->close();

// ---------------------------------------
// FETCH USER ADDRESS
// ---------------------------------------
$a_stmt = $con->prepare("SELECT * FROM user_address WHERE user_id = ? LIMIT 1");
$a_stmt->bind_param("i", $user_id);
$a_stmt->execute();
$a_result = $a_stmt->get_result();

if ($a_row = $a_result->fetch_assoc()) {
    $address_id = $a_row['address_id'];
    $address_text =
        $a_row['address_line1'] . ", " .
        ($a_row['address_line2'] ? $a_row['address_line2'] . ", " : "") .
        $a_row['city'] . ", " .
        $a_row['state'] . " - " .
        $a_row['postal_code'] . ", " .
        $a_row['country'];
}
$a_stmt->close();

// ---------------------------------------
// FETCH CART ITEMS FROM DATABASE
// ---------------------------------------
$cartItems = [];
$total = 0;

$sql = "SELECT c.quantity, p.* FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $qty = $row['quantity'];
        $subtotal = $row['price'] * $qty;
        $total += $subtotal;
        
        $img = "https://via.placeholder.com/100";
        if (!empty($row['images'])) {
            $decoded = json_decode($row['images'], true);
            if ($decoded && isset($decoded[0])) $img = $decoded[0];
        }
        
        $cartItems[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'price' => $row['price'],
            'qty' => $qty,
            'subtotal' => $subtotal,
            'image' => $img
        ];
    }
}
$stmt->close();

// If cart is empty, redirect to cart page
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// ---------------------------------------
// FETCH ACTIVE OFFERS & COUPONS
// ---------------------------------------
$currentDate = date('Y-m-d');
$discount_amount = 0;
$applied_offer = null;
$coupon_code = isset($_GET['coupon']) ? trim($_GET['coupon']) : '';
$coupon_error = '';
$coupon_success = '';

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
            $discount_amount = ($total * $applied_offer['discount_percentage']) / 100;
        } elseif ($applied_offer['discount_amount'] > 0) {
            $discount_amount = $applied_offer['discount_amount'];
        }
        
        $coupon_success = "Coupon '{$applied_offer['code']}' applied successfully!";
    } else {
        $coupon_error = "Invalid or expired coupon code.";
        $coupon_code = ''; // Reset invalid code
    }
    $stmt->close();
}

// 2. If no coupon applied, check for automatic offers (highest discount)
if (!$applied_offer) {
    $offerSql = "SELECT * FROM offers WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate' ORDER BY discount_percentage DESC LIMIT 1";
    $offerRes = $con->query($offerSql);

    if ($offerRes && $offerRes->num_rows > 0) {
        $applied_offer = $offerRes->fetch_assoc();
        $discount_percentage = $applied_offer['discount_percentage'];
        $discount_amount = ($total * $discount_percentage) / 100;
    }
}

// Ensure discount doesn't exceed total
if ($discount_amount > $total) {
    $discount_amount = $total;
}

$final_amount = $total - $discount_amount;
?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">Checkout</h2>

    <div class="row">
        <div class="col-md-8">
            <h5 class="mb-3">Billing Details</h5>

            <form action="order_place.php" method="POST" id="checkoutForm">
                <div class="mb-3">
                    <label class="fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Address</label>
                    <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($address_text) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="">Select Payment Method</option>
                        <option value="cod">Cash on Delivery</option>
                        <option value="online">Pay Online (Cashfree)</option>
                    </select>
                </div>

                <input type="hidden" name="total_amount" value="<?= $total ?>">
                <input type="hidden" name="discount_amount" value="<?= $discount_amount ?>">
                <input type="hidden" name="final_amount" value="<?= $final_amount ?>">
                <input type="hidden" name="address_id" value="<?= $address_id ?>">
                <input type="hidden" name="coupon_code" value="<?= htmlspecialchars($coupon_code) ?>">

                <button type="submit" class="btn btn-dark w-100 btn-lg mt-3">Place Order</button>
            </form>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Summary</h5>

                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="my-0"><?= htmlspecialchars($item['title']) ?></h6>
                                    <small class="text-muted">Qty: <?= $item['qty'] ?></small>
                                </div>
                                <span class="text-muted">₹<?= number_format($item['subtotal'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>

                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Subtotal</span>
                            <span>₹<?= number_format($total, 2) ?></span>
                        </li>

                        <li class="list-group-item px-0">
                            <!-- AVAILABLE COUPONS MESSAGE -->
                            <?php
                            $availPromoSql = "SELECT * FROM promotions WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate' AND code != ''";
                            $availPromoRes = $con->query($availPromoSql);
                            if ($availPromoRes && $availPromoRes->num_rows > 0):
                            ?>
                                <div class="alert alert-info p-2 mb-2 small">
                                    <strong>Available Coupons:</strong><br>
                                    <?php while($p = $availPromoRes->fetch_assoc()): ?>
                                        <span class="badge bg-white text-dark border me-1 mb-1">
                                            <?= htmlspecialchars($p['code']) ?>
                                        </span>
                                        <span class="text-muted">
                                            - Get <?= $p['discount_percentage'] > 0 ? intval($p['discount_percentage']).'% Off' : '₹'.intval($p['discount_amount']).' Off' ?>
                                        </span><br>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>

                            <form action="" method="GET" class="d-flex gap-2">
                                <input type="text" name="coupon" class="form-control form-control-sm" placeholder="Coupon Code" value="<?= htmlspecialchars($coupon_code) ?>">
                                <button type="submit" class="btn btn-outline-dark btn-sm">Apply</button>
                            </form>
                            <?php if ($coupon_error): ?>
                                <small class="text-danger"><?= $coupon_error ?></small>
                            <?php endif; ?>
                            <?php if ($coupon_success): ?>
                                <small class="text-success"><?= $coupon_success ?></small>
                            <?php endif; ?>
                        </li>


                        <?php if ($discount_amount > 0): ?>
                            <li class="list-group-item d-flex justify-content-between px-0 text-success">
                                <span>Discount (<?= htmlspecialchars($applied_offer['title'] ?? $applied_offer['code']) ?>)</span>
                                <span>-₹<?= number_format($discount_amount, 2) ?></span>
                            </li>
                        <?php endif; ?>

                        <li class="list-group-item d-flex justify-content-between px-0 fw-bold fs-5">
                            <span>Total (INR)</span>
                            <span>₹<?= number_format($final_amount, 2) ?></span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cashfree JS SDK -->
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>

<script>
const cashfree = Cashfree({
    mode: "sandbox" // or "production"
});

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const method = document.getElementById('payment_method').value;
    
    if (method === 'cod') {
        return; // Allow normal submission for COD
    }
    
    if (method === 'online') {
        e.preventDefault(); // Stop form for Online Payment
        
        const formData = new FormData(this);
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Processing...';
        submitBtn.disabled = true;
        
        // Create Cashfree Order
        fetch('cashfree_create_order.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                // Initiate Cashfree Checkout
                cashfree.checkout({
                    paymentSessionId: data.payment_session_id,
                    returnUrl: data.return_url || null // Optional, handled by backend usually but good for redirect
                }).then(function(result){
                    if(result.error){
                        alert(result.error.message);
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                    if(result.redirect){
                        console.log("Redirection");
                    }
                });
            } else {
                alert(data.message || 'Failed to create payment order. Please try again.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Something went wrong. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
