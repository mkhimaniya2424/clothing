<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';

    if ($order_id > 0) {
        $stmt = $con->prepare("UPDATE orders SET order_status=? WHERE id=?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: admin_manage-orders.php?msg=Order status updated successfully");
        exit;
    }
}

// Filter by status
$statusFilter = $_GET['status_filter'] ?? 'all';

// Fetch orders with user info
$ordersQuery = "
    SELECT o.*, u.username, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
";

if ($statusFilter !== 'all') {
    $ordersQuery .= " WHERE o.order_status = '" . $con->real_escape_string($statusFilter) . "'";
}

$ordersQuery .= " ORDER BY o.created_at DESC";

$orders = mysqli_query($con, $ordersQuery);
if (!$orders) {
    die("Query failed: " . mysqli_error($con));
}

// Get order statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'returned' => 0,
    'cancelled' => 0
];

$statsQuery = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
$statsResult = mysqli_query($con, $statsQuery);
while ($row = mysqli_fetch_assoc($statsResult)) {
    $stats[$row['order_status']] = $row['count'];
    $stats['total'] += $row['count'];
}

$msg = $_GET['msg'] ?? '';
?>

<style>
.stat-card {
    border-left: 4px solid;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.order-card {
    transition: all 0.3s ease;
    border-left: 4px solid #e2e8f0;
}
.order-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-left-color: #0d6efd;
}
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
.filter-btn {
    border-radius: 20px;
    padding: 8px 20px;
    transition: all 0.3s;
}
.filter-btn.active {
    background: #0d6efd;
    color: white;
}
</style>

