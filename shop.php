<?php
ob_start();
$title_page = "Shop";
require_once 'db_connect.php';

// --- Filters ---
$category = $_GET['category'] ?? "All";
$brand = $_GET['brand'] ?? "All";
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 10000;

// Fetch Categories
$catRes = $con->query("SELECT * FROM categories WHERE status='active' OR status=1 ORDER BY name");
$categories = [];
while($c = $catRes->fetch_assoc()) $categories[] = $c['name'];
array_unshift($categories, "All");

// Fetch Brands
$brandRes = $con->query("SELECT * FROM brands ORDER BY name");
$brands = [];
while($b = $brandRes->fetch_assoc()) $brands[] = $b['name'];
array_unshift($brands, "All");

// Build Query
$sql = "SELECT p.*, ps.stock FROM products p LEFT JOIN product_stock ps ON p.id = ps.product_id WHERE p.status='active'";

if($category != "All") {
    $sql .= " AND (p.category_main = '" . $con->real_escape_string($category) . "' OR p.category_sub = '" . $con->real_escape_string($category) . "')";
}
if($brand != "All") {
    $sql .= " AND p.category_brand = '" . $con->real_escape_string($brand) . "'";
}
$sql .= " AND p.price BETWEEN $min_price AND $max_price";

$result = $con->query($sql);
?>

<section class="container py-5">
    <div class="row">
        <!-- FILTERS SIDEBAR -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 p-3">
                <h5 class="fw-bold mb-3">Filters</h5>
                
                <form method="GET">
                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= $category==$cat?'selected':'' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Brand -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Brand</label>
                        <select name="brand" class="form-select" onchange="this.form.submit()">
                            <?php foreach($brands as $b): ?>
                                <option value="<?= $b ?>" <?= $brand==$b?'selected':'' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price Range</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= $min_price ?>">
                            <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= $max_price ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">Apply Filters</button>
                    <a href="shop.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                </form>
            </div>
        </div>

        <!-- PRODUCTS GRID -->
        <div class="col-md-9">
            <div class="row g-4">
                <?php 
                if($result && $result->num_rows > 0):
                    while($p = $result->fetch_assoc()):
                        $img = "https://via.placeholder.com/400";
                        if(!empty($p['images'])) {
                            $decoded = json_decode($p['images'], true);
                            if($decoded && count($decoded) > 0) $img = $decoded[0];
                        }
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['title']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($p['title']) ?></h6>
                            <p class="fw-bold text-primary">₹<?= number_format($p['price'], 2) ?></p>
                            <a href="product_view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark w-100">View</a>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-center gap-2">
                             <a href="wishlist.php?action=add&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" title="Wishlist"><i class="fa fa-heart"></i></a>
                             <form action="cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-sm btn-dark" title="Add to Cart"><i class="fa fa-shopping-cart"></i></button>
                             </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                else: ?>
                    <div class="col-12"><div class="alert alert-warning text-center">No products found matching your filters.</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
