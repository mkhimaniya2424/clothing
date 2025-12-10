<?php
//---------------------------------------------
// SESSION SECURITY
//---------------------------------------------
session_start();

// Prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Prevent session hijacking
if (!isset($_SESSION['security'])) {
    $_SESSION['security'] = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
} else {
    if ($_SESSION['security']['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '') ||
        $_SESSION['security']['ua'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        
       
    }
}

// Check login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$user_id = $_SESSION['user']['id'];
$title_page = "My Profile";

// Handle profile photo upload or removal
$uploadMessage = '';

// 1. Handle Photo Removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo'])) {
    // Update database to set profile_pic to NULL
    $stmt = $con->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $uploadMessage = 'Profile photo removed successfully!';
        // Optional: Delete the file from server if you want to save space
        // if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) { unlink($user['profile_pic']); }
    } else {
        $uploadMessage = 'Error removing photo.';
    }
    $stmt->close();
}

// 2. Handle Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowed)) {
            $uploadDir = __DIR__ . '/uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $photoPath = 'uploads/profiles/' . $newFileName;
                
                // Update database
                $stmt = $con->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $photoPath, $user_id);
                $stmt->execute();
                $stmt->close();
                
                $uploadMessage = 'Profile photo updated successfully!';
            } else {
                $uploadMessage = 'Error uploading photo.';
            }
        } else {
            $uploadMessage = 'Only JPG, JPEG, PNG, GIF files are allowed.';
        }
    }
}

//---------------------------------------------
// FETCH USER DETAILS
//---------------------------------------------
$stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

//---------------------------------------------
// FETCH ADDRESS DETAILS
//---------------------------------------------
$stmt2 = $con->prepare("SELECT * FROM user_address WHERE user_id = ? LIMIT 1");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$address = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

// Default profile photo
$profilePhoto = $user['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&size=200&background=667eea&color=fff';

ob_start();
?>

<style>
.profile-photo-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
}
.profile-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.photo-upload-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    border: 3px solid white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}
.photo-upload-btn:hover {
    background: #764ba2;
    transform: scale(1.1);
}
.photo-remove-btn {
    position: absolute;
    bottom: 5px;
    left: 5px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    border: 3px solid white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}
.photo-remove-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}
.profile-card {
    border-radius: 20px;
    overflow: hidden;
}
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 20px 20px;
    color: white;
    text-align: center;
}
</style>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if ($uploadMessage): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?= htmlspecialchars($uploadMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow-sm border-0 mb-4 profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-photo-container">
                    <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile Photo" class="profile-photo" id="profilePhotoPreview">
                    <label for="profilePhotoInput" class="photo-upload-btn" title="Change Photo">
                        <i class="fa fa-camera"></i>
                    </label>
                    
                    <?php if (!empty($user['profile_pic'])): ?>
                        <form method="POST" id="removePhotoForm" style="display:inline;">
                            <input type="hidden" name="remove_photo" value="1">
                            <button type="button" class="photo-remove-btn" title="Remove Photo" onclick="if(confirm('Are you sure you want to remove your profile photo?')) document.getElementById('removePhotoForm').submit();">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="photoUploadForm" style="display: none;">
                        <input type="file" name="profile_pic" id="profilePhotoInput" accept="image/*" onchange="document.getElementById('photoUploadForm').submit();">
                    </form>
                </div>
                <h3 class="mb-1"><?= htmlspecialchars($user['username']) ?></h3>
                <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <div class="card-body p-4">
                <h5 class="text-primary mb-3"><i class="fa fa-user me-2"></i>Personal Information</h5>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-phone text-muted me-2"></i>
                            <div>
                                <small class="text-muted d-block">Phone</small>
                                <strong><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-venus-mars text-muted me-2"></i>
                            <div>
                                <small class="text-muted d-block">Gender</small>
                                <strong><?= ucfirst(htmlspecialchars($user['gender'] ?? 'Not set')) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-birthday-cake text-muted me-2"></i>
                            <div>
                                <small class="text-muted d-block">Date of Birth</small>
                                <strong><?= htmlspecialchars($user['dob'] ?? 'Not set') ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-check-circle text-muted me-2"></i>
                            <div>
                                <small class="text-muted d-block">Account Status</small>
                                <span class="badge bg-success"><?= ucfirst(htmlspecialchars($user['status'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="text-primary mb-3"><i class="fa fa-map-marker-alt me-2"></i>Address Details</h5>

                <?php if ($address): ?>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-2"><strong><?= ucfirst($address['address_type']) ?> Address:</strong></p>
                        <p class="mb-0">
                            <?= htmlspecialchars($address['address_line1']) ?><br>
                            <?= htmlspecialchars($address['address_line2'] ?? '') ?><br>
                            <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['postal_code']) ?><br>
                            <?= htmlspecialchars($address['country']) ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-2"></i>No address found. Please add your address.
                    </div>
                <?php endif; ?>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="change_password.php" class="btn btn-outline-secondary">
                        <i class="fa fa-key me-2"></i>Change Password
                    </a>
                    <a href="profile_edit.php" class="btn btn-primary">
                        <i class="fa fa-edit me-2"></i>Edit Profile
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
