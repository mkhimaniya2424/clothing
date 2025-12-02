<?php
ob_start();
$title_page = "Privacy Policy";
?>

<section class="container py-5">
    <h1 class="fw-bold mb-4">Privacy Policy</h1>
    <p class="text-muted">Last updated: <?= date("F d, Y") ?></p>
    
    <h4 class="mt-4">1. Information We Collect</h4>
    <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us. This may include your name, email address, phone number, and shipping address.</p>
    
    <h4 class="mt-4">2. How We Use Your Information</h4>
    <p>We use the information we collect to process your orders, communicate with you, and improve our services. We do not sell your personal data to third parties.</p>
    
    <h4 class="mt-4">3. Security</h4>
    <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access or disclosure.</p>
    
    <h4 class="mt-4">4. Cookies</h4>
    <p>We use cookies to enhance your browsing experience and analyze site traffic. You can control cookie preferences through your browser settings.</p>
    
    <h4 class="mt-4">5. Contact Us</h4>
    <p>If you have any questions about this Privacy Policy, please contact us at support@clothingbrand.com.</p>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
