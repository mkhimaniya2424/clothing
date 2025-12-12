<?php
/**
 * Cashfree Payment Gateway Configuration
 * Environment: TEST (Sandbox)
 */

// Cashfree Credentials
define('CASHFREE_APP_ID', 'TEST1091646204f8537d4cf62aca3a0126461901');
define('CASHFREE_SECRET_KEY', 'cfsk_ma_test_f28f93f1cb62c067eac24eee28eea070_9671140b');

// Cashfree API URLs - Updated for correct endpoint structure
define('CASHFREE_API_BASE_URL', 'https://sandbox.cashfree.com'); // Sandbox URL
define('CASHFREE_API_VERSION', '2023-08-01');
define('CASHFREE_MODE', 'sandbox'); // sandbox or production

// For production, use: https://api.cashfree.com

// Return URLs
define('CASHFREE_RETURN_URL', 'http://localhost/clothing/cashfree_return.php');
define('CASHFREE_NOTIFY_URL', 'http://localhost/clothing/cashfree_webhook.php');

/**
 * Get Cashfree API Headers
 */
function getCashfreeHeaders() {
    return [
        'Content-Type: application/json',
        'Accept: application/json',
        'x-api-version: ' . CASHFREE_API_VERSION,
        'x-client-id: ' . CASHFREE_APP_ID,
        'x-client-secret: ' . CASHFREE_SECRET_KEY
    ];
}

/**
 * Get Cashfree API Endpoint
 */
function getCashfreeEndpoint($path) {
    return CASHFREE_API_BASE_URL . '/pg' . $path;
}
?>
