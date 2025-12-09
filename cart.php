<?php
ob_start();
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

// Require login
requireLogin('cart.php');

$user_id = getUserId();
$title_page = "Shopping Cart";

// Handle Promo Code Actions
$promoMessage = '';
$promoError = '';
$currentDate = date('Y-m-d');

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'apply_promo' && isset($_POST['promo_code'])) {
        $code = strtoupper(trim($_POST['promo_code']));
        
        // Check if promo code exists and is active
        $stmt = $con->prepare("SELECT * FROM promotions WHERE code = ? AND status='active'");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $promo = $result->fetch_assoc();
            $_SESSION['applied_promo'] = $promo;
            $promoMessage = "Promo code '{$code}' applied successfully!";
        } else {
            $promoError = "Invalid or expired promo code.";
        }
        $stmt->close();
        
        // Redirect to remove query params
        header("Location: cart.php");
        exit;
    } elseif ($_GET['action'] === 'remove_promo') {
        unset($_SESSION['applied_promo']);
        header("Location: cart.php");
        exit;
    }
}

// Handle Cart Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
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
            'image' => $img,
            'category_brand' => $row['category_brand'] ?? ''
        ];
    }
}

// Fetch recommended products (random products not in cart)
$cartProductIds = array_column($cartItems, 'id');
$excludeIds = !empty($cartProductIds) ? implode(',', $cartProductIds) : '0';
$recommendedSql = "SELECT * FROM products WHERE status='active' AND id NOT IN ($excludeIds) ORDER BY RAND() LIMIT 4";
$recommendedResult = $con->query($recommendedSql);
?>

<style>
.cart-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 0;
    color: white;
    margin-bottom: 30px;
}
.cart-item-card {
    border: none;
    border-radius: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}
