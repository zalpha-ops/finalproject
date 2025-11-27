<?php
// delete_log.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM logs WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: manage_log.php");
exit;
?>
