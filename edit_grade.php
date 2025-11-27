<?php
// edit_grades.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all students for dropdown
$students = $pdo->query("SELECT id, student_id, name FROM student_profiles ORDER BY name ASC")->fetchAll();

// Get selected student_id from query string
$student_id = $_GET['student_id'] ?? null;
$student = null;
$courses = [];
$grades = [];

if ($student_id) {
    // Fetch student info
    $studentStmt = $pdo->prepare("SELECT id, student_id, name FROM student_profiles WHERE id = ?");
    $studentStmt->execute([$student_id]);
    $student = $studentStmt->fetch();

    // Fetch all courses
    $courses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();

    // Fetch existing grades for this student
    $gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id = ?");
    $gradesStmt->execute([$student_id]);
    foreach ($gradesStmt->fetchAll() as $g) {
        $grades[$g['course_id']] = $g;
    }

    // Handle form submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST['grades'] as $course_id => $data) {
            $grade     = $data['grade'] ?? null;
            $score     = $data['score'] ?? null;
            $comments  = $data['comments'] ?? null;

            if (isset($grades[$course_id])) {
                // Update existing grade
                $stmt = $pdo->prepare("
                    UPDATE grades 
                    SET grade = :grade, score = :score, comments = :comments
                    WHERE student_id = :student_id AND course_id = :course_id
                ");
            } else {
                // Insert new grade
                $stmt = $pdo->prepare("
                    INSERT INTO grades (student_id, course_id, grade, score, comments)
                    VALUES (:student_id, :course_id, :grade, :score, :comments)
                ");
            }

            $stmt->execute([
                ':student_id' => $student_id,
                ':course_id'  => $course_id,
                ':grade'      => $grade,
                ':score'      => $score,
                ':comments'   => $comments
            ]);
        }

        $_SESSION['success'] = "✅ Grades updated successfully!";
        header("Location: edit_grade.php?student_id=" . $student_id);
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
    <select name="student_id" class="form-select" required>
      <option value="">-- Choose Student --</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= $s['id'] ?>" <?= ($student_id == $s['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['student_id']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary mt-2">Load Grades</button>
  </form>

  <?php if ($student): ?>
    <h4 class="mb-3">Grades for <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)</h4>

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
            <th>Score</th>
            <th>Comments</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): 
            $existing = $grades[$c['id']] ?? null;
          ?>
          <tr>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td>
              <input type="text" name="grades[<?= $c['id'] ?>][grade]" 
                     value="<?= htmlspecialchars($existing['grade'] ?? '') ?>" 
                     class="form-control form-control-sm" placeholder="A, B+, B, C, etc.">
            </td>
            <td>
              <input type="number" step="1" min="0" max="100" name="grades[<?= $c['id'] ?>][score]" 
                     value="<?= htmlspecialchars($existing['score'] ?? '') ?>" 
                     class="form-control form-control-sm" placeholder="0-100">
            </td>
            <td>
              <input type="text" name="grades[<?= $c['id'] ?>][comments]" 
                     value="<?= htmlspecialchars($existing['comments'] ?? '') ?>" 
                     class="form-control form-control-sm" placeholder="Optional comments">
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

<script>
// Auto-calculate grade based on score
function calculateGrade(score) {
    score = parseFloat(score);
    
    if (isNaN(score) || score < 0 || score > 100) {
        return '';
    }
    
    // Grade scale: A=95-100, A-=90-94, B+=85-89, B=80-84, B-=75-79, 
    // C+=70-74, C=65-69, C-=60-64, D+=55-59, D=50-54, D-=0-49
    if (score >= 95) return 'A';
    if (score >= 90) return 'A-';
    if (score >= 85) return 'B+';
    if (score >= 80) return 'B';
    if (score >= 75) return 'B-';
    if (score >= 70) return 'C+';
    if (score >= 65) return 'C';
    if (score >= 60) return 'C-';
    if (score >= 55) return 'D+';
    if (score >= 50) return 'D';
    return 'D-';
}

// Add event listeners to all score inputs
document.addEventListener('DOMContentLoaded', function() {
    const scoreInputs = document.querySelectorAll('input[name*="[score]"]');
    
    scoreInputs.forEach(function(scoreInput) {
        // Get the corresponding grade input
        const row = scoreInput.closest('tr');
        const gradeInput = row.querySelector('input[name*="[grade]"]');
        
        // Update grade when score changes
        scoreInput.addEventListener('input', function() {
            const grade = calculateGrade(this.value);
            if (grade && gradeInput) {
                gradeInput.value = grade;
            }
        });
        
        // Calculate grade on page load if score exists
        if (scoreInput.value) {
            const grade = calculateGrade(scoreInput.value);
            if (grade && gradeInput && !gradeInput.value) {
                gradeInput.value = grade;
            }
        }
    });
});
</script>
</body>
</html>
