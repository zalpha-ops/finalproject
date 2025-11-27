<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "<div class='alert alert-danger'>Please login as a student.</div>";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get or create student profile - handle missing columns gracefully
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.created_at, u.status,
               sp.name, sp.email, sp.phone, sp.student_id, sp.cohort, sp.track,
               sp.date_of_birth, sp.address, sp.city, sp.country, sp.timezone
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If columns don't exist, try basic query
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.created_at, u.status,
               sp.name, sp.email, sp.phone, sp.student_id, sp.cohort, sp.track
        FROM users u
        LEFT JOIN student_profiles sp ON u.username = sp.username
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Add missing fields
    $profile['date_of_birth'] = null;
    $profile['address'] = null;
    $profile['city'] = null;
    $profile['country'] = null;
    $profile['timezone'] = null;
}

// Auto-create profile if doesn't exist
if (!$profile['name']) {
    try {
        $insertStmt = $pdo->prepare("
            INSERT INTO student_profiles (username, name, student_id, status, created_at)
            VALUES (?, ?, ?, 'Active', NOW())
        ");
        $insertStmt->execute([$username, $username, $username]);
        
        // Reload profile
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Profile creation failed, continue with empty data
    }
}

// Calculate account age
$accountAge = '';
if ($profile['created_at']) {
    $created = new DateTime($profile['created_at']);
    $now = new DateTime();
    $diff = $created->diff($now);
    if ($diff->y > 0) {
        $accountAge = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    } elseif ($diff->m > 0) {
        $accountAge = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    } else {
        $accountAge = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Profile Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0;">
                    <div class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 48px; font-weight: bold;">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </div>
                    <h3 class="mb-1"><?= htmlspecialchars($profile['name'] ?: $username) ?></h3>
                    <p class="mb-0 opacity-75">Student ID: <?= htmlspecialchars($profile['student_id'] ?: $username) ?></p>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🔐 Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Username</label>
                            <div class="fw-bold"><?= htmlspecialchars($username) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Student ID</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['student_id'] ?: 'Not set') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Account Status</label>
                            <div>
                                <span class="badge bg-<?= $profile['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($profile['status'] ?: 'Active') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Member Since</label>
                            <div class="fw-bold">
                                <?= $profile['created_at'] ? date('M d, Y', strtotime($profile['created_at'])) : 'Unknown' ?>
                                <?= $accountAge ? " ($accountAge ago)" : '' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">👤 Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Full Name</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['name'] ?: 'Not set') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Date of Birth</label>
                            <div class="fw-bold">
                                <?= $profile['date_of_birth'] ? date('M d, Y', strtotime($profile['date_of_birth'])) : 'Not set' ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Address</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['address'] ?: 'Not set') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">City</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['city'] ?: 'Not set') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Country</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['country'] ?: 'Not set') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📧 Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Email Address</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['email'] ?: 'Not set') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Phone Number</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['phone'] ?: 'Not set') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Timezone</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['timezone'] ?: 'Not set') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">🎓 Academic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Cohort</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['cohort'] ?: 'Not assigned') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Track/Program</label>
                            <div class="fw-bold"><?= htmlspecialchars($profile['track'] ?: 'Not assigned') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4">
                <button onclick="loadEditProfile()" class="btn btn-primary btn-lg">
                    ✏️ Edit Profile
                </button>
                <button onclick="location.reload()" class="btn btn-outline-secondary btn-lg">
                    🔄 Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    border: none;
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
    font-weight: 600;
}
</style>
