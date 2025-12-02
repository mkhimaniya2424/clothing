<?php
ob_start();
require_once 'db_connect.php';
require_once 'email_helper.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if ($name && $email && $message) {
        // Save to DB
        $stmt = $con->prepare("INSERT INTO messages (from_user, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            // Send Email to Admin
            $adminSubject = "New Contact Message from $name";
            $body = "<p><strong>Name:</strong> $name</p>
                     <p><strong>Email:</strong> $email</p>
                     <p><strong>Subject:</strong> $subject</p>
                     <p><strong>Message:</strong><br>$message</p>";
            sendEmail('clothingsite60@gmail.com', $adminSubject, $body);
            
            $msg = "Message sent successfully! We will get back to you soon.";
        } else {
            $msg = "Error sending message.";
        }
        $stmt->close();
    } else {
        $msg = "All fields are required.";
    }
}
?>

<section class="container py-5">
    <h1 class="text-center fw-bold mb-5">Contact Us</h1>

    <?php if ($msg): ?>
        <div class="alert alert-info text-center mb-4"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Contact Info -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fa fa-map-marker-alt fa-2x mb-3 text-primary"></i>
                <h5 class="fw-bold">Address</h5>
                <p class="text-muted">123 Fashion Street, Style City, Country</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fa fa-phone fa-2x mb-3 text-primary"></i>
                <h5 class="fw-bold">Phone</h5>
                <p class="text-muted">+91 98765 43210</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fa fa-envelope fa-2x mb-3 text-primary"></i>
                <h5 class="fw-bold">Email</h5>
                <p class="text-muted">support@clothingbrand.com</p>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="row mt-5 justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-4">Send Us a Message</h3>
                <form method="post">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="message" rows="5" class="form-control" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
