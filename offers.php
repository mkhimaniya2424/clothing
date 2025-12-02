<?php
ob_start();
require_once 'db_connect.php';
$title_page = "Offers";

// Current date
$currentDate = date('Y-m-d');

// Fetch active offers
$offersSql = "SELECT id, title, description, discount_percentage, start_date, end_date 
              FROM offers 
              WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate'";
$offersResult = $con->query($offersSql);

// Fetch active promotions
$promoSql = "SELECT id, title, description, code, discount_percentage, discount_amount, start_date, end_date 
             FROM promotions 
             WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate'";
$promoResult = $con->query($promoSql);
?>

<!-- ================= HERO SECTION ================= -->
<section class="py-5 text-center" style="background-color:#ff4d6d; color:white;">
    <div class="container">
        <h1 class="fw-bold">Hot Offers & Promotions</h1>
        <p class="lead">Grab these exclusive deals before they are gone!</p>
    </div>
</section>

<!-- ================= OFFERS & PROMOTIONS GRID ================= -->
<section class="container py-4">
    <div class="row g-4">
        <?php if (($offersResult && $offersResult->num_rows > 0) || ($promoResult && $promoResult->num_rows > 0)): ?>

            <!-- Display Offers -->
            <?php if ($offersResult && $offersResult->num_rows > 0): ?>
                <?php while($offer = $offersResult->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 position-relative">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <span class="badge bg-danger fs-6"><?= intval($offer['discount_percentage']) ?>% OFF</span>
                            </div>
                            <div class="card-body text-center">
                                <h3 class="card-title fw-bold text-dark"><?= htmlspecialchars($offer['title']) ?></h3>
                                <p class="text-muted my-3"><?= nl2br(htmlspecialchars($offer['description'])) ?></p>
                                <p class="text-danger fw-bold">Valid until: <?= date("d M Y", strtotime($offer['end_date'])) ?></p>
                                <a href="shop.php" class="btn btn-dark w-100">Shop Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <!-- Display Promotions -->
            <?php if ($promoResult && $promoResult->num_rows > 0): ?>
                <?php while($promo = $promoResult->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 position-relative">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <?php if ($promo['discount_percentage'] > 0): ?>
                                    <span class="badge bg-danger fs-6"><?= intval($promo['discount_percentage']) ?>% OFF</span>
                                <?php elseif ($promo['discount_amount'] > 0): ?>
                                    <span class="badge bg-danger fs-6">₹<?= number_format($promo['discount_amount'], 2) ?> OFF</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center">
                                <h3 class="card-title fw-bold text-dark"><?= htmlspecialchars($promo['title']) ?></h3>
                                <p class="text-muted my-3"><?= nl2br(htmlspecialchars($promo['description'])) ?></p>
                                <?php if (!empty($promo['code'])): ?>
                                    <p class="fw-bold">Use Code: <span class="text-primary"><?= htmlspecialchars($promo['code']) ?></span></p>
                                <?php endif; ?>
                                <p class="text-danger fw-bold">Valid until: <?= date("d M Y", strtotime($promo['end_date'])) ?></p>
                                <a href="shop.php" class="btn btn-dark w-100">Shop Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-info text-center">
                <h4>No active offers or promotions at the moment.</h4>
                <p>Check back later or browse our <a href="shop.php">Shop</a> for great deals!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
