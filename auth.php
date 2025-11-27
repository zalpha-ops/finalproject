<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='studentlogin.php'>login</a>.");
}

// Function to check role
function require_role($allowed_roles) {
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        die("Access denied.");
    }
}
?>
