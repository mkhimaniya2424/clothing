<?php
ob_start();
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=product_view.php?id=" . $_POST['product_id']);
        exit;
    }
    
    $user_id = getUserId();
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $con->prepare("INSERT INTO rating_reviews (product_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $product_id, $user_id, $rating, $review);
        $stmt->execute();
        $stmt->close();
        // Redirect to avoid resubmission
        header("Location: product_view.php?id=$product_id");
        exit;
    }
}


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $con->prepare("SELECT p.* FROM products p 
                       WHERE p.id = ?");
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

// Get brand name
$brand_name = "N/A";
if (!empty($product['category_brand'])) {
    $brand_stmt = $con->prepare("SELECT name FROM brands WHERE name = ? LIMIT 1");
    $brand_stmt->bind_param("s", $product['category_brand']);
    $brand_stmt->execute();
    $brand_result = $brand_stmt->get_result();
    if ($brand_row = $brand_result->fetch_assoc()) {
        $brand_name = $brand_row['name'];
    }
    $brand_stmt->close();
}

// Get category name
$category_name = "N/A";
if (!empty($product['category_main'])) {
    $cat_stmt = $con->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1");
    $cat_id = intval($product['category_main']);
    $cat_stmt->bind_param("i", $cat_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat_row = $cat_result->fetch_assoc()) {
        $category_name = $cat_row['name'];
    }
    $cat_stmt->close();
}

// Parse sizes
$sizes = [];
if (!empty($product['sizes'])) {
    $sizes = array_map('trim', explode(',', $product['sizes']));
}

// Check stock
$stock = isset($product['stock']) ? intval($product['stock']) : 0;
$in_stock = $stock > 0 || $stock === 0; // If stock is 0 or not set, we'll allow ordering

// Fetch reviews
$reviews = [];
$review_stmt = $con->prepare("SELECT r.*, u.username FROM rating_reviews r 
                              JOIN users u ON r.user_id = u.id 
                              WHERE r.product_id = ? 
                              ORDER BY r.created_at DESC LIMIT 10");
$review_stmt->bind_param("i", $id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
while ($review_row = $review_result->fetch_assoc()) {
    $reviews[] = $review_row;
}
$review_stmt->close();

// Calculate average rating
$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $total_rating / count($reviews);
}

$stmt->close();
?>

<section class="container py-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6">
            <div id="prodCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded shadow-sm">
                    <?php foreach($images as $i => $img): ?>
                    <div class="carousel-item <?= $i===0?'active':'' ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100" alt="Product Image" style="max-height: 500px; object-fit: contain;">
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
        
        <!-- Product Details -->
        <div class="col-md-6">
            <h2 class="fw-bold"><?= htmlspecialchars($product['title']) ?></h2>
            
            <!-- Rating -->
            <?php if (!empty($reviews)): ?>
            <div class="mb-3">
                <span class="text-warning">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fa<?= $i <= round($avg_rating) ? 's' : 'r' ?> fa-star"></i>
                    <?php endfor; ?>
                </span>
                <span class="text-muted ms-2"><?= number_format($avg_rating, 1) ?> (<?= count($reviews) ?> reviews)</span>
            </div>
            <?php endif; ?>
            
            <h3 class="text-primary my-3">₹<?= number_format($product['price'], 2) ?></h3>
            
            <!-- Stock Status -->
            <?php if ($in_stock): ?>
                <p class="text-success"><i class="fa fa-check-circle me-1"></i>In Stock</p>
            <?php else: ?>
                <p class="text-danger"><i class="fa fa-times-circle me-1"></i>Out of Stock</p>
            <?php endif; ?>
            
            <!-- Category & Brand -->
            <div class="mb-3">
                <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($category_name) ?></p>
                <p class="mb-1"><strong>Brand:</strong> <?= htmlspecialchars($brand_name) ?></p>
                <?php if (!empty($product['fabric'])): ?>
                <p class="mb-1"><strong>Fabric:</strong> <?= htmlspecialchars($product['fabric']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <div class="mb-4">
                <h5>Description</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <!-- Highlights -->
            <?php if (!empty($product['highlight'])): ?>
            <div class="mb-4">
                <h5>Highlights</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($product['highlight'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form method="POST" action="cart.php" class="mb-3">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="action" value="add">
                
                <?php if (!empty($sizes)): ?>
                <div class="mb-3">
                    <label class="form-label"><strong>Select Size</strong></label>
                    <select name="size" class="form-select" style="max-width: 200px;">
                        <?php foreach($sizes as $size): ?>
                        <option value="<?= htmlspecialchars($size) ?>"><?= strtoupper(htmlspecialchars($size)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="d-flex gap-3 align-items-center mb-3">
                    <div class="input-group" style="max-width: 150px;">
                        <span class="input-group-text">Qty</span>
                        <input type="number" name="qty" class="form-control" value="1" min="1" max="10">
                    </div>
                    
                    <button type="submit" class="btn btn-dark btn-lg flex-grow-1" <?= !$in_stock ? 'disabled' : '' ?>>
                        <i class="fa fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                </div>
            </form>
            
            <!-- Wishlist Button -->
            <a href="wishlist.php?action=add&id=<?= $product['id'] ?>" class="btn btn-outline-danger w-100">
                <i class="fa fa-heart me-2"></i>Add to Wishlist
            </a>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="fw-bold mb-4">Customer Reviews</h4>
            
            <!-- Add Review Form -->
            <?php if (isLoggedIn()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Write a Review</h5>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select w-auto" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea name="review" class="form-control" rows="3" placeholder="Share your thoughts..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-dark">Submit Review</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-light border mb-4">
                    Please <a href="login.php?redirect=product_view.php?id=<?= $product['id'] ?>">login</a> to write a review.
                </div>
            <?php endif; ?>

            <?php if (!empty($reviews)): ?>
            <?php foreach($reviews as $review): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($review['username']) ?></h6>
                            <div class="text-warning mb-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <small class="text-muted"><?= date('M j, Y', strtotime($review['created_at'])) ?></small>
                    </div>
                    <?php if (!empty($review['review'])): ?>
                    <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
