<?php
$title_page = "Manage Wishlist";
ob_start();
include_once("db_connect.php");
?>
<div class="container-fluid mt-4">
    <h3>Manage Wishlist</h3>
    <div class="alert alert-info">Wishlist management feature coming soon.</div>
</div>
<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
