<?php
ob_start();
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';
$title_page = "Home";
?>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 100px 0;
    color: white;
    position: relative;
    overflow: hidden;
}
.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}
.hero-content {
    position: relative;
    z-index: 1;
}
.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}
.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 30px;
    opacity: 0.95;
}
.hero-btn {
    padding: 15px 40px;
    font-size: 1.1rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.hero-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* Product Cards */
.product-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}
.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}
.product-card img {
    transition: transform 0.5s ease;
}
.product-card:hover img {
    transform: scale(1.1);
}
.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    z-index: 10;
}

/* Category Cards */
.category-card {
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    height: 300px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.category-card:hover {
    transform: scale(1.05);
}
.category-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 30px 20px;
    color: white;
}

/* Brand Logos */
.brand-logo-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    transition: all 0.3s ease;
    border: 2px solid #f0f0f0;
}
.brand-logo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-color: #667eea;
}

/* Stats Section */
.stats-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
}
.stat-item {
    text-align: center;
}
.stat-number {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 10px;
}
.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Promo Banner */
.promo-banner {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: 20px;
    padding: 50px;
    color: white;
    text-align: center;
    margin: 50px 0;
}

/* Section Titles */
.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 15px;
    position: relative;
    display: inline-block;
}
.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start">
                <h1 class="hero-title">Discover Your Style</h1>
                <p class="hero-subtitle">Explore our exclusive collection of trendy and elegant clothing</p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                    <a href="shop.php" class="btn btn-light btn-lg hero-btn">
                        <i class="fa fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="offers.php" class="btn btn-outline-light btn-lg hero-btn">
                        <i class="fa fa-tags me-2"></i>View Offers
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <i class="fa fa-tshirt" style="font-size: 15rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">Featured Collections</h2>
        <p class="text-muted mt-4">Handpicked items just for you</p>
    </div>
    
    <div class="row g-4">
        <?php 
        // Fetch 8 random active products
        $featRes = $con->query("SELECT * FROM products WHERE status='active' ORDER BY RAND() LIMIT 8");

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
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card product-card h-100 shadow-sm">
                <span class="product-badge">New</span>
                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['title']) ?>" style="height: 250px; object-fit: cover;">
                <div class="card-body text-center">
                    <h6 class="card-title text-truncate"><?= htmlspecialchars($p['title']) ?></h6>
                    <p class="fw-bold text-primary mb-3">₹<?= number_format($p['price'], 2) ?></p>
                    <a href="product_view.php?id=<?= $p['id'] ?>" class="btn btn-dark btn-sm w-100">
                        <i class="fa fa-eye me-2"></i>View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; 
        else: ?>
            <p class="text-center">No featured products found.</p>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="shop.php" class="btn btn-outline-primary btn-lg">
            View All Products <i class="fa fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- Promo Banner -->
<div class="container">
    <div class="promo-banner">
        <h2 class="fw-bold mb-3">🎉 Seasonal Sale is Live!</h2>
        <p class="fs-5 mb-4">Get up to 50% off on selected collections</p>
        <a href="offers.php" class="btn btn-light btn-lg">
            <i class="fa fa-gift me-2"></i>Grab Deals Now
        </a>
    </div>
</div>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-6 col-md-3 stat-item mb-4 mb-md-0">
                <div class="stat-number">
                    <?php 
                    $productCount = $con->query("SELECT COUNT(*) as count FROM products WHERE status='active'")->fetch_assoc();
                    echo $productCount['count'];
                    ?>+
                </div>
                <div class="stat-label">Products</div>
            </div>
            <div class="col-6 col-md-3 stat-item mb-4 mb-md-0">
                <div class="stat-number">
                    <?php 
                    $userCount = $con->query("SELECT COUNT(*) as count FROM users WHERE status='active'")->fetch_assoc();
                    echo $userCount['count'];
                    ?>+
                </div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-number">
                    <?php 
                    $brandCount = $con->query("SELECT COUNT(*) as count FROM brands")->fetch_assoc();
                    echo $brandCount['count'];
                    ?>+
                </div>
                <div class="stat-label">Top Brands</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Quality Assured</div>
            </div>
        </div>
    </div>
</section>

<!-- Top Brands -->
<section class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">Top Brands</h2>
        <p class="text-muted mt-4">Shop from your favorite brands</p>
    </div>
    
    <div class="row g-4 justify-content-center">
        <?php 
        // Fetch Brands
        $brandRes = $con->query("SELECT * FROM brands ORDER BY name LIMIT 6");

        if($brandRes && $brandRes->num_rows > 0):
            while($b = $brandRes->fetch_assoc()):
                $logo = !empty($b['logo']) ? htmlspecialchars($b['logo']) : 'https://via.placeholder.com/150x80?text='.urlencode($b['name']);
        ?>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="brand-logo-card text-center">
                <img src="<?= $logo ?>" alt="<?= htmlspecialchars($b['name']) ?>" class="img-fluid" style="max-height:60px; object-fit:contain;">
            </div>
        </div>
        <?php endwhile; 
        else: ?>
            <p>No brands to display.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Choose Us</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fa fa-shipping-fast fa-3x text-primary"></i>
                </div>
                <h5>Free Shipping</h5>
                <p class="text-muted">On all orders above ₹999</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fa fa-lock fa-3x text-primary"></i>
                </div>
                <h5>Secure Payment</h5>
                <p class="text-muted">100% secure transactions</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fa fa-headset fa-3x text-primary"></i>
                </div>
                <h5>24/7 Support</h5>
                <p class="text-muted">Dedicated customer service</p>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
