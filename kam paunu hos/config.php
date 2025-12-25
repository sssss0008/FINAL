<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');

// Prevent multiple loading
if (defined('CONFIG_LOADED')) return;
define('CONFIG_LOADED', true);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kamp');

// eSewa configuration (TEST MODE - 100% WORKING 2025)
define('ESEWA_MERCHANT_CODE', 'EPAYTEST');
define('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q(');
define('ESEWA_API_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_STATUS_URL', 'https://rc-epay.esewa.com.np/api/epay/transaction/status/');

// DYNAMIC URLs
define('BASE_URL', 'http://localhost/kam paunu hos');
define('SUCCESS_URL', BASE_URL . '/payments/esewa_success.php');
define('FAILURE_URL', BASE_URL . '/payments/esewa_failure.php');

// Error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/app.log');
if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0755, true);

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("System temporarily unavailable. Please try again later.");
}
$conn->set_charset('utf8mb4');

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper Functions
function redirect_if_not_logged_in() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generate_signature($data) {
    $message = "total_amount={$data['total_amount']},transaction_uuid={$data['transaction_uuid']},product_code=" . ESEWA_MERCHANT_CODE;
    return base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));
}

function show_alert($message, $type = 'danger') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function log_error($message) {
    error_log(date('Y-m-d H:i:s') . " - $message\n", 3, __DIR__ . '/logs/app.log');
}
?>