<?php
session_start();
ob_start();
$title_page = "Offers";

// Sample products on offer
$offers = [
    ["name"=>"Stylish Dress 1","desc"=>"Elegant and comfy outfit.","price"=>"₹1,499","discount"=>"20% Off","img"=>"https://source.unsplash.com/400x400/?dress,1"],
    ["name"=>"Stylish Dress 2","desc"=>"Trendy & chic design.","price"=>"₹1,299","discount"=>"30% Off","img"=>"https://source.unsplash.com/400x400/?dress,2"],
    ["name"=>"Stylish Dress 3","desc"=>"Perfect for casual wear.","price"=>"₹1,799","discount"=>"25% Off","img"=>"https://source.unsplash.com/400x400/?dress,3"],
    ["name"=>"Stylish Dress 4","desc"=>"Premium fabric & fit.","price"=>"₹1,999","discount"=>"40% Off","img"=>"https://source.unsplash.com/400x400/?dress,4"]
];
?>

<!-- ================= HERO SECTION ================= -->
<section class="py-5 text-center" style="background-color:#ff4d6d; color:white;">
    <div class="container">
        <h1 class="fw-bold">Hot Offers</h1>
        <p class="lead">Grab these exclusive discounts before they are gone!</p>
    </div>
</section>



<!-- ================= OFFERS GRID ================= -->
<section class="container py-4">
    <div class="row g-4">
        <?php foreach($offers as $p): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 position-relative">
                <span class="badge bg-danger position-absolute top-0 start-0 m-2"><?= $p['discount'] ?></span>
                <img src="<?= $p['img'] ?>" class="card-img-top" alt="<?= $p['name'] ?>">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= $p['name'] ?></h5>
                    <p class="text-muted"><?= $p['desc'] ?></p>
                    <p class="fw-bold"><?= $p['price'] ?></p>
                    <a href="shop.php" class="btn btn-primary me-2"><i class="fa fa-cart-plus me-1"></i>Add to Cart</a>
                    <a href="wishlist.php" class="btn btn-outline-danger"><i class="fa fa-heart"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
