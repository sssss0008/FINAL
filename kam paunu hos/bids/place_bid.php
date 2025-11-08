<?php
include '../config.php';
redirect_if_not_logged_in();

if(isset($_POST['place_bid'])) {
    $job_id = intval($_POST['job_id']);
    $amount = floatval($_POST['amount']);
    $proposal = mysqli_real_escape_string($conn, $_POST['proposal']);
    $freelancer_id = $_SESSION['user']['id'];

    // Prevent duplicate bid
    $check = mysqli_query($conn, "SELECT * FROM bids WHERE job_id=$job_id AND freelancer_id=$freelancer_id");
    if(mysqli_num_rows($check) > 0) {
        header("Location: ../jobs/job_details.php?id=$job_id&msg=already_bid");
        exit();
    }

    $q = mysqli_query($conn, "INSERT INTO bids (job_id, freelancer_id, amount, proposal) 
                              VALUES ($job_id, $freelancer_id, $amount, '$proposal')");

    if($q) {
        header("Location: ../jobs/job_details.php?id=$job_id&msg=bid_success");
    } else {
        header("Location: ../jobs/job_details.php?id=$job_id&msg=error");
    }
    exit();
}
?>