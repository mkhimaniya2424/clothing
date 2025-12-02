<?php
ob_start();
$title_page = "Shipping Policy";
?>

<section class="container py-5">
    <h1 class="fw-bold mb-4">Shipping Policy</h1>
    
    <h4>1. Processing Time</h4>
    <p>All orders are processed within 1-2 business days.</p>
    
    <h4>2. Shipping Rates</h4>
    <p>We offer free shipping on orders over ₹999. For orders under ₹999, a standard shipping fee of ₹50 applies.</p>
    
    <h4>3. Delivery Estimates</h4>
    <p>Standard delivery takes 3-5 business days. Express delivery (available in select cities) takes 1-2 business days.</p>
    
    <h4>4. International Shipping</h4>
    <p>Currently, we only ship within India.</p>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
