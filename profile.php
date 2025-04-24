<?php
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: register.php');
    exit;
}

// Initialize variables
$success = false;
$error = '';

// Get user information
$userId = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $userId";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Process profile update form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    
    // Check if email exists and is not current user's email
    if ($email != $user['email']) {
        $checkQuery = "SELECT * FROM users WHERE email = '$email'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email address is already in use.";
        }
    }
    
    // Update profile if no error
    if (empty($error)) {
        $updateQuery = "UPDATE users SET 
                        name = '$name', 
                        email = '$email', 
                        phone = '$phone', 
                        company = '$company' 
                        WHERE id = $userId";
        
        if (mysqli_query($conn, $updateQuery)) {
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $success = true;
            
            // Refresh user data
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Profile update failed: " . mysqli_error($conn);
        }
    }
}

// Process password update form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    // Get password data
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill all password fields.";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $updateQuery = "UPDATE users SET password = '$hashed_password' WHERE id = $userId";
            
            if (mysqli_query($conn, $updateQuery)) {
                $success = true;
            } else {
                $error = "Password update failed: " . mysqli_error($conn);
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="section-title">My Profile</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>Your profile has been updated successfully.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Info Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle mr-2"></i>Account Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto mb-3">
                            <span class="avatar-initials"><?php echo substr($user['name'], 0, 1); ?></span>
                        </div>
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-envelope mr-2 text-muted"></i>Email:</span>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-phone mr-2 text-muted"></i>Phone:</span>
                            <span><?php echo htmlspecialchars($user['phone']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-building mr-2 text-muted"></i>Company:</span>
                            <span><?php echo !empty($user['company']) ? htmlspecialchars($user['company']) : 'Not specified'; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-alt mr-2 text-muted"></i>Joined:</span>
                            <span><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-toggle-on mr-2 text-muted"></i>Status:</span>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-credit-card mr-2 text-muted"></i>Subscription:</span>
                            <?php if (!empty($user['subscription_expiry'])): ?>
                                <?php 
                                $expiryDate = strtotime($user['subscription_expiry']);
                                $today = time();
                                $status = ($expiryDate < $today) ? 'danger' : 'success';
                                ?>
                                <span class="badge badge-<?php echo $status; ?>">
                                    Expires: <?php echo date('M d, Y', $expiryDate); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">No Subscription</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <?php if (!$user['is_active']): ?>
                <div class="card-footer bg-light">
                    <div class="alert alert-info mb-0 text-center">
                        <i class="fas fa-info-circle mr-2"></i>Your account is awaiting activation
                        <hr class="my-2">
                        <small class="d-block mb-2">Please contact us to complete your subscription activation.</small>
                        <a href="contact.php" class="btn btn-sm btn-outline-primary">Contact Support</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Profile Tab Card -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <!-- Tabs Navigation -->
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="edit-profile-tab" data-toggle="tab" href="#edit-profile" role="tab" aria-controls="edit-profile" aria-selected="true">
                                <i class="fas fa-user-edit mr-2"></i>Edit Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="change-password-tab" data-toggle="tab" href="#change-password" role="tab" aria-controls="change-password" aria-selected="false">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tabs Content -->
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Edit Profile Tab -->
                        <div class="tab-pane fade show active" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
                            <h5 class="card-title mb-4">Update Profile Information</h5>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="form-group">
                                    <label for="name"><i class="fas fa-user text-muted mr-2"></i>Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope text-muted mr-2"></i>Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone"><i class="fas fa-phone text-muted mr-2"></i>Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="company"><i class="fas fa-building text-muted mr-2"></i>Company Name</label>
                                    <input type="text" class="form-control" id="company" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                                    <small class="form-text text-muted">Optional: Enter your company or organization name</small>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </form>
                        </div>
                        
                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                            <h5 class="card-title mb-4">Update Your Password</h5>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="form-group">
                                    <label for="current_password"><i class="fas fa-lock text-muted mr-2"></i>Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password"><i class="fas fa-key text-muted mr-2"></i>New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password"><i class="fas fa-check-circle text-muted mr-2"></i>Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="password-strength-meter mb-3">
                                    <div class="password-strength-meter-fill" id="password-strength-meter-fill"></div>
                                </div>
                                <small id="passwordHelp" class="form-text text-muted mb-4">Password must be at least 8 characters long.</small>
                                
                                <button type="submit" name="update_password" class="btn btn-primary">
                                    <i class="fas fa-key mr-2"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Subscription Information Card -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-credit-card mr-2"></i>Subscription Information</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($user['subscription_expiry'])): ?>
                        <?php 
                        $expiryDate = strtotime($user['subscription_expiry']);
                        $today = time();
                        $daysLeft = ceil(($expiryDate - $today) / (60 * 60 * 24));
                        $status = ($expiryDate < $today) ? 'danger' : (($daysLeft < 14) ? 'warning' : 'success');
                        $statusText = ($expiryDate < $today) ? 'Expired' : 'Active';
                        ?>
                        
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6>Subscription Status</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="status-indicator bg-<?php echo $status; ?> mr-2"></div>
                                    <span class="h5 mb-0 text-<?php echo $status; ?>"><?php echo $statusText; ?></span>
                                </div>
                                
                                <p>
                                    <?php if ($expiryDate < $today): ?>
                                        Your subscription expired on <?php echo date('F j, Y', $expiryDate); ?>
                                    <?php else: ?>
                                        Your subscription is valid until <?php echo date('F j, Y', $expiryDate); ?>
                                        <br><small class="text-muted">(<?php echo $daysLeft; ?> days remaining)</small>
                                    <?php endif; ?>
                                </p>
                                
                                <?php if ($expiryDate < $today || $daysLeft < 14): ?>
                                    <a href="contact.php" class="btn btn-outline-primary">
                                        <i class="fas fa-sync-alt mr-2"></i>Renew Subscription
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mt-3 mt-md-0">
                                <div class="subscription-benefits p-3 bg-light rounded">
                                    <h6><i class="fas fa-star text-warning mr-2"></i>Subscription Benefits:</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success mr-2"></i>Access to all factory listings</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Contact information for factories</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Detailed factory specifications</li>
                                        <li><i class="fas fa-check text-success mr-2"></i>Priority customer support</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>You don't have an active subscription.
                        </div>
                        <p>Please contact our team to set up a subscription and get full access to our platform features.</p>
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-envelope mr-2"></i>Contact Us For Subscription
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 100px;
        height: 100px;
        background-color: #6c757d;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .avatar-initials {
        line-height: 1;
    }
    .password-strength-meter {
        height: 5px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
    }
    .password-strength-meter-fill {
        height: 100%;
        width: 0;
        transition: width 0.3s ease;
        border-radius: 5px;
    }
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .subscription-benefits {
        border-left: 4px solid #17a2b8;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength meter
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let color = '#dc3545'; // Default: red/weak
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/) || password.match(/[^a-zA-Z0-9]+/)) strength += 25;
        
        if (strength > 75) color = '#28a745'; // Strong: green
        else if (strength > 50) color = '#ffc107'; // Medium: yellow
        else if (strength > 25) color = '#fd7e14'; // Weak: orange
        
        const strengthMeter = document.getElementById('password-strength-meter-fill');
        strengthMeter.style.width = strength + '%';
        strengthMeter.style.backgroundColor = color;
        
        const helpText = document.getElementById('passwordHelp');
        
        if (strength <= 25) helpText.innerHTML = 'Weak password. Try adding more characters.';
        else if (strength <= 50) helpText.innerHTML = 'Fair password. Add uppercase letters or numbers.';
        else if (strength <= 75) helpText.innerHTML = 'Good password. Consider adding special characters.';
        else helpText.innerHTML = 'Strong password!';
    });
});
</script>

<?php include 'includes/footer.php'; ?>