<?php
require_once 'db_connect.php';

echo "DATABASE TABLES:\n";
echo str_repeat("=", 80) . "\n\n";

// Get all tables
$tables = $con->query("SHOW TABLES");
$tableList = [];

while ($row = $tables->fetch_array()) {
    $tableList[] = $row[0];
}

foreach ($tableList as $table) {
    echo "TABLE: $table\n";
    echo str_repeat("-", 80) . "\n";
    
    // Get columns
    $columns = $con->query("DESCRIBE $table");
    while ($col = $columns->fetch_assoc()) {
        $key = $col['Key'] ? " [{$col['Key']}]" : "";
        echo "  - {$col['Field']}: {$col['Type']}{$key}\n";
    }
    
    // Get foreign keys
    $fks = $con->query("
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'clothing_store'
        AND TABLE_NAME = '$table'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($fks && $fks->num_rows > 0) {
        echo "\n  FOREIGN KEYS:\n";
        while ($fk = $fks->fetch_assoc()) {
            echo "  - {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    }
    
    echo "\n";
}
?>
