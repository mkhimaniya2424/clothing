<?php
ob_start();
$title_page = "Shop";
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';

/* -----------------------------
   RECEIVE FILTER VALUES
------------------------------*/
$main_category = $_GET['main_category'] ?? "All";
$brand = $_GET['brand'] ?? "All";
$sort = $_GET['sort'] ?? "newest";

$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== "" ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== "" ? intval($_GET['max_price']) : 500000;

/* -----------------------------
   GET MAIN CATEGORIES FROM DATABASE
------------------------------*/
$main_categories = [];
$catRes = $con->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name");
while($c = $catRes->fetch_assoc()) {
    $main_categories[] = $c;
}

/* -----------------------------
   GET BRANDS FROM DATABASE
------------------------------*/
$brands = ["All"];
$brandRes = $con->query("SELECT name FROM brands ORDER BY name");
while($b = $brandRes->fetch_assoc()) {
    $brands[] = $b['name'];
}

/* -----------------------------
   BUILD PRODUCT QUERY
------------------------------*/
$sql = "
    SELECT p.*, ps.stock 
    FROM products p
    LEFT JOIN product_stock ps ON p.id = ps.product_id
    WHERE p.status='active'
";

// MAIN CATEGORY filter
if ($main_category !== "All") {
    // If main_category is numeric, it's an ID. If string, it might be a name.
    // The database seems to store IDs ('1') for categories.
    $cat = $con->real_escape_string($main_category);
    $sql .= " AND p.category_main = '$cat'";
}


// BRAND filter
if ($brand !== "All") {
    $br = $con->real_escape_string($brand);
    $sql .= " AND p.category_brand = '$br'";
}


// PRICE filter
if (isset($_GET['min_price']) && $_GET['min_price'] !== "") {
    $min_price = intval($_GET['min_price']);
    $sql .= " AND p.price >= $min_price";
}

if (isset($_GET['max_price']) && $_GET['max_price'] !== "") {
    $max_price = intval($_GET['max_price']);
    $sql .= " AND p.price <= $max_price";
}


// SORTING
switch($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.title ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$result = $con->query($sql);
?>

<!-- ===========================
     PAGE LAYOUT
=========================== -->
<section class="container py-5">
    <div class="row">

        <!-- ===========================
             FILTER SIDEBAR
        ============================ -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 p-3">
                <h5 class="fw-bold mb-3">Filters</h5>

                <form method="GET">


                    <!-- MAIN CATEGORY -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="main_category" class="form-select">
                            <option value="All">All Categories</option>
                            <?php foreach($main_categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $main_category == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <!-- BRAND (LOGOS) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Brand</label>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="border rounded p-1 cursor-pointer <?= $brand === 'All' ? 'border-primary bg-light' : '' ?>" style="cursor:pointer;" title="All Brands">
                                <input type="radio" name="brand" value="All" class="d-none" <?= $brand === 'All' ? 'checked' : '' ?> onchange="this.form.submit()">
                                <span class="px-2">All</span>
                            </label>
                            <?php 
                            // Fetch brands with logos
                            $brandRes = $con->query("SELECT name, logo FROM brands ORDER BY name");
                            while($b = $brandRes->fetch_assoc()):
                                $isSelected = ($brand === $b['name']);
                                $logoUrl = !empty($b['logo']) ? $b['logo'] : 'https://via.placeholder.com/50x30?text='.$b['name'];
                            ?>
                            <label class="border rounded p-1 cursor-pointer <?= $isSelected ? 'border-primary bg-light' : '' ?>" style="cursor:pointer; width: 60px; height: 40px; display: flex; align-items: center; justify-content: center;" title="<?= htmlspecialchars($b['name']) ?>">
                                <input type="radio" name="brand" value="<?= htmlspecialchars($b['name']) ?>" class="d-none" <?= $isSelected ? 'checked' : '' ?> onchange="this.form.submit()">
                                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($b['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- PRICE RANGE -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price Range</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                            <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                        </div>
                    </div>

                    <!-- SORT -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name: A to Z</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">Apply Filters</button>
                    <a href="shop.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                </form>

            </div>
        </div>


        <!-- ===========================
             PRODUCTS GRID
        ============================ -->
        <div class="col-md-9">
            <div class="row g-4">

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($p = $result->fetch_assoc()): 
                        $img = "https://via.placeholder.com/400";
                        if (!empty($p['images'])) {
                            $decoded = json_decode($p['images'], true);
                            if ($decoded && isset($decoded[0])) $img = $decoded[0];
                        }
                        
                        $stock = isset($p['stock']) ? intval($p['stock']) : 0;
                        $in_stock = true; // Allow ordering even if stock is 0
                    ?>

                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm border-0 position-relative">
                            <?php if (!$in_stock && $stock === 0): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">Out of Stock</span>
                            </div>
                            <?php endif; ?>
                            
                            <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                            
                            <div class="card-body text-center">
                                <h6 class="text-truncate"><?= htmlspecialchars($p['title']) ?></h6>
                                <p class="fw-bold text-primary mb-2">₹<?= number_format($p['price'], 2) ?></p>
                                <a href="product_view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark w-100">
                                    View Details
                                </a>
                            </div>

                            <div class="card-footer bg-white d-flex justify-content-center gap-2">
                                <a href="wishlist.php?action=add&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" title="Add to Wishlist">
                                    <i class="fa fa-heart"></i>
                                </a>

                                <form action="cart.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button class="btn btn-sm btn-dark" title="Add to Cart"><i class="fa fa-shopping-cart"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>

                <?php else: ?>
                    
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="fa fa-exclamation-triangle fa-3x mb-3 d-block"></i>
                            <h5>No products found</h5>
                            <p>Try adjusting your filters or browse all products.</p>
                            <a href="shop.php" class="btn btn-primary">View All Products</a>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>


<?php
$content = ob_get_clean();
include_once("layout.php");
?>
