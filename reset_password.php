<?php
ob_start();
require_once 'db_connect.php';

$msg = '';
$msg_type = 'info';
$title_page = "Reset Password";
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid request.");
}


// Validate Token
$stmt = $con->prepare("SELECT user_id FROM user_verification WHERE reset_token=? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Invalid or expired token.");
}

$row = $res->fetch_assoc();
$user_id = $row['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'];
    $cpass = $_POST['confirm_password'];
    
    if ($pass !== $cpass) {
        $msg = "Passwords do not match.";
        $msg_type = "danger";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        // Update Password
        $con->query("UPDATE users SET password_hash='$hash' WHERE id=$user_id");
        
        // Clear Token
        $con->query("UPDATE user_verification SET reset_token=NULL, reset_token_expiry=NULL WHERE user_id=$user_id");
        
        $msg = "Password reset successfully! <a href='login.php'>Login Now</a>";
        $msg_type = "success";
    }
}

?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Reset Password</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($msg): ?>
                        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