<div class="container-fluid mt-4">
    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fa fa-shopping-cart me-2"></i>Order Management</h2>
            <p class="text-muted mb-0">Manage and track all customer orders</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #6c757d;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Orders</p>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="text-secondary">
                            <i class="fa fa-box fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pending</p>
                            <h3 class="mb-0 text-warning"><?= $stats['pending'] ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="fa fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #17a2b8;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Confirmed</p>
                            <h3 class="mb-0 text-info"><?= $stats['confirmed'] ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="fa fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #0d6efd;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Shipped</p>
                            <h3 class="mb-0 text-primary"><?= $stats['shipped'] ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="fa fa-shipping-fast fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Delivered</p>
                            <h3 class="mb-0 text-success"><?= $stats['delivered'] ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="fa fa-check-double fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Cancelled</p>
                            <h3 class="mb-0 text-danger"><?= $stats['cancelled'] ?></h3>
                        </div>
                        <div class="text-danger">
                            <i class="fa fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="?status_filter=all" class="btn filter-btn <?= $statusFilter === 'all' ? 'active' : 'btn-outline-secondary' ?>">
                    <i class="fa fa-list me-1"></i>All Orders
                </a>
                <a href="?status_filter=pending" class="btn filter-btn <?= $statusFilter === 'pending' ? 'active' : 'btn-outline-warning' ?>">
                    <i class="fa fa-clock me-1"></i>Pending
                </a>
                <a href="?status_filter=confirmed" class="btn filter-btn <?= $statusFilter === 'confirmed' ? 'active' : 'btn-outline-info' ?>">
                    <i class="fa fa-check-circle me-1"></i>Confirmed
                </a>
                <a href="?status_filter=shipped" class="btn filter-btn <?= $statusFilter === 'shipped' ? 'active' : 'btn-outline-primary' ?>">
                    <i class="fa fa-shipping-fast me-1"></i>Shipped
                </a>
                <a href="?status_filter=delivered" class="btn filter-btn <?= $statusFilter === 'delivered' ? 'active' : 'btn-outline-success' ?>">
                    <i class="fa fa-check-double me-1"></i>Delivered
                </a>
                <a href="?status_filter=returned" class="btn filter-btn <?= $statusFilter === 'returned' ? 'active' : 'btn-outline-warning' ?>">
                    <i class="fa fa-undo me-1"></i>Returned
                </a>
                <a href="?status_filter=cancelled" class="btn filter-btn <?= $statusFilter === 'cancelled' ? 'active' : 'btn-outline-danger' ?>">
                    <i class="fa fa-times-circle me-1"></i>Cancelled
                </a>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($orders)): 
            // Get status badge class
            $statusClass = 'bg-secondary';
            $statusIcon = 'fa-question';
            switch($order['order_status']) {
                case 'pending': 
                    $statusClass = 'bg-warning text-dark'; 
                    $statusIcon = 'fa-clock';
                    break;
                case 'confirmed': 
                    $statusClass = 'bg-info text-dark'; 
                    $statusIcon = 'fa-check-circle';
                    break;
                case 'processing': 
                    $statusClass = 'bg-info text-dark'; 
                    $statusIcon = 'fa-cog';
                    break;
                case 'packed': 
                    $statusClass = 'bg-primary'; 
                    $statusIcon = 'fa-box';
                    break;
                case 'shipped': 
                    $statusClass = 'bg-primary'; 
                    $statusIcon = 'fa-shipping-fast';
                    break;
                case 'delivered': 
                    $statusClass = 'bg-success'; 
                    $statusIcon = 'fa-check-double';
                    break;
                case 'returned': 
                    $statusClass = 'bg-warning'; 
                    $statusIcon = 'fa-undo';
                    break;
                case 'cancelled': 
                    $statusClass = 'bg-danger'; 
                    $statusIcon = 'fa-times-circle';
                    break;
            }
        ?>
        <div class="card order-card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <h5 class="mb-1">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                        <small class="text-muted"><?= date('M d, Y', strtotime($order['created_at'])) ?></small>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="fa fa-user text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($order['username'] ?? 'Guest') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($order['email'] ?? 'N/A') ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Payment</small>
                        <span class="badge bg-light text-dark"><?= strtoupper($order['payment_method']) ?></span>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Amount</small>
                        <h5 class="mb-0 text-success">₹<?= number_format($order['final_amount'], 2) ?></h5>
                    </div>
                    <div class="col-md-2 text-center">
                        <span class="status-badge <?= $statusClass ?>">
                            <i class="fa <?= $statusIcon ?> me-1"></i><?= ucfirst($order['order_status']) ?>
                        </span>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#orderModal<?= $order['id'] ?>" title="View Details">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div class="modal fade" id="orderModal<?= $order['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-1">Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                            <small class="text-muted">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <!-- Customer Info -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3"><i class="fa fa-user me-2 text-primary"></i>Customer Details</h6>
                                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($order['username'] ?? 'Guest') ?></p>
                                        <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shipping Address -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3"><i class="fa fa-map-marker-alt me-2 text-success"></i>Shipping Address</h6>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Info -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3"><i class="fa fa-credit-card me-2 text-info"></i>Payment Information</h6>
                                        <p class="mb-1"><strong>Method:</strong> <?= strtoupper($order['payment_method']) ?></p>
                                        <p class="mb-0">
                                            <strong>Status:</strong> 
                                            <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= ucfirst($order['payment_status']) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <h6 class="mb-3"><i class="fa fa-shopping-bag me-2"></i>Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $oid = intval($order['id']);
                                    $itemsQuery = "
                                        SELECT oi.*, p.title as name, p.images
                                        FROM order_items oi 
                                        LEFT JOIN products p ON oi.product_id = p.id 
                                        WHERE oi.order_id = $oid
                                    ";
                                    $items = mysqli_query($con, $itemsQuery);
                                    if ($items && mysqli_num_rows($items) > 0):
                                        while($item = mysqli_fetch_assoc($items)):
                                            $img = "https://via.placeholder.com/60";
                                            if (!empty($item['images'])) {
                                                $decoded = json_decode($item['images'], true);
                                                if ($decoded && isset($decoded[0])) $img = '../' . $decoded[0];
                                            }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $img ?>" alt="Product" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($item['name']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">₹<?= number_format($item['price'], 2) ?></td>
                                        <td class="text-center"><span class="badge bg-secondary"><?= intval($item['quantity']) ?></span></td>
                                        <td class="text-end fw-bold">₹<?= number_format($item['total'], 2) ?></td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                    <tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">₹<?= number_format($order['total_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end text-success"><strong>Discount:</strong></td>
                                        <td class="text-end text-success">-₹<?= number_format($order['discount_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Final Amount:</strong></td>
                                        <td class="text-end"><h5 class="mb-0 text-primary">₹<?= number_format($order['final_amount'], 2) ?></h5></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Update Status -->
                        <div class="card border-0 bg-light mt-4">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="fa fa-edit me-2"></i>Update Order Status</h6>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <div class="row align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label">Order Status</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                                <option value="confirmed" <?= $order['order_status'] == 'confirmed' ? 'selected' : '' ?>>✅ Confirmed</option>
                                                <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>⚙️ Processing</option>
                                                <option value="packed" <?= $order['order_status'] == 'packed' ? 'selected' : '' ?>>📦 Packed</option>
                                                <option value="shipped" <?= $order['order_status'] == 'shipped' ? 'selected' : '' ?>>🚚 Shipped</option>
                                                <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>✔️ Delivered</option>
                                                <option value="returned" <?= $order['order_status'] == 'returned' ? 'selected' : '' ?>>🔄 Returned</option>
                                                <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" name="update_status" class="btn btn-primary w-100">
                                                <i class="fa fa-save me-2"></i>Update Status
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No orders found</h5>
                <p class="text-muted">There are no orders matching your filter criteria.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
