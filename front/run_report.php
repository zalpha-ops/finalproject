<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = :id");
$stmt->execute([':id' => $id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { echo "Report not found."; exit; }

$params = $r['params'] ? json_decode($r['params'], true) : [];
$course_id = $params['course_id'] ?? null;

$data = [];
switch ($r['type']) {
    case 'course_avg':
        $sql = "
            SELECT c.title AS course_name, AVG(g.grade) AS avg_grade, COUNT(g.id) AS total_grades
            FROM grades g
            JOIN courses c ON g.course_id = c.course_id
            " . ($course_id ? "WHERE g.course_id = :course_id" : "") . "
            GROUP BY c.course_id
            ORDER BY avg_grade DESC
        ";
        $q = $pdo->prepare($sql);
        $q->execute($course_id ? [':course_id'=>$course_id] : []);
        $data = $q->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'course_students':
        $sql = "
            SELECT c.title AS course_name, COUNT(DISTINCT g.student_id) AS total_students
            FROM grades g
            JOIN courses c ON g.course_id = c.course_id
            " . ($course_id ? "WHERE g.course_id = :course_id" : "") . "
            GROUP BY c.course_id
        ";
        $q = $pdo->prepare($sql);
        $q->execute($course_id ? [':course_id'=>$course_id] : []);
        $data = $q->fetchAll(PDO::FETCH_ASSOC);
        break;

    default:
        echo "Unsupported report type.";
        exit;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Run Report - <?= htmlspecialchars($r['title']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-1"><?= htmlspecialchars($r['title']) ?></h2>
  <p class="text-muted"><?= nl2br(htmlspecialchars($r['description'])) ?></p>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <?php if ($r['type'] === 'course_avg'): ?>
          <th>Course</th><th>Average Grade</th><th>Total Grades</th>
        <?php elseif ($r['type'] === 'course_students'): ?>
          <th>Course</th><th>Total Students</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $row): ?>
        <tr>
          <?php if ($r['type'] === 'course_avg'): ?>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= number_format($row['avg_grade'], 2) ?>%</td>
            <td><?= $row['total_grades'] ?></td>
          <?php elseif ($r['type'] === 'course_students'): ?>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= $row['total_students'] ?></td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="manage_reports.php" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>
