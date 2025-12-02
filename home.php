<?php
ob_start();
$title_page = "Home";
?>

<!-- ================= HERO SECTION ================= -->
<section class="hero-section position-relative mb-5">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $heroSlides = [
                ["img"=>"https://source.unsplash.com/1600x600/?fashion,women","title"=>"New Season, New Style","desc"=>"Discover the latest trends for Women’s Wear","btnText"=>"Shop Now","btnLink"=>"shop.php"],
                ["img"=>"https://source.unsplash.com/1600x600/?clothes,shopping","title"=>"Flat 40% Off","desc"=>"Grab exclusive discounts on top brands","btnText"=>"Grab Offer","btnLink"=>"offers.php"],
                ["img"=>"https://source.unsplash.com/1600x600/?style,women","title"=>"Luxury Meets Comfort","desc"=>"Explore premium women's wear collections","btnText"=>"Explore","btnLink"=>"shop.php"]
            ];
            foreach($heroSlides as $i => $slide):
            ?>
            <div class="carousel-item <?= $i===0?'active':'' ?>">
                <img src="<?= $slide['img'] ?>" class="d-block w-100" alt="<?= htmlspecialchars($slide['title']) ?>">
                <div class="carousel-caption d-none d-md-block text-start">
                    <h1 class="fw-bold text-white"><?= $slide['title'] ?></h1>
                    <p><?= $slide['desc'] ?></p>
                    <a href="<?= $slide['btnLink'] ?>" class="btn btn-light btn-lg"><?= $slide['btnText'] ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<!-- ================= FEATURED COLLECTIONS ================= -->
<section class="container py-5">
    <h2 class="text-center fw-bold mb-4">Featured Collections</h2>
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
            <div class="card h-100 shadow-sm border-0">
                <img src="<?= $p['img'] ?>" class="card-img-top rounded-top" alt="<?= $p['name'] ?>">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= $p['name'] ?></h5>
                    <p class="card-text text-muted"><?= $p['desc'] ?></p>
                    <p class="fw-bold text-dark"><?= $p['price'] ?></p>
                    <a href="shop.php" class="btn btn-primary w-100">Shop Now</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ================= NEW ARRIVALS ================= -->
<section class="bg-light py-5 text-center">
    <div class="container">
        <h2 class="fw-bold mb-3">New Arrivals</h2>
        <p class="mb-4 text-muted">Check out the latest additions to our collection</p>
        <a href="shop.php" class="btn btn-outline-primary btn-lg">Explore Now</a>
    </div>
</section>

<!-- ================= SEASONAL SALE ================= -->
<section class="py-5 text-white text-center" style="background-color:#808080;">
    <div class="container">
        <h2 class="fw-bold mb-3">Seasonal Sale</h2>
        <p class="mb-4">Up to 50% off on selected collections!</p>
        <a href="offers/offers.php" class="btn btn-light btn-lg">Grab Discount</a>
    </div>
</section>

<!-- ================= TOP BRANDS ================= -->
<section class="container py-5 text-center">
    <h2 class="fw-bold mb-4">Top Brands</h2>
    <div class="row g-4 justify-content-center">
        <?php 
        $brands = [
            "https://source.unsplash.com/150x100/?brand,1",
            "https://source.unsplash.com/150x100/?brand,2",
            "https://source.unsplash.com/150x100/?brand,3",
            "https://source.unsplash.com/150x100/?brand,4"
        ];
        foreach($brands as $b): ?>
        <div class="col-6 col-md-3">
            <img src="<?= $b ?>" class="img-fluid rounded shadow-sm" alt="Brand Logo">
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ================= ABOUT ================= -->
<section class="bg-light py-5 text-center">
    <div class="container">
        <h2 class="fw-bold mb-3">About Clothing Brand</h2>
        <p class="mb-4 text-muted">We provide trendy, elegant, and affordable clothing for women. Quality and style are our top priorities!</p>
        <a href="about/about.php" class="btn btn-primary btn-lg">Learn More</a>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
