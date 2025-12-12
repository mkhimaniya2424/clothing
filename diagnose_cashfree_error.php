<?php
require_once 'cashfree_config.php';

header('Content-Type: text/plain');

echo "Diagnosing Cashfree Error: 'endpoint or method is not valid'\n";
echo "---------------------------------------------------------\n";

// Helper to make request
function test_cashfree_request($label, $url, $payload, $method = 'POST') {
    echo "\nTest: $label\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, getCashfreeHeaders());
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    
    if (strpos($response, 'endpoint or method is not valid') !== false) {
        echo ">>> MATCH FOUND! This scenario reproduces the error.\n";
    }
}

// Prepare valid payload
$orderData = [
    'order_id' => 'diag_' . time(),
    'order_amount' => 1.00,
    'order_currency' => 'INR',
    'customer_details' => [
        'customer_id' => 'diag_user',
        'customer_name' => 'Diag User',
        'customer_email' => 'diag@example.com',
        'customer_phone' => '9999999999'
    ],
    'order_meta' => [
        'return_url' => 'http://localhost/return'
    ]
];
$validPayload = json_encode($orderData);

// 1. Control Test (Should Work)
test_cashfree_request("Control (Correct URL)", getCashfreeEndpoint('/orders'), $validPayload);

// 2. Trailing Slash
test_cashfree_request("Trailing Slash", getCashfreeEndpoint('/orders/'), $validPayload);

// 3. Missing /pg
test_cashfree_request("Missing /pg", CASHFREE_API_BASE_URL . '/orders', $validPayload);

// 4. GET Method
test_cashfree_request("GET Method", getCashfreeEndpoint('/orders'), null, 'GET');

// 5. Empty Body
test_cashfree_request("Empty Body", getCashfreeEndpoint('/orders'), '');

// 6. Malformed JSON
test_cashfree_request("Malformed JSON", getCashfreeEndpoint('/orders'), '{bad_json}');

?>
