<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) { 
    header('Location: instructor_login.php'); 
    exit; 
}
$instructor_id = (int)$_SESSION['instructor_id'];

// Get instructor's assigned students
$studentsStmt = $pdo->prepare("
    SELECT sp.id, sp.name, sp.student_id
    FROM student_instructors si
    JOIN student_profiles sp ON si.student_id = sp.id
    WHERE si.instructor_id = :instructor_id
    ORDER BY sp.name
");
$studentsStmt->execute([':instructor_id' => $instructor_id]);
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

$selected_student_id = (int)($_GET['student_id'] ?? 0);
$student = null;
$courses = [];
$grades = [];

if ($selected_student_id > 0) {
    // Verify student is assigned to this instructor
    $studentStmt = $pdo->prepare("
        SELECT sp.id, sp.name, sp.student_id, sp.email
        FROM student_instructors si
        JOIN student_profiles sp ON si.student_id = sp.id
        WHERE si.instructor_id = :instructor_id AND sp.id = :student_id
    ");
    $studentStmt->execute([
        ':instructor_id' => $instructor_id,
        ':student_id' => $selected_student_id
    ]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // Get instructor's courses
        $coursesStmt = $pdo->prepare("
            SELECT c.course_id, c.title, c.description
            FROM course_instructors ci
            JOIN courses c ON ci.course_id = c.course_id
            WHERE ci.instructor_id = :instructor_id
            ORDER BY c.title
        ");
        $coursesStmt->execute([':instructor_id' => $instructor_id]);
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get existing grades
        $gradesStmt = $pdo->prepare("
            SELECT g.*, c.title AS course_title
            FROM grades g
            JOIN courses c ON g.course_id = c.course_id
            WHERE g.student_id = :student_id
        ");
        $gradesStmt->execute([':student_id' => $selected_student_id]);
        foreach ($gradesStmt->fetchAll(PDO::FETCH_ASSOC) as $g) {
            $grades[$g['course_id']] = $g;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['grades'] as $course_id => $data) {
                $marks = $data['marks'] !== '' ? (int)$data['marks'] : null;
                $exam_date = $data['exam_date'] !== '' ? $data['exam_date'] : null;
                
                // Auto-calculate grade and comment
                $grade = null;
                $comment = '';
                $status = 'In Progress';
                
                if ($marks !== null) {
                    if ($marks >= 80) {
                        $grade = 'A';
                        $comment = 'Excellent work! Keep shining ✨';
                        $status = 'Pass';
                    } elseif ($marks >= 70) {
                        $grade = 'B';
                        $comment = 'Very good performance 👍';
                        $status = 'Pass';
                    } elseif ($marks >= 60) {
                        $grade = 'C';
                        $comment = 'Good effort, room to improve 📘';
                        $status = 'Pass';
                    } elseif ($marks >= 50) {
                        $grade = 'D';
                        $comment = 'Fair, but needs more practice 🛠️';
                        $status = 'Pass';
                    } else {
                        $grade = 'F';
                        $comment = 'Needs improvement, don\'t give up 💪';
                        $status = 'Fail';
                    }
                }
                
                // Check if grade exists
                if (isset($grades[$course_id])) {
                    // Update existing
                    $updateStmt = $pdo->prepare("
                        UPDATE grades 
                        SET grade = :grade, marks = :marks, comment = :comment,
                            status = :status, exam_date = :exam_date
                        WHERE student_id = :student_id AND course_id = :course_id
                    ");
                    $updateStmt->execute([
                        ':student_id' => $selected_student_id,
                        ':course_id' => $course_id,
                        ':grade' => $grade,
                        ':marks' => $marks,
                        ':comment' => $comment,
                        ':status' => $status,
                        ':exam_date' => $exam_date
                    ]);
                } else {
                    // Insert new
                    if ($marks !== null) {
                        $insertStmt = $pdo->prepare("
                            INSERT INTO grades (student_id, course_id, grade, marks, comment, status, exam_date)
                            VALUES (:student_id, :course_id, :grade, :marks, :comment, :status, :exam_date)
                        ");
                        $insertStmt->execute([
                            ':student_id' => $selected_student_id,
                            ':course_id' => $course_id,
                            ':grade' => $grade,
                            ':marks' => $marks,
                            ':comment' => $comment,
                            ':status' => $status,
                            ':exam_date' => $exam_date
                        ]);
                    }
                }
            }
            
            $_SESSION['success'] = "✅ Grades updated successfully!";
            header("Location: instructor_manage_grades.php?student_id=" . $selected_student_id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Student Grades</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    .marks-input:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .grade-cell {
      font-weight: bold;
      font-size: 1.1rem;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📊 Manage Student Grades</h3>
    <a href="instructor_dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
  </div>

  <!-- Student Selection -->
  <form method="get" class="card p-3 shadow-sm mb-4">
    <label class="form-label fw-bold">Select Student</label>
    <div class="row g-2">
      <div class="col-md-10">
        <select name="student_id" class="form-select" required>
          <option value="">-- Choose Student --</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($selected_student_id == $s['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['student_id']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Load Grades</button>
      </div>
    </div>
  </form>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($student): ?>
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <h5 class="card-title">Student Information</h5>
        <div class="row">
          <div class="col-md-4">
            <strong>Name:</strong> <?= htmlspecialchars($student['name']) ?>
          </div>
          <div class="col-md-4">
            <strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?>
          </div>
          <div class="col-md-4">
            <strong>Email:</strong> <?= htmlspecialchars($student['email'] ?? 'N/A') ?>
          </div>
        </div>
      </div>
    </div>

    <?php if (empty($courses)): ?>
      <div class="alert alert-warning">
        <strong>No courses assigned!</strong><br>
        You need to have courses assigned to you before you can manage grades.
        <a href="assign_course.php" class="btn btn-sm btn-primary mt-2">Assign Courses</a>
      </div>
    <?php else: ?>
      <form method="post" class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Course Grades</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-dark">
                <tr>
                  <th style="width: 25%;">Course</th>
                  <th style="width: 30%;">Description</th>
                  <th style="width: 10%;">Marks</th>
                  <th style="width: 8%;">Grade</th>
                  <th style="width: 10%;">Status</th>
                  <th style="width: 17%;">Exam Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($courses as $c): 
                  $existing = $grades[$c['course_id']] ?? null;
                ?>
                <tr>
                  <td><strong><?= htmlspecialchars($c['title']) ?></strong></td>
                  <td><small><?= htmlspecialchars($c['description'] ?? '') ?></small></td>
                  <td>
                    <input type="number" 
                           name="grades[<?= $c['course_id'] ?>][marks]" 
                           value="<?= htmlspecialchars($existing['marks'] ?? '') ?>" 
                           class="form-control form-control-sm marks-input"
                           min="0" max="100" step="1"
                           placeholder="0-100"
                           onchange="updateRowPreview(this, <?= $c['course_id'] ?>)">
                  </td>
                  <td class="grade-cell">
                    <span id="grade_<?= $c['course_id'] ?>" class="badge bg-secondary">
                      <?= htmlspecialchars($existing['grade'] ?? '-') ?>
                    </span>
                  </td>
                  <td>
                    <span id="status_<?= $c['course_id'] ?>" class="badge bg-info">
                      <?= htmlspecialchars($existing['status'] ?? 'Not Graded') ?>
                    </span>
                  </td>
                  <td>
                    <input type="date" 
                           name="grades[<?= $c['course_id'] ?>][exam_date]" 
                           value="<?= htmlspecialchars($existing['exam_date'] ?? '') ?>" 
                           class="form-control form-control-sm">
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-success btn-lg">
              💾 Save All Grades
            </button>
            <a href="instructor_manage_grades.php" class="btn btn-outline-secondary btn-lg">
              Cancel
            </a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateRowPreview(input, courseId) {
  const marks = parseInt(input.value);
  const gradeBadge = document.getElementById('grade_' + courseId);
  const statusBadge = document.getElementById('status_' + courseId);
  
  if (isNaN(marks) || marks < 0 || marks > 100) {
    gradeBadge.textContent = '-';
    gradeBadge.className = 'badge bg-secondary';
    statusBadge.textContent = 'Not Graded';
    statusBadge.className = 'badge bg-info';
    return;
  }
  
  let grade, status, gradeColor, statusColor;
  
  if (marks >= 80) {
    grade = 'A';
    status = 'Pass';
    gradeColor = 'bg-success';
    statusColor = 'bg-success';
  } else if (marks >= 70) {
    grade = 'B';
    status = 'Pass';
    gradeColor = 'bg-info';
    statusColor = 'bg-success';
  } else if (marks >= 60) {
    grade = 'C';
    status = 'Pass';
    gradeColor = 'bg-warning';
    statusColor = 'bg-success';
  } else if (marks >= 50) {
    grade = 'D';
    status = 'Pass';
    gradeColor = 'bg-warning';
    statusColor = 'bg-success';
  } else {
    grade = 'F';
    status = 'Fail';
    gradeColor = 'bg-danger';
    statusColor = 'bg-danger';
  }
  
  gradeBadge.textContent = grade;
  gradeBadge.className = 'badge ' + gradeColor;
  statusBadge.textContent = status;
  statusBadge.className = 'badge ' + statusColor;
}
</script>
</body>
</html>
