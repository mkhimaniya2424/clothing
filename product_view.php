<?php
ob_start();
require_once 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $con->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows == 0) {
    header("Location: shop.php");
    exit;
}

$product = $res->fetch_assoc();
$title_page = $product['title'];

$images = [];
if(!empty($product['images'])) {
    $decoded = json_decode($product['images'], true);
    if($decoded) $images = $decoded;
}
if(empty($images)) $images[] = "https://via.placeholder.com/500";
?>

<section class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <div id="prodCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded shadow-sm">
                    <?php foreach($images as $i => $img): ?>
                    <div class="carousel-item <?= $i===0?'active':'' ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100" alt="Product Image">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if(count($images) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#prodCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#prodCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <h2 class="fw-bold"><?= htmlspecialchars($product['title']) ?></h2>
            <h3 class="text-primary my-3">₹<?= number_format($product['price'], 2) ?></h3>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <?php if(!empty($product['sizes'])): ?>
            <div class="mb-4">
                <h5>Available Sizes</h5>
                <p><?= htmlspecialchars($product['sizes']) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="cart.php" class="d-flex gap-3 align-items-center">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="input-group w-25">
                    <span class="input-group-text">Qty</span>
                    <input type="number" name="qty" class="form-control" value="1" min="1">
                </div>
                
                <button type="submit" class="btn btn-dark btn-lg flex-grow-1">
                    <i class="fa fa-shopping-cart me-2"></i> Add to Cart
                </button>
            </form>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
