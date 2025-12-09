<?php
ob_start();
require_once 'db_connect.php';
require_once 'email_helper.php';

$title_page = "Contact Us";
$msg = '';
$msgType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if ($name && $email && $message) {
        // Save to contact_messages table
        $stmt = $con->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            // Send Email to Admin
            $adminSubject = "New Contact Message from $name";
            $body = "<p><strong>Name:</strong> $name</p>
                     <p><strong>Email:</strong> $email</p>
                     <p><strong>Phone:</strong> $phone</p>
                     <p><strong>Subject:</strong> $subject</p>
                     <p><strong>Message:</strong><br>$message</p>";
            sendEmail('clothingsite60@gmail.com', $adminSubject, $body);
            
            $msg = "Message sent successfully! We will get back to you soon.";
            $msgType = 'success';
        } else {
            $msg = "Error sending message. Please try again.";
            $msgType = 'danger';
        }
        $stmt->close();
    } else {
        $msg = "Please fill in all required fields.";
        $msgType = 'warning';
    }
}
?>

<style>
.contact-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 80px 0;
    color: white;
    margin-bottom: 50px;
}
.contact-info-card {
    border: none;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    transition: all 0.3s ease;
    background: white;
    height: 100%;
}
.contact-info-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
.contact-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 2rem;
}
.contact-form-card {
    border: none;
    border-radius: 20px;
    padding: 50px;
    background: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.form-control, .form-select {
    border-radius: 10px;
    padding: 15px 20px;
    border: 2px solid #e2e8f0;
}
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}
.submit-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}
.map-container {
    border-radius: 20px;
    overflow: hidden;
    height: 400px;
    margin-top: 50px;
}
</style>

<!-- Hero Section -->
<div class="contact-hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Get In Touch</h1>
        <p class="lead">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>
</div>

<div class="container pb-5">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
            <i class="fa fa-<?= $msgType === 'success' ? 'check-circle' : ($msgType === 'danger' ? 'exclamation-circle' : 'info-circle') ?> me-2"></i>
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Contact Info Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="contact-info-card shadow-sm">
                <div class="contact-icon">
                    <i class="fa fa-map-marker-alt"></i>
                </div>
                <h5 class="fw-bold mb-3">Visit Us</h5>
                <p class="text-muted mb-0">123 Fashion Street<br>Style City, SC 12345<br>India</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="contact-info-card shadow-sm">
                <div class="contact-icon">
                    <i class="fa fa-phone"></i>
                </div>
                <h5 class="fw-bold mb-3">Call Us</h5>
                <p class="text-muted mb-2"><a href="tel:+919876543210" class="text-decoration-none text-muted">+91 98765 43210</a></p>
                <p class="text-muted mb-0"><small>Mon-Sat: 9AM - 6PM</small></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="contact-info-card shadow-sm">
                <div class="contact-icon">
                    <i class="fa fa-envelope"></i>
                </div>
                <h5 class="fw-bold mb-3">Email Us</h5>
                <p class="text-muted mb-2"><a href="mailto:support@clothingbrand.com" class="text-decoration-none text-muted">support@clothingbrand.com</a></p>
                <p class="text-muted mb-0"><small>We reply within 24 hours</small></p>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="contact-form-card">
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-2">Send Us a Message</h2>
                    <p class="text-muted">Fill out the form below and we'll get back to you shortly</p>
                </div>
                
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" placeholder="How can we help?" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                            <textarea name="message" rows="6" class="form-control" placeholder="Tell us more about your inquiry..." required></textarea>
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="submit-btn btn btn-primary">
                                <i class="fa fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Map Section (Optional) -->
    <div class="map-container shadow-lg">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3671.9876543210!2d72.5714!3d23.0225!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDAxJzIxLjAiTiA3MsKwMzQnMTcuMCJF!5e0!3m2!1sen!2sin!4v1234567890"
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>

    <!-- FAQ Section -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <h3 class="fw-bold mb-4">Frequently Asked Questions</h3>
            <p class="text-muted mb-4">Can't find what you're looking for? Check out our <a href="faq.php">FAQ page</a> or contact us directly.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="faq.php" class="btn btn-outline-primary">
                    <i class="fa fa-question-circle me-2"></i>View FAQs
                </a>
                <a href="returns.php" class="btn btn-outline-secondary">
                    <i class="fa fa-undo me-2"></i>Returns Policy
                </a>
                <a href="shipping_policy.php" class="btn btn-outline-secondary">
                    <i class="fa fa-shipping-fast me-2"></i>Shipping Info
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
