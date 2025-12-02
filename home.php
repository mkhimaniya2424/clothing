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
                "images/slider/slider1.jpg",
                "images/slider/slider2.jpg",
                // "images/slider/slider3.jpg"
            ];

            foreach($heroSlides as $i => $img):
            ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= $img ?>" class="d-block w-100" alt="Slider Image">
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
        require_once 'db_connect.php';
        // Fetch 4 random active products
        $featRes = $con->query("SELECT * FROM products WHERE status='active' ORDER BY RAND() LIMIT 4");
        if($featRes && $featRes->num_rows > 0):
            while($p = $featRes->fetch_assoc()): 
                $img = "https://via.placeholder.com/400";
                if(!empty($p['images'])) {
                    $decoded = json_decode($p['images'], true);
                    if($decoded && count($decoded) > 0) {
                        $img = $decoded[0];
                    }
                }
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0">
                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top rounded-top" alt="<?= htmlspecialchars($p['title']) ?>" style="height: 300px; object-fit: cover;">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                    <p class="fw-bold text-dark">₹<?= number_format($p['price'], 2) ?></p>
                    <a href="product_view.php?id=<?= $p['id'] ?>" class="btn btn-primary w-100">View Details</a>
                </div>
            </div>
        </div>
        <?php endwhile; 
        else: ?>
            <p class="text-center">No featured products found.</p>
        <?php endif; ?>
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
        <a href="offers.php" class="btn btn-light btn-lg">Grab Discount</a>
    </div>
</section>

<!-- ================= TOP BRANDS ================= -->
<section class="container py-5 text-center">
    <h2 class="fw-bold mb-4">Top Brands</h2>
    <div class="row g-4 justify-content-center">
        <?php 
        // Fetch Brands
        $brandRes = $con->query("SELECT * FROM brands ORDER BY name LIMIT 4");
        if($brandRes && $brandRes->num_rows > 0):
            while($b = $brandRes->fetch_assoc()):
                // Assuming no logo column in db_connect.php, using name as placeholder or static image
        ?>
        <div class="col-6 col-md-3">
            <div class="p-4 border rounded bg-light">
                <h5 class="mb-0"><?= htmlspecialchars($b['name']) ?></h5>
            </div>
        </div>
        <?php endwhile; 
        else: ?>
            <p>No brands to display.</p>
        <?php endif; ?>
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
