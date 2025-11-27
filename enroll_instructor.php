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
    $certifications = trim($_POST['certifications'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    
    // Validation
    if (empty($name) || empty($username) || empty($password)) {
        $error = "Name, username, and password are required.";
    } else {
        try {
            // Check if username already exists
            $checkStmt = $pdo->prepare("SELECT instructor_id FROM instructors WHERE username = ?");
            $checkStmt->execute([$username]);
            if ($checkStmt->fetch()) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Hash password with bcrypt (instructors use bcrypt, not SHA-256)
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Check which columns exist in the table
                $stmt = $pdo->query("DESCRIBE instructors");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Build dynamic insert query based on existing columns
                $insertColumns = ['name', 'email', 'username', 'password', 'phone', 'certifications', 'status'];
                $insertValues = [':name', ':email', ':username', ':password', ':phone', ':certifications', ':status'];
                $insertData = [
                    ':name' => $name,
                    ':email' => $email,
                    ':username' => $username,
                    ':password' => $passwordHash,
                    ':phone' => $phone,
                    ':certifications' => $certifications,
                    ':status' => 'active'
                ];
                
                // Add optional columns if they exist
                if (in_array('username', $columns)) {
                    $insertColumns[] = 'username';
                    $insertValues[] = ':username';
                    $insertData[':username'] = $username;
                }
                
                if (in_array('password_hash', $columns)) {
                    $insertColumns[] = 'password_hash';
                    $insertValues[] = ':password_hash';
                    $insertData[':password_hash'] = $passwordHash;
                } elseif (in_array('password', $columns)) {
                    $insertColumns[] = 'password';
                    $insertValues[] = ':password';
                    $insertData[':password'] = $passwordHash;
                }
                
                if (in_array('experience', $columns)) {
                    $insertColumns[] = 'experience';
                    $insertValues[] = ':experience';
                    $insertData[':experience'] = $experience;
                }
                
                if (in_array('created_at', $columns)) {
                    $insertColumns[] = 'created_at';
                    $insertValues[] = 'NOW()';
                }
                
                // Build and execute the query
                $sql = "INSERT INTO instructors (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
                $insertStmt = $pdo->prepare($sql);
                $insertStmt->execute($insertData);
                
                $success = "✅ Instructor enrolled successfully!<br>
                           <strong>Name:</strong> $name<br>
                           <strong>Username:</strong> $username<br>
                           <strong>Password:</strong> $password<br>
                           <strong>Login URL:</strong> <a href='login.php'>login.php</a> (Select 'Instructor' role)";
            }
        } catch (Exception $e) {
            $error = "Error enrolling instructor: " . $e->getMessage();
        }
    }
}

// Get statistics
try {
    $totalInstructors = $pdo->query("SELECT COUNT(*) FROM instructors")->fetchColumn();
} catch (Exception $e) {
    $totalInstructors = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enroll New Instructor - Eagle Flight School</title>
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
        <h1 class="mb-2"><i class="fas fa-chalkboard-teacher me-2"></i>Enroll New Instructor</h1>
        <p class="mb-0 opacity-75">Add a new instructor to Eagle Flight School</p>
      </div>
      <a href="admin_dashboard.php" class="btn btn-light btn-lg">
        <i class="fas fa-arrow-left me-2"></i>Dashboard
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
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
          <h5 class="card-title mb-4">Instructor Information</h5>
          
          <form method="post" id="enrollmentForm">
            <!-- Personal Information -->
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required-field">Full Name</label>
                <input type="text" name="name" class="form-control" required 
                       placeholder="e.g., Captain John Smith">
                <small class="text-muted">Instructor's full name</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" 
                       placeholder="instructor@example.com">
                <small class="text-muted">For communication</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" 
                       placeholder="+1 (555) 123-4567">
                <small class="text-muted">Contact number</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Certifications</label>
                <input type="text" name="certifications" class="form-control" 
                       placeholder="CFI, CFII, MEI, ATP">
                <small class="text-muted">Comma-separated certifications (e.g., CFI, CFII, MEI)</small>
              </div>

              <div class="col-12">
                <label class="form-label">Experience</label>
                <textarea name="experience" class="form-control" rows="2"
                          placeholder="e.g., 10 years flight instruction, 5000+ hours"></textarea>
                <small class="text-muted">Brief description of experience</small>
              </div>
            </div>

            <hr class="my-4">

            <!-- Login Credentials -->
            <h6 class="mb-3">Login Credentials</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required-field">Username</label>
                <input type="text" name="username" id="usernameInput" class="form-control" required 
                       placeholder="e.g., jsmith" pattern="[a-zA-Z0-9_]+" 
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

            <!-- Submit Buttons -->
            <div class="d-flex gap-2 flex-wrap">
              <button type="submit" class="btn btn-purple btn-lg">
                <i class="fas fa-check-circle me-2"></i>Enroll Instructor
              </button>
              <a href="view_instructors.php" class="btn btn-outline-purple btn-lg">
                <i class="fas fa-users me-2"></i>View All Instructors
              </a>
              <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <h6 class="card-title">📊 Statistics</h6>
          <p class="mb-1"><strong>Total Instructors:</strong> <?= $totalInstructors ?></p>
        </div>
      </div>

      <div class="info-box">
        <h6>📝 Enrollment Process</h6>
        <ol class="mb-0 ps-3">
          <li>Fill in instructor information</li>
          <li>Create login credentials</li>
          <li>Click "Enroll Instructor"</li>
          <li>Share credentials with instructor</li>
        </ol>
      </div>

      <div class="info-box">
        <h6>🔐 Login Information</h6>
        <p class="mb-1"><strong>Login URL:</strong></p>
        <p class="mb-1"><small><a href="login.php">login.php</a></small></p>
        <p class="mb-1"><strong>Role:</strong> Instructor</p>
        <p class="mb-0"><strong>Password:</strong> bcrypt hashed</p>
      </div>

      <div class="info-box">
        <h6>💡 Tips</h6>
        <ul class="mb-0 ps-3">
          <li>Username must be unique</li>
          <li>Use strong passwords</li>
          <li>Save credentials securely</li>
          <li>Instructor can login immediately</li>
        </ul>
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
    const name = this.value.toLowerCase().replace(/\s+/g, '').replace(/[^a-z0-9]/g, '');
    usernameInput.value = name.substring(0, 20);
  }
});
</script>
</body>
</html>
