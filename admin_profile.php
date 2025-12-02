<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    header("Location: login1.php");
    exit;
}

include_once("../db/db_connect.php");

// Fetch admin data
$admin_id = $_SESSION['admin']['id'];
$sql = "SELECT * FROM admin WHERE id = $admin_id LIMIT 1";
$result = $con->query($sql);
$admin = $result->fetch_assoc();

// Profile picture path
$profileDir = "images/profile/";
$profilePic = !empty($admin['profile_pic']) && file_exists($profileDir . $admin['profile_pic']) 
              ? $profileDir . $admin['profile_pic'] 
              : $profileDir . "default.png";

ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h3>Admin Profile</h3>
            </div>
            <div class="card-body text-center">
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="rounded-circle mb-3" width="120" height="120">

                <div class="mb-3 text-start">
                    <label class="fw-bold">Full Name:</label>
                    <p class="form-control"><?= htmlspecialchars($admin['full_name']) ?></p>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold">Username:</label>
                    <p class="form-control"><?= htmlspecialchars($admin['username']) ?></p>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold">Email:</label>
                    <p class="form-control"><?= htmlspecialchars($admin['email'] ?? '') ?></p>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold">Mobile:</label>
                    <p class="form-control"><?= htmlspecialchars($admin['mobile'] ?? '') ?></p>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="dashboard.php" class="btn btn-secondary">Back</a>
                    <div>
                        <a href="profile_edit1.php" class="btn btn-warning me-2">Edit Profile</a>
                        <a href="change_password1.php" class="btn btn-danger">Change Password</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("layout1.php"); // use your existing layout
?>
