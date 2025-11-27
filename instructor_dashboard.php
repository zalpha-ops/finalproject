<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['instructor_id'])) { header('Location: instructor_login.php'); exit; }
$instructor_id = (int)$_SESSION['instructor_id'];

// Instructor info
$ins = $pdo->prepare("SELECT * FROM instructors WHERE instructor_id = :id");
$ins->execute([':id'=>$instructor_id]);
$instructor = $ins->fetch(PDO::FETCH_ASSOC);

// Students
$students = $pdo->prepare("
    SELECT sp.id, sp.name, sp.student_id, sp.cohort, sp.track, sp.email, sp.phone
    FROM student_instructors si
    JOIN student_profiles sp ON si.student_id = sp.id
    WHERE si.instructor_id = :id
    ORDER BY sp.name
");
$students->execute([':id'=>$instructor_id]);
$assigned_students = $students->fetchAll(PDO::FETCH_ASSOC);

// Courses
$courses = $pdo->prepare("
    SELECT c.course_id, c.title, c.description, ci.assigned_date
    FROM course_instructors ci
    JOIN courses c ON ci.course_id = c.course_id
    WHERE ci.instructor_id = :id
    ORDER BY c.title
");
$courses->execute([':id'=>$instructor_id]);
$assigned_courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// Grades
$grades = $pdo->prepare("
    SELECT g.id, g.student_id, sp.name AS student_name, c.title AS course_title, g.grade, g.created_at
    FROM grades g
    JOIN student_profiles sp ON g.student_id = sp.id
    JOIN courses c ON g.course_id = c.course_id
    WHERE g.student_id IN (
        SELECT si.student_id FROM student_instructors si WHERE si.instructor_id = :id
    )
    ORDER BY g.created_at DESC
    LIMIT 20
");
$grades->execute([':id'=>$instructor_id]);
$recent_grades = $grades->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Dashboard - Eagle Flight School</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-purple: #6B46C1;
      --dark-purple: #4C1D95;
      --light-purple: #9333EA;
      --accent-purple: #A855F7;
      --black: #0F0F0F;
      --dark-gray: #1A1A1A;
      --white: #FFFFFF;
    }
    
    body {
      background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
    }
    
    .navbar-custom {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      padding: 1rem 0;
      box-shadow: 0 4px 20px rgba(107, 70, 193, 0.3);
    }
    
    .card {
      border-radius: 15px;
      border: 1px solid rgba(107, 70, 193, 0.2);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      background: var(--white);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(107, 70, 193, 0.4);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      color: var(--white);
      border-radius: 15px 15px 0 0 !important;
      padding: 1rem 1.5rem;
      font-weight: 600;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      border: none;
      transition: all 0.3s;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(107, 70, 193, 0.4);
    }
    
    .table {
      background: var(--white);
    }
    
    .table thead {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      color: var(--white);
    }
    
    .stat-card {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      color: var(--white);
      border-radius: 15px;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 10px 30px rgba(107, 70, 193, 0.3);
    }
    
    .stat-card h3 {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
    }
    
    .stat-card p {
      margin: 0;
      opacity: 0.9;
    }
  </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-custom navbar-dark">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1">
      <i class="fas fa-graduation-cap me-2"></i>
      Eagle Flight School - Instructor Portal
    </span>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        <i class="fas fa-user-tie me-2"></i>
        <?= htmlspecialchars($instructor['name'] ?? 'Instructor') ?>
      </span>
      <a href="instructor_logout.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="container-fluid py-4">
  <!-- Statistics Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card">
        <i class="fas fa-users fa-2x mb-2"></i>
        <h3><?= count($assigned_students) ?></h3>
        <p>Assigned Students</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <i class="fas fa-book fa-2x mb-2"></i>
        <h3><?= count($assigned_courses) ?></h3>
        <p>Assigned Courses</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <i class="fas fa-chart-line fa-2x mb-2"></i>
        <h3><?= count($recent_grades) ?></h3>
        <p>Recent Grades</p>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card mb-4">
    <div class="card-header">
      <i class="fas fa-bolt me-2"></i> Quick Actions
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 flex-wrap">
        <a href="instructor_add_grade.php" class="btn btn-success">
          <i class="fas fa-plus-circle me-2"></i> Add Grade
        </a>
        <a href="instructor_manage_grades.php" class="btn btn-primary">
          <i class="fas fa-chart-bar me-2"></i> Manage Grades
        </a>
        <?php if (!empty($assigned_courses)): ?>
        <div class="dropdown">
          <button class="btn btn-info dropdown-toggle" type="button" id="submissionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-alt me-2"></i> View Submissions
          </button>
          <ul class="dropdown-menu" aria-labelledby="submissionsDropdown">
            <?php foreach ($assigned_courses as $c): ?>
              <li>
                <a class="dropdown-item" href="instructor_view_submissions.php?course_id=<?= $c['course_id'] ?>">
                  <i class="fas fa-book me-2"></i><?= htmlspecialchars($c['title']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        <a href="assign_student.php" class="btn btn-info">
          <i class="fas fa-user-plus me-2"></i> Assign Student
        </a>
        <a href="assign_course.php" class="btn btn-warning">
          <i class="fas fa-book-open me-2"></i> Assign Course
        </a>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Students -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body p-3">
          <h6 class="card-title mb-2">Assigned Students</h6>
          <table class="table table-sm table-striped">
            <thead class="table-dark">
              <tr><th>Name</th><th>ID</th><th>Cohort</th><th>Track</th><th>Contact</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($assigned_students as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['name']) ?></td>
                  <td><?= htmlspecialchars($s['student_id']) ?></td>
                  <td><?= htmlspecialchars($s['cohort'] ?? '') ?></td>
                  <td><?= htmlspecialchars($s['track'] ?? '') ?></td>
                  <td>
                    <small><?= htmlspecialchars($s['email'] ?? '') ?></small><br>
                    <small><?= htmlspecialchars($s['phone'] ?? '') ?></small>
                  </td>
                  <td>
                    <a href="instructor_add_grade.php?student_id=<?= $s['id'] ?>" class="btn btn-sm btn-success">Add Grade</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($assigned_students)): ?>
                <tr><td colspan="6" class="text-center">No students assigned yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Courses -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body p-3">
          <h6 class="card-title mb-2">Assigned Courses</h6>
          <table class="table table-sm table-striped">
            <thead class="table-dark">
              <tr><th>Course</th><th>Description</th><th>Assigned Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($assigned_courses as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['title']) ?></td>
                  <td><small><?= htmlspecialchars($c['description'] ?? '') ?></small></td>
                  <td><small><?= htmlspecialchars($c['assigned_date'] ?? '') ?></small></td>
                  <td>
                    <a href="instructor_view_submissions.php?course_id=<?= $c['course_id'] ?>" 
                       class="btn btn-sm btn-primary">
                      <i class="fas fa-file-alt me-1"></i> View Submissions
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($assigned_courses)): ?>
                <tr><td colspan="4" class="text-center">No courses assigned yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Grades -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body p-3">
          <h6 class="card-title mb-2">Recent Grades (Assigned Students)</h6>
          <table class="table table-sm table-striped">
            <thead class="table-dark">
              <tr><th>Date</th><th>Student</th><th>Course</th><th>Grade</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent_grades as $g): ?>
                <tr>
                  <td><?= htmlspecialchars($g['created_at']) ?></td>
                  <td><?= htmlspecialchars($g['student_name']) ?></td>
                  <td><?= htmlspecialchars($g['course_title']) ?></td>
                  <td><?= htmlspecialchars($g['grade']) ?>%</td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($recent_grades)): ?>
                <tr><td colspan="4" class="text-center">No recent grades.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
