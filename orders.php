<?php
ob_start();
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

$title_page = "My Orders";

// Require login
requireLogin('orders.php');

$user_id = getUserId();

// Fetch all orders for the user
$orders = [];
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Function to get order items
function getOrderItems($con, $order_id) {
    $items = [];
    $stmt = $con->prepare("SELECT oi.*, p.title, p.images FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $img = "https://via.placeholder.com/80";
        if (!empty($row['images'])) {
            $decoded = json_decode($row['images'], true);
            if ($decoded && isset($decoded[0])) $img = $decoded[0];
        }
        $row['image'] = $img;
        $items[] = $row;
    }
    $stmt->close();
    return $items;
}

// Function to get status badge class
function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'packed' => 'bg-primary',
        'shipped' => 'bg-primary',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    return $badges[$status] ?? 'bg-secondary';
}
?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">My Orders</h2>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info text-center">
            <i class="fa fa-box-open fa-3x mb-3 d-block"></i>
            <h5>No orders yet</h5>
            <p>You haven't placed any orders yet. Start shopping now!</p>
            <a href="shop.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        
        <?php foreach ($orders as $order): 
            $items = getOrderItems($con, $order['id']);
        ?>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <small class="text-muted">Order ID</small>
                        <h6 class="mb-0">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h6>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Order Date</small>
                        <h6 class="mb-0"><?= date('M j, Y', strtotime($order['created_at'])) ?></h6>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Total Amount</small>
                        <h6 class="mb-0">₹<?= number_format($order['final_amount'], 2) ?></h6>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge <?= getStatusBadge($order['order_status']) ?> px-3 py-2">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <h6 class="mb-3">Order Items</h6>
                
                <?php foreach ($items as $item): ?>
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                        <small class="text-muted">Quantity: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></small>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-0">₹<?= number_format($item['total'], 2) ?></h6>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="mb-2">Shipping Address</h6>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-2">Payment Details</h6>
                        <p class="mb-1"><strong>Method:</strong> <?= strtoupper($order['payment_method']) ?></p>
                        <p class="mb-0"><strong>Status:</strong> 
                            <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning' ?>">
                                <?= ucfirst($order['payment_status']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-white">
                <div class="d-flex gap-2">
                    <a href="product_view.php?id=<?= $items[0]['product_id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-eye me-1"></i>View Product
                    </a>
                    <?php if ($order['order_status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Are you sure you want to cancel this order?')) window.location.href='cancel_order.php?id=<?= $order['id'] ?>'">
                        <i class="fa fa-times me-1"></i>Cancel Order
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php endforeach; ?>
        
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
