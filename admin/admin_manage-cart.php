<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Fetch Cart Items
$sql = "SELECT c.*, u.username, p.title, p.price, p.images 
        FROM cart c 
        JOIN users u ON c.user_id = u.id 
        JOIN products p ON c.product_id = p.id 
        ORDER BY c.user_id, c.created_at DESC";
$result = $con->query($sql);
?>

<div class="container mt-4">
    <h3>Manage Carts (Active Users)</h3>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $img = "https://via.placeholder.com/50";
                                if(!empty($row['images'])) {
                                    $decoded = json_decode($row['images'], true);
                                    if($decoded && count($decoded) > 0) $img = $decoded[0];
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><img src="<?= htmlspecialchars($img) ?>" width="50" height="50" class="rounded"></td>
                                <td><?= $row['quantity'] ?></td>
                                <td>₹<?= number_format($row['price'], 2) ?></td>
                                <td>₹<?= number_format($row['price'] * $row['quantity'], 2) ?></td>
                                <td><?= $row['created_at'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No active carts found.</td></tr>
                        <?php endif; ?>
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
