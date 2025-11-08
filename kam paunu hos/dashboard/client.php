<?php include_once '../header.php'; redirect_if_not_logged_in(); 
if($_SESSION['user']['role']!='client') header('Location: ../login.php');
$u = $_SESSION['user'];
?>
<h1>My Jobs & Bids</h1>
<?php
$jobs = mysqli_query($conn, "SELECT j.*, c.name as cat FROM jobs j JOIN categories c ON j.category_id=c.id WHERE client_id=".$u['id']);
while($j = mysqli_fetch_assoc($jobs)){
    echo "<div class='card mb-4'>
        <div class='card-body'>
            <h5>".$j['title']." <span class='badge bg-primary'>".$j['cat']."</span></h5>
            <p>Budget: Rs. ".number_format($j['budget'])." | Status: ".$j['status']."</p>";
    
    $bids = mysqli_query($conn, "SELECT b.*, u.name FROM bids b JOIN users u ON b.freelancer_id=u.id WHERE job_id=".$j['id']);
    if(mysqli_num_rows($bids)>0){
        echo "<h6>Bids:</h6><ul>";
        while($b = mysqli_fetch_assoc($bids)){
            echo "<li><b>".$b['name']."</b>: Rs. ".$b['amount']." → ";
            if($b['status']=='pending')
                echo "<a href='../payments/pay_esewa.php?bid=".$b['id']."' class='btn btn-sm btn-success'>Pay & Award</a>";
            else echo "<span class='text-success'>AWARDED</span>";
            echo "</li>";
        }
        echo "</ul>";
    } else echo "<p>No bids yet.</p>";
    echo "</div></div>";
}
?>