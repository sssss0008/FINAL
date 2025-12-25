<?php 
include_once '../header.php'; 
redirect_if_not_logged_in(); 
if($_SESSION['user']['role'] !== 'client') {
    header('Location: ../login.php');
    exit();
}
$u = $_SESSION['user'];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-briefcase"></i> My Posted Jobs</h1>
        <a href="../jobs/post_job.php" class="btn btn-success btn-lg">
            <i class="fas fa-plus"></i> Post New Job
        </a>
    </div>

    <?php
    // FIXED QUERY – category is VARCHAR, not category_id
    $jobs_query = $conn->query("SELECT j.*, c.name as category_name 
                                FROM jobs j 
                                LEFT JOIN categories c ON j.category = c.name 
                                WHERE j.client_id = {$u['id']} 
                                ORDER BY j.created_at DESC");

    if ($jobs_query->num_rows == 0): ?>
        <div class="text-center py-5 bg-white rounded shadow">
            <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
            <h3>No jobs posted yet!</h3>
            <p>Start hiring freelancers today.</p>
            <a href="../jobs/post_job.php" class="btn btn-primary btn-lg">Post Your First Job</a>
        </div>
    <?php else: 
        while($j = $jobs_query->fetch_assoc()): ?>
            <div class="card shadow-lg mb-4 border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <?php echo htmlspecialchars($j['title']); ?>
                        <span class="badge bg-light text-dark float-end">
                            <?php echo htmlspecialchars($j['category_name'] ?? $j['category'] ?? 'Uncategorized'); ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="card-text">
                                <?php echo nl2br(htmlspecialchars($j['description'])); ?>
                            </p>
                            <p class="mb-1">
                                <strong>Budget:</strong> 
                                <span class="text-success fs-5">Rs. <?php echo number_format($j['budget']); ?></span>
                            </p>
                            <p class="mb-1">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php echo $j['status']=='open'?'success':($j['status']=='awarded'?'warning':'secondary'); ?>">
                                    <?php echo ucfirst($j['status']); ?>
                                </span>
                            </p>
                            <small class="text-muted">
                                Posted on: <?php echo date('M d, Y \a\t h:i A', strtotime($j['created_at'])); ?>
                            </small>
                        </div>
                        <div class="col-md-4">
                            <?php if($j['image'] && file_exists("../uploads/".$j['image'])): ?>
                                <img src="../uploads/<?php echo $j['image']; ?>" class="img-fluid rounded" style="max-height:200px; object-fit:cover;">
                            <?php else: ?>
                                <img src="../assets/images/default-job.jpg" class="img-fluid rounded" style="max-height:200px; object-fit:cover;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <h6><i class="fas fa-gavel"></i> Bids (<?php 
                        $bid_count = $conn->query("SELECT COUNT(*) as total FROM bids WHERE job_id={$j['id']}")->fetch_assoc();
                        echo $bid_count['total'];
                    ?>)</h6>

                    <?php
                    $bids = $conn->query("SELECT b.*, u.name as freelancer_name 
                                          FROM bids b 
                                          JOIN users u ON b.freelancer_id = u.id 
                                          WHERE b.job_id = {$j['id']} 
                                          ORDER BY b.amount ASC");

                    if ($bids->num_rows == 0): ?>
                        <p class="text-muted">No bids yet. Be patient!</p>
                    <?php else: ?>
                        <div class="row">
                            <?php while($b = $bids->fetch_assoc()): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 <?php echo $b['status']=='paid'?'border-success bg-light':''; ?>">
                                        <strong><?php echo htmlspecialchars($b['freelancer_name']); ?></strong>
                                        <br><span class="text-success fw-bold">Rs. <?php echo number_format($b['amount']); ?></span>
                                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($b['created_at'])); ?></small>
                                        <p class="mt-2 mb-2"><em><?php echo htmlspecialchars($b['proposal']); ?></em></p>

                                        <?php if($b['status'] == 'pending' && $j['status'] == 'open'): ?>
                                            <a href="../payments/pay_esewa.php?bid=<?php echo $b['id']; ?>" 
                                               class="btn btn-success btn-sm w-100">
                                                <i class="fab fa-esewa"></i> Pay & Award Job
                                            </a>
                                        <?php elseif($b['status'] == 'paid'): ?>
                                            <span class="badge bg-success float-end">AWARDED & PAID</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; 
    endif; ?>
</div>

<?php include '../footer.php'; ?>