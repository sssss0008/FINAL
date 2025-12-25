<?php
include '../config.php';
redirect_if_not_logged_in();

// Example bid fetch (replace with your real logic)
$bid_id = intval($_GET['bid'] ?? 0);
$bid = $conn->query("SELECT b.amount, j.title FROM bids b JOIN jobs j ON b.job_id = j.id WHERE b.id = $bid_id")->fetch_assoc();
if (!$bid) die("Invalid bid");

$amount = (float)$bid['amount'];
$tax_amount = 0;
$service_charge = 0;
$delivery_charge = 0;
$total_amount = $amount + $tax_amount + $service_charge + $delivery_charge;

// MUST BE UNIQUE EVERY TIME (very important!)
$transaction_uuid = "KAMP-" . time() . "-" . uniqid();

// Mandatory signed fields - EXACT order, no spaces after commas
$signed_field_names = "total_amount,transaction_uuid,product_code";

// The message for signature - EXACT format
$message = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=" . ESEWA_PRODUCT_CODE;

// Generate HMAC SHA256 signature (raw binary)
$signature_raw = hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true);
// Convert to base64
$signature = base64_encode($signature_raw);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to eSewa...</title>
</head>
<body>
    <h3>Redirecting to eSewa secure payment...</h3>

    <form id="esewaForm" action="<?php echo ESEWA_URL; ?>" method="POST">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
        <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
        <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
        <input type="hidden" name="product_code" value="<?php echo ESEWA_PRODUCT_CODE; ?>">
        <input type="hidden" name="product_service_charge" value="<?php echo $service_charge; ?>">
        <input type="hidden" name="product_delivery_charge" value="<?php echo $delivery_charge; ?>">
        <input type="hidden" name="success_url" value="<?php echo SUCCESS_URL; ?>">
        <input type="hidden" name="failure_url" value="<?php echo FAILURE_URL; ?>">
        <input type="hidden" name="signed_field_names" value="<?php echo $signed_field_names; ?>">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
    </form>

    <script>
        document.getElementById('esewaForm').submit();
    </script>
</body>
</html>