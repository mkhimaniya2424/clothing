<?php
ob_start();
?>


<?php
// Capture the content into $content1
$content1 = ob_get_clean();

// Set the page title
$title_page = "dashboard";

// Include the layout file which will use $title_page and $content1
//include_once("layout.php");
include_once("dashboard.php");

?>
