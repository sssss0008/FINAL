<?php include '../config.php';
$job_id = $_GET['job_id'];
$msgs = mysqli_query($conn, "SELECT m.*, u.name FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.job_id=$job_id ORDER BY timestamp");
while($m = mysqli_fetch_assoc($msgs)){
    echo "<div style='margin:10px 0; padding:10px; background:#f0f0f0; border-radius:10px;'>
        <b>".$m['name'].":</b> ".$m['message']."<br>
        <small>".$m['timestamp']."</small>
    </div>";
}
?>