<?php
// manage_student_grades.php
session_start();
require 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: quick_login.php");
    exit;
}

// Fetch all students for dropdown
try {
    $students = $pdo->query("SELECT id, name, username FROM student_profiles ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $students = [];
}

$student_id = $_GET['student_id'] ?? null;
$student = null;
$courses = [];
$grades = [];

if ($student_id) {
    // Fetch student info
    try {
        $studentStmt = $pdo->prepare("SELECT id, name, username FROM student_profiles WHERE id = ?");
        $studentStmt->execute([$student_id]);
        $student = $studentStmt->fetch();
    } catch (Exception $e) {
        $student = null;
    }

    // Fetch all courses
    try {
        $courses = $pdo->query("SELECT id, course_name as title, description FROM courses ORDER BY course_name ASC")->fetchAll();
    } catch (Exception $e) {
        $courses = [];
    }

    // Fetch existing grades
    try {
        $gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_username = (SELECT username FROM student_profiles WHERE id = ?)");
        $gradesStmt->execute([$student_id]);
        foreach ($gradesStmt->fetchAll() as $g) {
            $grades[$g['course_id']] = $g;
        }
    } catch (Exception $e) {
        $grades = [];
    }

    // Ensure every course has a grade record
    if ($student) {
        foreach ($courses as $c) {
            if (!isset($grades[$c['id']])) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO grades (student_username, course_id, grade, semester, year, points)
                        VALUES (:student_username, :course_id, NULL, 'Fall 2024', 2024, NULL)
                    ");
                    $stmt->execute([
                        ':student_username' => $student['username'],
                        ':course_id'  => $c['id']
                    ]);
                } catch (Exception $e) {
                    // Ignore duplicate errors
                }
            }
        }

        // Reload grades after insert
        try {
            $gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_username = ?");
            $gradesStmt->execute([$student['username']]);
            $grades = [];
            foreach ($gradesStmt->fetchAll() as $g) {
                $grades[$g['course_id']] = $g;
            }
        } catch (Exception $e) {
            $grades = [];
        }
    }

    // Handle form submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST['grades'] as $course_id => $data) {
            $marks     = $data['marks'] !== '' ? (int)$data['marks'] : null;
            $grade     = $data['grade'] !== '' ? $data['grade'] : null;
            $comment   = $data['comment'] ?? '';
            $status    = $data['status'] ?? 'In Progress';
            $exam_date = $data['exam_date'] !== '' ? $data['exam_date'] : null;

            // Auto-grading scale (server-side safety)
            if ($marks !== null) {
                if ($marks >= 80) {
                    $grade   = 'A';
                    $comment = 'Excellent work! Keep shining ✨';
                } elseif ($marks >= 70) {
                    $grade   = 'B';
                    $comment = 'Very good performance 👍';
                } elseif ($marks >= 60) {
                    $grade   = 'C';
                    $comment = 'Good effort, room to improve 📘';
                } elseif ($marks >= 50) {
                    $grade   = 'D';
                    $comment = 'Fair, but needs more practice 🛠️';
                } else {
                    $grade   = 'F';
                    $comment = 'Needs improvement, don’t give up 💪';
                }
            }

            $stmt = $pdo->prepare("
                UPDATE grades 
                SET grade = :grade, marks = :marks, comment = :comment,
                    status = :status, exam_date = :exam_date
                WHERE student_id = :student_id AND course_id = :course_id
            ");
            $stmt->execute([
                ':student_id' => $student_id,
                ':course_id'  => $course_id,
                ':grade'      => $grade,
                ':marks'      => $marks,
                ':comment'    => $comment,
                ':status'     => $status,
                ':exam_date'  => $exam_date
            ]);
        }

        $_SESSION['success'] = "✅ Grades updated successfully!";
        header("Location: manage_grades.php?student_id=" . $student_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Student Grades</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📚 Manage Student Grades</h2>

  <!-- Student dropdown -->
  <form method="get" class="mb-4 card p-3">
    <label class="form-label">Select Student</label>
    <select name="student_id" class="form-select" required>
      <option value="">-- Choose Student --</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= $s['id'] ?>" <?= ($student_id == $s['id']) ? 'selected' : '' ?>>
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
            <th>Description</th>
            <th>Grade</th>
            <th>Marks</th>
            <th>Comment</th>
            <th>Status</th>
            <th>Exam Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): 
            $existing = $grades[$c['course_id']] ?? null;
          ?>
          <tr>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td><?= htmlspecialchars($c['description']) ?></td>
            <td>
              <input type="text" name="grades[<?= $c['course_id'] ?>][grade]" 
                     value="<?= htmlspecialchars($existing['grade'] ?? '') ?>" 
                     class="form-control form-control-sm" readonly>
            </td>
            <td>
              <input type="number" name="grades[<?= $c['course_id'] ?>][marks]" 
                     value="<?= htmlspecialchars($existing['marks'] ?? '') ?>" 
                     class="form-control form-control-sm"
                     oninput="autoFillGradeComment(this, <?= $c['course_id'] ?>)">
            </td>
            <td>
              <input type="text" name="grades[<?= $c['course_id'] ?>][comment]" 
                     value="<?= htmlspecialchars($existing['comment'] ?? '') ?>" 
                     class="form-control form-control-sm" readonly>
            </td>
            <td>
              <select name="grades[<?= $c['course_id'] ?>][status]" class="form-select form-select-sm">
                <?php 
                $statuses = ['In Progress','Pass','Fail'];
                foreach ($statuses as $s) {
                  $sel = ($existing['status'] ?? '') === $s ? 'selected' : '';
                  echo "<option value='$s' $sel>$s</option>";
                }
                ?>
              </select>
            </td>
            <td>
              <input type="date" name="grades[<?= $c['course_id'] ?>][exam_date]" 
                     value="<?= htmlspecialchars($existing['exam_date'] ?? '') ?>" 
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

<!-- JavaScript for live auto-fill -->
<script>
function autoFillGradeComment(input, courseId) {
  const marks = parseInt(input.value);
  const gradeField = document.querySelector(`input[name="grades[${courseId}][grade]"]`);
  const commentField = document.querySelector(`input[name="grades[${courseId}][comment]"]`);
  
  if (isNaN(marks)) {
    gradeField.value = '';
    commentField.value = '';
    return;
  }
  
  if (marks >= 80) {
    gradeField.value = 'A';
    commentField.value = 'Excellent work! Keep shining ✨';
  } else if (marks >= 70) {
    gradeField.value = 'B';
    commentField.value = 'Very good performance 👍';
  } else if (marks >= 60) {
    gradeField.value = 'C';
    commentField.value = 'Good effort, room to improve 📘';
  } else if (marks >= 50) {
    gradeField.value = 'D';
    commentField.value = 'Fair, but needs more practice 🛠️';
  } else {
    gradeField.value = 'F';
    commentField.value = 'Needs improvement, don\'t give up 💪';
  }
}
</script>
</body>
</html>