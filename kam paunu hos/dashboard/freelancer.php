<?php include '../header.php'; redirect_if_not_logged_in(); 
if($_SESSION['user']['role']!='freelancer') header('Location: ../login.php');
$u = $_SESSION['user'];
?>
<h1>Freelancer Dashboard - <?php echo $u['name']; ?></h1>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Bids Placed</h5>
                <h2><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bids WHERE freelancer_id=".$u['id'])); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Jobs Won</h5>
                <h2><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bids WHERE freelancer_id=".$u['id']." AND status='paid'")); ?></h2>
            </div>
        </div>
    </div>
</div>

<h2>Recommended Jobs For You</h2>
<?php include '../index.php'; ?>

<?php include '../footer.php'; ?>