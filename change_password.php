<?php
ob_start();
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$msg = '';
$msg_type = 'info';
$user_id = $_SESSION['user']['id'];
$title_page = "Change Password";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password hash
    $stmt = $con->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();


    if (password_verify($current_password, $user['password_hash'])) {
        if ($new_password === $confirm_password) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $upd = $con->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $upd->bind_param("si", $new_hash, $user_id);
            
            if ($upd->execute()) {
                $msg = "Password changed successfully!";
                $msg_type = 'success';
            } else {
                $msg = "Error updating password.";
                $msg_type = 'danger';
            }
        } else {
            $msg = "New passwords do not match.";
            $msg_type = 'danger';
        }
    } else {
        $msg = "Incorrect current password.";
        $msg_type = 'danger';
    }

}
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Change Password</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($msg): ?>
                        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="profile.php" class="btn btn-outline-secondary">Back to Profile</a>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
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
