<?php
require_once 'cashfree_config.php';

header('Content-Type: text/plain');

echo "Debugging Cashfree Connection...\n";
echo "--------------------------------\n";

// 1. Check Configuration
echo "App ID: " . CASHFREE_APP_ID . "\n";
echo "Secret Key: " . substr(CASHFREE_SECRET_KEY, 0, 5) . "...\n";
echo "Base URL: " . CASHFREE_API_BASE_URL . "\n";
echo "API Version: " . CASHFREE_API_VERSION . "\n";

$endpoint = getCashfreeEndpoint('/orders');
echo "Target Endpoint: " . $endpoint . "\n";

// 2. Prepare Dummy Data
$order_id = 'debug_' . time();
$orderData = [
    'order_id' => $order_id,
    'order_amount' => 1.00,
    'order_currency' => 'INR',
    'customer_details' => [
        'customer_id' => 'debug_user_01',
        'customer_name' => 'Debug User',
        'customer_email' => 'debug@example.com',
        'customer_phone' => '9999999999'
    ],
    'order_meta' => [
        'return_url' => 'http://localhost/return'
    ]
];

$payload = json_encode($orderData);
echo "Payload: " . $payload . "\n\n";

// 3. Make Request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, getCashfreeHeaders());
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

echo "Sending Request...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

if ($curlError) {
    echo "cURL Error: " . $curlError . "\n";
}

// Print verbose info
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "\nVerbose Log:\n" . $verboseLog . "\n";

curl_close($ch);
?>
