<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student.</div>";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = null;
$success = null;

// Get student profile - handle missing columns gracefully
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username,
               sp.name, sp.email, sp.phone, sp.student_id,
               sp.city, sp.country, sp.timezone, sp.email_visibility
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If columns don't exist, try basic query
    $stmt = $pdo->prepare("
        SELECT u.id, u.username,
               sp.name, sp.email, sp.phone, sp.student_id
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Add missing fields
    $profile['city'] = null;
    $profile['country'] = null;
    $profile['timezone'] = null;
    $profile['email_visibility'] = 'hidden';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);
    $email = trim($_POST['email'] ?? '');
    $emailVisibility = $_POST['email_visibility'] ?? 'hidden';
    $city = trim($_POST['city'] ?? '');
    $country = $_POST['country'] ?? '';
    $timezone = $_POST['timezone'] ?? '';
    
    if (empty($firstName) || empty($lastName)) {
        $error = "First name and last name are required.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // Check if profile exists
            $checkStmt = $pdo->prepare("SELECT id FROM student_profiles WHERE username = ?");
            $checkStmt->execute([$username]);
            
            if ($checkStmt->fetch()) {
                // Update existing profile - try with all columns first
                try {
                    $updateStmt = $pdo->prepare("
                        UPDATE student_profiles 
                        SET name = ?, email = ?, city = ?, country = ?, 
                            timezone = ?, email_visibility = ?, updated_at = NOW()
                        WHERE username = ?
                    ");
                    $updateStmt->execute([$fullName, $email, $city, $country, $timezone, $emailVisibility, $username]);
                } catch (Exception $e) {
                    // If columns don't exist, update only basic fields
                    $updateStmt = $pdo->prepare("
                        UPDATE student_profiles 
                        SET name = ?, email = ?
                        WHERE username = ?
                    ");
                    $updateStmt->execute([$fullName, $email, $username]);
                }
            } else {
                // Create new profile - try with all columns first
                try {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO student_profiles 
                        (username, name, email, city, country, timezone, email_visibility, student_id, status, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
                    ");
                    $insertStmt->execute([$username, $fullName, $email, $city, $country, $timezone, $emailVisibility, $username]);
                } catch (Exception $e) {
                    // If columns don't exist, create with basic fields
                    $insertStmt = $pdo->prepare("
                        INSERT INTO student_profiles 
                        (username, name, email, student_id, status, created_at)
                        VALUES (?, ?, ?, ?, 'Active', NOW())
                    ");
                    $insertStmt->execute([$username, $fullName, $email, $username]);
                }
            }
            
            $success = "✅ Profile updated successfully!";
            
            // Reload profile
            try {
                $stmt->execute([$userId]);
                $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Ignore reload error
            }
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Split name into first and last
$nameParts = explode(' ', $profile['name'] ?? '', 2);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">✏️ Edit Profile</h4>
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

                    <!-- Student ID Display -->
                    <div class="alert alert-info mb-4">
                        <strong>Student ID:</strong> <?= htmlspecialchars($profile['student_id'] ?: $username) ?>
                    </div>

                    <form method="post">
                        <!-- General Section -->
                        <h5 class="border-bottom pb-2 mb-3">General</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required
                                       value="<?= htmlspecialchars($firstName) ?>"
                                       placeholder="Enter your first name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required
                                       value="<?= htmlspecialchars($lastName) ?>"
                                       placeholder="Enter your last name">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Email address</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($profile['email'] ?? '') ?>"
                                       placeholder="your.email@example.com">
                                <small class="text-muted">Your email address for notifications</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Email visibility</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="email_visibility" 
                                           value="hidden" id="emailHidden"
                                           <?= ($profile['email_visibility'] ?? 'hidden') === 'hidden' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="emailHidden">
                                        Hidden
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="email_visibility" 
                                           value="everyone" id="emailEveryone"
                                           <?= ($profile['email_visibility'] ?? '') === 'everyone' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="emailEveryone">
                                        Visible to everyone
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="email_visibility" 
                                           value="course" id="emailCourse"
                                           <?= ($profile['email_visibility'] ?? '') === 'course' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="emailCourse">
                                        Visible to course participants
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Location Section -->
                        <h5 class="border-bottom pb-2 mb-3">Location</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">City/town</label>
                                <input type="text" name="city" class="form-control"
                                       value="<?= htmlspecialchars($profile['city'] ?? '') ?>"
                                       placeholder="Enter your city">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Country</label>
                                <select name="country" class="form-select">
                                    <option value="">Select a country</option>
                                    <option value="Kenya" <?= ($profile['country'] ?? '') === 'Kenya' ? 'selected' : '' ?>>Kenya</option>
                                    <option value="Uganda" <?= ($profile['country'] ?? '') === 'Uganda' ? 'selected' : '' ?>>Uganda</option>
                                    <option value="Tanzania" <?= ($profile['country'] ?? '') === 'Tanzania' ? 'selected' : '' ?>>Tanzania</option>
                                    <option value="Rwanda" <?= ($profile['country'] ?? '') === 'Rwanda' ? 'selected' : '' ?>>Rwanda</option>
                                    <option value="Ethiopia" <?= ($profile['country'] ?? '') === 'Ethiopia' ? 'selected' : '' ?>>Ethiopia</option>
                                    <option value="South Africa" <?= ($profile['country'] ?? '') === 'South Africa' ? 'selected' : '' ?>>South Africa</option>
                                    <option value="Nigeria" <?= ($profile['country'] ?? '') === 'Nigeria' ? 'selected' : '' ?>>Nigeria</option>
                                    <option value="Ghana" <?= ($profile['country'] ?? '') === 'Ghana' ? 'selected' : '' ?>>Ghana</option>
                                    <option value="United States" <?= ($profile['country'] ?? '') === 'United States' ? 'selected' : '' ?>>United States</option>
                                    <option value="United Kingdom" <?= ($profile['country'] ?? '') === 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                                    <option value="Canada" <?= ($profile['country'] ?? '') === 'Canada' ? 'selected' : '' ?>>Canada</option>
                                    <option value="Australia" <?= ($profile['country'] ?? '') === 'Australia' ? 'selected' : '' ?>>Australia</option>
                                    <option value="Other" <?= ($profile['country'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Timezone</label>
                                <select name="timezone" class="form-select">
                                    <option value="">Select timezone</option>
                                    <option value="Africa/Nairobi" <?= ($profile['timezone'] ?? '') === 'Africa/Nairobi' ? 'selected' : '' ?>>Africa/Nairobi (EAT)</option>
                                    <option value="Africa/Lagos" <?= ($profile['timezone'] ?? '') === 'Africa/Lagos' ? 'selected' : '' ?>>Africa/Lagos (WAT)</option>
                                    <option value="Africa/Johannesburg" <?= ($profile['timezone'] ?? '') === 'Africa/Johannesburg' ? 'selected' : '' ?>>Africa/Johannesburg (SAST)</option>
                                    <option value="America/New_York" <?= ($profile['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New York (EST)</option>
                                    <option value="America/Chicago" <?= ($profile['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>America/Chicago (CST)</option>
                                    <option value="America/Los_Angeles" <?= ($profile['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>America/Los Angeles (PST)</option>
                                    <option value="Europe/London" <?= ($profile['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>Europe/London (GMT)</option>
                                    <option value="UTC" <?= ($profile['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                </select>
                                <small class="text-muted">Your local timezone</small>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                💾 Update Profile
                            </button>
                            <button type="button" onclick="loadUserProfile()" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </button>
                        </div>
                    </form>
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
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}
</style>
