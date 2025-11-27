<?php
session_start();
require 'db_connect.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student to access this page.</div>";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = null;
$success = null;

// Get student information - handle missing columns gracefully
try {
    $studentStmt = $pdo->prepare("
        SELECT u.id, u.username, u.created_at,
               sp.name, sp.email, sp.phone, sp.student_id, sp.cohort, sp.track,
               sp.date_of_birth, sp.address, sp.emergency_contact, sp.emergency_phone
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ?
    ");
    $studentStmt->execute([$userId]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If columns don't exist, try basic query
    $studentStmt = $pdo->prepare("
        SELECT u.id, u.username, u.created_at,
               sp.name, sp.email, sp.phone, sp.student_id, sp.cohort, sp.track
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ?
    ");
    $studentStmt->execute([$userId]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    
    // Add missing fields as null
    $student['date_of_birth'] = null;
    $student['address'] = null;
    $student['emergency_contact'] = null;
    $student['emergency_phone'] = null;
    
    // Show setup message
    $error = "⚠️ Database needs setup. Please ask admin to run: setup_student_profiles.php";
}

// Check if profile exists in student_profiles
$hasProfile = !empty($student['name']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dateOfBirth = $_POST['date_of_birth'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $emergencyContact = trim($_POST['emergency_contact'] ?? '');
    $emergencyPhone = trim($_POST['emergency_phone'] ?? '');
    
    // Validation
    if (empty($name)) {
        $error = "Name is required.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            if ($hasProfile) {
                // Update existing profile
                $updateStmt = $pdo->prepare("
                    UPDATE student_profiles 
                    SET name = :name, email = :email, phone = :phone,
                        date_of_birth = :dob, address = :address,
                        emergency_contact = :emergency_contact,
                        emergency_phone = :emergency_phone,
                        updated_at = NOW()
                    WHERE username = :username
                ");
                $updateStmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':dob' => $dateOfBirth,
                    ':address' => $address,
                    ':emergency_contact' => $emergencyContact,
                    ':emergency_phone' => $emergencyPhone,
                    ':username' => $username
                ]);
            } else {
                // Create new profile
                $insertStmt = $pdo->prepare("
                    INSERT INTO student_profiles 
                    (username, name, email, phone, date_of_birth, address, 
                     emergency_contact, emergency_phone, student_id, status, created_at)
                    VALUES (:username, :name, :email, :phone, :dob, :address,
                            :emergency_contact, :emergency_phone, :student_id, 'Active', NOW())
                ");
                $insertStmt->execute([
                    ':username' => $username,
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':dob' => $dateOfBirth,
                    ':address' => $address,
                    ':emergency_contact' => $emergencyContact,
                    ':emergency_phone' => $emergencyPhone,
                    ':student_id' => $username
                ]);
            }
            
            $success = "✅ Profile updated successfully!";
            
            // Reload student data
            $studentStmt->execute([$userId]);
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
            $hasProfile = true;
            
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Check if this is first login (no profile data)
$isFirstLogin = empty($student['name']);
?>

<div class="container-fluid">
    <?php if ($isFirstLogin): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <h5 class="alert-heading">👋 Welcome to Eagle Flight School!</h5>
            <p class="mb-0">Please complete your profile to get started. This information helps us serve you better.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">👤 My Profile</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="profileForm">
                        <!-- Personal Information -->
                        <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= htmlspecialchars($student['name'] ?? '') ?>"
                                       placeholder="Enter your full name">
                                <small class="text-muted">Your legal name as it appears on documents</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                       value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>">
                                <small class="text-muted">Optional</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <textarea name="address" class="form-control" rows="2"
                                          placeholder="Enter your full address"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                                <small class="text-muted">Your current residential address</small>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($student['email'] ?? '') ?>"
                                       placeholder="your.email@example.com">
                                <small class="text-muted">For important notifications</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($student['phone'] ?? '') ?>"
                                       placeholder="+1 (555) 123-4567">
                                <small class="text-muted">Your contact number</small>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <h6 class="border-bottom pb-2 mb-3">Emergency Contact</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact" class="form-control"
                                       value="<?= htmlspecialchars($student['emergency_contact'] ?? '') ?>"
                                       placeholder="Contact person name">
                                <small class="text-muted">Person to contact in case of emergency</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_phone" class="form-control"
                                       value="<?= htmlspecialchars($student['emergency_phone'] ?? '') ?>"
                                       placeholder="+1 (555) 987-6543">
                                <small class="text-muted">Emergency contact number</small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                💾 Save Profile
                            </button>
                            <?php if (!$isFirstLogin): ?>
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="location.reload()">
                                    Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Account Information -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">🔐 Account Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Username:</strong><br><?= htmlspecialchars($username) ?></p>
                    <p class="mb-2"><strong>Student ID:</strong><br><?= htmlspecialchars($student['student_id'] ?? $username) ?></p>
                    <p class="mb-2"><strong>Cohort:</strong><br><?= htmlspecialchars($student['cohort'] ?? 'Not assigned') ?></p>
                    <p class="mb-0"><strong>Track:</strong><br><?= htmlspecialchars($student['track'] ?? 'Not assigned') ?></p>
                    <hr>
                    <small class="text-muted">Member since: <?= date('M d, Y', strtotime($student['created_at'])) ?></small>
                </div>
            </div>

            <!-- What You Can Edit -->
            <div class="card shadow-sm mb-3 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">✏️ What You Can Edit</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li>Full Name</li>
                        <li>Date of Birth</li>
                        <li>Address</li>
                        <li>Email Address</li>
                        <li>Phone Number</li>
                        <li>Emergency Contact Info</li>
                    </ul>
                </div>
            </div>

            <!-- What You Cannot Edit -->
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">🔒 Cannot Be Changed</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li>Username</li>
                        <li>Student ID</li>
                        <li>Cohort (assigned by admin)</li>
                        <li>Track (assigned by admin)</li>
                    </ul>
                    <hr>
                    <small class="text-muted">Contact admin to change these fields</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
}
.form-label {
    margin-bottom: 0.25rem;
}
.form-control:focus {
    border-color: #4b0762ff;
    box-shadow: 0 0 0 0.25rem rgba(75, 7, 98, 0.25);
}
</style>
