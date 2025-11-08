<?php include '../config.php';
if(isset($_SESSION['user'])){
    $msg = $_GET['msg'];
    $job_id = $_GET['job_id'];
    $sender = $_SESSION['user']['id'];
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, message, job_id) VALUES ($sender, 0, '$msg', $job_id)");
}
?>