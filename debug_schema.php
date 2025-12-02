<?php
require_once 'db_connect.php';

function describeTable($con, $table) {
    echo "\nTABLE: $table\n";
    $result = $con->query("DESCRIBE $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . " | " . $row['Type'] . "\n";
        }
    } else {
        echo "Error: " . $con->error . "\n";
    }
    echo "--------------------------------\n";
}

describeTable($con, 'orders');
?>
