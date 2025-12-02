<?php
$title_page = "Visitors";
ob_start();
include_once("db_connect.php");
?>
<div class="container-fluid mt-4">
    <h3>Visitors</h3>
    <div class="alert alert-info">Visitor tracking feature coming soon.</div>
</div>
<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
