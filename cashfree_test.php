<?php
/**
 * Cashfree Integration Test Page
 * Use this to verify your Cashfree configuration
 */

require_once 'cashfree_config.php';

$test_results = [];

// Test 1: Check if constants are defined
$test_results[] = [
    'test' => 'Configuration Constants',
    'status' => defined('CASHFREE_APP_ID') && defined('CASHFREE_SECRET_KEY'),
    'message' => defined('CASHFREE_APP_ID') ? 'App ID: ' . substr(CASHFREE_APP_ID, 0, 20) . '...' : 'Not configured'
];

// Test 2: Check API URL
$test_results[] = [
    'test' => 'API URL Configuration',
    'status' => defined('CASHFREE_API_URL'),
    'message' => CASHFREE_API_URL ?? 'Not configured'
];

// Test 3: Check return URL
$test_results[] = [
    'test' => 'Return URL Configuration',
    'status' => defined('CASHFREE_RETURN_URL'),
    'message' => CASHFREE_RETURN_URL ?? 'Not configured'
];

// Test 4: Check cURL availability
$test_results[] = [
    'test' => 'cURL Extension',
    'status' => function_exists('curl_init'),
    'message' => function_exists('curl_init') ? 'Available' : 'Not installed'
];

// Test 5: Test API connectivity (ping)
$api_test = false;
$api_message = 'Not tested';

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CASHFREE_API_URL . '/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, getCashfreeHeaders());
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 400 || $httpCode == 401 || $httpCode == 200) {
        $api_test = true;
        $api_message = 'API is reachable (HTTP ' . $httpCode . ')';
    } else {
        $api_message = 'API not reachable (HTTP ' . $httpCode . ')';
    }
}

$test_results[] = [
    'test' => 'Cashfree API Connectivity',
    'status' => $api_test,
    'message' => $api_message
];

// Test 6: Check database connection
require_once 'db_connect.php';
$db_test = false;
$db_message = 'Not connected';

if (isset($con) && $con->ping()) {
    $db_test = true;
    $db_message = 'Connected';
}

$test_results[] = [
    'test' => 'Database Connection',
    'status' => $db_test,
    'message' => $db_message
];

// Test 7: Check payment_details table
$table_test = false;
$table_message = 'Not found';

if ($db_test) {
    $result = $con->query("SHOW COLUMNS FROM payment_details WHERE Field = 'payment_method'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (strpos($row['Type'], 'online') !== false) {
            $table_test = true;
            $table_message = 'Table updated with online payment support';
        } else {
            $table_message = 'Table exists but needs migration';
        }
    }
}

$test_results[] = [
    'test' => 'Payment Details Table',
    'status' => $table_test,
    'message' => $table_message
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Integration Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Cashfree Integration Test</h1>
        
        <div class="alert alert-info">
            <strong>Note:</strong> This page tests your Cashfree payment gateway configuration.
        </div>

        <div class="row">
            <div class="col-md-8">
                <?php foreach ($test_results as $result): ?>
                    <div class="card test-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php if ($result['status']): ?>
                                    <i class="test-pass">✓</i>
                                <?php else: ?>
                                    <i class="test-fail">✗</i>
                                <?php endif; ?>
                                <?= htmlspecialchars($result['test']) ?>
                            </h5>
                            <p class="card-text <?= $result['status'] ? 'test-pass' : 'test-fail' ?>">
                                <?= htmlspecialchars($result['message']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="checkout.php" class="btn btn-primary w-100 mb-2">Go to Checkout</a>
                        <a href="migrate_cashfree.php" class="btn btn-warning w-100 mb-2">Run Migration</a>
                        <a href="CASHFREE_INTEGRATION.md" class="btn btn-info w-100" target="_blank">View Documentation</a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Test Credentials</h5>
                    </div>
                    <div class="card-body">
                        <h6>Test Card (Success)</h6>
                        <p class="small mb-2">
                            <strong>Card:</strong> 4111 1111 1111 1111<br>
                            <strong>CVV:</strong> 123<br>
                            <strong>Expiry:</strong> 12/25
                        </p>
                        
                        <h6>Test UPI</h6>
                        <p class="small mb-0">
                            <strong>UPI ID:</strong> success@payu
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-4">
            <strong>Important:</strong> Delete this test file (cashfree_test.php) before deploying to production.
        </div>
    </div>
</body>
</html>
