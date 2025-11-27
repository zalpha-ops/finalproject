<?php
// manage_report.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch grades with student + course info
$sql = "
    SELECT g.id, sp.name AS student_name, c.title AS course_name,
           g.grade, g.score, g.created_at,
           CASE 
               WHEN g.grade IN ('A', 'A-', 'B+', 'B', 'B-', 'C+', 'C') THEN 'Pass'
               WHEN g.grade IN ('D', 'F') THEN 'Fail'
               ELSE 'Pending'
           END AS status
    FROM grades g
    JOIN student_profiles sp ON g.student_id = sp.id
    JOIN courses c ON g.course_id = c.id
    ORDER BY g.created_at DESC
";
$stmt = $pdo->query($sql);
$grades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Report</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📊 Manage Report</h2>

  <div class="mb-3">
    <a href="add_grade.php" class="btn btn-primary">➕ Add Report</a>
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
                $status = isset($g['status']) ? $g['status'] : 'Pending';
                $badge = $status === 'Pass' ? 'success' : ($status === 'Fail' ? 'danger' : 'secondary');
              ?>
              <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span>
            </td>
            <td><?= isset($g['exam_date']) ? htmlspecialchars($g['exam_date']) : 'N/A' ?></td>
            <td>
              <a href="edit_grade.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
              <a href="delete_grade.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this report?')">🗑️ Delete</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center">No reports found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
