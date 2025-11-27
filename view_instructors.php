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
    $instructorId = (int)$_GET['delete'];
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM instructors WHERE instructor_id = ?");
        $deleteStmt->execute([$instructorId]);
        $_SESSION['success'] = "Instructor deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting instructor: " . $e->getMessage();
    }
    header("Location: view_instructors.php");
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $instructorId = (int)$_GET['toggle_status'];
    try {
        $stmt = $pdo->prepare("UPDATE instructors SET status = IF(status = 'Active', 'Inactive', 'Active') WHERE instructor_id = ?");
        $stmt->execute([$instructorId]);
        $_SESSION['success'] = "Instructor status updated.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating status: " . $e->getMessage();
    }
    header("Location: view_instructors.php");
    exit;
}

// Fetch all instructors
$instructorsStmt = $pdo->query("
    SELECT instructor_id, name, username, email, phone, specialization, 
           status, created_at
    FROM instructors
    ORDER BY created_at DESC
");
$instructors = $instructorsStmt->fetchAll(PDO::FETCH_ASSOC);

$totalInstructors = count($instructors);
$activeInstructors = count(array_filter($instructors, fn($i) => $i['status'] === 'Active'));
$inactiveInstructors = $totalInstructors - $activeInstructors;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Instructors - Eagle Flight School</title>
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
        <h2 class="mb-2"><i class="fas fa-chalkboard-teacher me-2"></i>All Instructors</h2>
        <p class="mb-0 opacity-75">Manage and view all flight instructors</p>
      </div>
      <div>
        <a href="enroll_instructor.php" class="btn btn-light btn-lg me-2">
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
          <h6 class="text-muted">Total Instructors</h6>
          <h3 class="mb-0"><?= $totalInstructors ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm" style="border-left: 4px solid #28a745;">
        <div class="card-body">
          <h6 class="text-muted">Active Instructors</h6>
          <h3 class="mb-0 text-success"><?= $activeInstructors ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm" style="border-left: 4px solid #dc3545;">
        <div class="card-body">
          <h6 class="text-muted">Inactive Instructors</h6>
          <h3 class="mb-0 text-danger"><?= $inactiveInstructors ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Instructors Table -->
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Specialization</th>
              <th>Status</th>
              <th>Enrolled</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($instructors)): ?>
              <tr>
                <td colspan="9" class="text-center py-4">
                  <p class="text-muted mb-3">No instructors enrolled yet.</p>
                  <a href="enroll_instructor.php" class="btn btn-primary">Enroll First Instructor</a>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($instructors as $instructor): ?>
                <tr>
                  <td><?= $instructor['instructor_id'] ?></td>
                  <td><strong><?= htmlspecialchars($instructor['name']) ?></strong></td>
                  <td><?= htmlspecialchars($instructor['username']) ?></td>
                  <td><?= htmlspecialchars($instructor['email'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($instructor['phone'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($instructor['specialization'] ?? '-') ?></td>
                  <td>
                    <a href="?toggle_status=<?= $instructor['instructor_id'] ?>" 
                       class="badge status-badge <?= $instructor['status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>"
                       onclick="return confirm('Toggle status for <?= htmlspecialchars($instructor['username']) ?>?')">
                      <?= $instructor['status'] ?>
                    </a>
                  </td>
                  <td><small><?= date('M d, Y', strtotime($instructor['created_at'])) ?></small></td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="reset_instructor_password.php?id=<?= $instructor['instructor_id'] ?>" 
                         class="btn btn-outline-warning" title="Reset Password">
                        🔑
                      </a>
                      <a href="?delete=<?= $instructor['instructor_id'] ?>" 
                         class="btn btn-outline-danger" title="Delete"
                         onclick="return confirm('Delete <?= htmlspecialchars($instructor['username']) ?>? This cannot be undone!')">
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
        <a href="enroll_instructor.php" class="btn btn-purple btn-sm">
          <i class="fas fa-plus me-1"></i>Enroll New Instructor
        </a>
        <a href="view_students.php" class="btn btn-outline-purple btn-sm">
          <i class="fas fa-users me-1"></i>View Students
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
