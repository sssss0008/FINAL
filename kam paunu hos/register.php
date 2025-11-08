<?php include 'header.php'; 
if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];
    $role = $_POST['role'];
    $skills = mysqli_real_escape_string($conn, $_POST['skills'] ?? '');
    
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check)>0){
        echo "<div class='alert alert-danger'>Email already exists!</div>";
    } else {
        $q = mysqli_query($conn, "INSERT INTO users (name,email,password,role,skills) VALUES ('$name','$email','$pass','$role','$skills')");
        if($q){
            echo "<div class='alert alert-success text-center'>Registered! <a href='login.php'>Login Now</a></div>";
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Create Account</h2>
                <form method="POST">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="mb-3">
                        <select name="role" class="form-control">
                            <option value="freelancer">Freelancer</option>
                            <option value="client">Client</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea name="skills" class="form-control" placeholder="Your Skills (e.g., PHP, Laravel)" rows="3"></textarea>
                    </div>
                    <button type="submit" name="register" class="btn btn-success btn-lg w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>