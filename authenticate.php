<?php
// authenticate.php
session_start();
require 'db_connect.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !hash_equals($user['password_hash'], hash('sha256', $password))) {
    header("Location: studentlogin.php?error=Invalid credentials");
    exit;
}

if ($user['status'] !== 'active') {
    header("Location: studentlogin.php?error=Account inactive");
    exit;
}

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// Redirect by role
if ($user['role'] === 'admin') {
    header("Location: admin_dashboard.php");
} else {
    header("Location: dashboard.php");
}
exit;
?>
