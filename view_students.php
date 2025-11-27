<?php
session_start();
require 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    try {
        $pdo->beginTransaction();
        
        // Delete from users table
        $deleteUserStmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $deleteUserStmt->execute([$userId]);
        
        // Get username for cleanup
        $usernameStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $usernameStmt->execute([$userId]);
        $username = $usernameStmt->fetchColumn();
        
        if ($username) {
            // Delete from student_profiles if exists
            try {
                $deleteProfileStmt = $pdo->prepare("DELETE FROM student_profiles WHERE username = ?");
                $deleteProfileStmt->execute([$username]);
            } catch (Exception $e) {}
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Student deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting student: " . $e->getMessage();
    }
    header("Location: view_students.php");
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $userId = (int)$_GET['toggle_status'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$userId]);
        $_SESSION['success'] = "Student status updated.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating status: " . $e->getMessage();
    }
    header("Location: view_students.php");
    exit;
}

// Fetch all students
$studentsStmt = $pdo->query("
    SELECT u.id, u.username, u.status, u.created_at,
           sp.name, sp.student_id, sp.email, sp.phone, sp.cohort, sp.track
    FROM users u
    LEFT JOIN student_profiles sp ON u.username = sp.username
    WHERE u.role = 'student'
    ORDER BY u.created_at DESC
");
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

$totalStudents = count($students);
$activeStudents = count(array_filter($students, fn($s) => $s['status'] === 'active'));
$inactiveStudents = $totalStudents - $activeStudents;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Students - Eagle Flight School</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-purple: #6B46C1;
      --dark-purple: #4C1D95;
      --light-purple: #9333EA;
    }
    
    body {
      background: linear-gradient(135deg, #0F0F0F 0%, #4C1D95 50%, #6B46C1 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .page-header {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      color: white;
      padding: 30px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
    }
    
    .stats-card {
      background: white;
      border-radius: 15px;
      border-left: 5px solid var(--primary-purple);
      transition: all 0.3s;
    }
    
    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(107, 70, 193, 0.3);
    }
    
    .card {
      border-radius: 15px;
      border: none;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .table thead {
      background: linear-gradient(135deg, var(--primary-purple), var(--dark-purple));
    }
    
    .status-badge {
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .status-badge:hover {
      transform: scale(1.1);
    }
    
    .btn-purple {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      border: none;
      color: white;
    }
    
    .btn-purple:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
      color: white;
    }
    
    .btn-outline-purple {
      border: 2px solid var(--primary-purple);
      color: var(--primary-purple);
      background: white;
    }
    
    .btn-outline-purple:hover {
      background: var(--primary-purple);
      color: white;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="mb-2"><i class="fas fa-user-graduate me-2"></i>All Students</h2>
        <p class="mb-0 opacity-75">Manage and view all enrolled students</p>
      </div>
      <div>
        <a href="enroll_student.php" class="btn btn-light btn-lg me-2">
          <i class="fas fa-plus me-2"></i>Enroll New
        </a>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-lg">
          <i class="fas fa-arrow-left me-2"></i>Dashboard
        </a>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Statistics -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card stats-card shadow-sm">
        <div class="card-body">
          <h6 class="text-muted">Total Students</h6>
          <h3 class="mb-0"><?= $totalStudents ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm" style="border-left: 4px solid #28a745;">
        <div class="card-body">
          <h6 class="text-muted">Active Students</h6>
          <h3 class="mb-0 text-success"><?= $activeStudents ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm" style="border-left: 4px solid #dc3545;">
        <div class="card-body">
          <h6 class="text-muted">Inactive Students</h6>
          <h3 class="mb-0 text-danger"><?= $inactiveStudents ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Students Table -->
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Username</th>
              <th>Student ID</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Cohort</th>
              <th>Track</th>
              <th>Status</th>
              <th>Enrolled</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($students)): ?>
              <tr>
                <td colspan="11" class="text-center py-4">
                  <p class="text-muted mb-3">No students enrolled yet.</p>
                  <a href="enroll_student.php" class="btn btn-primary">Enroll First Student</a>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($students as $student): ?>
                <tr>
                  <td><?= $student['id'] ?></td>
                  <td><strong><?= htmlspecialchars($student['name'] ?? 'N/A') ?></strong></td>
                  <td><?= htmlspecialchars($student['username']) ?></td>
                  <td><?= htmlspecialchars($student['student_id'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($student['email'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($student['phone'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($student['cohort'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($student['track'] ?? '-') ?></td>
                  <td>
                    <a href="?toggle_status=<?= $student['id'] ?>" 
                       class="badge status-badge <?= $student['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>"
                       onclick="return confirm('Toggle status for <?= htmlspecialchars($student['username']) ?>?')">
                      <?= ucfirst($student['status']) ?>
                    </a>
                  </td>
                  <td><small><?= date('M d, Y', strtotime($student['created_at'])) ?></small></td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="edit_student.php?id=<?= $student['id'] ?>" 
                         class="btn btn-outline-primary" title="Edit">
                        ✏️
                      </a>
                      <a href="reset_student_password.php?id=<?= $student['id'] ?>" 
                         class="btn btn-outline-warning" title="Reset Password">
                        🔑
                      </a>
                      <a href="?delete=<?= $student['id'] ?>" 
                         class="btn btn-outline-danger" title="Delete"
                         onclick="return confirm('Delete <?= htmlspecialchars($student['username']) ?>? This cannot be undone!')">
                        🗑️
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card shadow-sm mt-4">
    <div class="card-body">
      <h6 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
      <div class="d-flex gap-2 flex-wrap">
        <a href="enroll_student.php" class="btn btn-purple btn-sm">
          <i class="fas fa-plus me-1"></i>Enroll New Student
        </a>
        <a href="manage_grades.php" class="btn btn-outline-purple btn-sm">
          <i class="fas fa-chart-line me-1"></i>Manage Grades
        </a>
        <a href="manage_courses.php" class="btn btn-outline-purple btn-sm">
          <i class="fas fa-book me-1"></i>Manage Courses
        </a>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
