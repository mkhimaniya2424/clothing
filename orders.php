<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$title_page = "My Orders";
ob_start();
?>

<div class="container">
    <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i> You haven't placed any orders yet.
    </div>
    
    <div class="text-center mt-5">
        <a href="shop.php" class="btn btn-primary">Start Shopping</a>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
