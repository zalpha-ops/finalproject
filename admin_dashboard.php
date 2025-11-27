<?php
require 'auth.php';

// Allow both admin and super_admin roles
require_role(['admin', 'super_admin']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Eagle Flight School</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #6B46C1;
            --dark-purple: #4C1D95;
            --light-purple: #9333EA;
            --accent-purple: #A855F7;
        }
        
        body {
            background: linear-gradient(135deg, #0F0F0F 0%, #4C1D95 50%, #6B46C1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
        }
        
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
            transition: all 0.3s;
            border: 2px solid transparent;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-purple);
            box-shadow: 0 15px 40px rgba(107, 70, 193, 0.3);
        }
        
        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary-purple), var(--light-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            background: transparent;
            transition: all 0.3s;
        }
        
        .btn-outline-purple:hover {
            background: var(--primary-purple);
            color: white;
        }
        
        .stats-badge {
            background: linear-gradient(135deg, var(--primary-purple), var(--light-purple));
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2">🦅 Eagle Flight School</h1>
                <h3 class="mb-2">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-user-shield me-2"></i>
                    Role: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>
                </p>
            </div>
            <div>
                <a href="logout.php" class="btn btn-light btn-lg">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Students Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h4 class="mb-3">Students</h4>
                <p class="text-muted mb-4">Manage student records and enrollment</p>
                <div class="d-grid gap-2">
                    <a href="enroll_student.php" class="btn btn-purple">
                        <i class="fas fa-plus me-2"></i>Enroll New
                    </a>
                    <a href="import_students.php" class="btn btn-success">
                        <i class="fas fa-file-csv me-2"></i>Import CSV
                    </a>
                    <a href="view_students.php" class="btn btn-outline-purple">
                        <i class="fas fa-list me-2"></i>View All
                    </a>
                </div>
            </div>
        </div>

        <!-- Instructors Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h4 class="mb-3">Instructors</h4>
                <p class="text-muted mb-4">Manage flight instructors and staff</p>
                <div class="d-grid gap-2">
                    <a href="enroll_instructor.php" class="btn btn-purple">
                        <i class="fas fa-plus me-2"></i>Enroll New
                    </a>
                    <a href="view_instructors.php" class="btn btn-outline-purple">
                        <i class="fas fa-list me-2"></i>View All
                    </a>
                </div>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h4 class="mb-3">Courses</h4>
                <p class="text-muted mb-4">Manage flight training courses</p>
                <div class="d-grid gap-2">
                    <a href="manage_courses.php" class="btn btn-purple">
                        <i class="fas fa-cog me-2"></i>Manage
                    </a>
                </div>
            </div>
        </div>

        <!-- Schedules Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4 class="mb-3">Schedules</h4>
                <p class="text-muted mb-4">Manage flight training schedules</p>
                <div class="d-grid gap-2">
                    <a href="admin_manage_schedules.php" class="btn btn-purple">
                        <i class="fas fa-calendar-check me-2"></i>Manage Schedules
                    </a>
                </div>
            </div>
        </div>

        <!-- Grades Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4 class="mb-3">Grades</h4>
                <p class="text-muted mb-4">View and manage student grades</p>
                <div class="d-grid gap-2">
                    <a href="manage_grades.php" class="btn btn-purple">
                        <i class="fas fa-graduation-cap me-2"></i>Manage
                    </a>
                </div>
            </div>
        </div>

        <!-- Announcements Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h4 class="mb-3">Announcements</h4>
                <p class="text-muted mb-4">Post and manage announcements</p>
                <div class="d-grid gap-2">
                    <a href="manage_announcements.php" class="btn btn-purple">
                        <i class="fas fa-megaphone me-2"></i>Manage
                    </a>
                </div>
            </div>
        </div>

        <!-- Reports Card -->
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card text-center">
                <div class="card-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4 class="mb-3">Reports</h4>
                <p class="text-muted mb-4">Generate and view system reports</p>
                <div class="d-grid gap-2">
                    <a href="manage_reports.php" class="btn btn-purple">
                        <i class="fas fa-chart-bar me-2"></i>View Reports
                    </a>
                </div>
            </div>
        </div>
        
        <!-- System Health Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card">
                <div class="card-icon mb-3">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h4 class="mb-3">System Health</h4>
                <p class="text-muted mb-4">Monitor system status and performance</p>
                <div class="d-grid gap-2">
                    <a href="system_health.php" class="btn btn-warning">
                        <i class="fas fa-stethoscope me-2"></i>Health Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
