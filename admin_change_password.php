<?php
session_start();
include_once("db_connect.php");

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin']['id'];
$success = $error = "";


$sql = "SELECT password FROM admin WHERE id = $admin_id LIMIT 1";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$current_hashed = $row['password'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Convert passwords to MySQL SHA1(SHA1()) format
    $old_hash = "*" . strtoupper(sha1(sha1($old_pass, true)));

    if ($old_hash !== $current_hashed) {
        $error = "Old password is incorrect!";
    } 
    else if (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters!";
    }
    else if ($new_pass !== $confirm_pass) {
        $error = "New password and confirm password do not match!";
    }
    else {
        // Convert new password to MySQL SHA1(SHA1()) format
        $new_hash = "*" . strtoupper(sha1(sha1($new_pass, true)));

        // Update password
        $updateSql = "UPDATE admin SET password='$new_hash' WHERE id=$admin_id";

        if ($con->query($updateSql)) {
            $success = "Password updated successfully!";
        } else {
            $error = "Something went wrong!";
        }
    }
}

ob_start();
$title_page = "Change Password";
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h3>Change Password</h3>
            </div>
            <div class="card-body">

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Old Password</label>
                        <input type="password" name="old_password" required class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="new_password" required class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" required class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    <a href="admin_profile.php" class="btn btn-secondary w-100 mt-2">Back to Profile</a>
                </form>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
