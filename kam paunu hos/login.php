<?php 
include 'header.php';

// Initialize variables
$email = $password = '';
$errors = [];
$login_attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
$lockout_time = isset($_SESSION['lockout_time']) ? $_SESSION['lockout_time'] : 0;

// Check if user is already logged in
if(isset($_SESSION['user'])) {
    header('Location: dashboard/'.$_SESSION['user']['role'].'.php');
    exit();
}

// Check if account is locked due to too many attempts
if($login_attempts >= 5 && time() < $lockout_time) {
    $lockout_remaining = $lockout_time - time();
    $errors[] = "Account temporarily locked. Try again in " . ceil($lockout_remaining/60) . " minutes.";
}

if(isset($_POST['login']) && empty($errors)){
    // Sanitize and validate inputs
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if(empty($email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif(strlen($email) > 150) {
        $errors[] = "Email is too long (max 150 characters)";
    }
    
    if(empty($password)) {
        $errors[] = "Password is required";
    } elseif(strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    } elseif(strlen($password) > 255) {
        $errors[] = "Password is too long (max 255 characters)";
    }
    
    // If no validation errors, proceed with login
    if(empty($errors)) {
        // Check if account exists
        $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        
        if(mysqli_num_rows($q) > 0) {
            $user = mysqli_fetch_assoc($q);
            
            // Verify password - IMPORTANT: Never store plain text passwords!
            // First check if password is hashed (assuming you'll update to hashed passwords)
            if(password_verify($password, $user['password'])) {
                // Password is hashed and correct
                handleSuccessfulLogin($user, $remember);
            } elseif($user['password'] === $password) {
                // TEMPORARY: For backward compatibility with plain text passwords
                // This should be removed after migrating to hashed passwords
                handleSuccessfulLogin($user, $remember);
            } else {
                handleFailedLogin();
            }
        } else {
            // User doesn't exist - still count as failed attempt for security
            handleFailedLogin();
        }
    }
}

function handleSuccessfulLogin($user, $remember) {
    global $conn;
    
    // Reset login attempts
    $_SESSION['login_attempts'] = 0;
    unset($_SESSION['lockout_time']);
    
    // Update last login time
    $userId = $user['id'];
    mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = $userId");
    
    // Set session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'profile_pic' => $user['profile_pic'],
        'login_time' => time()
    ];
    
    // Set remember me cookie if requested
    if($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database
        mysqli_query($conn, "INSERT INTO user_sessions (user_id, token, expires_at) 
                             VALUES ($userId, '$token', FROM_UNIXTIME($expiry))");
        
        // Set cookie
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Redirect based on role
    header('Location: dashboard/'.$user['role'].'.php');
    exit();
}

function handleFailedLogin() {
    // Increment failed attempts
    $login_attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
    $_SESSION['login_attempts'] = $login_attempts;
    
    // Lock account after 5 failed attempts for 15 minutes
    if($login_attempts >= 5) {
        $_SESSION['lockout_time'] = time() + (15 * 60); // 15 minutes
        $errors[] = "Too many failed attempts. Account locked for 15 minutes.";
    } else {
        $remaining = 5 - $login_attempts;
        $errors[] = "Wrong email or password! " . $remaining . " attempts remaining.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">
                    <i class="fas fa-sign-in-alt"></i> Login
                </h2>
                
                <!-- Display errors -->
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Please fix the following:</h6>
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Display success message if redirected from registration -->
                <?php if(isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Registration successful! Please login.
                    </div>
                <?php endif; ?>
                
                <!-- Display logout message -->
                <?php if(isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You have been logged out successfully.
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" id="email" 
                                   class="form-control form-control-lg" 
                                   placeholder="your@email.com" 
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   required 
                                   maxlength="150"
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                            <div class="invalid-feedback">
                                Please enter a valid email address (max 150 characters).
                            </div>
                        </div>
                        <small class="text-muted">Enter the email you used for registration</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter your password" 
                                   required 
                                   minlength="6"
                                   maxlength="255">
                            <button class="btn btn-outline-secondary" type="button" 
                                    onclick="togglePassword('password', 'passwordIcon')">
                                <i id="passwordIcon" class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback">
                                Password must be at least 6 characters.
                            </div>
                        </div>
                        <div class="progress mt-1" style="height: 3px;">
                            <div id="passwordStrength" class="progress-bar" role="progressbar"></div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Password must be 6-255 characters
                        </small>
                    </div>
                    
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="text-decoration-none">
                            <i class="fas fa-key"></i> Forgot Password?
                        </a>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="login" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                        <p class="text-center mt-3">
                            Don't have an account? 
                            <a href="register.php" class="text-decoration-none fw-bold">
                                <i class="fas fa-user-plus"></i> Register here
                            </a>
                        </p>
                    </div>
                </form>
                
                <!-- Test Accounts Section -->
                <div class="mt-4 pt-3 border-top">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-vial"></i> Test Accounts</h6>
                            <button type="button" class="btn btn-sm btn-light" 
                                    onclick="toggleTestAccounts()" id="toggleBtn">
                                Hide Accounts
                            </button>
                        </div>
                        <div class="card-body" id="testAccounts">
                            <p class="small text-muted mb-2">
                                <i class="fas fa-info-circle"></i> Use these accounts for testing:
                            </p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>Password</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-danger">Admin</span></td>
                                            <td><code>admin@kamp.com</code></td>
                                            <td><code>admin123</code></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="fillTestCredentials('admin@kamp.com', 'admin123')">
                                                    Use
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-success">Freelancer</span></td>
                                            <td><code>ram@kamp.com</code></td>
                                            <td><code>ram123</code></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="fillTestCredentials('ram@kamp.com', 'ram123')">
                                                    Use
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-primary">Client</span></td>
                                            <td><code>sita@kamp.com</code></td>
                                            <td><code>sita123</code></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="fillTestCredentials('sita@kamp.com', 'sita123')">
                                                    Use
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-warning small mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Security Note:</strong> These are test accounts with weak passwords. 
                                In production, all passwords should be securely hashed.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if(input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Toggle test accounts visibility
function toggleTestAccounts() {
    const accountsDiv = document.getElementById('testAccounts');
    const toggleBtn = document.getElementById('toggleBtn');
    
    if(accountsDiv.style.display === 'none') {
        accountsDiv.style.display = 'block';
        toggleBtn.textContent = 'Hide Accounts';
        toggleBtn.classList.remove('btn-success');
        toggleBtn.classList.add('btn-light');
    } else {
        accountsDiv.style.display = 'none';
        toggleBtn.textContent = 'Show Accounts';
        toggleBtn.classList.remove('btn-light');
        toggleBtn.classList.add('btn-success');
    }
}

// Fill test credentials
function fillTestCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    
    // Trigger password strength check
    document.getElementById('password').dispatchEvent(new Event('input'));
    
    // Show success message
    const form = document.getElementById('loginForm');
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> Test credentials filled! Click Login to continue.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Remove existing alerts
    const existingAlert = form.querySelector('.alert');
    if(existingAlert) {
        existingAlert.remove();
    }
    
    form.parentNode.insertBefore(alertDiv, form);
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    
    if(!strengthBar) return;
    
    let strength = 0;
    
    // Length check
    if(password.length >= 6) strength += 25;
    if(password.length >= 10) strength += 10;
    
    // Uppercase check
    if(/[A-Z]/.test(password)) strength += 25;
    
    // Number check
    if(/[0-9]/.test(password)) strength += 25;
    
    // Special character check
    if(/[^A-Za-z0-9]/.test(password)) strength += 15;
    
    // Update progress bar
    strengthBar.style.width = strength + '%';
    
    // Color coding
    if(strength < 50) {
        strengthBar.className = 'progress-bar bg-danger';
    } else if(strength < 75) {
        strengthBar.className = 'progress-bar bg-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
    }
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    
    // Real-time validation
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    emailInput.addEventListener('input', function() {
        validateEmail(this);
    });
    
    passwordInput.addEventListener('input', function() {
        validatePassword(this);
    });
    
    form.addEventListener('submit', function(event) {
        // Clear previous validation states
        form.classList.remove('was-validated');
        
        // Validate all fields
        const isEmailValid = validateEmail(emailInput);
        const isPasswordValid = validatePassword(passwordInput);
        
        if(!isEmailValid || !isPasswordValid) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
        }
    });
});

function validateEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
    
    if(email === '') {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else if(!emailRegex.test(email)) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else if(email.length > 150) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

function validatePassword(input) {
    const password = input.value;
    
    if(password === '') {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else if(password.length < 6) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else if(password.length > 255) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

// Auto-focus on email field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});
</script>

<style>
/* Custom styles for login page */
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.form-control-lg {
    border-radius: 8px;
    padding: 12px 15px;
}

.input-group-text {
    border-radius: 8px 0 0 8px;
}

.btn-lg {
    border-radius: 8px;
    padding: 12px;
}

.progress {
    border-radius: 3px;
}

/* Validation styles */
.is-valid {
    border-color: #28a745 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}

.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}

/* Test accounts table */
.table-sm th, .table-sm td {
    padding: 0.5rem;
}

code {
    color: #d63384;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 2rem !important;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
}
</style>

<?php include 'footer.php'; ?>