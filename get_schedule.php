<?php
session_start();

// Set content type first
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require 'db_connect.php';

$scheduleId = $_GET['id'] ?? 0;

if (!$scheduleId) {
    echo json_encode(['success' => false, 'message' => 'Schedule ID required']);
    exit;
}

try {
    // First, let's try a simple query to see if the schedule exists
    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $stmt->execute([$scheduleId]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$schedule) {
        echo json_encode(['success' => false, 'message' => 'Schedule not found with ID: ' . $scheduleId]);
        exit;
    }
    
    // Now get the full data with joins
    $stmt = $pdo->prepare("
        SELECT s.*, 
               sp.name as student_name,
               i.name as instructor_name,
               c.title as course_title
        FROM schedules s
        LEFT JOIN student_profiles sp ON s.student_id = sp.id
        LEFT JOIN instructors i ON s.instructor_id = i.instructor_id
        LEFT JOIN courses c ON s.course_id = c.course_id
        WHERE s.id = ?
    ");
    
    $stmt->execute([$scheduleId]);
    $fullSchedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fullSchedule) {
        echo json_encode([
            'success' => true,
            'schedule' => $fullSchedule
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error loading schedule details'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>