<?php
// Export progress for instructor’s assigned students to CSV
session_start();
require 'db_connect.php';
if (!isset($_SESSION['instructor_id'])) { header('Location: instructor_login.php'); exit; }
$instructor_id = (int)$_SESSION['instructor_id'];

// Prepare CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=progress_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// CSV header row
fputcsv($output, [
    'Student Name', 'Student ID', 'Cohort', 'Track',
    'Course', 'Grade', 'Recorded At', 'Instructor'
]);

// Query: grades of students assigned to this instructor
$sql = "
    SELECT sp.name AS student_name, sp.student_id AS student_code,
           sp.cohort, sp.track, c.title AS course_title,
           g.grade, g.recorded_at, i.name AS instructor_name
    FROM grades g
    JOIN student_profiles sp ON g.student_id = sp.id
    JOIN courses c ON g.course_id = c.course_id
    JOIN student_instructors si ON si.student_id = sp.id
    JOIN instructors i ON si.instructor_id = i.instructor_id
    WHERE si.instructor_id = :id
    ORDER BY sp.name, c.title, g.recorded_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $instructor_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['student_name'],
        $row['student_code'],
        $row['cohort'],
        $row['track'],
        $row['course_title'],
        $row['grade'],
        $row['recorded_at'],
        $row['instructor_name']
    ]);
}

fclose($output);
exit;
