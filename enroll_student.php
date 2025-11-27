<?php
session_start();
require 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = null;
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $student_id = trim($_POST['student_id'] ?? '');
    $cohort = trim($_POST['cohort'] ?? '');
    $track = trim($_POST['track'] ?? '');
    
    // Validation
    if (empty($name) || empty($username) || empty($password)) {
        $error = "Name, username, and password are required.";
    } else {
        try {
            // Check if username already exists in users table
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            if ($checkStmt->fetch()) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Start transaction
                $pdo->beginTransaction();
                
                // 1. Create login account in users table
                $passwordHash = hash('sha256', $password);
                $insertUserStmt = $pdo->prepare("
                    INSERT INTO users (username, password_hash, role, status, created_at)
                    VALUES (:username, :password_hash, 'student', 'active', NOW())
                ");
                $insertUserStmt->execute([
                    ':username' => $username,
                    ':password_hash' => $passwordHash
                ]);
                $userId = $pdo->lastInsertId();
                
                // 2. Create student profile (if student_profiles table exists)
                try {
                    $insertProfileStmt = $pdo->prepare("
                        INSERT INTO student_profiles (username, name, student_id, email, phone, cohort, track, status, created_at)
                        VALUES (:username, :name, :student_id, :email, :phone, :cohort, :track, 'Active', NOW())
                    ");
                    $insertProfileStmt->execute([
                        ':username' => $username,
                        ':name' => $name,
                        ':student_id' => $student_id ?: $username,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':cohort' => $cohort,
                        ':track' => $track
                    ]);
                } catch (Exception $e) {
                    // student_profiles table might not exist, that's okay
                }
                
                // 3. Create student record in students table (if exists)
                try {
                    $insertStudentStmt = $pdo->prepare("
                        INSERT INTO students (name, email, created_at)
                        VALUES (:name, :email, NOW())
                    ");
                    $insertStudentStmt->execute([
                        ':name' => $name,
                        ':email' => $email
                    ]);
                } catch (Exception $e) {
                    // students table might not exist, that's okay
                }
                
                // Commit transaction
                $pdo->commit();
                
                $success = "✅ Student enrolled successfully!<br>
                           <strong>Username:</strong> $username<br>
                           <strong>Password:</strong> $password<br>
                           <strong>Login URL:</strong> <a href='login.php'>login.php</a>";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error enrolling student: " . $e->getMessage();
        }
    }
}

// Get statistics
try {
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
} catch (Exception $e) {
    $totalStudents = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enroll New Student - Eagle Flight School</title>
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
    
    .enrollment-header {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      color: white;
      padding: 40px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
    }
    
    .card {
      border-radius: 15px;
      border: none;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .info-box {
      background: white;
      border-left: 5px solid var(--primary-purple);
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .required-field::after {
      content: " *";
      color: #dc3545;
      font-weight: bold;
    }
    
    .btn-purple {
      background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
      border: none;
      color: white;
      transition: all 0.3s;
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
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-purple);
      box-shadow: 0 0 0 0.25rem rgba(107, 70, 193, 0.25);
    }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="enrollment-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="mb-2"><i class="fas fa-user-graduate me-2"></i>Enroll New Student</h1>
        <p class="mb-0 opacity-75">Add a new student to Eagle Flight School</p>
      </div>
      <a href="admin_dashboard.php" class="btn btn-light btn-lg">
        <i class="fas fa-arrow-left me-2"></i>Dashboard
      </a>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <strong>⚠️ Error:</strong> <?= $error ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <?= $success ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-4">Student Information</h5>
          
          <form method="post" id="enrollmentForm">
            <!-- Personal Information -->
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required-field">Full Name</label>
                <input type="text" name="name" class="form-control" required 
                       placeholder="e.g., John Doe">
                <small class="text-muted">Student's full legal name</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Student ID</label>
                <input type="text" name="student_id" class="form-control" 
                       placeholder="e.g., STU2024001">
                <small class="text-muted">Optional - auto-generated if empty</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" 
                       placeholder="student@example.com">
                <small class="text-muted">For communication purposes</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" 
                       placeholder="+1 (555) 123-4567">
                <small class="text-muted">Contact number</small>
              </div>
            </div>

            <hr class="my-4">

            <!-- Login Credentials -->
            <h6 class="mb-3">Login Credentials</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required-field">Username</label>
                <input type="text" name="username" id="usernameInput" class="form-control" required 
                       placeholder="e.g., johndoe" pattern="[a-zA-Z0-9_]+" 
                       title="Only letters, numbers, and underscores">
                <small class="text-muted">Used for login - no spaces</small>
              </div>

              <div class="col-md-6">
                <label class="form-label required-field">Password</label>
                <div class="input-group">
                  <input type="password" name="password" id="passwordInput" class="form-control" required 
                         placeholder="Enter password" minlength="6">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    👁️ Show
                  </button>
                </div>
                <small class="text-muted">Minimum 6 characters</small>
              </div>

              <div class="col-12">
                <button type="button" class="btn btn-sm btn-outline-primary" id="generatePassword">
                  🎲 Generate Random Password
                </button>
              </div>
            </div>

            <hr class="my-4">

            <!-- Academic Information -->
            <h6 class="mb-3">Academic Information</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Cohort</label>
                <input type="text" name="cohort" class="form-control" 
                       placeholder="e.g., 2024 Spring">
                <small class="text-muted">Student's cohort or batch</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Track/Program</label>
                <select name="track" class="form-select">
                  <option value="">Select Track</option>
                  <option value="Private Pilot">Private Pilot</option>
                  <option value="Commercial Pilot">Commercial Pilot</option>
                  <option value="Instrument Rating">Instrument Rating</option>
                  <option value="Flight Instructor">Flight Instructor</option>
                  <option value="Multi-Engine">Multi-Engine</option>
                </select>
                <small class="text-muted">Training program</small>
              </div>
            </div>

            <hr class="my-4">

            <!-- Submit Buttons -->
            <div class="d-flex gap-2 flex-wrap">
              <button type="submit" class="btn btn-purple btn-lg">
                <i class="fas fa-check-circle me-2"></i>Enroll Student
              </button>
              <a href="view_students.php" class="btn btn-outline-purple btn-lg">
                <i class="fas fa-users me-2"></i>View All Students
              </a>
              <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
  const passwordInput = document.getElementById('passwordInput');
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  this.textContent = type === 'password' ? '👁️ Show' : '🙈 Hide';
});

// Generate random password
document.getElementById('generatePassword').addEventListener('click', function() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
  let password = '';
  for (let i = 0; i < 10; i++) {
    password += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  document.getElementById('passwordInput').value = password;
  document.getElementById('passwordInput').type = 'text';
  document.getElementById('togglePassword').textContent = '🙈 Hide';
});

// Auto-generate username from name
document.querySelector('input[name="name"]').addEventListener('blur', function() {
  const usernameInput = document.getElementById('usernameInput');
  if (!usernameInput.value) {
    const name = this.value.toLowerCase().replace(/\s+/g, '');
    usernameInput.value = name;
  }
});
</script>
</body>
</html>
