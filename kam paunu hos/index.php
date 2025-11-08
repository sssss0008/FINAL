<?php include 'header.php'; ?>

<div class="hero text-center text-white py-5 rounded-4 mb-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hero-bg.jpg') center/cover no-repeat;">
    <h1 class="display-3 fw-bold">Welcome to KAM PAUNUHOS</h1>
    <p class="lead fs-4">Hire Top Nepali Talent or Earn from Home!</p>
    <a href="register.php" class="btn btn-success btn-lg px-5">Get Started Free</a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="text-center p-4 bg-white rounded shadow">
            <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
            <h3>500+ Jobs</h3>
            <p>Daily new opportunities</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="text-center p-4 bg-white rounded shadow">
            <i class="fas fa-users fa-3x text-success mb-3"></i>
            <h3>1000+ Freelancers</h3>
            <p>Skilled professionals</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="text-center p-4 bg-white rounded shadow">
            <i class="fas fa-shield-alt fa-3x text-warning mb-3"></i>
            <h3>100% Secure</h3>
            <p>eSewa </p>
        </div>
    </div>
</div>

<h2 class="text-center mb-5">Latest Jobs</h2>
<div class="row">
<?php
$search = $_GET['q'] ?? '';
$sql = "SELECT j.*, u.name FROM jobs j JOIN users u ON j.client_id=u.id WHERE j.status='open'";
if($search) $sql .= " AND (j.title LIKE '%$search%' OR j.category LIKE '%$search%' OR j.description LIKE '%$search%')";
$sql .= " ORDER BY j.created_at DESC LIMIT 12";
$jobs = mysqli_query($conn, $sql);
if(mysqli_num_rows($jobs) == 0) {
    echo "<p class='text-center'>No jobs found. <a href='jobs/post_job.php'>Post one now!</a></p>";
}
while($j = mysqli_fetch_assoc($jobs)){ ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-lg border-0 hover-card">
            <img src="uploads/<?php echo $j['image']; ?>" class="card-img-top" alt="job" style="height:200px; object-fit:cover;">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars($j['title']); ?></h5>
                <p class="card-text">
                    <strong class="text-success">Rs. <?php echo number_format($j['budget']); ?></strong><br>
                    <span class="badge bg-primary"><?php echo $j['category']; ?></span><br>
                    <small class="text-muted">by <?php echo $j['name']; ?></small>
                </p>
                <div class="mt-auto">
                    <a href="jobs/job_details.php?id=<?php echo $j['id']; ?>" class="btn btn-primary w-100">View & Bid</a>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div>

<?php include 'footer.php'; ?>