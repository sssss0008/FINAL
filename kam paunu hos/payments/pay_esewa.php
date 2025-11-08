<?php 
include '../config.php';
redirect_if_not_logged_in();

$bid_id = intval($_GET['bid']);
$bid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT b.*, j.budget, j.title FROM bids b JOIN jobs j ON b.job_id=j.id WHERE b.id=$bid_id"));
if(!$bid || $bid['status'] != 'pending') die("Invalid bid!");

$amount = $bid['amount'];
$transaction_uuid = "KAMP-" . time() . "-" . $bid_id;
$product_code = "EPAYTEST";
$secret_key = "8gBm/:&EnhH.1/q(";  // OFFICIAL TEST SECRET

// OFFICIAL eSEWA SIGNATURE METHOD (2025)
$message = "total_amount={$amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
$signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));

// SUCCESS & FAILURE URL
$success_url = BASE_URL . "/payments/esewa_success.php?bid={$bid_id}";
$failure_url = BASE_URL . "/payments/esewa_failure.php";

mysqli_query($conn, "INSERT INTO payments (bid_id, transaction_uuid, amount, status) VALUES ($bid_id, '$transaction_uuid', $amount, 'pending')");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to eSewa...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div style="text-align:center; margin-top:100px;">
        <h2>Redirecting to eSewa...</h2>
        <p>Please wait</p>
    </div>

    <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST" id="esewaForm">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="tax_amount" value="0">
        <input type="hidden" name="total_amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
        <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
        <input type="hidden" name="product_service" value="">
        <input type="hidden" name="product_delivery" value="">
        <input type="hidden" name="success_url" value="<?php echo $success_url; ?>">
        <input type="hidden" name="failure_url" value="<?php echo $failure_url; ?>">
        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
    </form>

    <script>
        document.getElementById('esewaForm').submit();
        Swal.fire({
            title: 'Redirecting...',
            text: 'Opening eSewa payment gateway',
            icon: 'info',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
</body>
</html>