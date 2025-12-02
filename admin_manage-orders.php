    header("Location: admin_manage-orders.php");
    exit;
}

$orders = mysqli_query($con, "
    SELECT o.*, u.username, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Orders</h2>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div><?= htmlspecialchars($row['username'] ?? 'Guest') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['email'] ?? '') ?></small>
                            </td>
                            <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                switch($row['order_status']) {
                                    case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                    case 'processing': $statusClass = 'bg-info text-dark'; break;
                                    case 'shipped': $statusClass = 'bg-primary'; break;
                                    case 'delivered': $statusClass = 'bg-success'; break;
                                    case 'cancelled': $statusClass = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($row['order_status']) ?></span>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($row['order_date'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?= $row['id'] ?>">
                                    <i class="fa fa-eye"></i> View
                                </button>
                            </td>
                        </tr>

                        <!-- Order Details Modal -->
                        <div class="modal fade" id="orderModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Order #<?= $row['id'] ?> Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h6>Shipping Address</h6>
                                                <p class="text-muted"><?= nl2br(htmlspecialchars($row['shipping_address'])) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Order Info</h6>
                                                <p class="mb-1"><strong>Payment Method:</strong> <?= ucfirst($row['payment_method']) ?></p>
                                                <p class="mb-1"><strong>Payment Status:</strong> <?= ucfirst($row['payment_status']) ?></p>
                                            </div>
                                        </div>

                                        <h6>Order Items</h6>
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $oid = $row['id'];
                                                $items = mysqli_query($con, "
                                                    SELECT oi.*, p.name 
                                                    FROM order_items oi 
                                                    LEFT JOIN products p ON oi.product_id = p.id 
                                                    WHERE oi.order_id = $oid
                                                ");
                                                while($item = mysqli_fetch_assoc($items)):
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                                    <td>₹<?= number_format($item['price'], 2) ?></td>
                                                    <td><?= $item['quantity'] ?></td>
                                                    <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>

                                        <form method="POST" class="mt-4 border-top pt-3">
                                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                            <div class="row align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label">Update Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?= $row['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="processing" <?= $row['order_status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                                        <option value="shipped" <?= $row['order_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                        <option value="delivered" <?= $row['order_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="cancelled" <?= $row['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="submit" name="update_status" class="btn btn-success w-100">Update</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