.cart-item-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-left-color: #667eea;
}
.cart-summary {
    position: sticky;
    top: 100px;
}
.recommended-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}
.recommended-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.empty-cart-icon {
    font-size: 5rem;
    color: #cbd5e0;
}
.shopping-options {
    background: #f7fafc;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}
.option-btn {
    border-radius: 10px;
    padding: 12px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.qty-control {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
}
</style>

<!-- Cart Header -->
<div class="cart-header">
    <div class="container">
        <h1 class="mb-2"><i class="fa fa-shopping-cart me-3"></i>Shopping Cart</h1>
        <p class="mb-0 opacity-75"><?= count($cartItems) ?> item(s) in your cart</p>
    </div>
</div>

<section class="container pb-5">
    <?php if (empty($cartItems)): ?>
        <!-- Empty Cart State -->
        <div class="text-center py-5">
            <i class="fa fa-shopping-cart empty-cart-icon mb-4"></i>
            <h3 class="mb-3">Your Cart is Empty</h3>
            <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
            <a href="shop.php" class="btn btn-primary btn-lg px-5">
                <i class="fa fa-shopping-bag me-2"></i>Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <!-- Shopping Options -->
                <div class="shopping-options mb-4">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <a href="shop.php" class="btn btn-outline-primary option-btn w-100">
                                <i class="fa fa-plus-circle me-2"></i>Add More Items
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="wishlist.php" class="btn btn-outline-danger option-btn w-100">
                                <i class="fa fa-heart me-2"></i>View Wishlist
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="offers.php" class="btn btn-outline-success option-btn w-100">
                                <i class="fa fa-tags me-2"></i>View Offers
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="orders.php" class="btn btn-outline-info option-btn w-100">
                                <i class="fa fa-box me-2"></i>My Orders
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cart Items List -->
                <?php foreach ($cartItems as $item): ?>
                <div class="card cart-item-card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="<?= htmlspecialchars($item['image']) ?>" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($item['title']) ?></h6>
                                <?php if (!empty($item['category_brand'])): ?>
                                    <small class="text-muted"><i class="fa fa-tag me-1"></i><?= htmlspecialchars($item['category_brand']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Price</div>
                                <div class="fw-bold">₹<?= number_format($item['price'], 2) ?></div>
                            </div>
                            <div class="col-md-2">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <div class="input-group input-group-sm qty-control">
                                        <button type="button" class="btn btn-outline-secondary" onclick="this.nextElementSibling.stepDown(); this.form.submit();">-</button>
                                        <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" max="10" class="form-control text-center" onchange="this.form.submit()">
                                        <button type="button" class="btn btn-outline-secondary" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small">Subtotal</div>
                                <div class="fw-bold text-success">₹<?= number_format($item['subtotal'], 2) ?></div>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this item?')">
                                        <i class="fa fa-trash me-1"></i>Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-summary">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4"><i class="fa fa-receipt me-2"></i>Order Summary</h5>
                            
                            <?php
                            // Calculate discount if promo is applied
                            $discount = 0;
                            $appliedPromo = $_SESSION['applied_promo'] ?? null;
                            
                            if ($appliedPromo) {
                                if ($appliedPromo['discount_percentage'] > 0) {
                                    $discount = ($total * $appliedPromo['discount_percentage']) / 100;
                                } elseif ($appliedPromo['discount_amount'] > 0) {
                                    $discount = $appliedPromo['discount_amount'];
                                }
                                // Ensure discount doesn't exceed total
                                if ($discount > $total) $discount = $total;
                            }
                            
                            $finalTotal = $total - $discount;
                            ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal (<?= count($cartItems) ?> items)</span>
                                <strong>₹<?= number_format($total, 2) ?></strong>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Shipping</span>
                                <span class="text-success">FREE</span>
                            </div>
                            
                            <?php if ($discount > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Promo Discount</span>
                                <strong>-₹<?= number_format($discount, 2) ?></strong>
                            </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fs-5 fw-bold">Total</span>
                                <span class="fs-4 fw-bold text-primary">₹<?= number_format($finalTotal, 2) ?></span>
                            </div>
                            
                            <!-- Promo Code Section -->
                            <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 10px;">
                                <h6 class="mb-3"><i class="fa fa-tag me-2"></i>Promo Code</h6>
                                
                                <?php if ($appliedPromo): ?>
                                    <!-- Applied Promo -->
                                    <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa fa-check-circle me-2"></i>
                                            <strong><?= htmlspecialchars($appliedPromo['code']) ?></strong> applied!
                                            <br><small>You saved ₹<?= number_format($discount, 2) ?></small>
                                        </div>
                                        <a href="?action=remove_promo" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Apply Promo Form -->
                                    <form method="POST" action="?action=apply_promo">
                                        <div class="input-group">
                                            <input type="text" name="promo_code" class="form-control" placeholder="Enter promo code" style="border-radius: 8px 0 0 8px;">
                                            <button type="submit" class="btn btn-dark" style="border-radius: 0 8px 8px 0;">
                                                Apply
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <!-- Available Promos -->
                                    <?php
                                    $promoQuery = "SELECT * FROM promotions WHERE status='active' LIMIT 3";
                                    $promoResult = $con->query($promoQuery);
                                    if ($promoResult && $promoResult->num_rows > 0):
                                    ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Available codes:</small>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <?php while($promo = $promoResult->fetch_assoc()): ?>
                                            <span class="badge bg-light text-dark border" style="cursor: pointer;" onclick="document.querySelector('input[name=promo_code]').value='<?= $promo['code'] ?>'">
                                                <?= htmlspecialchars($promo['code']) ?>
                                            </span>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary w-100 btn-lg mb-3" style="border-radius: 10px;">
                                <i class="fa fa-lock me-2"></i>Proceed to Checkout
                            </a>
                            
                            <a href="shop.php" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">
                                <i class="fa fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>

                    <!-- Offers Banner -->
                    <div class="card shadow-sm border-0 mt-3" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body text-white text-center p-4">
                            <i class="fa fa-gift fa-3x mb-3"></i>
                            <h6 class="fw-bold">More Offers Available!</h6>
                            <p class="small mb-2">Check out our exclusive deals and save more</p>
                            <a href="offers.php" class="btn btn-light btn-sm">View All Offers</a>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Products -->
        <?php if ($recommendedResult && $recommendedResult->num_rows > 0): ?>
        <div class="mt-5">
            <h3 class="mb-4"><i class="fa fa-star me-2 text-warning"></i>You May Also Like</h3>
            <div class="row g-4">
                <?php while ($product = $recommendedResult->fetch_assoc()): 
                    $img = "https://via.placeholder.com/300";
                    if (!empty($product['images'])) {
                        $decoded = json_decode($product['images'], true);
                        if ($decoded && isset($decoded[0])) $img = $decoded[0];
                    }
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card recommended-card shadow-sm h-100">
                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($product['title']) ?></h6>
                            <p class="fw-bold text-primary mb-3">₹<?= number_format($product['price'], 2) ?></p>
                            <div class="d-flex gap-2">
                                <form method="POST" class="flex-fill">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button class="btn btn-sm btn-primary w-100">
                                        <i class="fa fa-cart-plus me-1"></i>Add
                                    </button>
                                </form>
                                <a href="product_view.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
