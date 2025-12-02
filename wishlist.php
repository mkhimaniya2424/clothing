<?php
ob_start();
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=wishlist.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Handle Add/Remove
if (isset($_GET['action']) && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    if ($_GET['action'] == 'add') {
        $con->query("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES ($user_id, $pid)");
        $msg = "Product added to wishlist.";
    } elseif ($_GET['action'] == 'remove') {
        $con->query("DELETE FROM wishlist WHERE user_id=$user_id AND product_id=$pid");
        $msg = "Product removed from wishlist.";
    }
}

// Fetch Wishlist
$sql = "SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = $user_id";
$result = $con->query($sql);
?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">My Wishlist</h2>
    
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while($p = $result->fetch_assoc()): 
                $img = "https://via.placeholder.com/400";
                if(!empty($p['images'])) {
                    $decoded = json_decode($p['images'], true);
                    if($decoded && count($decoded) > 0) $img = $decoded[0];
                }
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['title']) ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($p['title']) ?></h6>
                            <p class="fw-bold text-primary">₹<?= number_format($p['price'], 2) ?></p>
                        </div>
                        <div class="card-footer bg-white d-flex gap-2">
                            <form action="cart.php" method="POST" class="w-50">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-sm btn-dark w-100">Add to Cart</button>
                            </form>
                            <a href="wishlist.php?action=remove&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger w-50">Remove</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            Your wishlist is empty. <a href="shop.php">Browse Products</a>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
