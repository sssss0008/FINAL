<?php include '../header.php'; redirect_if_not_logged_in(); 
if($_SESSION['user']['role']!='admin') header('Location: ../login.php');
?>
<h1>ADMIN PANEL</h1>
<div class="row">
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>Total Users</h5>
                <h2><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users")); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Jobs</h5>
                <h2><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM jobs")); ?></h2>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-5">Recent Payments</h2>
<table class="table table-striped">
    <thead><tr><th>Freelancer</th><th>Job</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php
    $pays = mysqli_query($conn, "SELECT p.*, u.name, j.title FROM payments p JOIN bids b ON p.bid_id=b.id JOIN users u ON b.freelancer_id=u.id JOIN jobs j ON b.job_id=j.id ORDER BY p.created_at DESC LIMIT 10");
    while($p = mysqli_fetch_assoc($pays)){
        echo "<tr><td>".$p['name']."</td><td>".$p['title']."</td><td>Rs.".$p['amount']."</td><td><span class='badge bg-success'>".$p['status']."</span></td><td>".$p['created_at']."</td></tr>";
    }
    ?>
    </tbody>
</table>
<?php include '../footer.php'; ?>