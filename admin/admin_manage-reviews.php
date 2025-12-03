<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $con->query("DELETE FROM rating_reviews WHERE id=$id");
    header("Location: admin_manage-reviews.php");
    exit;
}

// Fetch Reviews
$sql = "SELECT r.*, p.title, u.username 
        FROM rating_reviews r 
        JOIN products p ON r.product_id = p.id 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";
$res = $con->query($sql);
?>

<div class="container mt-4">
    <h3>Manage Reviews</h3>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && $res->num_rows > 0): ?>
                            <?php while($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <span class="text-warning">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                                    </span>
                                </td>
                                <td><?= nl2br(htmlspecialchars($row['review'])) ?></td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No reviews found.</td></tr>
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
