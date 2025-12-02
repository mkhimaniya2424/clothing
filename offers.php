<?php
ob_start();
require_once 'db_connect.php';
$title_page = "Offers";

// Fetch Active Offers
$currentDate = date('Y-m-d');
$sql = "SELECT * FROM offers WHERE status='active' AND start_date <= '$currentDate' AND end_date >= '$currentDate'";
$result = $con->query($sql);
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
    <?php if ($result && $result->num_rows > 0): ?>
    <div class="row g-4">
        <?php while($offer = $result->fetch_assoc()): ?>
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
    </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <h4>No active offers at the moment.</h4>
            <p>Check back later or browse our <a href="shop.php">Shop</a> for great prices!</p>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
