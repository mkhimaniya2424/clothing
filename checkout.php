<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';
$title_page = "Checkout";

// Sample cart data
$cart = [
    ["id"=>1, "name"=>"Stylish Dress 1", "price"=>1499, "qty"=>2],
    ["id"=>3, "name"=>"Casual Shirt", "price"=>999, "qty"=>1]
];
$total = 0;
foreach($cart as $item) $total += $item['price'] * $item['qty'];
?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">Checkout</h2>

    <div class="row">
        <div class="col-md-8">
<?php
// Fetch user data if logged in
$name = "";
$email = "";
$address_text = "";

if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $u_query = "SELECT * FROM users WHERE id='$user_id'";
    $u_res = mysqli_query($con, $u_query);
    if ($u_row = mysqli_fetch_assoc($u_res)) {
        $name = $u_row['username']; // Or add a fullname column if needed, using username for now
        $email = $u_row['email'];
    }

    $a_query = "SELECT * FROM user_address WHERE user_id='$user_id' LIMIT 1";
    $a_res = mysqli_query($con, $a_query);
    if ($a_row = mysqli_fetch_assoc($a_res)) {
        $address_text = $a_row['address_line1'] . ", " . $a_row['city'] . ", " . $a_row['state'] . " - " . $a_row['postal_code'];
    }
}
?>
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
                    <textarea name="address" class="form-control" required><?= htmlspecialchars($address_text) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Payment Method</label>
                    <select name="payment" class="form-control" required>
                        <option value="cod">Cash on Delivery</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Place Order</button>
            </form>
        </div>

        <div class="col-md-4">
            <h5 class="mb-3">Order Summary</h5>
            <ul class="list-group mb-3">
                <?php foreach($cart as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= $item['name'] ?> x <?= $item['qty'] ?>
                        <span>₹<?= $item['price'] * $item['qty'] ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    Total <span>₹<?= $total ?></span>
                </li>
            </ul>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
