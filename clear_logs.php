<?php
// clear_logs.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Clear all logs
$pdo->exec("TRUNCATE TABLE logs");

header("Location: manage_log.php");
exit;
?>
