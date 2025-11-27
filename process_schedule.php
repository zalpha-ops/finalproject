<?php
session_start();
require 'db_connect.php';
require 'auth.php';
require_role('admin');

$action = $_GET['action'] ?? $_POST['action'] ?? 'create';

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleId = $_POST['schedule_id'] ?? 0;
    $studentId = $_POST['student_id'] ?? 0;
    $instructorId = $_POST['instructor_id'] ?? 0;
    $courseId = $_POST['course_id'] ?? null;
    $sessionType = $_POST['session_type'] ?? '';
    $sessionDate = $_POST['session_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $aircraftReg = $_POST['aircraft_registration'] ?? null;
    $location = $_POST['location'] ?? '';
    $status = $_POST['status'] ?? 'scheduled';
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    if (!$scheduleId || !$studentId || !$instructorId || !$sessionType || !$sessionDate || !$startTime || !$endTime || !$location) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: admin_manage_schedules.php");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE schedules 
            SET student_id = ?, instructor_id = ?, course_id = ?, session_type = ?, 
                session_date = ?, start_time = ?, end_time = ?, aircraft_registration = ?, 
                location = ?, status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        $stmt->execute([
            $studentId,
            $instructorId,
            $courseId ?: null,
            $sessionType,
            $sessionDate,
            $startTime,
            $endTime,
            $aircraftReg ?: null,
            $location,
            $status,
            $notes,
            $scheduleId
        ]);
        
        $_SESSION['success_message'] = "Schedule updated successfully!";
        header("Location: admin_manage_schedules.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating schedule: " . $e->getMessage();
        header("Location: admin_manage_schedules.php");
        exit;
    }
}

if ($action === 'delete') {
    $id = $_GET['id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Schedule deleted successfully!";
        header("Location: admin_manage_schedules.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting schedule: " . $e->getMessage();
        header("Location: admin_manage_schedules.php");
        exit;
    }
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'] ?? 0;
    $instructorId = $_POST['instructor_id'] ?? 0;
    $courseId = $_POST['course_id'] ?? null;
    $sessionType = $_POST['session_type'] ?? '';
    $sessionDate = $_POST['session_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $aircraftReg = $_POST['aircraft_registration'] ?? null;
    $location = $_POST['location'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    if (!$studentId || !$instructorId || !$sessionType || !$sessionDate || !$startTime || !$endTime || !$location) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: admin_manage_schedules.php");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO schedules 
            (student_id, instructor_id, course_id, session_type, session_date, start_time, end_time, aircraft_registration, location, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')
        ");
        
        $stmt->execute([
            $studentId,
            $instructorId,
            $courseId ?: null,
            $sessionType,
            $sessionDate,
            $startTime,
            $endTime,
            $aircraftReg ?: null,
            $location,
            $notes
        ]);
        
        $_SESSION['success_message'] = "Schedule created successfully!";
        header("Location: admin_manage_schedules.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error creating schedule: " . $e->getMessage();
        header("Location: admin_manage_schedules.php");
        exit;
    }
}

// If no valid action, redirect back
header("Location: admin_manage_schedules.php");
exit;
?>
