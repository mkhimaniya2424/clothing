<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

/* ---- Notifications & Messages ---- */
/* REMOVED CLOAPI STOCK NOTIFICATION COMPLETELY */
$notificationCount = 0;

$unreadCount = 0;
include_once("../db_connect.php");
// Check if contact_messages table exists
$checkTable = $con->query("SHOW TABLES LIKE 'contact_messages'");
if ($checkTable && $checkTable->num_rows > 0) {
    $res = $con->query("SELECT COUNT(*) as cnt FROM contact_messages WHERE status='pending'");
    if ($res) {
        $unreadCount = $res->fetch_assoc()['cnt'];
    }
}
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

<script src="../js/jquery-3.7.1.min.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>

<style>
/* ----------------- Sidebar ------------------- */
.sidebar {
    width: 230px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #202020;
    padding-top: 20px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.2);
}

.sidebar h4 {
    font-weight: 600;
}

.sidebar-link {
    display: block;
    padding: 12px 20px;
    margin: 4px 10px;
    color: #dcdcdc;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.3s;
    font-size: 15px;
}

.sidebar-link:hover,
.sidebar-link.active {
    background: #0d6efd;
    color: #fff;
}

/* ----------------- Header ------------------- */
.header {
    height: 65px;
    width: calc(100% - 230px);
    background: #fff;
    margin-left: 230px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 25px;
    border-bottom: 1px solid #ddd;
    position: fixed;
    top: 0;
    z-index: 1000;
}

.header i {
    cursor: pointer;
    transition: 0.3s;
}

.header i:hover {
    color: #0d6efd;
}

/* ---------------- Main Content ---------------- */
.main-content {
    margin-left: 230px;
    margin-top: 80px;
}
</style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4 class="text-center text-light mb-4 mt-2">
        <i class="fa-solid fa-shirt me-2"></i>Admin
    </h4>

    <a href="admin_dashboard.php" class="sidebar-link active"><i class="fa fa-home me-2"></i>Dashboard</a>
    <a href="admin_manage-categories.php" class="sidebar-link"><i class="fa fa-box-open me-2"></i>Manage categories</a>
    <a href="admin_manage-brands.php" class="sidebar-link"><i class="fa fa-tags me-2"></i>Manage Brands</a>
    <a href="admin_manage-products.php" class="sidebar-link"><i class="fa fa-box-open me-2"></i>Manage Products</a>
    <a href="admin_manage-orders.php" class="sidebar-link"><i class="fa fa-shopping-cart me-2"></i>Manage Orders</a>
    <a href="admin_manage-returns.php" class="sidebar-link"><i class="fa fa-undo me-2"></i>Return Requests</a>
    <a href="admin_manage-wishlist.php" class="sidebar-link"><i class="fa fa-heart me-2"></i>Manage Wishlist</a>
    <a href="admin_manage-cart.php" class="sidebar-link"><i class="fa fa-cart-plus me-2"></i>Manage Cart</a>
    <a href="admin_manage-offers.php" class="sidebar-link"><i class="fa fa-credit-card me-2"></i>Manage Offers</a>
    <a href="admin_manage-users.php" class="sidebar-link"><i class="fa fa-users me-2"></i>Manage Users</a>
    <a href="admin_manage-contact.php" class="sidebar-link"><i class="fa fa-envelope me-2"></i>Messages</a>
    <a href="admin_manage-reviews.php" class="sidebar-link"><i class="fa fa-star me-2"></i>Manage Reviews</a>

    <a href="admin_revenue.php" class="sidebar-link"><i class="fa fa-chart-line me-2"></i>Revenue</a>
</div>

<!-- HEADER -->
<div class="header">
    <div class="d-flex align-items-center">

        <!-- Notifications -->
        <a href="admin_notification.php" class="me-3 position-relative text-dark">
            <i class="fa fa-bell fs-5"></i>
            <?php
            // Count low stock notifications
            $stockNotifCount = 0;
            $stockRes = $con->query("SELECT COUNT(*) as cnt FROM product_stock WHERE stock <= 0");
            if ($stockRes) {
                $stockNotifCount = $stockRes->fetch_assoc()['cnt'];
            }
            
            if ($stockNotifCount > 0): 
            ?>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                    <?= $stockNotifCount ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Messages -->
        <a href="admin_manage-contact.php" class="me-3 position-relative text-dark">
            <i class="fa fa-envelope fs-5"></i>
            <?php if(!empty($unreadCount) && $unreadCount > 0): ?>
                <span class="badge bg-primary position-absolute top-0 start-100 translate-middle rounded-pill">
                    <?= $unreadCount ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="dropdown-toggle text-dark" id="adminMenu" data-bs-toggle="dropdown">
                <i class="fa fa-user-circle fs-5 me-1"></i>
                <?= htmlspecialchars($_SESSION['admin']['name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="admin_profile.php"><i class="fa fa-user me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="admin_change_password.php"><i class="fa fa-key me-2"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="admin_logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content p-4">
    <?php if (isset($content)) echo $content; ?>
</div>

</body>
</html>
