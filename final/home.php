<?php
ob_start();
$title_page = "Home";
?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>

    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="https://source.unsplash.com/1600x600/?fashion,women" class="d-block w-100" alt="Fashion 1">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="fw-bold text-white">New Season, New Style</h1>
                <p>Discover the latest trends for Women’s Wear</p>
                <a href="shop.php" class="btn btn-light btn-lg">Shop Now</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://source.unsplash.com/1600x600/?clothes,shopping" class="d-block w-100" alt="Fashion 2">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="fw-bold text-white">Flat 40% Off</h1>
                <p>Grab exclusive discounts on top brands</p>
                <a href="offers.php" class="btn btn-light btn-lg">Grab Offer</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://source.unsplash.com/1600x600/?style,women" class="d-block w-100" alt="Fashion 3">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="fw-bold text-white">Luxury Meets Comfort</h1>
                <p>Explore premium women's wear collections</p>
            </div>
        </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<!-- Featured Collections -->
<section class="container py-5">
    <h2 class="text-center fw-bold mb-4" style="color:var(--brand-main);">
        <i class="fa-solid fa-star me-2" style="color:var(--brand-main);"></i> Featured Collections <i class="fa-solid fa-star me-2" style="color:var(--brand-main);"></i>
    </h2>

    <div class="row g-4">
        <?php 
        $products = [
            ["name"=>"Stylish Dress 1", "desc"=>"Elegant and comfy outfit.", "price"=>"₹1,499", "img"=>"https://source.unsplash.com/400x400/?dress,1"],
            ["name"=>"Stylish Dress 2", "desc"=>"Trendy & chic design.", "price"=>"₹1,299", "img"=>"https://source.unsplash.com/400x400/?dress,2"],
            ["name"=>"Stylish Dress 3", "desc"=>"Perfect for casual wear.", "price"=>"₹1,799", "img"=>"https://source.unsplash.com/400x400/?dress,3"],
            ["name"=>"Stylish Dress 4", "desc"=>"Premium fabric & fit.", "price"=>"₹1,999", "img"=>"https://source.unsplash.com/400x400/?dress,4"]
        ];
        foreach($products as $p): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100 hover-shadow">
                <img src="<?= $p['img'] ?>" class="card-img-top" alt="<?= $p['name'] ?>">
                <div class="card-body text-center">
                    <h5 class="card-title fw-bold" style="color:var(--brand-main);"><?= $p['name'] ?></h5>
                    <p class="card-text text-muted"><?= $p['desc'] ?></p>
                    <p class="fw-bold text-dark"><?= $p['price'] ?></p>
                    <a href="shop.php" class="btn text-white" style="background-color:var(--brand-main);">Shop Now</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- New Arrivals -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-4" style="color:var(--brand-dark);">New Arrivals</h2>
        <p class="mb-4 text-dark">Check out the latest additions to our collection</p>
        <a href="shop.php" class="btn btn-outline-primary btn-lg">Explore Now</a>
    </div>
</section>

<!-- Discount Section -->
<section class="py-5 text-white" style="background-color:var(--brand-main);">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Seasonal Sale</h2>
        <p class="mb-4">Up to 50% off on selected collections!</p>
        <a href="offers.php" class="btn btn-light btn-lg">Grab Discount</a>
    </div>
</section>

<!-- Top Brands -->
<section class="container py-5 text-center">
    <h2 class="fw-bold mb-4" style="color:var(--brand-dark);">Top Brands</h2>
    <div class="row g-4 justify-content-center">
        <?php 
        $brands = ["https://source.unsplash.com/150x100/?brand,1",
                   "https://source.unsplash.com/150x100/?brand,2",
                   "https://source.unsplash.com/150x100/?brand,3",
                   "https://source.unsplash.com/150x100/?brand,4"];
        foreach($brands as $b): ?>
        <div class="col-6 col-md-3">
            <img src="<?= $b ?>" class="img-fluid" alt="Brand Logo">
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- About Us -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-3" style="color:var(--brand-dark);">About Clothing Brand</h2>
        <p class="mb-4 text-dark">We provide trendy, elegant, and affordable clothing for women. Quality and style are our top priorities!</p>
        <a href="about/about.php" class="btn" style="background-color:var(--brand-main); color:#fff;">Learn More</a>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="py-5 text-white" style="background-color:var(--brand-dark);">
    <div class="container text-center">
        <h4 class="mb-3">Subscribe to Our Newsletter</h4>
        <p class="mb-4">Get updates on new arrivals, discounts, and exclusive offers.</p>
        <form class="d-flex justify-content-center" action="" method="post">
            <input type="email" name="email" class="form-control w-50 me-2" placeholder="Enter your email" required>
            <button type="submit" class="btn" style="background-color:var(--brand-main); color:#fff;">Subscribe</button>
        </form>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
