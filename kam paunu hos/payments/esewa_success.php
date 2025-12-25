<?php
require '../config.php';

$data = $_GET['data'] ?? '';
if (!$data) die(show_alert("No payment data received!"));

$decoded = json_decode(base64_decode($data), true);
$status = $decoded['status'] ?? '';
$ref_id = $decoded['ref_id'] ?? '';
$transaction_uuid = $decoded['transaction_uuid'] ?? '';

if ($status === 'COMPLETE') {
    $payment = $conn->query("SELECT * FROM payments WHERE transaction_uuid='$transaction_uuid'")->fetch_assoc();
    if ($payment && $payment['status'] === 'pending') {
        $bid_id = $payment['bid_id'];
        
        $conn->query("UPDATE payments SET status='success', ref_id='$ref_id' WHERE transaction_uuid='$transaction_uuid'");
        $conn->query("UPDATE bids SET status='paid' WHERE id=$bid_id");
        
        $bid = $conn->query("SELECT job_id, freelancer_id FROM bids WHERE id=$bid_id")->fetch_assoc();
        $conn->query("UPDATE jobs SET status='awarded', awarded_to={$bid['freelancer_id']} WHERE id={$bid['job_id']}");
        
        echo show_alert("<h3>PAYMENT SUCCESSFUL!</h3>
                        <p>Reference ID: <b>$ref_id</b></p>
                        <p>Job awarded successfully!</p>
                        <a href='../dashboard/client.php' class='btn btn-success'>Back to Dashboard</a>", "success");
    }
} else {
    echo show_alert("Payment failed or cancelled!", "danger");
}
?>