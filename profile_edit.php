<?php
ob_start();
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Refresh user data
    } else {
        $msg = "Error updating profile: " . $con->error;
    }
}

// Fetch Current Data
$u_res = $con->query("SELECT * FROM users WHERE id='$user_id'");
$user = $u_res->fetch_assoc();

$a_res = $con->query("SELECT * FROM user_address WHERE user_id='$user_id'");
$address = $a_res->fetch_assoc();
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Edit Profile</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($msg): ?>
                        <div class="alert alert-success"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <h5 class="text-primary mb-3">Personal Details</h5>
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

                        <h5 class="text-primary mb-3">Address</h5>
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
                            <a href="profile.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
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
