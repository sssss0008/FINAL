<?php
include '../header.php'; 
redirect_if_not_logged_in(); 
if($_SESSION['user']['role'] != 'freelancer') header('Location: ../login.php');
$u = $_SESSION['user'];
include '../recommend.php';

$recommendations = recommendJobs($conn, $u['id']);
?>

<h2>Recommended Jobs For You</h2>
<div class="row">
    <?php foreach($recommendations as $rec): $j = $rec['job']; ?>
        <div class="col-md-6 mb-4">
            <div class="card border-<?php echo $rec['badge']=='bg-success'?'success':($rec['badge']=='bg-warning'?'warning':'secondary'); ?>">
                <div class="card-header">
                    <span class="badge <?php echo $rec['badge']; ?>">
                        <?php echo $rec['match_level']; ?> Match (Score: <?php echo $rec['score']; ?>)
                    </span>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($j['title']); ?></h5>
                    <p>Rs. <?php echo number_format($j['budget']); ?> | <?php echo $j['category']; ?></p>
                    <a href="../jobs/job_details.php?id=<?php echo $j['id']; ?>" class="btn btn-primary">View & Bid</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- If you want ALL JOBS fallback (optional) -->
<h2 class="mt-5">All Available Jobs</h2>
<!-- ... copy your all-jobs loop from previous freelancer.php ... -->