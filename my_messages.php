<?php
ob_start();
require_once 'db_connect.php';
session_start();

$title_page = "My Messages";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$userEmail = $_SESSION['user']['email'];

// Fetch user's contact messages
$query = "SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$messages = $stmt->get_result();
$stmt->close();
?>

<style>
.messages-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 60px 0;
    color: white;
    margin-bottom: 40px;
}
.message-card {
    border: none;
    border-radius: 15px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.message-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    transform: translateY(-3px);
}
.message-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-bottom: 2px solid #dee2e6;
}
.message-body {
    padding: 25px;
    background: white;
}
.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}
.status-pending {
    background: #fff3cd;
    color: #856404;
}
.status-read {
    background: #cfe2ff;
    color: #084298;
}
.status-replied {
    background: #d1e7dd;
    color: #0f5132;
}
.reply-section {
    background: #f8f9fa;
    border-left: 4px solid #28a745;
    padding: 20px;
    margin-top: 20px;
    border-radius: 8px;
}
.empty-state {
    text-align: center;
    padding: 80px 20px;
}
.empty-state i {
    font-size: 5rem;
    color: #dee2e6;
    margin-bottom: 20px;
}
</style>

<!-- Hero Section -->
<div class="messages-hero">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-3">My Messages</h1>
        <p class="lead">View your contact messages and our responses</p>
    </div>
</div>

<div class="container pb-5">
    <?php if (mysqli_num_rows($messages) > 0): ?>
        <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
            <div class="message-card">
                <div class="message-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <i class="fa fa-tag text-primary me-2"></i>
                                <?= htmlspecialchars($msg['subject']) ?>
                            </h5>
                            <small class="text-muted">
                                <i class="fa fa-clock me-1"></i>
                                Sent on <?= date('F d, Y \a\t h:i A', strtotime($msg['created_at'])) ?>
                            </small>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $msg['status'] ?>">
                                <?php if ($msg['status'] === 'pending'): ?>
                                    <i class="fa fa-clock me-1"></i>Pending
                                <?php elseif ($msg['status'] === 'read'): ?>
                                    <i class="fa fa-eye me-1"></i>Read
                                <?php else: ?>
                                    <i class="fa fa-check-circle me-1"></i>Replied
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="message-body">
                    <h6 class="text-muted mb-2">Your Message:</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                    
                    <?php if ($msg['status'] === 'replied'): ?>
                        <div class="reply-section">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="fa fa-reply me-2"></i>Our Response
                            </h6>
                            <div class="alert alert-success mb-0">
                                <i class="fa fa-info-circle me-2"></i>
                                <strong>We have replied to your message!</strong>
                                <p class="mb-0 mt-2">
                                    Our response has been sent to your email address: 
                                    <strong><?= htmlspecialchars($msg['email']) ?></strong>
                                </p>
                                <p class="mb-0 mt-2">
                                    <small>Please check your inbox (and spam folder) for our reply.</small>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($msg['status'] === 'read'): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fa fa-eye me-2"></i>
                            <strong>Your message has been read by our team.</strong>
                            We'll respond to you shortly via email.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fa fa-clock me-2"></i>
                            <strong>Your message is pending review.</strong>
                            We'll get back to you within 24-48 hours.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fa fa-inbox"></i>
                    <h4 class="text-muted mb-3">No Messages Yet</h4>
                    <p class="text-muted mb-4">
                        You haven't sent any contact messages yet.
                    </p>
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fa fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Help Section -->
    <div class="card mt-4 border-0 bg-light">
        <div class="card-body text-center p-4">
            <h5 class="fw-bold mb-3">Need More Help?</h5>
            <p class="text-muted mb-3">
                If you have additional questions or concerns, feel free to send us another message.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="contact.php" class="btn btn-primary">
                    <i class="fa fa-envelope me-2"></i>Send New Message
                </a>
                <a href="faq.php" class="btn btn-outline-secondary">
                    <i class="fa fa-question-circle me-2"></i>View FAQs
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
