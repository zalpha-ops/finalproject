<?php
session_start();
require 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get student ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Student ID not provided.";
    header("Location: view_students.php");
    exit;
}

$studentId = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $student_id = trim($_POST['student_id']);
    $cohort = trim($_POST['cohort']);
    $track = trim($_POST['track']);
    $status = $_POST['status'];
    
    try {
        $pdo->beginTransaction();
        
        // Get current username
        $userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ? AND role = 'student'");
        $userStmt->execute([$studentId]);
        $username = $userStmt->fetchColumn();
        
        if (!$username) {
            throw new Exception("Student not found.");
        }
        
        // Update users table
        $updateUserStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateUserStmt->execute([$status, $studentId]);
        
        // Update or insert student profile
        $checkProfileStmt = $pdo->prepare("SELECT id FROM student_profiles WHERE username = ?");
        $checkProfileStmt->execute([$username]);
        
        if ($checkProfileStmt->fetchColumn()) {
            // Update existing profile
            $updateProfileStmt = $pdo->prepare("
                UPDATE student_profiles 
                SET name = ?, email = ?, phone = ?, student_id = ?, cohort = ?, track = ?, status = ?
                WHERE username = ?
            ");
            $updateProfileStmt->execute([$name, $email, $phone, $student_id, $cohort, $track, $status, $username]);
        } else {
            // Insert new profile
            $insertProfileStmt = $pdo->prepare("
                INSERT INTO student_profiles (username, name, email, phone, student_id, cohort, track, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertProfileStmt->execute([$username, $name, $email, $phone, $student_id, $cohort, $track, $status]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Student updated successfully!";
        header("Location: view_students.php");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error updating student: " . $e->getMessage();
    }
}

// Fetch student data
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.status, u.created_at,
               sp.name, sp.student_id, sp.email, sp.phone, sp.cohort, sp.track
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ? AND u.role = 'student'
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        $_SESSION['error'] = "Student not found.";
        header("Location: view_students.php");
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching student data: " . $e->getMessage();
    header("Location: view_students.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Eagle Flight School</title>
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
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: var(--white);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(107, 70, 193, 0.3);
        }
        
        .form-card {
            background: var(--white);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .form-card .card-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: var(--white);
            border: none;
            padding: 1.5rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 0.2rem rgba(107, 70, 193, 0.25);
        }
        
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border: none;
            color: var(--white);
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(107, 70, 193, 0.4);
            color: var(--white);
        }
        
        .btn-outline-purple {
            border: 2px solid var(--primary-purple);
            color: var(--primary-purple);
            background: var(--white);
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline-purple:hover {
            background: var(--primary-purple);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-purple);
            margin-bottom: 0.5rem;
        }
        
        .student-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2"><i class="fas fa-user-edit me-2"></i>Edit Student</h2>
                <p class="mb-0 opacity-75">Update student information and profile</p>
            </div>
            <a href="view_students.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Students
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Student Info -->
    <div class="student-info">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-1"><i class="fas fa-user me-2"></i>Editing: <?= htmlspecialchars($student['name'] ?? $student['username']) ?></h5>
                <p class="text-muted mb-0">Username: <?= htmlspecialchars($student['username']) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Enrolled: <?= date('M d, Y', strtotime($student['created_at'])) ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card form-card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Student Information</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">
                            <i class="fas fa-user me-1"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($student['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="student_id" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Student ID
                        </label>
                        <input type="text" class="form-control" id="student_id" name="student_id" 
                               value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($student['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone me-1"></i>Phone Number
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="cohort" class="form-label">
                            <i class="fas fa-users me-1"></i>Cohort
                        </label>
                        <select class="form-control" id="cohort" name="cohort">
                            <option value="">Select Cohort</option>
                            <option value="2024-Spring" <?= ($student['cohort'] ?? '') === '2024-Spring' ? 'selected' : '' ?>>2024 Spring</option>
                            <option value="2024-Summer" <?= ($student['cohort'] ?? '') === '2024-Summer' ? 'selected' : '' ?>>2024 Summer</option>
                            <option value="2024-Fall" <?= ($student['cohort'] ?? '') === '2024-Fall' ? 'selected' : '' ?>>2024 Fall</option>
                            <option value="2025-Spring" <?= ($student['cohort'] ?? '') === '2025-Spring' ? 'selected' : '' ?>>2025 Spring</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="track" class="form-label">
                            <i class="fas fa-plane me-1"></i>Flight Track
                        </label>
                        <select class="form-control" id="track" name="track">
                            <option value="">Select Track</option>
                            <option value="Private Pilot" <?= ($student['track'] ?? '') === 'Private Pilot' ? 'selected' : '' ?>>Private Pilot</option>
                            <option value="Commercial Pilot" <?= ($student['track'] ?? '') === 'Commercial Pilot' ? 'selected' : '' ?>>Commercial Pilot</option>
                            <option value="Instrument Rating" <?= ($student['track'] ?? '') === 'Instrument Rating' ? 'selected' : '' ?>>Instrument Rating</option>
                            <option value="Multi-Engine" <?= ($student['track'] ?? '') === 'Multi-Engine' ? 'selected' : '' ?>>Multi-Engine</option>
                            <option value="Flight Instructor" <?= ($student['track'] ?? '') === 'Flight Instructor' ? 'selected' : '' ?>>Flight Instructor</option>
                        </select>
                    </div>
                    
                    <div class="col-md-12">
                        <label for="status" class="form-label">
                            <i class="fas fa-toggle-on me-1"></i>Account Status
                        </label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active" <?= ($student['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($student['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-purple">
                        <i class="fas fa-save me-2"></i>Update Student
                    </button>
                    <a href="view_students.php" class="btn btn-outline-purple">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Additional Actions -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title"><i class="fas fa-tools me-2"></i>Additional Actions</h6>
            <div class="d-flex gap-2 flex-wrap">
                <a href="reset_student_password.php?id=<?= $student['id'] ?>" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-key me-1"></i>Reset Password
                </a>
                <a href="manage_grades.php?student=<?= urlencode($student['username']) ?>" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-chart-line me-1"></i>View Grades
                </a>
                <a href="view_students.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i>All Students
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>