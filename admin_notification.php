<?php
ob_start();

// Load products from JSON
$productsFile = __DIR__ . '/cloapi/products.json';
$products = json_decode(file_get_contents($productsFile), true) ?: [];

// Prepare notifications array
$notifications = [];

// Check for out-of-stock products
foreach($products as $p) {
    if(($p['stock'] ?? 0) <= 0) {
        $notifications[] = [
            'type' => 'Stock Alert',
            'message' => "Product '{$p['title']}' is out of stock!",
            'id' => $p['id']
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
                    <a href="edit-products.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-warning">View</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-success mt-3">No new notifications!</div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("layout1.php"); // Use your main layout
?>
