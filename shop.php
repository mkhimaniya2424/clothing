<?php
ob_start();
$title_page = "Shop";
require_once 'db_connect.php';

/* -----------------------------
   RECEIVE FILTER VALUES
------------------------------*/
$main_category = $_GET['main_category'] ?? "All";
$brand = $_GET['brand'] ?? "All";

$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== "" ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== "" ? intval($_GET['max_price']) : 500000;


/* -----------------------------
   MAIN CATEGORIES (STATIC LIST)
------------------------------*/
$main_categories = ["All", "Men", "Women", "Kids", "Accessories"];


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
    $cat = $con->real_escape_string($main_category);
    $sql .= " AND p.category_main = '$cat'";
}

// BRAND filter
if ($brand !== "All") {
    $br = $con->real_escape_string($brand);
    $sql .= " AND p.category_brand = '$br'";
}

// PRICE filter
if ($min_price < 0) $min_price = 0;
if ($max_price < $min_price) $max_price = $min_price + 1;

$sql .= " AND p.price BETWEEN $min_price AND $max_price";

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
                        <select name="main_category" class="form-select" onchange="this.form.submit()">
                            <?php foreach($main_categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= $main_category == $cat ? 'selected' : '' ?>>
                                    <?= $cat ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- BRAND -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Brand</label>
                        <select name="brand" class="form-select" onchange="this.form.submit()">
                            <?php foreach($brands as $b): ?>
                                <option value="<?= $b ?>" <?= $brand == $b ? 'selected' : '' ?>>
                                    <?= $b ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- PRICE RANGE -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price Range</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= $min_price ?>">
                            <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= $max_price ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">Apply</button>
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
                    ?>

                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                            
                            <div class="card-body text-center">
                                <h6 class="text-truncate"><?= htmlspecialchars($p['title']) ?></h6>
                                <p class="fw-bold text-primary">₹<?= number_format($p['price'], 2) ?></p>
                                <a href="product_view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark w-100">
                                    View
                                </a>
                            </div>

                            <div class="card-footer bg-white d-flex justify-content-center gap-2">
                                <a href="wishlist.php?action=add&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-heart"></i>
                                </a>

                                <form action="cart.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button class="btn btn-sm btn-dark"><i class="fa fa-shopping-cart"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>

                <?php else: ?>
                    
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            No products found for selected filters.
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
