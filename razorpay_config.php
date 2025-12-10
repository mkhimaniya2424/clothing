<?php
require 'vendor/autoload.php';

use Razorpay\Api\Api;

// -------------------------------------------------------------------------
// RAZORPAY CONFIGURATION
// -------------------------------------------------------------------------
// Please replace the values below with your actual Razorpay API Keys.
// You can get them from https://dashboard.razorpay.com/app/keys
// -------------------------------------------------------------------------

$keyId = 'YOUR_KEY_ID';         // Enter your Key ID here
$keySecret = 'YOUR_KEY_SECRET'; // Enter your Key Secret here
$displayCurrency = 'INR';

$api = new Api($keyId, $keySecret);

// Error reporting for debugging (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
