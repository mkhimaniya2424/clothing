<?php
ob_start();
session_start();
require_once 'db_connect.php';
require_once 'session_helper.php';
$title_page = "Returns & Exchanges";

// Check if user is logged in for return request
$isLoggedIn = isset($_SESSION['user']);
$user_id = $isLoggedIn ? $_SESSION['user']['id'] : null;

// Handle return request submission
$requestMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    if (!$isLoggedIn) {
        $requestMessage = 'Please login to submit a return request.';
    } else {
        $order_id = intval($_POST['order_id']);
        $reason = $con->real_escape_string($_POST['reason']);
        $comments = $con->real_escape_string($_POST['comments']);
        
        // Verify order belongs to user
        $orderCheck = $con->query("SELECT id FROM orders WHERE id=$order_id AND user_id=$user_id");
        
        if ($orderCheck && $orderCheck->num_rows > 0) {
            // Check if return_requests table exists, create if not
            $tableCheck = $con->query("SHOW TABLES LIKE 'return_requests'");
            
            if ($tableCheck->num_rows == 0) {
                // Create the table
                $createTable = "CREATE TABLE return_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    user_id INT NOT NULL,
                    reason VARCHAR(100) NOT NULL,
                    comments TEXT,
                    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_order_id (order_id),
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status)
                )";
                $con->query($createTable);
            }
            
            // Insert return request into database
            $sql = "INSERT INTO return_requests (order_id, user_id, reason, comments, status, created_at) 
                    VALUES ($order_id, $user_id, '$reason', '$comments', 'pending', NOW())";
            
            if ($con->query($sql)) {
                $requestMessage = 'success';
            } else {
                $requestMessage = 'Error submitting request: ' . $con->error;
            }
        } else {
            $requestMessage = 'Invalid order ID.';
        }
    }
}

// Fetch user's recent orders if logged in
$userOrders = [];
if ($isLoggedIn) {
    $ordersQuery = "SELECT id, created_at, final_amount, order_status 
                    FROM orders 
                    WHERE user_id = $user_id 
                    AND order_status IN ('delivered', 'shipped')
                    ORDER BY created_at DESC 
                    LIMIT 10";
    $ordersResult = $con->query($ordersQuery);
    if ($ordersResult) {
        while ($row = $ordersResult->fetch_assoc()) {
            $userOrders[] = $row;
        }
    }
}
?>

