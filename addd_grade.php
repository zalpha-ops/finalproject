<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) { 
    header('Location: instructor_login.php'); 
    exit; 
}
$instructor_id = (int)$_SESSION['instructor_id'];

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);

    if ($course_id > 0) {
        // Prevent duplicate assignment
        $check = $pdo->prepare("
            SELECT 1 FROM course_instructors 
            WHERE course_id = :cid AND instructor_id = :iid
            LIMIT 1
        ");
        $check->execute([':cid' => $course_id, ':iid' => $instructor_id]);

        if (!$check->fetch()) {
            $assign = $pdo->prepare("
                INSERT INTO course_instructors (course_id, instructor_id, assigned_date)
                VALUES (:cid, :iid, CURDATE())
            ");
            $assign->execute([':cid' => $course_id, ':iid' => $instructor_id]);
        }
    }

    header('Location: instructor_dashboard.php');
    exit;
}

// Load available courses (you can filter out already-assigned if you prefer)
$courses = $pdo->query("
    SELECT c.course_id, c.title 
    FROM courses c
    ORDER BY c.title
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Course</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-3">Assign Course</h4>
  <form method="post" class="card p-3 shadow-sm" style="max-width: 520px;">
    <div class="mb-3">
      <label class="form-label">Course</label>
      <select name="course_id" class="form-select" required>
        <option value="" disabled selected>Select a course</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int)$c['course_id'] ?>">
            <?= htmlspecialchars($c['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Assign</button>
      <a href="instructor_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
