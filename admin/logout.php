<?php
session_start();

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
?>