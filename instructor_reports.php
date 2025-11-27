<?php
// Admin: instructor-focused reports
session_start();
require 'db_connect.php';
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }

$type = $_GET['type'] ?? 'students_per_instructor';

// Base datasets
$data = [];

switch ($type) {
    case 'students_per_instructor':
        $stmt = $pdo->query("
            SELECT i.name AS instructor_name, COUNT(DISTINCT si.student_id) AS total_students
            FROM instructors i
            LEFT JOIN student_instructors si ON si.instructor_id = i.instructor_id
            GROUP BY i.instructor_id
            ORDER BY total_students DESC, i.name ASC
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'avg_grades_per_instructor':
        $stmt = $pdo->query("
            SELECT i.name AS instructor_name, AVG(g.grade) AS avg_grade, COUNT(g.id) AS total_grades
            FROM instructors i
            LEFT JOIN student_instructors si ON si.instructor_id = i.instructor_id
            LEFT JOIN grades g ON g.student_id = si.student_id
            GROUP BY i.instructor_id
            ORDER BY avg_grade DESC
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'courses_per_instructor':
        $stmt = $pdo->query("
            SELECT i.name AS instructor_name, COUNT(DISTINCT ci.course_id) AS total_courses
            FROM instructors i
            LEFT JOIN course_instructors ci ON ci.instructor_id = i.instructor_id
            GROUP BY i.instructor_id
            ORDER BY total_courses DESC, i.name ASC
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    default:
        echo "Unsupported instructor report."; exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Instructor Reports</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Instructor Reports</h3>
    <a href="manage_reports.php" class="btn btn-secondary btn-sm">Back</a>
  </div>

  <div class="mb-3">
    <a class="btn btn-outline-primary btn-sm" href="?type=students_per_instructor">Students per Instructor</a>
    <a class="btn btn-outline-primary btn-sm" href="?type=avg_grades_per_instructor">Average Grades per Instructor</a>
    <a class="btn btn-outline-primary btn-sm" href="?type=courses_per_instructor">Courses per Instructor</a>
  </div>

  <div class="card">
    <div class="card-body">
      <?php if ($type === 'students_per_instructor'): ?>
        <h6 class="card-title">Students per Instructor</h6>
        <table class="table table-bordered table-sm">
          <thead class="table-dark"><tr><th>Instructor</th><th>Total Students</th></tr></thead>
          <tbody>
            <?php foreach ($data as $row): ?>
              <tr><td><?= htmlspecialchars($row['instructor_name']) ?></td><td><?= (int)$row['total_students'] ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?><tr><td colspan="2" class="text-center">No data.</td></tr><?php endif; ?>
          </tbody>
        </table>
      <?php elseif ($type === 'avg_grades_per_instructor'): ?>
        <h6 class="card-title">Average Grades per Instructor</h6>
        <table class="table table-bordered table-sm">
          <thead class="table-dark"><tr><th>Instructor</th><th>Average Grade</th><th>Total Grades</th></tr></thead>
          <tbody>
            <?php foreach ($data as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['instructor_name']) ?></td>
                <td><?= $row['avg_grade'] !== null ? number_format((float)$row['avg_grade'], 2) . '%' : '—' ?></td>
                <td><?= (int)$row['total_grades'] ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?><tr><td colspan="3" class="text-center">No data.</td></tr><?php endif; ?>
          </tbody>
        </table>
      <?php elseif ($type === 'courses_per_instructor'): ?>
        <h6 class="card-title">Courses per Instructor</h6>
        <table class="table table-bordered table-sm">
          <thead class="table-dark"><tr><th>Instructor</th><th>Total Courses</th></tr></thead>
          <tbody>
            <?php foreach ($data as $row): ?>
              <tr><td><?= htmlspecialchars($row['instructor_name']) ?></td><td><?= (int)$row['total_courses'] ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?><tr><td colspan="2" class="text-center">No data.</td></tr><?php endif; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
