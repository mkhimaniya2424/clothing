<?php
session_start();
require_once 'db_connect.php';

$title_page = "Login";
$error = "";

// Get redirect parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'home.php';

// If already logged in → redirect
if (isset($_SESSION['user'])) {
    header("Location: " . $redirect);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email_username = trim($_POST['email_username']);
    $password = $_POST['password'];

    if (empty($email_username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {

    // Secure prepared statement
    $stmt = $con->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email_username, $email_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {

            if ($user['status'] === 'active') {

                // 🔥 Correct session structure used by profile.php
                $_SESSION['user'] = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email']
                ];

                // Session Security
                $_SESSION['initiated'] = true;
                session_regenerate_id(true);

                $_SESSION['security'] = [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ];

                header("Location: " . $redirect);
                exit();

            } else {
                $error = "Your account is inactive. Please contact support.";
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

        $stmt->close();
    }
}

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Welcome Back</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="email_username" class="form-label">Email or Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa fa-user text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="email_username" name="email_username" data-validation="required" required>
                        </div>
                        <span id="email_usernameError" class="text-danger small" style="display:none;"></span>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa fa-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" data-validation="required" required>
                        </div>
                        <span id="passwordError" class="text-danger small" style="display:none;"></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                     
                        <a href="forgot_password.php" class="text-decoration-none small">Forgot Password?</a>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="fw-bold">Register here</a></p>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
<script src="js/validate.js"></script>
