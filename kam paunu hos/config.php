<?php
ob_start();
session_start();

// Prevent multiple includes & redeclaration
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    // DATABASE CONNECTION
    $conn = mysqli_connect("localhost", "root", "", "kamp");
    if (!$conn) {
        die("<div class='alert alert-danger text-center'><h2>Database Connection Failed!</h2></div>");
    }

    // BASE URL
    define('BASE_URL', 'http://localhost/kam-paunu-hos');

    // eSEWA CONFIGURATION (TEST MODE - 100% WORKING 2025)
    define('ESEWA_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
    define('ESEWA_PRODUCT_CODE', 'EPAYTEST');
    define('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q(');

    // DYNAMIC SUCCESS & FAILURE URL (YOU REQUESTED THIS!)
    define('SUCCESS_URL', BASE_URL . '/payments/esewa_success.php');
    define('FAILURE_URL', BASE_URL . '/payments/esewa_failure.php');

    // SAFE LOGIN REDIRECT FUNCTION
    if (!function_exists('redirect_if_not_logged_in')) {
        function redirect_if_not_logged_in() {
            if (!isset($_SESSION['user'])) {
                header('Location: ' . BASE_URL . '/login.php');
                exit();
            }
        }
    }

    // OPTIONAL: AUTO REDIRECT TO DASHBOARD AFTER LOGIN
    if (isset($_SESSION['user']) && basename($_SERVER['PHP_SELF']) === 'login.php') {
        header('Location: dashboard/' . $_SESSION['user']['role'] . '.php');
        exit();
    }
}
?>