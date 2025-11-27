<?php
// Public/admin view of a single instructor profile
session_start();
require 'db_connect.php';
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['instructor_id'])) { header('Location: login.php'); exit; }

$instructor_id = (int)($_GET['id'] ?? ($_SESSION['instructor_id'] ?? 0));
if (!$instructor_id) { echo "Instructor not found."; exit; }

$stmt = $pdo->prepare("SELECT * FROM instructors WHERE instructor_id = :id");
$stmt->execute([':id'=>$instructor_id]);
$i = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$i) { echo "Instructor not found."; exit; }

$students = $pdo->prepare("
    SELECT sp.id, sp.name, sp.student_id, sp.track, sp.cohort
    FROM student_instructors si
    JOIN student_profiles sp ON sp.id = si.student_id
    WHERE si.instructor_id = :id
    ORDER BY sp.name
");
$students->execute([':id'=>$instructor_id]);
$assigned_students = $students->fetchAll(PDO::FETCH_ASSOC);

$courses = $pdo->prepare("
    SELECT c.course_id, c.title
    FROM course_instructors ci
    JOIN courses c ON c.course_id = ci.course_id
    WHERE ci.instructor_id = :id
    ORDER BY c.title
");
$courses->execute([':id'=>$instructor_id]);
$assigned_courses = $courses->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Instructor Profile</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= htmlspecialchars($i['name']) ?></h3>
    <a href="instrmanage.php" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="card-title mb-2">Details</h6>
          <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($i['email'] ?? '—') ?></p>
          <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($i['phone'] ?? '—') ?></p>
          <p class="mb-1"><strong>Role:</strong> <?= htmlspecialchars($i['role'] ?? '—') ?></p>
          <p class="mb-1"><strong>Status:</strong> <?= htmlspecialchars($i['status'] ?? '—') ?></p>
          <p class="mb-1"><strong>Specialization:</strong> <?= htmlspecialchars($i['specialization'] ?? '—') ?></p>
          <p class="mb-1"><strong>Base:</strong> <?= htmlspecialchars($i['base'] ?? '—') ?></p>
          <p class="mb-1"><strong>Joined:</strong> <?= htmlspecialchars($i['created_at'] ?? '—') ?></p>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <h6 class="card-title mb-2">Assigned Courses</h6>
          <ul class="list-group list-group-flush">
            <?php foreach ($assigned_courses as $c): ?>
              <li class="list-group-item"><?= htmlspecialchars($c['title']) ?></li>
            <?php endforeach; ?>
            <?php if (empty($assigned_courses)): ?>
              <li class="list-group-item text-center">No courses assigned.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="card-title mb-2">Assigned Students</h6>
          <table class="table table-sm table-striped">
            <thead class="table-dark"><tr><th>Name</th><th>ID</th><th>Track</th><th>Cohort</th></tr></thead>
            <tbody>
              <?php foreach ($assigned_students as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['name']) ?></td>
                  <td><?= htmlspecialchars($s['student_id']) ?></td>
                  <td><?= htmlspecialchars($s['track'] ?? '') ?></td>
                  <td><?= htmlspecialchars($s['cohort'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($assigned_students)): ?>
                <tr><td colspan="4" class="text-center">No students assigned.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
