<?php
require_once 'cashfree_config.php';

echo "Testing URL Generation:\n";
echo "Base URL: " . CASHFREE_API_BASE_URL . "\n";
echo "Endpoint for /orders: " . getCashfreeEndpoint('/orders') . "\n";
?>
