<?php
session_start();
include_once("db_connect.php");

// If already logged in, go to dashboard
if (isset($_SESSION['admin'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $con->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Fetch admin row
    $sql = "SELECT * FROM admin WHERE username='$username' LIMIT 1";
    $result = $con->query($sql);

    if ($result && $result->num_rows == 1) {

        $row = $result->fetch_assoc();

        // Convert the entered password to MySQL format:  *SHA1(SHA1(password))
        $enteredHash = "*" . strtoupper(sha1(sha1($password, true)));

        if ($enteredHash === $row['password']) {

            // Save session
            $_SESSION["admin"] = [
                "id"       => $row["id"],
                "username" => $row["username"],
                "name"     => $row["full_name"]
            ];

            header("Location: admin_dashboard.php");
            exit;

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "Username not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Admin Login</h4>
                </div>

                <div class="card-body">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label><strong>Username</strong></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label><strong>Password</strong></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
