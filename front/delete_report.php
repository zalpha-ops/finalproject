<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$id = (int)($_GET['id'] ?? 0);
$report = $pdo->prepare("SELECT * FROM reports WHERE id = :id");
$report->execute([':id' => $id]);
$r = $report->fetch(PDO::FETCH_ASSOC);
if (!$r) { echo "Report not found."; exit; }

$courses = $pdo->query("SELECT course_id, title FROM courses ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$params = $r['params'] ? json_decode($r['params'], true) : [];
$current_course_id = $params['course_id'] ?? '';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type  = $_POST['type'] ?? 'course_avg';
    $course_id = $_POST['course_id'] ?: null;
    $description = trim($_POST['description'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if (!in_array($type, ['course_avg','course_students'])) $errors[] = 'Invalid type.';

    if (empty($errors)) {
        $newParams = null;
        if ($course_id) $newParams = json_encode(['course_id' => (int)$course_id]);

        $stmt = $pdo->prepare("UPDATE reports SET title = :title, description = :description, type = :type, params = :params WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':type' => $type,
            ':params' => $newParams,
            ':id' => $id
        ]);

        header("Location: manage_reports.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Report</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2>Edit Report</h2>

  <?php if ($errors): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($r['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($r['description']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select" required>
        <option value="course_avg" <?= $r['type']==='course_avg'?'selected':''; ?>>Average grade per course</option>
        <option value="course_students" <?= $r['type']==='course_students'?'selected':''; ?>>Students per course</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Optional filter: Course</label>
      <select name="course_id" class="form-select">
        <option value="">-- All Courses --</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['course_id'] ?>" <?= $current_course_id==$c['course_id']?'selected':''; ?>>
            <?= htmlspecialchars($c['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="manage_reports.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
