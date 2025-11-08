<?php include 'header.php'; 
if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$pass'");
    if(mysqli_num_rows($q)>0){
        $user = mysqli_fetch_assoc($q);
        $_SESSION['user'] = $user;
        header('Location: dashboard/'.$user['role'].'.php');
        exit();
    } else {
        echo "<div class='alert alert-danger text-center'>Wrong email or password!</div>";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Login</h2>
                <form method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-lg w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <p><b>Test Accounts:</b><br>
                    Admin: admin@kamp.com / admin123<br>
                    Freelancer: ram@kamp.com / ram123<br>
                    Client: sita@kamp.com / sita123
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>