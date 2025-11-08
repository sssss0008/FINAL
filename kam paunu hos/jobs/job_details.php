<?php include_once '../header.php'; 
redirect_if_not_logged_in(); // Must be logged in

// GET JOB ID SAFELY
$job_id = intval($_GET['id'] ?? 0);
if($job_id <= 0) {
    echo "<div class='alert alert-danger text-center'>Invalid Job ID!</div>";
    include '../footer.php';
    exit();
}

// FETCH JOB WITH ERROR CHECK
$job_query = mysqli_query($conn, "SELECT j.*, u.name AS client_name FROM jobs j JOIN
                                 JOIN users u ON j.client_id = u.id 
                                 WHERE j.id = $job_id AND j.status = 'open'");

if(mysqli_num_rows($job_query) == 0) {
    echo "<div class='alert alert-warning text-center'>Job not found or already awarded!</div>";
    include '../footer.php';
    exit();
}

$job = mysqli_fetch_assoc($job_query);
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <h1 class="card-title text-primary"><?php echo htmlspecialchars($job['title']); ?></h1>
                
                <?php if($job['image'] && file_exists("../uploads/".$job['image'])): ?>
                    <img src="../uploads/<?php echo $job['image']; ?>" class="img-fluid rounded mb-3" style="max-height:400px; object-fit:cover;" alt="Job Image">
                <?php else: ?>
                    <img src="../assets/images/default-job.jpg" class="img-fluid rounded mb-3" style="max-height:400px; object-fit:cover;" alt="Default Job">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Budget:</strong> 
                            <span class="fs-4 text-success fw-bold">Rs. <?php echo number_format($job['budget']); ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Category:</strong> 
                            <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($job['category']); ?></span>
                        </p>
                    </div>
                </div>

                <p><strong>Posted by:</strong> <?php echo htmlspecialchars($job['client_name']); ?></p>
                <p><strong>Posted on:</strong> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>

                <hr>
                <h5>Description</h5>
                <p class="lead"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
            </div>
        </div>

        <!-- PLACE BID FORM -->
        <?php if($_SESSION['user']['role'] == 'freelancer'): ?>
            <div class="card shadow-lg mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Place Your Bid</h4>
                </div>
                <div class="card-body">
                    <?php 
                    // Check if already bid
                    $already_bid = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bids WHERE job_id=$job_id AND freelancer_id=".$_SESSION['user']['id'])) > 0;
                    if($already_bid): ?>
                        <div class="alert alert-info">You have already placed a bid on this job.</div>
                    <?php else: ?>
                        <form method="POST" action="../bids/place_bid.php">
                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                            <div class="mb-3">
                                <label>Your Bid Amount (NPR)</label>
                                <input type="number" name="amount" class="form-control form-control-lg" min="1000" max="<?php echo $job['budget']*2; ?>" placeholder="e.g. 45000" required>
                            </div>
                            <div class="mb-3">
                                <label>Your Proposal</label>
                                <textarea name="proposal" class="form-control" rows="5" placeholder="Why should client hire you? Mention timeline, skills, experience..." required></textarea>
                            </div>
                            <button type="submit" name="place_bid" class="btn btn-success btn-lg w-100">
                                Place Bid Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- BIDS LIST -->
        <h3>Bids (<?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bids WHERE job_id=$job_id")); ?>)</h3>
        <div class="row">
            <?php
            $bids = mysqli_query($conn, "SELECT b.*, u.name FROM bids b 
                                        JOIN users u ON b.freelancer_id = u.id 
                                        WHERE b.job_id = $job_id 
                                        ORDER BY b.amount ASC");

            if(mysqli_num_rows($bids) == 0):
                echo "<p class='text-muted'>No bids yet. Be the first!</p>";
            else:
                while($b = mysqli_fetch_assoc($bids)):
            ?>
                <div class="col-md-6 mb-3">
                    <div class="card border <?php echo $b['status']=='paid'?'border-success':''; ?>">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($b['name']); ?></h6>
                            <p class="text-success fw-bold">Rs. <?php echo number_format($b['amount']); ?></p>
                            <p class="small text-muted"><?php echo nl2br(htmlspecialchars($b['proposal'])); ?></p>
                            <small class="text-muted">Bid on: <?php echo date('M d, Y', strtotime($b['created_at'])); ?></small>

                            <?php if(isset($_SESSION['user']) && $_SESSION['user']['id'] == $job['client_id'] && $b['status']=='pending'): ?>
                                <hr>
                                <a href="../payments/pay_esewa.php?bid=<?php echo $b['id']; ?>" class="btn btn-sm btn-success w-100">
                                    Pay & Award Job
                                </a>
                            <?php elseif($b['status']=='paid'): ?>
                                <span class="badge bg-success float-end">AWARDED</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            endif;
            ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-lg sticky-top" style="top:100px;">
            <div class="card-body text-center">
                <h5>Job Summary</h5>
                <hr>
                <p><strong>Budget:</strong> Rs. <?php echo number_format($job['budget']); ?></p>
                <p><strong>Bids:</strong> <?php echo mysqli_num_rows($bids); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-<?php echo $job['status']=='open'?'success':'warning'; ?>">
                        <?php echo ucfirst($job['status']); ?>
                    </span>
                </p>
                <a href="view_jobs.php" class="btn btn-outline-primary w-100">Back to Jobs</a>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>