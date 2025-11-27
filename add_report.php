<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$courses = $pdo->query("SELECT course_id, title FROM courses ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type  = $_POST['type'] ?? 'course_avg';
    $course_id = $_POST['course_id'] ?: null;
    $description = trim($_POST['description'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if (!in_array($type, ['course_avg','course_students'])) $errors[] = 'Invalid type.';

    if (empty($errors)) {
        $params = null;
        if ($course_id) $params = json_encode(['course_id' => (int)$course_id]);

        $stmt = $pdo->prepare("INSERT INTO reports (title, description, type, params) VALUES (:title, :description, :type, :params)");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':type' => $type,
            ':params' => $params
        ]);

        header("Location: manage_reports.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Create Report</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2>Create Report</h2>

  <?php if ($errors): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select" required>
        <option value="course_avg">Average grade per course</option>
        <option value="course_students">Students per course</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Optional filter: Course</label>
      <select name="course_id" class="form-select">
        <option value="">-- All Courses --</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="manage_reports.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
