<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

include_once("db_connect.php");

// Profile images folder
$profileDir = __DIR__ . "/images/profile/";
if (!is_dir($profileDir)) mkdir($profileDir, 0755, true);

// Fetch admin data
$admin_id = $_SESSION['admin']['id'];
$sql = "SELECT * FROM admin WHERE id = $admin_id LIMIT 1";
$result = $con->query($sql);
$admin = $result->fetch_assoc();

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $con->real_escape_string($_POST['full_name']);
    $email     = $con->real_escape_string($_POST['email']);
    $mobile    = $con->real_escape_string($_POST['mobile']);
    $profilePicName = $admin['profile_pic'];

    // Handle file upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed)) {
            $newFileName = 'admin_'.$admin_id.'_'.time().'.'.$ext;
            $targetPath = $profileDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $profilePicName = $newFileName;
                if (!empty($admin['profile_pic']) && file_exists($profileDir.$admin['profile_pic'])) {
                    unlink($profileDir.$admin['profile_pic']);
                }
            } else $error = "Failed to upload profile picture.";
        } else $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
    }

    if (!$error) {
        $update = $con->query("UPDATE admin SET full_name='$full_name', email='$email', mobile='$mobile', profile_pic='$profilePicName' WHERE id=$admin_id");
        if ($update) {
            $success = "Profile updated successfully.";
            $_SESSION['admin']['name'] = $full_name;
            $result = $con->query("SELECT * FROM admin WHERE id = $admin_id LIMIT 1");
            $admin = $result->fetch_assoc();
        } else $error = "Database update failed.";
    }
}

$profilePic = !empty($admin['profile_pic']) && file_exists($profileDir . $admin['profile_pic']) 
              ? "images/profile/" . $admin['profile_pic'] 
              : "images/profile/default.png";

ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-warning text-white text-center">
                <h3>Edit Profile</h3>
            </div>
            <div class="card-body">
                <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3 text-center">
                        <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="rounded-circle mb-3" width="120" height="120">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($admin['mobile'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Profile Picture</label>
                        <input type="file" name="profile_pic" class="form-control">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="admin_profile.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-success">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