<style>
.returns-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 60px 0;
    color: white;
    margin-bottom: 50px;
}
.policy-card {
    border: none;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    border-left: 4px solid #667eea;
}
.policy-card:hover {
    transform: translateX(10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.policy-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 20px;
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #667eea;
}
.timeline-item {
    position: relative;
    padding-bottom: 30px;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -35px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #667eea;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #f0f0f0;
}
.return-form-card {
    background: #f8f9fa;
    border-radius: 20px;
    padding: 40px;
}
.faq-item {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.faq-item:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<!-- Hero Section -->
<div class="returns-hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Returns & Exchanges</h1>
        <p class="lead">We want you to love what you buy. If you're not satisfied, we're here to help!</p>
    </div>
</div>

<div class="container pb-5">
    <?php if ($requestMessage === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i>Return request submitted successfully! Our team will contact you soon.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($requestMessage && $requestMessage !== 'success'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fa fa-exclamation-triangle me-2"></i><?= htmlspecialchars($requestMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Return Policy -->
        <div class="col-lg-8 mb-4">
            <h2 class="fw-bold mb-4">Our Return Policy</h2>
            
            <div class="policy-card shadow-sm">
                <div class="policy-icon">
                    <i class="fa fa-calendar-check"></i>
                </div>
                <h4>30-Day Return Window</h4>
                <p class="text-muted mb-0">You can return most items within 30 days of delivery for a full refund. Items must be in original condition with tags attached.</p>
            </div>

            <div class="policy-card shadow-sm">
                <div class="policy-icon">
                    <i class="fa fa-shipping-fast"></i>
                </div>
                <h4>Free Return Shipping</h4>
                <p class="text-muted mb-0">We offer free return shipping on all orders. Just request a return label through your account or contact our support team.</p>
            </div>

            <div class="policy-card shadow-sm">
                <div class="policy-icon">
                    <i class="fa fa-money-bill-wave"></i>
                </div>
                <h4>Quick Refunds</h4>
                <p class="text-muted mb-0">Refunds are processed within 5-7 business days after we receive your return. Money will be credited to your original payment method.</p>
            </div>

            <div class="policy-card shadow-sm">
                <div class="policy-icon">
                    <i class="fa fa-exchange-alt"></i>
                </div>
                <h4>Easy Exchanges</h4>
                <p class="text-muted mb-0">Want a different size or color? We offer hassle-free exchanges subject to availability. Contact us to arrange an exchange.</p>
            </div>

            <!-- Return Process -->
            <h3 class="fw-bold mt-5 mb-4">How to Return</h3>
            <div class="card border-0 shadow-sm p-4">
                <div class="timeline">
                    <div class="timeline-item">
                        <h5>Step 1: Request Return</h5>
                        <p class="text-muted">Submit a return request through the form below or contact our support team at <strong>support@clothingstore.com</strong></p>
                    </div>
                    <div class="timeline-item">
                        <h5>Step 2: Pack Your Item</h5>
                        <p class="text-muted">Pack the item securely in its original packaging with all tags attached. Include the invoice if available.</p>
                    </div>
                    <div class="timeline-item">
                        <h5>Step 3: Ship It Back</h5>
                        <p class="text-muted">Use the prepaid return label we'll email you. Drop off at any courier location or schedule a pickup.</p>
                    </div>
                    <div class="timeline-item">
                        <h5>Step 4: Get Your Refund</h5>
                        <p class="text-muted">Once we receive and inspect your return, we'll process your refund within 5-7 business days.</p>
                    </div>
                </div>
            </div>

            <!-- FAQs -->
            <h3 class="fw-bold mt-5 mb-4">Frequently Asked Questions</h3>
            
            <div class="faq-item">
                <h6 class="mb-2"><i class="fa fa-question-circle text-primary me-2"></i>What items cannot be returned?</h6>
                <p class="text-muted mb-0 small">Underwear, swimwear, and items marked as "Final Sale" cannot be returned for hygiene and safety reasons.</p>
            </div>

            <div class="faq-item">
                <h6 class="mb-2"><i class="fa fa-question-circle text-primary me-2"></i>Can I return sale items?</h6>
                <p class="text-muted mb-0 small">Yes! Sale items can be returned within the same 30-day window, unless marked as "Final Sale".</p>
            </div>

            <div class="faq-item">
                <h6 class="mb-2"><i class="fa fa-question-circle text-primary me-2"></i>How long does the refund take?</h6>
                <p class="text-muted mb-0 small">Refunds are processed within 5-7 business days after we receive your return. It may take an additional 3-5 days for the amount to reflect in your account.</p>
            </div>

            <div class="faq-item">
                <h6 class="mb-2"><i class="fa fa-question-circle text-primary me-2"></i>Can I exchange for a different product?</h6>
                <p class="text-muted mb-0 small">Currently, we only offer size/color exchanges for the same product. For different products, please return and place a new order.</p>
            </div>
        </div>

        <!-- Return Request Form -->
        <div class="col-lg-4">
            <div class="return-form-card sticky-top" id="returnForm" style="top: 100px;">
                <h4 class="fw-bold mb-4"><i class="fa fa-file-alt me-2"></i>Request a Return</h4>
                
                <?php if ($isLoggedIn): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Order</label>
                            <select name="order_id" id="orderSelect" class="form-select" required>
                                <option value="">Choose an order...</option>
                                <?php foreach ($userOrders as $order): ?>
                                    <option value="<?= $order['id'] ?>">
                                        Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?> - 
                                        ₹<?= number_format($order['final_amount'], 2) ?> - 
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason for Return</label>
                            <select name="reason" class="form-select" required>
                                <option value="">Select reason...</option>
                                <option value="wrong_size">Wrong Size</option>
                                <option value="wrong_item">Wrong Item Received</option>
                                <option value="defective">Defective/Damaged</option>
                                <option value="not_as_described">Not as Described</option>
                                <option value="changed_mind">Changed Mind</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Comments</label>
                            <textarea name="comments" class="form-control" rows="4" placeholder="Please provide any additional details..."></textarea>
                        </div>

                        <button type="submit" name="submit_return" class="btn btn-primary w-100">
                            <i class="fa fa-paper-plane me-2"></i>Submit Request
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fa fa-user-lock fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Please login to submit a return request</p>
                        <a href="login.php?redirect=returns.php" class="btn btn-primary w-100">
                            <i class="fa fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                <?php endif; ?>

                <hr class="my-4">

                <div class="text-center">
                    <h6 class="fw-bold mb-3">Need Help?</h6>
                    <p class="small text-muted mb-3">Our customer support team is here to assist you</p>
                    <a href="contact.php" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fa fa-envelope me-2"></i>Contact Support
                    </a>
                    <a href="tel:+911234567890" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fa fa-phone me-2"></i>Call: +91 123-456-7890
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-select order if coming from orders page
document.addEventListener('DOMContentLoaded', function() {
    const selectedOrderId = sessionStorage.getItem('selectedOrderId');
    if (selectedOrderId) {
        const orderSelect = document.getElementById('orderSelect');
        if (orderSelect) {
            orderSelect.value = selectedOrderId;
            sessionStorage.removeItem('selectedOrderId');
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
