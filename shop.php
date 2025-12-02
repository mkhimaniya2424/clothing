<?php
ob_start();
$title_page = "Shop";
require_once __DIR__ . '/db/db_connect.php'; // Adjust path to your DB connection

// ===================
// Get category filter
// ===================
$categories = ["All", "Women", "Men", "Kids"]; // You can generate dynamically from DB if needed
$currentCategory = $_GET['category'] ?? "All";

// ===================
// Fetch products from DB
// ===================
$sql = "SELECT p.id, p.title AS name, p.price, p.images, p.category_main, p.category_sub, p.category_type, p.category_brand, 
               ps.stock
        FROM products p
        LEFT JOIN product_stock ps ON p.id = ps.product_id
        WHERE p.status='active'";
$result = $con->query($sql);

$products = [];
if($result){
    while($row = $result->fetch_assoc()){
        // Determine category to display (you can combine fields as needed)
        $category = $row['category_main'] ?: 'Other';

        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'img' => !empty($row['images']) ? json_decode($row['images'])[0] ?? '' : '', // take first image
            'category' => $category,
            'stock' => intval($row['stock'] ?? 0)
        ];
    }
}
?>

<section class="container py-5">
    <div class="row">
        <!-- ================= FILTERS SIDEBAR ================= -->
        <div class="col-md-3 mb-4">
            <h5 class="fw-bold mb-3" style="color:#808080;">Filter by Category</h5>
            <div class="list-group mb-4">
                <?php foreach($categories as $cat): ?>
                    <a href="?category=<?= urlencode($cat) ?>" 
                       class="list-group-item list-group-item-action <?= $currentCategory==$cat?'active':'' ?>"
                       style="color:#333; <?= $currentCategory==$cat?'background-color:#808080;color:white;':'' ?>">
                       <?= $cat ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= PRODUCTS GRID ================= -->
        <div class="col-md-9">
            <div class="row g-4">
                <?php foreach($products as $p): 
                    if($currentCategory != "All" && $p['category'] != $currentCategory) continue;
                    if($p['stock'] == 0) continue; // Optional: hide out of stock products
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="<?= $p['img'] ?>" class="card-img-top rounded-top" alt="<?= $p['name'] ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title" style="color:#808080;"><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="fw-bold text-dark">₹<?= number_format($p['price']) ?></p>
                            <a href="product_view.php?id=<?= $p['id'] ?>" class="btn btn-sm" style="background-color:#808080; color:white;">View</a>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-center">
                            <a href="wishlist.php?id=<?= $p['id'] ?>" class="btn btn-sm" style="border:1px solid #808080; color:#808080; width:50%; margin-right:5px;"><i class="fa fa-heart"></i></a>
                            <a href="cart.php?id=<?= $p['id'] ?>" class="btn btn-sm" style="border:1px solid #808080; color:#808080; width:50%; margin-left:5px;"><i class="fa fa-shopping-cart"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if(!array_filter($products, function($p) use($currentCategory){
                    return ($currentCategory=="All" || $p['category']==$currentCategory) && $p['stock']>0;
                })): ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">No products found for this category.</div>
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
