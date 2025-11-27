<?php
session_start();
require 'db_connect.php';
require 'auth.php';
require_role('admin');

// System Health Check Functions
function checkDatabaseConnection($pdo) {
    try {
        $pdo->query("SELECT 1");
        return ['status' => 'healthy', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
}

function checkRequiredTables($pdo) {
    $requiredTables = ['users', 'student_profiles', 'courses', 'instructors', 'grades', 'assignments'];
    $missingTables = [];
    
    try {
        foreach ($requiredTables as $table) {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() === 0) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            return ['status' => 'healthy', 'message' => 'All required tables exist'];
        } else {
            return ['status' => 'warning', 'message' => 'Missing tables: ' . implode(', ', $missingTables)];
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Cannot check tables: ' . $e->getMessage()];
    }
}

function checkFilePermissions() {
    $criticalFiles = [
        'db_connect.php' => 'Database configuration',
        'auth.php' => 'Authentication system',
        'admin_dashboard.php' => 'Admin dashboard',
        'login.php' => 'Login system'
    ];
    
    $issues = [];
    
    foreach ($criticalFiles as $file => $description) {
        if (!file_exists($file)) {
            $issues[] = "$description ($file) - File missing";
        } elseif (!is_readable($file)) {
            $issues[] = "$description ($file) - Not readable";
        }
    }
    
    if (empty($issues)) {
        return ['status' => 'healthy', 'message' => 'All critical files accessible'];
    } else {
        return ['status' => 'error', 'message' => implode(', ', $issues)];
    }
}

function checkSessionSystem() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return ['status' => 'healthy', 'message' => 'Session system working'];
    } else {
        return ['status' => 'error', 'message' => 'Session system not active'];
    }
}

function checkSystemStats($pdo) {
    try {
        $stats = [];
        
        // Count users by role
        $userStats = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($userStats as $stat) {
            $stats[$stat['role'] . '_count'] = $stat['count'];
        }
        
        // Total students
        $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM student_profiles")->fetchColumn();
        
        // Total courses
        $stats['total_courses'] = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
        
        // Recent activity (last 7 days)
        $stats['recent_logins'] = $pdo->query("SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        
        return ['status' => 'healthy', 'data' => $stats];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Cannot retrieve stats: ' . $e->getMessage()];
    }
}

function checkDiskSpace() {
    $freeBytes = disk_free_space('.');
    $totalBytes = disk_total_space('.');
    
    if ($freeBytes === false || $totalBytes === false) {
        return ['status' => 'warning', 'message' => 'Cannot check disk space'];
    }
    
    $freeGB = round($freeBytes / 1024 / 1024 / 1024, 2);
    $totalGB = round($totalBytes / 1024 / 1024 / 1024, 2);
    $usedPercent = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 1);
    
    if ($usedPercent > 90) {
        return ['status' => 'error', 'message' => "Disk space critical: {$usedPercent}% used ({$freeGB}GB free)"];
    } elseif ($usedPercent > 80) {
        return ['status' => 'warning', 'message' => "Disk space low: {$usedPercent}% used ({$freeGB}GB free)"];
    } else {
        return ['status' => 'healthy', 'message' => "Disk space good: {$usedPercent}% used ({$freeGB}GB free)"];
    }
}

function checkPHPConfiguration() {
    $issues = [];
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $issues[] = 'PHP version too old: ' . PHP_VERSION;
    }
    
    // Check required extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'session', 'json'];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $issues[] = "Missing PHP extension: $ext";
        }
    }
    
    // Check upload settings
    $maxUpload = ini_get('upload_max_filesize');
    $maxPost = ini_get('post_max_size');
    
    if (empty($issues)) {
        return ['status' => 'healthy', 'message' => "PHP " . PHP_VERSION . " configured properly"];
    } else {
        return ['status' => 'error', 'message' => implode(', ', $issues)];
    }
}

// Run all health checks
$healthChecks = [
    'Database Connection' => checkDatabaseConnection($pdo),
    'Required Tables' => checkRequiredTables($pdo),
    'File Permissions' => checkFilePermissions(),
    'Session System' => checkSessionSystem(),
    'PHP Configuration' => checkPHPConfiguration(),
    'Disk Space' => checkDiskSpace(),
    'System Statistics' => checkSystemStats($pdo)
];

// Calculate overall health
$healthyCount = 0;
$warningCount = 0;
$errorCount = 0;

foreach ($healthChecks as $check) {
    switch ($check['status']) {
        case 'healthy':
            $healthyCount++;
            break;
        case 'warning':
            $warningCount++;
            break;
        case 'error':
            $errorCount++;
            break;
    }
}

