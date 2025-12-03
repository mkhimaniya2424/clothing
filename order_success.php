<?php
ob_start();
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

$title_page = "Order Success";

// Require login
requireLogin('home.php');

$user_id = getUserId();
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order details
$order = null;
if ($order_id > 0) {
    $stmt = $con->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
}

// Calculate estimated delivery (7 days from now)
$estimated_delivery = date('F j, Y', strtotime('+7 days'));
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fa fa-check-circle text-success" style="font-size: 80px;"></i>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Order Placed Successfully!</h2>
                    <p class="text-muted mb-4">Thank you for your purchase. Your order has been received and is being processed.</p>
                    
                    <?php if ($order): ?>
                    <div class="bg-light p-4 rounded mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted mb-1">Order ID</h6>
                                <p class="fw-bold mb-0">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted mb-1">Order Date</h6>
                                <p class="fw-bold mb-0"><?= date('F j, Y', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted mb-1">Total Amount</h6>
                                <p class="fw-bold mb-0">₹<?= number_format($order['final_amount'], 2) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted mb-1">Payment Method</h6>
                                <p class="fw-bold mb-0"><?= strtoupper($order['payment_method']) ?></p>
                            </div>
                            <div class="col-12">
                                <h6 class="text-muted mb-1">Estimated Delivery</h6>
                                <p class="fw-bold mb-0"><?= $estimated_delivery ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="orders.php" class="btn btn-primary btn-lg">
                            <i class="fa fa-box me-2"></i>View My Orders
                        </a>
                        <a href="shop.php" class="btn btn-outline-primary btn-lg">
                            <i class="fa fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <i class="fa fa-info-circle me-2"></i>
                <strong>What's Next?</strong> You will receive an email confirmation shortly. You can track your order status in the "My Orders" section.
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
