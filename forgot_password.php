<?php
ob_start();
require_once 'db_connect.php';
require_once 'email_helper.php';

$msg = '';

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
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Update token
        $upd = $con->prepare("UPDATE user_verification SET reset_token=?, reset_token_expiry=? WHERE user_id=?");
        $upd->bind_param("ssi", $token, $expiry, $user['id']);
        $upd->execute();
        
        // Send Email
        $link = "http://localhost/clothing/reset_password.php?token=$token";
        $subject = "Reset Password - Clothing Store";
        $body = "<p>Click the link below to reset your password:</p><a href='$link'>$link</a><p>Link expires in 1 hour.</p>";
        
        if (sendEmail($email, $subject, $body)) {
            $msg = "Password reset link sent to your email.";
        } else {
            $msg = "Failed to send email.";
        }
    } else {
        $msg = "Email not found.";
    }
}
?>

<?php include_once("layout.php"); ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Forgot Password</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($msg): ?>
                        <div class="alert alert-info"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enter your email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
