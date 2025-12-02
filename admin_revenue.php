<?php
$title_page = "Revenue";
ob_start();
include_once("db_connect.php");
?>
<div class="container-fluid mt-4">
    <h3>Revenue</h3>
    <div class="alert alert-info">Revenue analytics feature coming soon.</div>
</div>
<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
