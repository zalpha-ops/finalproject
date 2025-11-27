<?php
// add_grade.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch data for dropdowns
$students = [];
$courses = [];
$errorMsg = '';

try {
    $students = $pdo->query("SELECT id, name FROM students ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $courses  = $pdo->query("SELECT course_id, title FROM courses ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        $errorMsg .= "No students found in the database. Please add students first. ";
    }
    if (empty($courses)) {
        $errorMsg .= "No courses found in the database. Please add courses first.";
    }
} catch (Exception $e) {
    try {
        $logStmt = $pdo->prepare("INSERT INTO logs (admin_id, action, details) VALUES (:admin_id, 'Error', :details)");
        $logStmt->execute([
            ':admin_id' => $_SESSION['admin_id'] ?? null,
            ':details'  => "Error loading form data: " . $e->getMessage()
        ]);
    } catch (Exception $logError) {
        // Silently fail if logging doesn't work
    }
    $errorMsg = "⚠️ Database error: " . $e->getMessage();
}

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $course_id  = $_POST['course_id'] ?? null;
    $grade      = $_POST['grade'] ?? null;
    $status     = $_POST['status'] ?? 'In Progress';
    $exam_date  = $_POST['exam_date'] ?? null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO grades (student_id, course_id, grade, status, exam_date)
            VALUES (:student_id, :course_id, :grade, :status, :exam_date)
        ");
        $stmt->execute([
            ':student_id' => $student_id,
            ':course_id'  => $course_id,
            ':grade'      => $grade,
            ':status'     => $status,
            ':exam_date'  => $exam_date
        ]);

        $_SESSION['success'] = "✅ Grade added successfully!";
        header("Location: manage_grades.php");
        exit;

    } catch (Exception $e) {
        $logStmt = $pdo->prepare("INSERT INTO logs (admin_id, action, details) VALUES (:admin_id, 'Error', :details)");
        $logStmt->execute([
            ':admin_id' => $_SESSION['admin_id'] ?? null,
            ':details'  => "Error adding grade: " . $e->getMessage()
        ]);

        $_SESSION['error'] = "⚠️ Something went wrong while adding the grade.";
        header("Location: manage_grades.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Grade</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">➕ Add Grade</h2>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  
  <?php if (!empty($errorMsg)): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <form method="post" class="card p-3">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Student</label>
        <select name="student_id" class="form-select" required <?= empty($students) ? 'disabled' : '' ?>>
          <option value=""><?= empty($students) ? 'No students available' : 'Select student' ?></option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($students)): ?>
          <small class="text-muted">
            <a href="manage_students.php">Add students first</a>
          </small>
        <?php endif; ?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Course</label>
        <select name="course_id" class="form-select" required <?= empty($courses) ? 'disabled' : '' ?>>
          <option value=""><?= empty($courses) ? 'No courses available' : 'Select course' ?></option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($courses)): ?>
          <small class="text-muted">
            <a href="manage_courses.php">Add courses first</a>
          </small>
        <?php endif; ?>
      </div>

      <div class="col-md-4">
        <label class="form-label">Grade</label>
        <input type="text" name="grade" class="form-control" placeholder="e.g., 85" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="In Progress">In Progress</option>
          <option value="Pass">Pass</option>
          <option value="Fail">Fail</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Exam date</label>
        <input type="date" name="exam_date" class="form-control" required>
      </div>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Save Grade</button>
      <a href="manage_grades.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
