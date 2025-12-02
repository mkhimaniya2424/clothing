<?php
$title_page = "Manage Cart";
ob_start();
include_once("db_connect.php");
?>
<div class="container-fluid mt-4">
    <h3>Manage Cart</h3>
    <div class="alert alert-info">Cart management feature coming soon.</div>
</div>
<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
