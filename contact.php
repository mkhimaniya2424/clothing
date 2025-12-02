<?php
ob_start();
$title_page = "Contact Us";
?>

<section class="container py-5">
    <h1 class="text-center fw-bold mb-5" style="color:#2e2a2fff;">Contact Us</h1>

    <div class="row g-4">
        <!-- Contact Info -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fa fa-map-marker-alt fa-2x mb-3" style="color:#2e2a2fff;"></i>
                <h5 class="fw-bold" style="color:#2e2a2fff;">Address</h5>
                <p class="text-muted">123 Fashion Street, Style City, Country</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fa fa-phone fa-2x mb-3" style="color:#2e2a2fff;"></i>
                <h5 class="fw-bold" style="color:#2e2a2fff;">Phone</h5>
                <p class="text-muted">+91 98765 43210</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fa fa-envelope fa-2x mb-3" style="color:#2e2a2fff;"></i>
                <h5 class="fw-bold" style="color:#2e2a2fff;">Email</h5>
                <p class="text-muted">support@clothingbrand.com</p>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="row mt-5 justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-4" style="color:#2e2a2fff;">Send Us a Message</h3>
                <form action="contact_submit.php" method="post">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="message" rows="5" class="form-control" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn" style="background-color:#2e2a2fff; color:white;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
