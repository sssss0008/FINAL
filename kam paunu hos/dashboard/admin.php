<?php 
require_once '../config.php'; 
redirect_if_not_logged_in(); 
if($_SESSION['user']['role'] !== 'admin') die("<h1>Access Denied!</h1>");

// GET CURRENT TAB
$tab = $_GET['tab'] ?? 'dashboard';

// HANDLE ACTIONS
if(isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $conn->query("INSERT INTO categories (name) VALUES ('$name')");
    header("Location: admin.php?tab=categories&success=added");
}

if(isset($_GET['delete_category'])) {
    $id = intval($_GET['delete_category']);
    $conn->query("DELETE FROM categories WHERE id=$id");
    header("Location: admin.php?tab=categories&deleted=1");
}

if(isset($_POST['add_user'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $conn->query("INSERT INTO users (name,email,password,role) VALUES ('$name','$email','$pass','$role')");
    header("Location: admin.php?tab=users&success=added");
}

if(isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$id AND role != 'admin'");
    header("Location: admin.php?tab=users&deleted=1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - KAM PAUNUHOS</title>
    <?php include '../header.php'; ?>
    <style>
        .admin-nav { background: #1a1a2e; padding: 15px; border-radius: 10px; }
        .admin-nav a { color: white; margin: 0 15px; font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4 text-primary">
        <i class="fas fa-crown"></i> ADMIN PANEL - KAM PAUNUHOS
    </h1>

    <!-- ADMIN NAV -->
    <div class="admin-nav text-center mb-4">
        <a href="?tab=dashboard" class="<?php echo $tab=='dashboard'?'text-warning':''; ?>">Dashboard</a> |
        <a href="?tab=categories" class="<?php echo $tab=='categories'?'text-warning':''; ?>">Categories</a> |
        <a href="?tab=users" class="<?php echo $tab=='users'?'text-warning':''; ?>">Users</a> |
        <a href="?tab=revenue" class="<?php echo $tab=='revenue'?'text-warning':''; ?>">Revenue</a>
    </div>

    <!-- DASHBOARD TAB -->
    <div class="tab-content <?php echo $tab=='dashboard'?'active':''; ?>">
        <div class="row">
            <div class="col-md-4">
                <div class="card stats-card text-center p-4">
                    <h3><?php echo $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0]; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card text-center p-4">
                    <h3><?php echo $conn->query("SELECT COUNT(*) FROM jobs")->fetch_row()[0]; ?></h3>
                    <p>Total Jobs</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card text-center p-4">
                    <h3>Rs. <?php 
                        $total = $conn->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetch_row()[0] ?? 0;
                        echo number_format($total);
                    ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CATEGORIES TAB -->
    <div class="tab-content <?php echo $tab=='categories'?'active':''; ?>">
        <h2>Manage Categories</h2>
        <form method="POST" class="mb-4">
            <div class="input-group" style="max-width:500px;">
                <input type="text" name="name" class="form-control" placeholder="New Category Name" required>
                <button name="add_category" class="btn btn-success">Add Category</button>
            </div>
        </form>
        <?php if(isset($_GET['success'])) echo "<div class='alert alert-success'>Category Added!</div>"; ?>
        <?php if(isset($_GET['deleted'])) echo "<div class='alert alert-danger'>Category Deleted!</div>"; ?>

        <table class="table table-bordered">
            <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Action</th></tr></thead>
            <?php
            $cats = $conn->query("SELECT * FROM categories ORDER BY name");
            $i=1; while($c = $cats->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                <td>
                    <a href="?tab=categories&delete_category=<?php echo $c['id']; ?>" 
                       class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')">
                        Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- USERS TAB -->
    <div class="tab-content <?php echo $tab=='users'?'active':''; ?>">
        <h2>Manage Users</h2>
        <form method="POST" class="p-4 border rounded bg-light mb-4">
            <div class="row g-3">
                <div class="col"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                <div class="col"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="col"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="col">
                    <select name="role" class="form-control">
                        <option value="client">Client</option>
                        <option value="freelancer">Freelancer</option>
                    </select>
                </div>
                <div class="col"><button name="add_user" class="btn btn-primary">Add User</button></div>
            </div>
        </form>

        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Action</th></tr></thead>
            <?php
            $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
            while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                <td><?php echo $u['email']; ?></td>
                <td><span class="badge bg-<?php echo $u['role']=='admin'?'danger':($u['role']=='client'?'primary':'success'); ?>">
                    <?php echo ucfirst($u['role']); ?>
                </span></td>
                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if($u['role'] != 'admin'): ?>
                        <a href="?tab=users&delete_user=<?php echo $u['id']; ?>" 
                           class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">
                           Delete
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- REVENUE TAB -->
    <div class="tab-content <?php echo $tab=='revenue'?'active':''; ?>">
        <h2>Revenue Tracking</h2>
        <div class="card">
            <div class="card-body">
                <h4>Total Platform Revenue: 
                    <span class="text-success fs-3">Rs. <?php 
                        $total = $conn->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetch_row()[0] ?? 0;
                        echo number_format($total);
                    ?></span>
                </h4>
            </div>
        </div>

        <table class="table mt-4">
            <thead class="table-success"><tr><th>Freelancer</th><th>Jobs Won</th><th>Earned</th></tr></thead>
            <?php
            $rev = $conn->query("SELECT u.name, COUNT(b.id) as jobs, SUM(p.amount) as earned 
                                 FROM bids b 
                                 JOIN payments p ON b.id=p.bid_id AND p.status='success'
                                 JOIN users u ON b.freelancer_id=u.id 
                                 GROUP BY u.id ORDER BY earned DESC");
            while($r = $rev->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo $r['name']; ?></strong></td>
                <td><?php echo $r['jobs']; ?></td>
                <td><strong class="text-success">Rs. <?php echo number_format($r['earned']); ?></strong></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<script>
    // Auto refresh every 30 seconds
    setTimeout(() => location.reload(), 30000);
</script>

<?php include '../footer.php'; ?>
</body>
</html>