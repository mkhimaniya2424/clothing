<?php
ob_start();
include_once("db_connect.php");

// Prepare notifications array
$notifications = [];

// Check for out-of-stock products
$sql = "
    SELECT p.id, p.title, ps.stock 
    FROM products p 
    JOIN product_stock ps ON p.id = ps.product_id 
    WHERE ps.stock <= 0
";
$res = $con->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'type' => 'Stock Alert',
            'message' => "Product '{$row['title']}' is out of stock!",
            'id' => $row['id']
        ];
    }
}

// Calculate notification count for badge
$notificationCount = count($notifications);
?>

<div class="container mt-4">
    <h2>Notifications</h2>

    <?php if(!empty($notifications)): ?>
        <div class="list-group mt-3">
            <?php foreach($notifications as $note): ?>
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($note['type']) ?>:</strong> <?= htmlspecialchars($note['message']) ?>
                    </div>
                    <a href="admin_edit-product.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-warning">View</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-success mt-3">No new notifications!</div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php"); // Use your main layout
?>
