<?php
ob_start();
require_once 'db_connect.php';
require_once 'email_helper.php';

$msg = '';
$msg_type = 'info';
$title_page = "Forgot Password";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Check user
    $stmt = $con->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        
        // Insert or Update token (handles cases where user_verification record doesn't exist)
        // Using NOW() + INTERVAL to avoid timezone mismatch between PHP and MySQL
        $upd = $con->prepare("INSERT INTO user_verification (user_id, reset_token, reset_token_expiry) 
                              VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR)) 
                              ON DUPLICATE KEY UPDATE reset_token=?, reset_token_expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR)");
        $upd->bind_param("iss", $user['id'], $token, $token);
        $upd->execute();
        
        // Send Email
        $link = "http://localhost/clothing/reset_password.php?token=$token";
        $subject = "Reset Password - Clothing Store";
        $body = "<p>Click the link below to reset your password:</p><a href='$link'>$link</a><p>Link expires in 1 hour.</p>";
        
        if (sendEmail($email, $subject, $body)) {
            $msg = "Password reset link sent to your email.";
            $msg_type = "success";
        } else {
            $msg = "Failed to send email.";
            $msg_type = "danger";
        }
    } else {
        $msg = "Email not found.";
        $msg_type = "danger";
    }
}

?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Forgot Password</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($msg): ?>
                        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enter your email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
