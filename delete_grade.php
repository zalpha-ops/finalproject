<?php
// delete_grade.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM grades WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "✅ Grade deleted successfully!";
        header("Location: manage_grades.php");
        exit;

    } catch (Exception $e) {
        $logStmt = $pdo->prepare("INSERT INTO logs (admin_id, action, details) VALUES (:admin_id, 'Error', :details)");
        $logStmt->execute([
            ':admin_id' => $_SESSION['admin_id'] ?? null,
            ':details'  => "Error deleting grade: " . $e->getMessage()
        ]);

        $_SESSION['error'] = "⚠️ Could not delete grade.";
        header("Location: manage_grades.php");
        exit;
    }
} else {
    $_SESSION['error'] = "⚠️ Invalid grade id.";
    header("Location: manage_grades.php");
    exit;
}
?>
