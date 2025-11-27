<?php
// manage_grades.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch grades with student + course info
$sql = "
    SELECT g.id, s.name AS student_name, c.title AS course_name,
           g.grade, g.status, g.exam_date
    FROM grades g
    JOIN students s ON g.student_id = s.id
    JOIN courses c ON g.course_id = c.course_id
    ORDER BY g.exam_date DESC
";
$stmt = $pdo->query($sql);
$grades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Grades</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📘 Manage Grades</h2>

  <div class="mb-3">
    <a href="add_grade.php" class="btn btn-primary">➕ Add Grade</a>
    <a href="admin_dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Student</th>
          <th>Course</th>
          <th>Grade</th>
          <th>Status</th>
          <th>Exam Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($grades): foreach ($grades as $g): ?>
          <tr>
            <td><?= $g['id'] ?></td>
            <td><?= htmlspecialchars($g['student_name']) ?></td>
            <td><?= htmlspecialchars($g['course_name']) ?></td>
            <td><?= htmlspecialchars($g['grade']) ?></td>
            <td>
              <?php
                $status = $g['status'];
                $badge = $status === 'Pass' ? 'success' : ($status === 'Fail' ? 'danger' : 'secondary');
              ?>
              <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span>
            </td>
            <td><?= htmlspecialchars($g['exam_date']) ?></td>
            <td>
              <a href="edit_grade.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
              <a href="delete_grade.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this grade?')">🗑️ Delete</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center">No grades found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
