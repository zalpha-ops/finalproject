<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$assignmentId = $_POST['assignment_id'] ?? 0;
$studentId = $_POST['student_id'] ?? 0;
$comments = $_POST['comments'] ?? '';

// Validate
if (!$assignmentId || !$studentId) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Handle file upload
$uploadDir = 'uploads/assignments/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filePath = null;
if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['assignment_file'];
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
        exit;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'assignment_' . $assignmentId . '_student_' . $studentId . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }
}

try {
    // Insert or update submission
    $stmt = $pdo->prepare("
        INSERT INTO assignment_submissions (assignment_id, student_id, file_path, comments, status)
        VALUES (?, ?, ?, ?, 'submitted')
        ON DUPLICATE KEY UPDATE 
            file_path = VALUES(file_path),
            comments = VALUES(comments),
            submitted_at = CURRENT_TIMESTAMP,
            status = 'submitted'
    ");
    
    $stmt->execute([$assignmentId, $studentId, $filePath, $comments]);
    
    echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
