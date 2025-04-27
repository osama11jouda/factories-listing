<?php
require_once __DIR__ . '/../models/EnvLoader.php';

// Load environment variables
EnvLoader::init();

// Database connection parameters
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));
define('DB_NAME', EnvLoader::get('DB_NAME', 'factories_listing'));

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

// Site configuration constants
define('SITE_URL', EnvLoader::get('SITE_URL', 'http://localhost/FactoriesListing'));
define('SITE_NAME', EnvLoader::get('SITE_NAME', 'Syrian Factories Listing'));
define('ADMIN_EMAIL', EnvLoader::get('ADMIN_EMAIL', 'admin@example.com'));

// Upload configuration
define('UPLOAD_MAX_SIZE', EnvLoader::get('UPLOAD_MAX_SIZE', 5242880)); // 5MB default
define('ALLOWED_EXTENSIONS', EnvLoader::get('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,webp,gif'));

// Subscription configuration
define('SUBSCRIPTION_ENABLED', EnvLoader::get('SUBSCRIPTION_ENABLED', true));
define('TRIAL_DAYS', EnvLoader::get('TRIAL_DAYS', 0));

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Function to check if user has active subscription
function hasActiveSubscription() {
    return isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;
}

// Function to redirect with message
function redirectWithMessage($location, $message, $type = 'danger') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $location");
    exit;
}

// Function to display formatted date
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}

// Function to sanitize input data
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to generate a random string (for file names, etc.)
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>