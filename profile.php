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

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">

                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="fa fa-user fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?= htmlspecialchars($user['username']) ?></h3>
                        <p class="text-muted mb-0"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>

                <h5 class="text-primary mb-3">Personal Information</h5>

                <div class="row mb-4">
                    <div class="col-md-6 mb-2"><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'Not set') ?></div>
                    <div class="col-md-6 mb-2"><strong>Gender:</strong> <?= ucfirst(htmlspecialchars($user['gender'] ?? 'Not set')) ?></div>
                    <div class="col-md-6 mb-2"><strong>Date of Birth:</strong> <?= htmlspecialchars($user['dob'] ?? 'Not set') ?></div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-success"><?= ucfirst(htmlspecialchars($user['status'])) ?></span>
                    </div>
                </div>

                <hr>

                <h5 class="text-primary mb-3">Address Details</h5>

                <?php if ($address): ?>
                    <p><strong><?= ucfirst($address['address_type']) ?>:</strong></p>
                    <p>
                        <?= htmlspecialchars($address['address_line1']) ?><br>
                        <?= htmlspecialchars($address['address_line2'] ?? '') ?><br>
                        <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['postal_code']) ?><br>
                        <?= htmlspecialchars($address['country']) ?>
                    </p>
                <?php else: ?>
                    <p class="text-muted">No address found.</p>
                <?php endif; ?>

                <div class="mt-4 text-end">
                    <a href="change_password.php" class="btn btn-outline-secondary btn-sm me-2">Change Password</a>
                    <a href="profile_edit.php" class="btn btn-outline-primary btn-sm">Edit Profile</a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
