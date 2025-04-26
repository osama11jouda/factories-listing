<?php
include 'includes/header.php';

// Initialize variables
$registerSuccess = false;
$registerError = '';
$loginError = '';
$name = $email = $phone = '';
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'login'; // Default to login tab unless specified

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: factories.php');
    exit;
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Get registration form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Simple validation
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $registerError = "Please fill all required fields.";
        $activeTab = 'register';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = "Please enter a valid email address.";
        $activeTab = 'register';
    } elseif ($password != $confirm_password) {
        $registerError = "Passwords do not match.";
        $activeTab = 'register';
    } else {
        // Check if email already exists
        $checkQuery = "SELECT * FROM users WHERE email = '$email'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $registerError = "Email address is already registered. Please login.";
            $activeTab = 'login';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Register new user (with default inactive status)
            $registerQuery = "INSERT INTO users (name, email, phone, password, registration_date, is_active, is_admin) 
                             VALUES ('$name', '$email', '$phone', '$hashed_password', NOW(), 0, 0)";
                             
            if (mysqli_query($conn, $registerQuery)) {
                $registerSuccess = true;
                $name = $email = $phone = '';
                $activeTab = 'login';
            } else {
                $registerError = "Registration failed: " . mysqli_error($conn);
                $activeTab = 'register';
            }
        }
    }
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Get login form data
    $login_email = mysqli_real_escape_string($conn, $_POST['login_email']);
    $login_password = mysqli_real_escape_string($conn, $_POST['login_password']);
    
    // Validate input
    if (empty($login_email) || empty($login_password)) {
        $loginError = "Please enter both email and password.";
        $activeTab = 'login';
    } else {
        // Check user credentials
        $loginQuery = "SELECT * FROM users WHERE email = '$login_email'";
        $loginResult = mysqli_query($conn, $loginQuery);
        
        if (mysqli_num_rows($loginResult) == 1) {
            $user = mysqli_fetch_assoc($loginResult);
            
            // Verify password
            if (password_verify($login_password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_active'] = $user['is_active'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Redirect based on user type
                if ($user['is_admin'] == 1) {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: factories.php');
                }
                exit;
            } else {
                $loginError = "Invalid password.";
                $activeTab = 'login';
            }
        } else {
            $loginError = "Email not registered.";
            $activeTab = 'login';
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="section-title">Account Access</h1>
            <p class="text-muted">Join our platform to access factory listings across Syria</p>
        </div>
    </div>
    
    <?php if ($registerSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-check-circle mr-2"></i> Registration Successful!</h4>
            <p>
                Your account has been created successfully. Our team will review your registration and activate your account shortly.
                You will receive a confirmation email when your account is activated. Activation requires a subscription payment.
            </p>
            <p>In the meantime, you can login with your credentials but you'll have limited access until your account is activated.</p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="auth-form-container">
        <!-- Tabs Navigation -->
        <div class="auth-tabs-container">
            <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab === 'login' ? 'active' : ''; ?> bg-white" 
                       id="login-tab" data-toggle="pill" href="#login-panel" role="tab" 
                       aria-controls="login-panel" aria-selected="<?php echo $activeTab === 'login' ? 'true' : 'false'; ?>">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeTab === 'register' ? 'active' : ''; ?> bg-white" 
                       id="register-tab" data-toggle="pill" href="#register-panel" role="tab" 
                       aria-controls="register-panel" aria-selected="<?php echo $activeTab === 'register' ? 'true' : 'false'; ?>">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content" id="authTabsContent">
            <!-- Login Tab Panel -->
            <div class="tab-pane fade <?php echo $activeTab === 'login' ? 'show active' : ''; ?>" id="login-panel" role="tabpanel" aria-labelledby="login-tab">
                <div class="auth-card card shadow animation-fade">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-sign-in-alt mr-2"></i>Login to Your Account</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($loginError)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $loginError; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="login_email">Email Address</label>
                                <i class="fas fa-envelope form-icon"></i>
                                <input type="email" class="form-control" id="login_email" name="login_email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="login_password">Password</label>
                                <i class="fas fa-lock form-icon"></i>
                                <input type="password" class="form-control" id="login_password" name="login_password" required>
                                <i class="fas fa-eye password-toggle" data-toggle="password" data-target="#login_password"></i>
                            </div>
                            
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">Remember Me</label>
                                <a href="#" class="float-right text-muted">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-lg btn-block btn-login text-white" style="background-color: #28a745; border-color: #28a745;">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </button>
                            
                            <div class="form-switch-text">
                                <p>Don't have an account? 
                                    <a href="#register-panel" data-toggle="pill" role="tab" aria-controls="register-panel" 
                                       aria-selected="false" class="font-weight-bold text-success toggle-form" data-target="register-tab">
                                       Register Now
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Register Tab Panel -->
            <div class="tab-pane fade <?php echo $activeTab === 'register' ? 'show active' : ''; ?>" id="register-panel" role="tabpanel" aria-labelledby="register-tab">
                <div class="auth-card card shadow animation-fade">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-user-plus mr-2"></i>Create New Account</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($registerError)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $registerError; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registerForm">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <i class="fas fa-user form-icon"></i>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <i class="fas fa-envelope form-icon"></i>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <i class="fas fa-phone form-icon"></i>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <i class="fas fa-lock form-icon"></i>
                                <input type="password" class="form-control password-input" id="password" name="password" required>
                                <i class="fas fa-eye password-toggle" data-toggle="password" data-target="#password"></i>
                                <div class="password-strength-meter">
                                    <div class="password-strength-meter-fill" id="password-strength-meter-fill"></div>
                                </div>
                                <small id="passwordHelp" class="form-text text-muted">Password must be at least 8 characters long.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <i class="fas fa-lock form-icon"></i>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <i class="fas fa-eye password-toggle" data-toggle="password" data-target="#confirm_password"></i>
                            </div>
                            
                            <button type="submit" name="register" class="btn btn-lg btn-block btn-register text-white" style="background-color: #28a745; border-color: #28a745;">
                                <i class="fas fa-user-plus mr-2"></i> Register
                            </button>
                            
                            <div class="form-switch-text">
                                <p>Already have an account? 
                                    <a href="#login-panel" data-toggle="pill" role="tab" aria-controls="login-panel" 
                                       aria-selected="false" class="font-weight-bold text-success toggle-form" data-target="login-tab">
                                       Login Here
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light border-0 rounded-lg shadow">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4"><i class="fas fa-info-circle mr-2"></i>Subscription Information</h4>
                    <p class="card-text text-center mb-4">
                        Access to detailed factory listings and contact information requires an active subscription. 
                        Here's how it works:
                    </p>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card subscription-step h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-3 text-primary">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <h5>Step 1: Register</h5>
                                    <p class="text-muted">Create your account with your name, email and contact details.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card subscription-step h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-3 text-primary">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <h5>Step 2: Subscribe</h5>
                                    <p class="text-muted">Purchase a subscription. Our team will contact you with payment options.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card subscription-step h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-3 text-primary">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h5>Step 3: Access</h5>
                                    <p class="text-muted">Once activated, access all factory listings, details and contact information.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="mb-2">For subscription inquiries, please <a href="contact.php" class="font-weight-bold">contact us</a> or call <span class="text-primary">+963 11 123 4567</span>.</p>
                        <a href="pricing.php" class="btn btn-outline-primary">View Pricing Options</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>