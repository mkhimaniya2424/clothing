<?php
ob_start();
require_once 'db_connect.php';
$title_page = "Offers & Promotions";

// Fetch active offers (no date restriction)
$offersSql = "SELECT id, title, description, discount_percentage, start_date, end_date 
              FROM offers 
              WHERE status='active'
              ORDER BY discount_percentage DESC";
$offersResult = $con->query($offersSql);

// Fetch active promotions (no date restriction)
$promoSql = "SELECT id, title, description, code, discount_percentage, discount_amount, start_date, end_date 
             FROM promotions 
             WHERE status='active'
             ORDER BY discount_percentage DESC, discount_amount DESC";
$promoResult = $con->query($promoSql);
?>

<style>
.offer-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}
.offer-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}
.offer-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: white;
    position: relative;
}
.offer-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}
.offer-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.2rem;
    box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
    z-index: 10;
}
.promo-badge {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
}
.offer-card-body {
    padding: 40px 30px;
    position: relative;
}
.offer-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 15px;
}
.offer-description {
    color: #718096;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 20px;
}
.offer-code {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: 2px;
    display: inline-block;
    margin: 15px 0;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.offer-validity {
    color: #e53e3e;
    font-weight: 600;
    font-size: 0.95rem;
    margin: 15px 0;
}
.offer-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 15px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.offer-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    color: white;
}
.offer-icon {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 20px;
}
.empty-state {
    padding: 100px 20px;
    text-align: center;
}
.empty-state i {
    font-size: 5rem;
    color: #cbd5e0;
    margin-bottom: 30px;
}
</style>

<!-- Hero Section -->
<section class="offer-hero text-white text-center position-relative">
    <div class="container position-relative" style="z-index: 1;">
        <h1 class="display-3 fw-bold mb-3">🎉 Hot Deals & Offers</h1>
        <p class="lead fs-4 mb-0">Exclusive discounts and promotions just for you!</p>
    </div>
</section>

<!-- Offers & Promotions Grid -->
<section class="container py-5">
    <?php if (($offersResult && $offersResult->num_rows > 0) || ($promoResult && $promoResult->num_rows > 0)): ?>
        
        <!-- Offers Section -->
        <?php if ($offersResult && $offersResult->num_rows > 0): ?>
            <div class="mb-5">
                <h2 class="text-center fw-bold mb-4">
                    <i class="fa fa-fire text-danger me-2"></i>Limited Time Offers
                </h2>
                <div class="row g-4">
                    <?php while($offer = $offersResult->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="offer-card shadow-lg">
                                <span class="offer-badge">
                                    <?= intval($offer['discount_percentage']) ?>% OFF
                                </span>
                                <div class="offer-card-body text-center">
                                    <div class="offer-icon">
                                        <i class="fa fa-gift"></i>
                                    </div>
                                    <h3 class="offer-title"><?= htmlspecialchars($offer['title']) ?></h3>
                                    <p class="offer-description"><?= nl2br(htmlspecialchars($offer['description'])) ?></p>
                                    <div class="offer-validity">
                                        <i class="fa fa-clock me-2"></i>Valid until <?= date("d M Y", strtotime($offer['end_date'])) ?>
                                    </div>
                                    <a href="shop.php" class="offer-btn mt-3">
                                        <i class="fa fa-shopping-bag me-2"></i>Shop Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Promotions Section -->
        <?php if ($promoResult && $promoResult->num_rows > 0): ?>
            <div class="mb-5">
                <h2 class="text-center fw-bold mb-4">
                    <i class="fa fa-tags text-primary me-2"></i>Promo Codes
                </h2>
                <div class="row g-4">
                    <?php while($promo = $promoResult->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="offer-card shadow-lg">
                                <span class="offer-badge promo-badge">
                                    <?php if ($promo['discount_percentage'] > 0): ?>
                                        <?= intval($promo['discount_percentage']) ?>% OFF
                                    <?php elseif ($promo['discount_amount'] > 0): ?>
                                        ₹<?= number_format($promo['discount_amount'], 0) ?> OFF
                                    <?php endif; ?>
                                </span>
                                <div class="offer-card-body text-center">
                                    <div class="offer-icon">
                                        <i class="fa fa-ticket-alt"></i>
                                    </div>
                                    <h3 class="offer-title"><?= htmlspecialchars($promo['title']) ?></h3>
                                    <p class="offer-description"><?= nl2br(htmlspecialchars($promo['description'])) ?></p>
                                    
                                    <?php if (!empty($promo['code'])): ?>
                                        <div class="offer-code">
                                            <?= htmlspecialchars($promo['code']) ?>
                                        </div>
                                        <small class="text-muted d-block">Copy this code at checkout</small>
                                    <?php endif; ?>
                                    
                                    <div class="offer-validity">
                                        <i class="fa fa-clock me-2"></i>Valid until <?= date("d M Y", strtotime($promo['end_date'])) ?>
                                    </div>
                                    <a href="shop.php" class="offer-btn mt-3">
                                        <i class="fa fa-shopping-bag me-2"></i>Shop Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fa fa-inbox"></i>
            <h3 class="fw-bold text-muted mb-3">No Active Offers Right Now</h3>
            <p class="text-muted mb-4">Check back soon for exciting deals and promotions!</p>
            <a href="shop.php" class="offer-btn">
                <i class="fa fa-shopping-bag me-2"></i>Browse Products
            </a>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
