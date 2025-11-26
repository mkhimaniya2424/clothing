<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Clothing Brand</title>

<!-- Bootstrap -->
<link rel="stylesheet" href="../css/bootstrap.min.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="../fontawesome/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="../css/admin.css">

<!-- jQuery & Bootstrap JS -->
<script src="../js/jquery-3.7.1.min.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar bg-dark text-light">
    <h4 class="text-center mb-4 mt-2">
        <i class="fa-solid fa-shirt me-2"></i>Admin
    </h4>
    <a href="dashboard.php" class="sidebar-link active"><i class="fa fa-home me-2"></i>Dashboard</a>
    <a href="manage-products.php" class="sidebar-link"><i class="fa fa-box-open me-2"></i>Manage Products</a>
    <a href="manage-orders.php" class="sidebar-link"><i class="fa fa-shopping-cart me-2"></i>Manage Orders</a>
    <a href="manage-users.php" class="sidebar-link"><i class="fa fa-users me-2"></i>Manage Users</a>
    <a href="manage-admin.php" class="sidebar-link"><i class="fa fa-user-shield me-2"></i>Manage admin</a>
    <a href="revenue.php" class="sidebar-link"><i class="fa fa-chart-line me-2"></i>Revenue</a>
    <a href="settings.php" class="sidebar-link"><i class="fa fa-cog me-2"></i>Settings</a>
</div>

<!-- HEADER -->
<div class="header bg-light d-flex justify-content-end align-items-center px-4">
    <div class="d-flex align-items-center">
        <!-- Notifications -->
        <a href="#" class="me-3 position-relative text-dark">
            <i class="fa fa-bell fs-5"></i>
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">3</span>
        </a>

        <!-- Messages -->
        <a href="#" class="me-3 position-relative text-dark">
            <i class="fa fa-envelope fs-5"></i>
            <span class="badge bg-primary position-absolute top-0 start-100 translate-middle rounded-pill">5</span>
        </a>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="dropdown-toggle text-dark" id="adminMenu" data-bs-toggle="dropdown">
                <i class="fa fa-user-circle fs-5"></i>
                <?php echo isset($_SESSION['admin']) ? $_SESSION['admin']['name'] : 'Admin'; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fa fa-cog me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content p-4">
    <?php if(isset($content)) echo $content; ?>
</div>

</body>
</html>
