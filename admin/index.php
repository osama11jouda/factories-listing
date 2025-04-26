<?php
session_start();
require_once '../models/User.php';

// Check if already logged in as admin
if(isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
include '../includes/config.php';

$error = '';

// Process login form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate the admin using User model
    $user = new User();
    if($user->findByEmail($email)) {
        if($user->isAdmin()) {
            if($user->verifyPassword($password)) {
                // Store admin data in session
                $_SESSION['admin_id'] = $user->getId();
                $_SESSION['admin_name'] = $user->getName();
                $_SESSION['admin_email'] = $user->getEmail();
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "You do not have admin privileges";
        }
    } else {
        $error = "Invalid email address";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Syrian Factories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .btn-primary {
            background-color: #343a40;
            border-color: #343a40;
        }
        .btn-primary:hover {
            background-color: #23272b;
            border-color: #23272b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-shield"></i> Admin Login</h3>
                    <p class="mb-0">Syrian Factories Management</p>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-block mt-4" style="background-color: #28a745; border-color: #28a745;">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php">Return to Website</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>