<?php
ob_start();
?>


<?php
// Capture the content into $content1
$content1 = ob_get_clean();

// Set the page title
$title_page = "home";

// Include the layout file which will use $title_page and $content1
//include_once("layout.php");
include_once("home.php");

?>
