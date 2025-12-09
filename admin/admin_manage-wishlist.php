<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Fetch Wishlist Items
$sql = "SELECT w.*, u.username, p.title, p.images 
        FROM wishlist w 
        JOIN users u ON w.user_id = u.id 
        JOIN products p ON w.product_id = p.id 
        ORDER BY w.id DESC";
$result = $con->query($sql);
?>

<div class="container mt-4">
    <h3>Manage Wishlists</h3>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $img = "https://via.placeholder.com/50";
                                if(!empty($row['images'])) {
                                    $decoded = json_decode($row['images'], true);
                                    if($decoded && count($decoded) > 0) $img = '../' . $decoded[0];
                                }
                            ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><img src="<?= htmlspecialchars($img) ?>" width="50" height="50" class="rounded" style="object-fit: cover;"></td>
                                <td><?= $row['created_at'] ?? 'N/A' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No items in wishlists.</td></tr>
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
