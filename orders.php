<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$title_page = "My Orders";
ob_start();
?>

<?php
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $con->query($sql);
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">My Orders</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): 
                        $oid = $order['id'];
                        // Fetch item count
                        $item_res = $con->query("SELECT COUNT(*) as count FROM order_items WHERE order_id = $oid");
                        $item_count = $item_res->fetch_assoc()['count'];
                    ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= date("d M Y", strtotime($order['created_at'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $order['order_status']=='delivered'?'success':($order['order_status']=='cancelled'?'danger':'warning') ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                        </td>
                        <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td><?= $item_count ?> Items</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#order-<?= $oid ?>">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <!-- Order Details Row -->
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <div class="collapse" id="order-<?= $oid ?>">
                                <div class="card card-body bg-light border-0 m-3">
                                    <h6 class="fw-bold">Order Items:</h6>
                                    <ul class="list-group">
                                        <?php 
                                        $items_sql = "SELECT oi.*, p.title, p.images FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $oid";
                                        $items_res = $con->query($items_sql);
                                        while($item = $items_res->fetch_assoc()):
                                            $img = "https://via.placeholder.com/50";
                                            if(!empty($item['images'])) {
                                                $decoded = json_decode($item['images'], true);
                                                if($decoded && count($decoded) > 0) $img = $decoded[0];
                                            }
                                        ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= htmlspecialchars($img) ?>" alt="Product" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <p class="mb-0 fw-bold"><?= htmlspecialchars($item['title'] ?? 'Product Removed') ?></p>
                                                    <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                                </div>
                                            </div>
                                            <span>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                        </li>
                                        <?php endwhile; ?>
                                    </ul>
                                    <div class="mt-3">
                                        <strong>Shipping Address:</strong><br>
                                        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fa fa-shopping-bag fa-3x mb-3 text-muted"></i>
            <h4>No orders found!</h4>
            <p class="text-muted">Looks like you haven't placed any orders yet.</p>
            <a href="shop.php" class="btn btn-primary mt-3">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
