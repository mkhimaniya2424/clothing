<?php
ob_start();
$title_page = "Returns & Exchanges";
?>

<section class="container py-5">
    <h1 class="fw-bold mb-4">Returns & Exchanges</h1>
    
    <h4>1. Return Policy</h4>
    <p>We accept returns within 7 days of delivery. Items must be unused, unwashed, and with original tags attached.</p>
    
    <h4>2. How to Return</h4>
    <p>To initiate a return, please contact our support team at support@clothingbrand.com with your order ID.</p>
    
    <h4>3. Refunds</h4>
    <p>Refunds will be processed to the original payment method within 5-7 business days after we receive the returned item.</p>
    
    <h4>4. Exchanges</h4>
    <p>We offer size exchanges subject to availability. Please contact support to request an exchange.</p>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
