<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Profile</h1>
</div>

<?php
// Initialize variables
$success = false;
$error = '';

// Get admin information
$adminId = $_SESSION['admin_id'];
$query = "SELECT * FROM users WHERE id = $adminId AND is_admin = 1";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Process profile update form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    
    // Check if email exists and is not current user's email
    if ($email != $admin['email']) {
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
                        WHERE id = $adminId";
        
        if (mysqli_query($conn, $updateQuery)) {
            // Update session variables
            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;
            
            $success = true;
            
            // Refresh admin data
            $result = mysqli_query($conn, $query);
            $admin = mysqli_fetch_assoc($result);
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
        if (password_verify($current_password, $admin['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $updateQuery = "UPDATE users SET password = '$hashed_password' WHERE id = $adminId";
            
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

// Get activity statistics
$stats = [
    'factories' => 0,
    'users' => 0,
    'active_users' => 0,
    'messages' => 0,
    'last_login' => 'N/A'
];

// Count factories
$query = "SELECT COUNT(*) as count FROM factories";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['factories'] = $row['count'];
}

// Count users
$query = "SELECT COUNT(*) as count FROM users WHERE is_admin = 0";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['users'] = $row['count'];
}

// Count active users
$query = "SELECT COUNT(*) as count FROM users WHERE is_admin = 0 AND is_active = 1";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['active_users'] = $row['count'];
}

// Count messages
$query = "SELECT COUNT(*) as count FROM contact_messages";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['messages'] = $row['count'];
}
?>

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
    <!-- Admin Info Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-user-shield mr-2"></i>Admin Account</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="admin-avatar mb-3">
                        <i class="fas fa-user-shield fa-3x"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($admin['name']); ?></h4>
                    <p class="badge badge-dark">System Administrator</p>
                </div>
                
                <hr>
                
                <div>
                    <p><i class="fas fa-envelope text-muted mr-2"></i> <strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
                    <p><i class="fas fa-phone text-muted mr-2"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($admin['phone']); ?></p>
                    <p><i class="fas fa-building text-muted mr-2"></i> <strong>Company:</strong> <?php echo !empty($admin['company']) ? htmlspecialchars($admin['company']) : 'Not specified'; ?></p>
                    <p><i class="fas fa-calendar-alt text-muted mr-2"></i> <strong>Registered:</strong> <?php echo date('M d, Y', strtotime($admin['registration_date'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Activity Stats Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>System Statistics</h6>
            </div>
            <div class="card-body">
                <div class="admin-stat">
                    <div class="admin-stat-icon bg-primary">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h4><?php echo $stats['factories']; ?></h4>
                        <p>Total Factories</p>
                    </div>
                </div>
                
                <div class="admin-stat">
                    <div class="admin-stat-icon bg-success">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h4><?php echo $stats['users']; ?></h4>
                        <p>Registered Users</p>
                    </div>
                </div>
                
                <div class="admin-stat">
                    <div class="admin-stat-icon bg-warning">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h4><?php echo $stats['active_users']; ?></h4>
                        <p>Active Users</p>
                    </div>
                </div>
                
                <div class="admin-stat">
                    <div class="admin-stat-icon bg-danger">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h4><?php echo $stats['messages']; ?></h4>
                        <p>Messages</p>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="dashboard.php" class="btn btn-sm btn-outline-primary">View Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Tabs Card -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <!-- Tabs Navigation -->
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs card-header-tabs" id="adminProfileTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="edit-profile-tab" data-toggle="tab" href="#edit-profile" role="tab" aria-controls="edit-profile" aria-selected="true">
                            <i class="fas fa-user-edit mr-2"></i>Edit Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">
                            <i class="fas fa-key mr-2"></i>Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="preferences-tab" data-toggle="tab" href="#preferences" role="tab" aria-controls="preferences" aria-selected="false">
                            <i class="fas fa-cog mr-2"></i>Preferences
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Tabs Content -->
            <div class="card-body">
                <div class="tab-content" id="adminProfileTabsContent">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
                        <h5 class="card-title mb-4">Update Profile Information</h5>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="company">Company</label>
                                <input type="text" class="form-control" id="company" name="company" value="<?php echo htmlspecialchars($admin['company'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </form>
                    </div>
                    
                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <h5 class="card-title mb-4">Update Password</h5>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="form-text text-muted">Password should be at least 8 characters with a mix of letters, numbers, and symbols.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="password-strength-meter mb-3">
                                <div class="password-strength-meter-fill" id="password-strength-meter-fill"></div>
                            </div>
                            <small id="passwordHelp" class="form-text text-muted mb-4">Password strength indicator</small>
                            
                            <button type="submit" name="update_password" class="btn btn-primary">
                                <i class="fas fa-key mr-2"></i>Update Password
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <h5 class="card-title mb-4">Security Options</h5>
                        <div class="security-settings">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="two-factor-auth" disabled>
                                    <label class="custom-control-label" for="two-factor-auth">Enable Two-Factor Authentication</label>
                                </div>
                                <small class="form-text text-muted">Coming soon: Add an extra layer of security to your account.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="login-notifications" disabled>
                                    <label class="custom-control-label" for="login-notifications">Email notifications for new logins</label>
                                </div>
                                <small class="form-text text-muted">Coming soon: Receive email alerts when your account is accessed from a new device or location.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preferences Tab -->
                    <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                        <h5 class="card-title mb-4">System Preferences</h5>
                        
                        <form>
                            <div class="form-group">
                                <label for="items-per-page">Items Per Page</label>
                                <select class="form-control" id="items-per-page">
                                    <option>10</option>
                                    <option>20</option>
                                    <option>50</option>
                                    <option>100</option>
                                </select>
                                <small class="form-text text-muted">Set the default number of items to display in tables.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="email-notifications" checked>
                                    <label class="custom-control-label" for="email-notifications">Email Notifications</label>
                                </div>
                                <small class="form-text text-muted">Receive email notifications for important system events.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="auto-approve" checked>
                                    <label class="custom-control-label" for="auto-approve">Auto-approve User Registrations</label>
                                </div>
                                <small class="form-text text-muted">Automatically approve new user registrations without admin review.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="debug-mode">
                                    <label class="custom-control-label" for="debug-mode">Debug Mode</label>
                                </div>
                                <small class="form-text text-muted">Enable additional logging and debugging information.</small>
                            </div>
                            
                            <button type="button" class="btn btn-primary" disabled>
                                <i class="fas fa-save mr-2"></i>Save Preferences
                            </button>
                            <small class="form-text text-muted mt-2">Preference settings coming soon in a future update.</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Action Card -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-bolt mr-2"></i>Quick Admin Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <a href="factories.php" class="quick-action-card">
                            <div class="quick-action-icon bg-primary">
                                <i class="fas fa-industry"></i>
                            </div>
                            <h6>Manage Factories</h6>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <a href="users.php" class="quick-action-card">
                            <div class="quick-action-icon bg-success">
                                <i class="fas fa-users"></i>
                            </div>
                            <h6>Manage Users</h6>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <a href="messages.php" class="quick-action-card">
                            <div class="quick-action-icon bg-info">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h6>Messages</h6>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="add_factory.php" class="quick-action-card">
                            <div class="quick-action-icon bg-warning">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h6>Add Factory</h6>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-avatar {
        width: 100px;
        height: 100px;
        background-color: #6c757d;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .admin-stat {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .admin-stat:last-child {
        margin-bottom: 0;
    }
    
    .admin-stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #fff;
        flex-shrink: 0;
    }
    
    .admin-stat-content h4 {
        margin: 0;
        font-weight: 700;
        font-size: 20px;
    }
    
    .admin-stat-content p {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
    }
    
    .quick-action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 15px 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
        color: #495057;
        text-decoration: none;
    }
    
    .quick-action-card:hover {
        background-color: #f8f9fa;
        transform: translateY(-5px);
        text-decoration: none;
        color: #212529;
    }
    
    .quick-action-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        color: #fff;
    }
    
    .quick-action-card h6 {
        margin: 0;
        font-size: 14px;
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