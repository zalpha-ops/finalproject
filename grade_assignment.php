<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['instructor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$submissionId = $_POST['submission_id'] ?? 0;
$grade = $_POST['grade'] ?? '';
$feedback = $_POST['feedback'] ?? '';
$instructorId = $_SESSION['instructor_id'];

if (!$submissionId || !$grade || !$feedback) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE assignment_submissions 
        SET grade = ?, 
            feedback = ?, 
            status = 'graded',
            graded_at = CURRENT_TIMESTAMP,
            graded_by = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$grade, $feedback, $instructorId, $submissionId]);
    
    echo json_encode(['success' => true, 'message' => 'Grade submitted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
