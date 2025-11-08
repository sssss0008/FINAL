<?php
include '../config.php';

$data = $_GET['data'] ?? '';
if(!$data) die("No data received!");

$decoded = json_decode(base64_decode($data), true);
$transaction_uuid = $decoded['transaction_uuid'] ?? '';
$status = $decoded['status'] ?? '';
$ref_id = $decoded['ref_id'] ?? '';

if($status === 'COMPLETE') {
    // Update payment
    mysqli_query($conn, "UPDATE payments SET status='success', ref_id='$ref_id' WHERE transaction_uuid='$transaction_uuid'");
    
    // Get bid
    $payment = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM payments WHERE transaction_uuid='$transaction_uuid'"));
    $bid_id = $payment['bid_id'];
    
    // Award job
    mysqli_query($conn, "UPDATE bids SET status='paid' WHERE id=$bid_id");
    $bid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT job_id, freelancer_id FROM bids WHERE id=$bid_id"));
    mysqli_query($conn, "UPDATE jobs SET status='awarded', awarded_to={$bid['freelancer_id']} WHERE id={$bid['job_id']}");
    
    echo "<div class='alert alert-success text-center'>
            <h1>PAYMENT SUCCESSFUL!</h1>
            <p>Ref ID: <b>$ref_id</b></p>
            <p>Job has been awarded to freelancer!</p>
            <a href='../dashboard/client.php' class='btn btn-primary'>Back to Dashboard</a>
          </div>";
} else {
    echo "<div class='alert alert-danger'>Payment failed or cancelled!</div>";
}
?>