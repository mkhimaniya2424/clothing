<?php
ob_start();
$title_page = "About Us";
?>

<section class="container py-5 text-center">
    <h1 class="fw-bold mb-4" style="color:#2e2a2fff;">About Clothing Brand</h1>
    <p class="mb-4" style="color:#555;">
        Clothing Brand provides trendy, elegant, and affordable clothing for men, women, and kids. 
        Our mission is to deliver high-quality fashion that combines style, comfort, and value.
    </p>

    <div class="row g-4 mt-5">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fa-solid fa-shirt fa-3x mb-3" style="color:#2e2a2fff;"></i>
                    <h5 class="card-title fw-bold" style="color:#2e2a2fff;">Trendy Collections</h5>
                    <p class="card-text text-muted">Stay ahead with our latest seasonal collections designed for all ages.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fa-solid fa-star fa-3x mb-3" style="color:#2e2a2fff;"></i>
                    <h5 class="card-title fw-bold" style="color:#2e2a2fff;">Premium Quality</h5>
                    <p class="card-text text-muted">We focus on quality fabrics, perfect fits, and lasting comfort.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fa-solid fa-hand-holding-dollar fa-3x mb-3" style="color:#2e2a2fff;"></i>
                    <h5 class="card-title fw-bold" style="color:#2e2a2fff;">Affordable Prices</h5>
                    <p class="card-text text-muted">High-quality fashion that doesn’t break your budget.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-light py-5 text-center">
    <div class="container">
        <h2 class="fw-bold mb-3" style="color:#2e2a2fff;">Our Story</h2>
        <p class="text-muted mb-4">
            Since our inception, Clothing Brand has been dedicated to bringing the best fashion experience to our customers. 
            With a passion for design and quality, we create collections that inspire confidence and style.
        </p>
        <a href="shop.php" class="btn" style="background-color:#2e2a2fff; color:white;">Shop Now</a>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
