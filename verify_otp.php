<?php
ob_start();
require_once 'db_connect.php';
require_once 'email_helper.php';

$msg = '';
$email = $_GET['email'] ?? '';

if (!$email) {
    header("Location: register.php");
    exit;
}

// Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $otp = trim($_POST['otp']);
    
    // Check OTP
    $stmt = $con->prepare("
        SELECT u.id, uv.email_otp, uv.email_otp_expiry 
        FROM users u 
        JOIN user_verification uv ON u.id = uv.user_id 
        WHERE u.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ($row['email_otp'] === $otp) {
            if (strtotime($row['email_otp_expiry']) > time()) {
                // Success
                $con->query("UPDATE users SET status='active' WHERE id=" . $row['id']);
                $con->query("UPDATE user_verification SET email_verified=1, email_verified_at=NOW(), email_otp=NULL WHERE user_id=" . $row['id']);
                
                $msg = "Email verified successfully! <a href='login.php'>Login Now</a>";
            } else {
                $msg = "OTP has expired.";
            }
        } else {
            $msg = "Invalid OTP.";
        }
    } else {
        $msg = "User not found.";
    }
}

// Resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
    
    $stmt = $con->prepare("UPDATE user_verification uv JOIN users u ON u.id=uv.user_id SET uv.email_otp=?, uv.email_otp_expiry=? WHERE u.email=?");
    $stmt->bind_param("sss", $otp, $expiry, $email);
    
    if ($stmt->execute()) {
        $subject = "Resend OTP - Clothing Store";
        $body = "<h3>New OTP Request</h3><p>Your new OTP is: <strong>$otp</strong></p>";
        if (sendEmail($email, $subject, $body)) {
            $msg = "New OTP sent to your email.";
        } else {
            $msg = "Failed to send email.";
        }
    }
}
?>

<?php include_once("layout.php"); ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Verify Email</h4>
                </div>
                <div class="card-body p-4 text-center">
                    <p>We have sent an OTP to <strong><?= htmlspecialchars($email) ?></strong></p>
                    
                    <?php if($msg): ?>
                        <div class="alert alert-info"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="otp" class="form-control text-center fs-4" placeholder="Enter 6-digit OTP" maxlength="6" required>
                        </div>
                        <button type="submit" name="verify" class="btn btn-success w-100 mb-3">Verify OTP</button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="resend" class="btn btn-link">Resend OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
