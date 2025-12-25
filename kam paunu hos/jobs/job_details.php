<?php 
include_once '../header.php'; 
redirect_if_not_logged_in();

$job_id = intval($_GET['id'] ?? 0);
if ($job_id <= 0) {
    echo "<div class='alert alert-danger text-center'>Invalid Job ID!</div>";
    include '../footer.php';
    exit();
}

// FIXED QUERY – clean & safe
$job_query = $conn->query("SELECT j.*, u.name AS client_name 
                           FROM jobs j 
                           JOIN users u ON j.client_id = u.id 
                           WHERE j.id = $job_id");

if ($job_query->num_rows == 0) {
    echo "<div class='alert alert-warning text-center'>Job not found or removed!</div>";
    include '../footer.php';
    exit();
}

$job = $job_query->fetch_assoc();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h1 class="card-title text-primary"><?php echo htmlspecialchars($job['title']); ?></h1>

                    <?php 
                    // Image display - fixed path + fallback
                    $img_path = file_exists("../uploads/" . $job['image']) 
                                ? "../uploads/" . $job['image'] 
                                : "../assets/images/default-job.jpg";
                    ?>
                    <img src="<?php echo $img_path; ?>" 
                         class="img-fluid rounded mb-4 shadow" 
                         style="max-height:500px; object-fit:cover; width:100%;" 
                         alt="<?php echo htmlspecialchars($job['title']); ?>">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Budget:</strong> 
                                <span class="fs-4 text-success fw-bold">Rs. <?php echo number_format($job['budget']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Category:</strong> 
                                <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($job['category']); ?></span>
                            </p>
                        </div>
                    </div>

                    <p class="mb-2">
                        <strong>Posted by:</strong> <?php echo htmlspecialchars($job['client_name']); ?>
                    </p>

                    <!-- FIXED: Safe date display with fallback -->
                    <p class="mb-4">
                        <strong>Posted on:</strong> 
                        <?php 
                        if (!empty($job['created_at']) && $job['created_at'] !== '0000-00-00 00:00:00') {
                            echo date('F d, Y \a\t g:i A', strtotime($job['created_at']));
                        } else {
                            echo "Recently";
                        }
                        ?>
                    </p>

                    <hr>

                    <h5>Description</h5>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                </div>
            </div>

            <!-- PLACE BID FORM -->
            <?php if ($_SESSION['user']['role'] == 'freelancer'): ?>
                <div class="card shadow-lg mt-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-gavel"></i> Place Your Bid</h4>
                    </div>
                    <div class="card-body">
                        <?php 
                        $already_bid = $conn->query("SELECT * FROM bids WHERE job_id=$job_id AND freelancer_id=".$_SESSION['user']['id'])->num_rows > 0;
                        if ($already_bid): ?>
                            <div class="alert alert-info">You have already placed a bid on this job.</div>
                        <?php else: ?>
                            <form method="POST" action="../bids/place_bid.php">
                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                <div class="mb-3">
                                    <label>Your Bid Amount (NPR)</label>
                                    <input type="number" name="amount" class="form-control form-control-lg" 
                                           min="1000" max="<?php echo $job['budget'] * 2; ?>" 
                                           placeholder="e.g. 45000" required>
                                </div>
                                <div class="mb-3">
                                    <label>Your Proposal</label>
                                    <textarea name="proposal" class="form-control" rows="5" 
                                              placeholder="Why should the client hire you?..." required></textarea>
                                </div>
                                <button type="submit" name="place_bid" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-paper-plane"></i> Submit Bid
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BIDS LIST -->
            <h3 class="mt-5">Bids Received</h3>
            <?php
            $bids = $conn->query("SELECT b.*, u.name AS freelancer_name 
                                  FROM bids b 
                                  JOIN users u ON b.freelancer_id = u.id 
                                  WHERE b.job_id = $job_id 
                                  ORDER BY b.amount ASC");

            if ($bids->num_rows == 0): ?>
                <p class="text-muted">No bids yet. Be the first freelancer to bid!</p>
            <?php else: ?>
                <div class="row">
                    <?php while ($b = $bids->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card <?php echo $b['status']=='paid' ? 'border-success' : ''; ?>">
                                <div class="card-body">
                                    <h6><?php echo htmlspecialchars($b['freelancer_name']); ?></h6>
                                    <p class="text-success fw-bold">Rs. <?php echo number_format($b['amount']); ?></p>
                                    <p class="small"><em><?php echo htmlspecialchars($b['proposal']); ?></em></p>
                                    <small class="text-muted">Bid on: 
                                        <?php 
                                        echo !empty($b['created_at']) && $b['created_at'] !== '0000-00-00 00:00:00' 
                                            ? date('M d, Y', strtotime($b['created_at'])) 
                                            : "Recently";
                                        ?>
                                    </small>

                                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $job['client_id'] && $b['status']=='pending'): ?>
                                        <hr>
                                        <a href="../payments/pay_esewa.php?bid=<?php echo $b['id']; ?>" 
                                           class="btn btn-success btn-sm w-100">
                                            <i class="fab fa-esewa"></i> Pay & Award Job
                                        </a>
                                    <?php elseif ($b['status']=='paid'): ?>
                                        <span class="badge bg-success float-end">AWARDED & PAID</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">
            <div class="card shadow-lg sticky-top" style="top:100px;">
                <div class="card-body">
                    <h5>Job Summary</h5>
                    <hr>
                    <p><strong>Budget:</strong> Rs. <?php echo number_format($job['budget']); ?></p>
                    <p><strong>Bids:</strong> <?php echo $bids->num_rows; ?></p>
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
</div>

<?php include '../footer.php'; ?>