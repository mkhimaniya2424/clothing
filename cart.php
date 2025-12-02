<?php
ob_start();
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $pid = intval($_POST['product_id']);
        
        if ($_POST['action'] === 'add') {
            $qty = intval($_POST['qty']);
            // Check if exists
            $check = $con->query("SELECT id, quantity FROM cart WHERE user_id=$user_id AND product_id=$pid");
            if ($check->num_rows > 0) {
                $con->query("UPDATE cart SET quantity = quantity + $qty WHERE user_id=$user_id AND product_id=$pid");
            } else {
                $con->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $pid, $qty)");
            }
        } elseif ($_POST['action'] === 'update') {
            $qty = intval($_POST['qty']);
            if ($qty > 0) {
                $con->query("UPDATE cart SET quantity=$qty WHERE user_id=$user_id AND product_id=$pid");
            } else {
                $con->query("DELETE FROM cart WHERE user_id=$user_id AND product_id=$pid");
            }
        } elseif ($_POST['action'] === 'remove') {
            $con->query("DELETE FROM cart WHERE user_id=$user_id AND product_id=$pid");
        }
    }
    header("Location: cart.php");
    exit;
}

// Fetch Cart Items from DB
$cartItems = [];
$total = 0;

$sql = "SELECT c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id";
$res = $con->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $qty = $row['quantity'];
        $subtotal = $row['price'] * $qty;
        $total += $subtotal;
        
        $img = "https://via.placeholder.com/100";
        if (!empty($row['images'])) {
            $decoded = json_decode($row['images'], true);
            if ($decoded) $img = $decoded[0];
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
?>

<section class="container py-5">
    <h2 class="mb-4">Shopping Cart</h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info text-center">
            Your cart is empty. <a href="shop.php">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($item['image']) ?>" width="60" class="me-3 rounded">
                                        <span><?= htmlspecialchars($item['title']) ?></span>
                                    </div>
                                </td>
                                <td>₹<?= number_format($item['price'], 2) ?></td>
                                <td style="width: 150px;">
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" class="form-control form-control-sm me-2" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td>₹<?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Cart Total</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <strong>₹<?= number_format($total, 2) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fs-5">Total</span>
                            <span class="fs-5 fw-bold">₹<?= number_format($total, 2) ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-dark w-100 btn-lg">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
