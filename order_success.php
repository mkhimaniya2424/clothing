<?php
ob_start();
$title_page = "Order Placed";
require_once 'db_connect.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<section class="container py-5 text-center">
    <div class="card shadow-sm border-0 p-5 mx-auto" style="max-width: 600px;">
        <div class="mb-4 text-success">
            <i class="fa fa-check-circle fa-5x"></i>
        </div>
        <h2 class="fw-bold mb-3">Order Placed Successfully!</h2>
        <p class="text-muted mb-4">Thank you for your purchase. Your order ID is <strong>#<?= $order_id ?></strong>.</p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="shop.php" class="btn btn-dark">Continue Shopping</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="orders.php" class="btn btn-outline-dark">View My Orders</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
