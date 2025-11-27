<?php
// edit_grades.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all students for dropdown
$students = $pdo->query("SELECT username, name FROM student_profiles ORDER BY name ASC")->fetchAll();

// Get selected student_username from query string
$student_username = $_GET['student_username'] ?? null;
$student = null;
$courses = [];
$grades = [];

if ($student_username) {
    // Fetch student info
    $studentStmt = $pdo->prepare("SELECT username, name FROM student_profiles WHERE username = ?");
    $studentStmt->execute([$student_username]);
    $student = $studentStmt->fetch();

    // Fetch all courses
    $courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();

    // Fetch existing grades for this student
    $gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_username = ?");
    $gradesStmt->execute([$student_username]);
    foreach ($gradesStmt->fetchAll() as $g) {
        $grades[$g['course_id']] = $g;
    }

    // Handle form submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST['grades'] as $course_id => $data) {
            $grade     = $data['grade'] ?? null;
            $points    = $data['points'] ?? null;
            $semester  = $data['semester'] ?? null;
            $year      = $data['year'] ?? date('Y');

            if (isset($grades[$course_id])) {
                // Update existing grade
                $stmt = $pdo->prepare("
                    UPDATE grades 
                    SET grade = :grade, points = :points, semester = :semester, year = :year
                    WHERE student_username = :student_username AND course_id = :course_id
                ");
            } else {
                // Insert new grade
                $stmt = $pdo->prepare("
                    INSERT INTO grades (student_username, course_id, grade, points, semester, year)
                    VALUES (:student_username, :course_id, :grade, :points, :semester, :year)
                ");
            }

            $stmt->execute([
                ':student_username' => $student_username,
                ':course_id'  => $course_id,
                ':grade'      => $grade,
                ':points'     => $points,
                ':semester'   => $semester,
                ':year'       => $year
            ]);
        }

        $_SESSION['success'] = "✅ Grades updated successfully!";
        header("Location: edit_grade.php?student_username=" . $student_username);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Grades</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">✏️ Manage Grades</h2>

  <!-- Student Dropdown -->
  <form method="get" class="mb-4 card p-3">
    <label class="form-label">Select Student</label>
    <select name="student_username" class="form-select" required>
      <option value="">-- Choose Student --</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= $s['username'] ?>" <?= ($student_username == $s['username']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary mt-2">Load Grades</button>
  </form>

  <?php if ($student): ?>
    <h4 class="mb-3">Grades for <?= htmlspecialchars($student['name']) ?></h4>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="post" class="card p-3">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Course</th>
            <th>Grade</th>
            <th>Points</th>
            <th>Semester</th>
            <th>Year</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): 
            $existing = $grades[$c['id']] ?? null;
          ?>
          <tr>
            <td><?= htmlspecialchars($c['course_name']) ?></td>
            <td>
              <input type="text" name="grades[<?= $c['id'] ?>][grade]" 
                     value="<?= htmlspecialchars($existing['grade'] ?? '') ?>" 
                     class="form-control form-control-sm" placeholder="A, B, C, D, F">
            </td>
            <td>
              <input type="number" step="0.01" name="grades[<?= $c['id'] ?>][points]" 
                     value="<?= htmlspecialchars($existing['points'] ?? '') ?>" 
                     class="form-control form-control-sm">
            </td>
            <td>
              <input type="text" name="grades[<?= $c['id'] ?>][semester]" 
                     value="<?= htmlspecialchars($existing['semester'] ?? '') ?>" 
                     class="form-control form-control-sm" placeholder="Fall, Spring">
            </td>
            <td>
              <input type="number" name="grades[<?= $c['id'] ?>][year]" 
                     value="<?= htmlspecialchars($existing['year'] ?? date('Y')) ?>" 
                     class="form-control form-control-sm">
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="mt-3">
        <button type="submit" class="btn btn-success">💾 Save All Grades</button>
        <a href="manage_grades.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
