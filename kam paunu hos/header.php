<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAM PAUNUHOS - Nepal Freelance Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="chatbot/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include_once 'config.php'; ?>  <!-- FIXED: include_once -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-lg">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            <img src="assets/images/logo.png" width="40" alt="Logo" onerror="this.src='https://via.placeholder.com/40'"> KAM PAUNUHOS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="jobs/view_jobs.php">Browse Jobs</a></li>
                <?php if(isset($_SESSION['user']) && $_SESSION['user']['role']=='client'): ?>
                    <li class="nav-item"><a class="nav-link" href="jobs/post_job.php">Post Job</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="chat/chat.php">Messages</a></li>
            </ul>
            <form class="d-flex me-3" method="GET" action="index.php">
                <input class="form-control me-2" type="search" name="q" placeholder="Search jobs..." value="<?php echo htmlspecialchars($_GET['q']??''); ?>">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            <?php if(isset($_SESSION['user'])): ?>
                <div class="dropdown">
                    <a class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="dashboard/<?php echo $_SESSION['user']['role']; ?>.php">Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                <a href="register.php" class="btn btn-warning">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-4"></div>