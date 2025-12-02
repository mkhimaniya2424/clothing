<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';

$title_page = "Checkout";

// ---------------------------------------
// CHECK IF USER IS LOGGED IN
// ---------------------------------------
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Default values
$name = "";
$email = "";
$address_text = "";

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
    $address_text =
        $a_row['address_line1'] . ", " .
        $a_row['city'] . ", " .
        $a_row['state'] . " - " .
        $a_row['postal_code'];
}
$a_stmt->close();


// ---------------------------------------
// Fetch cart items (required)
// Replace this with your actual cart logic
// ---------------------------------------

$cartItems = $_SESSION['cart'] ?? [];
$total = 0;

foreach ($cartItems as $item) {
    $total += $item['subtotal'];
}
?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">Checkout</h2>

    <div class="row">
        <div class="col-md-8">
            <h5 class="mb-3">Billing Details</h5>

            <form action="order_place.php" method="POST">
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
                    <select name="payment" class="form-control" required>
                        <option value="cod">Cash on Delivery</option>
                        <option value="online">Online Payment (Dummy)</option>
                    </select>
                </div>

                <input type="hidden" name="total_amount" value="<?= $total ?>">

                <button type="submit" class="btn btn-dark w-100 btn-lg">Place Order</button>
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

                        <li class="list-group-item d-flex justify-content-between px-0 fw-bold">
                            <span>Total (INR)</span>
                            <span>₹<?= number_format($total, 2) ?></span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