$totalChecks = count($healthChecks) - 1; // Exclude stats from health calculation
$overallStatus = 'healthy';
if ($errorCount > 0) {
    $overallStatus = 'error';
} elseif ($warningCount > 0) {
    $overallStatus = 'warning';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Dashboard - Eagle Flight School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #6B46C1;
            --dark-purple: #4C1D95;
            --success-green: #10B981;
            --warning-orange: #F59E0B;
            --error-red: #EF4444;
        }
        
        body {
            background: linear-gradient(135deg, #0F0F0F 0%, #4C1D95 50%, #6B46C1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .health-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
        }
        
        .health-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #e5e7eb;
        }
        
        .health-card.healthy {
            border-left-color: var(--success-green);
        }
        
        .health-card.warning {
            border-left-color: var(--warning-orange);
        }
        
        .health-card.error {
            border-left-color: var(--error-red);
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .status-healthy {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .status-warning {
            background: #FEF3C7;
            color: #92400E;
        }
        
        .status-error {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .overall-status {
            font-size: 1.5rem;
            font-weight: bold;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-purple);
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border: none;
            color: white;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
            color: white;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="health-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fas fa-heartbeat me-2"></i>System Health Dashboard</h1>
                <p class="mb-0 opacity-75">Real-time monitoring of Eagle Flight School systems</p>
            </div>
            <div>
                <button onclick="location.reload()" class="btn refresh-btn btn-lg me-2">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <a href="admin_dashboard.php" class="btn btn-light btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Status -->
    <div class="overall-status status-<?= $overallStatus ?>">
        <?php if ($overallStatus === 'healthy'): ?>
            <i class="fas fa-check-circle me-2"></i>System Status: All Systems Operational
        <?php elseif ($overallStatus === 'warning'): ?>
            <i class="fas fa-exclamation-triangle me-2"></i>System Status: Minor Issues Detected
        <?php else: ?>
            <i class="fas fa-times-circle me-2"></i>System Status: Critical Issues Require Attention
        <?php endif; ?>
        <div class="mt-2">
            <small>
                ✅ <?= $healthyCount ?> Healthy | 
                ⚠️ <?= $warningCount ?> Warnings | 
                ❌ <?= $errorCount ?> Errors
            </small>
        </div>
    </div>

    <!-- Health Checks -->
    <div class="row">
        <div class="col-lg-8">
            <h3 class="text-white mb-4">🔍 System Components</h3>
            
            <?php foreach ($healthChecks as $checkName => $result): ?>
                <?php if ($checkName === 'System Statistics') continue; ?>
                <div class="health-card <?= $result['status'] ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-2"><?= $checkName ?></h5>
                            <p class="mb-0 text-muted"><?= $result['message'] ?></p>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $result['status'] ?>">
                                <?php if ($result['status'] === 'healthy'): ?>
                                    <i class="fas fa-check me-1"></i>Healthy
                                <?php elseif ($result['status'] === 'warning'): ?>
                                    <i class="fas fa-exclamation-triangle me-1"></i>Warning
                                <?php else: ?>
                                    <i class="fas fa-times me-1"></i>Error
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
            <h3 class="text-white mb-4">📊 System Statistics</h3>
            
            <?php if (isset($healthChecks['System Statistics']['data'])): ?>
                <?php $stats = $healthChecks['System Statistics']['data']; ?>
                <div class="health-card">
                    <h5 class="mb-3">Current System Data</h5>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['total_students'] ?? 0 ?></div>
                            <div class="text-muted">Total Students</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['total_courses'] ?? 0 ?></div>
                            <div class="text-muted">Total Courses</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['admin_count'] ?? 0 ?></div>
                            <div class="text-muted">Administrators</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['instructor_count'] ?? 0 ?></div>
                            <div class="text-muted">Instructors</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['student_count'] ?? 0 ?></div>
                            <div class="text-muted">Student Accounts</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?= $stats['recent_logins'] ?? 0 ?></div>
                            <div class="text-muted">Recent Logins (7d)</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <div class="health-card">
                <h5 class="mb-3">🛠️ Quick Actions</h5>
                
                <div class="d-grid gap-2">
                    <a href="view_students.php" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i>View All Students
                    </a>
                    <a href="check_tables.php" class="btn btn-outline-secondary">
                        <i class="fas fa-database me-2"></i>Check Database
                    </a>
                    <a href="admin_dashboard.php" class="btn btn-outline-success">
                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                    </a>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="health-card">
                <h5 class="mb-3">ℹ️ System Information</h5>
                
                <div class="small text-muted">
                    <div class="mb-2"><strong>PHP Version:</strong> <?= PHP_VERSION ?></div>
                    <div class="mb-2"><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>
                    <div class="mb-2"><strong>Last Check:</strong> <?= date('Y-m-d H:i:s') ?></div>
                    <div class="mb-0"><strong>Timezone:</strong> <?= date_default_timezone_get() ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-refresh every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);

// Add loading state to refresh button
document.querySelector('.refresh-btn').addEventListener('click', function() {
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    this.disabled = true;
});
</script>
</body>
</html>