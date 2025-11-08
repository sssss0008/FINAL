<?php
$message = strtolower($_POST['message'] ?? '');

$responses = [
    "hello|hi|namaste" => "Namaste! Welcome to KAM PAUNUHOS! How can I help you?",
    "how are you" => "I'm doing great! Ready to help you earn or hire!",
    "how to post job" => "Login as Client → Dashboard → Post New Job → Fill form → Done!",
    "how to bid" => "Browse Jobs → Click View → Place Bid → Win job!",
    "payment|esewa" => "Client pays via eSewa → Job awarded automatically! Safe & Fast!",
    "login" => "Use: admin@kamp.com / admin123<br>ram@kamp.com / ram123<br>sita@kamp.com / sita123",
    ".*" => "Great question! Here are quick links:<br>
            • <a href='jobs/post_job.php'>Post a Job</a><br>
            • <a href='jobs/view_jobs.php'>Browse Jobs</a><br>
            • <a href='dashboard/freelancer.php'>Dashboard</a>"
];

foreach ($responses as $pattern => $response) {
    if (preg_match("/$pattern/i", $message)) {
        echo $response;
        exit();
    }
}
echo "I'm still learning! Ask me anything about KAM PAUNUHOS!";
?>