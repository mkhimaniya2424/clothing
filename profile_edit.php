<?php
ob_start();
session_start();
include_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$msg = "";
$msg_type = "info";
$title_page = "Edit Profile";

// Handle Profile Photo Upload
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_photo'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (in_array($fileExt, $allowed)) {
        $uploadDir = __DIR__ . '/uploads/profiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $photoPath = 'uploads/profiles/' . $newFileName;
            
            // Delete old photo if exists
            $oldPhoto = $con->query("SELECT profile_photo FROM users WHERE id='$user_id'")->fetch_assoc();
            if ($oldPhoto && !empty($oldPhoto['profile_photo']) && file_exists($oldPhoto['profile_photo'])) {
                unlink($oldPhoto['profile_photo']);
            }
            
            // Update database
            $con->query("UPDATE users SET profile_photo='$photoPath' WHERE id='$user_id'");
            $msg = "Profile photo updated successfully!";
            $msg_type = "success";
        }
    } else {
        $msg = "Only JPG, JPEG, PNG, GIF files are allowed.";
        $msg_type = "danger";
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['profile_photo'])) {
    $phone = $con->real_escape_string($_POST['phone']);
    $gender = $con->real_escape_string($_POST['gender']);
    $dob = $con->real_escape_string($_POST['dob']);
    
    $address1 = $con->real_escape_string($_POST['address_line1']);
    $address2 = $con->real_escape_string($_POST['address_line2']);
    $city = $con->real_escape_string($_POST['city']);
    $state = $con->real_escape_string($_POST['state']);
    $zip = $con->real_escape_string($_POST['postal_code']);
    $country = $con->real_escape_string($_POST['country']);

    // Update User Info
    $u_sql = "UPDATE users SET phone='$phone', gender='$gender', dob='$dob' WHERE id='$user_id'";
    $con->query($u_sql);

    // Update/Insert Address
    $check_addr = $con->query("SELECT * FROM user_address WHERE user_id='$user_id'");
    if ($check_addr->num_rows > 0) {
        $a_sql = "UPDATE user_address SET address_line1='$address1', address_line2='$address2', city='$city', state='$state', postal_code='$zip', country='$country' WHERE user_id='$user_id'";
    } else {
        $a_sql = "INSERT INTO user_address (user_id, address_line1, address_line2, city, state, postal_code, country) VALUES ('$user_id', '$address1', '$address2', '$city', '$state', '$zip', '$country')";
    }
    
    if ($con->query($a_sql)) {
        $msg = "Profile updated successfully!";
        $msg_type = "success";
    } else {
        $msg = "Error updating profile: " . $con->error;
        $msg_type = "danger";
    }
}


// Fetch Current Data
$u_res = $con->query("SELECT * FROM users WHERE id='$user_id'");
$user = $u_res->fetch_assoc();

$a_res = $con->query("SELECT * FROM user_address WHERE user_id='$user_id'");
$address = $a_res->fetch_assoc();

// Default profile photo
$profilePhoto = $user['profile_photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&size=200&background=667eea&color=fff';
?>

<style>
.profile-photo-upload {
    text-align: center;
    margin-bottom: 30px;
}
.profile-photo-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #667eea;
    margin: 0 auto 15px;
    display: block;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.photo-upload-label {
    display: inline-block;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.photo-upload-label:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}
.edit-profile-card {
    border-radius: 20px;
    overflow: hidden;
}
</style>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 edit-profile-card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h4 class="mb-0"><i class="fa fa-edit me-2"></i>Edit Profile</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($msg): ?>
                        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
                            <?= $msg ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Photo Upload -->
                    <div class="profile-photo-upload">
                        <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile Photo" class="profile-photo-preview" id="photoPreview">
                        <form method="POST" enctype="multipart/form-data" id="photoForm">
                            <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/*" style="display: none;" onchange="previewAndSubmit(this)">
                            <label for="profilePhotoInput" class="photo-upload-label">
                                <i class="fa fa-camera me-2"></i>Change Profile Photo
                            </label>
                        </form>
                        <div><small class="text-muted">JPG, JPEG, PNG, or GIF (Max 5MB)</small></div>
                    </div>

                    <hr class="my-4">

                    <form method="POST">
                        <h5 class="text-primary mb-3"><i class="fa fa-user me-2"></i>Personal Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male" <?= ($user['gender']??'')=='male'?'selected':'' ?>>Male</option>
                                <option value="female" <?= ($user['gender']??'')=='female'?'selected':'' ?>>Female</option>
                                <option value="other" <?= ($user['gender']??'')=='other'?'selected':'' ?>>Other</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <h5 class="text-primary mb-3"><i class="fa fa-map-marker-alt me-2"></i>Address</h5>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="<?= htmlspecialchars($address['address_line1'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="<?= htmlspecialchars($address['address_line2'] ?? '') ?>">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($address['city'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($address['state'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($address['postal_code'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($address['country'] ?? 'India') ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function previewAndSubmit(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
        
        // Submit the form
        document.getElementById('photoForm').submit();
    }
}
</script>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
