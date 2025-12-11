<?php
session_start();
require_once 'db_connect.php';

$title_page = "Register";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User Details
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    
    // Address Details
    $address_line1 = mysqli_real_escape_string($con, $_POST['address_line1']);
    $address_line2 = mysqli_real_escape_string($con, $_POST['address_line2']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $postal_code = mysqli_real_escape_string($con, $_POST['postal_code']);
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $address_type = mysqli_real_escape_string($con, $_POST['address_type']);

    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if user exists
        $check_query = "SELECT id FROM users WHERE email = '$email' OR username = '$username'";
        $check_result = mysqli_query($con, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Start Transaction
            mysqli_begin_transaction($con);
            
            try {
                // Insert into users table
                $insert_user = "INSERT INTO users (username, email, password_hash, phone, gender) 
                                VALUES ('$username', '$email', '$password_hash', '$phone', '$gender')";
                
                if (!mysqli_query($con, $insert_user)) {
                    throw new Exception("Error creating user: " . mysqli_error($con));
                }
                
                $user_id = mysqli_insert_id($con);
                
                // Insert into user_address table
                $insert_address = "INSERT INTO user_address (user_id, address_line1, address_line2, city, state, postal_code, country, address_type) 
                                   VALUES ('$user_id', '$address_line1', '$address_line2', '$city', '$state', '$postal_code', '$country', '$address_type')";
                
                if (!mysqli_query($con, $insert_address)) {
                    throw new Exception("Error adding address: " . mysqli_error($con));
                }
                
                // Insert into user_verification table (required for password reset and email verification)
                $insert_verification = "INSERT INTO user_verification (user_id, email_verified) 
                                       VALUES ('$user_id', 1)";
                
                if (!mysqli_query($con, $insert_verification)) {
                    throw new Exception("Error creating verification record: " . mysqli_error($con));
                }
                
                mysqli_commit($con);
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                
            } catch (Exception $e) {
                mysqli_rollback($con);
                $error = $e->getMessage();
            }
        }
    }
}

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Create an Account</h2>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <h5 class="mb-3 text-primary">Personal Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Gender</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" checked>
                            <label class="form-check-label" for="male">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                            <label class="form-check-label" for="female">Female</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="other" value="other">
                            <label class="form-check-label" for="other">Other</label>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary">Address Details</h5>

                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                    </div>

                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="Unknown">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="address_type" class="form-label">Address Type</label>
                        <select class="form-select" id="address_type" name="address_type">
                            <option value="home">Home</option>
                            <option value="office">Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
