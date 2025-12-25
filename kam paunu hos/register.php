<?php 
include 'header.php';

// Define validation patterns
$email_pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
$password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

if(isset($_POST['register'])){
    $errors = [];
    
    // Validate name
    $name = trim($_POST['name'] ?? '');
    if(empty($name)){
        $errors[] = "Full name is required";
    } elseif(strlen($name) < 2){
        $errors[] = "Name must be at least 2 characters long";
    } elseif(strlen($name) > 100){
        $errors[] = "Name must not exceed 100 characters";
    } elseif(!preg_match('/^[a-zA-Z\s\.\-]+$/', $name)){
        $errors[] = "Name can only contain letters, spaces, dots and hyphens";
    }
    
    // Validate email
    $email = trim($_POST['email'] ?? '');
    if(empty($email)){
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email format";
    } elseif(!preg_match($email_pattern, $email)){
        $errors[] = "Email must be in a valid format (e.g., user@example.com)";
    } elseif(strlen($email) > 255){
        $errors[] = "Email must not exceed 255 characters";
    }
    
    // Validate password
    $pass = $_POST['password'] ?? '';
    if(empty($pass)){
        $errors[] = "Password is required";
    } elseif(strlen($pass) < 8){
        $errors[] = "Password must be at least 8 characters long";
    } elseif(!preg_match($password_pattern, $pass)){
        $errors[] = "Password must contain at least:<br>
                    - One uppercase letter<br>
                    - One lowercase letter<br>
                    - One number<br>
                    - One special character (@$!%*?&)";
    } elseif(strlen($pass) > 72){ // bcrypt limit
        $errors[] = "Password must not exceed 72 characters";
    }
    
    // Validate role
    $role = $_POST['role'] ?? '';
    if(empty($role) || !in_array($role, ['freelancer', 'client'])){
        $errors[] = "Please select a valid role";
    }
    
    // Validate skills (optional)
    $skills = trim($_POST['skills'] ?? '');
    if(strlen($skills) > 500){
        $errors[] = "Skills description must not exceed 500 characters";
    }
    
    // If no validation errors, proceed
    if(empty($errors)){
        // Escape data for database
        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $skills = mysqli_real_escape_string($conn, $skills);
        
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if(!$check){
            $errors[] = "Database error: " . mysqli_error($conn);
        } elseif(mysqli_num_rows($check) > 0){
            $errors[] = "Email already exists!";
        } else {
            // Hash the password
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            
            // Use prepared statement for security
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, skills) VALUES (?, ?, ?, ?, ?)");
            
            if($stmt){
                mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_pass, $role, $skills);
                
                if(mysqli_stmt_execute($stmt)){
                    echo "<div class='alert alert-success text-center'>
                            <i class='fas fa-check-circle'></i> Registration Successful! 
                            <a href='login.php' class='alert-link'>Login Now</a>
                          </div>";
                    
                    // Clear form data
                    $_POST = [];
                } else {
                    $errors[] = "Registration failed: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Database preparation failed: " . mysqli_error($conn);
            }
        }
    }
    
    // Display errors if any
    if(!empty($errors)){
        echo "<div class='alert alert-danger'>";
        echo "<h5><i class='fas fa-exclamation-triangle'></i> Please fix the following errors:</h5>";
        echo "<ul class='mb-0'>";
        foreach($errors as $error){
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Create Account</h2>
                
                <!-- Password Requirements Card -->
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Password Requirements</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Minimum 8 characters</li>
                            <li>At least one uppercase letter (A-Z)</li>
                            <li>At least one lowercase letter (a-z)</li>
                            <li>At least one number (0-9)</li>
                            <li>At least one special character (@$!%*?&)</li>
                            <li>Maximum 72 characters</li>
                        </ul>
                    </div>
                </div>
                
                <form method="POST" id="registrationForm">
                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               class="form-control" 
                               placeholder="Enter your full name"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               required
                               minlength="2"
                               maxlength="100">
                        <div class="form-text">Letters, spaces, dots and hyphens only. 2-100 characters.</div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               class="form-control" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               required
                               maxlength="255">
                        <div class="form-text">We'll never share your email with anyone else.</div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-control" 
                                   placeholder="Create a strong password"
                                   required
                                   minlength="8"
                                   maxlength="72">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">See password requirements above.</div>
                        <div class="mt-2" id="passwordStrength"></div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password"
                                   class="form-control" 
                                   placeholder="Re-enter your password"
                                   required
                                   minlength="8"
                                   maxlength="72">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text" id="passwordMatch"></div>
                    </div>
                    
                    <!-- Role Field -->
                    <div class="mb-3">
                        <label for="role" class="form-label">I want to register as *</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="freelancer" <?php echo (($_POST['role'] ?? '') == 'freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                            <option value="client" <?php echo (($_POST['role'] ?? '') == 'client') ? 'selected' : ''; ?>>Client</option>
                        </select>
                    </div>
                    
                    <!-- Skills Field -->
                    <div class="mb-4">
                        <label for="skills" class="form-label">Your Skills (Optional)</label>
                        <textarea name="skills" 
                                  id="skills"
                                  class="form-control" 
                                  placeholder="List your skills separated by commas (e.g., PHP, Laravel, JavaScript, MySQL)"
                                  rows="3"
                                  maxlength="500"><?php echo htmlspecialchars($_POST['skills'] ?? ''); ?></textarea>
                        <div class="form-text">Maximum 500 characters. Freelancers: This will help clients find you.</div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            name="register" 
                            class="btn btn-success btn-lg w-100">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for real-time validation and password toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordField.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Real-time password strength check
    passwordField.addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('passwordStrength');
        
        if (password.length === 0) {
            strengthDiv.innerHTML = '';
            return;
        }
        
        let strength = 0;
        let message = '';
        let color = '';
        
        // Length check
        if (password.length >= 8) strength++;
        
        // Complexity checks
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[@$!%*?&]/.test(password)) strength++;
        
        // Determine strength level
        switch(strength) {
            case 0:
            case 1:
                message = 'Very Weak';
                color = 'danger';
                break;
            case 2:
                message = 'Weak';
                color = 'warning';
                break;
            case 3:
                message = 'Good';
                color = 'info';
                break;
            case 4:
                message = 'Strong';
                color = 'success';
                break;
            case 5:
                message = 'Very Strong';
                color = 'success';
                break;
        }
        
        strengthDiv.innerHTML = `
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-${color}" style="width: ${strength * 20}%"></div>
            </div>
            <small class="text-${color}">${message}</small>
        `;
    });
    
    // Real-time password confirmation check
    confirmPasswordField.addEventListener('input', function() {
        const password = passwordField.value;
        const confirmPassword = this.value;
        const matchDiv = document.getElementById('passwordMatch');
        
        if (confirmPassword.length === 0) {
            matchDiv.innerHTML = '';
            matchDiv.className = 'form-text';
            return;
        }
        
        if (password === confirmPassword) {
            matchDiv.innerHTML = '<i class="fas fa-check text-success"></i> Passwords match';
            matchDiv.className = 'form-text text-success';
        } else {
            matchDiv.innerHTML = '<i class="fas fa-times text-danger"></i> Passwords do not match';
            matchDiv.className = 'form-text text-danger';
        }
    });
    
    // Form validation before submission
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        // Check password match
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match. Please correct and try again.');
            confirmPasswordField.focus();
            return;
        }
        
        // Check password strength pattern
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordPattern.test(password)) {
            e.preventDefault();
            alert('Password does not meet the requirements. Please check the password requirements.');
            passwordField.focus();
            return;
        }
    });
});
</script>

<?php include 'footer.php'; ?>