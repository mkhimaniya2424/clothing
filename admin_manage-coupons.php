<?php
ob_start();
require_once 'db_connect.php';

$msg = '';

// Add Coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coupon') {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['discount_type'];
    $value = floatval($_POST['discount_value']);
    $min_purchase = floatval($_POST['min_purchase']);
    $valid_from = $_POST['valid_from'];
    $valid_until = $_POST['valid_until'];

    $stmt = $con->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_purchase_amount, valid_from, valid_until, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssddss", $code, $type, $value, $min_purchase, $valid_from, $valid_until);
    
    if ($stmt->execute()) {
        $msg = "Coupon added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete Coupon
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $con->query("DELETE FROM coupons WHERE id=$id");
    $msg = "Coupon deleted.";
}

// Toggle Status
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $con->query("UPDATE coupons SET status = IF(status='active','inactive','active') WHERE id=$id");
    $msg = "Coupon status updated.";
}

// Fetch Coupons
$coupons = $con->query("SELECT * FROM coupons ORDER BY id DESC");
?>

<div class="container mt-4">
    <h3>Manage Coupons</h3>
    
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addCouponModal">
        + Add Coupon
    </button>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Code</th>
                <th>Discount</th>
                <th>Min Purchase</th>
                <th>Valid From</th>
                <th>Valid Until</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $coupons->fetch_assoc()): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['code']) ?></strong></td>
                <td>
                    <?= $row['discount_value'] ?> 
                    <?= $row['discount_type'] == 'percentage' ? '%' : 'INR' ?>
                </td>
                <td><?= $row['min_purchase_amount'] ?></td>
                <td><?= $row['valid_from'] ?></td>
                <td><?= $row['valid_until'] ?></td>
                <td>
                    <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="?toggle=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">Toggle</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete coupon?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="add_coupon">
                <div class="modal-header">
                    <h5 class="modal-title">Add Coupon</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Coupon Code</label>
                        <input type="text" name="code" class="form-control text-uppercase" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Type</label>
                            <select name="discount_type" class="form-control">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (INR)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Value</label>
                            <input type="number" step="0.01" name="discount_value" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Min Purchase Amount</label>
                        <input type="number" step="0.01" name="min_purchase" class="form-control" value="0">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Valid From</label>
                            <input type="date" name="valid_from" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Valid Until</label>
                            <input type="date" name="valid_until" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
